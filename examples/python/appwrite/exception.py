class AppwriteException(Exception):
    def __init__(self, message, code = 0, response = None):
        self.message = message
        self.code = code
        self.response = response
        super().__init__(self.message)