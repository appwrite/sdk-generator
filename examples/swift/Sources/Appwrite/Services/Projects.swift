

class Projects: Service
{
    /**
     * List Projects
     *
     * @throws Exception
     * @return array
     */

    func listProjects()-> Array<Any> {
        let path: String = "/projects"


                let params: [String: Any] = [:]
        

        return [self.client.call(method: Client.HTTPMethod.get.rawValue, path: path, headers: [
            "content-type": "application/json",
        ], params: params)];
    }

    /**
     * Create Project
     *
     * @param String name
     * @param String teamId
     * @param String description
     * @param String logo
     * @param String url
     * @param String legalName
     * @param String legalCountry
     * @param String legalState
     * @param String legalCity
     * @param String legalAddress
     * @param String legalTaxId
     * @throws Exception
     * @return array
     */

    func createProject(name: String, teamId: String, description: String = "", logo: String = "", url: String = "", legalName: String = "", legalCountry: String = "", legalState: String = "", legalCity: String = "", legalAddress: String = "", legalTaxId: String = "")-> Array<Any> {
        let path: String = "/projects"


                var params: [String: Any] = [:]
        
        params["name"] = name
        params["teamId"] = teamId
        params["description"] = description
        params["logo"] = logo
        params["url"] = url
        params["legalName"] = legalName
        params["legalCountry"] = legalCountry
        params["legalState"] = legalState
        params["legalCity"] = legalCity
        params["legalAddress"] = legalAddress
        params["legalTaxId"] = legalTaxId

        return [self.client.call(method: Client.HTTPMethod.post.rawValue, path: path, headers: [
            "content-type": "application/json",
        ], params: params)];
    }

    /**
     * Get Project
     *
     * @param String projectId
     * @throws Exception
     * @return array
     */

    func getProject(projectId: String)-> Array<Any> {
        var path: String = "/projects/{projectId}"

        path = path.replacingOccurrences(
          of: "{projectId}",
          with: projectId
        )

                let params: [String: Any] = [:]
        

        return [self.client.call(method: Client.HTTPMethod.get.rawValue, path: path, headers: [
            "content-type": "application/json",
        ], params: params)];
    }

    /**
     * Update Project
     *
     * @param String projectId
     * @param String name
     * @param String description
     * @param String logo
     * @param String url
     * @param String legalName
     * @param String legalCountry
     * @param String legalState
     * @param String legalCity
     * @param String legalAddress
     * @param String legalTaxId
     * @throws Exception
     * @return array
     */

    func updateProject(projectId: String, name: String, description: String = "", logo: String = "", url: String = "", legalName: String = "", legalCountry: String = "", legalState: String = "", legalCity: String = "", legalAddress: String = "", legalTaxId: String = "")-> Array<Any> {
        var path: String = "/projects/{projectId}"

        path = path.replacingOccurrences(
          of: "{projectId}",
          with: projectId
        )

                var params: [String: Any] = [:]
        
        params["name"] = name
        params["description"] = description
        params["logo"] = logo
        params["url"] = url
        params["legalName"] = legalName
        params["legalCountry"] = legalCountry
        params["legalState"] = legalState
        params["legalCity"] = legalCity
        params["legalAddress"] = legalAddress
        params["legalTaxId"] = legalTaxId

        return [self.client.call(method: Client.HTTPMethod.patch.rawValue, path: path, headers: [
            "content-type": "application/json",
        ], params: params)];
    }

    /**
     * Delete Project
     *
     * @param String projectId
     * @throws Exception
     * @return array
     */

    func deleteProject(projectId: String)-> Array<Any> {
        var path: String = "/projects/{projectId}"

        path = path.replacingOccurrences(
          of: "{projectId}",
          with: projectId
        )

                let params: [String: Any] = [:]
        

        return [self.client.call(method: Client.HTTPMethod.delete.rawValue, path: path, headers: [
            "content-type": "application/json",
        ], params: params)];
    }

    /**
     * List Keys
     *
     * @param String projectId
     * @throws Exception
     * @return array
     */

