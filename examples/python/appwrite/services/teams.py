from ..service import Service
from ..exception import AppwriteException

class Teams(Service):

    def __init__(self, client):
        super(Teams, self).__init__(client)

    def list(self, search = None, limit = None, offset = None, order_type = None):
        """List Teams"""

        params = {}
        path = '/teams'

        if search is not None: 
            params['search'] = search

        if limit is not None: 
            params['limit'] = limit

        if offset is not None: 
            params['offset'] = offset

        if order_type is not None: 
            params['orderType'] = order_type

        return self.client.call('get', path, {
            'content-type': 'application/json',
        }, params)

    def create(self, name, roles = None):
        """Create Team"""

        if name is None: 
            raise AppwriteException('Missing required parameter: "name"')

        params = {}
        path = '/teams'

        if name is not None: 
            params['name'] = name

        if roles is not None: 
            params['roles'] = roles

        return self.client.call('post', path, {
            'content-type': 'application/json',
        }, params)

    def get(self, team_id):
        """Get Team"""

        if team_id is None: 
            raise AppwriteException('Missing required parameter: "team_id"')

        params = {}
        path = '/teams/{teamId}'
        path = path.replace('{teamId}', team_id)                

        return self.client.call('get', path, {
            'content-type': 'application/json',
        }, params)

    def update(self, team_id, name):
        """Update Team"""

        if team_id is None: 
            raise AppwriteException('Missing required parameter: "team_id"')

        if name is None: 
            raise AppwriteException('Missing required parameter: "name"')

        params = {}
        path = '/teams/{teamId}'
        path = path.replace('{teamId}', team_id)                

        if name is not None: 
            params['name'] = name

        return self.client.call('put', path, {
            'content-type': 'application/json',
        }, params)

    def delete(self, team_id):
        """Delete Team"""

        if team_id is None: 
            raise AppwriteException('Missing required parameter: "team_id"')

        params = {}
        path = '/teams/{teamId}'
        path = path.replace('{teamId}', team_id)                

        return self.client.call('delete', path, {
            'content-type': 'application/json',
        }, params)

    def get_memberships(self, team_id, search = None, limit = None, offset = None, order_type = None):
        """Get Team Memberships"""

        if team_id is None: 
            raise AppwriteException('Missing required parameter: "team_id"')

        params = {}
        path = '/teams/{teamId}/memberships'
        path = path.replace('{teamId}', team_id)                

        if search is not None: 
            params['search'] = search

        if limit is not None: 
            params['limit'] = limit

        if offset is not None: 
            params['offset'] = offset

        if order_type is not None: 
            params['orderType'] = order_type

        return self.client.call('get', path, {
            'content-type': 'application/json',
        }, params)

    def create_membership(self, team_id, email, roles, url, name = None):
        """Create Team Membership"""

        if team_id is None: 
            raise AppwriteException('Missing required parameter: "team_id"')

        if email is None: 
            raise AppwriteException('Missing required parameter: "email"')

        if roles is None: 
            raise AppwriteException('Missing required parameter: "roles"')

        if url is None: 
            raise AppwriteException('Missing required parameter: "url"')

        params = {}
        path = '/teams/{teamId}/memberships'
        path = path.replace('{teamId}', team_id)                

        if email is not None: 
            params['email'] = email

        if roles is not None: 
            params['roles'] = roles

        if url is not None: 
            params['url'] = url

        if name is not None: 
            params['name'] = name

        return self.client.call('post', path, {
            'content-type': 'application/json',
        }, params)

    def update_membership_roles(self, team_id, membership_id, roles):
        """Update Membership Roles"""

        if team_id is None: 
            raise AppwriteException('Missing required parameter: "team_id"')

        if membership_id is None: 
            raise AppwriteException('Missing required parameter: "membership_id"')

        if roles is None: 
            raise AppwriteException('Missing required parameter: "roles"')

        params = {}
        path = '/teams/{teamId}/memberships/{membershipId}'
        path = path.replace('{teamId}', team_id)                
        path = path.replace('{membershipId}', membership_id)                

        if roles is not None: 
            params['roles'] = roles

        return self.client.call('patch', path, {
            'content-type': 'application/json',
        }, params)

    def delete_membership(self, team_id, membership_id):
        """Delete Team Membership"""

        if team_id is None: 
            raise AppwriteException('Missing required parameter: "team_id"')

        if membership_id is None: 
            raise AppwriteException('Missing required parameter: "membership_id"')

        params = {}
        path = '/teams/{teamId}/memberships/{membershipId}'
        path = path.replace('{teamId}', team_id)                
        path = path.replace('{membershipId}', membership_id)                

        return self.client.call('delete', path, {
            'content-type': 'application/json',
        }, params)

    def update_membership_status(self, team_id, membership_id, user_id, secret):
        """Update Team Membership Status"""

        if team_id is None: 
            raise AppwriteException('Missing required parameter: "team_id"')

        if membership_id is None: 
            raise AppwriteException('Missing required parameter: "membership_id"')

        if user_id is None: 
            raise AppwriteException('Missing required parameter: "user_id"')

        if secret is None: 
            raise AppwriteException('Missing required parameter: "secret"')

        params = {}
        path = '/teams/{teamId}/memberships/{membershipId}/status'
        path = path.replace('{teamId}', team_id)                
        path = path.replace('{membershipId}', membership_id)                

        if user_id is not None: 
            params['userId'] = user_id

        if secret is not None: 
            params['secret'] = secret

        return self.client.call('patch', path, {
            'content-type': 'application/json',
        }, params)
