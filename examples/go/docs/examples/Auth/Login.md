# Auth Examples

## Login

```go
    package appwrite-login

    import (
        "fmt"
        "os"
        "github.com/appwrite/go-sdk"
    )

    func main() {
        // Create a Client
        var clt := appwrite.Client{}

        // Set Client required headers
        clt.SetProject("")

        // Create a new Auth service passing Client
        var srv := appwrite.Auth{
            client: &clt
        }

        // Call Login method and handle results
        var res, err := srv.Login("email@example.com", "password", "https://example.com", "https://example.com")
        if err != nil {
            panic(err)
        }

        fmt.Println(res)
    }
```