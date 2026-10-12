package appwrite

import (
	"context"
	"encoding/json"
	"errors"
	"fmt"
	"net/http"
	"net/url"
	"strconv"
	"strings"
)
{% verbatim %}
// App IDs live behind the portal's App Store Connect style API under
// services-account/v1, which takes the same cookie session as the key calls.
// With a web session, as fastlane's spaceship uses it, reads are POSTs that
// carry X-HTTP-Method-Override: GET and the query in the body, and every
// write carries the team in its attributes.

const (
	pushCapability = "PUSH_NOTIFICATIONS"
	appIDsPageSize = 200
	// maxAppIDPages bounds the lookup should the portal keep handing out
	// next pages.
	maxAppIDPages = 100
)

// AppID is a registered App ID and whether it can receive pushes.
type AppID struct {
	ID         string
	Identifier string
	SeedID     string
	Push       bool
}

type jsonAPIResource struct {
	Type          string          `json:"type"`
	ID            string          `json:"id"`
	Attributes    json.RawMessage `json:"attributes"`
	Relationships map[string]struct {
		Data json.RawMessage `json:"data"`
	} `json:"relationships"`
}

type jsonAPIDocument struct {
	Data     json.RawMessage   `json:"data"`
	Included []jsonAPIResource `json:"included"`
	// Links.Next is the next page of a list, as a URL whose query carries
	// the cursor.
	Links struct {
		Next string `json:"next"`
	} `json:"links"`
	Errors []struct {
		Status string `json:"status"`
		Code   string `json:"code"`
		Title  string `json:"title"`
		Detail string `json:"detail"`
	} `json:"errors"`
}

// connectAPI calls services-account/v1/<path>. A GET goes out as a POST with
// the query in the body; other methods send body as JSON:API.
func (p *portalClient) connectAPI(ctx context.Context, method, path, teamID string, query url.Values, body map[string]any) (*jsonAPIDocument, error) {
	target := p.hosts.Portal + "/services-account/v1/" + path
	headers := map[string]string{
		"Accept":           "application/json, text/plain, */*",
		"Content-Type":     "application/vnd.api+json",
		"X-Requested-With": "XMLHttpRequest",
	}
	if p.csrf != "" || p.csrfTS != "" {
		headers["csrf"] = p.csrf
		headers["csrf_ts"] = p.csrfTS
	}
	var payload any
	switch method {
	case http.MethodGet:
		headers["X-HTTP-Method-Override"] = http.MethodGet
		method = http.MethodPost
		payload = map[string]any{"urlEncodedQueryParams": query.Encode(), "teamId": teamID}
	default:
		payload = body
	}

	response, raw, err := p.request(ctx, method, target, payload, headers)
	if err != nil {
		return nil, fmt.Errorf("could not reach the Apple Developer portal: %w", err)
	}
	if token := response.Header.Get("csrf"); token != "" {
		p.csrf, p.csrfTS = token, response.Header.Get("csrf_ts")
	}
	if response.StatusCode == http.StatusUnauthorized {
		return nil, errSessionExpired
	}
	var document jsonAPIDocument
	if len(raw) > 0 {
		if err := json.Unmarshal(raw, &document); err != nil && response.StatusCode < 300 {
			return nil, fmt.Errorf("the Apple Developer portal sent an unreadable response: %w", err)
		}
	}
	if response.StatusCode >= 300 {
		for _, problem := range document.Errors {
			if detail := strings.TrimSpace(problem.Detail); detail != "" {
				return nil, fmt.Errorf("the Apple Developer portal refused the request: %s", detail)
			}
		}

		return nil, fmt.Errorf("the Apple Developer portal returned HTTP %d for %s", response.StatusCode, path)
	}

	return &document, nil
}

// appID looks up identifier among the team's App IDs. It returns nil when
// the team has none with that identifier. The portal's filter also returns
// longer identifiers that start with it, so every page is read until the
// exact one turns up.
func (p *portalClient) appID(ctx context.Context, teamID, identifier string) (*AppID, error) {
	query := url.Values{}
	query.Set("filter[identifier]", identifier)
	query.Set("include", "bundleIdCapabilities")
	query.Set("limit", strconv.Itoa(appIDsPageSize))
	for page := 0; page < maxAppIDPages; page++ {
		document, err := p.connectAPI(ctx, http.MethodGet, "bundleIds", teamID, query, nil)
		if err != nil {
			return nil, err
		}
		if found, err := exactAppID(document, identifier); found != nil || err != nil {
			return found, err
		}
		if document.Links.Next == "" {
			return nil, nil
		}
		next, err := url.Parse(document.Links.Next)
		if err != nil || next.Query().Get("cursor") == "" {
			return nil, fmt.Errorf("the Apple Developer portal sent an unreadable next page link %q", document.Links.Next)
		}
		query.Set("cursor", next.Query().Get("cursor"))
	}

	return nil, fmt.Errorf("could not find %s within %d pages of App IDs", identifier, maxAppIDPages)
}

