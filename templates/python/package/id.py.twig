from datetime import datetime
import math
import os

class ID:
    # Generate an hex ID based on timestamp
    # Recreated from https://www.php.net/manual/en/function.uniqid.php
    @staticmethod
    def __hex_timestamp():
        now = datetime.now()
        sec = int(now.timestamp())
        usec = (now.microsecond % 1000)
        hex_timestamp = f'{sec:08x}{usec:05x}'
        return hex_timestamp

    @staticmethod
    def custom(id):
        return id

    # Generate a unique ID with padding to have a longer ID
    @staticmethod
    def unique(padding = 7):
        base_id = ID.__hex_timestamp()
        random_bytes = os.urandom(math.ceil(padding / 2))
        random_padding = random_bytes.hex()[:padding]
        return base_id + random_padding
