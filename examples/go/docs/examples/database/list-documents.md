package main

import (
    "fmt"
    "github.com/appwrite/go-sdk"
)

func main() {
    var client := appwrite.Client{}

    client.SetProject("5df5acd0d48c2") // Your project ID

    var service := appwrite.Database{
        client: &client
    }

    var response, error := service.ListDocuments("[COLLECTION_ID]", [], 0, 0, "[ORDER_FIELD]", "DESC", "int", "[SEARCH]", 0, 0)

    if error != nil {
        panic(error)
    }

    fmt.Println(response)
}