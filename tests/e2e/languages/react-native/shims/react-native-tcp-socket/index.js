// Node stand-in for react-native-tcp-socket, used only by the RN push e2e (push.node.js).
// tcp-stream.ts consumes a strict subset of Node's net.Socket / tls.TLSSocket API
// (createConnection/connectTLS returning a socket with setNoDelay/write/destroy/resume/pause
// and 'connect'/'secureConnect'/'data'/'error'/'close' events), so these one-line adapters
// let the real generated transport run over a real TCP socket to the broker on 1883.
const net = require('net');
const tls = require('tls');

module.exports = {
    createConnection: (options, callback) =>
        net.createConnection({ host: options.host, port: options.port }, callback),
    connectTLS: (options, callback) =>
        tls.connect(
            {
                host: options.host,
                port: options.port,
                rejectUnauthorized: options.rejectUnauthorized,
            },
            callback,
        ),
};
