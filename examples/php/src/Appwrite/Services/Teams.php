<?php

namespace Appwrite\Services;

use Appwrite\AppwriteException;
use Appwrite\Client;
use Appwrite\Service;

class Teams extends Service
{
    /**
     * List Teams
     *
     * Get a list of all the current user teams. You can use the query params to
     * filter your results. On admin mode, this endpoint will return a list of all
     * of the project's teams. [Learn more about different API
     * modes](/docs/admin).
     *
     * @param string $search
     * @param int $limit
     * @param int $offset
     * @param string $orderType
     * @throws AppwriteException
     * @return array
     */
    public function list(string $search = null, int $limit = null, int $offset = null, string $orderType = null): array
    {
        $path   = str_replace([], [], '/teams');
        $params = [];

        if (!is_null($search)) {
            $params['search'] = $search;
        }

        if (!is_null($limit)) {
            $params['limit'] = $limit;
        }

        if (!is_null($offset)) {
            $params['offset'] = $offset;
        }

        if (!is_null($orderType)) {
            $params['orderType'] = $orderType;
        }

        return $this->client->call(Client::METHOD_GET, $path, [
            'content-type' => 'application/json',
        ], $params);
    }

    /**
     * Create Team
     *
     * Create a new team. The user who creates the team will automatically be
     * assigned as the owner of the team. The team owner can invite new members,
     * who will be able add new owners and update or delete the team from your
     * project.
     *
     * @param string $name
     * @param array $roles
     * @throws AppwriteException
     * @return array
     */
    public function create(string $name, array $roles = null): array
    {
        if (!isset($name)) {
            throw new AppwriteException('Missing required parameter: "name"');
        }

        $path   = str_replace([], [], '/teams');
        $params = [];

        if (!is_null($name)) {
            $params['name'] = $name;
        }

        if (!is_null($roles)) {
            $params['roles'] = $roles;
        }

        return $this->client->call(Client::METHOD_POST, $path, [
            'content-type' => 'application/json',
        ], $params);
    }

    /**
     * Get Team
     *
     * Get a team by its unique ID. All team members have read access for this
     * resource.
     *
     * @param string $teamId
     * @throws AppwriteException
     * @return array
     */
    public function get(string $teamId): array
    {
        if (!isset($teamId)) {
            throw new AppwriteException('Missing required parameter: "teamId"');
        }

        $path   = str_replace(['{teamId}'], [$teamId], '/teams/{teamId}');
        $params = [];

        return $this->client->call(Client::METHOD_GET, $path, [
            'content-type' => 'application/json',
        ], $params);
    }

    /**
     * Update Team
     *
     * Update a team by its unique ID. Only team owners have write access for this
     * resource.
     *
     * @param string $teamId
     * @param string $name
     * @throws AppwriteException
     * @return array
     */
    public function update(string $teamId, string $name): array
    {
        if (!isset($teamId)) {
            throw new AppwriteException('Missing required parameter: "teamId"');
        }

        if (!isset($name)) {
            throw new AppwriteException('Missing required parameter: "name"');
        }

        $path   = str_replace(['{teamId}'], [$teamId], '/teams/{teamId}');
        $params = [];

        if (!is_null($name)) {
            $params['name'] = $name;
        }

        return $this->client->call(Client::METHOD_PUT, $path, [
            'content-type' => 'application/json',
        ], $params);
    }

    /**
     * Delete Team
     *
     * Delete a team by its unique ID. Only team owners have write access for this
     * resource.
     *
     * @param string $teamId
     * @throws AppwriteException
     * @return array
     */
    public function delete(string $teamId): array
    {
        if (!isset($teamId)) {
            throw new AppwriteException('Missing required parameter: "teamId"');
        }

        $path   = str_replace(['{teamId}'], [$teamId], '/teams/{teamId}');
        $params = [];

        return $this->client->call(Client::METHOD_DELETE, $path, [
            'content-type' => 'application/json',
        ], $params);
    }

    /**
     * Get Team Memberships
     *
     * Get a team members by the team unique ID. All team members have read access
     * for this list of resources.
     *
     * @param string $teamId
     * @param string $search
     * @param int $limit
     * @param int $offset
     * @param string $orderType
     * @throws AppwriteException
     * @return array
     */
    public function getMemberships(string $teamId, string $search = null, int $limit = null, int $offset = null, string $orderType = null): array
    {
        if (!isset($teamId)) {
            throw new AppwriteException('Missing required parameter: "teamId"');
        }

        $path   = str_replace(['{teamId}'], [$teamId], '/teams/{teamId}/memberships');
        $params = [];

        if (!is_null($search)) {
            $params['search'] = $search;
        }

        if (!is_null($limit)) {
            $params['limit'] = $limit;
        }

        if (!is_null($offset)) {
            $params['offset'] = $offset;
        }

        if (!is_null($orderType)) {
            $params['orderType'] = $orderType;
        }

        return $this->client->call(Client::METHOD_GET, $path, [
            'content-type' => 'application/json',
        ], $params);
    }

