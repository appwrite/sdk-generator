// Node stand-in for react-native: the Android platform. NativeModules starts empty, as in Expo Go;
// a test can install a stand-in native module there. NativeEventEmitter listens on the module's own
// `emitter` (a Node EventEmitter), which the stand-in uses to send events.
const { EventEmitter } = require('events');

class NativeEventEmitter {
    constructor(nativeModule) {
        this.emitter = (nativeModule && nativeModule.emitter) || new EventEmitter();
    }

    addListener(event, listener) {
        this.emitter.on(event, listener);
        return { remove: () => this.emitter.off(event, listener) };
    }
}

// PermissionsAndroid records each permission requested and answers that it was granted.
const PermissionsAndroid = {
    PERMISSIONS: { POST_NOTIFICATIONS: 'android.permission.POST_NOTIFICATIONS' },
    requested: [],
    check: async () => false,
    request: async (permission) => {
        PermissionsAndroid.requested.push(permission);
        return 'granted';
    },
};

module.exports = { AppState: { currentState: 'background' }, Platform: { OS: 'android', Version: 34 }, NativeModules: {}, NativeEventEmitter, PermissionsAndroid };
