const jar = new Map();

module.exports = {
    get: async (url) => ({ ...(jar.get(url) ?? {}) }),
    set: async (url, cookie) => {
        jar.set(url, { ...(jar.get(url) ?? {}), [cookie.name]: cookie });
        return true;
    },
};
