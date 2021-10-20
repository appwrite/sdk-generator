module github.com/appwrite/sdk-generator/tests/languages/go

go 1.12

require github.com/appwrite/sdk-for-go/appwrite v0.0.0
replace github.com/appwrite/sdk-for-go/appwrite => ../../../examples/go/appwrite

replace github.com/appwrite/sdk-for-go => ../../../examples/go
require github.com/appwrite/sdk-generator/tests/languages/go/utils v0.0.0
replace github.com/appwrite/sdk-generator/tests/languages/go/utils => ./utils
