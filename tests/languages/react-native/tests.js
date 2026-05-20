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
    let pageErrored = false;
    page.on('console', message => {
        if (message.type() === 'log') {
            console.log(message.text());
        }
    });
    page.on('pageerror', err => {
        pageErrored = true;
        console.log('PAGE ERROR: ' + err.message);
    });
    await page.goto('http://localhost:3000');

    let timedOut = false;
    try {
        await page.waitForFunction(() => window.__APPWRITE_TEST_DONE__ === true, {
            timeout: 20000,
        });
    } catch (e) {
        timedOut = true;
        console.log('TEST RUNNER TIMEOUT: ' + e.message);
    }

    await browser.close();
    server.close();
    exit(pageErrored || timedOut ? 1 : 0);
});
