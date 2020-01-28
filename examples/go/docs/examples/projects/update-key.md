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

    // Create a new Projects service passing Client
    var srv := appwrite.Projects{
        client: &client
    }

    // Call UpdateKey method and handle results
    var res, err := srv.UpdateKey("[PROJECT_ID]", "[KEY_ID]", "[NAME]", [])
    if err != nil {
        panic(err)
    }

    fmt.Println(res)
}