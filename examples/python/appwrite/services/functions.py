from ..service import Service
from ..exception import AppwriteException

class Functions(Service):

    def __init__(self, client):
        super(Functions, self).__init__(client)

    def list(self, search = None, limit = None, offset = None, order_type = None):
        """List Functions"""

        params = {}
        path = '/functions'

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

    def create(self, name, execute, runtime, vars = None, events = None, schedule = None, timeout = None):
        """Create Function"""

        if name is None: 
            raise AppwriteException('Missing required parameter: "name"')

        if execute is None: 
            raise AppwriteException('Missing required parameter: "execute"')

        if runtime is None: 
            raise AppwriteException('Missing required parameter: "runtime"')

        params = {}
        path = '/functions'

        if name is not None: 
            params['name'] = name

        if execute is not None: 
            params['execute'] = execute

        if runtime is not None: 
            params['runtime'] = runtime

        if vars is not None: 
            params['vars'] = vars

        if events is not None: 
            params['events'] = events

        if schedule is not None: 
            params['schedule'] = schedule

        if timeout is not None: 
            params['timeout'] = timeout

        return self.client.call('post', path, {
            'content-type': 'application/json',
        }, params)

    def get(self, function_id):
        """Get Function"""

        if function_id is None: 
            raise AppwriteException('Missing required parameter: "function_id"')

        params = {}
        path = '/functions/{functionId}'
        path = path.replace('{functionId}', function_id)                

        return self.client.call('get', path, {
            'content-type': 'application/json',
        }, params)

    def update(self, function_id, name, execute, vars = None, events = None, schedule = None, timeout = None):
        """Update Function"""

        if function_id is None: 
            raise AppwriteException('Missing required parameter: "function_id"')

        if name is None: 
            raise AppwriteException('Missing required parameter: "name"')

        if execute is None: 
            raise AppwriteException('Missing required parameter: "execute"')

        params = {}
        path = '/functions/{functionId}'
        path = path.replace('{functionId}', function_id)                

        if name is not None: 
            params['name'] = name

        if execute is not None: 
            params['execute'] = execute

        if vars is not None: 
            params['vars'] = vars

        if events is not None: 
            params['events'] = events

        if schedule is not None: 
            params['schedule'] = schedule

        if timeout is not None: 
            params['timeout'] = timeout

        return self.client.call('put', path, {
            'content-type': 'application/json',
        }, params)

    def delete(self, function_id):
        """Delete Function"""

        if function_id is None: 
            raise AppwriteException('Missing required parameter: "function_id"')

        params = {}
        path = '/functions/{functionId}'
        path = path.replace('{functionId}', function_id)                

        return self.client.call('delete', path, {
            'content-type': 'application/json',
        }, params)

    def list_executions(self, function_id, search = None, limit = None, offset = None, order_type = None):
        """List Executions"""

        if function_id is None: 
            raise AppwriteException('Missing required parameter: "function_id"')

        params = {}
        path = '/functions/{functionId}/executions'
        path = path.replace('{functionId}', function_id)                

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

    def create_execution(self, function_id, data = None):
        """Create Execution"""

        if function_id is None: 
            raise AppwriteException('Missing required parameter: "function_id"')

        params = {}
        path = '/functions/{functionId}/executions'
        path = path.replace('{functionId}', function_id)                

        if data is not None: 
            params['data'] = data

        return self.client.call('post', path, {
            'content-type': 'application/json',
        }, params)

    def get_execution(self, function_id, execution_id):
        """Get Execution"""

        if function_id is None: 
            raise AppwriteException('Missing required parameter: "function_id"')

        if execution_id is None: 
            raise AppwriteException('Missing required parameter: "execution_id"')

        params = {}
        path = '/functions/{functionId}/executions/{executionId}'
        path = path.replace('{functionId}', function_id)                
        path = path.replace('{executionId}', execution_id)                

        return self.client.call('get', path, {
            'content-type': 'application/json',
        }, params)

    def update_tag(self, function_id, tag):
        """Update Function Tag"""

        if function_id is None: 
            raise AppwriteException('Missing required parameter: "function_id"')

        if tag is None: 
            raise AppwriteException('Missing required parameter: "tag"')

        params = {}
        path = '/functions/{functionId}/tag'
        path = path.replace('{functionId}', function_id)                

        if tag is not None: 
            params['tag'] = tag

        return self.client.call('patch', path, {
            'content-type': 'application/json',
        }, params)

    def list_tags(self, function_id, search = None, limit = None, offset = None, order_type = None):
        """List Tags"""

        if function_id is None: 
            raise AppwriteException('Missing required parameter: "function_id"')

        params = {}
        path = '/functions/{functionId}/tags'
        path = path.replace('{functionId}', function_id)                

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

    def create_tag(self, function_id, command, code):
        """Create Tag"""

        if function_id is None: 
            raise AppwriteException('Missing required parameter: "function_id"')

        if command is None: 
            raise AppwriteException('Missing required parameter: "command"')

        if code is None: 
            raise AppwriteException('Missing required parameter: "code"')

        params = {}
        path = '/functions/{functionId}/tags'
        path = path.replace('{functionId}', function_id)                

        if command is not None: 
            params['command'] = command

        if code is not None: 
            params['code'] = code

        return self.client.call('post', path, {
            'content-type': 'multipart/form-data',
        }, params)

    def get_tag(self, function_id, tag_id):
        """Get Tag"""

        if function_id is None: 
            raise AppwriteException('Missing required parameter: "function_id"')

        if tag_id is None: 
            raise AppwriteException('Missing required parameter: "tag_id"')

        params = {}
        path = '/functions/{functionId}/tags/{tagId}'
        path = path.replace('{functionId}', function_id)                
        path = path.replace('{tagId}', tag_id)                

        return self.client.call('get', path, {
            'content-type': 'application/json',
        }, params)

    def delete_tag(self, function_id, tag_id):
        """Delete Tag"""

        if function_id is None: 
            raise AppwriteException('Missing required parameter: "function_id"')

        if tag_id is None: 
            raise AppwriteException('Missing required parameter: "tag_id"')

        params = {}
        path = '/functions/{functionId}/tags/{tagId}'
        path = path.replace('{functionId}', function_id)                
        path = path.replace('{tagId}', tag_id)                

        return self.client.call('delete', path, {
            'content-type': 'application/json',
        }, params)
