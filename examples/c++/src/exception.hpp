#pragma once

#include <stdexcept>
#include <string>

using string = std::string;

namespace Appwrite {

class AppwriteException : public std::runtime_error {
 protected:
    string message;
    int code;
    string response;

 public:
    explicit AppwriteException(string message, int code = 0, string response = "");
    const char* what() const throw();
    string getResponse();
};
} // namespace Appwrite
