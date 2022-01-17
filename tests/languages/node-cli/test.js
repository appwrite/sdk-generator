const childProcess = require('child_process');

const baseCommand = '../../sdks/node-cli/index'
async function start() {
    var output;

    console.log("Hello world")
    output = childProcess.execSync('node index', { stdio: 'pipe' });
    console.log(output.toString());

}

start();