package main

import (
    "fmt"
    "github.com/appwrite/go-sdk"
)

func main() {
    var client := appwrite.Client{}

    var service := appwrite.Account{
        client: &client
    }

    var response, error := service.UpdateRecovery("[USER_ID]", "[SECRET]", "password", "password")

    if error != nil {
        panic(error)
    }

    fmt.Println(response)
}