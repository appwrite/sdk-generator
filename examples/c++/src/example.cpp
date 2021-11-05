#include <iostream>
#include <map>
#include <string>

#include "client.hpp"
#include "exception.hpp"
#include "services/users.hpp"

using string = std::string;

int main() {
    std::cout << "Hello World!" << std::endl;

    Appwrite::Client client;

    std::cout << "Instantiated a Client." << std::endl;

    // Try a typical client configuration
    client.setEndpoint("https://appwrite.website.com") // Your API Endpoint
        .setProject("123412341234") // Your project ID
        .setKey("supersecretinformation") // Your secret API key
        .setSelfSigned(true); // Use only on dev mode with a self-signed SSL cert

    // Headers are protected, but if we make them public (in client.hpp), we can print them by uncommenting the following:
/*
    std::cout << "Headers:" << std::endl;
    for (std::map<string, string>::iterator it = client.headers.begin(); it != client.headers.end(); it++) {
        std::cout << "  " << it->first << " => " << it->second << std::endl;
    }
*/

    // Quick test for AppwriteException
    try {
        throw Appwrite::AppwriteException("The SDK is not feeling so good.");
    }
    catch ( Appwrite::AppwriteException &exc ) {
        std::cout << "Something broke: " << exc.what() << std::endl;
    }

    // Basic service test: List users
    std::cout << "Instantiating Users." << std::endl;
    Appwrite::Users users(client);
    std::cout << "User list:" << std::endl;
    std::cout << users.list() << std::endl;

    // See what happens when we don't catch the exception
    throw Appwrite::AppwriteException("I'm not feeling so good.");

    return 0;
}
