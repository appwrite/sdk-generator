

class Projects: Service
{
    /**
     * List Projects
     *
     * @throws Exception
     * @return array
     */

    func listProjects() -> Array<Any> {
        let path: String = "/projects"


                let params: [String: Any] = [:]
        

        return [self.client.call(method: Client.HTTPMethod.get.rawValue, path: path, headers: [
            "content-type": "application/json",
        ], params: params)];
    }

    /**
     * Create Project
     *
     * @param String _name
     * @param String _teamId
     * @param String _description
     * @param String _logo
     * @param String _url
     * @param String _legalName
     * @param String _legalCountry
     * @param String _legalState
     * @param String _legalCity
     * @param String _legalAddress
     * @param String _legalTaxId
     * @throws Exception
     * @return array
     */

    func createProject(_name: String, _teamId: String, _description: String = "", _logo: String = "", _url: String = "", _legalName: String = "", _legalCountry: String = "", _legalState: String = "", _legalCity: String = "", _legalAddress: String = "", _legalTaxId: String = "") -> Array<Any> {
        let path: String = "/projects"


                var params: [String: Any] = [:]
        
        params["name"] = _name
        params["teamId"] = _teamId
        params["description"] = _description
        params["logo"] = _logo
        params["url"] = _url
        params["legalName"] = _legalName
        params["legalCountry"] = _legalCountry
        params["legalState"] = _legalState
        params["legalCity"] = _legalCity
        params["legalAddress"] = _legalAddress
        params["legalTaxId"] = _legalTaxId

        return [self.client.call(method: Client.HTTPMethod.post.rawValue, path: path, headers: [
            "content-type": "application/json",
        ], params: params)];
    }

    /**
     * Get Project
     *
     * @param String _projectId
     * @throws Exception
     * @return array
     */

    func getProject(_projectId: String) -> Array<Any> {
        var path: String = "/projects/{projectId}"

        path = path.replacingOccurrences(
          of: "{projectId}",
          with: _projectId
        )

                let params: [String: Any] = [:]
        

        return [self.client.call(method: Client.HTTPMethod.get.rawValue, path: path, headers: [
            "content-type": "application/json",
        ], params: params)];
    }

    /**
     * Update Project
     *
     * @param String _projectId
     * @param String _name
     * @param String _description
     * @param String _logo
     * @param String _url
     * @param String _legalName
     * @param String _legalCountry
     * @param String _legalState
     * @param String _legalCity
     * @param String _legalAddress
     * @param String _legalTaxId
     * @throws Exception
     * @return array
     */

    func updateProject(_projectId: String, _name: String, _description: String = "", _logo: String = "", _url: String = "", _legalName: String = "", _legalCountry: String = "", _legalState: String = "", _legalCity: String = "", _legalAddress: String = "", _legalTaxId: String = "") -> Array<Any> {
        var path: String = "/projects/{projectId}"

        path = path.replacingOccurrences(
          of: "{projectId}",
          with: _projectId
        )

                var params: [String: Any] = [:]
        
        params["name"] = _name
        params["description"] = _description
        params["logo"] = _logo
        params["url"] = _url
        params["legalName"] = _legalName
        params["legalCountry"] = _legalCountry
        params["legalState"] = _legalState
        params["legalCity"] = _legalCity
        params["legalAddress"] = _legalAddress
        params["legalTaxId"] = _legalTaxId

        return [self.client.call(method: Client.HTTPMethod.patch.rawValue, path: path, headers: [
            "content-type": "application/json",
        ], params: params)];
    }

    /**
     * Delete Project
     *
     * @param String _projectId
     * @throws Exception
     * @return array
     */

    func deleteProject(_projectId: String) -> Array<Any> {
        var path: String = "/projects/{projectId}"

        path = path.replacingOccurrences(
          of: "{projectId}",
          with: _projectId
        )

                let params: [String: Any] = [:]
        

        return [self.client.call(method: Client.HTTPMethod.delete.rawValue, path: path, headers: [
            "content-type": "application/json",
        ], params: params)];
    }

    /**
     * List Keys
     *
     * @param String _projectId
     * @throws Exception
     * @return array
     */

