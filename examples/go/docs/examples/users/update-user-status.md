package main

import (
    "fmt"
    "os"
    "github.com/appwrite/go-sdk"
)

func main() {
    var client := appwrite.Client{}

    client.SetProject("")

    var service := appwrite.Users{
        client: &client
    }

    var response, error := service.UpdateUserStatus("[USER_ID]", "1")

    if error != nil {
        panic(error)
    }

    fmt.Println(response)
}