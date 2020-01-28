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

    // Call Login method and handle results
    var res, err := srv.Login("email@example.com", "password")
    if err != nil {
        panic(err)
    }

    fmt.Println(res)
}