    func listKeys(_projectId: String) -> Array<Any> {
        var path: String = "/projects/{projectId}/keys"

        path = path.replacingOccurrences(
          of: "{projectId}",
          with: _projectId
        )

                let params: [String: Any] = [:]
        

        return [self.client.call(method: Client.HTTPMethod.get.rawValue, path: path, headers: [
            "content-type": "application/json",
        ], params: params)];
    }

    /**
     * Create Key
     *
     * @param String _projectId
     * @param String _name
     * @param Array<Any> _scopes
     * @throws Exception
     * @return array
     */

    func createKey(_projectId: String, _name: String, _scopes: Array<Any>) -> Array<Any> {
        var path: String = "/projects/{projectId}/keys"

        path = path.replacingOccurrences(
          of: "{projectId}",
          with: _projectId
        )

                var params: [String: Any] = [:]
        
        params["name"] = _name
        params["scopes"] = _scopes

        return [self.client.call(method: Client.HTTPMethod.post.rawValue, path: path, headers: [
            "content-type": "application/json",
        ], params: params)];
    }

    /**
     * Get Key
     *
     * @param String _projectId
     * @param String _keyId
     * @throws Exception
     * @return array
     */

    func getKey(_projectId: String, _keyId: String) -> Array<Any> {
        var path: String = "/projects/{projectId}/keys/{keyId}"

        path = path.replacingOccurrences(
          of: "{projectId}",
          with: _projectId
        )
        path = path.replacingOccurrences(
          of: "{keyId}",
          with: _keyId
        )

                let params: [String: Any] = [:]
        

        return [self.client.call(method: Client.HTTPMethod.get.rawValue, path: path, headers: [
            "content-type": "application/json",
        ], params: params)];
    }

    /**
     * Update Key
     *
     * @param String _projectId
     * @param String _keyId
     * @param String _name
     * @param Array<Any> _scopes
     * @throws Exception
     * @return array
     */

    func updateKey(_projectId: String, _keyId: String, _name: String, _scopes: Array<Any>) -> Array<Any> {
        var path: String = "/projects/{projectId}/keys/{keyId}"

        path = path.replacingOccurrences(
          of: "{projectId}",
          with: _projectId
        )
        path = path.replacingOccurrences(
          of: "{keyId}",
          with: _keyId
        )

                var params: [String: Any] = [:]
        
        params["name"] = _name
        params["scopes"] = _scopes

        return [self.client.call(method: Client.HTTPMethod.put.rawValue, path: path, headers: [
            "content-type": "application/json",
        ], params: params)];
    }

    /**
     * Delete Key
     *
     * @param String _projectId
     * @param String _keyId
     * @throws Exception
     * @return array
     */

    func deleteKey(_projectId: String, _keyId: String) -> Array<Any> {
        var path: String = "/projects/{projectId}/keys/{keyId}"

        path = path.replacingOccurrences(
          of: "{projectId}",
          with: _projectId
        )
        path = path.replacingOccurrences(
          of: "{keyId}",
          with: _keyId
        )

                let params: [String: Any] = [:]
        

        return [self.client.call(method: Client.HTTPMethod.delete.rawValue, path: path, headers: [
            "content-type": "application/json",
        ], params: params)];
    }

    /**
     * Update Project OAuth
     *
     * @param String _projectId
     * @param String _provider
     * @param String _appId
     * @param String _secret
     * @throws Exception
     * @return array
     */

    func updateProjectOAuth(_projectId: String, _provider: String, _appId: String = "", _secret: String = "") -> Array<Any> {
        var path: String = "/projects/{projectId}/oauth"

        path = path.replacingOccurrences(
          of: "{projectId}",
          with: _projectId
        )

                var params: [String: Any] = [:]
        
        params["provider"] = _provider
        params["appId"] = _appId
        params["secret"] = _secret

        return [self.client.call(method: Client.HTTPMethod.patch.rawValue, path: path, headers: [
            "content-type": "application/json",
        ], params: params)];
    }

    /**
     * List Platforms
     *
     * @param String _projectId
     * @throws Exception
     * @return array
     */

