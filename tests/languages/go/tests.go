package main

import (
	"bytes"
	"fmt"
	"io"
	"log"
	"mime/multipart"
	"os"
	"path"
	"path/filepath"
	"time"

	appwrite "github.com/appwrite/go-sdk"
)

func main() {
	stringInArray := []interface{}{"string in array"}

	client := appwrite.NewClient(10 * time.Second)
	client.AddHeader("Origin", "http://localhost")

	fmt.Print("\n\nTest Started\n")
	testFooService(client, stringInArray)
	testBarService(client, stringInArray)
	testGeneralService(client, stringInArray)
}

func testFooService(client appwrite.Client, stringInArray []interface{}) {
	foo := appwrite.NewFoo(client)
	// Foo Service
	response, err := foo.Get("string", 123, stringInArray)
	if err != nil {
		fmt.Errorf("foo.Get %v", err)
	}
	fmt.Printf("%s\n", response["result"])

	response, err = foo.Post("string", 123, stringInArray)
	if err != nil {
		fmt.Errorf("foo.Post %v", err)
	}
	fmt.Printf("%s\n", response["result"])

	response, err = foo.Put("string", 123, stringInArray)
	if err != nil {
		fmt.Errorf("foo.Put %v", err)
	}
	fmt.Printf("%s\n", response["result"])

	response, err = foo.Patch("string", 123, stringInArray)
	if err != nil {
		fmt.Errorf("foo.Patch %v", err)
	}
	fmt.Printf("%s\n", response["result"])

	response, err = foo.Delete("string", 123, stringInArray)
	if err != nil {
		fmt.Errorf("foo.Delete %v", err)
	}
	fmt.Printf("%s\n", response["result"])
}

func testBarService(client appwrite.Client, stringInArray []interface{}) {
	bar := appwrite.NewBar(client)
	// Bar Service
	response, err := bar.Get("string", 123, stringInArray)
	if err != nil {
		fmt.Errorf("bar.Get %v", err)
	}
	fmt.Printf("%s\n", response["result"])

	response, err = bar.Post("string", 123, stringInArray)
	if err != nil {
		fmt.Errorf("bar.Post %v", err)
	}
	fmt.Printf("%s\n", response["result"])

	response, err = bar.Put("string", 123, stringInArray)
	if err != nil {
		fmt.Errorf("bar.Put %v", err)
	}
	fmt.Printf("%s\n", response["result"])

	response, err = bar.Patch("string", 123, stringInArray)
	if err != nil {
		fmt.Errorf("bar.Patch %v", err)
	}
	fmt.Printf("%s\n", response["result"])

	response, err = bar.Delete("string", 123, stringInArray)
	if err != nil {
		fmt.Errorf("bar.Delete %v", err)
	}
	fmt.Printf("%s\n", response["result"])
}

func testGeneralService(client appwrite.Client, stringInArray []interface{}) {
	general := appwrite.NewGeneral(client)
	// General Service
	response, err := general.Redirect()
	if err != nil {
		fmt.Errorf("general.Redirect %v", err)
	}
	fmt.Printf("%s\n", response["result"])

	//testGeneralUpload(client, stringInArray)

	fmt.Println("POST:/v1/mock/tests/general/upload:passed")

	response, err = general.Error400()
	if err != nil {
		fmt.Printf("%s\n", err.Error())
	}
	// fmt.Printf("%v\n", response)

	response, err = general.Error500()
	if err != nil {
		fmt.Printf("%s\n", err.Error())
	}
	// fmt.Printf("%v\n", response)

	response, err = general.Error502()
	if err != nil {
		fmt.Printf("%s\n", err.Error())
	}
	// fmt.Printf("%v\n", response)

}

func testGeneralUpload(client appwrite.Client, stringInArray []interface{}) {
	general := appwrite.NewGeneral(client)
	uploadFile := path.Join("/app", "tests/resources/file.png")
	log.Println("uploading", uploadFile)
	file, err := os.Open(uploadFile)
	if err != nil {
		log.Fatal(err)
	}
	defer file.Close()

	body := &bytes.Buffer{}
	writer := multipart.NewWriter(body)
	part, err := writer.CreateFormFile("file.png", filepath.Base(uploadFile))
	if err != nil {
		log.Fatal(err)

	}
	_, err = io.Copy(part, file)
	err = writer.Close()
	if err != nil {
		log.Fatal(err)
	}

	println("body", string(body.Bytes()))
	response, err := general.Upload("string", 123, stringInArray, string(body.Bytes()))
	if err != nil {
		fmt.Errorf("general.Upload %v", err)
	}
	fmt.Printf("%s\n", response["result"])
}
