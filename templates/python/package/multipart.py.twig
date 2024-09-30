from email.parser import BytesParser
from email.policy import default
from .payload import Payload
import json

class MultipartParser:
    def __init__(self, multipart_bytes, content_type):
        self.multipart_bytes = multipart_bytes
        self.content_type = content_type
        self.parts = {}
        self.parse()

    def parse(self):
        # Create a message object
        headers = f'Content-Type: {self.content_type}\r\n\r\n'.encode('ascii')
        msg = BytesParser(policy=default).parsebytes(headers + self.multipart_bytes)
        
        # Process each part
        for part in msg.walk():
            if part.is_multipart():
                continue
            
            # Get the name from Content-Disposition
            content_disposition = part.get("Content-Disposition", "")
            name = part.get_param("name", header="content-disposition")
            if not name:
                name = f"unnamed_part_{len(self.parts)}"
            
            # Store the parsed data
            self.parts[name] = {
                "contents": part.get_payload(decode=True),
                "headers": dict(part.items())
            }

    def to_dict(self):
        result = {}
        for name, part in self.parts.items():
            if name == "responseBody":
                result[name] = Payload.from_binary(part["contents"])
            elif name == "responseHeaders":
                headers_str = part["contents"].decode('utf-8', errors='replace')
                result[name] = json.loads(headers_str)
            elif name == "responseStatusCode":
                result[name] = int(part["contents"])
            elif name == "duration":
                result[name] = float(part["contents"])
            else:
                result[name] = part["contents"].decode('utf-8', errors='replace')
        return result