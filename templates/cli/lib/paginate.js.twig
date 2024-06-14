const paginate = async (action, args = {}, limit = 100, wrapper = '') => {
    let pageNumber = 0;
    let results = [];
    let total = 0;

    while (true) {
        const offset = pageNumber * limit;
        // Merge the limit and offset into the args
        const response = await action({
            ...args,
            queries: [
                JSON.stringify({ method: 'limit', values: [limit] }),
                JSON.stringify({ method: 'offset', values: [offset] })
            ]
        });

        if (wrapper === '') {
            if (response.length === 0) {
                break;
            }
            results = results.concat(response);
        } else {
            if (response[wrapper].length === 0) {
                break;
            }
            results = results.concat(response[wrapper]);
        }

        total = response.total;

        if (results.length >= total) {
            break;
        }

        pageNumber++;
    }

    if (wrapper === '') {
        return results;
    }

    return {
        [wrapper]: results,
        total,
    };
}

module.exports = {
    paginate
};
