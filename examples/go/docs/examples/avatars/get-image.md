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

    // Create a new Avatars service passing Client
    var srv := appwrite.Avatars{
        client: &client
    }

    // Call GetImage method and handle results
    var res, err := srv.GetImage("https://example.com")
    if err != nil {
        panic(err)
    }

    fmt.Println(res)
}