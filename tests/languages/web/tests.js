const playwright = require('playwright');
const handler = require('serve-handler');
const http = require('http');
const path = require('path');
const { exit } = require('process');

const server = http.createServer((request, response) => {
    return handler(request, response)
});

server.listen(3000, async () => {
    console.log('Test Started');
    const browser = await playwright[process.env.BROWSER].launch({
        args: [
            "--allow-insecure-localhost",
            "--disable-web-security",
        ]
    });
    const context = await browser.newContext();
    const page = await context.newPage();
    page.on('console', message => {
        if (message.type() == 'log') {
            console.log(message);
        }
    });
    await page.goto('http://localhost:3000');
    await page.setInputFiles('#file', path.join(__dirname, '/../../resources/file.png'));
    await page.setInputFiles('#file2', path.join(__dirname, '/../../resources/large_file.mp4'));
    await page.click('#start');

    setTimeout(async () => {
        await browser.close();
        server.close();
        exit(0);
    }, 15000);
});
