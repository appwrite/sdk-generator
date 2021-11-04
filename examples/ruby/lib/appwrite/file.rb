require 'mime/types'

module Appwrite
    class File
        def initialize(path)
            @name = ::File.basename(path)
            @content = ::File.read(path)
            @mimeType = MIME::Types.type_for(path)
        end

        def name
            @name
        end

        def content
            @content
        end

        def mimeType
            @mimeType
        end
    end
end