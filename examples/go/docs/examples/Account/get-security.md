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

    // Create a new Account service passing Client
    var srv := appwrite.Account{
        client: &client
    }

    // Call GetSecurity method and handle results
    var res, err := srv.GetSecurity()
    if err != nil {
        panic(err)
    }

    fmt.Println(res)
}