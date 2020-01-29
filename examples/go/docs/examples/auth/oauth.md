package main

import (
    "fmt"
    "github.com/appwrite/go-sdk"
)

func main() {
    var client := appwrite.Client{}

    client.SetProject("")

    var service := appwrite.Auth{
        client: &client
    }

    var response, error := service.Oauth("bitbucket", "https://example.com", "https://example.com")

    if error != nil {
        panic(error)
    }

    fmt.Println(response)
}