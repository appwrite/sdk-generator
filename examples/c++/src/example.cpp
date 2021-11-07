#include <iostream>
#include <map>
#include <string>

#include "client.hpp"
#include "exception.hpp"
#include "services/users.hpp"
#include "services/account.hpp"

using string = std::string;

int main()
{
    std::cout << "Hello World!" << std::endl;

    Appwrite::Client client;

    std::cout << "Instantiated a Client." << std::endl;

    // Try a typical client configuration
    client.setEndpoint("http://localhost/v1")                                                                                                                                                                                                                                  // Your API Endpoint
        .setProject("617ff618903c3")                                                                                                                                                                                                                                                // Your project ID
        .setKey("test this ") // Your secret API key
        .setSelfSigned(true);                                                                                                                                                                                                                                                       // Use only on dev mode with a self-signed SSL cert

    Appwrite::Account account(client);
    // Headers are protected, but if we make them public (in client.hpp), we can print them by uncommenting the following:
    /* 
    std::cout << "Headers:" << std::endl;
    for (std::map<string, string>::iterator it = client.headers.begin(); it != client.headers.end(); it++) {
        std::cout << "  " << it->first << " => " << it->second << std::endl;
    }
 */

    // Quick test for AppwriteException
    try
    {
        json response = account.getLogs();
        std::cout << "Response: " << response.dump(4) << std::endl;
        // throw Appwrite::AppwriteException("The SDK is not feeling so good.");
    }
    catch (Appwrite::AppwriteException &exc)
    {
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
