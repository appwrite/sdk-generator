const { exec, execSync } = require('child_process');

execSync("node index client --endpoint 'http://appwrite.io/v1' --projectId 6013fbefdfa41 --key=35y3h5h345 --selfSigned true", { stdio: 'inherit' });

var output;
console.log('\nTest Started');

// Foo
output = execSync("node index foo get  --x string  --y 123 --z string in array", { stdio: 'pipe'}).toString();
console.log(output.split('\n')[1].split('": "')[1].slice(0, -1));

output = execSync("node index foo post  --x string  --y 123 --z string in array", { stdio: 'pipe'}).toString();
console.log(output.split('\n')[1].split('": "')[1].slice(0, -1));

output = execSync("node index foo put  --x string  --y 123 --z string in array", { stdio: 'pipe'}).toString();
console.log(output.split('\n')[1].split('": "')[1].slice(0, -1));

output = execSync("node index foo patch  --x string  --y 123 --z string in array", { stdio: 'pipe'}).toString();
console.log(output.split('\n')[1].split('": "')[1].slice(0, -1));

output = execSync("node index foo delete  --x string  --y 123 --z string in array", { stdio: 'pipe'}).toString();
console.log(output.split('\n')[1].split('": "')[1].slice(0, -1));

// Bar
output = execSync("node index bar get  --required string  --xdefault 123 --z string in array", { stdio: 'pipe'}).toString();
console.log(output.split('\n')[1].split('": "')[1].slice(0, -1));

output = execSync("node index bar post  --required string  --xdefault 123 --z string in array", { stdio: 'pipe'}).toString();
console.log(output.split('\n')[1].split('": "')[1].slice(0, -1));

output = execSync("node index bar put  --required string  --xdefault 123 --z string in array", { stdio: 'pipe'}).toString();
console.log(output.split('\n')[1].split('": "')[1].slice(0, -1));

output = execSync("node index bar patch  --required string  --xdefault 123 --z string in array", { stdio: 'pipe'}).toString();
console.log(output.split('\n')[1].split('": "')[1].slice(0, -1));

output = execSync("node index bar delete  --required string  --xdefault 123 --z string in array", { stdio: 'pipe'}).toString();
console.log(output.split('\n')[1].split('": "')[1].slice(0, -1));

// General
output = execSync("node index general redirect", { stdio: 'pipe'}).toString();
console.log(output.split('\n')[1].split('": "')[1].slice(0, -1));

output = execSync("node index general upload --x string  --y 123 --z string in array --file ../../resources/file.png", { stdio: 'pipe'}).toString();
console.log(output.split('\n')[1].split('": "')[1].slice(0, -1));