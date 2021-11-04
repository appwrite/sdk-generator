
using System.Collections.Generic;
using System.IO;
using System.Net.Http;
using System.Threading.Tasks;

namespace Appwrite
{
    public class Teams : Service
    {
        public Teams(Client client) : base(client) { }

        /// <summary>
        /// List Teams
        /// <para>
        /// Get a list of all the current user teams. You can use the query params to
        /// filter your results. On admin mode, this endpoint will return a list of all
        /// of the project's teams. [Learn more about different API
        /// modes](/docs/admin).
        /// </para>
        /// </summary>
        public async Task<HttpResponseMessage> List(string search = "", int? limit = 25, int? offset = 0, OrderType orderType = OrderType.ASC) 
        {
            string path = "/teams";

            Dictionary<string, object> parameters = new Dictionary<string, object>()
            {
                { "search", search },
                { "limit", limit },
                { "offset", offset },
                { "orderType", orderType.ToString() }
            };

            Dictionary<string, string> headers = new Dictionary<string, string>()
            {
                { "content-type", "application/json" }
            };

            return await _client.Call("GET", path, headers, parameters);
        }

        /// <summary>
        /// Create Team
        /// <para>
        /// Create a new team. The user who creates the team will automatically be
        /// assigned as the owner of the team. The team owner can invite new members,
        /// who will be able add new owners and update or delete the team from your
        /// project.
        /// </para>
        /// </summary>
        public async Task<HttpResponseMessage> Create(string name, List<object> roles = null) 
        {
            string path = "/teams";

            Dictionary<string, object> parameters = new Dictionary<string, object>()
            {
                { "name", name },
                { "roles", roles }
            };

            Dictionary<string, string> headers = new Dictionary<string, string>()
            {
                { "content-type", "application/json" }
            };

            return await _client.Call("POST", path, headers, parameters);
        }

        /// <summary>
        /// Get Team
        /// <para>
        /// Get a team by its unique ID. All team members have read access for this
        /// resource.
        /// </para>
        /// </summary>
        public async Task<HttpResponseMessage> Get(string teamId) 
        {
            string path = "/teams/{teamId}".Replace("{teamId}", teamId);

            Dictionary<string, object> parameters = new Dictionary<string, object>()
            {
            };

            Dictionary<string, string> headers = new Dictionary<string, string>()
            {
                { "content-type", "application/json" }
            };

            return await _client.Call("GET", path, headers, parameters);
        }

        /// <summary>
        /// Update Team
        /// <para>
        /// Update a team by its unique ID. Only team owners have write access for this
        /// resource.
        /// </para>
        /// </summary>
        public async Task<HttpResponseMessage> Update(string teamId, string name) 
        {
            string path = "/teams/{teamId}".Replace("{teamId}", teamId);

            Dictionary<string, object> parameters = new Dictionary<string, object>()
            {
                { "name", name }
            };

            Dictionary<string, string> headers = new Dictionary<string, string>()
            {
                { "content-type", "application/json" }
            };

            return await _client.Call("PUT", path, headers, parameters);
        }

        /// <summary>
        /// Delete Team
        /// <para>
        /// Delete a team by its unique ID. Only team owners have write access for this
        /// resource.
        /// </para>
        /// </summary>
        public async Task<HttpResponseMessage> Delete(string teamId) 
        {
            string path = "/teams/{teamId}".Replace("{teamId}", teamId);

            Dictionary<string, object> parameters = new Dictionary<string, object>()
            {
            };

            Dictionary<string, string> headers = new Dictionary<string, string>()
            {
                { "content-type", "application/json" }
            };

            return await _client.Call("DELETE", path, headers, parameters);
        }

        /// <summary>
        /// Get Team Memberships
        /// <para>
        /// Get a team members by the team unique ID. All team members have read access
        /// for this list of resources.
        /// </para>
        /// </summary>
        public async Task<HttpResponseMessage> GetMemberships(string teamId, string search = "", int? limit = 25, int? offset = 0, OrderType orderType = OrderType.ASC) 
        {
            string path = "/teams/{teamId}/memberships".Replace("{teamId}", teamId);

            Dictionary<string, object> parameters = new Dictionary<string, object>()
            {
                { "search", search },
                { "limit", limit },
                { "offset", offset },
                { "orderType", orderType.ToString() }
            };

            Dictionary<string, string> headers = new Dictionary<string, string>()
            {
                { "content-type", "application/json" }
            };

            return await _client.Call("GET", path, headers, parameters);
        }

