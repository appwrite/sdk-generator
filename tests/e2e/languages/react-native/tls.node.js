// TLS checks for the RN push transport, run by push.node.js under Node as Platform.OS "android".
//
// On Android, react-native-tcp-socket validates the broker's certificate chain but never its
// hostname, and the shim mirrors that. So any certificate from a trusted root, for any name,
// would be accepted and the CONNECT packet, which carries the session credential, sent to it.
// Each check opens the generated transport to a local TLS server and records what reaches it.
import tls from 'tls';
import { Buffer } from 'buffer';
import { trust } from 'react-native-tcp-socket';
import { certificateMatchesHost, createTcpStream } from './src/lib/tcp-stream';
import { TLS_CA, TLS_KEY, TLS_LOCALHOST, TLS_WRONG_HOST } from './tls-fixtures';

const CREDENTIAL = 'CONNECT session-credential';

// Serves `cert`, connects to it as `host`, writes the credential, and reports whether the
// stream failed and how many bytes the server received.
async function handshake(cert, host, tlsInsecure = false) {
    let received = 0;
    const server = tls.createServer({ key: TLS_KEY, cert }, (socket) => {
        socket.on('data', (data) => (received += data.length));
        socket.on('error', () => {});
    });
    await new Promise((resolve) => server.listen(0, resolve));
    trust.ca = TLS_CA;

    const stream = createTcpStream({ host, port: server.address().port, tls: true, tlsInsecure });
    const failed = new Promise((resolve) => stream.on('error', () => resolve(true)));
    stream.write(Buffer.from(CREDENTIAL));
    const error = await Promise.race([failed, new Promise((resolve) => setTimeout(() => resolve(false), 1500))]);

    stream.destroy();
    server.close();
    trust.ca = undefined;
    return { error, received };
}

// [certificate name, host, matches]
const HOSTNAME_RULES = [
    ['push.example.com', 'push.example.com', true],
    ['PUSH.Example.com.', 'push.example.com', true],
    ['*.example.com', 'push.example.com', true],
    ['*.example.com', 'a.push.example.com', false],
    ['*.example.com', 'example.com', false],
    ['p*.example.com', 'push.example.com', false],
    ['*.com', 'example.com', false],
    ['*.0.0.1', '127.0.0.1', false],
    ['127.0.0.1', '127.0.0.1', true],
    ['example.com', 'push.example.com', false],
    [undefined, 'push.example.com', false],
];

export async function checkTls() {
    const broken = HOSTNAME_RULES.filter(([name, host, matches]) => certificateMatchesHost(name, host) !== matches);
    console.log(broken.length === 0 ? 'Push TLS hostname rules:passed' : `Push TLS hostname rules:failed (${JSON.stringify(broken)})`);

    // Trusted root, matching name: the credential goes through.
    const match = await handshake(TLS_LOCALHOST, 'localhost');
    console.log(
        !match.error && match.received === CREDENTIAL.length
            ? 'Push TLS hostname match:passed'
            : `Push TLS hostname match:failed (${JSON.stringify(match)})`,
    );

    // Trusted root, another name: refused before a single byte is written.
    const mismatch = await handshake(TLS_WRONG_HOST, 'localhost');
    console.log(
        mismatch.error && mismatch.received === 0
            ? 'Push TLS hostname mismatch:passed'
            : `Push TLS hostname mismatch:failed (${JSON.stringify(mismatch)})`,
    );

    // ?tlsInsecure=true opts out of verification, hostname included.
    const insecure = await handshake(TLS_WRONG_HOST, 'localhost', true);
    console.log(
        !insecure.error && insecure.received === CREDENTIAL.length
            ? 'Push TLS insecure:passed'
            : `Push TLS insecure:failed (${JSON.stringify(insecure)})`,
    );
}
