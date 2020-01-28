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

    var response, error := service.ConfirmResend("https://example.com")

    if error != nil {
        panic(error)
    }

    fmt.Println(response)
}