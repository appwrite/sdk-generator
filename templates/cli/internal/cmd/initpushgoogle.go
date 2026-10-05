//go:build !browser

package cmd

import (
	"bytes"
	"crypto/rand"
	"encoding/base64"
	"encoding/json"
	"errors"
	"fmt"
	"io"
	"net/http"
	"net/url"
	"strings"
	"time"

	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/client"
	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/output"
	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/prompt"
)

const (
	googleCloudScope    = "https://www.googleapis.com/auth/cloud-platform"
	fcmSenderRole       = "roles/firebasecloudmessaging.admin"
	googleSignInTimeout = 5 * time.Minute
)

var (
	errAutomaticKey          = errors.New("automatic key creation chosen")
	errProviderAlreadySetUp  = errors.New("provider already set up")
	fcmProvisionPermissions  = []string{"iam.serviceAccounts.create", "iam.serviceAccountKeys.create", "resourcemanager.projects.setIamPolicy"}
	fcmProvisionServices     = []string{"cloudresourcemanager.googleapis.com", "iam.googleapis.com", "fcm.googleapis.com"}
	defaultGoogleCloudHosts  = map[string]string{"firebase": "https://firebase.googleapis.com", "crm": "https://cloudresourcemanager.googleapis.com", "iam": "https://iam.googleapis.com", "serviceusage": "https://serviceusage.googleapis.com"}
	googleRolePropagationTry = 6
)

type googleAPIError struct {
	Status  int
	Reason  string
	Message string
	HelpURL string
}

func (e *googleAPIError) Error() string {
	if e.Message != "" {
		return e.Message
	}

	return fmt.Sprintf("Google request failed (%d)", e.Status)
}

type googleCloud struct {
	token string
	http  *http.Client
	hosts map[string]string
	wait  time.Duration
}

func (g *googleCloud) call(method, service, path, quotaProject string, body, out any) error {
	var reader io.Reader
	if body != nil {
		encoded, err := json.Marshal(body)
		if err != nil {
			return err
		}
		reader = bytes.NewReader(encoded)
	}
	request, err := http.NewRequest(method, g.hosts[service]+path, reader)
	if err != nil {
		return err
	}
	request.Header.Set("Accept", "application/json")
	request.Header.Set("Authorization", "Bearer "+g.token)
	if body != nil {
		request.Header.Set("Content-Type", "application/json")
	}
	if quotaProject != "" {
		request.Header.Set("X-Goog-User-Project", quotaProject)
	}

	response, err := g.http.Do(request)
	if err != nil {
		return err
	}
	defer response.Body.Close()
	payload, err := io.ReadAll(response.Body)
	if err != nil {
		return err
	}

	if response.StatusCode < 200 || response.StatusCode >= 300 {
		return readGoogleError(response.StatusCode, payload)
	}
	if out != nil && len(payload) > 0 {
		return json.Unmarshal(payload, out)
	}

	return nil
}

func readGoogleError(status int, payload []byte) *googleAPIError {
	apiError := &googleAPIError{Status: status}
	var parsed struct {
		Error struct {
			Message string `json:"message"`
			Status  string `json:"status"`
			Details []struct {
				Reason   string `json:"reason"`
				Metadata struct {
					ActivationURL string `json:"activationUrl"`
				} `json:"metadata"`
				Links []struct {
					URL string `json:"url"`
				} `json:"links"`
			} `json:"details"`
		} `json:"error"`
	}
	if json.Unmarshal(payload, &parsed) != nil {
		return apiError
	}
	apiError.Message = parsed.Error.Message
	apiError.Reason = parsed.Error.Status
	for _, detail := range parsed.Error.Details {
		if detail.Reason != "" {
			apiError.Reason = detail.Reason
		}
		if apiError.HelpURL == "" && detail.Metadata.ActivationURL != "" {
			apiError.HelpURL = detail.Metadata.ActivationURL
		}
		if apiError.HelpURL == "" && len(detail.Links) > 0 {
			apiError.HelpURL = detail.Links[0].URL
		}
	}

	return apiError
}

