#!/bin/sh
set -e

mkdir -p /go/src/github.com/repoowner/reponame/
cp -Rf /app/tests/e2e/sdks/go/* /go/src/github.com/repoowner/reponame/

cp /app/tests/e2e/languages/go/models/nullable_test.go /go/src/github.com/repoowner/reponame/models/
go test github.com/repoowner/reponame/client github.com/repoowner/reponame/models
go run tests.go
