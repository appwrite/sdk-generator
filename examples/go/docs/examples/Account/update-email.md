package main

import (
    "fmt"
    "os"
    "github.com/appwrite/go-sdk"
)

func main() {
    var client := appwrite.Client{}

    client.SetProject("")

    var service := appwrite.Account{
        client: &client
    }

    var response, error := service.UpdateEmail("email@example.com", "password")

    if error != nil {
        panic(error)
    }

    fmt.Println(response)
}