func googleErrorIs(err error, check func(*googleAPIError) bool) bool {
	var apiError *googleAPIError

	return errors.As(err, &apiError) && check(apiError)
}

func isGoogleUnauthorized(err error) bool {
	return googleErrorIs(err, func(e *googleAPIError) bool { return e.Status == 401 })
}

func isGoogleScopeError(err error) bool {
	return googleErrorIs(err, func(e *googleAPIError) bool {
		if e.Status != 403 {
			return false
		}
		message := strings.ToLower(e.Message)

		return e.Reason == "ACCESS_TOKEN_SCOPE_INSUFFICIENT" || (strings.Contains(message, "scope") && strings.Contains(message, "insufficient"))
	})
}

func isGoogleServiceDisabled(err error) bool {
	return googleErrorIs(err, func(e *googleAPIError) bool { return e.Reason == "SERVICE_DISABLED" })
}

func isGoogleKeyCreationBlocked(err error) bool {
	return googleErrorIs(err, func(e *googleAPIError) bool {
		message := strings.ToLower(e.Message)

		return (e.Status == 400 || e.Status == 403 || e.Status == 412) &&
			(strings.Contains(message, "key creation") || strings.Contains(message, "disableserviceaccountkeycreation"))
	})
}

func isGoogleNotFoundYet(err error) bool {
	return googleErrorIs(err, func(e *googleAPIError) bool {
		message := strings.ToLower(e.Message)

		return (e.Status == 400 || e.Status == 404) && strings.Contains(message, "does not exist")
	})
}

func (g *googleCloud) firebaseProjects() ([]firebaseProject, error) {
	var projects []firebaseProject
	pageToken := ""
	for page := 0; page < 10; page++ {
		query := url.Values{"pageSize": []string{"100"}}
		if pageToken != "" {
			query.Set("pageToken", pageToken)
		}
		var result struct {
			Results []struct {
				ProjectID   string `json:"projectId"`
				DisplayName string `json:"displayName"`
				State       string `json:"state"`
			} `json:"results"`
			NextPageToken string `json:"nextPageToken"`
		}
		if err := g.call("GET", "firebase", "/v1beta1/projects?"+query.Encode(), "", nil, &result); err != nil {
			return nil, err
		}
		for _, raw := range result.Results {
			if raw.ProjectID == "" || (raw.State != "" && raw.State != "ACTIVE") {
				continue
			}
			projects = append(projects, firebaseProject{ProjectID: raw.ProjectID, DisplayName: raw.DisplayName})
		}
		pageToken = result.NextPageToken
		if pageToken == "" {
			break
		}
	}

	return projects, nil
}

func (g *googleCloud) missingPermissions(projectID string) ([]string, error) {
	var result struct {
		Permissions []string `json:"permissions"`
	}
	path := "/v1/projects/" + url.PathEscape(projectID) + ":testIamPermissions"
	if err := g.call("POST", "crm", path, projectID, map[string]any{"permissions": fcmProvisionPermissions}, &result); err != nil {
		return nil, err
	}
	var missing []string
	for _, permission := range fcmProvisionPermissions {
		if !contains(result.Permissions, permission) {
			missing = append(missing, permission)
		}
	}

	return missing, nil
}

func (g *googleCloud) enableServices(projectID string) error {
	var operation struct {
		Name string `json:"name"`
		Done bool   `json:"done"`
	}
	path := "/v1/projects/" + url.PathEscape(projectID) + "/services:batchEnable"
	if err := g.call("POST", "serviceusage", path, projectID, map[string]any{"serviceIds": fcmProvisionServices}, &operation); err != nil {
		return err
	}
	for attempt := 0; !operation.Done && operation.Name != "" && attempt < 20; attempt++ {
		time.Sleep(g.wait)
		if err := g.call("GET", "serviceusage", "/v1/"+operation.Name, projectID, nil, &operation); err != nil {
			return err
		}
	}

	return nil
}