    func listPlatforms(_projectId: String) -> Array<Any> {
        var path: String = "/projects/{projectId}/platforms"

        path = path.replacingOccurrences(
          of: "{projectId}",
          with: _projectId
        )

                let params: [String: Any] = [:]
        

        return [self.client.call(method: Client.HTTPMethod.get.rawValue, path: path, headers: [
            "content-type": "application/json",
        ], params: params)];
    }

    /**
     * Create Platform
     *
     * @param String _projectId
     * @param String _type
     * @param String _name
     * @param String _key
     * @param String _store
     * @param String _url
     * @throws Exception
     * @return array
     */

    func createPlatform(_projectId: String, _type: String, _name: String, _key: String = "", _store: String = "", _url: String = "") -> Array<Any> {
        var path: String = "/projects/{projectId}/platforms"

        path = path.replacingOccurrences(
          of: "{projectId}",
          with: _projectId
        )

                var params: [String: Any] = [:]
        
        params["type"] = _type
        params["name"] = _name
        params["key"] = _key
        params["store"] = _store
        params["url"] = _url

        return [self.client.call(method: Client.HTTPMethod.post.rawValue, path: path, headers: [
            "content-type": "application/json",
        ], params: params)];
    }

    /**
     * Get Platform
     *
     * @param String _projectId
     * @param String _platformId
     * @throws Exception
     * @return array
     */

    func getPlatform(_projectId: String, _platformId: String) -> Array<Any> {
        var path: String = "/projects/{projectId}/platforms/{platformId}"

        path = path.replacingOccurrences(
          of: "{projectId}",
          with: _projectId
        )
        path = path.replacingOccurrences(
          of: "{platformId}",
          with: _platformId
        )

                let params: [String: Any] = [:]
        

        return [self.client.call(method: Client.HTTPMethod.get.rawValue, path: path, headers: [
            "content-type": "application/json",
        ], params: params)];
    }

    /**
     * Update Platform
     *
     * @param String _projectId
     * @param String _platformId
     * @param String _name
     * @param String _key
     * @param String _store
     * @param String _url
     * @throws Exception
     * @return array
     */

    func updatePlatform(_projectId: String, _platformId: String, _name: String, _key: String = "", _store: String = "", _url: String = "") -> Array<Any> {
        var path: String = "/projects/{projectId}/platforms/{platformId}"

        path = path.replacingOccurrences(
          of: "{projectId}",
          with: _projectId
        )
        path = path.replacingOccurrences(
          of: "{platformId}",
          with: _platformId
        )

                var params: [String: Any] = [:]
        
        params["name"] = _name
        params["key"] = _key
        params["store"] = _store
        params["url"] = _url

        return [self.client.call(method: Client.HTTPMethod.put.rawValue, path: path, headers: [
            "content-type": "application/json",
        ], params: params)];
    }

    /**
     * Delete Platform
     *
     * @param String _projectId
     * @param String _platformId
     * @throws Exception
     * @return array
     */

    func deletePlatform(_projectId: String, _platformId: String) -> Array<Any> {
        var path: String = "/projects/{projectId}/platforms/{platformId}"

        path = path.replacingOccurrences(
          of: "{projectId}",
          with: _projectId
        )
        path = path.replacingOccurrences(
          of: "{platformId}",
          with: _platformId
        )

                let params: [String: Any] = [:]
        

        return [self.client.call(method: Client.HTTPMethod.delete.rawValue, path: path, headers: [
            "content-type": "application/json",
        ], params: params)];
    }

    /**
     * List Tasks
     *
     * @param String _projectId
     * @throws Exception
     * @return array
     */

    func listTasks(_projectId: String) -> Array<Any> {
        var path: String = "/projects/{projectId}/tasks"

        path = path.replacingOccurrences(
          of: "{projectId}",
          with: _projectId
        )

                let params: [String: Any] = [:]
        

        return [self.client.call(method: Client.HTTPMethod.get.rawValue, path: path, headers: [
            "content-type": "application/json",
        ], params: params)];
    }

    /**
     * Create Task
     *
     * @param String _projectId
     * @param String _name
     * @param String _status
     * @param String _schedule
     * @param Int _security
     * @param String _httpMethod
     * @param String _httpUrl
     * @param Array<Any> _httpHeaders
     * @param String _httpUser
     * @param String _httpPass
     * @throws Exception
     * @return array
     */

