package appwrite

import (
    "fmt"
    "os"
    "github.com/appwrite/go-sdk"
)

func main() {
    // Create a Client
    var client := appwrite.Client{}

    // Set Client required headers
    client.SetProject("")

    // Create a new Teams service passing Client
    var srv := appwrite.Teams{
        client: &client
    }

    // Call CreateTeamMembership method and handle results
    var res, err := srv.CreateTeamMembership("[TEAM_ID]", "email@example.com", [], "https://example.com")
    if err != nil {
        panic(err)
    }

    fmt.Println(res)
}