type mintedKey struct {
	account map[string]any
	name    string
	email   string
}

func (g *googleCloud) mintServiceAccountKey(projectID, displayName string) (*mintedKey, error) {
	var created struct {
		Email string `json:"email"`
	}
	suffix := make([]byte, 8)
	if _, err := rand.Read(suffix); err != nil {
		return nil, err
	}
	const alphabet = "abcdefghijklmnopqrstuvwxyz0123456789"
	for index, value := range suffix {
		suffix[index] = alphabet[int(value)%len(alphabet)]
	}
	body := map[string]any{
		"accountId":      "appwrite-fcm-" + string(suffix),
		"serviceAccount": map[string]any{"displayName": displayName},
	}
	if err := g.call("POST", "iam", "/v1/projects/"+url.PathEscape(projectID)+"/serviceAccounts", projectID, body, &created); err != nil {
		return nil, err
	}
	if created.Email == "" {
		return nil, errors.New("Google did not return a service account")
	}

	if err := g.grantSenderRole(projectID, created.Email); err != nil {
		return nil, err
	}

	var key struct {
		Name           string `json:"name"`
		PrivateKeyData string `json:"privateKeyData"`
	}
	keyBody := map[string]any{"privateKeyType": "TYPE_GOOGLE_CREDENTIALS_FILE", "keyAlgorithm": "KEY_ALG_RSA_2048"}
	keyPath := "/v1/projects/" + url.PathEscape(projectID) + "/serviceAccounts/" + url.PathEscape(created.Email) + "/keys"
	if err := g.call("POST", "iam", keyPath, projectID, keyBody, &key); err != nil {
		return nil, err
	}
	decoded, err := base64.StdEncoding.DecodeString(key.PrivateKeyData)
	if err != nil || key.Name == "" {
		return nil, errors.New("Google did not return a readable service account key")
	}
	account, err := parseServiceAccount(decoded, "the created key", projectID)
	if err != nil {
		return nil, err
	}

	return &mintedKey{account: account, name: key.Name, email: created.Email}, nil
}

func (g *googleCloud) grantSenderRole(projectID, email string) error {
	member := "serviceAccount:" + email
	base := "/v1/projects/" + url.PathEscape(projectID)
	var err error
	for attempt := 0; attempt < googleRolePropagationTry; attempt++ {
		if attempt > 0 {
			time.Sleep(g.wait)
		}
		var policy map[string]any
		if err = g.call("POST", "crm", base+":getIamPolicy", projectID, map[string]any{"options": map[string]any{"requestedPolicyVersion": 3}}, &policy); err != nil {
			return err
		}
		policy = withRoleMember(policy, fcmSenderRole, member)
		err = g.call("POST", "crm", base+":setIamPolicy", projectID, map[string]any{"policy": policy}, nil)
		if err == nil || !isGoogleNotFoundYet(err) {
			return err
		}
	}

	return err
}

func withRoleMember(policy map[string]any, role, member string) map[string]any {
	if policy == nil {
		policy = map[string]any{}
	}
	bindings, _ := policy["bindings"].([]any)
	for _, raw := range bindings {
		binding, _ := raw.(map[string]any)
		if binding == nil || binding["role"] != role || binding["condition"] != nil {
			continue
		}
		members, _ := binding["members"].([]any)
		for _, existing := range members {
			if existing == member {
				return policy
			}
		}
		binding["members"] = append(members, member)

		return policy
	}
	policy["bindings"] = append(bindings, map[string]any{"role": role, "members": []any{member}})

	return policy
}

func (g *googleCloud) deleteKey(name string) error {
	return g.call("DELETE", "iam", "/v1/"+name, "", nil, nil)
}

