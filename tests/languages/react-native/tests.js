const playwright = require('playwright');
const handler = require('serve-handler');
const http = require('http');
const { exit } = require('process');

const server = http.createServer((request, response) => {
    return handler(request, response);
});

server.listen(3000, async () => {
    console.log('Test Started');
    const browser = await playwright[process.env.BROWSER].launch({
        args: [
            '--allow-insecure-localhost',
            '--disable-web-security',
        ],
    });
    const context = await browser.newContext();
    const page = await context.newPage();
    page.on('console', message => {
        if (message.type() === 'log') {
            console.log(message.text());
        }
    });
    page.on('pageerror', err => {
        console.log('PAGE ERROR: ' + err.message);
    });
    await page.goto('http://localhost:3000');

    setTimeout(async () => {
        await browser.close();
        server.close();
        exit(0);
    }, 20000);
});
