// Node stand-in for expo-notifications: a test sets the response that launched the app with
// __test.lastResponse and delivers later taps with __test.respond.
const listeners = new Set();

const __test = {
    lastResponse: null,
    respond(response) {
        for (const listener of listeners) {
            listener(response);
        }
    },
};

module.exports = {
    __test,
    getLastNotificationResponseAsync: async () => __test.lastResponse,
    clearLastNotificationResponseAsync: async () => {
        __test.lastResponse = null;
    },
    addNotificationResponseReceivedListener: (listener) => {
        listeners.add(listener);
        return { remove: () => listeners.delete(listener) };
    },
};
