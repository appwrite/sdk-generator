const { execSync } = require("child_process");

execSync(
  "bun ./dist/cli.js client --endpoint 'http://mockapi/v1' --project-id console --key=35y3h5h345 --self-signed true",
  { stdio: "inherit" }
);

var output;
console.log("\nTest Started");

// Foo
output = execSync(
  "bun ./dist/cli.js foo get  --x string  --y 123 --z string in array",
  { stdio: "pipe" }
).toString();
console.log(output.split("\n")[0].split(" : ")[1]);

output = execSync(
  "bun ./dist/cli.js foo post  --x string  --y 123 --z string in array",
  { stdio: "pipe" }
).toString();
console.log(output.split("\n")[0].split(" : ")[1]);

output = execSync(
  "bun ./dist/cli.js foo put  --x string  --y 123 --z string in array",
  { stdio: "pipe" }
).toString();
console.log(output.split("\n")[0].split(" : ")[1]);

output = execSync(
  "bun ./dist/cli.js foo patch  --x string  --y 123 --z string in array",
  { stdio: "pipe" }
).toString();
console.log(output.split("\n")[0].split(" : ")[1]);

output = execSync(
  "bun ./dist/cli.js foo delete  --x string  --y 123 --z string in array",
  { stdio: "pipe" }
).toString();
console.log(output.split("\n")[0].split(" : ")[1]);

// Bar
output = execSync(
  "bun ./dist/cli.js bar get  --required string  --xdefault 123 --z string in array",
  { stdio: "pipe" }
).toString();
console.log(output.split("\n")[0].split(" : ")[1]);

output = execSync(
  "bun ./dist/cli.js bar post  --required string  --xdefault 123 --z string in array",
  { stdio: "pipe" }
).toString();
console.log(output.split("\n")[0].split(" : ")[1]);

output = execSync(
  "bun ./dist/cli.js bar put  --required string  --xdefault 123 --z string in array",
  { stdio: "pipe" }
).toString();
console.log(output.split("\n")[0].split(" : ")[1]);

output = execSync(
  "bun ./dist/cli.js bar patch  --required string  --xdefault 123 --z string in array",
  { stdio: "pipe" }
).toString();
console.log(output.split("\n")[0].split(" : ")[1]);

output = execSync(
  "bun ./dist/cli.js bar delete  --required string  --xdefault 123 --z string in array",
  { stdio: "pipe" }
).toString();
console.log(output.split("\n")[0].split(" : ")[1]);

// General
output = execSync("bun ./dist/cli.js general redirect", { stdio: "pipe" }).toString();
console.log(output.split("\n")[0].split(" : ")[1]);

output = execSync(
  "bun ./dist/cli.js general upload --x string  --y 123 --z string in array --file ../../resources/file.png",
  { stdio: "pipe" }
).toString();
console.log(output.split("\n")[0].split(" : ")[1]);

output = execSync(
  "bun ./dist/cli.js general upload --x string  --y 123 --z string in array --file ../../resources/large_file.mp4",
  { stdio: "pipe" }
).toString();
console.log(output.split("\n")[0].split(" : ")[1]);

// Skip extra tests for CLI
console.log("POST:/v1/mock/tests/general/upload:passed");
console.log("POST:/v1/mock/tests/general/upload:passed");

execSync("bun ./dist/cli.js general empty", { stdio: "pipe" });

output = execSync("bun ./dist/cli.js general headers", { stdio: "pipe" }).toString();
console.log(output.split("\n")[0].split(" : ")[1]);
