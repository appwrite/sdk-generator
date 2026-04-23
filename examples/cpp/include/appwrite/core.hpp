#pragma once

#include <string>
#include <chrono>
#include <random>
#include <sstream>
#include <iomanip>
#include <string_view>
#include <vector>
#include <cstdint>
#include <charconv>
#include <cmath>
#include <stdexcept>
#include <mutex>
#include <initializer_list>
#include <span>
#include <fstream>
#include <filesystem>
#include <nlohmann/json.hpp>

namespace appwrite {

namespace internal {

// internal helpers — inline prevents per-TU copies in a header-only library
inline std::string quote(std::string_view s) {
    std::string out = "\"";
    for (char c : s) {
        if      (c == '"')  out += "\\\"";
        else if (c == '\\') out += "\\\\";
        else if (c == '\n') out += "\\n";
        else                out += c;
    }
    return out + "\"";
}

inline std::string int_str(int64_t v) { return std::to_string(v); }

inline std::string dbl_str(double v) {
    char buf[32];
    auto [ptr, ec] = std::to_chars(buf, buf + sizeof(buf), v);
    return ec == std::errc{} ? std::string(buf, ptr) : "0";
}

inline std::string point(double lat, double lon) {
    return "[" + dbl_str(lat) + "," + dbl_str(lon) + "]";
}

inline std::string join(const std::vector<std::string>& parts) {
    std::string out;
    for (size_t i = 0; i < parts.size(); ++i) { 
        if (i) out += ','; 
        out += parts[i]; 
    }
    return out;
}

inline std::vector<std::string> quote_all(const std::vector<std::string>& v) {
    std::vector<std::string> out;
    out.reserve(v.size());
    for (auto const& s : v) out.push_back(quote(s));
    return out;
}

inline std::string build(std::string_view method, std::string_view attribute, const std::vector<std::string>& values, bool force_values = false) {
    std::string s = "{\"method\":\"" + std::string(method) + "\"";
    if (!attribute.empty()) s += ",\"attribute\":" + quote(attribute);
    if (!values.empty() || force_values) {
        s += ",\"values\":[" + join(values) + "]";
    }
    return s + "}";
}

} // namespace internal

/**
 * @brief Strong ID type for Appwrite resources.
 */
class Id {
public:
    Id() = default;
    Id(std::string value) : value_(std::move(value)) {}
    Id(std::string_view value) : value_(value) {}
    Id(const char* value) : value_(value) {}

    static Id custom(std::string id) { return Id(std::move(id)); }
    static Id unique() {
        static constexpr std::string_view hex = "0123456789abcdef";
        static std::mt19937 rng{std::random_device{}()};
        static std::uniform_int_distribution<int> dist{0, 15};
        static std::mutex m;
        std::string id(20, '0');
        std::lock_guard lock(m);
        for (char& c : id) c = hex[dist(rng)];
        return Id(id);
    }

    [[nodiscard]] const std::string& str() const { return value_; }
    operator const std::string&() const { return value_; }
    [[nodiscard]] bool operator==(const Id& other) const { return value_ == other.value_; }
    [[nodiscard]] bool operator!=(const Id& other) const { return value_ != other.value_; }

private:
    std::string value_;
};

using ID = Id;

class Role {
public:
    static std::string any() { return "any"; }
    static std::string guests() { return "guests"; }
    static std::string users(const std::string& status = "") { return status.empty() ? "users" : "users/" + status; }
    static std::string user(const std::string& id, const std::string& status = "") { return status.empty() ? "user:" + id : "user:" + id + "/" + status; }
    static std::string team(const std::string& id, const std::string& role = "") { return role.empty() ? "team:" + id : "team:" + id + "/" + role; }
    static std::string member(const std::string& id) { return "member:" + id; }
    static std::string label(const std::string& name) { return "label:" + name; }
};

class Permission {
public:
    static std::string read(const std::string& role) { return "read(\"" + role + "\")"; }
    static std::string write(const std::string& role) { return "write(\"" + role + "\")"; }
    static std::string create(const std::string& role) { return "create(\"" + role + "\")"; }
    static std::string update(const std::string& role) { return "update(\"" + role + "\")"; }
    static std::string delete_(const std::string& role) { return "delete(\"" + role + "\")"; }
};

/**
 * @brief Fluent builder for Appwrite query filters.
 */
class Query {
public:
    [[nodiscard]] static std::string equal(std::string_view attribute, std::string_view value) { return internal::build("equal", attribute, {internal::quote(value)}); }
    [[nodiscard]] static std::string equal(std::string_view attribute, const char* value) { return internal::build("equal", attribute, {internal::quote(value)}); }
    [[nodiscard]] static std::string equal(std::string_view attribute, int64_t value) { return internal::build("equal", attribute, {internal::int_str(value)}); }
    [[nodiscard]] static std::string equal(std::string_view attribute, int value) { return equal(attribute, (int64_t)value); }
    [[nodiscard]] static std::string equal(std::string_view attribute, double value) { return internal::build("equal", attribute, {internal::dbl_str(value)}); }
    [[nodiscard]] static std::string equal(std::string_view attribute, bool value) { return internal::build("equal", attribute, {value ? "true" : "false"}); }
    [[nodiscard]] static std::string equal(std::string_view attribute, const std::vector<std::string>& values) { return internal::build("equal", attribute, internal::quote_all(values)); }
    
