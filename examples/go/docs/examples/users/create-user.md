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

    // Create a new Users service passing Client
    var srv := appwrite.Users{
        client: &client
    }

    // Call CreateUser method and handle results
    var res, err := srv.CreateUser("email@example.com", "password")
    if err != nil {
        panic(err)
    }

    fmt.Println(res)
}