type googleIdentity struct {
	Provider    string `json:"provider"`
	Email       string `json:"providerEmail"`
	AccessToken string `json:"providerAccessToken"`
	Expiry      string `json:"providerAccessTokenExpiry"`
}

func (i googleIdentity) usable(now time.Time) bool {
	if i.AccessToken == "" {
		return false
	}
	expiry, err := time.Parse(time.RFC3339Nano, i.Expiry)

	return err == nil && expiry.After(now.Add(time.Minute))
}

func (s *pushSetup) googleIdentity() (googleIdentity, error) {
	var list struct {
		Identities []googleIdentity `json:"identities"`
	}
	query := url.Values{"queries[]": []string{`{"method":"equal","attribute":"provider","values":["google"]}`}}
	if err := s.console.Call("GET", "/account/identities?"+query.Encode(), nil, &list); err != nil {
		return googleIdentity{}, err
	}
	if len(list.Identities) == 0 {
		return googleIdentity{}, nil
	}

	return list.Identities[0], nil
}

func consoleURL(endpoint string) string {
	return strings.TrimSuffix(strings.TrimSuffix(endpoint, "/"), "/v1") + "/console"
}

func (s *pushSetup) googleAccessToken(reauthorize bool) (string, error) {
	current, err := s.googleIdentity()
	if err != nil {
		return "", fmt.Errorf("could not read your Appwrite account's linked identities: %w", err)
	}
	if !reauthorize && current.usable(time.Now()) {
		output.Log(s.out, "Using the Google account %s linked to your Appwrite account.", current.Email)

		return current.AccessToken, nil
	}

	var account struct {
		Email string `json:"email"`
	}
	_ = s.console.Call("GET", "/account", nil, &account)

	console := consoleURL(s.console.Endpoint)
	query := url.Values{
		"project":  []string{"console"},
		"success":  []string{console},
		"failure":  []string{console},
		"scopes[]": []string{googleCloudScope},
	}
	signIn := strings.TrimSuffix(s.console.Endpoint, "/") + "/account/tokens/oauth2/google?" + query.Encode()

	output.Log(s.out, "Opening Google sign-in. Allow access to Google Cloud so Appwrite can create the key.")
	if account.Email != "" {
		output.Log(s.out, "The browser must be signed in to the Appwrite console as %s.", account.Email)
	}
	output.Log(s.out, "If the browser does not open, visit %s", signIn)
	s.open(signIn)
	output.Log(s.out, "Waiting for Google sign-in (Ctrl-C to cancel) ...")

	deadline := time.Now().Add(s.googleTimeout)
	for time.Now().Before(deadline) {
		time.Sleep(s.googlePoll)
		identity, err := s.googleIdentity()
		if err != nil {
			continue
		}
		if identity.usable(time.Now()) && identity.AccessToken != current.AccessToken {
			output.Log(s.out, "Signed in to Google as %s.", identity.Email)

			return identity.AccessToken, nil
		}
	}

	hint := "Google sign-in did not reach your Appwrite account"
	if account.Email != "" {
		hint += fmt.Sprintf(". Make sure the browser is signed in to the Appwrite console as %s and try again", account.Email)
	}

	return "", errors.New(hint)
}

