from ..service import Service
from ..exception import AppwriteException

class Storage(Service):

    def __init__(self, client):
        super(Storage, self).__init__(client)

    def list_files(self, search = None, limit = None, offset = None, order_type = None):
        """List Files"""

        params = {}
        path = '/storage/files'

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

    def create_file(self, file, read = None, write = None):
        """Create File"""

        if file is None: 
            raise AppwriteException('Missing required parameter: "file"')

        params = {}
        path = '/storage/files'

        if file is not None: 
            params['file'] = file

        if read is not None: 
            params['read'] = read

        if write is not None: 
            params['write'] = write

        return self.client.call('post', path, {
            'content-type': 'multipart/form-data',
        }, params)

    def get_file(self, file_id):
        """Get File"""

        if file_id is None: 
            raise AppwriteException('Missing required parameter: "file_id"')

        params = {}
        path = '/storage/files/{fileId}'
        path = path.replace('{fileId}', file_id)                

        return self.client.call('get', path, {
            'content-type': 'application/json',
        }, params)

    def update_file(self, file_id, read, write):
        """Update File"""

        if file_id is None: 
            raise AppwriteException('Missing required parameter: "file_id"')

        if read is None: 
            raise AppwriteException('Missing required parameter: "read"')

        if write is None: 
            raise AppwriteException('Missing required parameter: "write"')

        params = {}
        path = '/storage/files/{fileId}'
        path = path.replace('{fileId}', file_id)                

        if read is not None: 
            params['read'] = read

        if write is not None: 
            params['write'] = write

        return self.client.call('put', path, {
            'content-type': 'application/json',
        }, params)

    def delete_file(self, file_id):
        """Delete File"""

        if file_id is None: 
            raise AppwriteException('Missing required parameter: "file_id"')

        params = {}
        path = '/storage/files/{fileId}'
        path = path.replace('{fileId}', file_id)                

        return self.client.call('delete', path, {
            'content-type': 'application/json',
        }, params)

    def get_file_download(self, file_id):
        """Get File for Download"""

        if file_id is None: 
            raise AppwriteException('Missing required parameter: "file_id"')

        params = {}
        path = '/storage/files/{fileId}/download'
        path = path.replace('{fileId}', file_id)                

        return self.client.call('get', path, {
            'content-type': 'application/json',
        }, params)

    def get_file_preview(self, file_id, width = None, height = None, gravity = None, quality = None, border_width = None, border_color = None, border_radius = None, opacity = None, rotation = None, background = None, output = None):
        """Get File Preview"""

        if file_id is None: 
            raise AppwriteException('Missing required parameter: "file_id"')

        params = {}
        path = '/storage/files/{fileId}/preview'
        path = path.replace('{fileId}', file_id)                

        if width is not None: 
            params['width'] = width

        if height is not None: 
            params['height'] = height

        if gravity is not None: 
            params['gravity'] = gravity

        if quality is not None: 
            params['quality'] = quality

        if border_width is not None: 
            params['borderWidth'] = border_width

        if border_color is not None: 
            params['borderColor'] = border_color

        if border_radius is not None: 
            params['borderRadius'] = border_radius

        if opacity is not None: 
            params['opacity'] = opacity

        if rotation is not None: 
            params['rotation'] = rotation

        if background is not None: 
            params['background'] = background

        if output is not None: 
            params['output'] = output

        return self.client.call('get', path, {
            'content-type': 'application/json',
        }, params)

    def get_file_view(self, file_id):
        """Get File for View"""

        if file_id is None: 
            raise AppwriteException('Missing required parameter: "file_id"')

        params = {}
        path = '/storage/files/{fileId}/view'
        path = path.replace('{fileId}', file_id)                

        return self.client.call('get', path, {
            'content-type': 'application/json',
        }, params)