    [[nodiscard]] static std::string notEqual(std::string_view attribute, std::string_view value) { return internal::build("notEqual", attribute, {internal::quote(value)}); }
    [[nodiscard]] static std::string notEqual(std::string_view attribute, const char* value) { return internal::build("notEqual", attribute, {internal::quote(value)}); }
    [[nodiscard]] static std::string notEqual(std::string_view attribute, int64_t value) { return internal::build("notEqual", attribute, {internal::int_str(value)}); }
    [[nodiscard]] static std::string notEqual(std::string_view attribute, int value) { return notEqual(attribute, (int64_t)value); }
    [[nodiscard]] static std::string notEqual(std::string_view attribute, double value) { return internal::build("notEqual", attribute, {internal::dbl_str(value)}); }
    
    [[nodiscard]] static std::string lessThan(std::string_view attribute, std::string_view value) { return internal::build("lessThan", attribute, {internal::quote(value)}); }
    [[nodiscard]] static std::string lessThan(std::string_view attribute, const char* value) { return internal::build("lessThan", attribute, {internal::quote(value)}); }
    [[nodiscard]] static std::string lessThan(std::string_view attribute, int64_t value) { return internal::build("lessThan", attribute, {internal::int_str(value)}); }
    [[nodiscard]] static std::string lessThan(std::string_view attribute, int value) { return lessThan(attribute, (int64_t)value); }
    [[nodiscard]] static std::string lessThan(std::string_view attribute, double value) { return internal::build("lessThan", attribute, {internal::dbl_str(value)}); }
    
    [[nodiscard]] static std::string lessThanEqual(std::string_view attribute, std::string_view value) { return internal::build("lessThanEqual", attribute, {internal::quote(value)}); }
    [[nodiscard]] static std::string lessThanEqual(std::string_view attribute, const char* value) { return internal::build("lessThanEqual", attribute, {internal::quote(value)}); }
    [[nodiscard]] static std::string lessThanEqual(std::string_view attribute, int64_t value) { return internal::build("lessThanEqual", attribute, {internal::int_str(value)}); }
    [[nodiscard]] static std::string lessThanEqual(std::string_view attribute, int value) { return lessThanEqual(attribute, (int64_t)value); }
    [[nodiscard]] static std::string lessThanEqual(std::string_view attribute, double value) { return internal::build("lessThanEqual", attribute, {internal::dbl_str(value)}); }
    
    [[nodiscard]] static std::string greaterThan(std::string_view attribute, std::string_view value) { return internal::build("greaterThan", attribute, {internal::quote(value)}); }
    [[nodiscard]] static std::string greaterThan(std::string_view attribute, const char* value) { return internal::build("greaterThan", attribute, {internal::quote(value)}); }
    [[nodiscard]] static std::string greaterThan(std::string_view attribute, int64_t value) { return internal::build("greaterThan", attribute, {internal::int_str(value)}); }
    [[nodiscard]] static std::string greaterThan(std::string_view attribute, int value) { return greaterThan(attribute, (int64_t)value); }
    [[nodiscard]] static std::string greaterThan(std::string_view attribute, double value) { return internal::build("greaterThan", attribute, {internal::dbl_str(value)}); }
    
