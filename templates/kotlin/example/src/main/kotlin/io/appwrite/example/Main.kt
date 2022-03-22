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

val storage = Storage(client)

var fileId: String = "unique()"

suspend fun main() {
    println("Running Upload File API")

    val file = File("./tyson.mp4")
    try {
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
    } catch (ex: AppwriteException) {
        ex.printStackTrace()
        exitProcess(1)
    }
}