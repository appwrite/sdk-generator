# Users Service

## List Users

```http request
GET https://appwrite.io/v1/users
```

** Get a list of all the project&#039;s users. You can use the query params to filter your results. **

### Parameters

| Field Name | Type | Description | Default |
| --- | --- | --- | --- |
| search | string | Search term to filter your list results. Max length: 256 chars. |  |
| limit | integer | Results limit value. By default will return maximum 25 results. Maximum of 100 results allowed per request. | 25 |
| offset | integer | Results offset. The default value is 0. Use this param to manage pagination. | 0 |
| orderType | string | Order result by ASC or DESC order. | ASC |

## Create User

```http request
POST https://appwrite.io/v1/users
```

** Create a new user. **

### Parameters

| Field Name | Type | Description | Default |
| --- | --- | --- | --- |
| email | string | User email. |  |
| password | string | User password. Must be between 6 to 32 chars. |  |
| name | string | User name. Max length: 128 chars. |  |

## Get User

```http request
GET https://appwrite.io/v1/users/{userId}
```

** Get a user by its unique ID. **

### Parameters

| Field Name | Type | Description | Default |
| --- | --- | --- | --- |
| userId | string | **Required** User unique ID. |  |

## Delete User

```http request
DELETE https://appwrite.io/v1/users/{userId}
```

** Delete a user by its unique ID. **

### Parameters

| Field Name | Type | Description | Default |
| --- | --- | --- | --- |
| userId | string | **Required** User unique ID. |  |

## Update Email

```http request
PATCH https://appwrite.io/v1/users/{userId}/email
```

** Update the user email by its unique ID. **

### Parameters

| Field Name | Type | Description | Default |
| --- | --- | --- | --- |
| userId | string | **Required** User unique ID. |  |
| email | string | User email. |  |

## Get User Logs

```http request
GET https://appwrite.io/v1/users/{userId}/logs
```

** Get a user activity logs list by its unique ID. **

### Parameters

| Field Name | Type | Description | Default |
| --- | --- | --- | --- |
| userId | string | **Required** User unique ID. |  |

## Update Name

```http request
PATCH https://appwrite.io/v1/users/{userId}/name
```

** Update the user name by its unique ID. **

### Parameters

| Field Name | Type | Description | Default |
| --- | --- | --- | --- |
| userId | string | **Required** User unique ID. |  |
| name | string | User name. Max length: 128 chars. |  |

## Update Password

```http request
PATCH https://appwrite.io/v1/users/{userId}/password
```

** Update the user password by its unique ID. **

### Parameters

| Field Name | Type | Description | Default |
| --- | --- | --- | --- |
| userId | string | **Required** User unique ID. |  |
| password | string | New user password. Must be between 6 to 32 chars. |  |

## Get User Preferences

```http request
GET https://appwrite.io/v1/users/{userId}/prefs
```

** Get the user preferences by its unique ID. **

### Parameters

| Field Name | Type | Description | Default |
| --- | --- | --- | --- |
| userId | string | **Required** User unique ID. |  |

## Update User Preferences

```http request
PATCH https://appwrite.io/v1/users/{userId}/prefs
```

** Update the user preferences by its unique ID. You can pass only the specific settings you wish to update. **

### Parameters

| Field Name | Type | Description | Default |
| --- | --- | --- | --- |
| userId | string | **Required** User unique ID. |  |
| prefs | object | Prefs key-value JSON object. |  |

## Get User Sessions

```http request
GET https://appwrite.io/v1/users/{userId}/sessions
```

** Get the user sessions list by its unique ID. **

### Parameters

| Field Name | Type | Description | Default |
| --- | --- | --- | --- |
| userId | string | **Required** User unique ID. |  |

## Delete User Sessions

```http request
DELETE https://appwrite.io/v1/users/{userId}/sessions
```

** Delete all user&#039;s sessions by using the user&#039;s unique ID. **

### Parameters

| Field Name | Type | Description | Default |
| --- | --- | --- | --- |
| userId | string | **Required** User unique ID. |  |

## Delete User Session

```http request
DELETE https://appwrite.io/v1/users/{userId}/sessions/{sessionId}
```

** Delete a user sessions by its unique ID. **

### Parameters

| Field Name | Type | Description | Default |
| --- | --- | --- | --- |
| userId | string | **Required** User unique ID. |  |
| sessionId | string | **Required** User unique session ID. |  |

## Update User Status

```http request
PATCH https://appwrite.io/v1/users/{userId}/status
```

** Update the user status by its unique ID. **

### Parameters

| Field Name | Type | Description | Default |
| --- | --- | --- | --- |
| userId | string | **Required** User unique ID. |  |
| status | integer | User Status code. To activate the user pass 1, to block the user pass 2 and for disabling the user pass 0 |  |

## Update Email Verification

```http request
PATCH https://appwrite.io/v1/users/{userId}/verification
```

** Update the user email verification status by its unique ID. **

### Parameters

| Field Name | Type | Description | Default |
| --- | --- | --- | --- |
| userId | string | **Required** User unique ID. |  |
| emailVerification | boolean | User Email Verification Status. |  |