func (s *pushSetup) provisionFCM(projectID string, before func(string) error) (map[string]any, func(), error) {
	token, err := s.googleAccessToken(false)
	if err != nil {
		return nil, nil, err
	}
	google := s.newGoogleCloud(token)

	projects, err := google.firebaseProjects()
	if isGoogleUnauthorized(err) || isGoogleScopeError(err) {
		if token, err = s.googleAccessToken(true); err != nil {
			return nil, nil, err
		}
		google = s.newGoogleCloud(token)
		projects, err = google.firebaseProjects()
	}
	if err != nil {
		return nil, nil, describeGoogleFailure("list your Firebase projects", err)
	}
	if len(projects) == 0 {
		return nil, nil, fmt.Errorf("this Google account has no Firebase projects. Create one at %s first", fcmConsoleHome)
	}

	project, err := s.pickGoogleProject(projects, projectID)
	if err != nil {
		return nil, nil, err
	}
	if before != nil {
		if err := before(project.ProjectID); err != nil {
			return nil, nil, err
		}
	}

	missing, err := google.missingPermissions(project.ProjectID)
	if err != nil {
		return nil, nil, describeGoogleFailure("check your permissions on "+project.ProjectID, err)
	}
	if len(missing) > 0 {
		return nil, nil, fmt.Errorf("your Google account is missing %s on %s. Ask a project owner, or provide a key yourself",
			strings.Join(missing, ", "), project.ProjectID)
	}

	output.Log(s.out, "Creating a service account that can send messages in %s ...", project.ProjectID)
	_ = google.enableServices(project.ProjectID)

	name := "Appwrite FCM"
	if label := strings.TrimSpace(project.DisplayName); label != "" {
		name += ": " + label
	}
	if len(name) > 100 {
		name = strings.TrimSpace(name[:100])
	}
	minted, err := google.mintServiceAccountKey(project.ProjectID, name)
	if err != nil {
		return nil, nil, describeGoogleFailure("create the service account key", err)
	}
	output.Success(s.out, "Created service account %s and a key for it.", minted.email)

	revoke := func() {
		if err := google.deleteKey(minted.name); err != nil {
			output.Warn(s.out, "Could not revoke the unused key for %s: %s", minted.email, err)
		}
	}

	return minted.account, revoke, nil
}

func (s *pushSetup) pickGoogleProject(projects []firebaseProject, projectID string) (firebaseProject, error) {
	if projectID != "" {
		for _, project := range projects {
			if project.ProjectID == projectID {
				return project, nil
			}
		}

		return firebaseProject{}, fmt.Errorf("this Google account cannot access Firebase project %s", projectID)
	}
	options := make([]prompt.Option, 0, len(projects))
	for _, project := range projects {
		label := project.ProjectID
		if project.DisplayName != "" && project.DisplayName != project.ProjectID {
			label = project.DisplayName + " (" + project.ProjectID + ")"
		}
		options = append(options, prompt.Option{Label: label, Value: project.ProjectID})
	}
	chosen, err := s.prompter.Choice(prompt.Choice{
		Message: "Which Firebase project should send push notifications?",
		Options: options,
		Default: projects[0].ProjectID,
		Filter:  len(options) > 8,
		Flag:    "--project-id",
	})
	if err != nil {
		return firebaseProject{}, err
	}
	for _, project := range projects {
		if project.ProjectID == chosen {
			return project, nil
		}
	}

	return firebaseProject{}, fmt.Errorf("unknown Firebase project %s", chosen)
}

func describeGoogleFailure(action string, err error) error {
	switch {
	case isGoogleScopeError(err):
		return fmt.Errorf("could not %s: Google sign-in did not grant access to Google Cloud. Sign in again and allow it", action)
	case isGoogleKeyCreationBlocked(err):
		return fmt.Errorf("could not %s: an organization policy blocks service account key creation in this project", action)
	case isGoogleServiceDisabled(err):
		var apiError *googleAPIError
		errors.As(err, &apiError)
		message := fmt.Sprintf("could not %s: a Google API this needs is disabled", action)
		if apiError.HelpURL != "" {
			message += ". Enable it at " + apiError.HelpURL
		}

		return errors.New(message)
	}

	return fmt.Errorf("could not %s: %w", action, err)
}

func (s *pushSetup) newGoogleCloud(token string) *googleCloud {
	hosts := s.googleHosts
	if hosts == nil {
		hosts = defaultGoogleCloudHosts
	}

	return &googleCloud{token: token, http: &http.Client{Timeout: 60 * time.Second}, hosts: hosts, wait: s.googlePoll}
}

func newConsoleForPush() *client.Client {
	api, _, err := consoleClient()
	if err != nil {
		return nil
	}

	return api
}
