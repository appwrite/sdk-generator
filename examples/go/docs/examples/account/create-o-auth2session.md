package main

import (
    "fmt"
    "github.com/appwrite/go-sdk"
)

func main() {
    var client := appwrite.Client{}

    client.SetEndpoint("https://[HOSTNAME_OR_IP]/v1") // Your API Endpoint
    client.SetProject("5df5acd0d48c2") // Your project ID

    var service := appwrite.Account{
        client: &client
    }

    var response, error := service.CreateOAuth2Session("amazon", "https://example.com", "https://example.com", [])

    if error != nil {
        panic(error)
    }

    fmt.Println(response)
}