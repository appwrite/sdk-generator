import "package:dart_appwrite/service.dart";
import "package:dart_appwrite/client.dart";

class Projects extends Service {
     
     Projects(Client client): super(client);

    listProjects() async {
       String path = '/projects';

       var params = {
       };

       return await this.client.call('get', path: path, params: params);
    }
    createProject({name, teamId, description = null, logo = null, url = null, legalName = null, legalCountry = null, legalState = null, legalCity = null, legalAddress = null, legalTaxId = null}) async {
       String path = '/projects';

       var params = {
         'name': name,
         'teamId': teamId,
         'description': description,
         'logo': logo,
         'url': url,
         'legalName': legalName,
         'legalCountry': legalCountry,
         'legalState': legalState,
         'legalCity': legalCity,
         'legalAddress': legalAddress,
         'legalTaxId': legalTaxId,
       };

       return await this.client.call('post', path: path, params: params);
    }
    getProject({projectId}) async {
       String path = '/projects/{projectId}'.replaceAll(RegExp('{projectId}'), projectId);

       var params = {
       };

       return await this.client.call('get', path: path, params: params);
    }
    updateProject({projectId, name, description = null, logo = null, url = null, legalName = null, legalCountry = null, legalState = null, legalCity = null, legalAddress = null, legalTaxId = null}) async {
       String path = '/projects/{projectId}'.replaceAll(RegExp('{projectId}'), projectId);

       var params = {
         'name': name,
         'description': description,
         'logo': logo,
         'url': url,
         'legalName': legalName,
         'legalCountry': legalCountry,
         'legalState': legalState,
         'legalCity': legalCity,
         'legalAddress': legalAddress,
         'legalTaxId': legalTaxId,
       };

       return await this.client.call('patch', path: path, params: params);
    }
    deleteProject({projectId}) async {
       String path = '/projects/{projectId}'.replaceAll(RegExp('{projectId}'), projectId);

       var params = {
       };

       return await this.client.call('delete', path: path, params: params);
    }
    listKeys({projectId}) async {
       String path = '/projects/{projectId}/keys'.replaceAll(RegExp('{projectId}'), projectId);

       var params = {
       };

       return await this.client.call('get', path: path, params: params);
    }
    createKey({projectId, name, scopes}) async {
       String path = '/projects/{projectId}/keys'.replaceAll(RegExp('{projectId}'), projectId);

       var params = {
         'name': name,
         'scopes': scopes,
       };

       return await this.client.call('post', path: path, params: params);
    }
    getKey({projectId, keyId}) async {
       String path = '/projects/{projectId}/keys/{keyId}'.replaceAll(RegExp('{projectId}'), projectId).replaceAll(RegExp('{keyId}'), keyId);

       var params = {
       };

       return await this.client.call('get', path: path, params: params);
    }
    updateKey({projectId, keyId, name, scopes}) async {
       String path = '/projects/{projectId}/keys/{keyId}'.replaceAll(RegExp('{projectId}'), projectId).replaceAll(RegExp('{keyId}'), keyId);

       var params = {
         'name': name,
         'scopes': scopes,
       };

       return await this.client.call('put', path: path, params: params);
    }
    deleteKey({projectId, keyId}) async {
       String path = '/projects/{projectId}/keys/{keyId}'.replaceAll(RegExp('{projectId}'), projectId).replaceAll(RegExp('{keyId}'), keyId);

       var params = {
       };

       return await this.client.call('delete', path: path, params: params);
    }
    updateProjectOAuth({projectId, provider, appId = null, secret = null}) async {
       String path = '/projects/{projectId}/oauth'.replaceAll(RegExp('{projectId}'), projectId);

       var params = {
         'provider': provider,
         'appId': appId,
         'secret': secret,
       };

       return await this.client.call('patch', path: path, params: params);
    }
    listPlatforms({projectId}) async {
       String path = '/projects/{projectId}/platforms'.replaceAll(RegExp('{projectId}'), projectId);

       var params = {
       };

       return await this.client.call('get', path: path, params: params);
    }
    createPlatform({projectId, type, name, key = null, store = null, url = null}) async {
       String path = '/projects/{projectId}/platforms'.replaceAll(RegExp('{projectId}'), projectId);

       var params = {
         'type': type,
         'name': name,
         'key': key,
         'store': store,
         'url': url,
       };

       return await this.client.call('post', path: path, params: params);
    }
    getPlatform({projectId, platformId}) async {
       String path = '/projects/{projectId}/platforms/{platformId}'.replaceAll(RegExp('{projectId}'), projectId).replaceAll(RegExp('{platformId}'), platformId);

       var params = {
       };

       return await this.client.call('get', path: path, params: params);
    }
    updatePlatform({projectId, platformId, name, key = null, store = null, url = null}) async {
       String path = '/projects/{projectId}/platforms/{platformId}'.replaceAll(RegExp('{projectId}'), projectId).replaceAll(RegExp('{platformId}'), platformId);

       var params = {
         'name': name,
         'key': key,
         'store': store,
         'url': url,
       };

       return await this.client.call('put', path: path, params: params);
    }
    deletePlatform({projectId, platformId}) async {
       String path = '/projects/{projectId}/platforms/{platformId}'.replaceAll(RegExp('{projectId}'), projectId).replaceAll(RegExp('{platformId}'), platformId);

       var params = {
       };

       return await this.client.call('delete', path: path, params: params);
    }
    listTasks({projectId}) async {
       String path = '/projects/{projectId}/tasks'.replaceAll(RegExp('{projectId}'), projectId);

       var params = {
       };

       return await this.client.call('get', path: path, params: params);
    }
    createTask({projectId, name, status, schedule, security, httpMethod, httpUrl, httpHeaders = null, httpUser = null, httpPass = null}) async {
       String path = '/projects/{projectId}/tasks'.replaceAll(RegExp('{projectId}'), projectId);

       var params = {
         'name': name,
         'status': status,
         'schedule': schedule,
         'security': security,
         'httpMethod': httpMethod,
         'httpUrl': httpUrl,
         'httpHeaders': httpHeaders,
         'httpUser': httpUser,
         'httpPass': httpPass,
       };

       return await this.client.call('post', path: path, params: params);
    }
    getTask({projectId, taskId}) async {
       String path = '/projects/{projectId}/tasks/{taskId}'.replaceAll(RegExp('{projectId}'), projectId).replaceAll(RegExp('{taskId}'), taskId);

       var params = {
       };

       return await this.client.call('get', path: path, params: params);
    }
    updateTask({projectId, taskId, name, status, schedule, security, httpMethod, httpUrl, httpHeaders = null, httpUser = null, httpPass = null}) async {
       String path = '/projects/{projectId}/tasks/{taskId}'.replaceAll(RegExp('{projectId}'), projectId).replaceAll(RegExp('{taskId}'), taskId);

       var params = {
         'name': name,
         'status': status,
         'schedule': schedule,
         'security': security,
         'httpMethod': httpMethod,
         'httpUrl': httpUrl,
         'httpHeaders': httpHeaders,
         'httpUser': httpUser,
         'httpPass': httpPass,
       };

       return await this.client.call('put', path: path, params: params);
    }
    deleteTask({projectId, taskId}) async {
       String path = '/projects/{projectId}/tasks/{taskId}'.replaceAll(RegExp('{projectId}'), projectId).replaceAll(RegExp('{taskId}'), taskId);

       var params = {
       };

       return await this.client.call('delete', path: path, params: params);
    }
    getProjectUsage({projectId}) async {
       String path = '/projects/{projectId}/usage'.replaceAll(RegExp('{projectId}'), projectId);

       var params = {
       };

       return await this.client.call('get', path: path, params: params);
    }
    listWebhooks({projectId}) async {
       String path = '/projects/{projectId}/webhooks'.replaceAll(RegExp('{projectId}'), projectId);

       var params = {
       };

       return await this.client.call('get', path: path, params: params);
    }
    createWebhook({projectId, name, events, url, security, httpUser = null, httpPass = null}) async {
       String path = '/projects/{projectId}/webhooks'.replaceAll(RegExp('{projectId}'), projectId);

       var params = {
         'name': name,
         'events': events,
         'url': url,
         'security': security,
         'httpUser': httpUser,
         'httpPass': httpPass,
       };

       return await this.client.call('post', path: path, params: params);
    }
    getWebhook({projectId, webhookId}) async {
       String path = '/projects/{projectId}/webhooks/{webhookId}'.replaceAll(RegExp('{projectId}'), projectId).replaceAll(RegExp('{webhookId}'), webhookId);

       var params = {
       };

       return await this.client.call('get', path: path, params: params);
    }
    updateWebhook({projectId, webhookId, name, events, url, security, httpUser = null, httpPass = null}) async {
       String path = '/projects/{projectId}/webhooks/{webhookId}'.replaceAll(RegExp('{projectId}'), projectId).replaceAll(RegExp('{webhookId}'), webhookId);

       var params = {
         'name': name,
         'events': events,
         'url': url,
         'security': security,
         'httpUser': httpUser,
         'httpPass': httpPass,
       };

       return await this.client.call('put', path: path, params: params);
    }
    deleteWebhook({projectId, webhookId}) async {
       String path = '/projects/{projectId}/webhooks/{webhookId}'.replaceAll(RegExp('{projectId}'), projectId).replaceAll(RegExp('{webhookId}'), webhookId);

       var params = {
       };

       return await this.client.call('delete', path: path, params: params);
    }
}