// exactAppID returns the App ID with exactly identifier on one page of the
// list, or nil.
func exactAppID(document *jsonAPIDocument, identifier string) (*AppID, error) {
	var resources []jsonAPIResource
	if err := json.Unmarshal(document.Data, &resources); err != nil {
		return nil, fmt.Errorf("the Apple Developer portal sent an unreadable App ID list: %w", err)
	}
	for _, resource := range resources {
		var attributes struct {
			Identifier string `json:"identifier"`
			SeedID     string `json:"seedId"`
		}
		if err := json.Unmarshal(resource.Attributes, &attributes); err != nil {
			continue
		}
		// The filter also matches by prefix: only the exact identifier counts.
		if attributes.Identifier != identifier {
			continue
		}

		return &AppID{
			ID:         resource.ID,
			Identifier: attributes.Identifier,
			SeedID:     attributes.SeedID,
			Push:       hasPush(resource, document.Included),
		}, nil
	}

	return nil, nil
}

// hasPush reports whether the App ID has the Push Notifications capability
// among its included capabilities.
func hasPush(appID jsonAPIResource, included []jsonAPIResource) bool {
	var linked []struct {
		ID string `json:"id"`
	}
	if relationship, ok := appID.Relationships["bundleIdCapabilities"]; ok {
		_ = json.Unmarshal(relationship.Data, &linked)
	}
	ids := map[string]bool{}
	for _, capability := range linked {
		ids[capability.ID] = true
	}
	for _, resource := range included {
		if resource.Type != "bundleIdCapabilities" || !ids[resource.ID] {
			continue
		}
		var attributes struct {
			CapabilityType string `json:"capabilityType"`
			Enabled        *bool  `json:"enabled"`
		}
		if err := json.Unmarshal(resource.Attributes, &attributes); err != nil {
			continue
		}
		if attributes.CapabilityType == pushCapability && (attributes.Enabled == nil || *attributes.Enabled) {
			return true
		}
	}

	return false
}

// createAppID registers identifier as an explicit iOS App ID.
func (p *portalClient) createAppID(ctx context.Context, teamID, identifier, name string) (*AppID, error) {
	document, err := p.connectAPI(ctx, http.MethodPost, "bundleIds", teamID, nil, map[string]any{
		"data": map[string]any{
			"type": "bundleIds",
			"attributes": map[string]any{
				"identifier": identifier,
				"name":       name,
				"platform":   "IOS",
				"seedId":     teamID,
				"teamId":     teamID,
			},
		},
	})
	if err != nil {
		return nil, err
	}
	var resource jsonAPIResource
	if err := json.Unmarshal(document.Data, &resource); err != nil || resource.ID == "" {
		return nil, errors.New("the Apple Developer portal registered no App ID")
	}

	return &AppID{ID: resource.ID, Identifier: identifier, SeedID: teamID}, nil
}

// enablePush turns on the Push Notifications capability of an App ID.
func (p *portalClient) enablePush(ctx context.Context, teamID string, appID *AppID) error {
	seedID := appID.SeedID
	if seedID == "" {
		seedID = teamID
	}
	_, err := p.connectAPI(ctx, http.MethodPatch, "bundleIds/"+appID.ID, teamID, nil, map[string]any{
		"data": map[string]any{
			"type": "bundleIds",
			"id":   appID.ID,
			"attributes": map[string]any{
				"permissions": map[string]bool{"edit": true, "delete": true},
				"seedId":      seedID,
				"teamId":      teamID,
			},
			"relationships": map[string]any{
				"bundleIdCapabilities": map[string]any{
					"data": []map[string]any{{
						"type":       "bundleIdCapabilities",
						"attributes": map[string]any{"enabled": true, "settings": []any{}},
						"relationships": map[string]any{
							"capability": map[string]any{
								"data": map[string]string{"type": "capabilities", "id": pushCapability},
							},
						},
					}},
				},
			},
		},
	})
	if err != nil {
		return err
	}
	appID.Push = true

	return nil
}
{% endverbatim %}