const playwright = require('playwright');
const handler = require('serve-handler');
const http = require('http');

const server = http.createServer((request, response) => {
    return handler(request, response)
});

server.listen(3000, async () => {
    const browser = await playwright[process.env.BROWSER].launch({
        args: ['--disable-dev-shm-usage']
    });
    const context = await browser.newContext();
    const page = await context.newPage();
    page.on('console', message => {
        if (message.type() == 'log') {
            console.log(message);
        }
    });
    await page.goto('http://localhost:3000');
    setTimeout(async () => {
        await browser.close();
        server.close();
    }, 10000);
});
