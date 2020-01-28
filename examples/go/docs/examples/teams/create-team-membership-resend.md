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

    var response, error := service.CreateTeamMembershipResend("[TEAM_ID]", "[INVITE_ID]", "https://example.com")

    if error != nil {
        panic(error)
    }

    fmt.Println(response)
}