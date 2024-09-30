require 'fileutils'

module Appwrite
  class Payload
    attr_reader :filename
    attr_reader :size

    def initialize(data, path, filename)
      if data.nil? && path.nil? then
        raise ArgumentError.new('Payload must have one of data or path')
      end

      @path = path
      @data = data

      @filename = filename
      @size = if @data then
        @data.bytesize
      else
        File.size(@path)
      end
    end

    # @param [Integer] offset
    # @param [Integer] length
    # @return [String]
    def to_binary(offset = 0, length = nil)
      length ||= @size - offset
      if @data then
        @data.byteslice(offset, length)
      else
        IO.read(@path, length, offset)
      end
    end

    # @return [String]
    def to_s()
      to_binary().force_encoding('UTF-8')
    end

    alias to_string to_s

    # @return [Hash]
    def to_json()
      JSON.parse(to_s())
    end

    # @param [String] path 
    # @return [void]
    def to_file(path)
      FileUtils.mkdir_p(File.dirname(path))
      File.open(path, 'wb') { |f| f.write(to_binary()) }
    end

    # @param [String] bytes
    # @param [String, nil] filename
    # @return [Payload]
    def self.from_binary(bytes, filename: nil)
      new(bytes, nil, filename)
    end

    # @param [String] string
    # @param [String, nil] filename
    # @return [Payload]
    def self.from_string(string, filename: nil)
      bytes = string.encode('UTF-8')
      new(bytes, nil, filename)
    end

    # @param [Hash, Array] object
    # @param [String, nil] filename
    # @return [Payload]
    def self.from_json(object, filename: nil)
      if !object.is_a?(Hash) && !object.is_a?(Array) then
        raise ArgumentError.new('Object must be a Hash or Array')
      end
      json = JSON.generate(object)
      self.from_string(json, filename: filename)
    end

    # @param [String] path
    # @param [String, nil] filename
    # @return [Payload]
    def self.from_file(path, filename: nil)
      raise ArgumentError.new('File not found') if !File.exists?(path)
      filename = if filename.nil? then
        File.basename(path)
      else
        filename
      end
      new(nil, path, filename)
    end
  end
end