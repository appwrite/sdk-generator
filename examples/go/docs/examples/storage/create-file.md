package main

import (
    "fmt"
    "github.com/appwrite/go-sdk"
)

func main() {
    var client := appwrite.Client{}

    client.SetProject("5df5acd0d48c2")

    var service := appwrite.Storage{
        client: &client
    }

    var response, error := service.CreateFile(file, [], [])

    if error != nil {
        panic(error)
    }

    fmt.Println(response)
}