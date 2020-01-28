package main

import (
    "fmt"
    "os"
    "github.com/appwrite/go-sdk"
)

func main() {
    var client := appwrite.Client{}

    client.SetProject("")

    var service := appwrite.Avatars{
        client: &client
    }

    var response, error := service.GetBrowser("aa")

    if error != nil {
        panic(error)
    }

    fmt.Println(response)
}