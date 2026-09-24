#!/bin/sh
set -e
mkdir -p /go/src/github.com/repoowner/reponame/v2/
cp -Rf /app/tests/e2e/sdks/go/* /go/src/github.com/repoowner/reponame/v2/

cp /app/tests/e2e/languages/go/models/nullable_test.go /go/src/github.com/repoowner/reponame/v2/models/
go test github.com/repoowner/reponame/v2/models
go run tests.go