    func createTask(_projectId: String, _name: String, _status: String, _schedule: String, _security: Int, _httpMethod: String, _httpUrl: String, _httpHeaders: Array<Any> = [], _httpUser: String = "", _httpPass: String = "") -> Array<Any> {
        var path: String = "/projects/{projectId}/tasks"

        path = path.replacingOccurrences(
          of: "{projectId}",
          with: _projectId
        )

                var params: [String: Any] = [:]
        
        params["name"] = _name
        params["status"] = _status
        params["schedule"] = _schedule
        params["security"] = _security
        params["httpMethod"] = _httpMethod
        params["httpUrl"] = _httpUrl
        params["httpHeaders"] = _httpHeaders
        params["httpUser"] = _httpUser
        params["httpPass"] = _httpPass

        return [self.client.call(method: Client.HTTPMethod.post.rawValue, path: path, headers: [
            "content-type": "application/json",
        ], params: params)];
    }

    /**
     * Get Task
     *
     * @param String _projectId
     * @param String _taskId
     * @throws Exception
     * @return array
     */

    func getTask(_projectId: String, _taskId: String) -> Array<Any> {
        var path: String = "/projects/{projectId}/tasks/{taskId}"

        path = path.replacingOccurrences(
          of: "{projectId}",
          with: _projectId
        )
        path = path.replacingOccurrences(
          of: "{taskId}",
          with: _taskId
        )

                let params: [String: Any] = [:]
        

        return [self.client.call(method: Client.HTTPMethod.get.rawValue, path: path, headers: [
            "content-type": "application/json",
        ], params: params)];
    }

    /**
     * Update Task
     *
     * @param String _projectId
     * @param String _taskId
     * @param String _name
     * @param String _status
     * @param String _schedule
     * @param Int _security
     * @param String _httpMethod
     * @param String _httpUrl
     * @param Array<Any> _httpHeaders
     * @param String _httpUser
     * @param String _httpPass
     * @throws Exception
     * @return array
     */

    func updateTask(_projectId: String, _taskId: String, _name: String, _status: String, _schedule: String, _security: Int, _httpMethod: String, _httpUrl: String, _httpHeaders: Array<Any> = [], _httpUser: String = "", _httpPass: String = "") -> Array<Any> {
        var path: String = "/projects/{projectId}/tasks/{taskId}"

        path = path.replacingOccurrences(
          of: "{projectId}",
          with: _projectId
        )
        path = path.replacingOccurrences(
          of: "{taskId}",
          with: _taskId
        )

                var params: [String: Any] = [:]
        
        params["name"] = _name
        params["status"] = _status
        params["schedule"] = _schedule
        params["security"] = _security
        params["httpMethod"] = _httpMethod
        params["httpUrl"] = _httpUrl
        params["httpHeaders"] = _httpHeaders
        params["httpUser"] = _httpUser
        params["httpPass"] = _httpPass

        return [self.client.call(method: Client.HTTPMethod.put.rawValue, path: path, headers: [
            "content-type": "application/json",
        ], params: params)];
    }

    /**
     * Delete Task
     *
     * @param String _projectId
     * @param String _taskId
     * @throws Exception
     * @return array
     */

    func deleteTask(_projectId: String, _taskId: String) -> Array<Any> {
        var path: String = "/projects/{projectId}/tasks/{taskId}"

        path = path.replacingOccurrences(
          of: "{projectId}",
          with: _projectId
        )
        path = path.replacingOccurrences(
          of: "{taskId}",
          with: _taskId
        )

                let params: [String: Any] = [:]
        

        return [self.client.call(method: Client.HTTPMethod.delete.rawValue, path: path, headers: [
            "content-type": "application/json",
        ], params: params)];
    }

    /**
     * Get Project
     *
     * @param String _projectId
     * @throws Exception
     * @return array
     */

    func getProjectUsage(_projectId: String) -> Array<Any> {
        var path: String = "/projects/{projectId}/usage"

        path = path.replacingOccurrences(
          of: "{projectId}",
          with: _projectId
        )

                let params: [String: Any] = [:]
        

        return [self.client.call(method: Client.HTTPMethod.get.rawValue, path: path, headers: [
            "content-type": "application/json",
        ], params: params)];
    }

