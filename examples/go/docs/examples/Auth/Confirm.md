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

    // Create a new Auth service passing Client
    var srv := appwrite.Auth{
        client: &client
    }

    // Call Confirm method and handle results
    var res, err := srv.Confirm("[USER_ID]", "[TOKEN]")
    if err != nil {
        panic(err)
    }

    fmt.Println(res)
}