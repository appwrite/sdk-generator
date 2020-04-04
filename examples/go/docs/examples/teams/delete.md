package main

import (
    "fmt"
    "github.com/appwrite/go-sdk"
)

func main() {
    var client := appwrite.Client{}

    client.SetProject("5df5acd0d48c2") // Your project ID

    var service := appwrite.Teams{
        client: &client
    }

    var response, error := service.Delete("[TEAM_ID]")

    if error != nil {
        panic(error)
    }

    fmt.Println(response)
}