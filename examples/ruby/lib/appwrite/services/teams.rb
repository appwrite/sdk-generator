module Appwrite
    class Teams < Service

        def list(search: nil, limit: nil, offset: nil, order_type: nil)
            path = '/teams'

            params = {}

            if !search.nil?
                params[:search] = search
            end

            if !limit.nil?
                params[:limit] = limit
            end

            if !offset.nil?
                params[:offset] = offset
            end

            if !order_type.nil?
                params[:orderType] = order_type
            end

            return @client.call('get', path, {
                'content-type' => 'application/json',
            }, params);
        end

        def create(name:, roles: nil)
            if name.nil?
                raise Appwrite::Exception.new('Missing required parameter: "name"')
            end

            path = '/teams'

            params = {}

            if !name.nil?
                params[:name] = name
            end

            if !roles.nil?
                params[:roles] = roles
            end

            return @client.call('post', path, {
                'content-type' => 'application/json',
            }, params);
        end

        def get(team_id:)
            if team_id.nil?
                raise Appwrite::Exception.new('Missing required parameter: "teamId"')
            end

            path = '/teams/{teamId}'
                .gsub('{teamId}', team_id)

            params = {}

            return @client.call('get', path, {
                'content-type' => 'application/json',
            }, params);
        end

        def update(team_id:, name:)
            if team_id.nil?
                raise Appwrite::Exception.new('Missing required parameter: "teamId"')
            end

            if name.nil?
                raise Appwrite::Exception.new('Missing required parameter: "name"')
            end

            path = '/teams/{teamId}'
                .gsub('{teamId}', team_id)

            params = {}

            if !name.nil?
                params[:name] = name
            end

            return @client.call('put', path, {
                'content-type' => 'application/json',
            }, params);
        end

        def delete(team_id:)
            if team_id.nil?
                raise Appwrite::Exception.new('Missing required parameter: "teamId"')
            end

            path = '/teams/{teamId}'
                .gsub('{teamId}', team_id)

            params = {}

            return @client.call('delete', path, {
                'content-type' => 'application/json',
            }, params);
        end

        def get_memberships(team_id:, search: nil, limit: nil, offset: nil, order_type: nil)
            if team_id.nil?
                raise Appwrite::Exception.new('Missing required parameter: "teamId"')
            end

            path = '/teams/{teamId}/memberships'
                .gsub('{teamId}', team_id)

            params = {}

            if !search.nil?
                params[:search] = search
            end

            if !limit.nil?
                params[:limit] = limit
            end

            if !offset.nil?
                params[:offset] = offset
            end

            if !order_type.nil?
                params[:orderType] = order_type
            end

            return @client.call('get', path, {
                'content-type' => 'application/json',
            }, params);
        end

        def create_membership(team_id:, email:, roles:, url:, name: nil)
            if team_id.nil?
                raise Appwrite::Exception.new('Missing required parameter: "teamId"')
            end

            if email.nil?
                raise Appwrite::Exception.new('Missing required parameter: "email"')
            end

            if roles.nil?
                raise Appwrite::Exception.new('Missing required parameter: "roles"')
            end

            if url.nil?
                raise Appwrite::Exception.new('Missing required parameter: "url"')
            end

            path = '/teams/{teamId}/memberships'
                .gsub('{teamId}', team_id)

            params = {}

            if !email.nil?
                params[:email] = email
            end

            if !roles.nil?
                params[:roles] = roles
            end

            if !url.nil?
                params[:url] = url
            end

            if !name.nil?
                params[:name] = name
            end

            return @client.call('post', path, {
                'content-type' => 'application/json',
            }, params);
        end

        def update_membership_roles(team_id:, membership_id:, roles:)
            if team_id.nil?
                raise Appwrite::Exception.new('Missing required parameter: "teamId"')
            end

            if membership_id.nil?
                raise Appwrite::Exception.new('Missing required parameter: "membershipId"')
            end

            if roles.nil?
                raise Appwrite::Exception.new('Missing required parameter: "roles"')
            end

            path = '/teams/{teamId}/memberships/{membershipId}'
                .gsub('{teamId}', team_id)
                .gsub('{membershipId}', membership_id)

            params = {}

            if !roles.nil?
                params[:roles] = roles
            end

            return @client.call('patch', path, {
                'content-type' => 'application/json',
            }, params);
        end

        def delete_membership(team_id:, membership_id:)
            if team_id.nil?
                raise Appwrite::Exception.new('Missing required parameter: "teamId"')
            end

            if membership_id.nil?
                raise Appwrite::Exception.new('Missing required parameter: "membershipId"')
            end

            path = '/teams/{teamId}/memberships/{membershipId}'
                .gsub('{teamId}', team_id)
                .gsub('{membershipId}', membership_id)

            params = {}

            return @client.call('delete', path, {
                'content-type' => 'application/json',
            }, params);
        end

        def update_membership_status(team_id:, membership_id:, user_id:, secret:)
            if team_id.nil?
                raise Appwrite::Exception.new('Missing required parameter: "teamId"')
            end

            if membership_id.nil?
                raise Appwrite::Exception.new('Missing required parameter: "membershipId"')
            end

            if user_id.nil?
                raise Appwrite::Exception.new('Missing required parameter: "userId"')
            end

            if secret.nil?
                raise Appwrite::Exception.new('Missing required parameter: "secret"')
            end

            path = '/teams/{teamId}/memberships/{membershipId}/status'
                .gsub('{teamId}', team_id)
                .gsub('{membershipId}', membership_id)

            params = {}

            if !user_id.nil?
                params[:userId] = user_id
            end

            if !secret.nil?
                params[:secret] = secret
            end

            return @client.call('patch', path, {
                'content-type' => 'application/json',
            }, params);
        end


        protected

        private
    end 
end