    func listKeys(projectId: String)-> Array<Any> {
        var path: String = "/projects/{projectId}/keys"

        path = path.replacingOccurrences(
          of: "{projectId}",
          with: projectId
        )

                let params: [String: Any] = [:]
        

        return [self.client.call(method: Client.HTTPMethod.get.rawValue, path: path, headers: [
            "content-type": "application/json",
        ], params: params)];
    }

    /**
     * Create Key
     *
     * @param String projectId
     * @param String name
     * @param Array<Any> scopes
     * @throws Exception
     * @return array
     */

    func createKey(projectId: String, name: String, scopes: Array<Any>)-> Array<Any> {
        var path: String = "/projects/{projectId}/keys"

        path = path.replacingOccurrences(
          of: "{projectId}",
          with: projectId
        )

                var params: [String: Any] = [:]
        
        params["name"] = name
        params["scopes"] = scopes

        return [self.client.call(method: Client.HTTPMethod.post.rawValue, path: path, headers: [
            "content-type": "application/json",
        ], params: params)];
    }

    /**
     * Get Key
     *
     * @param String projectId
     * @param String keyId
     * @throws Exception
     * @return array
     */

    func getKey(projectId: String, keyId: String)-> Array<Any> {
        var path: String = "/projects/{projectId}/keys/{keyId}"

        path = path.replacingOccurrences(
          of: "{projectId}",
          with: projectId
        )
        path = path.replacingOccurrences(
          of: "{keyId}",
          with: keyId
        )

                let params: [String: Any] = [:]
        

        return [self.client.call(method: Client.HTTPMethod.get.rawValue, path: path, headers: [
            "content-type": "application/json",
        ], params: params)];
    }

    /**
     * Update Key
     *
     * @param String projectId
     * @param String keyId
     * @param String name
     * @param Array<Any> scopes
     * @throws Exception
     * @return array
     */

    func updateKey(projectId: String, keyId: String, name: String, scopes: Array<Any>)-> Array<Any> {
        var path: String = "/projects/{projectId}/keys/{keyId}"

        path = path.replacingOccurrences(
          of: "{projectId}",
          with: projectId
        )
        path = path.replacingOccurrences(
          of: "{keyId}",
          with: keyId
        )

                var params: [String: Any] = [:]
        
        params["name"] = name
        params["scopes"] = scopes

        return [self.client.call(method: Client.HTTPMethod.put.rawValue, path: path, headers: [
            "content-type": "application/json",
        ], params: params)];
    }

    /**
     * Delete Key
     *
     * @param String projectId
     * @param String keyId
     * @throws Exception
     * @return array
     */

    func deleteKey(projectId: String, keyId: String)-> Array<Any> {
        var path: String = "/projects/{projectId}/keys/{keyId}"

        path = path.replacingOccurrences(
          of: "{projectId}",
          with: projectId
        )
        path = path.replacingOccurrences(
          of: "{keyId}",
          with: keyId
        )

                let params: [String: Any] = [:]
        

        return [self.client.call(method: Client.HTTPMethod.delete.rawValue, path: path, headers: [
            "content-type": "application/json",
        ], params: params)];
    }

    /**
     * Update Project OAuth
     *
     * @param String projectId
     * @param String provider
     * @param String appId
     * @param String secret
     * @throws Exception
     * @return array
     */

    func updateProjectOAuth(projectId: String, provider: String, appId: String = "", secret: String = "")-> Array<Any> {
        var path: String = "/projects/{projectId}/oauth"

        path = path.replacingOccurrences(
          of: "{projectId}",
          with: projectId
        )

                var params: [String: Any] = [:]
        
        params["provider"] = provider
        params["appId"] = appId
        params["secret"] = secret

        return [self.client.call(method: Client.HTTPMethod.patch.rawValue, path: path, headers: [
            "content-type": "application/json",
        ], params: params)];
    }

    /**
     * List Platforms
     *
     * @param String projectId
     * @throws Exception
     * @return array
     */

