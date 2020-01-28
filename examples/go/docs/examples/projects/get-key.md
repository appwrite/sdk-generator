package main

import (
    "fmt"
    "github.com/appwrite/go-sdk"
)

func main() {
    var client := appwrite.Client{}

    client.SetProject("")

    var service := appwrite.Projects{
        client: &client
    }

    var response, error := service.GetKey("[PROJECT_ID]", "[KEY_ID]")

    if error != nil {
        panic(error)
    }

    fmt.Println(response)
}