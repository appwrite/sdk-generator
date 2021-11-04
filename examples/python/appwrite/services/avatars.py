from ..service import Service
from ..exception import AppwriteException

class Avatars(Service):

    def __init__(self, client):
        super(Avatars, self).__init__(client)

    def get_browser(self, code, width = None, height = None, quality = None):
        """Get Browser Icon"""

        if code is None: 
            raise AppwriteException('Missing required parameter: "code"')

        params = {}
        path = '/avatars/browsers/{code}'
        path = path.replace('{code}', code)                

        if width is not None: 
            params['width'] = width

        if height is not None: 
            params['height'] = height

        if quality is not None: 
            params['quality'] = quality

        return self.client.call('get', path, {
            'content-type': 'application/json',
        }, params)

    def get_credit_card(self, code, width = None, height = None, quality = None):
        """Get Credit Card Icon"""

        if code is None: 
            raise AppwriteException('Missing required parameter: "code"')

        params = {}
        path = '/avatars/credit-cards/{code}'
        path = path.replace('{code}', code)                

        if width is not None: 
            params['width'] = width

        if height is not None: 
            params['height'] = height

        if quality is not None: 
            params['quality'] = quality

        return self.client.call('get', path, {
            'content-type': 'application/json',
        }, params)

    def get_favicon(self, url):
        """Get Favicon"""

        if url is None: 
            raise AppwriteException('Missing required parameter: "url"')

        params = {}
        path = '/avatars/favicon'

        if url is not None: 
            params['url'] = url

        return self.client.call('get', path, {
            'content-type': 'application/json',
        }, params)

    def get_flag(self, code, width = None, height = None, quality = None):
        """Get Country Flag"""

        if code is None: 
            raise AppwriteException('Missing required parameter: "code"')

        params = {}
        path = '/avatars/flags/{code}'
        path = path.replace('{code}', code)                

        if width is not None: 
            params['width'] = width

        if height is not None: 
            params['height'] = height

        if quality is not None: 
            params['quality'] = quality

        return self.client.call('get', path, {
            'content-type': 'application/json',
        }, params)

    def get_image(self, url, width = None, height = None):
        """Get Image from URL"""

        if url is None: 
            raise AppwriteException('Missing required parameter: "url"')

        params = {}
        path = '/avatars/image'

        if url is not None: 
            params['url'] = url

        if width is not None: 
            params['width'] = width

        if height is not None: 
            params['height'] = height

        return self.client.call('get', path, {
            'content-type': 'application/json',
        }, params)

    def get_initials(self, name = None, width = None, height = None, color = None, background = None):
        """Get User Initials"""

        params = {}
        path = '/avatars/initials'

        if name is not None: 
            params['name'] = name

        if width is not None: 
            params['width'] = width

        if height is not None: 
            params['height'] = height

        if color is not None: 
            params['color'] = color

        if background is not None: 
            params['background'] = background

        return self.client.call('get', path, {
            'content-type': 'application/json',
        }, params)

    def get_qr(self, text, size = None, margin = None, download = None):
        """Get QR Code"""

        if text is None: 
            raise AppwriteException('Missing required parameter: "text"')

        params = {}
        path = '/avatars/qr'

        if text is not None: 
            params['text'] = text

        if size is not None: 
            params['size'] = size

        if margin is not None: 
            params['margin'] = margin

        if download is not None: 
            params['download'] = download

        return self.client.call('get', path, {
            'content-type': 'application/json',
        }, params)
