#!/bin/sh
mkdir -p /go/src/github.com/repoowner/reponame/
cp -Rf /app/tests/e2e/sdks/go/* /go/src/github.com/repoowner/reponame/

go run tests.go
