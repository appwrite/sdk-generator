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

    var response, error := service.UpdateTeamMembershipStatus("[TEAM_ID]", "[INVITE_ID]", "[USER_ID]", "[SECRET]")

    if error != nil {
        panic(error)
    }

    fmt.Println(response)
}