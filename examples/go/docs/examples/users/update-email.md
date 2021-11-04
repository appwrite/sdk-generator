package main

import (
    "fmt"
    "github.com/appwrite/go-sdk"
)

func main() {
    var client := appwrite.Client{}

    client.SetEndpoint("https://[HOSTNAME_OR_IP]/v1") // Your API Endpoint
    client.SetProject("5df5acd0d48c2") // Your project ID
    client.SetKey("919c2d18fb5d4...a2ae413da83346ad2") // Your secret API key

    var service := appwrite.Users{
        client: &client
    }

    var response, error := service.UpdateEmail("[USER_ID]", "email@example.com")

    if error != nil {
        panic(error)
    }

    fmt.Println(response)
}