        /// <summary>
        /// Create Team Membership
        /// <para>
        /// Use this endpoint to invite a new member to join your team. If initiated
        /// from Client SDK, an email with a link to join the team will be sent to the
        /// new member's email address if the member doesn't exist in the project it
        /// will be created automatically. If initiated from server side SDKs, new
        /// member will automatically be added to the team.
        /// 
        /// Use the 'URL' parameter to redirect the user from the invitation email back
        /// to your app. When the user is redirected, use the [Update Team Membership
        /// Status](/docs/client/teams#teamsUpdateMembershipStatus) endpoint to allow
        /// the user to accept the invitation to the team.  While calling from side
        /// SDKs the redirect url can be empty string.
        /// 
        /// Please note that in order to avoid a [Redirect
        /// Attacks](https://github.com/OWASP/CheatSheetSeries/blob/master/cheatsheets/Unvalidated_Redirects_and_Forwards_Cheat_Sheet.md)
        /// the only valid redirect URL's are the once from domains you have set when
        /// added your platforms in the console interface.
        /// </para>
        /// </summary>
        public async Task<HttpResponseMessage> CreateMembership(string teamId, string email, List<object> roles, string url, string name = "") 
        {
            string path = "/teams/{teamId}/memberships".Replace("{teamId}", teamId);

            Dictionary<string, object> parameters = new Dictionary<string, object>()
            {
                { "email", email },
                { "roles", roles },
                { "url", url },
                { "name", name }
            };

            Dictionary<string, string> headers = new Dictionary<string, string>()
            {
                { "content-type", "application/json" }
            };

            return await _client.Call("POST", path, headers, parameters);
        }

        /// <summary>
        /// Update Membership Roles
        /// </summary>
        public async Task<HttpResponseMessage> UpdateMembershipRoles(string teamId, string membershipId, List<object> roles) 
        {
            string path = "/teams/{teamId}/memberships/{membershipId}".Replace("{teamId}", teamId).Replace("{membershipId}", membershipId);

            Dictionary<string, object> parameters = new Dictionary<string, object>()
            {
                { "roles", roles }
            };

            Dictionary<string, string> headers = new Dictionary<string, string>()
            {
                { "content-type", "application/json" }
            };

            return await _client.Call("PATCH", path, headers, parameters);
        }

        /// <summary>
        /// Delete Team Membership
        /// <para>
        /// This endpoint allows a user to leave a team or for a team owner to delete
        /// the membership of any other team member. You can also use this endpoint to
        /// delete a user membership even if it is not accepted.
        /// </para>
        /// </summary>
        public async Task<HttpResponseMessage> DeleteMembership(string teamId, string membershipId) 
        {
            string path = "/teams/{teamId}/memberships/{membershipId}".Replace("{teamId}", teamId).Replace("{membershipId}", membershipId);

            Dictionary<string, object> parameters = new Dictionary<string, object>()
            {
            };

            Dictionary<string, string> headers = new Dictionary<string, string>()
            {
                { "content-type", "application/json" }
            };

            return await _client.Call("DELETE", path, headers, parameters);
        }

        /// <summary>
        /// Update Team Membership Status
        /// <para>
        /// Use this endpoint to allow a user to accept an invitation to join a team
        /// after being redirected back to your app from the invitation email recieved
        /// by the user.
        /// </para>
        /// </summary>
        public async Task<HttpResponseMessage> UpdateMembershipStatus(string teamId, string membershipId, string userId, string secret) 
        {
            string path = "/teams/{teamId}/memberships/{membershipId}/status".Replace("{teamId}", teamId).Replace("{membershipId}", membershipId);

            Dictionary<string, object> parameters = new Dictionary<string, object>()
            {
                { "userId", userId },
                { "secret", secret }
            };

            Dictionary<string, string> headers = new Dictionary<string, string>()
            {
                { "content-type", "application/json" }
            };

            return await _client.Call("PATCH", path, headers, parameters);
        }
    };
}