#pragma once

#include <string>
#include <map>
#include "../temp_libs/json.hpp"
#include "../client.hpp"
#include "../service.hpp"

using json = nlohmann::json;
using string = std::string;

namespace Appwrite {

class Storage : public Service {
 public:
    using Service::Service;
    json listFiles(string search = "", int limit = 25, int offset = 0, string orderType = "ASC");
    json createFile(file file, array read = , array write = );
    json getFile(string fileId);
    json updateFile(string fileId, array read, array write);
    json deleteFile(string fileId);
    string getFileDownload(string fileId);
    string getFilePreview(string fileId, int width = 0, int height = 0, string gravity = "center", int quality = 100, int borderWidth = 0, string borderColor = "", int borderRadius = 0, double opacity = 1, int rotation = 0, string background = "", string output = "");
    string getFileView(string fileId);
};
} // namespace Appwrite
