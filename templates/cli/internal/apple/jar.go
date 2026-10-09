package apple

import (
	"encoding/json"
	"net/http"
	"net/http/cookiejar"
	"net/url"
	"os"
	"path/filepath"
	"sync"
	"time"
)

// savedJar is a cookie jar that can be written to disk, so a signed-in and
// trusted Apple session is reused and the next run skips two-factor.
// net/http/cookiejar cannot list its cookies, so each cookie is also kept with
// the URL it came from and replayed into a fresh jar when loaded.
type savedJar struct {
	mu      sync.Mutex
	inner   *cookiejar.Jar
	cookies map[string]savedCookie
}

type savedCookie struct {
	URL      string    `json:"url"`
	Name     string    `json:"name"`
	Value    string    `json:"value"`
	Quoted   bool      `json:"quoted,omitempty"`
	Domain   string    `json:"domain,omitempty"`
	Path     string    `json:"path,omitempty"`
	Expires  time.Time `json:"expires,omitzero"`
	Secure   bool      `json:"secure,omitempty"`
	HTTPOnly bool      `json:"httpOnly,omitempty"`
}

func newSavedJar() *savedJar {
	inner, _ := cookiejar.New(nil)

	return &savedJar{inner: inner, cookies: map[string]savedCookie{}}
}

func (j *savedJar) SetCookies(u *url.URL, cookies []*http.Cookie) {
	j.mu.Lock()
	defer j.mu.Unlock()
	j.inner.SetCookies(u, cookies)
	origin := (&url.URL{Scheme: u.Scheme, Host: u.Host, Path: "/"}).String()
	for _, cookie := range cookies {
		domain := cookie.Domain
		if domain == "" {
			domain = u.Hostname()
		}
		id := domain + "|" + cookie.Path + "|" + cookie.Name
		expires := cookie.Expires
		if cookie.MaxAge > 0 {
			expires = time.Now().Add(time.Duration(cookie.MaxAge) * time.Second)
		}
		if cookie.MaxAge < 0 || (!expires.IsZero() && expires.Before(time.Now())) {
			delete(j.cookies, id)

			continue
		}
		j.cookies[id] = savedCookie{
			URL: origin, Name: cookie.Name, Value: cookie.Value, Quoted: cookie.Quoted,
			Domain: cookie.Domain, Path: cookie.Path, Expires: expires,
			Secure: cookie.Secure, HTTPOnly: cookie.HttpOnly,
		}
	}
}

func (j *savedJar) Cookies(u *url.URL) []*http.Cookie {
	return j.inner.Cookies(u)
}

// load replays the cookies saved at path. A missing or unreadable file leaves
// the jar empty: the caller then signs in again.
func (j *savedJar) load(path string) {
	contents, err := os.ReadFile(path)
	if err != nil {
		return
	}
	var saved []savedCookie
	if json.Unmarshal(contents, &saved) != nil {
		return
	}
	for _, cookie := range saved {
		origin, err := url.Parse(cookie.URL)
		if err != nil || (!cookie.Expires.IsZero() && cookie.Expires.Before(time.Now())) {
			continue
		}
		j.SetCookies(origin, []*http.Cookie{{
			Name: cookie.Name, Value: cookie.Value, Quoted: cookie.Quoted,
			Domain: cookie.Domain, Path: cookie.Path, Expires: cookie.Expires,
			Secure: cookie.Secure, HttpOnly: cookie.HTTPOnly,
		}})
	}
}

// save writes the cookies to path, readable by the user only.
func (j *savedJar) save(path string) error {
	j.mu.Lock()
	saved := make([]savedCookie, 0, len(j.cookies))
	for _, cookie := range j.cookies {
		saved = append(saved, cookie)
	}
	j.mu.Unlock()

	contents, err := json.Marshal(saved)
	if err != nil {
		return err
	}
	if err := os.MkdirAll(filepath.Dir(path), 0o700); err != nil {
		return err
	}
	temporary, err := os.CreateTemp(filepath.Dir(path), ".session-*")
	if err != nil {
		return err
	}
	defer os.Remove(temporary.Name())
	if _, err := temporary.Write(contents); err != nil {
		temporary.Close()

		return err
	}
	if err := temporary.Close(); err != nil {
		return err
	}

	return os.Rename(temporary.Name(), path)
}
