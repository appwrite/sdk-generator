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

    var response, error := service.UpdateTask("[PROJECT_ID]", "[TASK_ID]", "[NAME]", "play", "", 0, "GET", "https://example.com")

    if error != nil {
        panic(error)
    }

    fmt.Println(response)
}