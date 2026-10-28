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
// What the server sends as soon as the handshake completes, as an impostor's PUBLISH would.
const INJECTED = 'PUBLISH injected';

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
// credential, and reports whether the stream failed, how many bytes the server received, and how
// many of the server's bytes the stream delivered.
async function handshake(cert, host, { tlsInsecure = false, native = nativeTls } = {}) {
    let received = 0;
    let delivered = 0;
    const server = tls.createServer({ key: TLS_KEY, cert }, (socket) => {
        socket.write(INJECTED);
        socket.on('data', (data) => (received += data.length));
        socket.on('error', () => {});
    });
    await new Promise((resolve) => server.listen(0, resolve));
    trust.ca = TLS_CA;
    const previous = NativeModules.AppwritePush;
    NativeModules.AppwritePush = native;

    const stream = createTcpStream({ host, port: server.address().port, tls: true, tlsInsecure });
    const failed = new Promise((resolve) => stream.on('error', () => resolve(true)));
    stream.on('data', (data) => (delivered += data.length));
    stream.write(Buffer.from(CREDENTIAL));
    const error = await Promise.race([failed, new Promise((resolve) => setTimeout(() => resolve(false), 1500))]);

    stream.destroy();
    server.close();
    trust.ca = undefined;
    NativeModules.AppwritePush = previous;
    return { error, received, delivered };
}

// A server that floods the socket while the native check is still running. Reports whether the
// server managed to hand the whole flood to the network before the check finished (only possible
// if the transport kept reading, and holding, everything it was sent) and how much the stream
// delivered once verified.
async function floodWhileVerifying() {
    const FLOOD = 64 * 1024 * 1024;
    let flushed = false;
    let flushedBeforeVerified = null;
    let delivered = 0;
    const server = tls.createServer({ key: TLS_KEY, cert: TLS_LOCALHOST }, (socket) => {
        socket.on('error', () => {});
        socket.write(Buffer.alloc(FLOOD), (err) => (flushed ||= !err));
    });
    await new Promise((resolve) => server.listen(0, resolve));
    trust.ca = TLS_CA;
    const previous = NativeModules.AppwritePush;
    NativeModules.AppwritePush = {
        verifiedCertificate: async (host, port) => {
            const verified = await nativeTls.verifiedCertificate(host, port);
            await new Promise((resolve) => setTimeout(resolve, 1000));
            flushedBeforeVerified = flushed;
            return verified;
        },
    };

    const stream = createTcpStream({ host: 'localhost', port: server.address().port, tls: true, tlsInsecure: false });
    stream.on('error', () => {});
    const done = new Promise((resolve) =>
        stream.on('data', (data) => (delivered += data.length) === FLOOD && resolve()),
    );
    await Promise.race([done, new Promise((resolve) => setTimeout(resolve, 10000))]);

    stream.destroy();
    server.close();
    trust.ca = undefined;
    NativeModules.AppwritePush = previous;
    return { flushedBeforeVerified, delivered, expected: FLOOD };
}

// Refused: nothing written to the server and nothing it sent handed on.
const refused = (outcome) => outcome.error && outcome.received === 0 && outcome.delivered === 0;
const accepted = (outcome) =>
    !outcome.error && outcome.received === CREDENTIAL.length && outcome.delivered === INJECTED.length;

const report = (name, passed, outcome) =>
    console.log(passed ? `Push TLS ${name}:passed` : `Push TLS ${name}:failed (${JSON.stringify(outcome)})`);

export async function checkTls() {
    // Trusted root, matching name: the credential goes through, and the server's bytes come back.
    const match = await handshake(TLS_LOCALHOST, 'localhost');
    report('hostname match', accepted(match), match);

    // Trusted root, another name: refused before a single byte is written or read.
    const mismatch = await handshake(TLS_WRONG_HOST, 'localhost');
    report('hostname mismatch', refused(mismatch), mismatch);

    // Trusted root, CN names the host but the subjectAltName does not: the SAN wins, so refused.
    const sanElsewhere = await handshake(TLS_SAN_ELSEWHERE, 'localhost');
    report('SAN mismatch', refused(sanElsewhere), sanElsewhere);

    // The socket's certificate is not the one the native module verified: refused.
    const other = await handshake(TLS_LOCALHOST, 'localhost', {
        native: { verifiedCertificate: async () => '00:11:22' },
    });
    report('other certificate', refused(other), other);

    // Without the native module nothing can verify the hostname: refused.
    const unverifiable = await handshake(TLS_LOCALHOST, 'localhost', { native: null });
    report('no native module', refused(unverifiable), unverifiable);

    // Flooded while verifying: the transport stops reading instead of holding the flood, and
    // delivers all of it once verified.
    const flood = await floodWhileVerifying();
    report('flood while verifying', flood.flushedBeforeVerified === false && flood.delivered === flood.expected, flood);

    // ?tlsInsecure=true opts out of verification, hostname included.
    const insecure = await handshake(TLS_WRONG_HOST, 'localhost', { tlsInsecure: true });
    report('insecure', accepted(insecure), insecure);
}
