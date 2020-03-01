from ..service import Service


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

    def create(self, email, password, name=''):
        """Create Account"""

        params = {}
        path = '/account'
        params['email'] = email
        params['password'] = password
        params['name'] = name

        return self.client.call('post', path, {
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

        params = {}
        path = '/account/email'
        params['email'] = email
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

        params = {}
        path = '/account/name'
        params['name'] = name

        return self.client.call('patch', path, {
            'content-type': 'application/json',
        }, params)

    def update_password(self, password, old_password):
        """Update Account Password"""

        params = {}
        path = '/account/password'
        params['password'] = password
        params['old-password'] = old_password

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

        params = {}
        path = '/account/prefs'
        params['prefs'] = prefs

        return self.client.call('patch', path, {
            'content-type': 'application/json',
        }, params)

    def create_recovery(self, email, url):
        """Create Password Recovery"""

        params = {}
        path = '/account/recovery'
        params['email'] = email
        params['url'] = url

        return self.client.call('post', path, {
            'content-type': 'application/json',
        }, params)

    def update_recovery(self, user_id, secret, password_a, password_b):
        """Complete Password Recovery"""

        params = {}
        path = '/account/recovery'
        params['userId'] = user_id
        params['secret'] = secret
        params['password-a'] = password_a
        params['password-b'] = password_b

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

    def create_session(self, email, password):
        """Create Account Session"""

        params = {}
        path = '/account/sessions'
        params['email'] = email
        params['password'] = password

        return self.client.call('post', path, {
            'content-type': 'application/json',
        }, params)

    def delete_sessions(self):
        """Delete All Account Sessions"""

        params = {}
        path = '/account/sessions'

        return self.client.call('delete', path, {
            'content-type': 'application/json',
        }, params)

    def create_o_auth2session(self, provider, success, failure):
        """Create Account Session with OAuth2"""

        params = {}
        path = '/account/sessions/oauth2/{provider}'
        path = path.replace('{provider}', provider)                
        params['success'] = success
        params['failure'] = failure

        return self.client.call('get', path, {
            'content-type': 'application/json',
        }, params)

    def delete_session(self, session_id):
        """Delete Account Session"""

        params = {}
        path = '/account/sessions/{sessionId}'
        path = path.replace('{sessionId}', session_id)                

        return self.client.call('delete', path, {
            'content-type': 'application/json',
        }, params)

    def create_verification(self, url):
        """Create Email Verification"""

        params = {}
        path = '/account/verification'
        params['url'] = url

        return self.client.call('post', path, {
            'content-type': 'application/json',
        }, params)

    def update_verification(self, user_id, secret):
        """Complete Email Verification"""

        params = {}
        path = '/account/verification'
        params['userId'] = user_id
        params['secret'] = secret

        return self.client.call('put', path, {
            'content-type': 'application/json',
        }, params)
