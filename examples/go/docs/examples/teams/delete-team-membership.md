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

    var response, error := service.DeleteTeamMembership("[TEAM_ID]", "[INVITE_ID]")

    if error != nil {
        panic(error)
    }

    fmt.Println(response)
}