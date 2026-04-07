#!/bin/sh
mkdir -p /go/src/github.com/repoowner/reponame/v2/
cp -Rf /app/tests/sdks/go/* /go/src/github.com/repoowner/reponame/v2/

go run tests.go
