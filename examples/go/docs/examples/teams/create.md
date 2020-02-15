package main

import (
    "fmt"
    "github.com/appwrite/go-sdk"
)

func main() {
    var client := appwrite.Client{}

    client.SetProject("5df5acd0d48c2")

    var service := appwrite.Teams{
        client: &client
    }

    var response, error := service.Create("[NAME]", [])

    if error != nil {
        panic(error)
    }

    fmt.Println(response)
}