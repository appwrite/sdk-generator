package main

import (
    "fmt"
    "github.com/appwrite/go-sdk"
)

func main() {
    var client := appwrite.Client{}

    var service := appwrite.Account{
        client: &client
    }

    var response, error := service.Create("email@example.com", "password", "[NAME]")

    if error != nil {
        panic(error)
    }

    fmt.Println(response)
}