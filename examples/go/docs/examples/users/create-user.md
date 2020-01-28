package main

import (
    "fmt"
    "github.com/appwrite/go-sdk"
)

func main() {
    var client := appwrite.Client{}

    client.SetProject("")

    var service := appwrite.Users{
        client: &client
    }

    var response, error := service.CreateUser("email@example.com", "password", "[NAME]")

    if error != nil {
        panic(error)
    }

    fmt.Println(response)
}