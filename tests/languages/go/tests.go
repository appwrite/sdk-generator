package main

import (
	"fmt"
	"path"
	"time"
	"errors"
	"regexp"
	"strings"
	"crypto/md5"
	"github.com/repoowner/sdk-for-go/appwrite"
	"github.com/repoowner/sdk-for-go/client"
	"github.com/repoowner/sdk-for-go/payload"
	"github.com/repoowner/sdk-for-go/id"
	"github.com/repoowner/sdk-for-go/permission"
	"github.com/repoowner/sdk-for-go/query"
	"github.com/repoowner/sdk-for-go/role"
	"github.com/repoowner/sdk-for-go/general"
)

func main() {
	stringInArray := []string{"string in array"}

	client := appwrite.NewClient(
		appwrite.WithTimeout(60 * time.Second),
	)
	client.AddHeader("Origin", "http://localhost")
	fmt.Print("\n\nTest Started\n")
	testFooService(client, stringInArray)
	testBarService(client, stringInArray)
	testGeneralService(client, stringInArray)
}

func testFooService(client client.Client, stringInArray []string) {
	foo := appwrite.NewFoo(client)
	// Foo Service
	response, err := foo.Get("string", 123, stringInArray)
	if err != nil {
		fmt.Printf("foo.Get => error %v", err)
	}
	fmt.Printf("%s\n", response.Result)

	response, err = foo.Post("string", 123, stringInArray)
	if err != nil {
		fmt.Printf("foo.Post => error %v", err)
	}
	fmt.Printf("%s\n", response.Result)

	response, err = foo.Put("string", 123, stringInArray)
	if err != nil {
		fmt.Printf("foo.Put => error %v", err)
	}
	fmt.Printf("%s\n", response.Result)

	response, err = foo.Patch("string", 123, stringInArray)
	if err != nil {
		fmt.Printf("foo.Patch => error %v", err)
	}
	fmt.Printf("%s\n", response.Result)

	response, err = foo.Delete("string", 123, stringInArray)
	if err != nil {
		fmt.Printf("foo.Delete => error %v", err)
	}
	fmt.Printf("%s\n", response.Result)
}

func testBarService(client client.Client, stringInArray []string) {
	bar := appwrite.NewBar(client)
	// Bar Service
	response, err := bar.Get("string", 123, stringInArray)
	if err != nil {
		fmt.Printf("bar.Get => error %v", err)
	}
	fmt.Printf("%s\n", response.Result)

	response, err = bar.Post("string", 123, stringInArray)
	if err != nil {
		fmt.Printf("bar.Post => error %v", err)
	}
	fmt.Printf("%s\n", response.Result)

	response, err = bar.Put("string", 123, stringInArray)
	if err != nil {
		fmt.Printf("bar.Put => error %v", err)
	}
	fmt.Printf("%s\n", response.Result)

	response, err = bar.Patch("string", 123, stringInArray)
	if err != nil {
		fmt.Printf("bar.Patch => error %v", err)
	}
	fmt.Printf("%s\n", response.Result)

	response, err = bar.Delete("string", 123, stringInArray)
	if err != nil {
		fmt.Printf("bar.Delete => error %v", err)
	}
	fmt.Printf("%s\n", response.Result)
}

func testGeneralService(client client.Client, stringInArray []string) {
	general := appwrite.NewGeneral(client)
	// General Service
	response, err := general.Redirect()
	if err != nil {
		fmt.Printf("general.Redirect => error %v", err)
	}
	fmt.Printf("%s\n", (*response).(map[string]interface{})["result"].(string))

	testGeneralUpload(client, stringInArray)
	testGeneralUpload(client, stringInArray)
	testLargeUpload(client, stringInArray)
	testLargeUpload(client, stringInArray)

	// Extended General Responses
	testGeneralDownload(client)

	// Exception Responses
	_, err = general.Error400()
	if err != nil {
		fmt.Printf("%s\n", err.Error())
	}

	_, err = general.Error500()
	if err != nil {
		fmt.Printf("%s\n", err.Error())
	}

	_, err = general.Error502()
	if err != nil {
		fmt.Printf("%s\n", err.Error())
	}

	general.Empty()

    // Test Multipart
    testMultipart(client)

	// Test Queries
	testQueries()

	// Test Permission Helpers
	testPermissionHelpers()

	// Test Id Helpers
	testIdHelpers()

	// Final test
	headersResponse, err := general.Headers()
	if err != nil {
		fmt.Printf("general.Headers => error %v", err)
	}
	fmt.Printf("%s\n", headersResponse.Result)
}

