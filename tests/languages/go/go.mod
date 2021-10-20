module github.com/appwrite/sdk-generator/tests/languages/go

go 1.12

replace github.com/appwrite/sdk-for-go/appwrite => ../../../examples/go/appwrite

replace github.com/appwrite/sdk-generator/tests/languages/go/utils => ./utils

require (
	github.com/appwrite/sdk-for-go/appwrite v0.0.0-00010101000000-000000000000 // indirect
	github.com/appwrite/sdk-generator/tests/languages/go/utils v0.0.0-00010101000000-000000000000 // indirect
)
