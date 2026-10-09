package appwrite

import (
	"bytes"
	"context"
	"encoding/base64"
	"encoding/json"
	"errors"
	"fmt"
	"io"
	"net/http"
	"net/url"
	"os"
	"path/filepath"
	"strconv"
	"strings"
	"time"

	"github.com/{{ sdk.gitUserName }}/{{ sdk.gitRepoName | caseDash }}/internal/apns"
)

// The sign-in follows spaceship (fastlane), which keeps up with Apple's
// changes in readable code: the widget key from the sign out redirect, SRP
// against idmsa.apple.com, hashcash, then two-factor and a trusted session.

// Hosts are the Apple servers the client talks to. Tests point them at a fake.
type Hosts struct {
	Idmsa           string
	AppStoreConnect string
	Portal          string
}

// DefaultHosts are Apple's production servers.
var DefaultHosts = Hosts{
	Idmsa:           "https://idmsa.apple.com",
	AppStoreConnect: "https://appstoreconnect.apple.com",
	Portal:          "https://developer.apple.com",
}

type portalClient struct {
	http   *http.Client
	jar    *savedJar
	hosts  Hosts
	dir    string
	asker  apns.Asker
	log    func(string, ...any)
	random io.Reader
	now    func() time.Time

	widgetKey string
	sessionID string
	scnt      string
	csrf      string
	csrfTS    string
}

type olympusSession struct {
	User struct {
		EmailAddress string `json:"emailAddress"`
	} `json:"user"`
	Provider *struct {
		ProviderID int64  `json:"providerId"`
		Name       string `json:"name"`
	} `json:"provider"`
}

type serviceErrors struct {
	ServiceErrors []struct {
		Code    string `json:"code"`
		Title   string `json:"title"`
		Message string `json:"message"`
	} `json:"serviceErrors"`
}

func (e serviceErrors) message() string {
	for _, failure := range e.ServiceErrors {
		if failure.Message != "" {
			return failure.Message
		}
		if failure.Title != "" {
			return failure.Title
		}
	}

	return ""
}

func (e serviceErrors) has(code string) bool {
	for _, failure := range e.ServiceErrors {
		if failure.Code == code {
			return true
		}
	}

	return false
}

// request sends one request and reads the whole response.
func (p *portalClient) request(ctx context.Context, method, target string, body any, headers map[string]string) (*http.Response, []byte, error) {
	var reader io.Reader
	if body != nil {
		encoded, err := json.Marshal(body)
		if err != nil {
			return nil, nil, err
		}
		reader = bytes.NewReader(encoded)
	}
	request, err := http.NewRequestWithContext(ctx, method, target, reader)
	if err != nil {
		return nil, nil, err
	}
	request.Header.Set("User-Agent", "Appwrite CLI")
	if body != nil {
		request.Header.Set("Content-Type", "application/json")
	}
	for name, value := range headers {
		request.Header.Set(name, value)
	}
	response, err := p.http.Do(request)
	if err != nil {
		return nil, nil, err
	}
	defer response.Body.Close()
	payload, err := io.ReadAll(response.Body)
	if err != nil {
		return nil, nil, err
	}

	return response, payload, nil
}

// idmsaHeaders are sent with every sign-in request. After Apple asks for
// two-factor, they also carry the session it handed out.
func (p *portalClient) idmsaHeaders() map[string]string {
	headers := map[string]string{
		"Accept":             "application/json, text/javascript",
		"X-Requested-With":   "XMLHttpRequest",
		"X-Apple-Widget-Key": p.widgetKey,
	}
	if p.sessionID != "" {
		headers["Accept"] = "application/json"
		headers["X-Apple-Id-Session-Id"] = p.sessionID
		headers["scnt"] = p.scnt
	}

	return headers
}

// session asks App Store Connect who is signed in. It returns nil when the
// cookies do not hold a usable session.
func (p *portalClient) session(ctx context.Context) (*olympusSession, error) {
	response, payload, err := p.request(ctx, http.MethodGet, p.hosts.AppStoreConnect+"/olympus/v1/session", nil,
		map[string]string{"Accept": "application/json"})
	if err != nil {
		return nil, err
	}
	if response.StatusCode == http.StatusUnauthorized || response.StatusCode == http.StatusForbidden {
		return nil, nil
	}
	var session olympusSession
	if response.StatusCode != http.StatusOK || json.Unmarshal(payload, &session) != nil || session.Provider == nil {
		return nil, nil
	}

	return &session, nil
}