    /**
     * Create Team Membership
     *
     * Use this endpoint to invite a new member to join your team. If initiated
     * from Client SDK, an email with a link to join the team will be sent to the
     * new member's email address if the member doesn't exist in the project it
     * will be created automatically. If initiated from server side SDKs, new
     * member will automatically be added to the team.
     * 
     * Use the 'URL' parameter to redirect the user from the invitation email back
     * to your app. When the user is redirected, use the [Update Team Membership
     * Status](/docs/client/teams#teamsUpdateMembershipStatus) endpoint to allow
     * the user to accept the invitation to the team.  While calling from side
     * SDKs the redirect url can be empty string.
     * 
     * Please note that in order to avoid a [Redirect
     * Attacks](https://github.com/OWASP/CheatSheetSeries/blob/master/cheatsheets/Unvalidated_Redirects_and_Forwards_Cheat_Sheet.md)
     * the only valid redirect URL's are the once from domains you have set when
     * added your platforms in the console interface.
     *
     * @param string $teamId
     * @param string $email
     * @param array $roles
     * @param string $url
     * @param string $name
     * @throws AppwriteException
     * @return array
     */
    public function createMembership(string $teamId, string $email, array $roles, string $url, string $name = null): array
    {
        if (!isset($teamId)) {
            throw new AppwriteException('Missing required parameter: "teamId"');
        }

        if (!isset($email)) {
            throw new AppwriteException('Missing required parameter: "email"');
        }

        if (!isset($roles)) {
            throw new AppwriteException('Missing required parameter: "roles"');
        }

        if (!isset($url)) {
            throw new AppwriteException('Missing required parameter: "url"');
        }

        $path   = str_replace(['{teamId}'], [$teamId], '/teams/{teamId}/memberships');
        $params = [];

        if (!is_null($email)) {
            $params['email'] = $email;
        }

        if (!is_null($roles)) {
            $params['roles'] = $roles;
        }

        if (!is_null($url)) {
            $params['url'] = $url;
        }

        if (!is_null($name)) {
            $params['name'] = $name;
        }

        return $this->client->call(Client::METHOD_POST, $path, [
            'content-type' => 'application/json',
        ], $params);
    }

    /**
     * Update Membership Roles
     *
     * @param string $teamId
     * @param string $membershipId
     * @param array $roles
     * @throws AppwriteException
     * @return array
     */
    public function updateMembershipRoles(string $teamId, string $membershipId, array $roles): array
    {
        if (!isset($teamId)) {
            throw new AppwriteException('Missing required parameter: "teamId"');
        }

        if (!isset($membershipId)) {
            throw new AppwriteException('Missing required parameter: "membershipId"');
        }

        if (!isset($roles)) {
            throw new AppwriteException('Missing required parameter: "roles"');
        }

        $path   = str_replace(['{teamId}', '{membershipId}'], [$teamId, $membershipId], '/teams/{teamId}/memberships/{membershipId}');
        $params = [];

        if (!is_null($roles)) {
            $params['roles'] = $roles;
        }

        return $this->client->call(Client::METHOD_PATCH, $path, [
            'content-type' => 'application/json',
        ], $params);
    }

    /**
     * Delete Team Membership
     *
     * This endpoint allows a user to leave a team or for a team owner to delete
     * the membership of any other team member. You can also use this endpoint to
     * delete a user membership even if it is not accepted.
     *
     * @param string $teamId
     * @param string $membershipId
     * @throws AppwriteException
     * @return array
     */
    public function deleteMembership(string $teamId, string $membershipId): array
    {
        if (!isset($teamId)) {
            throw new AppwriteException('Missing required parameter: "teamId"');
        }

        if (!isset($membershipId)) {
            throw new AppwriteException('Missing required parameter: "membershipId"');
        }

        $path   = str_replace(['{teamId}', '{membershipId}'], [$teamId, $membershipId], '/teams/{teamId}/memberships/{membershipId}');
        $params = [];

        return $this->client->call(Client::METHOD_DELETE, $path, [
            'content-type' => 'application/json',
        ], $params);
    }

    /**
     * Update Team Membership Status
     *
     * Use this endpoint to allow a user to accept an invitation to join a team
     * after being redirected back to your app from the invitation email recieved
     * by the user.
     *
     * @param string $teamId
     * @param string $membershipId
     * @param string $userId
     * @param string $secret
     * @throws AppwriteException
     * @return array
     */
    public function updateMembershipStatus(string $teamId, string $membershipId, string $userId, string $secret): array
    {
        if (!isset($teamId)) {
            throw new AppwriteException('Missing required parameter: "teamId"');
        }

        if (!isset($membershipId)) {
            throw new AppwriteException('Missing required parameter: "membershipId"');
        }

        if (!isset($userId)) {
            throw new AppwriteException('Missing required parameter: "userId"');
        }

        if (!isset($secret)) {
            throw new AppwriteException('Missing required parameter: "secret"');
        }

        $path   = str_replace(['{teamId}', '{membershipId}'], [$teamId, $membershipId], '/teams/{teamId}/memberships/{membershipId}/status');
        $params = [];

        if (!is_null($userId)) {
            $params['userId'] = $userId;
        }

        if (!is_null($secret)) {
            $params['secret'] = $secret;
        }

        return $this->client->call(Client::METHOD_PATCH, $path, [
            'content-type' => 'application/json',
        ], $params);
    }
}
