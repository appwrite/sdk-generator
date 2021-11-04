package appwrite

import (
	"strings"
)

// Teams service
type Teams struct {
	client Client
}

func NewTeams(clt Client) Teams {  
    service := Teams{
		client: clt,
	}

    return service
}

// List get a list of all the current user teams. You can use the query params
// to filter your results. On admin mode, this endpoint will return a list of
// all of the project's teams. [Learn more about different API
// modes](/docs/admin).
func (srv *Teams) List(Search string, Limit int, Offset int, OrderType string) (map[string]interface{}, error) {
	path := "/teams"

	params := map[string]interface{}{
		"search": Search,
		"limit": Limit,
		"offset": Offset,
		"orderType": OrderType,
	}

	return srv.client.Call("GET", path, nil, params)
}

// Create create a new team. The user who creates the team will automatically
// be assigned as the owner of the team. The team owner can invite new
// members, who will be able add new owners and update or delete the team from
// your project.
func (srv *Teams) Create(Name string, Roles []interface{}) (map[string]interface{}, error) {
	path := "/teams"

	params := map[string]interface{}{
		"name": Name,
		"roles": Roles,
	}

	return srv.client.Call("POST", path, nil, params)
}

// Get get a team by its unique ID. All team members have read access for this
// resource.
func (srv *Teams) Get(TeamId string) (map[string]interface{}, error) {
	r := strings.NewReplacer("{teamId}", TeamId)
	path := r.Replace("/teams/{teamId}")

	params := map[string]interface{}{
	}

	return srv.client.Call("GET", path, nil, params)
}

// Update update a team by its unique ID. Only team owners have write access
// for this resource.
func (srv *Teams) Update(TeamId string, Name string) (map[string]interface{}, error) {
	r := strings.NewReplacer("{teamId}", TeamId)
	path := r.Replace("/teams/{teamId}")

	params := map[string]interface{}{
		"name": Name,
	}

	return srv.client.Call("PUT", path, nil, params)
}

// Delete delete a team by its unique ID. Only team owners have write access
// for this resource.
func (srv *Teams) Delete(TeamId string) (map[string]interface{}, error) {
	r := strings.NewReplacer("{teamId}", TeamId)
	path := r.Replace("/teams/{teamId}")

	params := map[string]interface{}{
	}

	return srv.client.Call("DELETE", path, nil, params)
}

// GetMemberships get a team members by the team unique ID. All team members
// have read access for this list of resources.
func (srv *Teams) GetMemberships(TeamId string, Search string, Limit int, Offset int, OrderType string) (map[string]interface{}, error) {
	r := strings.NewReplacer("{teamId}", TeamId)
	path := r.Replace("/teams/{teamId}/memberships")

	params := map[string]interface{}{
		"search": Search,
		"limit": Limit,
		"offset": Offset,
		"orderType": OrderType,
	}

	return srv.client.Call("GET", path, nil, params)
}

// CreateMembership use this endpoint to invite a new member to join your
// team. If initiated from Client SDK, an email with a link to join the team
// will be sent to the new member's email address if the member doesn't exist
// in the project it will be created automatically. If initiated from server
// side SDKs, new member will automatically be added to the team.
// 
// Use the 'URL' parameter to redirect the user from the invitation email back
// to your app. When the user is redirected, use the [Update Team Membership
// Status](/docs/client/teams#teamsUpdateMembershipStatus) endpoint to allow
// the user to accept the invitation to the team.  While calling from side
// SDKs the redirect url can be empty string.
// 
// Please note that in order to avoid a [Redirect
// Attacks](https://github.com/OWASP/CheatSheetSeries/blob/master/cheatsheets/Unvalidated_Redirects_and_Forwards_Cheat_Sheet.md)
// the only valid redirect URL's are the once from domains you have set when
// added your platforms in the console interface.
func (srv *Teams) CreateMembership(TeamId string, Email string, Roles []interface{}, Url string, Name string) (map[string]interface{}, error) {
	r := strings.NewReplacer("{teamId}", TeamId)
	path := r.Replace("/teams/{teamId}/memberships")

	params := map[string]interface{}{
		"email": Email,
		"roles": Roles,
		"url": Url,
		"name": Name,
	}

	return srv.client.Call("POST", path, nil, params)
}

// UpdateMembershipRoles
func (srv *Teams) UpdateMembershipRoles(TeamId string, MembershipId string, Roles []interface{}) (map[string]interface{}, error) {
	r := strings.NewReplacer("{teamId}", TeamId, "{membershipId}", MembershipId)
	path := r.Replace("/teams/{teamId}/memberships/{membershipId}")

	params := map[string]interface{}{
		"roles": Roles,
	}

	return srv.client.Call("PATCH", path, nil, params)
}

// DeleteMembership this endpoint allows a user to leave a team or for a team
// owner to delete the membership of any other team member. You can also use
// this endpoint to delete a user membership even if it is not accepted.
func (srv *Teams) DeleteMembership(TeamId string, MembershipId string) (map[string]interface{}, error) {
	r := strings.NewReplacer("{teamId}", TeamId, "{membershipId}", MembershipId)
	path := r.Replace("/teams/{teamId}/memberships/{membershipId}")

	params := map[string]interface{}{
	}

	return srv.client.Call("DELETE", path, nil, params)
}

// UpdateMembershipStatus use this endpoint to allow a user to accept an
// invitation to join a team after being redirected back to your app from the
// invitation email recieved by the user.
func (srv *Teams) UpdateMembershipStatus(TeamId string, MembershipId string, UserId string, Secret string) (map[string]interface{}, error) {
	r := strings.NewReplacer("{teamId}", TeamId, "{membershipId}", MembershipId)
	path := r.Replace("/teams/{teamId}/memberships/{membershipId}/status")

	params := map[string]interface{}{
		"userId": UserId,
		"secret": Secret,
	}

	return srv.client.Call("PATCH", path, nil, params)
}
