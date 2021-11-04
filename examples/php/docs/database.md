# Database Service

## List Collections

```http request
GET https://appwrite.io/v1/database/collections
```

** Get a list of all the user collections. You can use the query params to filter your results. On admin mode, this endpoint will return a list of all of the project&#039;s collections. [Learn more about different API modes](/docs/admin). **

### Parameters

| Field Name | Type | Description | Default |
| --- | --- | --- | --- |
| search | string | Search term to filter your list results. Max length: 256 chars. |  |
| limit | integer | Results limit value. By default will return maximum 25 results. Maximum of 100 results allowed per request. | 25 |
| offset | integer | Results offset. The default value is 0. Use this param to manage pagination. | 0 |
| orderType | string | Order result by ASC or DESC order. | ASC |

## Create Collection

```http request
POST https://appwrite.io/v1/database/collections
```

** Create a new Collection. **

### Parameters

| Field Name | Type | Description | Default |
| --- | --- | --- | --- |
| name | string | Collection name. Max length: 128 chars. |  |
| read | array | An array of strings with read permissions. By default no user is granted with any read permissions. [learn more about permissions](/docs/permissions) and get a full list of available permissions. |  |
| write | array | An array of strings with write permissions. By default no user is granted with any write permissions. [learn more about permissions](/docs/permissions) and get a full list of available permissions. |  |
| rules | array | Array of [rule objects](/docs/rules). Each rule define a collection field name, data type and validation. |  |

## Get Collection

```http request
GET https://appwrite.io/v1/database/collections/{collectionId}
```

** Get a collection by its unique ID. This endpoint response returns a JSON object with the collection metadata. **

### Parameters

| Field Name | Type | Description | Default |
| --- | --- | --- | --- |
| collectionId | string | **Required** Collection unique ID. |  |

## Update Collection

```http request
PUT https://appwrite.io/v1/database/collections/{collectionId}
```

** Update a collection by its unique ID. **

### Parameters

| Field Name | Type | Description | Default |
| --- | --- | --- | --- |
| collectionId | string | **Required** Collection unique ID. |  |
| name | string | Collection name. Max length: 128 chars. |  |
| read | array | An array of strings with read permissions. By default inherits the existing read permissions. [learn more about permissions](/docs/permissions) and get a full list of available permissions. |  |
| write | array | An array of strings with write permissions. By default inherits the existing write permissions. [learn more about permissions](/docs/permissions) and get a full list of available permissions. |  |
| rules | array | Array of [rule objects](/docs/rules). Each rule define a collection field name, data type and validation. | [] |

## Delete Collection

```http request
DELETE https://appwrite.io/v1/database/collections/{collectionId}
```

** Delete a collection by its unique ID. Only users with write permissions have access to delete this resource. **

### Parameters

| Field Name | Type | Description | Default |
| --- | --- | --- | --- |
| collectionId | string | **Required** Collection unique ID. |  |

## List Documents

```http request
GET https://appwrite.io/v1/database/collections/{collectionId}/documents
```

** Get a list of all the user documents. You can use the query params to filter your results. On admin mode, this endpoint will return a list of all of the project&#039;s documents. [Learn more about different API modes](/docs/admin). **

### Parameters

| Field Name | Type | Description | Default |
| --- | --- | --- | --- |
| collectionId | string | **Required** Collection unique ID. You can create a new collection with validation rules using the Database service [server integration](/docs/server/database#createCollection). |  |
| filters | array | Array of filter strings. Each filter is constructed from a key name, comparison operator (=, !=, &gt;, &lt;, &lt;=, &gt;=) and a value. You can also use a dot (.) separator in attribute names to filter by child document attributes. Examples: &#039;name=John Doe&#039; or &#039;category.$id&gt;=5bed2d152c362&#039;. | [] |
| limit | integer | Maximum number of documents to return in response.  Use this value to manage pagination. By default will return maximum 25 results. Maximum of 100 results allowed per request. | 25 |
| offset | integer | Offset value. The default value is 0. Use this param to manage pagination. | 0 |
| orderField | string | Document field that results will be sorted by. |  |
| orderType | string | Order direction. Possible values are DESC for descending order, or ASC for ascending order. | ASC |
| orderCast | string | Order field type casting. Possible values are int, string, date, time or datetime. The database will attempt to cast the order field to the value you pass here. The default value is a string. | string |
| search | string | Search query. Enter any free text search. The database will try to find a match against all document attributes and children. Max length: 256 chars. |  |

## Create Document

```http request
POST https://appwrite.io/v1/database/collections/{collectionId}/documents
```

** Create a new Document. Before using this route, you should create a new collection resource using either a [server integration](/docs/server/database#databaseCreateCollection) API or directly from your database console. **

### Parameters

| Field Name | Type | Description | Default |
| --- | --- | --- | --- |
| collectionId | string | **Required** Collection unique ID. You can create a new collection with validation rules using the Database service [server integration](/docs/server/database#createCollection). |  |
| data | object | Document data as JSON object. |  |
| read | array | An array of strings with read permissions. By default only the current user is granted with read permissions. [learn more about permissions](/docs/permissions) and get a full list of available permissions. |  |
| write | array | An array of strings with write permissions. By default only the current user is granted with write permissions. [learn more about permissions](/docs/permissions) and get a full list of available permissions. |  |
| parentDocument | string | Parent document unique ID. Use when you want your new document to be a child of a parent document. |  |
| parentProperty | string | Parent document property name. Use when you want your new document to be a child of a parent document. |  |
| parentPropertyType | string | Parent document property connection type. You can set this value to **assign**, **append** or **prepend**, default value is assign. Use when you want your new document to be a child of a parent document. | assign |

## Get Document

```http request
GET https://appwrite.io/v1/database/collections/{collectionId}/documents/{documentId}
```

** Get a document by its unique ID. This endpoint response returns a JSON object with the document data. **

### Parameters

| Field Name | Type | Description | Default |
| --- | --- | --- | --- |
| collectionId | string | **Required** Collection unique ID. You can create a new collection with validation rules using the Database service [server integration](/docs/server/database#createCollection). |  |
| documentId | string | **Required** Document unique ID. |  |

## Update Document

```http request
PATCH https://appwrite.io/v1/database/collections/{collectionId}/documents/{documentId}
```

** Update a document by its unique ID. Using the patch method you can pass only specific fields that will get updated. **

### Parameters

| Field Name | Type | Description | Default |
| --- | --- | --- | --- |
| collectionId | string | **Required** Collection unique ID. You can create a new collection with validation rules using the Database service [server integration](/docs/server/database#createCollection). |  |
| documentId | string | **Required** Document unique ID. |  |
| data | object | Document data as JSON object. |  |
| read | array | An array of strings with read permissions. By default inherits the existing read permissions. [learn more about permissions](/docs/permissions) and get a full list of available permissions. |  |
| write | array | An array of strings with write permissions. By default inherits the existing write permissions. [learn more about permissions](/docs/permissions) and get a full list of available permissions. |  |

## Delete Document

```http request
DELETE https://appwrite.io/v1/database/collections/{collectionId}/documents/{documentId}
```

** Delete a document by its unique ID. This endpoint deletes only the parent documents, its attributes and relations to other documents. Child documents **will not** be deleted. **

### Parameters

| Field Name | Type | Description | Default |
| --- | --- | --- | --- |
| collectionId | string | **Required** Collection unique ID. You can create a new collection with validation rules using the Database service [server integration](/docs/server/database#createCollection). |  |
| documentId | string | **Required** Document unique ID. |  |

