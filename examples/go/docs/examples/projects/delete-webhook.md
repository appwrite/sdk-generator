package main

import (
    "fmt"
    "os"
    "github.com/appwrite/go-sdk"
)

func main() {
    var client := appwrite.Client{}

    client.SetProject("")

    var service := appwrite.Projects{
        client: &client
    }

    var response, error := service.DeleteWebhook("[PROJECT_ID]", "[WEBHOOK_ID]")

    if error != nil {
        panic(error)
    }

    fmt.Println(response)
}