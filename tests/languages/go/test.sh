#!/bin/sh
mkdir -p /go/src/github.com/appwrite/sdk-for-go/
cp -Rf /app/tests/sdks/go/* /go/src/github.com/appwrite/sdk-for-go/

go run tests.go
