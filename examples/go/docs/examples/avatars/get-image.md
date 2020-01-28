package main

import (
    "fmt"
    "github.com/appwrite/go-sdk"
)

func main() {
    var client := appwrite.Client{}

    client.SetProject("")

    var service := appwrite.Avatars{
        client: &client
    }

    var response, error := service.GetImage("https://example.com")

    if error != nil {
        panic(error)
    }

    fmt.Println(response)
}