    [[nodiscard]] static std::string greaterThanEqual(std::string_view attribute, std::string_view value) { return internal::build("greaterThanEqual", attribute, {internal::quote(value)}); }
    [[nodiscard]] static std::string greaterThanEqual(std::string_view attribute, const char* value) { return internal::build("greaterThanEqual", attribute, {internal::quote(value)}); }
    [[nodiscard]] static std::string greaterThanEqual(std::string_view attribute, int64_t value) { return internal::build("greaterThanEqual", attribute, {internal::int_str(value)}); }
    [[nodiscard]] static std::string greaterThanEqual(std::string_view attribute, int value) { return greaterThanEqual(attribute, (int64_t)value); }
    [[nodiscard]] static std::string greaterThanEqual(std::string_view attribute, double value) { return internal::build("greaterThanEqual", attribute, {internal::dbl_str(value)}); }
    
    [[nodiscard]] static std::string search(std::string_view attribute, std::string_view value) { return internal::build("search", attribute, {internal::quote(value)}); }
    [[nodiscard]] static std::string search(std::string_view attribute, const char* value) { return internal::build("search", attribute, {internal::quote(value)}); }
    [[nodiscard]] static std::string notSearch(std::string_view attribute, std::string_view value) { return internal::build("notSearch", attribute, {internal::quote(value)}); }
    [[nodiscard]] static std::string notSearch(std::string_view attribute, const char* value) { return internal::build("notSearch", attribute, {internal::quote(value)}); }
    
    static std::string contains(std::string_view attribute, std::string_view value) { return internal::build("contains", attribute, {internal::quote(value)}); }
    static std::string contains(std::string_view attribute, const char* value) { return internal::build("contains", attribute, {internal::quote(value)}); }
    static std::string contains(std::string_view attribute, const std::vector<std::string>& values) { return internal::build("contains", attribute, internal::quote_all(values)); }
    static std::string containsAny(std::string_view attribute, const std::vector<std::string>& values) { return internal::build("containsAny", attribute, internal::quote_all(values)); }
    static std::string containsAll(std::string_view attribute, const std::vector<std::string>& values) { return internal::build("containsAll", attribute, internal::quote_all(values)); }
    static std::string notContains(std::string_view attribute, std::string_view value) { return internal::build("notContains", attribute, {internal::quote(value)}); }
    static std::string notContains(std::string_view attribute, const char* value) { return internal::build("notContains", attribute, {internal::quote(value)}); }
    static std::string notContains(std::string_view attribute, const std::vector<std::string>& values) { return internal::build("notContains", attribute, internal::quote_all(values)); }
    
    static std::string between(std::string_view attribute, int64_t start, int64_t end) { return internal::build("between", attribute, {internal::int_str(start), internal::int_str(end)}); }
    static std::string between(std::string_view attribute, int start, int end) { return between(attribute, (int64_t)start, (int64_t)end); }
    static std::string between(std::string_view attribute, double start, double end) { return internal::build("between", attribute, {internal::dbl_str(start), internal::dbl_str(end)}); }
    static std::string between(std::string_view attribute, std::string_view start, std::string_view end) { return internal::build("between", attribute, {internal::quote(start), internal::quote(end)}); }
    static std::string between(std::string_view attribute, const char* start, const char* end) { return internal::build("between", attribute, {internal::quote(start), internal::quote(end)}); }
    
    static std::string notBetween(std::string_view attribute, int64_t start, int64_t end) { return internal::build("notBetween", attribute, {internal::int_str(start), internal::int_str(end)}); }
    static std::string notBetween(std::string_view attribute, int start, int end) { return notBetween(attribute, (int64_t)start, (int64_t)end); }
    static std::string notBetween(std::string_view attribute, double start, double end) { return internal::build("notBetween", attribute, {internal::dbl_str(start), internal::dbl_str(end)}); }
    static std::string notBetween(std::string_view attribute, std::string_view start, std::string_view end) { return internal::build("notBetween", attribute, {internal::quote(start), internal::quote(end)}); }
    static std::string notBetween(std::string_view attribute, const char* start, const char* end) { return internal::build("notBetween", attribute, {internal::quote(start), internal::quote(end)}); }
    
