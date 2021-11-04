#include <iostream>
#include <map>
#include <string>

#include "client.hpp"
#include "exception.hpp"

using string = std::string;
// #include "services/users.hpp"

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

    throw Appwrite::AppwriteException("I'm not feeling so good.");

    // Once Service classes are working, we can try the following:
/*
    Appwrite::Users users(client);
    std::cout << "User list:";
    std::cout << users.list();
*/
    return 0;
}
