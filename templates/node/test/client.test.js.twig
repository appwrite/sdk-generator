const { Client } = require("../dist/client");
const { fetch: mockedFetch, Response, Dispatcher } = require("undici");

jest.mock("undici", () => ({ ...jest.requireActual("undici"), fetch: jest.fn() }));

describe("Client", () => {
    beforeEach(() => {
        mockedFetch.mockReset();
        mockedFetch.mockImplementation(() => Response.json({ ok: true }));
    });

    test("does not include a dispatcher by default", async () => {
        const client = new Client();

        await client.call("GET", new URL("https://cloud.appwrite.io/v1/health"));

        expect(mockedFetch).toHaveBeenCalledTimes(1);
        expect(mockedFetch.mock.calls[0][1]).not.toHaveProperty("dispatcher");
    });

    test("includes a dispatcher for self-signed requests", async () => {
        const client = new Client();
        client.setSelfSigned(true);

        try {
            await client.call("GET", new URL("https://self-hosted.example/v1/health"));

            expect(mockedFetch).toHaveBeenCalledTimes(1);
            expect(mockedFetch.mock.calls[0][1].dispatcher).toBeInstanceOf(Dispatcher);
        } finally {
            client.setSelfSigned(false);
        }
    });
});
