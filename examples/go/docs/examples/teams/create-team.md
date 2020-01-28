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

    // Call CreateTeam method and handle results
    var res, err := srv.CreateTeam("[NAME]")
    if err != nil {
        panic(err)
    }

    fmt.Println(res)
}