const playwright = require('playwright');
const handler = require('serve-handler');
const http = require('http');

const server = http.createServer((request, response) => handler(request, response));

server.listen(3000, async () => {
    const browser = await playwright.chromium.launch({
      args: ['--disable-dev-shm-usage']
    });
    const context = await browser.newContext();
    const page = await context.newPage();
    function logRequest(interceptedRequest) {
      console.log('A request was made:', interceptedRequest.url());
    }
    await page.goto('http://localhost:3000');
    page.on('request', logRequest);
    page.on("console", message => console.log(message));

    await browser.close();
});