    func listPlatforms(projectId: String)-> Array<Any> {
        var path: String = "/projects/{projectId}/platforms"

        path = path.replacingOccurrences(
          of: "{projectId}",
          with: projectId
        )

                let params: [String: Any] = [:]
        

        return [self.client.call(method: Client.HTTPMethod.get.rawValue, path: path, headers: [
            "content-type": "application/json",
        ], params: params)];
    }

    /**
     * Create Platform
     *
     * @param String projectId
     * @param String type
     * @param String name
     * @param String key
     * @param String store
     * @param String url
     * @throws Exception
     * @return array
     */

    func createPlatform(projectId: String, type: String, name: String, key: String = "", store: String = "", url: String = "")-> Array<Any> {
        var path: String = "/projects/{projectId}/platforms"

        path = path.replacingOccurrences(
          of: "{projectId}",
          with: projectId
        )

                var params: [String: Any] = [:]
        
        params["type"] = type
        params["name"] = name
        params["key"] = key
        params["store"] = store
        params["url"] = url

        return [self.client.call(method: Client.HTTPMethod.post.rawValue, path: path, headers: [
            "content-type": "application/json",
        ], params: params)];
    }

    /**
     * Get Platform
     *
     * @param String projectId
     * @param String platformId
     * @throws Exception
     * @return array
     */

    func getPlatform(projectId: String, platformId: String)-> Array<Any> {
        var path: String = "/projects/{projectId}/platforms/{platformId}"

        path = path.replacingOccurrences(
          of: "{projectId}",
          with: projectId
        )
        path = path.replacingOccurrences(
          of: "{platformId}",
          with: platformId
        )

                let params: [String: Any] = [:]
        

        return [self.client.call(method: Client.HTTPMethod.get.rawValue, path: path, headers: [
            "content-type": "application/json",
        ], params: params)];
    }

    /**
     * Update Platform
     *
     * @param String projectId
     * @param String platformId
     * @param String name
     * @param String key
     * @param String store
     * @param String url
     * @throws Exception
     * @return array
     */

    func updatePlatform(projectId: String, platformId: String, name: String, key: String = "", store: String = "", url: String = "")-> Array<Any> {
        var path: String = "/projects/{projectId}/platforms/{platformId}"

        path = path.replacingOccurrences(
          of: "{projectId}",
          with: projectId
        )
        path = path.replacingOccurrences(
          of: "{platformId}",
          with: platformId
        )

                var params: [String: Any] = [:]
        
        params["name"] = name
        params["key"] = key
        params["store"] = store
        params["url"] = url

        return [self.client.call(method: Client.HTTPMethod.put.rawValue, path: path, headers: [
            "content-type": "application/json",
        ], params: params)];
    }

    /**
     * Delete Platform
     *
     * @param String projectId
     * @param String platformId
     * @throws Exception
     * @return array
     */

    func deletePlatform(projectId: String, platformId: String)-> Array<Any> {
        var path: String = "/projects/{projectId}/platforms/{platformId}"

        path = path.replacingOccurrences(
          of: "{projectId}",
          with: projectId
        )
        path = path.replacingOccurrences(
          of: "{platformId}",
          with: platformId
        )

                let params: [String: Any] = [:]
        

        return [self.client.call(method: Client.HTTPMethod.delete.rawValue, path: path, headers: [
            "content-type": "application/json",
        ], params: params)];
    }

    /**
     * List Tasks
     *
     * @param String projectId
     * @throws Exception
     * @return array
     */

    func listTasks(projectId: String)-> Array<Any> {
        var path: String = "/projects/{projectId}/tasks"

        path = path.replacingOccurrences(
          of: "{projectId}",
          with: projectId
        )

                let params: [String: Any] = [:]
        

        return [self.client.call(method: Client.HTTPMethod.get.rawValue, path: path, headers: [
            "content-type": "application/json",
        ], params: params)];
    }

    /**
     * Create Task
     *
     * @param String projectId
     * @param String name
     * @param String status
     * @param String schedule
     * @param Int security
     * @param String httpMethod
     * @param String httpUrl
     * @param Array<Any> httpHeaders
     * @param String httpUser
     * @param String httpPass
     * @throws Exception
     * @return array
     */

