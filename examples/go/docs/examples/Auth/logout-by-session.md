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

    // Create a new Auth service passing Client
    var srv := appwrite.Auth{
        client: &client
    }

    // Call LogoutBySession method and handle results
    var res, err := srv.LogoutBySession("[ID]")
    if err != nil {
        panic(err)
    }

    fmt.Println(res)
}