func testGeneralUpload(client client.Client, stringInArray []string) {
	general := appwrite.NewGeneral(client)
	uploadFile := path.Join("/app", "tests/resources/file.png")
	inputFile := payload.NewPayloadFromFile(uploadFile, "file.png")

	response, err := general.Upload("string", 123, stringInArray, inputFile)
	if err != nil {
		fmt.Printf("general.Upload => error %v", err)
	}
	fmt.Printf("%s\n", response.Result)
}

func testGeneralDownload(client client.Client) {
	general := appwrite.NewGeneral(client)
	response, err := general.Download()
	if err != nil {
		fmt.Printf("general.Download => error %v", err)
	}
	fmt.Printf("%s\n", string(*response))
}

func testLargeUpload(client client.Client, stringInArray []string) {
	general := appwrite.NewGeneral(client)
	uploadFile := path.Join("/app", "tests/resources/large_file.mp4")
	inputFile := payload.NewPayloadFromFile(uploadFile, "large_file.mp4")

	response, err := general.Upload("string", 123, stringInArray, inputFile)
	if err != nil {
		fmt.Printf("general.Upload => error %v\n", err)
	}
	fmt.Printf("%s\n", response.Result)
}

func testMultipart(client client.Client) {
	g := general.New(client)
	mp, err := g.MultipartCompiled()
	if err != nil {
		return
	}

	bytesValue, ok := (*mp).([]byte)
	if !ok {
		return
	}

	data, err := parse(bytesValue)
	if err != nil {
		return
	}

	fmt.Println(data["x"])

	responseBodyInterface, exists := data["responseBody"]
	if !exists {
		return
	}

	var responseBodyBytes []byte

	switch v := responseBodyInterface.(type) {
	case string:
		responseBodyBytes = []byte(v)
	case []byte:
		responseBodyBytes = v
	default:
		return
	}

	fmt.Printf("%x\n", md5.Sum(responseBodyBytes))

	// String payload
	stringPayload := payload.NewPayloadFromString("Hello, World!")
	mp, er := g.MultipartEcho(stringPayload)
	if er != nil {
		return
	}

	bytesValue, ok = (*mp).([]byte)
	if !ok {
		return
	}

	data, err = parse(bytesValue)
	if err != nil {
		return
	}

	responseBodyInterface, exists = data["responseBody"]
	if !exists {
		return
	}

	switch v := responseBodyInterface.(type) {
	case string:
		fmt.Println(v)
	case []byte:
		fmt.Println(string(v))
	default:
		return
	}

	// JSON payload
	jsonPayload := payload.NewPayloadFromJson(map[string]interface{}{"key": "myStringValue"}, "")
	mp, er = g.MultipartEcho(jsonPayload)
	if er != nil {
		return
	}

	bytesValue, ok = (*mp).([]byte)
	if !ok {
		return
	}

	data, err = parse(bytesValue)
	if err != nil {
		return
	}

	responseBodyInterface, exists = data["responseBody"]
	if !exists {
		return
	}

	var responsePayload *payload.Payload

	switch v := responseBodyInterface.(type) {
	case string:
		responsePayload = payload.NewPayloadFromString(v)
	case []byte:
		responsePayload = payload.NewPayloadFromBinary(v, "")
	default:
		return
	}

	fmt.Println(responsePayload.ToJson()["key"])

	// File payload
	filePayload := payload.NewPayloadFromFile("tests/resources/file.png", "file.png")
	mp, er = g.MultipartEcho(filePayload)
	if er != nil {
		return
	}

	bytesValue, ok = (*mp).([]byte)
	if !ok {
		return
	}

	data, err = parse(bytesValue)
	if err != nil {
		return
	}

	responseBodyInterface, exists = data["responseBody"]
	if !exists {
		return
	}

	switch v := responseBodyInterface.(type) {
	case string:
		responsePayload = payload.NewPayloadFromString(v)
	case []byte:
		responsePayload = payload.NewPayloadFromBinary(v, "")
	default:
		return
	}

	err = responsePayload.ToFile("tests/resources/file_copy.png")
	if err != nil {
		return
	}

	file, err := os.ReadFile("tests/resources/file_copy.png")
	if err != nil {
		return
	}

	fmt.Printf("%x\n", md5.Sum(file))
}