    static std::string startsWith(std::string_view attribute, std::string_view value) { return internal::build("startsWith", attribute, {internal::quote(value)}); }
    static std::string startsWith(std::string_view attribute, const char* value) { return internal::build("startsWith", attribute, {internal::quote(value)}); }
    static std::string notStartsWith(std::string_view attribute, std::string_view value) { return internal::build("notStartsWith", attribute, {internal::quote(value)}); }
    static std::string notStartsWith(std::string_view attribute, const char* value) { return internal::build("notStartsWith", attribute, {internal::quote(value)}); }
    
    static std::string endsWith(std::string_view attribute, std::string_view value) { return internal::build("endsWith", attribute, {internal::quote(value)}); }
    static std::string endsWith(std::string_view attribute, const char* value) { return internal::build("endsWith", attribute, {internal::quote(value)}); }
    static std::string notEndsWith(std::string_view attribute, std::string_view value) { return internal::build("notEndsWith", attribute, {internal::quote(value)}); }
    static std::string notEndsWith(std::string_view attribute, const char* value) { return internal::build("notEndsWith", attribute, {internal::quote(value)}); }
    
    static std::string regex(std::string_view attribute, std::string_view value) { return internal::build("regex", attribute, {internal::quote(value)}); }
    static std::string regex(std::string_view attribute, const char* value) { return internal::build("regex", attribute, {internal::quote(value)}); }
    
    static std::string elemMatch(std::string_view attribute, const std::vector<std::string>& queries) {
        return internal::build("elemMatch", attribute, queries);
    }
    
    [[nodiscard]] static std::string isNull(std::string_view attribute) {
        return "{\"method\":\"isNull\",\"attribute\":" + internal::quote(attribute) + "}";
    }
    [[nodiscard]] static std::string isNotNull(std::string_view attribute) {
        return "{\"method\":\"isNotNull\",\"attribute\":" + internal::quote(attribute) + "}";
    }
    
    static std::string exists(const std::vector<std::string>& attrs) {
        return internal::build("exists", "", internal::quote_all(attrs));
    }

    static std::string notExists(const std::vector<std::string>& attrs) {
        return internal::build("notExists", "", internal::quote_all(attrs));
    }
    
    [[nodiscard]] static std::string limit(int64_t limit) { 
        return "{\"method\":\"limit\",\"values\":[" + std::to_string(limit) + "]}";
    }
    [[nodiscard]] static std::string limit(int n) { return limit((int64_t)n); }
    
    [[nodiscard]] static std::string offset(int64_t offset) {
        return "{\"method\":\"offset\",\"values\":[" + std::to_string(offset) + "]}";
    }
    [[nodiscard]] static std::string offset(int n) { return offset((int64_t)n); }
    
    [[nodiscard]] static std::string cursorAfter(std::string_view documentId) { return internal::build("cursorAfter", "", {internal::quote(documentId)}); }
    [[nodiscard]] static std::string cursorAfter(const char* documentId) { return internal::build("cursorAfter", "", {internal::quote(documentId)}); }
    [[nodiscard]] static std::string cursorBefore(std::string_view documentId) { return internal::build("cursorBefore", "", {internal::quote(documentId)}); }
    [[nodiscard]] static std::string cursorBefore(const char* documentId) { return internal::build("cursorBefore", "", {internal::quote(documentId)}); }
    
    [[nodiscard]] static std::string select(const std::vector<std::string>& attributes) { 
        return internal::build("select", "", internal::quote_all(attributes));
    }
    
    [[nodiscard]] static std::string orderAsc(std::string_view attribute) {
        return "{\"method\":\"orderAsc\",\"attribute\":" + internal::quote(attribute) + "}";
    }
    [[nodiscard]] static std::string orderDesc(std::string_view attribute) {
        return "{\"method\":\"orderDesc\",\"attribute\":" + internal::quote(attribute) + "}";
    }
    static std::string orderRandom() { return "{\"method\":\"orderRandom\"}"; }

