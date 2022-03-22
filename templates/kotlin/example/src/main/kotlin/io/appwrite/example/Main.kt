package io.appwrite.example

import io.appwrite.Client
import io.appwrite.exceptions.AppwriteException
import io.appwrite.extensions.toJson
import io.appwrite.services.Database
import io.appwrite.services.Functions
import io.appwrite.services.Storage
import io.appwrite.services.Users
import kotlinx.coroutines.delay
import java.io.File
import kotlin.system.exitProcess

val client = Client()
    .setEndpoint("http://192.168.4.23/v1")
    .setProject("test")
    .setKey("2c0e2053019c010cd499e427e1289d1b7bf23825285a27107c7bfdb3d50ed7015f840be8182100efff134f313668679f745c8e0972a9b38df3f0c29e91c939f21aad8327d6eed62cbd66a46131e6cdecd446910ab2209241c22ef7a9db575e365862bdf2f4046c0caa1d4e4907ca9daf12015bd8ec18a05b658c80aacd9a6766")
    .setSelfSigned(true)

val database = Database(client)
val storage = Storage(client)
val functions = Functions(client)
val users = Users(client)

lateinit var collectionId: String
lateinit var documentId: String
var fileId: String = "unique()"
lateinit var bucketId: String
lateinit var userId: String
lateinit var functionId: String

suspend fun main() {
    // createUser("${Math.random()}@appwrite.io", "user@123", "Kotlin Player")
    // listUsers()
    // deleteUser()

    // createCollection()
    // listCollections()
    // createDocument()
    // listDocuments()
    // deleteDocument()
    // deleteCollection()

    // createFunction()
    // listFunctions()
    // deleteFunction()

    // createBucket()
    // listBuckets()
    uploadFile()
    // listFiles()
    // deleteFile()

    // println("Ran playground successfully!")
    // exitProcess(0)
}

suspend fun createUser(email: String, password: String, name: String) {
    println("Running create user API")
    val user = users.create(
        userId = "unique()",
        email,
        password,
        name
    )
    userId = user.id
    println(user.toJson())
}

suspend fun listUsers() {
    println("Running list users API")
    val users = users.list()
    println(users.toJson())
}

suspend fun deleteUser() {
    println("Running delete user API")
    users.delete(userId)
    println("User deleted")
}

suspend fun createCollection() {
    println("Running create collection API")

    val collection = database.createCollection(
        collectionId = "unique()",
        name = "Movies",
        permission = "document",
        read = arrayListOf("role:all"),
        write = arrayListOf("role:all")
    )
    collectionId = collection.id
    println(collection.toJson())

    println("Running create string attribute")
    val str = database.createStringAttribute(
        collectionId = collectionId,
        key = "name",
        size = 255,
        required = true,
        default = "",
        array = false
    )
    println(str.toJson())

    println("Running create integer attribute")
    val int = database.createIntegerAttribute(
        collectionId = collectionId,
        key = "release_year",
        required = true,
        min = 0,
        max = 9999
    )
    println(int.toJson())

    println("Running create float attribute")
    val float = database.createFloatAttribute(
        collectionId = collectionId,
        key = "rating",
        required = true,
        min = 0.0,
        max = 99.99
    )
    println(float.toJson())

    println("Running create boolean attribute")
    val bool = database.createBooleanAttribute(
        collectionId = collectionId,
        key = "kids",
        required = true
    )
    println(bool.toJson())

    println("Running create email attribute")
    val email = database.createEmailAttribute(
        collectionId = collectionId,
        key = "email",
        required = false,
        default = ""
    )
    println(email.toJson())

    delay(3000)

    println("Running create index")
    val index = database.createIndex(
        collectionId = collectionId,
        key = "name_email_idx",
        type = "fulltext",
        attributes = listOf("name", "email")
    )
    println(index.toJson())
}

suspend fun listCollections() {
    println("Running list collection API")
    val collections = database.listCollections()
    println(collections.toJson())
}

suspend fun deleteCollection() {
    println("Running delete collection API")
    database.deleteCollection(collectionId)
    println("Collection Deleted")
}

suspend fun createDocument() {
    println("Running Add Document API")

    val document = database.createDocument(
        collectionId = collectionId,
        documentId = "unique()",
        data = mapOf(
            "name" to "Spider Man",
            "release_year" to 1920,
            "rating" to 98.5,
            "kids" to false
        ),
        read = listOf("role:all"),
        write = listOf("role:all")
    )
    documentId = document.id
    println(document.toJson())
}

suspend fun listDocuments() {
    println("Running List Document API")
    val documents = database.listDocuments(collectionId)
    println(documents.toJson())
}

suspend fun deleteDocument() {
    println("Running Delete Document API")
    database.deleteDocument(collectionId, documentId)
    println("Document Deleted")
}

suspend fun createFunction() {
    println("Running Create Function API")
    val function = functions.create(
        functionId = "unique()",
        name = "Test Function",
        execute = listOf("role:all"),
        runtime = "dart-2.16",
        vars = mapOf("ENV" to "value")
    )
    functionId = function.id
    println(function.toJson())
}

suspend fun listFunctions() {
    println("Running List Functions API")
    val functions = functions.list()
    println(functions.toJson())
}

suspend fun deleteFunction() {
    println("Running Delete Function API")
    functions.delete(functionId)
    println("Function Deleted")
}

suspend fun uploadFile() {
    println("Running Upload File API")

    val file = File("./tyson.mp4")
    val storageFile = storage.createFile(
        bucketId = "test",
        fileId = fileId,
        file = file,
        read = listOf("role:all"),
        onProgress = {
            println("Uploading file: ${it.progress}%")
            println("Size uploaded: ${it.sizeUploaded}")
            println("Chunks total: ${it.chunksTotal}")
            println("Chunks uploaded: ${it.chunksUploaded}")      
        }
    )
    fileId = storageFile.id
    println(storageFile.toJson())
}

suspend fun createBucket() {
    println("Running Create Bucket API")
    val bucket = storage.createBucket(
        bucketId = "unique()",
        name = "Name",
        permission = "bucket"
    )
    bucketId = bucket.id
    println(bucket.toJson())
}

suspend fun listBuckets() {
    println("Running List Buckets API")
    val buckets = storage.listBuckets()
    println(buckets.toJson())
}

suspend fun listFiles() {
    println("Running List File API")
    val files = storage.listFiles(bucketId)
    println(files.toJson())
}

suspend fun deleteFile() {
    println("Running Delete File API")
    storage.deleteFile(bucketId, fileId)
    println("File Deleted")
}
