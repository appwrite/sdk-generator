package main

import (
    "fmt"
    "github.com/appwrite/go-sdk"
)

func main() {
    var client := appwrite.Client{}

    client.SetProject("")

    var service := appwrite.Locale{
        client: &client
    }

    var response, error := service.GetCountriesPhones()

    if error != nil {
        panic(error)
    }

    fmt.Println(response)
}