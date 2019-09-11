import "package:dart_appwrite/service.dart";
import "package:dart_appwrite/client.dart";

class Database extends Service {
     
     Database(Client client): super(client);

     /// Get a list of all the user collections. You can use the query params to
     /// filter your results. On admin mode, this endpoint will return a list of all
     /// of the project collections. [Learn more about different API
     /// modes](/docs/modes).
    listCollections({search = null, limit = 25, offset = 0, orderType = 'ASC'}) async {
       String path = '/database';

       var params = {
         'search': search,
         'limit': limit,
         'offset': offset,
         'orderType': orderType,
       };

       return await this.client.call('get', path: path, params: params);
    }
     /// Create a new Collection.
    createCollection({name, read = const [], write = const [], rules = const []}) async {
       String path = '/database';

       var params = {
         'name': name,
         'read': read,
         'write': write,
         'rules': rules,
       };

       return await this.client.call('post', path: path, params: params);
    }
     /// Get collection by its unique ID. This endpoint response returns a JSON
     /// object with the collection metadata.
    getCollection({collectionId}) async {
       String path = '/database/{collectionId}'.replaceAll(RegExp('{collectionId}'), collectionId);

       var params = {
       };

       return await this.client.call('get', path: path, params: params);
    }
     /// Update collection by its unique ID.
    updateCollection({collectionId, name, read = const [], write = const [], rules = const []}) async {
       String path = '/database/{collectionId}'.replaceAll(RegExp('{collectionId}'), collectionId);

       var params = {
         'name': name,
         'read': read,
         'write': write,
         'rules': rules,
       };

       return await this.client.call('put', path: path, params: params);
    }
     /// Delete a collection by its unique ID. Only users with write permissions
     /// have access to delete this resource.
    deleteCollection({collectionId}) async {
       String path = '/database/{collectionId}'.replaceAll(RegExp('{collectionId}'), collectionId);

       var params = {
       };

       return await this.client.call('delete', path: path, params: params);
    }
     /// Get a list of all the user documents. You can use the query params to
     /// filter your results. On admin mode, this endpoint will return a list of all
     /// of the project documents. [Learn more about different API
     /// modes](/docs/modes).
    listDocuments({collectionId, filters = const [], offset = 0, limit = 50, orderField = '$uid', orderType = 'ASC', orderCast = 'string', search = null, first = 0, last = 0}) async {
       String path = '/database/{collectionId}/documents'.replaceAll(RegExp('{collectionId}'), collectionId);

       var params = {
         'filters': filters,
         'offset': offset,
         'limit': limit,
         'order-field': orderField,
         'order-type': orderType,
         'order-cast': orderCast,
         'search': search,
         'first': first,
         'last': last,
       };

       return await this.client.call('get', path: path, params: params);
    }
     /// Create a new Document.
    createDocument({collectionId, data, read = const [], write = const [], parentDocument = null, parentProperty = null, parentPropertyType = 'assign'}) async {
       String path = '/database/{collectionId}/documents'.replaceAll(RegExp('{collectionId}'), collectionId);

       var params = {
         'data': data,
         'read': read,
         'write': write,
         'parentDocument': parentDocument,
         'parentProperty': parentProperty,
         'parentPropertyType': parentPropertyType,
       };

       return await this.client.call('post', path: path, params: params);
    }
     /// Get document by its unique ID. This endpoint response returns a JSON object
     /// with the document data.
    getDocument({collectionId, documentId}) async {
       String path = '/database/{collectionId}/documents/{documentId}'.replaceAll(RegExp('{collectionId}'), collectionId).replaceAll(RegExp('{documentId}'), documentId);

       var params = {
       };

       return await this.client.call('get', path: path, params: params);
    }
    updateDocument({collectionId, documentId, data, read = const [], write = const []}) async {
       String path = '/database/{collectionId}/documents/{documentId}'.replaceAll(RegExp('{collectionId}'), collectionId).replaceAll(RegExp('{documentId}'), documentId);

       var params = {
         'data': data,
         'read': read,
         'write': write,
       };

       return await this.client.call('patch', path: path, params: params);
    }
     /// Delete document by its unique ID. This endpoint deletes only the parent
     /// documents, his attributes and relations to other documents. Child documents
     /// **will not** be deleted.
    deleteDocument({collectionId, documentId}) async {
       String path = '/database/{collectionId}/documents/{documentId}'.replaceAll(RegExp('{collectionId}'), collectionId).replaceAll(RegExp('{documentId}'), documentId);

       var params = {
       };

       return await this.client.call('delete', path: path, params: params);
    }
}