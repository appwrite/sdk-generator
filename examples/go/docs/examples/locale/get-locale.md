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

    // Create a new Locale service passing Client
    var srv := appwrite.Locale{
        client: &client
    }

    // Call GetLocale method and handle results
    var res, err := srv.GetLocale()
    if err != nil {
        panic(err)
    }

    fmt.Println(res)
}