package appwrite

import (
    "fmt"
    "os"
    "github.com/appwrite/go-sdk"
)

func main() {
    // Create a Client
    var client := appwrite.Client{}

    // Set Client required headers
    client.SetProject("")

    // Create a new Projects service passing Client
    var srv := appwrite.Projects{
        client: &client
    }

    // Call UpdateWebhook method and handle results
    var res, err := srv.UpdateWebhook("[PROJECT_ID]", "[WEBHOOK_ID]", "[NAME]", [], "[URL]", 0)
    if err != nil {
        panic(err)
    }

    fmt.Println(res)
}