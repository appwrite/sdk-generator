module Appwrite
    class Avatars < Service

        def get_browser(code:, width: nil, height: nil, quality: nil)
            if code.nil?
                raise Appwrite::Exception.new('Missing required parameter: "code"')
            end

            path = '/avatars/browsers/{code}'
                .gsub('{code}', code)

            params = {}

            if !width.nil?
                params[:width] = width
            end

            if !height.nil?
                params[:height] = height
            end

            if !quality.nil?
                params[:quality] = quality
            end

            return @client.call('get', path, {
                'content-type' => 'application/json',
            }, params);
        end

        def get_credit_card(code:, width: nil, height: nil, quality: nil)
            if code.nil?
                raise Appwrite::Exception.new('Missing required parameter: "code"')
            end

            path = '/avatars/credit-cards/{code}'
                .gsub('{code}', code)

            params = {}

            if !width.nil?
                params[:width] = width
            end

            if !height.nil?
                params[:height] = height
            end

            if !quality.nil?
                params[:quality] = quality
            end

            return @client.call('get', path, {
                'content-type' => 'application/json',
            }, params);
        end

        def get_favicon(url:)
            if url.nil?
                raise Appwrite::Exception.new('Missing required parameter: "url"')
            end

            path = '/avatars/favicon'

            params = {}

            if !url.nil?
                params[:url] = url
            end

            return @client.call('get', path, {
                'content-type' => 'application/json',
            }, params);
        end

        def get_flag(code:, width: nil, height: nil, quality: nil)
            if code.nil?
                raise Appwrite::Exception.new('Missing required parameter: "code"')
            end

            path = '/avatars/flags/{code}'
                .gsub('{code}', code)

            params = {}

            if !width.nil?
                params[:width] = width
            end

            if !height.nil?
                params[:height] = height
            end

            if !quality.nil?
                params[:quality] = quality
            end

            return @client.call('get', path, {
                'content-type' => 'application/json',
            }, params);
        end

        def get_image(url:, width: nil, height: nil)
            if url.nil?
                raise Appwrite::Exception.new('Missing required parameter: "url"')
            end

            path = '/avatars/image'

            params = {}

            if !url.nil?
                params[:url] = url
            end

            if !width.nil?
                params[:width] = width
            end

            if !height.nil?
                params[:height] = height
            end

            return @client.call('get', path, {
                'content-type' => 'application/json',
            }, params);
        end

        def get_initials(name: nil, width: nil, height: nil, color: nil, background: nil)
            path = '/avatars/initials'

            params = {}

            if !name.nil?
                params[:name] = name
            end

            if !width.nil?
                params[:width] = width
            end

            if !height.nil?
                params[:height] = height
            end

            if !color.nil?
                params[:color] = color
            end

            if !background.nil?
                params[:background] = background
            end

            return @client.call('get', path, {
                'content-type' => 'application/json',
            }, params);
        end

        def get_qr(text:, size: nil, margin: nil, download: nil)
            if text.nil?
                raise Appwrite::Exception.new('Missing required parameter: "text"')
            end

            path = '/avatars/qr'

            params = {}

            if !text.nil?
                params[:text] = text
            end

            if !size.nil?
                params[:size] = size
            end

            if !margin.nil?
                params[:margin] = margin
            end

            if !download.nil?
                params[:download] = download
            end

            return @client.call('get', path, {
                'content-type' => 'application/json',
            }, params);
        end


        protected

        private
    end 
end