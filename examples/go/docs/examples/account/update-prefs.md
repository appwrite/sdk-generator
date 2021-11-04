package main

import (
    "fmt"
    "github.com/appwrite/go-sdk"
)

func main() {
    var client := appwrite.Client{}

    client.SetEndpoint("https://[HOSTNAME_OR_IP]/v1") // Your API Endpoint
    client.SetProject("5df5acd0d48c2") // Your project ID
    client.SetJWT("eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJ...") // Your secret JSON Web Token

    var service := appwrite.Account{
        client: &client
    }

    var response, error := service.UpdatePrefs()

    if error != nil {
        panic(error)
    }

    fmt.Println(response)
}