func testQueries() {
	fmt.Println(query.Equal("released", true))
	fmt.Println(query.Equal("title", []interface{}{"Spiderman", "Dr. Strange"}))
	fmt.Println(query.NotEqual("title", "Spiderman"))
	fmt.Println(query.LessThan("releasedYear", 1990))
	fmt.Println(query.GreaterThan("releasedYear", 1990))
	fmt.Println(query.Search("name", "john"))
	fmt.Println(query.IsNull("name"))
	fmt.Println(query.IsNotNull("name"))
	fmt.Println(query.Between("age", 50, 100))
	fmt.Println(query.Between("age", 50.5, 100.5))
	fmt.Println(query.Between("name", "Anna", "Brad"))
	fmt.Println(query.StartsWith("name", "Ann"))
	fmt.Println(query.EndsWith("name", "nne"))
	fmt.Println(query.Select([]interface{}{"name", "age"}))
	fmt.Println(query.OrderAsc("title"))
	fmt.Println(query.OrderDesc("title"))
	fmt.Println(query.CursorAfter("my_movie_id"))
	fmt.Println(query.CursorBefore("my_movie_id"))
	fmt.Println(query.Limit(50))
	fmt.Println(query.Offset(20))
	fmt.Println(query.Contains("title", "Spider"))
	fmt.Println(query.Contains("labels", "first"))
	fmt.Println(query.Or([]string{
		query.Equal("released", true),
		query.LessThan("releasedYear", 1990),
	}))
	fmt.Println(query.And([]string{
		query.Equal("released", false),
		query.GreaterThan("releasedYear", 2015),
	}))
}

func testPermissionHelpers() {
	fmt.Println(permission.Read(role.Any()))
	fmt.Println(permission.Write(role.User(id.Custom("userid"), "")))
	fmt.Println(permission.Create(role.Users("")))
	fmt.Println(permission.Update(role.Guests()))
	fmt.Println(permission.Delete(role.Team("teamId", "owner")))
	fmt.Println(permission.Delete(role.Team("teamId", "")))
	fmt.Println(permission.Create(role.Member("memberId")))
	fmt.Println(permission.Update(role.Users("verified")))
	fmt.Println(permission.Update(role.User(id.Custom("userid"), "unverified")))
	fmt.Println(permission.Create(role.Label("admin")))
}

func testIdHelpers() {
	fmt.Println(id.Unique())
	fmt.Println(id.Custom("custom_id"))
}


func parse(bytesData []byte) (map[string]string, error) {

	responseData := string(bytesData)

	matches := regexp.MustCompile("(-+\\w+)--").FindStringSubmatch(responseData)

	if len(matches) != 2 {
		return nil, errors.New("unexpected response type")
	}

	parts := strings.Split(responseData, matches[1])

	if len(parts) == 0 {
		return nil, errors.New("unexpected response type")
	}
	execution := make(map[string]string, 10)

	for _, part := range parts {
		cleanPart := strings.TrimSpace(part)
		partName := regexp.MustCompile("name=\"?(\\w+)").FindStringSubmatch(cleanPart)

		if len(partName) != 2 {
			continue
		}

		name := strings.TrimSpace(partName[1])
		lines := strings.Split(cleanPart, "\r\n")

	Inner:
		for i, line := range lines[1:] {
			if line == "" {
				continue
			}

			if line == "Content-Type: application/json" {
				for _, line := range lines[i:] {
					if line == "" {
						continue
					}

					execution[name] = line
				}
				continue Inner
			}

			execution[name] += line + "\r\n"
		}
        execution[name] = strings.TrimSuffix(execution[name],"\r\n")
	}

	return execution, nil
}

