// TLS checks for the RN push transport, run by push.node.js under Node as Platform.OS "android".
//
// On Android, react-native-tcp-socket validates the broker's certificate chain but never its
// hostname, and the shim mirrors that. So any certificate from a trusted root, for any name,
// would be accepted and the CONNECT packet, which carries the session credential, sent to it.
// Each check opens the generated transport to a local TLS server and records what reaches it.
import tls from 'tls';
import { Buffer } from 'buffer';
import { trust } from 'react-native-tcp-socket';
import { createTcpStream } from './src/lib/tcp-stream';
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

export async function checkTls() {
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
