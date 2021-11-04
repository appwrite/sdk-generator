from ..service import Service
from ..exception import AppwriteException

class Users(Service):

    def __init__(self, client):
        super(Users, self).__init__(client)

    def list(self, search = None, limit = None, offset = None, order_type = None):
        """List Users"""

        params = {}
        path = '/users'

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

    def create(self, email, password, name = None):
        """Create User"""

        if email is None: 
            raise AppwriteException('Missing required parameter: "email"')

        if password is None: 
            raise AppwriteException('Missing required parameter: "password"')

        params = {}
        path = '/users'

        if email is not None: 
            params['email'] = email

        if password is not None: 
            params['password'] = password

        if name is not None: 
            params['name'] = name

        return self.client.call('post', path, {
            'content-type': 'application/json',
        }, params)

    def get(self, user_id):
        """Get User"""

        if user_id is None: 
            raise AppwriteException('Missing required parameter: "user_id"')

        params = {}
        path = '/users/{userId}'
        path = path.replace('{userId}', user_id)                

        return self.client.call('get', path, {
            'content-type': 'application/json',
        }, params)

    def delete(self, user_id):
        """Delete User"""

        if user_id is None: 
            raise AppwriteException('Missing required parameter: "user_id"')

        params = {}
        path = '/users/{userId}'
        path = path.replace('{userId}', user_id)                

        return self.client.call('delete', path, {
            'content-type': 'application/json',
        }, params)

    def update_email(self, user_id, email):
        """Update Email"""

        if user_id is None: 
            raise AppwriteException('Missing required parameter: "user_id"')

        if email is None: 
            raise AppwriteException('Missing required parameter: "email"')

        params = {}
        path = '/users/{userId}/email'
        path = path.replace('{userId}', user_id)                

        if email is not None: 
            params['email'] = email

        return self.client.call('patch', path, {
            'content-type': 'application/json',
        }, params)

    def get_logs(self, user_id):
        """Get User Logs"""

        if user_id is None: 
            raise AppwriteException('Missing required parameter: "user_id"')

        params = {}
        path = '/users/{userId}/logs'
        path = path.replace('{userId}', user_id)                

        return self.client.call('get', path, {
            'content-type': 'application/json',
        }, params)

    def update_name(self, user_id, name):
        """Update Name"""

        if user_id is None: 
            raise AppwriteException('Missing required parameter: "user_id"')

        if name is None: 
            raise AppwriteException('Missing required parameter: "name"')

        params = {}
        path = '/users/{userId}/name'
        path = path.replace('{userId}', user_id)                

        if name is not None: 
            params['name'] = name

        return self.client.call('patch', path, {
            'content-type': 'application/json',
        }, params)

    def update_password(self, user_id, password):
        """Update Password"""

        if user_id is None: 
            raise AppwriteException('Missing required parameter: "user_id"')

        if password is None: 
            raise AppwriteException('Missing required parameter: "password"')

        params = {}
        path = '/users/{userId}/password'
        path = path.replace('{userId}', user_id)                

        if password is not None: 
            params['password'] = password

        return self.client.call('patch', path, {
            'content-type': 'application/json',
        }, params)

    def get_prefs(self, user_id):
        """Get User Preferences"""

        if user_id is None: 
            raise AppwriteException('Missing required parameter: "user_id"')

        params = {}
        path = '/users/{userId}/prefs'
        path = path.replace('{userId}', user_id)                

        return self.client.call('get', path, {
            'content-type': 'application/json',
        }, params)

    def update_prefs(self, user_id, prefs):
        """Update User Preferences"""

        if user_id is None: 
            raise AppwriteException('Missing required parameter: "user_id"')

        if prefs is None: 
            raise AppwriteException('Missing required parameter: "prefs"')

        params = {}
        path = '/users/{userId}/prefs'
        path = path.replace('{userId}', user_id)                

        if prefs is not None: 
            params['prefs'] = prefs

        return self.client.call('patch', path, {
            'content-type': 'application/json',
        }, params)

    def get_sessions(self, user_id):
        """Get User Sessions"""

        if user_id is None: 
            raise AppwriteException('Missing required parameter: "user_id"')

        params = {}
        path = '/users/{userId}/sessions'
        path = path.replace('{userId}', user_id)                

        return self.client.call('get', path, {
            'content-type': 'application/json',
        }, params)

    def delete_sessions(self, user_id):
        """Delete User Sessions"""

        if user_id is None: 
            raise AppwriteException('Missing required parameter: "user_id"')

        params = {}
        path = '/users/{userId}/sessions'
        path = path.replace('{userId}', user_id)                

        return self.client.call('delete', path, {
            'content-type': 'application/json',
        }, params)

    def delete_session(self, user_id, session_id):
        """Delete User Session"""

        if user_id is None: 
            raise AppwriteException('Missing required parameter: "user_id"')

        if session_id is None: 
            raise AppwriteException('Missing required parameter: "session_id"')

        params = {}
        path = '/users/{userId}/sessions/{sessionId}'
        path = path.replace('{userId}', user_id)                
        path = path.replace('{sessionId}', session_id)                

        return self.client.call('delete', path, {
            'content-type': 'application/json',
        }, params)

    def update_status(self, user_id, status):
        """Update User Status"""

        if user_id is None: 
            raise AppwriteException('Missing required parameter: "user_id"')

        if status is None: 
            raise AppwriteException('Missing required parameter: "status"')

        params = {}
        path = '/users/{userId}/status'
        path = path.replace('{userId}', user_id)                

        if status is not None: 
            params['status'] = status

        return self.client.call('patch', path, {
            'content-type': 'application/json',
        }, params)

    def update_verification(self, user_id, email_verification):
        """Update Email Verification"""

        if user_id is None: 
            raise AppwriteException('Missing required parameter: "user_id"')

        if email_verification is None: 
            raise AppwriteException('Missing required parameter: "email_verification"')

        params = {}
        path = '/users/{userId}/verification'
        path = path.replace('{userId}', user_id)                

        if email_verification is not None: 
            params['emailVerification'] = email_verification

        return self.client.call('patch', path, {
            'content-type': 'application/json',
        }, params)
