# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [0.0.1] - TBD

### Added
- Initial release of Appwrite C++ SDK
- Full support for Appwrite API 1.9.1
- Header-only library for easy integration
- Modern C++20 API with coroutine/async support via C++20 coroutines
- Built-in `Result<T>` error handling (no exceptions required in calling code)
- File upload support with automatic chunking for large files
- Query builder (`Query::equal`, `Query::between`, geo queries, etc.)
- Permission and role management utilities
- ID generation utilities
- Comprehensive model structs with JSON serialization/deserialization
- Enum types for all API parameters
- Realtime event subscription support
- Self-signed certificate support
- Custom header support
- CMake FetchContent integration for zero-install setup

### Services
#### Account
The Account service allows you to authenticate and manage a user account.
- `get()` - Get the currently logged in user.
- `create()` - Use this endpoint to allow a new user to register a new account in your project. After the user registration completes successfully, you can use the [/account/verfication](https://appwrite.io/docs/references/cloud/client-web/account#createVerification) route to start verifying the user email address. To allow the new user to login to their new account, you need to create a new [account session](https://appwrite.io/docs/references/cloud/client-web/account#createEmailSession).
- `updateEmail()` - Update currently logged in user account email address. After changing user address, the user confirmation status will get reset. A new confirmation email is not sent automatically however you can use the send confirmation email endpoint again to send the confirmation email. For security measures, user password is required to complete this request.
This endpoint can also be used to convert an anonymous account to a normal one, by passing an email address and a new password.

- `listIdentities()` - Get the list of identities for the currently logged in user.
- `deleteIdentity()` - Delete an identity by its unique ID.
- `createJWT()` - Use this endpoint to create a JSON Web Token. You can use the resulting JWT to authenticate on behalf of the current user when working with the Appwrite server-side API and SDKs. The JWT secret is valid for 15 minutes from its creation and will be invalid if the user will logout in that time frame.
- `listLogs()` - Get the list of latest security activity logs for the currently logged in user. Each log returns user IP address, location and date and time of log.
- `updateMFA()` - Enable or disable MFA on an account.
- `createMfaAuthenticator()` - Add an authenticator app to be used as an MFA factor. Verify the authenticator using the [verify authenticator](/docs/references/cloud/client-web/account#updateMfaAuthenticator) method.
- `createMFAAuthenticator()` - Add an authenticator app to be used as an MFA factor. Verify the authenticator using the [verify authenticator](/docs/references/cloud/client-web/account#updateMfaAuthenticator) method.
- `updateMfaAuthenticator()` - Verify an authenticator app after adding it using the [add authenticator](/docs/references/cloud/client-web/account#createMfaAuthenticator) method.
- `updateMFAAuthenticator()` - Verify an authenticator app after adding it using the [add authenticator](/docs/references/cloud/client-web/account#createMfaAuthenticator) method.
- `deleteMfaAuthenticator()` - Delete an authenticator for a user by ID.
- `deleteMFAAuthenticator()` - Delete an authenticator for a user by ID.
- `createMfaChallenge()` - Begin the process of MFA verification after sign-in. Finish the flow with [updateMfaChallenge](/docs/references/cloud/client-web/account#updateMfaChallenge) method.
- `createMFAChallenge()` - Begin the process of MFA verification after sign-in. Finish the flow with [updateMfaChallenge](/docs/references/cloud/client-web/account#updateMfaChallenge) method.
- `updateMfaChallenge()` - Complete the MFA challenge by providing the one-time password. Finish the process of MFA verification by providing the one-time password. To begin the flow, use [createMfaChallenge](/docs/references/cloud/client-web/account#createMfaChallenge) method.
- `updateMFAChallenge()` - Complete the MFA challenge by providing the one-time password. Finish the process of MFA verification by providing the one-time password. To begin the flow, use [createMfaChallenge](/docs/references/cloud/client-web/account#createMfaChallenge) method.
- `listMfaFactors()` - List the factors available on the account to be used as a MFA challange.
- `listMFAFactors()` - List the factors available on the account to be used as a MFA challange.
- `getMfaRecoveryCodes()` - Get recovery codes that can be used as backup for MFA flow. Before getting codes, they must be generated using [createMfaRecoveryCodes](/docs/references/cloud/client-web/account#createMfaRecoveryCodes) method. An OTP challenge is required to read recovery codes.
- `getMFARecoveryCodes()` - Get recovery codes that can be used as backup for MFA flow. Before getting codes, they must be generated using [createMfaRecoveryCodes](/docs/references/cloud/client-web/account#createMfaRecoveryCodes) method. An OTP challenge is required to read recovery codes.
- `createMfaRecoveryCodes()` - Generate recovery codes as backup for MFA flow. It&#039;s recommended to generate and show then immediately after user successfully adds their authehticator. Recovery codes can be used as a MFA verification type in [createMfaChallenge](/docs/references/cloud/client-web/account#createMfaChallenge) method.
- `createMFARecoveryCodes()` - Generate recovery codes as backup for MFA flow. It&#039;s recommended to generate and show then immediately after user successfully adds their authehticator. Recovery codes can be used as a MFA verification type in [createMfaChallenge](/docs/references/cloud/client-web/account#createMfaChallenge) method.
- `updateMfaRecoveryCodes()` - Regenerate recovery codes that can be used as backup for MFA flow. Before regenerating codes, they must be first generated using [createMfaRecoveryCodes](/docs/references/cloud/client-web/account#createMfaRecoveryCodes) method. An OTP challenge is required to regenreate recovery codes.
- `updateMFARecoveryCodes()` - Regenerate recovery codes that can be used as backup for MFA flow. Before regenerating codes, they must be first generated using [createMfaRecoveryCodes](/docs/references/cloud/client-web/account#createMfaRecoveryCodes) method. An OTP challenge is required to regenreate recovery codes.
- `updateName()` - Update currently logged in user account name.
- `updatePassword()` - Update currently logged in user password. For validation, user is required to pass in the new password, and the old password. For users created with OAuth, Team Invites and Magic URL, oldPassword is optional.
- `updatePhone()` - Update the currently logged in user&#039;s phone number. After updating the phone number, the phone verification status will be reset. A confirmation SMS is not sent automatically, however you can use the [POST /account/verification/phone](https://appwrite.io/docs/references/cloud/client-web/account#createPhoneVerification) endpoint to send a confirmation SMS.
- `getPrefs()` - Get the preferences as a key-value object for the currently logged in user.
- `updatePrefs()` - Update currently logged in user account preferences. The object you pass is stored as is, and replaces any previous value. The maximum allowed prefs size is 64kB and throws error if exceeded.
- `createRecovery()` - Sends the user an email with a temporary secret key for password reset. When the user clicks the confirmation link he is redirected back to your app password reset URL with the secret key and email address values attached to the URL query string. Use the query string params to submit a request to the [PUT /account/recovery](https://appwrite.io/docs/references/cloud/client-web/account#updateRecovery) endpoint to complete the process. The verification link sent to the user&#039;s email address is valid for 1 hour.
- `updateRecovery()` - Use this endpoint to complete the user account password reset. Both the **userId** and **secret** arguments will be passed as query parameters to the redirect URL you have provided when sending your request to the [POST /account/recovery](https://appwrite.io/docs/references/cloud/client-web/account#createRecovery) endpoint.

Please note that in order to avoid a [Redirect Attack](https://github.com/OWASP/CheatSheetSeries/blob/master/cheatsheets/Unvalidated_Redirects_and_Forwards_Cheat_Sheet.md) the only valid redirect URLs are the ones from domains you have set when adding your platforms in the console interface.
- `listSessions()` - Get the list of active sessions across different devices for the currently logged in user.
- `deleteSessions()` - Delete all sessions from the user account and remove any sessions cookies from the end client.
- `createAnonymousSession()` - Use this endpoint to allow a new user to register an anonymous account in your project. This route will also create a new session for the user. To allow the new user to convert an anonymous account to a normal account, you need to update its [email and password](https://appwrite.io/docs/references/cloud/client-web/account#updateEmail) or create an [OAuth2 session](https://appwrite.io/docs/references/cloud/client-web/account#CreateOAuth2Session).
- `createEmailPasswordSession()` - Allow the user to login into their account by providing a valid email and password combination. This route will create a new session for the user.

A user is limited to 10 active sessions at a time by default. [Learn more about session limits](https://appwrite.io/docs/authentication-security#limits).
- `updateMagicURLSession()` - Use this endpoint to create a session from token. Provide the **userId** and **secret** parameters from the successful response of authentication flows initiated by token creation. For example, magic URL and phone login.
- `updatePhoneSession()` - Use this endpoint to create a session from token. Provide the **userId** and **secret** parameters from the successful response of authentication flows initiated by token creation. For example, magic URL and phone login.
- `createSession()` - Use this endpoint to create a session from token. Provide the **userId** and **secret** parameters from the successful response of authentication flows initiated by token creation. For example, magic URL and phone login.
- `getSession()` - Use this endpoint to get a logged in user&#039;s session using a Session ID. Inputting &#039;current&#039; will return the current session being used.
- `updateSession()` - Use this endpoint to extend a session&#039;s length. Extending a session is useful when session expiry is short. If the session was created using an OAuth provider, this endpoint refreshes the access token from the provider.
- `deleteSession()` - Logout the user. Use &#039;current&#039; as the session ID to logout on this device, use a session ID to logout on another device. If you&#039;re looking to logout the user on all devices, use [Delete Sessions](https://appwrite.io/docs/references/cloud/client-web/account#deleteSessions) instead.
- `updateStatus()` - Block the currently logged in user account. Behind the scene, the user record is not deleted but permanently blocked from any access. To completely delete a user, use the Users API instead.
- `createEmailToken()` - Sends the user an email with a secret key for creating a session. If the email address has never been used, a **new account is created** using the provided `userId`. Otherwise, if the email address is already attached to an account, the **user ID is ignored**. Then, the user will receive an email with the one-time password. Use the returned user ID and secret and submit a request to the [POST /v1/account/sessions/token](https://appwrite.io/docs/references/cloud/client-web/account#createSession) endpoint to complete the login process. The secret sent to the user&#039;s email is valid for 15 minutes.

A user is limited to 10 active sessions at a time by default. [Learn more about session limits](https://appwrite.io/docs/authentication-security#limits).

- `createMagicURLToken()` - Sends the user an email with a secret key for creating a session. If the provided user ID has not been registered, a new user will be created. When the user clicks the link in the email, the user is redirected back to the URL you provided with the secret key and userId values attached to the URL query string. Use the query string parameters to submit a request to the [POST /v1/account/sessions/token](https://appwrite.io/docs/references/cloud/client-web/account#createSession) endpoint to complete the login process. The link sent to the user&#039;s email address is valid for 1 hour.

A user is limited to 10 active sessions at a time by default. [Learn more about session limits](https://appwrite.io/docs/authentication-security#limits).

- `createOAuth2Token()` - Allow the user to login to their account using the OAuth2 provider of their choice. Each OAuth2 provider should be enabled from the Appwrite console first. Use the success and failure arguments to provide a redirect URL&#039;s back to your app when login is completed. 

If authentication succeeds, `userId` and `secret` of a token will be appended to the success URL as query parameters. These can be used to create a new session using the [Create session](https://appwrite.io/docs/references/cloud/client-web/account#createSession) endpoint.

A user is limited to 10 active sessions at a time by default. [Learn more about session limits](https://appwrite.io/docs/authentication-security#limits).
- `createPhoneToken()` - Sends the user an SMS with a secret key for creating a session. If the provided user ID has not be registered, a new user will be created. Use the returned user ID and secret and submit a request to the [POST /v1/account/sessions/token](https://appwrite.io/docs/references/cloud/client-web/account#createSession) endpoint to complete the login process. The secret sent to the user&#039;s phone is valid for 15 minutes.

A user is limited to 10 active sessions at a time by default. [Learn more about session limits](https://appwrite.io/docs/authentication-security#limits).
- `createEmailVerification()` - Use this endpoint to send a verification message to your user email address to confirm they are the valid owners of that address. Both the **userId** and **secret** arguments will be passed as query parameters to the URL you have provided to be attached to the verification email. The provided URL should redirect the user back to your app and allow you to complete the verification process by verifying both the **userId** and **secret** parameters. Learn more about how to [complete the verification process](https://appwrite.io/docs/references/cloud/client-web/account#updateVerification). The verification link sent to the user&#039;s email address is valid for 7 days.

Please note that in order to avoid a [Redirect Attack](https://github.com/OWASP/CheatSheetSeries/blob/master/cheatsheets/Unvalidated_Redirects_and_Forwards_Cheat_Sheet.md), the only valid redirect URLs are the ones from domains you have set when adding your platforms in the console interface.

- `createVerification()` - Use this endpoint to send a verification message to your user email address to confirm they are the valid owners of that address. Both the **userId** and **secret** arguments will be passed as query parameters to the URL you have provided to be attached to the verification email. The provided URL should redirect the user back to your app and allow you to complete the verification process by verifying both the **userId** and **secret** parameters. Learn more about how to [complete the verification process](https://appwrite.io/docs/references/cloud/client-web/account#updateVerification). The verification link sent to the user&#039;s email address is valid for 7 days.

Please note that in order to avoid a [Redirect Attack](https://github.com/OWASP/CheatSheetSeries/blob/master/cheatsheets/Unvalidated_Redirects_and_Forwards_Cheat_Sheet.md), the only valid redirect URLs are the ones from domains you have set when adding your platforms in the console interface.

- `updateEmailVerification()` - Use this endpoint to complete the user email verification process. Use both the **userId** and **secret** parameters that were attached to your app URL to verify the user email ownership. If confirmed this route will return a 200 status code.
- `updateVerification()` - Use this endpoint to complete the user email verification process. Use both the **userId** and **secret** parameters that were attached to your app URL to verify the user email ownership. If confirmed this route will return a 200 status code.
- `createPhoneVerification()` - Use this endpoint to send a verification SMS to the currently logged in user. This endpoint is meant for use after updating a user&#039;s phone number using the [accountUpdatePhone](https://appwrite.io/docs/references/cloud/client-web/account#updatePhone) endpoint. Learn more about how to [complete the verification process](https://appwrite.io/docs/references/cloud/client-web/account#updatePhoneVerification). The verification code sent to the user&#039;s phone number is valid for 15 minutes.
- `updatePhoneVerification()` - Use this endpoint to complete the user phone verification process. Use the **userId** and **secret** that were sent to your user&#039;s phone number to verify the user email ownership. If confirmed this route will return a 200 status code.

#### Databases
The Databases service allows you to create structured collections of documents, query and filter lists of documents
- `list()` - Get a list of all databases from the current Appwrite project. You can use the search parameter to filter your results.
- `create()` - Create a new Database.

- `listTransactions()` - List transactions across all databases.
- `createTransaction()` - Create a new transaction.
- `getTransaction()` - Get a transaction by its unique ID.
- `updateTransaction()` - Update a transaction, to either commit or roll back its operations.
- `deleteTransaction()` - Delete a transaction by its unique ID.
- `createOperations()` - Create multiple operations in a single transaction.
- `get()` - Get a database by its unique ID. This endpoint response returns a JSON object with the database metadata.
- `update()` - Update a database by its unique ID.
- `delete()` - Delete a database by its unique ID. Only API keys with with databases.write scope can delete a database.
- `listCollections()` - Get a list of all collections that belong to the provided databaseId. You can use the search parameter to filter your results.
- `createCollection()` - Create a new Collection. Before using this route, you should create a new database resource using either a [server integration](https://appwrite.io/docs/server/databases#databasesCreateCollection) API or directly from your database console.
- `getCollection()` - Get a collection by its unique ID. This endpoint response returns a JSON object with the collection metadata.
- `updateCollection()` - Update a collection by its unique ID.
- `deleteCollection()` - Delete a collection by its unique ID. Only users with write permissions have access to delete this resource.
- `listAttributes()` - List attributes in the collection.
- `createBooleanAttribute()` - Create a boolean attribute.

- `updateBooleanAttribute()` - Update a boolean attribute. Changing the `default` value will not update already existing documents.
- `createDatetimeAttribute()` - Create a date time attribute according to the ISO 8601 standard.
- `updateDatetimeAttribute()` - Update a date time attribute. Changing the `default` value will not update already existing documents.
- `createEmailAttribute()` - Create an email attribute.

- `updateEmailAttribute()` - Update an email attribute. Changing the `default` value will not update already existing documents.

- `createEnumAttribute()` - Create an enum attribute. The `elements` param acts as a white-list of accepted values for this attribute. 

- `updateEnumAttribute()` - Update an enum attribute. Changing the `default` value will not update already existing documents.

- `createFloatAttribute()` - Create a float attribute. Optionally, minimum and maximum values can be provided.

- `updateFloatAttribute()` - Update a float attribute. Changing the `default` value will not update already existing documents.

- `createIntegerAttribute()` - Create an integer attribute. Optionally, minimum and maximum values can be provided.

- `updateIntegerAttribute()` - Update an integer attribute. Changing the `default` value will not update already existing documents.

- `createIpAttribute()` - Create IP address attribute.

- `updateIpAttribute()` - Update an ip attribute. Changing the `default` value will not update already existing documents.

- `createLineAttribute()` - Create a geometric line attribute.
- `updateLineAttribute()` - Update a line attribute. Changing the `default` value will not update already existing documents.
- `createLongtextAttribute()` - Create a longtext attribute.

- `updateLongtextAttribute()` - Update a longtext attribute. Changing the `default` value will not update already existing documents.

- `createMediumtextAttribute()` - Create a mediumtext attribute.

- `updateMediumtextAttribute()` - Update a mediumtext attribute. Changing the `default` value will not update already existing documents.

- `createPointAttribute()` - Create a geometric point attribute.
- `updatePointAttribute()` - Update a point attribute. Changing the `default` value will not update already existing documents.
- `createPolygonAttribute()` - Create a geometric polygon attribute.
- `updatePolygonAttribute()` - Update a polygon attribute. Changing the `default` value will not update already existing documents.
- `createRelationshipAttribute()` - Create relationship attribute. [Learn more about relationship attributes](https://appwrite.io/docs/databases-relationships#relationship-attributes).

- `updateRelationshipAttribute()` - Update relationship attribute. [Learn more about relationship attributes](https://appwrite.io/docs/databases-relationships#relationship-attributes).

- `createStringAttribute()` - Create a string attribute.

- `updateStringAttribute()` - Update a string attribute. Changing the `default` value will not update already existing documents.

- `createTextAttribute()` - Create a text attribute.

- `updateTextAttribute()` - Update a text attribute. Changing the `default` value will not update already existing documents.

- `createUrlAttribute()` - Create a URL attribute.

- `updateUrlAttribute()` - Update an url attribute. Changing the `default` value will not update already existing documents.

- `createVarcharAttribute()` - Create a varchar attribute.

- `updateVarcharAttribute()` - Update a varchar attribute. Changing the `default` value will not update already existing documents.

- `getAttribute()` - Get attribute by ID.
- `deleteAttribute()` - Deletes an attribute.
- `listDocuments()` - Get a list of all the user&#039;s documents in a given collection. You can use the query params to filter your results.
- `createDocument()` - Create a new Document. Before using this route, you should create a new collection resource using either a [server integration](https://appwrite.io/docs/server/databases#databasesCreateCollection) API or directly from your database console.
- `createDocuments()` - Create new Documents. Before using this route, you should create a new collection resource using either a [server integration](https://appwrite.io/docs/server/databases#databasesCreateCollection) API or directly from your database console.
- `upsertDocuments()` - Create or update Documents. Before using this route, you should create a new collection resource using either a [server integration](https://appwrite.io/docs/server/databases#databasesCreateCollection) API or directly from your database console.

- `updateDocuments()` - Update all documents that match your queries, if no queries are submitted then all documents are updated. You can pass only specific fields to be updated.
- `deleteDocuments()` - Bulk delete documents using queries, if no queries are passed then all documents are deleted.
- `getDocument()` - Get a document by its unique ID. This endpoint response returns a JSON object with the document data.
- `upsertDocument()` - Create or update a Document. Before using this route, you should create a new collection resource using either a [server integration](https://appwrite.io/docs/server/databases#databasesCreateCollection) API or directly from your database console.
- `updateDocument()` - Update a document by its unique ID. Using the patch method you can pass only specific fields that will get updated.
- `deleteDocument()` - Delete a document by its unique ID.
- `decrementDocumentAttribute()` - Decrement a specific attribute of a document by a given value.
- `incrementDocumentAttribute()` - Increment a specific attribute of a document by a given value.
- `listIndexes()` - List indexes in the collection.
- `createIndex()` - Creates an index on the attributes listed. Your index should include all the attributes you will query in a single request.
Attributes can be `key`, `fulltext`, and `unique`.
- `getIndex()` - Get an index by its unique ID.
- `deleteIndex()` - Delete an index.

#### Functions
The Functions Service allows you view, create and manage your Cloud Functions.
- `list()` - Get a list of all the project&#039;s functions. You can use the query params to filter your results.
- `create()` - Create a new function. You can pass a list of [permissions](https://appwrite.io/docs/permissions) to allow different project users or team with access to execute the function using the client API.
- `listRuntimes()` - Get a list of all runtimes that are currently active on your instance.
- `listSpecifications()` - List allowed function specifications for this instance.
- `get()` - Get a function by its unique ID.
- `update()` - Update function by its unique ID.
- `delete()` - Delete a function by its unique ID.
- `updateFunctionDeployment()` - Update the function active deployment. Use this endpoint to switch the code deployment that should be used when visitor opens your function.
- `listDeployments()` - Get a list of all the function&#039;s code deployments. You can use the query params to filter your results.
- `createDeployment()` - Create a new function code deployment. Use this endpoint to upload a new version of your code function. To execute your newly uploaded code, you&#039;ll need to update the function&#039;s deployment to use your new deployment UID.

This endpoint accepts a tar.gz file compressed with your code. Make sure to include any dependencies your code has within the compressed file. You can learn more about code packaging in the [Appwrite Cloud Functions tutorial](https://appwrite.io/docs/functions).

Use the &quot;command&quot; param to set the entrypoint used to execute your code.
- `createDuplicateDeployment()` - Create a new build for an existing function deployment. This endpoint allows you to rebuild a deployment with the updated function configuration, including its entrypoint and build commands if they have been modified. The build process will be queued and executed asynchronously. The original deployment&#039;s code will be preserved and used for the new build.
- `createTemplateDeployment()` - Create a deployment based on a template.

Use this endpoint with combination of [listTemplates](https://appwrite.io/docs/products/functions/templates) to find the template details.
- `createVcsDeployment()` - Create a deployment when a function is connected to VCS.

This endpoint lets you create deployment from a branch, commit, or a tag.
- `getDeployment()` - Get a function deployment by its unique ID.
- `deleteDeployment()` - Delete a code deployment by its unique ID.
- `getDeploymentDownload()` - Get a function deployment content by its unique ID. The endpoint response return with a &#039;Content-Disposition: attachment&#039; header that tells the browser to start downloading the file to user downloads directory.
- `updateDeploymentStatus()` - Cancel an ongoing function deployment build. If the build is already in progress, it will be stopped and marked as canceled. If the build hasn&#039;t started yet, it will be marked as canceled without executing. You cannot cancel builds that have already completed (status &#039;ready&#039;) or failed. The response includes the final build status and details.
- `listExecutions()` - Get a list of all the current user function execution logs. You can use the query params to filter your results.
- `createExecution()` - Trigger a function execution. The returned object will return you the current execution status. You can ping the `Get Execution` endpoint to get updates on the current execution status. Once this endpoint is called, your function execution process will start asynchronously.
- `getExecution()` - Get a function execution log by its unique ID.
- `deleteExecution()` - Delete a function execution by its unique ID.
- `listVariables()` - Get a list of all variables of a specific function.
- `createVariable()` - Create a new function environment variable. These variables can be accessed in the function at runtime as environment variables.
- `getVariable()` - Get a variable by its unique ID.
- `updateVariable()` - Update variable by its unique ID.
- `deleteVariable()` - Delete a variable by its unique ID.

#### Storage
The Storage service allows you to manage your project files.
- `listBuckets()` - Get a list of all the storage buckets. You can use the query params to filter your results.
- `createBucket()` - Create a new storage bucket.
- `getBucket()` - Get a storage bucket by its unique ID. This endpoint response returns a JSON object with the storage bucket metadata.
- `updateBucket()` - Update a storage bucket by its unique ID.
- `deleteBucket()` - Delete a storage bucket by its unique ID.
- `listFiles()` - Get a list of all the user files. You can use the query params to filter your results.
- `createFile()` - Create a new file. Before using this route, you should create a new bucket resource using either a [server integration](https://appwrite.io/docs/server/storage#storageCreateBucket) API or directly from your Appwrite console.

Larger files should be uploaded using multiple requests with the [content-range](https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Content-Range) header to send a partial request with a maximum supported chunk of `5MB`. The `content-range` header values should always be in bytes.

When the first request is sent, the server will return the **File** object, and the subsequent part request must include the file&#039;s **id** in `x-appwrite-id` header to allow the server to know that the partial upload is for the existing file and not for a new one.

If you&#039;re creating a new file using one of the Appwrite SDKs, all the chunking logic will be managed by the SDK internally.

- `getFile()` - Get a file by its unique ID. This endpoint response returns a JSON object with the file metadata.
- `updateFile()` - Update a file by its unique ID. Only users with write permissions have access to update this resource.
- `deleteFile()` - Delete a file by its unique ID. Only users with write permissions have access to delete this resource.
- `getFileDownload()` - Get a file content by its unique ID. The endpoint response return with a &#039;Content-Disposition: attachment&#039; header that tells the browser to start downloading the file to user downloads directory.
- `getFilePreview()` - Get a file preview image. Currently, this method supports preview for image files (jpg, png, and gif), other supported formats, like pdf, docs, slides, and spreadsheets, will return the file icon image. You can also pass query string arguments for cutting and resizing your preview image. Preview is supported only for image files smaller than 10MB.
- `getFileView()` - Get a file content by its unique ID. This endpoint is similar to the download method but returns with no  &#039;Content-Disposition: attachment&#039; header.

#### TablesDB

- `list()` - Get a list of all databases from the current Appwrite project. You can use the search parameter to filter your results.
- `create()` - Create a new Database.

- `listTransactions()` - List transactions across all databases.
- `createTransaction()` - Create a new transaction.
- `getTransaction()` - Get a transaction by its unique ID.
- `updateTransaction()` - Update a transaction, to either commit or roll back its operations.
- `deleteTransaction()` - Delete a transaction by its unique ID.
- `createOperations()` - Create multiple operations in a single transaction.
- `get()` - Get a database by its unique ID. This endpoint response returns a JSON object with the database metadata.
- `update()` - Update a database by its unique ID.
- `delete()` - Delete a database by its unique ID. Only API keys with with databases.write scope can delete a database.
- `listTables()` - Get a list of all tables that belong to the provided databaseId. You can use the search parameter to filter your results.
- `createTable()` - Create a new Table. Before using this route, you should create a new database resource using either a [server integration](https://appwrite.io/docs/references/cloud/server-dart/tablesDB#createTable) API or directly from your database console.
- `getTable()` - Get a table by its unique ID. This endpoint response returns a JSON object with the table metadata.
- `updateTable()` - Update a table by its unique ID.
- `deleteTable()` - Delete a table by its unique ID. Only users with write permissions have access to delete this resource.
- `listColumns()` - List columns in the table.
- `createBooleanColumn()` - Create a boolean column.

- `updateBooleanColumn()` - Update a boolean column. Changing the `default` value will not update already existing rows.
- `createDatetimeColumn()` - Create a date time column according to the ISO 8601 standard.
- `updateDatetimeColumn()` - Update a date time column. Changing the `default` value will not update already existing rows.
- `createEmailColumn()` - Create an email column.

- `updateEmailColumn()` - Update an email column. Changing the `default` value will not update already existing rows.

- `createEnumColumn()` - Create an enumeration column. The `elements` param acts as a white-list of accepted values for this column.
- `updateEnumColumn()` - Update an enum column. Changing the `default` value will not update already existing rows.

- `createFloatColumn()` - Create a float column. Optionally, minimum and maximum values can be provided.

- `updateFloatColumn()` - Update a float column. Changing the `default` value will not update already existing rows.

- `createIntegerColumn()` - Create an integer column. Optionally, minimum and maximum values can be provided.

- `updateIntegerColumn()` - Update an integer column. Changing the `default` value will not update already existing rows.

- `createIpColumn()` - Create IP address column.

- `updateIpColumn()` - Update an ip column. Changing the `default` value will not update already existing rows.

- `createLineColumn()` - Create a geometric line column.
- `updateLineColumn()` - Update a line column. Changing the `default` value will not update already existing rows.
- `createLongtextColumn()` - Create a longtext column.

- `updateLongtextColumn()` - Update a longtext column. Changing the `default` value will not update already existing rows.

- `createMediumtextColumn()` - Create a mediumtext column.

- `updateMediumtextColumn()` - Update a mediumtext column. Changing the `default` value will not update already existing rows.

- `createPointColumn()` - Create a geometric point column.
- `updatePointColumn()` - Update a point column. Changing the `default` value will not update already existing rows.
- `createPolygonColumn()` - Create a geometric polygon column.
- `updatePolygonColumn()` - Update a polygon column. Changing the `default` value will not update already existing rows.
- `createRelationshipColumn()` - Create relationship column. [Learn more about relationship columns](https://appwrite.io/docs/databases-relationships#relationship-columns).

- `createStringColumn()` - Create a string column.

- `updateStringColumn()` - Update a string column. Changing the `default` value will not update already existing rows.

- `createTextColumn()` - Create a text column.

- `updateTextColumn()` - Update a text column. Changing the `default` value will not update already existing rows.

- `createUrlColumn()` - Create a URL column.

- `updateUrlColumn()` - Update an url column. Changing the `default` value will not update already existing rows.

- `createVarcharColumn()` - Create a varchar column.

- `updateVarcharColumn()` - Update a varchar column. Changing the `default` value will not update already existing rows.

- `getColumn()` - Get column by ID.
- `deleteColumn()` - Deletes a column.
- `updateRelationshipColumn()` - Update relationship column. [Learn more about relationship columns](https://appwrite.io/docs/databases-relationships#relationship-columns).

- `listIndexes()` - List indexes on the table.
- `createIndex()` - Creates an index on the columns listed. Your index should include all the columns you will query in a single request.
Type can be `key`, `fulltext`, or `unique`.
- `getIndex()` - Get index by ID.
- `deleteIndex()` - Delete an index.
- `listRows()` - Get a list of all the user&#039;s rows in a given table. You can use the query params to filter your results.
- `createRow()` - Create a new Row. Before using this route, you should create a new table resource using either a [server integration](https://appwrite.io/docs/references/cloud/server-dart/tablesDB#createTable) API or directly from your database console.
- `createRows()` - Create new Rows. Before using this route, you should create a new table resource using either a [server integration](https://appwrite.io/docs/references/cloud/server-dart/tablesDB#createTable) API or directly from your database console.
- `upsertRows()` - Create or update Rows. Before using this route, you should create a new table resource using either a [server integration](https://appwrite.io/docs/references/cloud/server-dart/tablesDB#createTable) API or directly from your database console.

- `updateRows()` - Update all rows that match your queries, if no queries are submitted then all rows are updated. You can pass only specific fields to be updated.
- `deleteRows()` - Bulk delete rows using queries, if no queries are passed then all rows are deleted.
- `getRow()` - Get a row by its unique ID. This endpoint response returns a JSON object with the row data.
- `upsertRow()` - Create or update a Row. Before using this route, you should create a new table resource using either a [server integration](https://appwrite.io/docs/references/cloud/server-dart/tablesDB#createTable) API or directly from your database console.
- `updateRow()` - Update a row by its unique ID. Using the patch method you can pass only specific fields that will get updated.
- `deleteRow()` - Delete a row by its unique ID.
- `decrementRowColumn()` - Decrement a specific column of a row by a given value.
- `incrementRowColumn()` - Increment a specific column of a row by a given value.

#### Teams
The Teams service allows you to group users of your project and to enable them to share read and write access to your project resources
- `list()` - Get a list of all the teams in which the current user is a member. You can use the parameters to filter your results.
- `create()` - Create a new team. The user who creates the team will automatically be assigned as the owner of the team. Only the users with the owner role can invite new members, add new owners and delete or update the team.
- `get()` - Get a team by its ID. All team members have read access for this resource.
- `updateName()` - Update the team&#039;s name by its unique ID.
- `delete()` - Delete a team using its ID. Only team members with the owner role can delete the team.
- `listMemberships()` - Use this endpoint to list a team&#039;s members using the team&#039;s ID. All team members have read access to this endpoint. Hide sensitive attributes from the response by toggling membership privacy in the Console.
- `createMembership()` - Invite a new member to join your team. Provide an ID for existing users, or invite unregistered users using an email or phone number. If initiated from a Client SDK, Appwrite will send an email or sms with a link to join the team to the invited user, and an account will be created for them if one doesn&#039;t exist. If initiated from a Server SDK, the new member will be added automatically to the team.

You only need to provide one of a user ID, email, or phone number. Appwrite will prioritize accepting the user ID &gt; email &gt; phone number if you provide more than one of these parameters.

Use the `url` parameter to redirect the user from the invitation email to your app. After the user is redirected, use the [Update Team Membership Status](https://appwrite.io/docs/references/cloud/client-web/teams#updateMembershipStatus) endpoint to allow the user to accept the invitation to the team. 

Please note that to avoid a [Redirect Attack](https://github.com/OWASP/CheatSheetSeries/blob/master/cheatsheets/Unvalidated_Redirects_and_Forwards_Cheat_Sheet.md) Appwrite will accept the only redirect URLs under the domains you have added as a platform on the Appwrite Console.

- `getMembership()` - Get a team member by the membership unique id. All team members have read access for this resource. Hide sensitive attributes from the response by toggling membership privacy in the Console.
- `updateMembership()` - Modify the roles of a team member. Only team members with the owner role have access to this endpoint. Learn more about [roles and permissions](https://appwrite.io/docs/permissions).

- `deleteMembership()` - This endpoint allows a user to leave a team or for a team owner to delete the membership of any other team member. You can also use this endpoint to delete a user membership even if it is not accepted.
- `updateMembershipStatus()` - Use this endpoint to allow a user to accept an invitation to join a team after being redirected back to your app from the invitation email received by the user.

If the request is successful, a session for the user is automatically created.

- `getPrefs()` - Get the team&#039;s shared preferences by its unique ID. If a preference doesn&#039;t need to be shared by all team members, prefer storing them in [user preferences](https://appwrite.io/docs/references/cloud/client-web/account#getPrefs).
- `updatePrefs()` - Update the team&#039;s preferences by its unique ID. The object you pass is stored as is and replaces any previous value. The maximum allowed prefs size is 64kB and throws an error if exceeded.

#### Users
The Users service allows you to manage your project users.
- `list()` - Get a list of all the project&#039;s users. You can use the query params to filter your results.
- `create()` - Create a new user.
- `createArgon2User()` - Create a new user. Password provided must be hashed with the [Argon2](https://en.wikipedia.org/wiki/Argon2) algorithm. Use the [POST /users](https://appwrite.io/docs/server/users#usersCreate) endpoint to create users with a plain text password.
- `createBcryptUser()` - Create a new user. Password provided must be hashed with the [Bcrypt](https://en.wikipedia.org/wiki/Bcrypt) algorithm. Use the [POST /users](https://appwrite.io/docs/server/users#usersCreate) endpoint to create users with a plain text password.
- `listIdentities()` - Get identities for all users.
- `deleteIdentity()` - Delete an identity by its unique ID.
- `createMD5User()` - Create a new user. Password provided must be hashed with the [MD5](https://en.wikipedia.org/wiki/MD5) algorithm. Use the [POST /users](https://appwrite.io/docs/server/users#usersCreate) endpoint to create users with a plain text password.
- `createPHPassUser()` - Create a new user. Password provided must be hashed with the [PHPass](https://www.openwall.com/phpass/) algorithm. Use the [POST /users](https://appwrite.io/docs/server/users#usersCreate) endpoint to create users with a plain text password.
- `createScryptUser()` - Create a new user. Password provided must be hashed with the [Scrypt](https://github.com/Tarsnap/scrypt) algorithm. Use the [POST /users](https://appwrite.io/docs/server/users#usersCreate) endpoint to create users with a plain text password.
- `createScryptModifiedUser()` - Create a new user. Password provided must be hashed with the [Scrypt Modified](https://gist.github.com/Meldiron/eecf84a0225eccb5a378d45bb27462cc) algorithm. Use the [POST /users](https://appwrite.io/docs/server/users#usersCreate) endpoint to create users with a plain text password.
- `createSHAUser()` - Create a new user. Password provided must be hashed with the [SHA](https://en.wikipedia.org/wiki/Secure_Hash_Algorithm) algorithm. Use the [POST /users](https://appwrite.io/docs/server/users#usersCreate) endpoint to create users with a plain text password.
- `get()` - Get a user by its unique ID.
- `delete()` - Delete a user by its unique ID, thereby releasing it&#039;s ID. Since ID is released and can be reused, all user-related resources like documents or storage files should be deleted before user deletion. If you want to keep ID reserved, use the [updateStatus](https://appwrite.io/docs/server/users#usersUpdateStatus) endpoint instead.
- `updateEmail()` - Update the user email by its unique ID.
- `updateImpersonator()` - Enable or disable whether a user can impersonate other users. When impersonation headers are used, the request runs as the target user for API behavior, while internal audit logs still attribute the action to the original impersonator and store the impersonated target details only in internal audit payload data.

- `createJWT()` - Use this endpoint to create a JSON Web Token for user by its unique ID. You can use the resulting JWT to authenticate on behalf of the user. The JWT secret will become invalid if the session it uses gets deleted.
- `updateLabels()` - Update the user labels by its unique ID. 

Labels can be used to grant access to resources. While teams are a way for user&#039;s to share access to a resource, labels can be defined by the developer to grant access without an invitation. See the [Permissions docs](https://appwrite.io/docs/permissions) for more info.
- `listLogs()` - Get the user activity logs list by its unique ID.
- `listMemberships()` - Get the user membership list by its unique ID.
- `updateMfa()` - Enable or disable MFA on a user account.
- `updateMFA()` - Enable or disable MFA on a user account.
- `deleteMfaAuthenticator()` - Delete an authenticator app.
- `deleteMFAAuthenticator()` - Delete an authenticator app.
- `listMfaFactors()` - List the factors available on the account to be used as a MFA challange.
- `listMFAFactors()` - List the factors available on the account to be used as a MFA challange.
- `getMfaRecoveryCodes()` - Get recovery codes that can be used as backup for MFA flow by User ID. Before getting codes, they must be generated using [createMfaRecoveryCodes](/docs/references/cloud/client-web/account#createMfaRecoveryCodes) method.
- `getMFARecoveryCodes()` - Get recovery codes that can be used as backup for MFA flow by User ID. Before getting codes, they must be generated using [createMfaRecoveryCodes](/docs/references/cloud/client-web/account#createMfaRecoveryCodes) method.
- `updateMfaRecoveryCodes()` - Regenerate recovery codes that can be used as backup for MFA flow by User ID. Before regenerating codes, they must be first generated using [createMfaRecoveryCodes](/docs/references/cloud/client-web/account#createMfaRecoveryCodes) method.
- `updateMFARecoveryCodes()` - Regenerate recovery codes that can be used as backup for MFA flow by User ID. Before regenerating codes, they must be first generated using [createMfaRecoveryCodes](/docs/references/cloud/client-web/account#createMfaRecoveryCodes) method.
- `createMfaRecoveryCodes()` - Generate recovery codes used as backup for MFA flow for User ID. Recovery codes can be used as a MFA verification type in [createMfaChallenge](/docs/references/cloud/client-web/account#createMfaChallenge) method by client SDK.
- `createMFARecoveryCodes()` - Generate recovery codes used as backup for MFA flow for User ID. Recovery codes can be used as a MFA verification type in [createMfaChallenge](/docs/references/cloud/client-web/account#createMfaChallenge) method by client SDK.
- `updateName()` - Update the user name by its unique ID.
- `updatePassword()` - Update the user password by its unique ID.
- `updatePhone()` - Update the user phone by its unique ID.
- `getPrefs()` - Get the user preferences by its unique ID.
- `updatePrefs()` - Update the user preferences by its unique ID. The object you pass is stored as is, and replaces any previous value. The maximum allowed prefs size is 64kB and throws error if exceeded.
- `listSessions()` - Get the user sessions list by its unique ID.
- `createSession()` - Creates a session for a user. Returns an immediately usable session object.

If you want to generate a token for a custom authentication flow, use the [POST /users/{userId}/tokens](https://appwrite.io/docs/server/users#createToken) endpoint.
- `deleteSessions()` - Delete all user&#039;s sessions by using the user&#039;s unique ID.
- `deleteSession()` - Delete a user sessions by its unique ID.
- `updateStatus()` - Update the user status by its unique ID. Use this endpoint as an alternative to deleting a user if you want to keep user&#039;s ID reserved.
- `listTargets()` - List the messaging targets that are associated with a user.
- `createTarget()` - Create a messaging target.
- `getTarget()` - Get a user&#039;s push notification target by ID.
- `updateTarget()` - Update a messaging target.
- `deleteTarget()` - Delete a messaging target.
- `createToken()` - Returns a token with a secret key for creating a session. Use the user ID and secret and submit a request to the [PUT /account/sessions/token](https://appwrite.io/docs/references/cloud/client-web/account#createSession) endpoint to complete the login process.

- `updateEmailVerification()` - Update the user email verification status by its unique ID.
- `updatePhoneVerification()` - Update the user phone verification status by its unique ID.

#### Webhooks

- `list()` - Get a list of all webhooks belonging to the project. You can use the query params to filter your results.
- `create()` - Create a new webhook. Use this endpoint to configure a URL that will receive events from Appwrite when specific events occur.
- `get()` - Get a webhook by its unique ID. This endpoint returns details about a specific webhook configured for a project. 
- `update()` - Update a webhook by its unique ID. Use this endpoint to update the URL, events, or status of an existing webhook.
- `delete()` - Delete a webhook by its unique ID. Once deleted, the webhook will no longer receive project events. 
- `updateSecret()` - Update the webhook signing key. This endpoint can be used to regenerate the signing key used to sign and validate payload deliveries for a specific webhook.


### Models
- `RowList` - Rows List
- `DocumentList` - Documents List
- `TableList` - Tables List
- `CollectionList` - Collections List
- `DatabaseList` - Databases List
- `IndexList` - Indexes List
- `ColumnIndexList` - Column Indexes List
- `UserList` - Users List
- `SessionList` - Sessions List
- `IdentityList` - Identities List
- `LogList` - Logs List
- `FileList` - Files List
- `BucketList` - Buckets List
- `TeamList` - Teams List
- `MembershipList` - Memberships List
- `FunctionList` - Functions List
- `RuntimeList` - Runtimes List
- `DeploymentList` - Deployments List
- `ExecutionList` - Executions List
- `WebhookList` - Webhooks List
- `VariableList` - Variables List
- `TargetList` - Target list
- `TransactionList` - Transaction List
- `SpecificationList` - Specifications List
- `Database` - Database
- `Collection` - Collection
- `AttributeList` - Attributes List
- `AttributeString` - AttributeString
- `AttributeInteger` - AttributeInteger
- `AttributeFloat` - AttributeFloat
- `AttributeBoolean` - AttributeBoolean
- `AttributeEmail` - AttributeEmail
- `AttributeEnum` - AttributeEnum
- `AttributeIp` - AttributeIP
- `AttributeUrl` - AttributeURL
- `AttributeDatetime` - AttributeDatetime
- `AttributeRelationship` - AttributeRelationship
- `AttributePoint` - AttributePoint
- `AttributeLine` - AttributeLine
- `AttributePolygon` - AttributePolygon
- `AttributeVarchar` - AttributeVarchar
- `AttributeText` - AttributeText
- `AttributeMediumtext` - AttributeMediumtext
- `AttributeLongtext` - AttributeLongtext
- `Table` - Table
- `ColumnList` - Columns List
- `ColumnString` - ColumnString
- `ColumnInteger` - ColumnInteger
- `ColumnFloat` - ColumnFloat
- `ColumnBoolean` - ColumnBoolean
- `ColumnEmail` - ColumnEmail
- `ColumnEnum` - ColumnEnum
- `ColumnIp` - ColumnIP
- `ColumnUrl` - ColumnURL
- `ColumnDatetime` - ColumnDatetime
- `ColumnRelationship` - ColumnRelationship
- `ColumnPoint` - ColumnPoint
- `ColumnLine` - ColumnLine
- `ColumnPolygon` - ColumnPolygon
- `ColumnVarchar` - ColumnVarchar
- `ColumnText` - ColumnText
- `ColumnMediumtext` - ColumnMediumtext
- `ColumnLongtext` - ColumnLongtext
- `Index` - Index
- `ColumnIndex` - Index
- `Row` - Row
- `Document` - Document
- `Log` - Log
- `User` - User
- `AlgoMd5` - AlgoMD5
- `AlgoSha` - AlgoSHA
- `AlgoPhpass` - AlgoPHPass
- `AlgoBcrypt` - AlgoBcrypt
- `AlgoScrypt` - AlgoScrypt
- `AlgoScryptModified` - AlgoScryptModified
- `AlgoArgon2` - AlgoArgon2
- `Preferences` - Preferences
- `Session` - Session
- `Identity` - Identity
- `Token` - Token
- `Jwt` - JWT
- `File` - File
- `Bucket` - Bucket
- `Team` - Team
- `Membership` - Membership
- `Function` - Function
- `Runtime` - Runtime
- `Deployment` - Deployment
- `Execution` - Execution
- `Webhook` - Webhook
- `Variable` - Variable
- `Headers` - Headers
- `Specification` - Specification
- `MfaChallenge` - MFA Challenge
- `MfaRecoveryCodes` - MFA Recovery Codes
- `MfaType` - MFAType
- `MfaFactors` - MFAFactors
- `Transaction` - Transaction
- `Target` - Target

### Dependencies
- [cpr](https://github.com/libcpr/cpr) 1.10.5+ for HTTP client
- [nlohmann/json](https://github.com/nlohmann/json) 3.11.3+ for JSON serialization

[0.0.1]: https://github.com/repoowner/sdk-for-cpp/releases/tag/0.0.1