// loadWidgetKey reads the key App Store Connect's web app signs in with from
// the sign out redirect, then from the copy saved by an earlier run, then from
// the endpoint Apple used to document (removed in September 2026).
func (p *portalClient) loadWidgetKey(ctx context.Context) error {
	// Without a session folder there is nowhere to keep a copy.
	cache := ""
	if p.dir != "" {
		cache = filepath.Join(p.dir, "widget-key")
	}
	if key := p.widgetKeyFromSignOut(ctx); key != "" {
		p.widgetKey = key
		p.cacheWidgetKey(cache)

		return nil
	}
	if cached, err := os.ReadFile(cache); cache != "" && err == nil && len(bytes.TrimSpace(cached)) > 0 {
		p.widgetKey = string(bytes.TrimSpace(cached))

		return nil
	}

	response, payload, err := p.request(ctx, http.MethodGet,
		p.hosts.AppStoreConnect+"/olympus/v1/app/config?hostname=itunesconnect.apple.com", nil,
		map[string]string{"Accept": "application/json"})
	if err != nil {
		return fmt.Errorf("could not reach App Store Connect: %w", err)
	}
	var config struct {
		AuthServiceKey string `json:"authServiceKey"`
	}
	if response.StatusCode != http.StatusOK || json.Unmarshal(payload, &config) != nil || config.AuthServiceKey == "" {
		return fmt.Errorf("could not read App Store Connect's sign-in key (HTTP %d)", response.StatusCode)
	}
	p.widgetKey = config.AuthServiceKey
	p.cacheWidgetKey(cache)

	return nil
}

func (p *portalClient) cacheWidgetKey(cache string) {
	if cache != "" {
		_ = os.WriteFile(cache, []byte(p.widgetKey), 0o600)
	}
}

// widgetKeyFromSignOut must neither send the session cookies nor follow the
// redirect: following it signs the session out.
func (p *portalClient) widgetKeyFromSignOut(ctx context.Context) string {
	bare := &http.Client{
		Transport:     p.http.Transport,
		Timeout:       p.http.Timeout,
		CheckRedirect: func(*http.Request, []*http.Request) error { return http.ErrUseLastResponse },
	}
	request, err := http.NewRequestWithContext(ctx, http.MethodHead, p.hosts.AppStoreConnect+"/logout", nil)
	if err != nil {
		return ""
	}
	request.Header.Set("User-Agent", "Appwrite CLI")
	response, err := bare.Do(request)
	if err != nil {
		return ""
	}
	response.Body.Close()
	location, err := url.Parse(response.Header.Get("Location"))
	if err != nil {
		return ""
	}

	return location.Query().Get("widgetKey")
}

