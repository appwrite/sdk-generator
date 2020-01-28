package main

import (
    "fmt"
    "github.com/appwrite/go-sdk"
)

func main() {
    var client := appwrite.Client{}

    client.SetProject("")

    var service := appwrite.Database{
        client: &client
    }

    var response, error := service.GetDocument("[COLLECTION_ID]", "[DOCUMENT_ID]")

    if error != nil {
        panic(error)
    }

    fmt.Println(response)
}