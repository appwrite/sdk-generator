module Appwrite
    class Account < Service

        def get()
            path = '/account'

            params = {
            }

            return @client.call('get', path, {
                'content-type' => 'application/json',
            }, params);
        end

        def create(email:, password:, name: '')
            path = '/account'

            params = {
                'email': email, 
                'password': password, 
                'name': name
            }

            return @client.call('post', path, {
                'content-type' => 'application/json',
            }, params);
        end

        def delete()
            path = '/account'

            params = {
            }

            return @client.call('delete', path, {
                'content-type' => 'application/json',
            }, params);
        end

        def update_email(email:, password:)
            path = '/account/email'

            params = {
                'email': email, 
                'password': password
            }

            return @client.call('patch', path, {
                'content-type' => 'application/json',
            }, params);
        end

        def get_logs()
            path = '/account/logs'

            params = {
            }

            return @client.call('get', path, {
                'content-type' => 'application/json',
            }, params);
        end

        def update_name(name:)
            path = '/account/name'

            params = {
                'name': name
            }

            return @client.call('patch', path, {
                'content-type' => 'application/json',
            }, params);
        end

        def update_password(password:, old_password:)
            path = '/account/password'

            params = {
                'password': password, 
                'old-password': old_password
            }

            return @client.call('patch', path, {
                'content-type' => 'application/json',
            }, params);
        end

        def get_prefs()
            path = '/account/prefs'

            params = {
            }

            return @client.call('get', path, {
                'content-type' => 'application/json',
            }, params);
        end

        def update_prefs(prefs:)
            path = '/account/prefs'

            params = {
                'prefs': prefs
            }

            return @client.call('patch', path, {
                'content-type' => 'application/json',
            }, params);
        end

        def create_recovery(email:, url:)
            path = '/account/recovery'

            params = {
                'email': email, 
                'url': url
            }

            return @client.call('post', path, {
                'content-type' => 'application/json',
            }, params);
        end

        def update_recovery(user_id:, secret:, password_a:, password_b:)
            path = '/account/recovery'

            params = {
                'userId': user_id, 
                'secret': secret, 
                'password-a': password_a, 
                'password-b': password_b
            }

            return @client.call('put', path, {
                'content-type' => 'application/json',
            }, params);
        end

        def get_sessions()
            path = '/account/sessions'

            params = {
            }

            return @client.call('get', path, {
                'content-type' => 'application/json',
            }, params);
        end

        def create_session(email:, password:)
            path = '/account/sessions'

            params = {
                'email': email, 
                'password': password
            }

            return @client.call('post', path, {
                'content-type' => 'application/json',
            }, params);
        end

        def delete_sessions()
            path = '/account/sessions'

            params = {
            }

            return @client.call('delete', path, {
                'content-type' => 'application/json',
            }, params);
        end

        def create_o_auth_session(provider:, success:, failure:)
            path = '/account/sessions/oauth/{provider}'
                .gsub('{provider}', provider)

            params = {
                'success': success, 
                'failure': failure
            }

            return @client.call('get', path, {
                'content-type' => 'application/json',
            }, params);
        end

        def delete_session(session_uid:)
            path = '/account/sessions/{sessionUid}'
                .gsub('{session_uid}', session_uid)

            params = {
            }

            return @client.call('delete', path, {
                'content-type' => 'application/json',
            }, params);
        end

        def create_verification(url:)
            path = '/account/verification'

            params = {
                'url': url
            }

            return @client.call('post', path, {
                'content-type' => 'application/json',
            }, params);
        end

        def update_verification(user_id:, secret:)
            path = '/account/verification'

            params = {
                'userId': user_id, 
                'secret': secret
            }

            return @client.call('put', path, {
                'content-type' => 'application/json',
            }, params);
        end


        protected

        private
    end 
end