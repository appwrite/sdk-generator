module Appwrite
    class Account < Service

        def get()
            path = '/account'

            params = {}

            return @client.call('get', path, {
                'content-type' => 'application/json',
            }, params);
        end

        def delete()
            path = '/account'

            params = {}

            return @client.call('delete', path, {
                'content-type' => 'application/json',
            }, params);
        end

        def update_email(email:, password:)
            if email.nil?
                raise Appwrite::Exception.new('Missing required parameter: "email"')
            end

            if password.nil?
                raise Appwrite::Exception.new('Missing required parameter: "password"')
            end

            path = '/account/email'

            params = {}

            if !email.nil?
                params[:email] = email
            end

            if !password.nil?
                params[:password] = password
            end

            return @client.call('patch', path, {
                'content-type' => 'application/json',
            }, params);
        end

        def get_logs()
            path = '/account/logs'

            params = {}

            return @client.call('get', path, {
                'content-type' => 'application/json',
            }, params);
        end

        def update_name(name:)
            if name.nil?
                raise Appwrite::Exception.new('Missing required parameter: "name"')
            end

            path = '/account/name'

            params = {}

            if !name.nil?
                params[:name] = name
            end

            return @client.call('patch', path, {
                'content-type' => 'application/json',
            }, params);
        end

        def update_password(password:, old_password: nil)
            if password.nil?
                raise Appwrite::Exception.new('Missing required parameter: "password"')
            end

            path = '/account/password'

            params = {}

            if !password.nil?
                params[:password] = password
            end

            if !old_password.nil?
                params[:oldPassword] = old_password
            end

            return @client.call('patch', path, {
                'content-type' => 'application/json',
            }, params);
        end

        def get_prefs()
            path = '/account/prefs'

            params = {}

            return @client.call('get', path, {
                'content-type' => 'application/json',
            }, params);
        end

        def update_prefs(prefs:)
            if prefs.nil?
                raise Appwrite::Exception.new('Missing required parameter: "prefs"')
            end

            path = '/account/prefs'

            params = {}

            if !prefs.nil?
                params[:prefs] = prefs
            end

            return @client.call('patch', path, {
                'content-type' => 'application/json',
            }, params);
        end

        def create_recovery(email:, url:)
            if email.nil?
                raise Appwrite::Exception.new('Missing required parameter: "email"')
            end

            if url.nil?
                raise Appwrite::Exception.new('Missing required parameter: "url"')
            end

            path = '/account/recovery'

            params = {}

            if !email.nil?
                params[:email] = email
            end

            if !url.nil?
                params[:url] = url
            end

            return @client.call('post', path, {
                'content-type' => 'application/json',
            }, params);
        end

        def update_recovery(user_id:, secret:, password:, password_again:)
            if user_id.nil?
                raise Appwrite::Exception.new('Missing required parameter: "userId"')
            end

            if secret.nil?
                raise Appwrite::Exception.new('Missing required parameter: "secret"')
            end

            if password.nil?
                raise Appwrite::Exception.new('Missing required parameter: "password"')
            end

            if password_again.nil?
                raise Appwrite::Exception.new('Missing required parameter: "passwordAgain"')
            end

            path = '/account/recovery'

            params = {}

            if !user_id.nil?
                params[:userId] = user_id
            end

            if !secret.nil?
                params[:secret] = secret
            end

            if !password.nil?
                params[:password] = password
            end

            if !password_again.nil?
                params[:passwordAgain] = password_again
            end

            return @client.call('put', path, {
                'content-type' => 'application/json',
            }, params);
        end

        def get_sessions()
            path = '/account/sessions'

            params = {}

            return @client.call('get', path, {
                'content-type' => 'application/json',
            }, params);
        end

        def delete_sessions()
            path = '/account/sessions'

            params = {}

            return @client.call('delete', path, {
                'content-type' => 'application/json',
            }, params);
        end

        def get_session(session_id:)
            if session_id.nil?
                raise Appwrite::Exception.new('Missing required parameter: "sessionId"')
            end

            path = '/account/sessions/{sessionId}'
                .gsub('{sessionId}', session_id)

            params = {}

            return @client.call('get', path, {
                'content-type' => 'application/json',
            }, params);
        end

        def delete_session(session_id:)
            if session_id.nil?
                raise Appwrite::Exception.new('Missing required parameter: "sessionId"')
            end

            path = '/account/sessions/{sessionId}'
                .gsub('{sessionId}', session_id)

            params = {}

            return @client.call('delete', path, {
                'content-type' => 'application/json',
            }, params);
        end

        def create_verification(url:)
            if url.nil?
                raise Appwrite::Exception.new('Missing required parameter: "url"')
            end

            path = '/account/verification'

            params = {}

            if !url.nil?
                params[:url] = url
            end

            return @client.call('post', path, {
                'content-type' => 'application/json',
            }, params);
        end

        def update_verification(user_id:, secret:)
            if user_id.nil?
                raise Appwrite::Exception.new('Missing required parameter: "userId"')
            end

            if secret.nil?
                raise Appwrite::Exception.new('Missing required parameter: "secret"')
            end

            path = '/account/verification'

            params = {}

            if !user_id.nil?
                params[:userId] = user_id
            end

            if !secret.nil?
                params[:secret] = secret
            end

            return @client.call('put', path, {
                'content-type' => 'application/json',
            }, params);
        end


        protected

        private
    end 
end