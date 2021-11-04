# Storage Service

## List Files

```http request
GET https://appwrite.io/v1/storage/files
```

** Get a list of all the user files. You can use the query params to filter your results. On admin mode, this endpoint will return a list of all of the project&#039;s files. [Learn more about different API modes](/docs/admin). **

### Parameters

| Field Name | Type | Description | Default |
| --- | --- | --- | --- |
| search | string | Search term to filter your list results. Max length: 256 chars. |  |
| limit | integer | Results limit value. By default will return maximum 25 results. Maximum of 100 results allowed per request. | 25 |
| offset | integer | Results offset. The default value is 0. Use this param to manage pagination. | 0 |
| orderType | string | Order result by ASC or DESC order. | ASC |

## Create File

```http request
POST https://appwrite.io/v1/storage/files
```

** Create a new file. The user who creates the file will automatically be assigned to read and write access unless he has passed custom values for read and write arguments. **

### Parameters

| Field Name | Type | Description | Default |
| --- | --- | --- | --- |
| file | file | Binary file. |  |
| read | array | An array of strings with read permissions. By default only the current user is granted with read permissions. [learn more about permissions](/docs/permissions) and get a full list of available permissions. |  |
| write | array | An array of strings with write permissions. By default only the current user is granted with write permissions. [learn more about permissions](/docs/permissions) and get a full list of available permissions. |  |

## Get File

```http request
GET https://appwrite.io/v1/storage/files/{fileId}
```

** Get a file by its unique ID. This endpoint response returns a JSON object with the file metadata. **

### Parameters

| Field Name | Type | Description | Default |
| --- | --- | --- | --- |
| fileId | string | **Required** File unique ID. |  |

## Update File

```http request
PUT https://appwrite.io/v1/storage/files/{fileId}
```

** Update a file by its unique ID. Only users with write permissions have access to update this resource. **

### Parameters

| Field Name | Type | Description | Default |
| --- | --- | --- | --- |
| fileId | string | **Required** File unique ID. |  |
| read | array | An array of strings with read permissions. By default no user is granted with any read permissions. [learn more about permissions](/docs/permissions) and get a full list of available permissions. |  |
| write | array | An array of strings with write permissions. By default no user is granted with any write permissions. [learn more about permissions](/docs/permissions) and get a full list of available permissions. |  |

## Delete File

```http request
DELETE https://appwrite.io/v1/storage/files/{fileId}
```

** Delete a file by its unique ID. Only users with write permissions have access to delete this resource. **

### Parameters

| Field Name | Type | Description | Default |
| --- | --- | --- | --- |
| fileId | string | **Required** File unique ID. |  |

## Get File for Download

```http request
GET https://appwrite.io/v1/storage/files/{fileId}/download
```

** Get a file content by its unique ID. The endpoint response return with a &#039;Content-Disposition: attachment&#039; header that tells the browser to start downloading the file to user downloads directory. **

### Parameters

| Field Name | Type | Description | Default |
| --- | --- | --- | --- |
| fileId | string | **Required** File unique ID. |  |

## Get File Preview

```http request
GET https://appwrite.io/v1/storage/files/{fileId}/preview
```

** Get a file preview image. Currently, this method supports preview for image files (jpg, png, and gif), other supported formats, like pdf, docs, slides, and spreadsheets, will return the file icon image. You can also pass query string arguments for cutting and resizing your preview image. **

### Parameters

| Field Name | Type | Description | Default |
| --- | --- | --- | --- |
| fileId | string | **Required** File unique ID |  |
| width | integer | Resize preview image width, Pass an integer between 0 to 4000. | 0 |
| height | integer | Resize preview image height, Pass an integer between 0 to 4000. | 0 |
| gravity | string | Image crop gravity. Can be one of center,top-left,top,top-right,left,right,bottom-left,bottom,bottom-right | center |
| quality | integer | Preview image quality. Pass an integer between 0 to 100. Defaults to 100. | 100 |
| borderWidth | integer | Preview image border in pixels. Pass an integer between 0 to 100. Defaults to 0. | 0 |
| borderColor | string | Preview image border color. Use a valid HEX color, no # is needed for prefix. |  |
| borderRadius | integer | Preview image border radius in pixels. Pass an integer between 0 to 4000. | 0 |
| opacity | number | Preview image opacity. Only works with images having an alpha channel (like png). Pass a number between 0 to 1. | 1 |
| rotation | integer | Preview image rotation in degrees. Pass an integer between 0 and 360. | 0 |
| background | string | Preview image background color. Only works with transparent images (png). Use a valid HEX color, no # is needed for prefix. |  |
| output | string | Output format type (jpeg, jpg, png, gif and webp). |  |

## Get File for View

```http request
GET https://appwrite.io/v1/storage/files/{fileId}/view
```

** Get a file content by its unique ID. This endpoint is similar to the download method but returns with no  &#039;Content-Disposition: attachment&#039; header. **

### Parameters

| Field Name | Type | Description | Default |
| --- | --- | --- | --- |
| fileId | string | **Required** File unique ID. |  |

