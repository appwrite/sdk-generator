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

    // Create a new Database service passing Client
    var srv := appwrite.Database{
        client: &client
    }

    // Call UpdateCollection method and handle results
    var res, err := srv.UpdateCollection("[COLLECTION_ID]", "[NAME]", [], [])
    if err != nil {
        panic(err)
    }

    fmt.Println(res)
}