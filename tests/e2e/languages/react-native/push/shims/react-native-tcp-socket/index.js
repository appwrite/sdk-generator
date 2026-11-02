// Node stand-in for react-native-tcp-socket, used only by the RN push e2e (push.node.js).
// tcp-stream.ts consumes a strict subset of Node's net.Socket / tls.TLSSocket API
// (createConnection/connectTLS returning a socket with setNoDelay/write/destroy/resume/pause
// and 'connect'/'secureConnect'/'data'/'error'/'close' events), so these one-line adapters
// let the real generated transport run over a real TCP socket to the broker on 1883.
const net = require('net');
const tls = require('tls');

// Roots connectTLS trusts; a test sets `trust.ca` to its own CA (undefined = Node's defaults).
const trust = { ca: undefined };

module.exports = {
    trust,
    createConnection: (options, callback) =>
        net.createConnection({ host: options.host, port: options.port }, callback),
    connectTLS: (options, callback) => {
        const socket = tls.connect(
            {
                host: options.host,
                port: options.port,
                rejectUnauthorized: options.rejectUnauthorized,
                // As on Android: a string `ca` is the only trusted certificate, and it need not be
                // a root (Android accepts a chain whose leaf is itself trusted).
                ca: options.ca ?? trust.ca,
                allowPartialTrustChain: options.ca !== undefined,
                // As on Android: the native socket validates the chain but never the hostname.
                checkServerIdentity: () => undefined,
            },
            callback,
        );
        // As the library, once connected (Node's handshake reads the original): a Promise of the
        // peer certificate, which it builds by reading an RSA key's modulus, so any other key
        // (ECDSA) rejects.
        socket.once('secureConnect', () => {
            const peer = socket.getPeerCertificate.bind(socket);
            socket.getPeerCertificate = async () => {
                const certificate = peer();
                if (!certificate.modulus) {
                    throw new Error('Error processing certificate');
                }
                return { subject: { CN: certificate.subject?.CN }, fingerprint256: certificate.fingerprint256 };
            };
        });
        return socket;
    },
};
