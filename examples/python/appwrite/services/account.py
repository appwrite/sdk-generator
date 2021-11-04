from ..service import Service
from ..exception import AppwriteException

class Account(Service):

    def __init__(self, client):
        super(Account, self).__init__(client)

    def get(self):
        """Get Account"""

        params = {}
        path = '/account'

        return self.client.call('get', path, {
            'content-type': 'application/json',
        }, params)

    def delete(self):
        """Delete Account"""

        params = {}
        path = '/account'

        return self.client.call('delete', path, {
            'content-type': 'application/json',
        }, params)

    def update_email(self, email, password):
        """Update Account Email"""

        if email is None: 
            raise AppwriteException('Missing required parameter: "email"')

        if password is None: 
            raise AppwriteException('Missing required parameter: "password"')

        params = {}
        path = '/account/email'

        if email is not None: 
            params['email'] = email

        if password is not None: 
            params['password'] = password

        return self.client.call('patch', path, {
            'content-type': 'application/json',
        }, params)

    def get_logs(self):
        """Get Account Logs"""

        params = {}
        path = '/account/logs'

        return self.client.call('get', path, {
            'content-type': 'application/json',
        }, params)

    def update_name(self, name):
        """Update Account Name"""

        if name is None: 
            raise AppwriteException('Missing required parameter: "name"')

        params = {}
        path = '/account/name'

        if name is not None: 
            params['name'] = name

        return self.client.call('patch', path, {
            'content-type': 'application/json',
        }, params)

    def update_password(self, password, old_password = None):
        """Update Account Password"""

        if password is None: 
            raise AppwriteException('Missing required parameter: "password"')

        params = {}
        path = '/account/password'

        if password is not None: 
            params['password'] = password

        if old_password is not None: 
            params['oldPassword'] = old_password

        return self.client.call('patch', path, {
            'content-type': 'application/json',
        }, params)

    def get_prefs(self):
        """Get Account Preferences"""

        params = {}
        path = '/account/prefs'

        return self.client.call('get', path, {
            'content-type': 'application/json',
        }, params)

    def update_prefs(self, prefs):
        """Update Account Preferences"""

        if prefs is None: 
            raise AppwriteException('Missing required parameter: "prefs"')

        params = {}
        path = '/account/prefs'

        if prefs is not None: 
            params['prefs'] = prefs

        return self.client.call('patch', path, {
            'content-type': 'application/json',
        }, params)

    def create_recovery(self, email, url):
        """Create Password Recovery"""

        if email is None: 
            raise AppwriteException('Missing required parameter: "email"')

        if url is None: 
            raise AppwriteException('Missing required parameter: "url"')

        params = {}
        path = '/account/recovery'

        if email is not None: 
            params['email'] = email

        if url is not None: 
            params['url'] = url

        return self.client.call('post', path, {
            'content-type': 'application/json',
        }, params)

    def update_recovery(self, user_id, secret, password, password_again):
        """Create Password Recovery (confirmation)"""

        if user_id is None: 
            raise AppwriteException('Missing required parameter: "user_id"')

        if secret is None: 
            raise AppwriteException('Missing required parameter: "secret"')

        if password is None: 
            raise AppwriteException('Missing required parameter: "password"')

        if password_again is None: 
            raise AppwriteException('Missing required parameter: "password_again"')

        params = {}
        path = '/account/recovery'

        if user_id is not None: 
            params['userId'] = user_id

        if secret is not None: 
            params['secret'] = secret

        if password is not None: 
            params['password'] = password

        if password_again is not None: 
            params['passwordAgain'] = password_again

        return self.client.call('put', path, {
            'content-type': 'application/json',
        }, params)

    def get_sessions(self):
        """Get Account Sessions"""

        params = {}
        path = '/account/sessions'

        return self.client.call('get', path, {
            'content-type': 'application/json',
        }, params)

    def delete_sessions(self):
        """Delete All Account Sessions"""

        params = {}
        path = '/account/sessions'

        return self.client.call('delete', path, {
            'content-type': 'application/json',
        }, params)

    def get_session(self, session_id):
        """Get Session By ID"""

        if session_id is None: 
            raise AppwriteException('Missing required parameter: "session_id"')

        params = {}
        path = '/account/sessions/{sessionId}'
        path = path.replace('{sessionId}', session_id)                

        return self.client.call('get', path, {
            'content-type': 'application/json',
        }, params)

    def delete_session(self, session_id):
        """Delete Account Session"""

        if session_id is None: 
            raise AppwriteException('Missing required parameter: "session_id"')

        params = {}
        path = '/account/sessions/{sessionId}'
        path = path.replace('{sessionId}', session_id)                

        return self.client.call('delete', path, {
            'content-type': 'application/json',
        }, params)

    def create_verification(self, url):
        """Create Email Verification"""

        if url is None: 
            raise AppwriteException('Missing required parameter: "url"')

        params = {}
        path = '/account/verification'

        if url is not None: 
            params['url'] = url

        return self.client.call('post', path, {
            'content-type': 'application/json',
        }, params)

    def update_verification(self, user_id, secret):
        """Create Email Verification (confirmation)"""

        if user_id is None: 
            raise AppwriteException('Missing required parameter: "user_id"')

        if secret is None: 
            raise AppwriteException('Missing required parameter: "secret"')

        params = {}
        path = '/account/verification'

        if user_id is not None: 
            params['userId'] = user_id

        if secret is not None: 
            params['secret'] = secret

        return self.client.call('put', path, {
            'content-type': 'application/json',
        }, params)
