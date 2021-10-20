<<<<<<< HEAD
module github.com/appwrite/sdk-generator/tests/languages/go

go 1.12

replace github.com/appwrite/sdk-for-go/appwrite => ../../../tests/sdks/go/appwrite

replace github.com/appwrite/sdk-generator/tests/languages/go/utils => ./utils

require (
	github.com/appwrite/sdk-for-go/appwrite v0.0.0-00010101000000-000000000000 // indirect
	github.com/appwrite/sdk-generator/tests/languages/go/utils v0.0.0-00010101000000-000000000000 // indirect
)
=======
module github.com/appwrite/sdk-generator/tree/master/tests/go

go 1.12

require github.com/appwrite/sdk-for-go v0.0.0

replace github.com/appwrite/sdk-for-go => ../../../examples/go
>>>>>>> e4c5048c (Adding Test and bug fixes in Client Template)