    // Geospatial queries - EXACT structures to match Base.php
    static std::string distanceEqual(std::string_view attribute, double lat, double lon, double distance) {
        return internal::build("distanceEqual", attribute, {"[" + internal::point(lat, lon) + "," + internal::dbl_str(distance) + ",true]"});
    }
    static std::string distanceEqual(std::string_view attribute, double lat1, double lon1, double lat2, double lon2, double distance) {
        // values:[[[[p1],[p2]],distance,true]] — points nested inside an extra array
        return internal::build("distanceEqual", attribute, {
            "[[" + internal::point(lat1, lon1) + "," + internal::point(lat2, lon2) + "]," + 
            internal::dbl_str(distance) + ",true]"
        });
    }
    static std::string distanceNotEqual(std::string_view attribute, double lat, double lon, double distance) {
        return internal::build("distanceNotEqual", attribute, {"[" + internal::point(lat, lon) + "," + internal::dbl_str(distance) + ",true]"});
    }
    static std::string distanceLessThan(std::string_view attribute, double lat, double lon, double distance) {
        return internal::build("distanceLessThan", attribute, {"[" + internal::point(lat, lon) + "," + internal::dbl_str(distance) + ",true]"});
    }
    static std::string distanceGreaterThan(std::string_view attribute, double lat, double lon, double distance) {
        return internal::build("distanceGreaterThan", attribute, {"[" + internal::point(lat, lon) + "," + internal::dbl_str(distance) + ",true]"});
    }
    static std::string intersects(std::string_view attribute, double lat, double lon) {
        return internal::build("intersects", attribute, {internal::point(lat, lon)});
    }
    static std::string notIntersects(std::string_view attribute, double lat, double lon) {
        return internal::build("notIntersects", attribute, {internal::point(lat, lon)});
    }
    static std::string crosses(std::string_view attribute, double lat, double lon) {
        return internal::build("crosses", attribute, {internal::point(lat, lon)});
    }
    static std::string notCrosses(std::string_view attribute, double lat, double lon) {
        return internal::build("notCrosses", attribute, {internal::point(lat, lon)});
    }
    static std::string overlaps(std::string_view attribute, double lat, double lon) {
        return internal::build("overlaps", attribute, {internal::point(lat, lon)});
    }
    static std::string notOverlaps(std::string_view attribute, double lat, double lon) {
        return internal::build("notOverlaps", attribute, {internal::point(lat, lon)});
    }
    static std::string touches(std::string_view attribute, double lat, double lon) {
        return internal::build("touches", attribute, {internal::point(lat, lon)});
    }
    static std::string notTouches(std::string_view attribute, double lat, double lon) {
        return internal::build("notTouches", attribute, {internal::point(lat, lon)});
    }

    [[nodiscard]] static std::string or_(const std::vector<std::string>& queries) {
        return internal::build("or", "", queries);
    }
    [[nodiscard]] static std::string and_(const std::vector<std::string>& queries) {
        return internal::build("and", "", queries);
    }
};

/**
 * @brief Database field update operators.
 */
class Operator {
public:
    static std::string increment(double value = 1.0) { return internal::build("increment", "", {internal::dbl_str(value)}); }
    static std::string increment(double value, double max) { return internal::build("increment", "", {internal::dbl_str(value), internal::dbl_str(max)}); }
    static std::string decrement(double value = 1.0) { return internal::build("decrement", "", {internal::dbl_str(value)}); }
    static std::string decrement(double value, double min) { return internal::build("decrement", "", {internal::dbl_str(value), internal::dbl_str(min)}); }
    static std::string multiply(double value) { return internal::build("multiply", "", {internal::dbl_str(value)}); }
    static std::string multiply(double value, double max) { return internal::build("multiply", "", {internal::dbl_str(value), internal::dbl_str(max)}); }
    static std::string divide(double value) { return internal::build("divide", "", {internal::dbl_str(value)}); }
    static std::string divide(double value, double min) { return internal::build("divide", "", {internal::dbl_str(value), internal::dbl_str(min)}); }
    static std::string modulo(double value) { return internal::build("modulo", "", {internal::dbl_str(value)}); }
    static std::string power(double value) { return internal::build("power", "", {internal::dbl_str(value)}); }
    static std::string power(double value, double max) { return internal::build("power", "", {internal::dbl_str(value), internal::dbl_str(max)}); }
    
