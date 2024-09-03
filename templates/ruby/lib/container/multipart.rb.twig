require 'mime/types'

module Appwrite
    class MultipartBuilder
        attr_reader :boundary

        def initialize(boundary: nil)
            @boundary = boundary ||= "----RubyMultipartPost#{rand(1000000)}"
            @parts = []
        end

        def add(name, contents, filename: nil, content_type: nil)
            if contents.is_a?(Array)
                contents.each_with_index do |element, index|
                    add("#{name}[#{index}]", element)
                end
                return
            end

            part = "--#{@boundary}\r\n"
            part << "Content-Disposition: form-data; name=\"#{name}\""
            part << "; filename=\"#{filename}\"" if filename
            part << "\r\n"
            if content_type
            part << "Content-Type: #{content_type}\r\n"
            elsif filename
            content_type = MIME::Types.type_for(filename).first&.content_type || 'application/octet-stream'
            part << "Content-Type: #{content_type}\r\n"
            end
            part << "\r\n"
            part << contents.to_s
            part << "\r\n"

            @parts << part
        end

        def body
            @parts.join + "--#{@boundary}--\r\n"
        end

        def content_type
            "multipart/form-data; boundary=#{@boundary}"
        end
    end

    class MultipartParser
        attr_reader :parts

        def initialize(multipart_string, content_type)
            @multipart_string = multipart_string
            @boundary = _extract_boundary(content_type)
            @parts = {}
            parse
        end

        def _extract_boundary(content_type)
            match = content_type.match(/boundary="?(.+?)"?(?:\s*;|$)/)
            if match
                return match[1]
            end

            puts content_type
            
            raise "Boundary not found in Content-Type header"
        end

        def parse
            # Split the multipart string into individual parts
            parts = @multipart_string.split("--#{@boundary}")

            # Remove the first (empty) and last (boundary end) elements
            parts = parts[1...-1]

            parts.each do |part|
                # Split headers and content
                headers, content = part.strip.split("\r\n\r\n", 2)

                # Parse headers
                headers_hash = headers.split("\r\n").each_with_object({}) do |header, hash|
                    key, value = header.split(": ", 2)
                    hash[key.downcase] = value
                end

                # Extract name from Content-Disposition header
                content_disposition = headers_hash["content-disposition"] || ""
                name = content_disposition[/name="([^"]*)"/, 1]

                # If no name is found, use a default naming scheme
                name ||= "unnamed_part_#{@parts.length}"

                # Store the parsed data
                @parts[name] = {
                    contents: content.strip,
                    headers: headers_hash
                }
            end
        end

        def to_hash
            h = {}

            @parts.each do |name, part|
                case name
                when "responseBody"
                    h[name] = Payload.from_binary(part[:contents])
                when "responseHeaders"
                    h[name] = part[:contents].split("\r\n").each_with_object({}) do |header, hash|
                        key, value = header.split(": ", 2)
                        hash[key] = value
                    end
                when "responseStatusCode"
                    h[name] = part[:contents].to_i
                when "duration"
                    h[name] = part[:contents].to_f
                else
                    begin
                        h[name] = part[:contents].force_encoding("utf-8")
                    rescue
                        h[name] = part[:contents]
                    end
                end
            end

            h
        end
    end
end