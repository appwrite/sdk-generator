package appwrite

import (
	"strings"
)

// Avatars service
type Avatars struct {
	client Client
}

func NewAvatars(clt Client) Avatars {  
    service := Avatars{
		client: clt,
	}

    return service
}

// GetBrowser you can use this endpoint to show different browser icons to
// your users. The code argument receives the browser code as it appears in
// your user /account/sessions endpoint. Use width, height and quality
// arguments to change the output settings.
func (srv *Avatars) GetBrowser(Code string, Width int, Height int, Quality int) (map[string]interface{}, error) {
	r := strings.NewReplacer("{code}", Code)
	path := r.Replace("/avatars/browsers/{code}")

	params := map[string]interface{}{
		"width": Width,
		"height": Height,
		"quality": Quality,
	}

	return srv.client.Call("GET", path, nil, params)
}

// GetCreditCard the credit card endpoint will return you the icon of the
// credit card provider you need. Use width, height and quality arguments to
// change the output settings.
func (srv *Avatars) GetCreditCard(Code string, Width int, Height int, Quality int) (map[string]interface{}, error) {
	r := strings.NewReplacer("{code}", Code)
	path := r.Replace("/avatars/credit-cards/{code}")

	params := map[string]interface{}{
		"width": Width,
		"height": Height,
		"quality": Quality,
	}

	return srv.client.Call("GET", path, nil, params)
}

// GetFavicon use this endpoint to fetch the favorite icon (AKA favicon) of
// any remote website URL.
// 
func (srv *Avatars) GetFavicon(Url string) (map[string]interface{}, error) {
	path := "/avatars/favicon"

	params := map[string]interface{}{
		"url": Url,
	}

	return srv.client.Call("GET", path, nil, params)
}

// GetFlag you can use this endpoint to show different country flags icons to
// your users. The code argument receives the 2 letter country code. Use
// width, height and quality arguments to change the output settings.
func (srv *Avatars) GetFlag(Code string, Width int, Height int, Quality int) (map[string]interface{}, error) {
	r := strings.NewReplacer("{code}", Code)
	path := r.Replace("/avatars/flags/{code}")

	params := map[string]interface{}{
		"width": Width,
		"height": Height,
		"quality": Quality,
	}

	return srv.client.Call("GET", path, nil, params)
}

// GetImage use this endpoint to fetch a remote image URL and crop it to any
// image size you want. This endpoint is very useful if you need to crop and
// display remote images in your app or in case you want to make sure a 3rd
// party image is properly served using a TLS protocol.
func (srv *Avatars) GetImage(Url string, Width int, Height int) (map[string]interface{}, error) {
	path := "/avatars/image"

	params := map[string]interface{}{
		"url": Url,
		"width": Width,
		"height": Height,
	}

	return srv.client.Call("GET", path, nil, params)
}

// GetInitials use this endpoint to show your user initials avatar icon on
// your website or app. By default, this route will try to print your
// logged-in user name or email initials. You can also overwrite the user name
// if you pass the 'name' parameter. If no name is given and no user is
// logged, an empty avatar will be returned.
// 
// You can use the color and background params to change the avatar colors. By
// default, a random theme will be selected. The random theme will persist for
// the user's initials when reloading the same theme will always return for
// the same initials.
func (srv *Avatars) GetInitials(Name string, Width int, Height int, Color string, Background string) (map[string]interface{}, error) {
	path := "/avatars/initials"

	params := map[string]interface{}{
		"name": Name,
		"width": Width,
		"height": Height,
		"color": Color,
		"background": Background,
	}

	return srv.client.Call("GET", path, nil, params)
}

// GetQR converts a given plain text to a QR code image. You can use the query
// parameters to change the size and style of the resulting image.
func (srv *Avatars) GetQR(Text string, Size int, Margin int, Download bool) (map[string]interface{}, error) {
	path := "/avatars/qr"

	params := map[string]interface{}{
		"text": Text,
		"size": Size,
		"margin": Margin,
		"download": Download,
	}

	return srv.client.Call("GET", path, nil, params)
}
