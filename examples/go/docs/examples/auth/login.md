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

    var response, error := service.Login("email@example.com", "password")

    if error != nil {
        panic(error)
    }

    fmt.Println(response)
}