// signIn runs the SRP sign-in and, when Apple asks for it, two-factor.
func (p *portalClient) signIn(ctx context.Context, accountName, password string) error {
	client, err := newSRPClient(p.random)
	if err != nil {
		return err
	}
	response, payload, err := p.request(ctx, http.MethodPost, p.hosts.Idmsa+"/appleauth/auth/signin/init", map[string]any{
		"a":           base64.StdEncoding.EncodeToString(client.A),
		"accountName": accountName,
		"protocols":   []string{"s2k", "s2k_fo"},
	}, p.idmsaHeaders())
	if err != nil {
		return fmt.Errorf("could not reach Apple: %w", err)
	}
	var challenge struct {
		serviceErrors
		Iteration int    `json:"iteration"`
		Salt      string `json:"salt"`
		Protocol  string `json:"protocol"`
		B         string `json:"b"`
		C         string `json:"c"`
	}
	if err := json.Unmarshal(payload, &challenge); err != nil || response.StatusCode != http.StatusOK || challenge.B == "" {
		if message := challenge.message(); message != "" {
			return fmt.Errorf("Apple refused the sign-in: %s", message)
		}

		return fmt.Errorf("Apple refused the sign-in (HTTP %d)", response.StatusCode)
	}
	salt, saltErr := base64.StdEncoding.DecodeString(challenge.Salt)
	serverB, bErr := base64.StdEncoding.DecodeString(challenge.B)
	if saltErr != nil || bErr != nil {
		return errors.New("Apple sent an unreadable sign-in challenge")
	}
	key, err := passwordKey(password, salt, challenge.Iteration, challenge.Protocol)
	if err != nil {
		return err
	}
	m1, m2, err := client.proofs(accountName, key, salt, serverB)
	if err != nil {
		return err
	}

	headers := p.idmsaHeaders()
	if token := p.hashcash(ctx); token != "" {
		headers["X-Apple-HC"] = token
	}
	response, payload, err = p.request(ctx, http.MethodPost, p.hosts.Idmsa+"/appleauth/auth/signin/complete?isRememberMeEnabled=false", map[string]any{
		"accountName": accountName,
		"c":           challenge.C,
		"m1":          base64.StdEncoding.EncodeToString(m1),
		"m2":          base64.StdEncoding.EncodeToString(m2),
		"rememberMe":  false,
	}, headers)
	if err != nil {
		return fmt.Errorf("could not reach Apple: %w", err)
	}

	switch response.StatusCode {
	case http.StatusOK:
		return nil
	case http.StatusConflict:
		p.sessionID = response.Header.Get("X-Apple-Id-Session-Id")
		p.scnt = response.Header.Get("scnt")

		return p.twoFactor(ctx)
	case http.StatusUnauthorized, http.StatusForbidden:
		return apns.ErrInvalidCredentials
	case http.StatusPreconditionFailed:
		return errors.New("Apple needs you to accept its terms or update your account first. Sign in at https://appleid.apple.com, then try again")
	default:
		var failure serviceErrors
		_ = json.Unmarshal(payload, &failure)
		if message := failure.message(); message != "" {
			return fmt.Errorf("Apple refused the sign-in: %s", message)
		}

		return fmt.Errorf("Apple refused the sign-in (HTTP %d)", response.StatusCode)
	}
}

// hashcash answers the proof-of-work challenge Apple hands out on the sign-in
// page. Without one, Apple decides; spaceship carries on in that case too.
func (p *portalClient) hashcash(ctx context.Context) string {
	response, _, err := p.request(ctx, http.MethodGet,
		p.hosts.Idmsa+"/appleauth/auth/signin?widgetKey="+url.QueryEscape(p.widgetKey), nil, nil)
	if err != nil {
		return ""
	}
	bits, err := strconv.Atoi(response.Header.Get("X-Apple-HC-Bits"))
	challenge := response.Header.Get("X-Apple-HC-Challenge")
	if err != nil || challenge == "" || bits < 0 || bits > 64 {
		return ""
	}

	return hashcash(bits, challenge, p.now())
}

type trustedPhone struct {
	ID                 int64  `json:"id"`
	NumberWithDialCode string `json:"numberWithDialCode"`
	PushMode           string `json:"pushMode"`
}

const codeAttempts = 3