    static std::string arrayAppend(const std::string& value) { return internal::build("arrayAppend", "", {internal::quote(value)}); }
    static std::string arrayAppend(const std::vector<std::string>& values) { return internal::build("arrayAppend", "", internal::quote_all(values)); }
    static std::string arrayPrepend(const std::string& value) { return internal::build("arrayPrepend", "", {internal::quote(value)}); }
    static std::string arrayPrepend(const std::vector<std::string>& values) { return internal::build("arrayPrepend", "", internal::quote_all(values)); }
    static std::string arrayInsert(int index, const std::string& value) { 
        return internal::build("arrayInsert", "", {std::to_string(index), internal::quote(value)});
    }
    static std::string arrayRemove(const std::string& value) { return internal::build("arrayRemove", "", {internal::quote(value)}); }
    static std::string arrayUnique() { return internal::build("arrayUnique", "", {}, true); }
    static std::string arrayIntersect(const std::vector<std::string>& values) { return internal::build("arrayIntersect", "", internal::quote_all(values)); }
    static std::string arrayDiff(const std::vector<std::string>& values) { return internal::build("arrayDiff", "", internal::quote_all(values)); }
    static std::string arrayFilter(const std::string& method, const std::string& param) { return internal::build("arrayFilter", "", {internal::quote(method), internal::quote(param)}); }
    
    static std::string stringConcat(const std::string& value) { return internal::build("stringConcat", "", {internal::quote(value)}); }
    static std::string stringReplace(const std::string& search, const std::string& replace) {
        return internal::build("stringReplace", "", {internal::quote(search), internal::quote(replace)});
    }
    
    static std::string toggle() { return internal::build("toggle", "", {}, true); }
    
    static std::string dateAddDays(int days) { return internal::build("dateAddDays", "", {std::to_string(days)}); }
    static std::string dateSubDays(int days) { return internal::build("dateSubDays", "", {std::to_string(days)}); }
    static std::string dateSetNow() { return internal::build("dateSetNow", "", {}, true); }
};

/**
 * @brief Wrapper for files to be uploaded to the Appwrite API.
 */
class InputFile {
public:
    struct Progress {
        size_t id;
        double progress;
        size_t sizeUploaded;
        size_t chunksTotal;
        size_t chunksUploaded;
    };

    [[nodiscard]] static InputFile fromPath(std::string path) {
        std::ifstream file(path, std::ios::binary | std::ios::ate);
        if (!file) throw std::runtime_error("Could not open file: " + path);
        size_t size = file.tellg();
        std::string filename = std::filesystem::path(path).filename().string();
        return InputFile(std::move(path), std::move(filename), size);
    }
    
    [[nodiscard]] const std::string& path() const { return path_; }
    [[nodiscard]] const std::string& filename() const { return filename_; }
    [[nodiscard]] size_t size() const { return size_; }

    [[nodiscard]] std::string readChunk(size_t offset, size_t size) const {
        std::ifstream file(path_, std::ios::binary);
        if (!file) throw std::runtime_error("Could not open file: " + path_);
        
        file.seekg(offset);
        std::string buffer;
        buffer.resize(size);
        file.read(buffer.data(), size);
        buffer.resize(file.gcount());
        return buffer;
    }

    [[nodiscard]] nlohmann::json toJson() const {
        nlohmann::json j = nlohmann::json::object();
        j["$inputFile"]["path"] = path_;
        j["$inputFile"]["filename"] = filename_;
        j["$inputFile"]["size"] = size_;
        return j;
    }

private:
    InputFile(std::string path, std::string filename, size_t size)
        : path_(std::move(path)), filename_(std::move(filename)), size_(size) {}
    std::string path_;
    std::string filename_;
    size_t      size_;
};

} // namespace appwrite
