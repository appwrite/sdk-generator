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

    var response, error := service.GetFilePreview("[FILE_ID]", 0, 0, 0, "", "jpg")

    if error != nil {
        panic(error)
    }

    fmt.Println(response)
}