// twoFactor asks for the code Apple sent to the person's devices or phone,
// checks it, and marks the session trusted so later runs skip this step.
func (p *portalClient) twoFactor(ctx context.Context) error {
	response, payload, err := p.request(ctx, http.MethodGet, p.hosts.Idmsa+"/appleauth/auth", nil, p.idmsaHeaders())
	if err != nil {
		return fmt.Errorf("could not reach Apple: %w", err)
	}
	var options struct {
		serviceErrors
		TrustedDevices      []any          `json:"trustedDevices"`
		TrustedPhoneNumbers []trustedPhone `json:"trustedPhoneNumbers"`
		NoTrustedDevices    bool           `json:"noTrustedDevices"`
		SecurityCode        struct {
			Length int `json:"length"`
		} `json:"securityCode"`
	}
	if json.Unmarshal(payload, &options) != nil || response.StatusCode >= 300 {
		return fmt.Errorf("Apple asked for two-factor authentication but did not say how (HTTP %d)", response.StatusCode)
	}
	if len(options.TrustedPhoneNumbers) == 0 {
		if len(options.TrustedDevices) > 0 {
			return errors.New("this Apple ID uses the older two-step verification. Turn on two-factor authentication at https://appleid.apple.com, then try again")
		}
		if message := options.message(); message != "" {
			return fmt.Errorf("Apple could not send a verification code: %s", message)
		}

		return errors.New("Apple asked for two-factor authentication but offered no way to receive a code")
	}
	length := options.SecurityCode.Length
	if length == 0 {
		length = 6
	}

	// A code was pushed to the trusted devices, unless there are none. With no
	// devices and a single phone, Apple has already texted it.
	var phone *trustedPhone
	question := fmt.Sprintf("Enter the %d-digit code shown on your Apple device (or type sms to get a text message)", length)
	if options.NoTrustedDevices {
		if len(options.TrustedPhoneNumbers) == 1 {
			phone = &options.TrustedPhoneNumbers[0]
		} else if phone, err = p.textCode(ctx, options.TrustedPhoneNumbers); err != nil {
			return err
		}
		question = fmt.Sprintf("Enter the %d-digit code sent to %s", length, phone.NumberWithDialCode)
	}

	// Only codes Apple rejects count as attempts; switching to a text message
	// does not.
	wrong := 0
	for {
		code, err := p.asker.Ask(question, false)
		if err != nil {
			return err
		}
		code = strings.TrimSpace(code)
		if phone == nil && strings.EqualFold(code, "sms") {
			if phone, err = p.textCode(ctx, options.TrustedPhoneNumbers); err != nil {
				return err
			}
			question = fmt.Sprintf("Enter the %d-digit code sent to %s", length, phone.NumberWithDialCode)

			continue
		}

		route := "trusteddevice"
		body := map[string]any{"securityCode": map[string]any{"code": code}}
		if phone != nil {
			route = "phone"
			body["phoneNumber"] = map[string]any{"id": phone.ID}
			body["mode"] = phoneMode(phone)
		}
		response, payload, err := p.request(ctx, http.MethodPost,
			p.hosts.Idmsa+"/appleauth/auth/verify/"+route+"/securitycode", body, p.idmsaHeaders())
		if err != nil {
			return fmt.Errorf("could not reach Apple: %w", err)
		}
		if response.StatusCode < 300 {
			break
		}
		var failure serviceErrors
		_ = json.Unmarshal(payload, &failure)
		if failure.has("-21669") || strings.Contains(strings.ToLower(failure.message()), "verification code") {
			wrong++
			if wrong >= codeAttempts {
				return errors.New("the verification code was wrong too many times")
			}
			p.log("That code is not right. Try again.")

			continue
		}
		if message := failure.message(); message != "" {
			return fmt.Errorf("Apple refused the verification code: %s", message)
		}

		return fmt.Errorf("Apple refused the verification code (HTTP %d)", response.StatusCode)
	}

	// Trusting the session is what lets the next run skip two-factor. A
	// failure here still leaves a working session, as spaceship notes.
	_, _, _ = p.request(ctx, http.MethodGet, p.hosts.Idmsa+"/appleauth/auth/2sv/trust", nil, p.idmsaHeaders())

	return nil
}

func phoneMode(phone *trustedPhone) string {
	if phone.PushMode != "" {
		return phone.PushMode
	}

	return "sms"
}

// textCode asks which trusted phone should get the code and has Apple text it.
func (p *portalClient) textCode(ctx context.Context, phones []trustedPhone) (*trustedPhone, error) {
	chosen := 0
	if len(phones) > 1 {
		labels := make([]string, len(phones))
		for index, phone := range phones {
			labels[index] = phone.NumberWithDialCode
		}
		var err error
		if chosen, err = p.asker.Choose("Which phone number should get the code?", labels); err != nil {
			return nil, err
		}
	}
	phone := &phones[chosen]
	response, payload, err := p.request(ctx, http.MethodPut, p.hosts.Idmsa+"/appleauth/auth/verify/phone", map[string]any{
		"phoneNumber": map[string]any{"id": phone.ID},
		"mode":        phoneMode(phone),
	}, p.idmsaHeaders())
	if err != nil {
		return nil, fmt.Errorf("could not reach Apple: %w", err)
	}
	if response.StatusCode >= 300 {
		var failure serviceErrors
		_ = json.Unmarshal(payload, &failure)
		if message := failure.message(); message != "" {
			return nil, fmt.Errorf("Apple could not text the code: %s", message)
		}

		return nil, fmt.Errorf("Apple could not text the code (HTTP %d)", response.StatusCode)
	}
	p.log("Apple sent a code to %s.", phone.NumberWithDialCode)

	return phone, nil
}
