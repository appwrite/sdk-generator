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

    var response, error := service.Update("[TEAM_ID]", "[NAME]")

    if error != nil {
        panic(error)
    }

    fmt.Println(response)
}