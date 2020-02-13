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

    var response, error := service.UpdateTask("[PROJECT_ID]", "[TASK_ID]", "[NAME]", "play", "", 0, "GET", "https://example.com", [], "[HTTP_USER]", "[HTTP_PASS]")

    if error != nil {
        panic(error)
    }

    fmt.Println(response)
}