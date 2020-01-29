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

    var response, error := service.UpdateProject("[PROJECT_ID]", "[NAME]", "[DESCRIPTION]", "[LOGO]", "https://example.com", "[LEGAL_NAME]", "[LEGAL_COUNTRY]", "[LEGAL_STATE]", "[LEGAL_CITY]", "[LEGAL_ADDRESS]", "[LEGAL_TAX_ID]")

    if error != nil {
        panic(error)
    }

    fmt.Println(response)
}