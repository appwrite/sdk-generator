const { sdkForProject } = require('../sdks')
const { parse } = require('../parser')
const { showConsoleLink } = require('../utils.js');

/**
 * @typedef {Object} OrganizationsListRequestParams
 * @property {string[]} queries Array of query strings generated using the Query class provided by the SDK. [Learn more about queries](https://appwrite.io/docs/queries). Maximum of 100 queries are allowed, each 4096 characters long. You may filter on the following attributes: name, total, billingPlan
 * @property {string} search Search term to filter your list results. Max length: 256 chars.
 * @property {boolean} parseOutput
 * @property {libClient | undefined} sdk
 */

/**
 * @param {OrganizationsListRequestParams} params
 */
const organizationsList = async ({queries, search, parseOutput = true, sdk = undefined, console}) => {
    let client = !sdk ? await sdkForProject() :
    sdk;
    let apiPath = '/organizations';
    let payload = {};
    if (typeof queries !== 'undefined') {
        payload['queries'] = queries;
    }
    if (typeof search !== 'undefined') {
        payload['search'] = search;
    }

    let response = undefined;

    response = await client.call('get', apiPath, {
        'content-type': 'application/json',
    }, payload);

    if (parseOutput) {
        if(console) {
            showConsoleLink('organizations', 'list');
         } else {
            parse(response)
        }
    }

    return response;

}

module.exports = {
    organizationsList
}