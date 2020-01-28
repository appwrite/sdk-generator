package main

import (
    "fmt"
    "os"
    "github.com/appwrite/go-sdk"
)

func main() {
    var client := appwrite.Client{}


    var service := appwrite.Auth{
        client: &client
    }

    var response, error := service.Confirm("[USER_ID]", "[TOKEN]")

    if error != nil {
        panic(error)
    }

    fmt.Println(response)
}