    func createTask(projectId: String, name: String, status: String, schedule: String, security: Int, httpMethod: String, httpUrl: String, httpHeaders: Array<Any> = [], httpUser: String = "", httpPass: String = "")-> Array<Any> {
        var path: String = "/projects/{projectId}/tasks"

        path = path.replacingOccurrences(
          of: "{projectId}",
          with: projectId
        )

                var params: [String: Any] = [:]
        
        params["name"] = name
        params["status"] = status
        params["schedule"] = schedule
        params["security"] = security
        params["httpMethod"] = httpMethod
        params["httpUrl"] = httpUrl
        params["httpHeaders"] = httpHeaders
        params["httpUser"] = httpUser
        params["httpPass"] = httpPass

        return [self.client.call(method: Client.HTTPMethod.post.rawValue, path: path, headers: [
            "content-type": "application/json",
        ], params: params)];
    }

    /**
     * Get Task
     *
     * @param String projectId
     * @param String taskId
     * @throws Exception
     * @return array
     */

    func getTask(projectId: String, taskId: String)-> Array<Any> {
        var path: String = "/projects/{projectId}/tasks/{taskId}"

        path = path.replacingOccurrences(
          of: "{projectId}",
          with: projectId
        )
        path = path.replacingOccurrences(
          of: "{taskId}",
          with: taskId
        )

                let params: [String: Any] = [:]
        

        return [self.client.call(method: Client.HTTPMethod.get.rawValue, path: path, headers: [
            "content-type": "application/json",
        ], params: params)];
    }

    /**
     * Update Task
     *
     * @param String projectId
     * @param String taskId
     * @param String name
     * @param String status
     * @param String schedule
     * @param Int security
     * @param String httpMethod
     * @param String httpUrl
     * @param Array<Any> httpHeaders
     * @param String httpUser
     * @param String httpPass
     * @throws Exception
     * @return array
     */

    func updateTask(projectId: String, taskId: String, name: String, status: String, schedule: String, security: Int, httpMethod: String, httpUrl: String, httpHeaders: Array<Any> = [], httpUser: String = "", httpPass: String = "")-> Array<Any> {
        var path: String = "/projects/{projectId}/tasks/{taskId}"

        path = path.replacingOccurrences(
          of: "{projectId}",
          with: projectId
        )
        path = path.replacingOccurrences(
          of: "{taskId}",
          with: taskId
        )

                var params: [String: Any] = [:]
        
        params["name"] = name
        params["status"] = status
        params["schedule"] = schedule
        params["security"] = security
        params["httpMethod"] = httpMethod
        params["httpUrl"] = httpUrl
        params["httpHeaders"] = httpHeaders
        params["httpUser"] = httpUser
        params["httpPass"] = httpPass

        return [self.client.call(method: Client.HTTPMethod.put.rawValue, path: path, headers: [
            "content-type": "application/json",
        ], params: params)];
    }

    /**
     * Delete Task
     *
     * @param String projectId
     * @param String taskId
     * @throws Exception
     * @return array
     */

    func deleteTask(projectId: String, taskId: String)-> Array<Any> {
        var path: String = "/projects/{projectId}/tasks/{taskId}"

        path = path.replacingOccurrences(
          of: "{projectId}",
          with: projectId
        )
        path = path.replacingOccurrences(
          of: "{taskId}",
          with: taskId
        )

                let params: [String: Any] = [:]
        

        return [self.client.call(method: Client.HTTPMethod.delete.rawValue, path: path, headers: [
            "content-type": "application/json",
        ], params: params)];
    }

    /**
     * Get Project
     *
     * @param String projectId
     * @throws Exception
     * @return array
     */

    func getProjectUsage(projectId: String)-> Array<Any> {
        var path: String = "/projects/{projectId}/usage"

        path = path.replacingOccurrences(
          of: "{projectId}",
          with: projectId
        )

                let params: [String: Any] = [:]
        

        return [self.client.call(method: Client.HTTPMethod.get.rawValue, path: path, headers: [
            "content-type": "application/json",
        ], params: params)];
    }

