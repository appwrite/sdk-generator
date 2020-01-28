package main

import (
    "fmt"
    "github.com/appwrite/go-sdk"
)

func main() {
    var client := appwrite.Client{}

    client.SetProject("")

    var service := appwrite.Projects{
        client: &client
    }

    var response, error := service.UpdateWebhook("[PROJECT_ID]", "[WEBHOOK_ID]", "[NAME]", [], "[URL]", 0)

    if error != nil {
        panic(error)
    }

    fmt.Println(response)
}