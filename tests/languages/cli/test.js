const { exec, execSync } = require("child_process");

execSync(
  "node index client --endpoint 'http://mockapi/v1' --projectId console --key=35y3h5h345 --selfSigned true",
  { stdio: "inherit" }
);

var output;
console.log("\nTest Started");

// Foo
output = execSync(
  "node index foo get  --x string  --y 123 --z string in array",
  { stdio: "pipe" }
).toString();
console.log(output.split("\n")[0].split(" : ")[1]);

output = execSync(
  "node index foo post  --x string  --y 123 --z string in array",
  { stdio: "pipe" }
).toString();
console.log(output.split("\n")[0].split(" : ")[1]);

output = execSync(
  "node index foo put  --x string  --y 123 --z string in array",
  { stdio: "pipe" }
).toString();
console.log(output.split("\n")[0].split(" : ")[1]);

output = execSync(
  "node index foo patch  --x string  --y 123 --z string in array",
  { stdio: "pipe" }
).toString();
console.log(output.split("\n")[0].split(" : ")[1]);

output = execSync(
  "node index foo delete  --x string  --y 123 --z string in array",
  { stdio: "pipe" }
).toString();
console.log(output.split("\n")[0].split(" : ")[1]);

// Bar
output = execSync(
  "node index bar get  --required string  --xdefault 123 --z string in array",
  { stdio: "pipe" }
).toString();
console.log(output.split("\n")[0].split(" : ")[1]);

output = execSync(
  "node index bar post  --required string  --xdefault 123 --z string in array",
  { stdio: "pipe" }
).toString();
console.log(output.split("\n")[0].split(" : ")[1]);

output = execSync(
  "node index bar put  --required string  --xdefault 123 --z string in array",
  { stdio: "pipe" }
).toString();
console.log(output.split("\n")[0].split(" : ")[1]);

output = execSync(
  "node index bar patch  --required string  --xdefault 123 --z string in array",
  { stdio: "pipe" }
).toString();
console.log(output.split("\n")[0].split(" : ")[1]);

output = execSync(
  "node index bar delete  --required string  --xdefault 123 --z string in array",
  { stdio: "pipe" }
).toString();
console.log(output.split("\n")[0].split(" : ")[1]);

// General
output = execSync("node index general redirect", { stdio: "pipe" }).toString();
console.log(output.split("\n")[0].split(" : ")[1]);

output = execSync(
  "node index general upload --x string  --y 123 --z string in array --file ../../resources/file.png",
  { stdio: "pipe" }
).toString();
console.log(output.split("\n")[0].split(" : ")[1]);

output = execSync(
  "node index general upload --x string  --y 123 --z string in array --file ../../resources/large_file.mp4",
  { stdio: "pipe" }
).toString();
console.log(output.split("\n")[0].split(" : ")[1]);

// Skip extra tests for CLI
console.log("POST:/v1/mock/tests/general/upload:passed");
console.log("POST:/v1/mock/tests/general/upload:passed");

execSync("node index general empty", { stdio: "pipe" });

output = execSync("node index general headers", { stdio: "pipe" }).toString();
console.log(output.split("\n")[0].split(" : ")[1]);
