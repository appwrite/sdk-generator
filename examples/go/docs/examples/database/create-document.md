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

    var response, error := service.CreateDocument("[COLLECTION_ID]", "{}", [], [], "[PARENT_DOCUMENT]", "", "assign")

    if error != nil {
        panic(error)
    }

    fmt.Println(response)
}