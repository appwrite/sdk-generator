package main

import (
	"log"
	"os"
	"time"

	"github.com/appwrite/sdk-generator/tests/languages/go/utils"

	"github.com/appwrite/sdk-for-go/appwrite"
)

func main() {

	// endpoint := os.Getenv("APPWRITE_ENDPOINT")
	// project := os.Getenv("APPWRITE_PROJECT")
	// key := os.Getenv("APPWRITE_API_KEY")
	// client := appwrite.NewClient(10 * time.Second)
	// client.SetEndpoint(endpoint)
	// client.SetProject(project)
	// client.SetKey(key)

	client := appwrite.NewClient(10 * time.Second)
	client.SetLocale("en-US")

	publicClient := appwrite.NewClient(10 * time.Second)
	publicClient.SetLocale("en-US")

	testCreateDocument(client)
	testAccount(publicClient)
}

var EmptyArray = []interface{}{}
var EmptyString = ""

func testCreateDocument(client appwrite.Client) {
	db := appwrite.NewDatabase(client)
	data := map[string]string{
		"hello": "world",
	}
	doc, err := db.CreateDocument(
		os.Getenv("COLLECTION_ID"),
		data,
		EmptyArray,
		EmptyArray,
		EmptyString,
		EmptyString,
		EmptyString,
	)
	if err != nil {
		log.Printf("Error creating document: %v", err)
	}
	log.Printf("Created document: %v", doc)
}

func testAccount(client appwrite.Client) {
	userId := utils.StringWithCharset(12)
	username := "user" + userId
	password := "pass" + userId
	email := username + "@example.com"
	testCreateAccount(client, email, password, username)
	testAccountSession(client, email, password)
	testAccountPrefs(client)
	testDeleteAccount(client)
}

func testCreateAccount(client appwrite.Client, email, password, username string) {
	account := appwrite.NewAccount(client)
	result, err := account.Create(email, password, username)
	if err != nil {
		log.Printf("Error creating account: %v", err)
	}
	log.Printf("Created account: %v", result)
}

func testDeleteAccount(client appwrite.Client) {
	account := appwrite.NewAccount(client)
	result, err := account.Delete()
	if err != nil {
		log.Printf("Error deleting account: %v", err)
	}
	log.Printf("Deleting account: %v", result)
}
func testAccountSession(client appwrite.Client, email string, password string) {
	var result map[string]interface{}
	var err error
	account := appwrite.NewAccount(client)
	result, err = account.CreateSession(email, password)
	if err != nil {
		log.Printf("Error creating account session: %v", err)
	}
	log.Printf("Created account session: %v", result)
	result, err = account.GetSessions()
	if err != nil {
		log.Printf("Error getting account sessions: %v", err)
	}
	log.Printf("Getting account sessions: %v", result)
}

func testAccountPrefs(client appwrite.Client) {
	var result map[string]interface{}
	var err error
	newPrefs := map[string]string{
		"foo": "bar",
	}
	account := appwrite.NewAccount(client)
	result, err = account.GetPrefs()
	if err != nil {
		log.Printf("Error getting account prefs: %v", err)
	}
	log.Printf("Getting account prefs: %v", result)

	result, err = account.UpdatePrefs(newPrefs)
	if err != nil {
		log.Printf("Error updating account prefs: %v", err)
	}
	log.Printf("Updating account prefs: %v", result)

	result, err = account.GetPrefs()
	if err != nil {
		log.Printf("Error getting account prefs: %v", err)
	}
	log.Printf("Getting account prefs: %v", result)
}
