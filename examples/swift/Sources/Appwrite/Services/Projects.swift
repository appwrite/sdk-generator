

class Projects: Service
{
    /**
     * List Projects
     *
     * @throws Exception
     * @return array
     */

    func listProjects()-> Array<Any> {
        let methodPath = "/projects"
        let path = methodPath.replacingOccurrences(
          of: "[]",
          with: "",
          options: .regularExpression,
          range: nil
        )
        let params = []


        return self.client.call(HTTPMethod.get, path, [
            "content-type": "application/json",
        ], params);
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

    func createProject(name: String, teamId: String, description: String = null, logo: String = null, url: String = null, legalName: String = null, legalCountry: String = null, legalState: String = null, legalCity: String = null, legalAddress: String = null, legalTaxId: String = null)-> Array<Any> {
        let methodPath = "/projects"
        let path = methodPath.replacingOccurrences(
          of: "[]",
          with: "",
          options: .regularExpression,
          range: nil
        )
        let params = []

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

        return self.client.call(HTTPMethod.post, path, [
            "content-type": "application/json",
        ], params);
    }

    /**
     * Get Project
     *
     * @param String projectId
     * @throws Exception
     * @return array
     */

    func getProject(projectId: String)-> Array<Any> {
        let methodPath = "/projects/{projectId}"
        let path = methodPath.replacingOccurrences(
          of: "['//{projectId}']",
          with: "$projectId",
          options: .regularExpression,
          range: nil
        )
        let params = []


        return self.client.call(HTTPMethod.get, path, [
            "content-type": "application/json",
        ], params);
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

    func updateProject(projectId: String, name: String, description: String = null, logo: String = null, url: String = null, legalName: String = null, legalCountry: String = null, legalState: String = null, legalCity: String = null, legalAddress: String = null, legalTaxId: String = null)-> Array<Any> {
        let methodPath = "/projects/{projectId}"
        let path = methodPath.replacingOccurrences(
          of: "['//{projectId}']",
          with: "$projectId",
          options: .regularExpression,
          range: nil
        )
        let params = []

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

        return self.client.call(HTTPMethod.patch, path, [
            "content-type": "application/json",
        ], params);
    }

    /**
     * Delete Project
     *
     * @param String projectId
     * @throws Exception
     * @return array
     */

    func deleteProject(projectId: String)-> Array<Any> {
        let methodPath = "/projects/{projectId}"
        let path = methodPath.replacingOccurrences(
          of: "['//{projectId}']",
          with: "$projectId",
          options: .regularExpression,
          range: nil
        )
        let params = []


        return self.client.call(HTTPMethod.delete, path, [
            "content-type": "application/json",
        ], params);
    }

    /**
     * List Keys
     *
     * @param String projectId
     * @throws Exception
     * @return array
     */

    func listKeys(projectId: String)-> Array<Any> {
        let methodPath = "/projects/{projectId}/keys"
        let path = methodPath.replacingOccurrences(
          of: "['//{projectId}']",
          with: "$projectId",
          options: .regularExpression,
          range: nil
        )
        let params = []


        return self.client.call(HTTPMethod.get, path, [
            "content-type": "application/json",
        ], params);
    }

    /**
     * Create Key
     *
     * @param String projectId
     * @param String name
     * @param Array scopes
     * @throws Exception
     * @return array
     */

    func createKey(projectId: String, name: String, scopes: Array)-> Array<Any> {
        let methodPath = "/projects/{projectId}/keys"
        let path = methodPath.replacingOccurrences(
          of: "['//{projectId}']",
          with: "$projectId",
          options: .regularExpression,
          range: nil
        )
        let params = []

        params["name"] = name
        params["scopes"] = scopes

        return self.client.call(HTTPMethod.post, path, [
            "content-type": "application/json",
        ], params);
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
        let methodPath = "/projects/{projectId}/keys/{keyId}"
        let path = methodPath.replacingOccurrences(
          of: "['//{projectId}', '//{keyId}']",
          with: "$projectId, $keyId",
          options: .regularExpression,
          range: nil
        )
        let params = []


        return self.client.call(HTTPMethod.get, path, [
            "content-type": "application/json",
        ], params);
    }

    /**
     * Update Key
     *
     * @param String projectId
     * @param String keyId
     * @param String name
     * @param Array scopes
     * @throws Exception
     * @return array
     */

    func updateKey(projectId: String, keyId: String, name: String, scopes: Array)-> Array<Any> {
        let methodPath = "/projects/{projectId}/keys/{keyId}"
        let path = methodPath.replacingOccurrences(
          of: "['//{projectId}', '//{keyId}']",
          with: "$projectId, $keyId",
          options: .regularExpression,
          range: nil
        )
        let params = []

        params["name"] = name
        params["scopes"] = scopes

        return self.client.call(HTTPMethod.put, path, [
            "content-type": "application/json",
        ], params);
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
        let methodPath = "/projects/{projectId}/keys/{keyId}"
        let path = methodPath.replacingOccurrences(
          of: "['//{projectId}', '//{keyId}']",
          with: "$projectId, $keyId",
          options: .regularExpression,
          range: nil
        )
        let params = []


        return self.client.call(HTTPMethod.delete, path, [
            "content-type": "application/json",
        ], params);
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

    func updateProjectOAuth(projectId: String, provider: String, appId: String = null, secret: String = null)-> Array<Any> {
        let methodPath = "/projects/{projectId}/oauth"
        let path = methodPath.replacingOccurrences(
          of: "['//{projectId}']",
          with: "$projectId",
          options: .regularExpression,
          range: nil
        )
        let params = []

        params["provider"] = provider
        params["appId"] = appId
        params["secret"] = secret

        return self.client.call(HTTPMethod.patch, path, [
            "content-type": "application/json",
        ], params);
    }

    /**
     * List Platforms
     *
     * @param String projectId
     * @throws Exception
     * @return array
     */

    func listPlatforms(projectId: String)-> Array<Any> {
        let methodPath = "/projects/{projectId}/platforms"
        let path = methodPath.replacingOccurrences(
          of: "['//{projectId}']",
          with: "$projectId",
          options: .regularExpression,
          range: nil
        )
        let params = []


        return self.client.call(HTTPMethod.get, path, [
            "content-type": "application/json",
        ], params);
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

    func createPlatform(projectId: String, type: String, name: String, key: String = null, store: String = null, url: String = null)-> Array<Any> {
        let methodPath = "/projects/{projectId}/platforms"
        let path = methodPath.replacingOccurrences(
          of: "['//{projectId}']",
          with: "$projectId",
          options: .regularExpression,
          range: nil
        )
        let params = []

        params["type"] = type
        params["name"] = name
        params["key"] = key
        params["store"] = store
        params["url"] = url

        return self.client.call(HTTPMethod.post, path, [
            "content-type": "application/json",
        ], params);
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
        let methodPath = "/projects/{projectId}/platforms/{platformId}"
        let path = methodPath.replacingOccurrences(
          of: "['//{projectId}', '//{platformId}']",
          with: "$projectId, $platformId",
          options: .regularExpression,
          range: nil
        )
        let params = []


        return self.client.call(HTTPMethod.get, path, [
            "content-type": "application/json",
        ], params);
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

    func updatePlatform(projectId: String, platformId: String, name: String, key: String = null, store: String = null, url: String = null)-> Array<Any> {
        let methodPath = "/projects/{projectId}/platforms/{platformId}"
        let path = methodPath.replacingOccurrences(
          of: "['//{projectId}', '//{platformId}']",
          with: "$projectId, $platformId",
          options: .regularExpression,
          range: nil
        )
        let params = []

        params["name"] = name
        params["key"] = key
        params["store"] = store
        params["url"] = url

        return self.client.call(HTTPMethod.put, path, [
            "content-type": "application/json",
        ], params);
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
        let methodPath = "/projects/{projectId}/platforms/{platformId}"
        let path = methodPath.replacingOccurrences(
          of: "['//{projectId}', '//{platformId}']",
          with: "$projectId, $platformId",
          options: .regularExpression,
          range: nil
        )
        let params = []


        return self.client.call(HTTPMethod.delete, path, [
            "content-type": "application/json",
        ], params);
    }

    /**
     * List Tasks
     *
     * @param String projectId
     * @throws Exception
     * @return array
     */

    func listTasks(projectId: String)-> Array<Any> {
        let methodPath = "/projects/{projectId}/tasks"
        let path = methodPath.replacingOccurrences(
          of: "['//{projectId}']",
          with: "$projectId",
          options: .regularExpression,
          range: nil
        )
        let params = []


        return self.client.call(HTTPMethod.get, path, [
            "content-type": "application/json",
        ], params);
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
     * @param Array httpHeaders
     * @param String httpUser
     * @param String httpPass
     * @throws Exception
     * @return array
     */

    func createTask(projectId: String, name: String, status: String, schedule: String, security: Int, httpMethod: String, httpUrl: String, httpHeaders: Array = null, httpUser: String = null, httpPass: String = null)-> Array<Any> {
        let methodPath = "/projects/{projectId}/tasks"
        let path = methodPath.replacingOccurrences(
          of: "['//{projectId}']",
          with: "$projectId",
          options: .regularExpression,
          range: nil
        )
        let params = []

        params["name"] = name
        params["status"] = status
        params["schedule"] = schedule
        params["security"] = security
        params["httpMethod"] = httpMethod
        params["httpUrl"] = httpUrl
        params["httpHeaders"] = httpHeaders
        params["httpUser"] = httpUser
        params["httpPass"] = httpPass

        return self.client.call(HTTPMethod.post, path, [
            "content-type": "application/json",
        ], params);
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
        let methodPath = "/projects/{projectId}/tasks/{taskId}"
        let path = methodPath.replacingOccurrences(
          of: "['//{projectId}', '//{taskId}']",
          with: "$projectId, $taskId",
          options: .regularExpression,
          range: nil
        )
        let params = []


        return self.client.call(HTTPMethod.get, path, [
            "content-type": "application/json",
        ], params);
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
     * @param Array httpHeaders
     * @param String httpUser
     * @param String httpPass
     * @throws Exception
     * @return array
     */

    func updateTask(projectId: String, taskId: String, name: String, status: String, schedule: String, security: Int, httpMethod: String, httpUrl: String, httpHeaders: Array = null, httpUser: String = null, httpPass: String = null)-> Array<Any> {
        let methodPath = "/projects/{projectId}/tasks/{taskId}"
        let path = methodPath.replacingOccurrences(
          of: "['//{projectId}', '//{taskId}']",
          with: "$projectId, $taskId",
          options: .regularExpression,
          range: nil
        )
        let params = []

        params["name"] = name
        params["status"] = status
        params["schedule"] = schedule
        params["security"] = security
        params["httpMethod"] = httpMethod
        params["httpUrl"] = httpUrl
        params["httpHeaders"] = httpHeaders
        params["httpUser"] = httpUser
        params["httpPass"] = httpPass

        return self.client.call(HTTPMethod.put, path, [
            "content-type": "application/json",
        ], params);
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
        let methodPath = "/projects/{projectId}/tasks/{taskId}"
        let path = methodPath.replacingOccurrences(
          of: "['//{projectId}', '//{taskId}']",
          with: "$projectId, $taskId",
          options: .regularExpression,
          range: nil
        )
        let params = []


        return self.client.call(HTTPMethod.delete, path, [
            "content-type": "application/json",
        ], params);
    }

    /**
     * Get Project
     *
     * @param String projectId
     * @throws Exception
     * @return array
     */

    func getProjectUsage(projectId: String)-> Array<Any> {
        let methodPath = "/projects/{projectId}/usage"
        let path = methodPath.replacingOccurrences(
          of: "['//{projectId}']",
          with: "$projectId",
          options: .regularExpression,
          range: nil
        )
        let params = []


        return self.client.call(HTTPMethod.get, path, [
            "content-type": "application/json",
        ], params);
    }

    /**
     * List Webhooks
     *
     * @param String projectId
     * @throws Exception
     * @return array
     */

    func listWebhooks(projectId: String)-> Array<Any> {
        let methodPath = "/projects/{projectId}/webhooks"
        let path = methodPath.replacingOccurrences(
          of: "['//{projectId}']",
          with: "$projectId",
          options: .regularExpression,
          range: nil
        )
        let params = []


        return self.client.call(HTTPMethod.get, path, [
            "content-type": "application/json",
        ], params);
    }

    /**
     * Create Webhook
     *
     * @param String projectId
     * @param String name
     * @param Array events
     * @param String url
     * @param Int security
     * @param String httpUser
     * @param String httpPass
     * @throws Exception
     * @return array
     */

    func createWebhook(projectId: String, name: String, events: Array, url: String, security: Int, httpUser: String = null, httpPass: String = null)-> Array<Any> {
        let methodPath = "/projects/{projectId}/webhooks"
        let path = methodPath.replacingOccurrences(
          of: "['//{projectId}']",
          with: "$projectId",
          options: .regularExpression,
          range: nil
        )
        let params = []

        params["name"] = name
        params["events"] = events
        params["url"] = url
        params["security"] = security
        params["httpUser"] = httpUser
        params["httpPass"] = httpPass

        return self.client.call(HTTPMethod.post, path, [
            "content-type": "application/json",
        ], params);
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
        let methodPath = "/projects/{projectId}/webhooks/{webhookId}"
        let path = methodPath.replacingOccurrences(
          of: "['//{projectId}', '//{webhookId}']",
          with: "$projectId, $webhookId",
          options: .regularExpression,
          range: nil
        )
        let params = []


        return self.client.call(HTTPMethod.get, path, [
            "content-type": "application/json",
        ], params);
    }

    /**
     * Update Webhook
     *
     * @param String projectId
     * @param String webhookId
     * @param String name
     * @param Array events
     * @param String url
     * @param Int security
     * @param String httpUser
     * @param String httpPass
     * @throws Exception
     * @return array
     */

    func updateWebhook(projectId: String, webhookId: String, name: String, events: Array, url: String, security: Int, httpUser: String = null, httpPass: String = null)-> Array<Any> {
        let methodPath = "/projects/{projectId}/webhooks/{webhookId}"
        let path = methodPath.replacingOccurrences(
          of: "['//{projectId}', '//{webhookId}']",
          with: "$projectId, $webhookId",
          options: .regularExpression,
          range: nil
        )
        let params = []

        params["name"] = name
        params["events"] = events
        params["url"] = url
        params["security"] = security
        params["httpUser"] = httpUser
        params["httpPass"] = httpPass

        return self.client.call(HTTPMethod.put, path, [
            "content-type": "application/json",
        ], params);
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
        let methodPath = "/projects/{projectId}/webhooks/{webhookId}"
        let path = methodPath.replacingOccurrences(
          of: "['//{projectId}', '//{webhookId}']",
          with: "$projectId, $webhookId",
          options: .regularExpression,
          range: nil
        )
        let params = []


        return self.client.call(HTTPMethod.delete, path, [
            "content-type": "application/json",
        ], params);
    }

}
