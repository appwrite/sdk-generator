package main

import (
    "fmt"
    "os"
    "github.com/appwrite/go-sdk"
)

func main() {
    var client := appwrite.Client{}

    client.SetProject("")

    var service := appwrite.Teams{
        client: &client
    }

    var response, error := service.CreateTeamMembership("[TEAM_ID]", "email@example.com", [], "https://example.com")

    if error != nil {
        panic(error)
    }

    fmt.Println(response)
}