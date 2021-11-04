import io.appwrite.Client
import io.appwrite.services.Storage

suspend fun main() {
    val client = Client(context)
      .setEndpoint("https://[HOSTNAME_OR_IP]/v1") // Your API Endpoint
      .setProject("5df5acd0d48c2") // Your project ID
      .setKey("919c2d18fb5d4...a2ae413da83346ad2") // Your secret API key

    val storage = Storage(client)
    val response = storage.updateFile(
        fileId = "[FILE_ID]",
        read = listOf(),
        write = listOf()
    )
    val json = response.body?.string()
}