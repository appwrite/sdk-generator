package apple

import (
	"context"
	"encoding/json"
	"errors"
	"fmt"
	"net/http"
	"net/url"
	"strings"
)

// The Developer portal's private API, as the portal's own pages and
// @expo/apple-utils use it: cookie authenticated JSON under
// services-account/QH65B2, with the team in every request and CSRF tokens
// that a read hands out and a write sends back.

const (
	apnsServiceID = "U27F4V844T"
	keysPageSize  = 500
)

var (
	errSessionExpired = errors.New("the Apple session expired")
	errMaxKeys        = errors.New("maximum keys")
)

// Team is an Apple Developer team the account belongs to.
type Team struct {
	ID   string `json:"teamId"`
	Name string `json:"name"`
	Type string `json:"type"`
}

type portalResult struct {
	ResultCode   int    `json:"resultCode"`
	ResultString string `json:"resultString"`
	UserString   string `json:"userString"`
}

func (r portalResult) message() string {
	for _, value := range []string{r.UserString, r.ResultString} {
		if decoded, err := url.PathUnescape(value); err == nil && decoded != "" {
			return decoded
		}
		if value != "" {
			return value
		}
	}

	return ""
}

// portal calls one endpoint and returns the raw body after checking the
// result code. GET requests carry teamID in the query, others in the body.
func (p *portalClient) portal(ctx context.Context, method, path, teamID string, values map[string]any) ([]byte, error) {
	target := p.hosts.Portal + "/services-account/QH65B2/" + path
	var body any
	if method == http.MethodGet {
		query := url.Values{}
		if teamID != "" {
			query.Set("teamId", teamID)
		}
		for name, value := range values {
			query.Set(name, fmt.Sprint(value))
		}
		target += "?" + query.Encode()
	} else {
		data := map[string]any{}
		if teamID != "" {
			data["teamId"] = teamID
		}
		for name, value := range values {
			data[name] = value
		}
		body = data
	}
	headers := map[string]string{"Accept": "application/json, text/plain, */*"}
	if p.csrf != "" || p.csrfTS != "" {
		headers["csrf"] = p.csrf
		headers["csrf_ts"] = p.csrfTS
	}

	response, payload, err := p.request(ctx, method, target, body, headers)
	if err != nil {
		return nil, fmt.Errorf("could not reach the Apple Developer portal: %w", err)
	}
	if token := response.Header.Get("csrf"); token != "" {
		p.csrf, p.csrfTS = token, response.Header.Get("csrf_ts")
	}
	if response.StatusCode == http.StatusUnauthorized || response.StatusCode == http.StatusForbidden {
		return nil, errSessionExpired
	}
	if response.StatusCode >= 300 {
		return nil, fmt.Errorf("the Apple Developer portal returned HTTP %d for %s", response.StatusCode, path)
	}

	return payload, nil
}

// result decodes a JSON portal response, failing on a non-zero result code.
func result(payload []byte, out any) error {
	var status portalResult
	if err := json.Unmarshal(payload, &status); err != nil {
		return fmt.Errorf("the Apple Developer portal sent an unreadable response: %w", err)
	}
	if status.ResultCode != 0 {
		message := status.message()
		if strings.Contains(message, "session has expired") {
			return errSessionExpired
		}
		if strings.Contains(message, "maximum allowed number of Keys") {
			return errMaxKeys
		}
		if message == "" {
			message = fmt.Sprintf("result code %d", status.ResultCode)
		}

		return fmt.Errorf("the Apple Developer portal refused the request: %s", message)
	}

	return json.Unmarshal(payload, out)
}

func (p *portalClient) teams(ctx context.Context) ([]Team, error) {
	payload, err := p.portal(ctx, http.MethodPost, "account/listTeams.action", "", nil)
	if err != nil {
		return nil, err
	}
	var response struct {
		Teams []Team `json:"teams"`
	}
	if err := result(payload, &response); err != nil {
		return nil, err
	}

	return response.Teams, nil
}

// keys lists the team's keys. It also hands out the CSRF tokens that creating
// a key needs, so it always runs first.
func (p *portalClient) keys(ctx context.Context, teamID string) ([]ExistingKey, error) {
	var all []ExistingKey
	for page := 1; ; page++ {
		payload, err := p.portal(ctx, http.MethodPost, "account/auth/key/list", teamID, map[string]any{
			"pageNumber": page,
			"pageSize":   keysPageSize,
			"sort":       "name=asc",
		})
		if err != nil {
			return nil, err
		}
		var response struct {
			Keys []ExistingKey `json:"keys"`
		}
		if err := result(payload, &response); err != nil {
			return nil, err
		}
		all = append(all, response.Keys...)
		if len(response.Keys) < keysPageSize {
			return all, nil
		}
	}
}

// createKey creates a team-scoped key with only APNs enabled, for the given
// environment, and returns its ID.
func (p *portalClient) createKey(ctx context.Context, teamID, name string, environment Environment) (string, error) {
	payload, err := p.portal(ctx, http.MethodPost, "account/auth/key/v2/create", teamID, map[string]any{
		"name":                  name,
		"scope":                 "team",
		"serviceConfigurations": map[string][]string{apnsServiceID: {}},
		"serviceConfigurationsRequests": []map[string]any{{
			"isNew":       true,
			"serviceId":   apnsServiceID,
			"identifiers": map[string]any{},
			"environment": string(environment),
			"scope":       "team",
		}},
	})
	if err != nil {
		return "", err
	}
	var response struct {
		Keys []ExistingKey `json:"keys"`
	}
	if err := result(payload, &response); err != nil {
		return "", err
	}
	if len(response.Keys) == 0 || response.Keys[0].ID == "" {
		return "", errors.New("the Apple Developer portal created no key")
	}

	return response.Keys[0].ID, nil
}

// downloadKey returns the key's .p8 contents. Apple serves them once.
func (p *portalClient) downloadKey(ctx context.Context, teamID, keyID string) (string, error) {
	payload, err := p.portal(ctx, http.MethodGet, "account/auth/key/download", teamID, map[string]any{"keyId": keyID})
	if err != nil {
		return "", err
	}
	contents := string(payload)
	if !strings.Contains(contents, "PRIVATE KEY") {
		var ignored struct{}
		if err := result(payload, &ignored); err != nil {
			return "", err
		}

		return "", errors.New("the Apple Developer portal did not send the key")
	}

	return contents, nil
}
