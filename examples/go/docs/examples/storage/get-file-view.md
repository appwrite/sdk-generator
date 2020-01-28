package main

import (
    "fmt"
    "github.com/appwrite/go-sdk"
)

func main() {
    var client := appwrite.Client{}

    client.SetProject("")

    var service := appwrite.Storage{
        client: &client
    }

    var response, error := service.GetFileView("[FILE_ID]", "pdf")

    if error != nil {
        panic(error)
    }

    fmt.Println(response)
}