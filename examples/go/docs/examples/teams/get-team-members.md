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

    var response, error := service.GetTeamMembers("[TEAM_ID]")

    if error != nil {
        panic(error)
    }

    fmt.Println(response)
}