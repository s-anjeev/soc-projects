import socket
import random
import struct
import time

# ==========================================================
# Configuration
# ==========================================================

LOCAL_DNS = "192.168.56.102"   # Your local DNS server
DNS_PORT = 53

DOMAIN = "testdomain.com"      # Domain to resolve
REQUESTS_PER_MINUTE = 5         # 5 DNS requests every minute

# Interval between requests (12 seconds)
INTERVAL = 60 / REQUESTS_PER_MINUTE


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
    Build a standard DNS query.

    qtype:
        1  = A
        15 = MX
        16 = TXT
    """

    transaction_id = random.randint(0, 65535)

    header = struct.pack(
        "!HHHHHH",
        transaction_id,
        0x0100,      # Standard recursive query
        1,           # Questions
        0,           # Answers
        0,           # Authority RRs
        0            # Additional RRs
    )

    question = (
        encode_dns_name(domain) +
        struct.pack("!H", qtype) +
        struct.pack("!H", 1)      # IN class
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
            response, _ = sock.recvfrom(4096)
            return len(response)
        except socket.timeout:
            return None

    finally:
        sock.close()


# ==========================================================
# Main
# ==========================================================

def main():

    print("=" * 60)
    print("DNS Traffic Generator")
    print("=" * 60)
    print(f"DNS Server          : {LOCAL_DNS}")
    print(f"Domain              : {DOMAIN}")
    print("Record Type         : A")
    print(f"Requests Per Minute : {REQUESTS_PER_MINUTE}")
    print(f"Interval            : {INTERVAL:.1f} seconds")
    print("\nPress Ctrl+C to stop.")
    print("=" * 60)

    count = 0

    try:
        while True:

            count += 1

            try:
                response_size = send_dns_query(
                    LOCAL_DNS,
                    DOMAIN,
                    qtype=1
                )

                timestamp = time.strftime("%Y-%m-%d %H:%M:%S")

                if response_size is None:
                    print(f"[{count:05}] {timestamp}  {DOMAIN}  ->  No response")
                else:
                    print(f"[{count:05}] {timestamp}  {DOMAIN}  ->  Response received ({response_size} bytes)")

            except Exception as e:
                print(f"[ERROR] {e}")

            time.sleep(INTERVAL)

    except KeyboardInterrupt:
        print("\n\nStopped by user.")


if __name__ == "__main__":
    main()