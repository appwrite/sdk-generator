#!/bin/sh
mkdir -p /go/src/github.com/repoowner/sdk-for-go/
cp -Rf /app/tests/sdks/go/* /go/src/github.com/repoowner/sdk-for-go/
go mod tidy
go run tests.go
