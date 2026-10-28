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
                ca: trust.ca,
                // As on Android: the native socket validates the chain but never the hostname.
                checkServerIdentity: () => undefined,
            },
            callback,
        );
        // As the library: a Promise of the peer certificate, with only the subject CN as a name.
        const peer = socket.getPeerCertificate.bind(socket);
        socket.getPeerCertificate = async () => ({ subject: { CN: peer().subject?.CN } });
        return socket;
    },
};
