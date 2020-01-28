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

    // Create a new Storage service passing Client
    var srv := appwrite.Storage{
        client: &client
    }

    // Call DeleteFile method and handle results
    var res, err := srv.DeleteFile("[FILE_ID]")
    if err != nil {
        panic(err)
    }

    fmt.Println(res)
}