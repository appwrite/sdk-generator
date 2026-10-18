const playwright = require('playwright');
const handler = require('serve-handler');
const http = require('http');
const path = require('path');
const { exit } = require('process');

const server = http.createServer((request, response) => {
    return handler(request, response)
});

const log = message => {
    if (message.type() == 'log') {
        console.log(message);
    }
};

server.listen(3000, async () => {
    console.log('Test Started');
    const browser = await playwright[process.env.BROWSER].launch({
        args: [
            '--allow-insecure-localhost',
            '--disable-web-security',
        ]
    });
    const context = await browser.newContext();
    try {
        const page = await context.newPage();
        page.on('console', log);
        await page.goto('http://localhost:3000');
        await page.setInputFiles('#file', path.join(__dirname, '/../../../resources/file.png'));
        await page.setInputFiles('#file2', path.join(__dirname, '/../../../resources/large_file.mp4'));
        await page.click('#start');
        await page.waitForFunction(() => window.indexDone === true, null, { timeout: 30000 }).catch(error => console.log(`Index did not finish: ${error}`));
        await page.close();

        const modulePage = await context.newPage();
        modulePage.on('console', log);
        modulePage.on('request', request => {
            if (/chunks\/mqtt/.test(request.url())) {
                console.log(`Module flow loaded ${request.url()}`);
            }
        });
        await modulePage.goto('http://localhost:3000/module.html');
        await modulePage.waitForFunction(() => window.moduleDone === true, null, { timeout: 15000 });
    } catch (error) {
        console.log(`Test failed: ${error}`);
    } finally {
        await browser.close();
        server.close();
        exit(0);
    }
});
