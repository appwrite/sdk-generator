#include <stdexcept>
#include <string>
#include "exception.hpp"

using string = std::string;

namespace Appwrite {

AppwriteException::AppwriteException(string message, int code, string response):
    runtime_error(message) {
    this->message = message;
    this->code = code;
    this->response = response;
}

const char* AppwriteException::what() const throw() {
    return this->message.c_str();
}

string AppwriteException::getResponse() {
    return this->response;
}
} // namespace Appwrite
