package main

import (
    "fmt"
    "os"
    "github.com/appwrite/go-sdk"
)

func main() {
    var client := appwrite.Client{}

    client.SetProject("")

    var service := appwrite.Auth{
        client: &client
    }

    var response, error := service.Register("email@example.com", "password", "https://example.com")

    if error != nil {
        panic(error)
    }

    fmt.Println(response)
}