    /**
     * List Webhooks
     *
     * @param String projectId
     * @throws Exception
     * @return array
     */

    func listWebhooks(projectId: String)-> Array<Any> {
        var path: String = "/projects/{projectId}/webhooks"

        path = path.replacingOccurrences(
          of: "{projectId}",
          with: projectId
        )

                let params: [String: Any] = [:]
        

        return [self.client.call(method: Client.HTTPMethod.get.rawValue, path: path, headers: [
            "content-type": "application/json",
        ], params: params)];
    }

    /**
     * Create Webhook
     *
     * @param String projectId
     * @param String name
     * @param Array<Any> events
     * @param String url
     * @param Int security
     * @param String httpUser
     * @param String httpPass
     * @throws Exception
     * @return array
     */

    func createWebhook(projectId: String, name: String, events: Array<Any>, url: String, security: Int, httpUser: String = "", httpPass: String = "")-> Array<Any> {
        var path: String = "/projects/{projectId}/webhooks"

        path = path.replacingOccurrences(
          of: "{projectId}",
          with: projectId
        )

                var params: [String: Any] = [:]
        
        params["name"] = name
        params["events"] = events
        params["url"] = url
        params["security"] = security
        params["httpUser"] = httpUser
        params["httpPass"] = httpPass

        return [self.client.call(method: Client.HTTPMethod.post.rawValue, path: path, headers: [
            "content-type": "application/json",
        ], params: params)];
    }

    /**
     * Get Webhook
     *
     * @param String projectId
     * @param String webhookId
     * @throws Exception
     * @return array
     */

    func getWebhook(projectId: String, webhookId: String)-> Array<Any> {
        var path: String = "/projects/{projectId}/webhooks/{webhookId}"

        path = path.replacingOccurrences(
          of: "{projectId}",
          with: projectId
        )
        path = path.replacingOccurrences(
          of: "{webhookId}",
          with: webhookId
        )

                let params: [String: Any] = [:]
        

        return [self.client.call(method: Client.HTTPMethod.get.rawValue, path: path, headers: [
            "content-type": "application/json",
        ], params: params)];
    }

    /**
     * Update Webhook
     *
     * @param String projectId
     * @param String webhookId
     * @param String name
     * @param Array<Any> events
     * @param String url
     * @param Int security
     * @param String httpUser
     * @param String httpPass
     * @throws Exception
     * @return array
     */

    func updateWebhook(projectId: String, webhookId: String, name: String, events: Array<Any>, url: String, security: Int, httpUser: String = "", httpPass: String = "")-> Array<Any> {
        var path: String = "/projects/{projectId}/webhooks/{webhookId}"

        path = path.replacingOccurrences(
          of: "{projectId}",
          with: projectId
        )
        path = path.replacingOccurrences(
          of: "{webhookId}",
          with: webhookId
        )

                var params: [String: Any] = [:]
        
        params["name"] = name
        params["events"] = events
        params["url"] = url
        params["security"] = security
        params["httpUser"] = httpUser
        params["httpPass"] = httpPass

        return [self.client.call(method: Client.HTTPMethod.put.rawValue, path: path, headers: [
            "content-type": "application/json",
        ], params: params)];
    }

    /**
     * Delete Webhook
     *
     * @param String projectId
     * @param String webhookId
     * @throws Exception
     * @return array
     */

    func deleteWebhook(projectId: String, webhookId: String)-> Array<Any> {
        var path: String = "/projects/{projectId}/webhooks/{webhookId}"

        path = path.replacingOccurrences(
          of: "{projectId}",
          with: projectId
        )
        path = path.replacingOccurrences(
          of: "{webhookId}",
          with: webhookId
        )

                let params: [String: Any] = [:]
        

        return [self.client.call(method: Client.HTTPMethod.delete.rawValue, path: path, headers: [
            "content-type": "application/json",
        ], params: params)];
    }

}
