// Node stand-in for expo-notifications: a test sets the response that launched the app with
// __test.lastResponse, delivers later taps with __test.respond and reads the notifications
// posted from __test.scheduled.
const listeners = new Set();

const __test = {
    lastResponse: null,
    scheduled: [],
    respond(response) {
        for (const listener of listeners) {
            listener(response);
        }
    },
};

module.exports = {
    __test,
    scheduleNotificationAsync: async (request) => {
        __test.scheduled.push(request);
        return `e2e-notification-${__test.scheduled.length}`;
    },
    getLastNotificationResponseAsync: async () => __test.lastResponse,
    clearLastNotificationResponseAsync: async () => {
        __test.lastResponse = null;
    },
    addNotificationResponseReceivedListener: (listener) => {
        listeners.add(listener);
        return { remove: () => listeners.delete(listener) };
    },
};
