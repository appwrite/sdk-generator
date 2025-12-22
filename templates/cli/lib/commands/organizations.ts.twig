import { sdkForProject } from '../sdks';
import { parse } from '../parser';
import { showConsoleLink } from '../utils';
import Client from '../client';

interface OrganizationsListRequestParams {
    queries?: string[];
    search?: string;
    parseOutput?: boolean;
    sdk?: Client;
    console?: boolean;
}

export const organizationsList = async ({
    queries, 
    search, 
    parseOutput = true, 
    sdk = undefined, 
    console: showConsole
}: OrganizationsListRequestParams): Promise<any> => {
    let client = !sdk ? await sdkForProject() : sdk;
    let apiPath = '/organizations';
    let payload: Record<string, any> = {};
    
    if (typeof queries !== 'undefined') {
        payload['queries'] = queries;
    }
    if (typeof search !== 'undefined') {
        payload['search'] = search;
    }

    let response = await client.call('get', apiPath, {
        'content-type': 'application/json',
    }, payload);

    if (parseOutput) {
        if(showConsole) {
            showConsoleLink('organizations', 'list');
        } else {
            parse(response)
        }
    }

    return response;
}

