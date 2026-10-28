// TLS checks for the RN push transport, run by push.node.js under Node as Platform.OS "android".
//
// On Android, react-native-tcp-socket validates the broker's certificate chain but never its
// hostname, and the shim mirrors that. So any certificate from a trusted root, for any name,
// would be accepted and the CONNECT packet, which carries the session credential, sent to it.
// Each check opens the generated transport to a local TLS server and records what reaches it.
import tls from 'tls';
import { Buffer } from 'buffer';
import { NativeModules } from 'react-native';
import { trust } from 'react-native-tcp-socket';
import { createTcpStream } from './src/lib/tcp-stream';
import { TLS_CA, TLS_KEY, TLS_LOCALHOST, TLS_SAN_ELSEWHERE, TLS_WRONG_HOST } from './tls-fixtures';

const CREDENTIAL = 'CONNECT session-credential';

// Stand-in for the SDK's native Android module: verifies host:port as HTTPS does (Node checks the
// subjectAltName and ignores the CN when there is one) and returns that certificate's fingerprint.
const nativeTls = {
    verifiedCertificate: (host, port) =>
        new Promise((resolve, reject) => {
            const socket = tls.connect({ host, port, servername: host, ca: TLS_CA }, () => {
                resolve(socket.getPeerCertificate().fingerprint256);
                socket.destroy();
            });
            socket.on('error', reject);
        }),
};

// Serves `cert`, connects to it as `host` with `native` as the SDK's native module, writes the
// credential, and reports whether the stream failed and how many bytes the server received.
async function handshake(cert, host, { tlsInsecure = false, native = nativeTls } = {}) {
    let received = 0;
    const server = tls.createServer({ key: TLS_KEY, cert }, (socket) => {
        socket.on('data', (data) => (received += data.length));
        socket.on('error', () => {});
    });
    await new Promise((resolve) => server.listen(0, resolve));
    trust.ca = TLS_CA;
    const previous = NativeModules.AppwritePush;
    NativeModules.AppwritePush = native;

    const stream = createTcpStream({ host, port: server.address().port, tls: true, tlsInsecure });
    const failed = new Promise((resolve) => stream.on('error', () => resolve(true)));
    stream.write(Buffer.from(CREDENTIAL));
    const error = await Promise.race([failed, new Promise((resolve) => setTimeout(() => resolve(false), 1500))]);

    stream.destroy();
    server.close();
    trust.ca = undefined;
    NativeModules.AppwritePush = previous;
    return { error, received };
}

const report = (name, passed, outcome) =>
    console.log(passed ? `Push TLS ${name}:passed` : `Push TLS ${name}:failed (${JSON.stringify(outcome)})`);

export async function checkTls() {
    // Trusted root, matching name: the credential goes through.
    const match = await handshake(TLS_LOCALHOST, 'localhost');
    report('hostname match', !match.error && match.received === CREDENTIAL.length, match);

    // Trusted root, another name: refused before a single byte is written.
    const mismatch = await handshake(TLS_WRONG_HOST, 'localhost');
    report('hostname mismatch', mismatch.error && mismatch.received === 0, mismatch);

    // Trusted root, CN names the host but the subjectAltName does not: the SAN wins, so refused.
    const sanElsewhere = await handshake(TLS_SAN_ELSEWHERE, 'localhost');
    report('SAN mismatch', sanElsewhere.error && sanElsewhere.received === 0, sanElsewhere);

    // The socket's certificate is not the one the native module verified: refused.
    const other = await handshake(TLS_LOCALHOST, 'localhost', {
        native: { verifiedCertificate: async () => '00:11:22' },
    });
    report('other certificate', other.error && other.received === 0, other);

    // Without the native module nothing can verify the hostname: refused.
    const unverifiable = await handshake(TLS_LOCALHOST, 'localhost', { native: null });
    report('no native module', unverifiable.error && unverifiable.received === 0, unverifiable);

    // ?tlsInsecure=true opts out of verification, hostname included.
    const insecure = await handshake(TLS_WRONG_HOST, 'localhost', { tlsInsecure: true });
    report('insecure', !insecure.error && insecure.received === CREDENTIAL.length, insecure);
}