    /**
     * List Webhooks
     *
     * @param String _projectId
     * @throws Exception
     * @return array
     */

    func listWebhooks(_projectId: String) -> Array<Any> {
        var path: String = "/projects/{projectId}/webhooks"

        path = path.replacingOccurrences(
          of: "{projectId}",
          with: _projectId
        )

                let params: [String: Any] = [:]
        

        return [self.client.call(method: Client.HTTPMethod.get.rawValue, path: path, headers: [
            "content-type": "application/json",
        ], params: params)];
    }

    /**
     * Create Webhook
     *
     * @param String _projectId
     * @param String _name
     * @param Array<Any> _events
     * @param String _url
     * @param Int _security
     * @param String _httpUser
     * @param String _httpPass
     * @throws Exception
     * @return array
     */

    func createWebhook(_projectId: String, _name: String, _events: Array<Any>, _url: String, _security: Int, _httpUser: String = "", _httpPass: String = "") -> Array<Any> {
        var path: String = "/projects/{projectId}/webhooks"

        path = path.replacingOccurrences(
          of: "{projectId}",
          with: _projectId
        )

                var params: [String: Any] = [:]
        
        params["name"] = _name
        params["events"] = _events
        params["url"] = _url
        params["security"] = _security
        params["httpUser"] = _httpUser
        params["httpPass"] = _httpPass

        return [self.client.call(method: Client.HTTPMethod.post.rawValue, path: path, headers: [
            "content-type": "application/json",
        ], params: params)];
    }

    /**
     * Get Webhook
     *
     * @param String _projectId
     * @param String _webhookId
     * @throws Exception
     * @return array
     */

    func getWebhook(_projectId: String, _webhookId: String) -> Array<Any> {
        var path: String = "/projects/{projectId}/webhooks/{webhookId}"

        path = path.replacingOccurrences(
          of: "{projectId}",
          with: _projectId
        )
        path = path.replacingOccurrences(
          of: "{webhookId}",
          with: _webhookId
        )

                let params: [String: Any] = [:]
        

        return [self.client.call(method: Client.HTTPMethod.get.rawValue, path: path, headers: [
            "content-type": "application/json",
        ], params: params)];
    }

    /**
     * Update Webhook
     *
     * @param String _projectId
     * @param String _webhookId
     * @param String _name
     * @param Array<Any> _events
     * @param String _url
     * @param Int _security
     * @param String _httpUser
     * @param String _httpPass
     * @throws Exception
     * @return array
     */

    func updateWebhook(_projectId: String, _webhookId: String, _name: String, _events: Array<Any>, _url: String, _security: Int, _httpUser: String = "", _httpPass: String = "") -> Array<Any> {
        var path: String = "/projects/{projectId}/webhooks/{webhookId}"

        path = path.replacingOccurrences(
          of: "{projectId}",
          with: _projectId
        )
        path = path.replacingOccurrences(
          of: "{webhookId}",
          with: _webhookId
        )

                var params: [String: Any] = [:]
        
        params["name"] = _name
        params["events"] = _events
        params["url"] = _url
        params["security"] = _security
        params["httpUser"] = _httpUser
        params["httpPass"] = _httpPass

        return [self.client.call(method: Client.HTTPMethod.put.rawValue, path: path, headers: [
            "content-type": "application/json",
        ], params: params)];
    }

    /**
     * Delete Webhook
     *
     * @param String _projectId
     * @param String _webhookId
     * @throws Exception
     * @return array
     */

    func deleteWebhook(_projectId: String, _webhookId: String) -> Array<Any> {
        var path: String = "/projects/{projectId}/webhooks/{webhookId}"

        path = path.replacingOccurrences(
          of: "{projectId}",
          with: _projectId
        )
        path = path.replacingOccurrences(
          of: "{webhookId}",
          with: _webhookId
        )

                let params: [String: Any] = [:]
        

        return [self.client.call(method: Client.HTTPMethod.delete.rawValue, path: path, headers: [
            "content-type": "application/json",
        ], params: params)];
    }

}
