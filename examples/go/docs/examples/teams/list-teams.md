package main

import (
    "fmt"
    "github.com/appwrite/go-sdk"
)

func main() {
    var client := appwrite.Client{}

    client.SetProject("")

    var service := appwrite.Teams{
        client: &client
    }

    var response, error := service.ListTeams("[SEARCH]", 0, 0, "ASC")

    if error != nil {
        panic(error)
    }

    fmt.Println(response)
}