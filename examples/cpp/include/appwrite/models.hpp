#pragma once

#include <string>
#include <vector>
#include <optional>
#include <nlohmann/json.hpp>
#include "base.hpp"
#include "enums/enums.hpp"

namespace appwrite {
namespace models {

// Forward declarations — required because some structs reference types defined later
struct Row;
struct RowList;
struct Document;
struct DocumentList;
struct ColumnIndex;
struct Table;
struct TableList;
struct Index;
struct Collection;
struct CollectionList;
struct Database;
struct DatabaseList;
struct IndexList;
struct ColumnIndexList;
struct Preferences;
struct Target;
struct User;
struct UserList;
struct Session;
struct SessionList;
struct Identity;
struct IdentityList;
struct Log;
struct LogList;
struct File;
struct FileList;
struct Bucket;
struct BucketList;
struct Team;
struct TeamList;
struct Membership;
struct MembershipList;
struct Variable;
struct Function;
struct FunctionList;
struct Runtime;
struct RuntimeList;
struct Deployment;
struct DeploymentList;
struct Headers;
struct Execution;
struct ExecutionList;
struct Webhook;
struct WebhookList;
struct VariableList;
struct TargetList;
struct Transaction;
struct TransactionList;
struct Specification;
struct SpecificationList;
struct AttributeList;
struct AttributeString;
struct AttributeInteger;
struct AttributeFloat;
struct AttributeBoolean;
struct AttributeEmail;
struct AttributeEnum;
struct AttributeIp;
struct AttributeUrl;
struct AttributeDatetime;
struct AttributeRelationship;
struct AttributePoint;
struct AttributeLine;
struct AttributePolygon;
struct AttributeVarchar;
struct AttributeText;
struct AttributeMediumtext;
struct AttributeLongtext;
struct ColumnList;
struct ColumnString;
struct ColumnInteger;
struct ColumnFloat;
struct ColumnBoolean;
struct ColumnEmail;
struct ColumnEnum;
struct ColumnIp;
struct ColumnUrl;
struct ColumnDatetime;
struct ColumnRelationship;
struct ColumnPoint;
struct ColumnLine;
struct ColumnPolygon;
struct ColumnVarchar;
struct ColumnText;
struct ColumnMediumtext;
struct ColumnLongtext;
struct AlgoMd5;
struct AlgoSha;
struct AlgoPhpass;
struct AlgoBcrypt;
struct AlgoScrypt;
struct AlgoScryptModified;
struct AlgoArgon2;
struct Token;
struct Jwt;
struct MfaChallenge;
struct MfaRecoveryCodes;
struct MfaType;
struct MfaFactors;

/**
 * @brief Row
 */
struct Row {
    /** @brief Row ID. */
    std::string id;
    /** @brief Row sequence ID. */
    std::string sequence;
    /** @brief Table ID. */
    std::string tableId;
    /** @brief Database ID. */
    std::string databaseId;
    /** @brief Row creation date in ISO 8601 format. */
    std::string createdAt;
    /** @brief Row update date in ISO 8601 format. */
    std::string updatedAt;
    /** @brief Row permissions. [Learn more about permissions](https://appwrite.io/docs/permissions). */
    std::vector<std::string> permissions;

    /** @brief Deserialize from JSON */
    static Row fromJson(const nlohmann::json& j) {
        Row obj{}; // value-init: zeroes bools, ints, enums
        if (j.contains("$id")) {
            if (j["$id"].is_null()) {
                throw appwrite::DeserializationException("Required field '$id' is null");
            } else {
                obj.id = j["$id"].get<std::string>();
            }
        }
        if (j.contains("$sequence")) {
            if (j["$sequence"].is_null()) {
                throw appwrite::DeserializationException("Required field '$sequence' is null");
            } else {
                obj.sequence = j["$sequence"].get<std::string>();
            }
        }
        if (j.contains("$tableId")) {
            if (j["$tableId"].is_null()) {
                throw appwrite::DeserializationException("Required field '$tableId' is null");
            } else {
                obj.tableId = j["$tableId"].get<std::string>();
            }
        }
        if (j.contains("$databaseId")) {
            if (j["$databaseId"].is_null()) {
                throw appwrite::DeserializationException("Required field '$databaseId' is null");
            } else {
                obj.databaseId = j["$databaseId"].get<std::string>();
            }
        }
        if (j.contains("$createdAt")) {
            if (j["$createdAt"].is_null()) {
                throw appwrite::DeserializationException("Required field '$createdAt' is null");
            } else {
                obj.createdAt = j["$createdAt"].get<std::string>();
            }
        }
        if (j.contains("$updatedAt")) {
            if (j["$updatedAt"].is_null()) {
                throw appwrite::DeserializationException("Required field '$updatedAt' is null");
            } else {
                obj.updatedAt = j["$updatedAt"].get<std::string>();
            }
        }
        if (j.contains("$permissions")) {
            if (j["$permissions"].is_null()) {
                throw appwrite::DeserializationException("Required field '$permissions' is null");
            } else {
                obj.permissions = j["$permissions"].get<std::vector<std::string>>();
            }
        }
        return obj;
    }

    /** @brief Serialize to JSON */
    [[nodiscard]] nlohmann::json toJson() const {
        nlohmann::json j = nlohmann::json::object();
        {
            j["$id"] = this->id;
        }
        {
            j["$sequence"] = this->sequence;
        }
        {
            j["$tableId"] = this->tableId;
        }
        {
            j["$databaseId"] = this->databaseId;
        }
        {
            j["$createdAt"] = this->createdAt;
        }
        {
            j["$updatedAt"] = this->updatedAt;
        }
        {
            j["$permissions"] = this->permissions;
        }
        return j;
    }
};

/** @brief ADL hook for nlohmann::json */
inline void from_json(const nlohmann::json& j, Row& x) {
    x = Row::fromJson(j);
}

inline void to_json(nlohmann::json& j, const Row& x) {
    j = x.toJson();
}

/**
 * @brief Rows List
 */
struct RowList {
    /** @brief Total number of rows that matched your query. */
    int64_t total;
    /** @brief List of rows. */
    std::vector<appwrite::models::Row> rows;

    /** @brief Deserialize from JSON */
    static RowList fromJson(const nlohmann::json& j) {
        RowList obj{}; // value-init: zeroes bools, ints, enums
        if (j.contains("total")) {
            if (j["total"].is_null()) {
                throw appwrite::DeserializationException("Required field 'total' is null");
            } else {
                obj.total = j["total"].get<int64_t>();
            }
        }
        if (j.contains("rows")) {
            if (j["rows"].is_null()) {
                throw appwrite::DeserializationException("Required field 'rows' is null");
            } else {
                                            for (auto& item : j["rows"]) {
                                    obj.rows.push_back(Row::fromJson(item));
                                }
                        }
        }
        return obj;
    }

    /** @brief Serialize to JSON */
    [[nodiscard]] nlohmann::json toJson() const {
        nlohmann::json j = nlohmann::json::object();
        {
            j["total"] = this->total;
        }
        {
                nlohmann::json arr = nlohmann::json::array();
            for (auto& item : this->rows) arr.push_back(item.toJson());
            j["rows"] = arr;
            }
        return j;
    }
};

/** @brief ADL hook for nlohmann::json */
inline void from_json(const nlohmann::json& j, RowList& x) {
    x = RowList::fromJson(j);
}

inline void to_json(nlohmann::json& j, const RowList& x) {
    j = x.toJson();
}

/**
 * @brief Document
 */
struct Document {
    /** @brief Document ID. */
    std::string id;
    /** @brief Document sequence ID. */
    std::string sequence;
    /** @brief Collection ID. */
    std::string collectionId;
    /** @brief Database ID. */
    std::string databaseId;
    /** @brief Document creation date in ISO 8601 format. */
    std::string createdAt;
    /** @brief Document update date in ISO 8601 format. */
    std::string updatedAt;
    /** @brief Document permissions. [Learn more about permissions](https://appwrite.io/docs/permissions). */
    std::vector<std::string> permissions;

    /** @brief Deserialize from JSON */
    static Document fromJson(const nlohmann::json& j) {
        Document obj{}; // value-init: zeroes bools, ints, enums
        if (j.contains("$id")) {
            if (j["$id"].is_null()) {
                throw appwrite::DeserializationException("Required field '$id' is null");
            } else {
                obj.id = j["$id"].get<std::string>();
            }
        }
        if (j.contains("$sequence")) {
            if (j["$sequence"].is_null()) {
                throw appwrite::DeserializationException("Required field '$sequence' is null");
            } else {
                obj.sequence = j["$sequence"].get<std::string>();
            }
        }
        if (j.contains("$collectionId")) {
            if (j["$collectionId"].is_null()) {
                throw appwrite::DeserializationException("Required field '$collectionId' is null");
            } else {
                obj.collectionId = j["$collectionId"].get<std::string>();
            }
        }
        if (j.contains("$databaseId")) {
            if (j["$databaseId"].is_null()) {
                throw appwrite::DeserializationException("Required field '$databaseId' is null");
            } else {
                obj.databaseId = j["$databaseId"].get<std::string>();
            }
        }
        if (j.contains("$createdAt")) {
            if (j["$createdAt"].is_null()) {
                throw appwrite::DeserializationException("Required field '$createdAt' is null");
            } else {
                obj.createdAt = j["$createdAt"].get<std::string>();
            }
        }
        if (j.contains("$updatedAt")) {
            if (j["$updatedAt"].is_null()) {
                throw appwrite::DeserializationException("Required field '$updatedAt' is null");
            } else {
                obj.updatedAt = j["$updatedAt"].get<std::string>();
            }
        }
        if (j.contains("$permissions")) {
            if (j["$permissions"].is_null()) {
                throw appwrite::DeserializationException("Required field '$permissions' is null");
            } else {
                obj.permissions = j["$permissions"].get<std::vector<std::string>>();
            }
        }
        return obj;
    }

    /** @brief Serialize to JSON */
    [[nodiscard]] nlohmann::json toJson() const {
        nlohmann::json j = nlohmann::json::object();
        {
            j["$id"] = this->id;
        }
        {
            j["$sequence"] = this->sequence;
        }
        {
            j["$collectionId"] = this->collectionId;
        }
        {
            j["$databaseId"] = this->databaseId;
        }
        {
            j["$createdAt"] = this->createdAt;
        }
        {
            j["$updatedAt"] = this->updatedAt;
        }
        {
            j["$permissions"] = this->permissions;
        }
        return j;
    }
};

/** @brief ADL hook for nlohmann::json */
inline void from_json(const nlohmann::json& j, Document& x) {
    x = Document::fromJson(j);
}

inline void to_json(nlohmann::json& j, const Document& x) {
    j = x.toJson();
}

/**
 * @brief Documents List
 */
struct DocumentList {
    /** @brief Total number of documents that matched your query. */
    int64_t total;
    /** @brief List of documents. */
    std::vector<appwrite::models::Document> documents;

    /** @brief Deserialize from JSON */
    static DocumentList fromJson(const nlohmann::json& j) {
        DocumentList obj{}; // value-init: zeroes bools, ints, enums
        if (j.contains("total")) {
            if (j["total"].is_null()) {
                throw appwrite::DeserializationException("Required field 'total' is null");
            } else {
                obj.total = j["total"].get<int64_t>();
            }
        }
        if (j.contains("documents")) {
            if (j["documents"].is_null()) {
                throw appwrite::DeserializationException("Required field 'documents' is null");
            } else {
                                            for (auto& item : j["documents"]) {
                                    obj.documents.push_back(Document::fromJson(item));
                                }
                        }
        }
        return obj;
    }

    /** @brief Serialize to JSON */
    [[nodiscard]] nlohmann::json toJson() const {
        nlohmann::json j = nlohmann::json::object();
        {
            j["total"] = this->total;
        }
        {
                nlohmann::json arr = nlohmann::json::array();
            for (auto& item : this->documents) arr.push_back(item.toJson());
            j["documents"] = arr;
            }
        return j;
    }
};

/** @brief ADL hook for nlohmann::json */
inline void from_json(const nlohmann::json& j, DocumentList& x) {
    x = DocumentList::fromJson(j);
}

inline void to_json(nlohmann::json& j, const DocumentList& x) {
    j = x.toJson();
}

/**
 * @brief Index
 */
struct ColumnIndex {
    /** @brief Index ID. */
    std::string id;
    /** @brief Index creation date in ISO 8601 format. */
    std::string createdAt;
    /** @brief Index update date in ISO 8601 format. */
    std::string updatedAt;
    /** @brief Index Key. */
    std::string key;
    /** @brief Index type. */
    std::string type;
    /** @brief Index status. Possible values: `available`, `processing`, `deleting`, `stuck`, or `failed` */
    std::string status;
    /** @brief Error message. Displays error generated on failure of creating or deleting an index. */
    std::string error;
    /** @brief Index columns. */
    std::vector<std::string> columns;
    /** @brief Index columns length. */
    std::vector<int64_t> lengths;
    /** @brief Index orders. */
    std::optional<std::vector<std::string>> orders = std::nullopt;

    /** @brief Deserialize from JSON */
    static ColumnIndex fromJson(const nlohmann::json& j) {
        ColumnIndex obj{}; // value-init: zeroes bools, ints, enums
        if (j.contains("$id")) {
            if (j["$id"].is_null()) {
                throw appwrite::DeserializationException("Required field '$id' is null");
            } else {
                obj.id = j["$id"].get<std::string>();
            }
        }
        if (j.contains("$createdAt")) {
            if (j["$createdAt"].is_null()) {
                throw appwrite::DeserializationException("Required field '$createdAt' is null");
            } else {
                obj.createdAt = j["$createdAt"].get<std::string>();
            }
        }
        if (j.contains("$updatedAt")) {
            if (j["$updatedAt"].is_null()) {
                throw appwrite::DeserializationException("Required field '$updatedAt' is null");
            } else {
                obj.updatedAt = j["$updatedAt"].get<std::string>();
            }
        }
        if (j.contains("key")) {
            if (j["key"].is_null()) {
                throw appwrite::DeserializationException("Required field 'key' is null");
            } else {
                obj.key = j["key"].get<std::string>();
            }
        }
        if (j.contains("type")) {
            if (j["type"].is_null()) {
                throw appwrite::DeserializationException("Required field 'type' is null");
            } else {
                obj.type = j["type"].get<std::string>();
            }
        }
        if (j.contains("status")) {
            if (j["status"].is_null()) {
                throw appwrite::DeserializationException("Required field 'status' is null");
            } else {
                obj.status = j["status"].get<std::string>();
            }
        }
        if (j.contains("error")) {
            if (j["error"].is_null()) {
                throw appwrite::DeserializationException("Required field 'error' is null");
            } else {
                obj.error = j["error"].get<std::string>();
            }
        }
        if (j.contains("columns")) {
            if (j["columns"].is_null()) {
                throw appwrite::DeserializationException("Required field 'columns' is null");
            } else {
                obj.columns = j["columns"].get<std::vector<std::string>>();
            }
        }
        if (j.contains("lengths")) {
            if (j["lengths"].is_null()) {
                throw appwrite::DeserializationException("Required field 'lengths' is null");
            } else {
                obj.lengths = j["lengths"].get<std::vector<int64_t>>();
            }
        }
        if (j.contains("orders")) {
            if (j["orders"].is_null()) {
                obj.orders = std::nullopt;
            } else {
                obj.orders = j["orders"].get<std::optional<std::vector<std::string>>>();
            }
        }
        return obj;
    }

    /** @brief Serialize to JSON */
    [[nodiscard]] nlohmann::json toJson() const {
        nlohmann::json j = nlohmann::json::object();
        {
            j["$id"] = this->id;
        }
        {
            j["$createdAt"] = this->createdAt;
        }
        {
            j["$updatedAt"] = this->updatedAt;
        }
        {
            j["key"] = this->key;
        }
        {
            j["type"] = this->type;
        }
        {
            j["status"] = this->status;
        }
        {
            j["error"] = this->error;
        }
        {
            j["columns"] = this->columns;
        }
        {
            j["lengths"] = this->lengths;
        }
        if (this->orders.has_value()) {
            j["orders"] = this->orders.value();
        }
        return j;
    }
};

/** @brief ADL hook for nlohmann::json */
inline void from_json(const nlohmann::json& j, ColumnIndex& x) {
    x = ColumnIndex::fromJson(j);
}

inline void to_json(nlohmann::json& j, const ColumnIndex& x) {
    j = x.toJson();
}

/**
 * @brief Table
 */
struct Table {
    /** @brief Table ID. */
    std::string id;
    /** @brief Table creation date in ISO 8601 format. */
    std::string createdAt;
    /** @brief Table update date in ISO 8601 format. */
    std::string updatedAt;
    /** @brief Table permissions. [Learn more about permissions](https://appwrite.io/docs/permissions). */
    std::vector<std::string> permissions;
    /** @brief Database ID. */
    std::string databaseId;
    /** @brief Table name. */
    std::string name;
    /** @brief Table enabled. Can be 'enabled' or 'disabled'. When disabled, the table is inaccessible to users, but remains accessible to Server SDKs using API keys. */
    bool enabled;
    /** @brief Whether row-level permissions are enabled. [Learn more about permissions](https://appwrite.io/docs/permissions). */
    bool rowSecurity;
    /** @brief Table columns. */
    std::vector<std::string> columns;
    /** @brief Table indexes. */
    std::vector<appwrite::models::ColumnIndex> indexes;
    /** @brief Maximum row size in bytes. Returns 0 when no limit applies. */
    int64_t bytesMax;
    /** @brief Currently used row size in bytes based on defined columns. */
    int64_t bytesUsed;

    /** @brief Deserialize from JSON */
    static Table fromJson(const nlohmann::json& j) {
        Table obj{}; // value-init: zeroes bools, ints, enums
        if (j.contains("$id")) {
            if (j["$id"].is_null()) {
                throw appwrite::DeserializationException("Required field '$id' is null");
            } else {
                obj.id = j["$id"].get<std::string>();
            }
        }
        if (j.contains("$createdAt")) {
            if (j["$createdAt"].is_null()) {
                throw appwrite::DeserializationException("Required field '$createdAt' is null");
            } else {
                obj.createdAt = j["$createdAt"].get<std::string>();
            }
        }
        if (j.contains("$updatedAt")) {
            if (j["$updatedAt"].is_null()) {
                throw appwrite::DeserializationException("Required field '$updatedAt' is null");
            } else {
                obj.updatedAt = j["$updatedAt"].get<std::string>();
            }
        }
        if (j.contains("$permissions")) {
            if (j["$permissions"].is_null()) {
                throw appwrite::DeserializationException("Required field '$permissions' is null");
            } else {
                obj.permissions = j["$permissions"].get<std::vector<std::string>>();
            }
        }
        if (j.contains("databaseId")) {
            if (j["databaseId"].is_null()) {
                throw appwrite::DeserializationException("Required field 'databaseId' is null");
            } else {
                obj.databaseId = j["databaseId"].get<std::string>();
            }
        }
        if (j.contains("name")) {
            if (j["name"].is_null()) {
                throw appwrite::DeserializationException("Required field 'name' is null");
            } else {
                obj.name = j["name"].get<std::string>();
            }
        }
        if (j.contains("enabled")) {
            if (j["enabled"].is_null()) {
                throw appwrite::DeserializationException("Required field 'enabled' is null");
            } else {
                obj.enabled = j["enabled"].get<bool>();
            }
        }
        if (j.contains("rowSecurity")) {
            if (j["rowSecurity"].is_null()) {
                throw appwrite::DeserializationException("Required field 'rowSecurity' is null");
            } else {
                obj.rowSecurity = j["rowSecurity"].get<bool>();
            }
        }
        if (j.contains("columns")) {
            if (j["columns"].is_null()) {
                throw appwrite::DeserializationException("Required field 'columns' is null");
            } else {
                obj.columns = j["columns"].get<std::vector<std::string>>();
            }
        }
        if (j.contains("indexes")) {
            if (j["indexes"].is_null()) {
                throw appwrite::DeserializationException("Required field 'indexes' is null");
            } else {
                                            for (auto& item : j["indexes"]) {
                                    obj.indexes.push_back(ColumnIndex::fromJson(item));
                                }
                        }
        }
        if (j.contains("bytesMax")) {
            if (j["bytesMax"].is_null()) {
                throw appwrite::DeserializationException("Required field 'bytesMax' is null");
            } else {
                obj.bytesMax = j["bytesMax"].get<int64_t>();
            }
        }
        if (j.contains("bytesUsed")) {
            if (j["bytesUsed"].is_null()) {
                throw appwrite::DeserializationException("Required field 'bytesUsed' is null");
            } else {
                obj.bytesUsed = j["bytesUsed"].get<int64_t>();
            }
        }
        return obj;
    }

    /** @brief Serialize to JSON */
    [[nodiscard]] nlohmann::json toJson() const {
        nlohmann::json j = nlohmann::json::object();
        {
            j["$id"] = this->id;
        }
        {
            j["$createdAt"] = this->createdAt;
        }
        {
            j["$updatedAt"] = this->updatedAt;
        }
        {
            j["$permissions"] = this->permissions;
        }
        {
            j["databaseId"] = this->databaseId;
        }
        {
            j["name"] = this->name;
        }
        {
            j["enabled"] = this->enabled;
        }
        {
            j["rowSecurity"] = this->rowSecurity;
        }
        {
            j["columns"] = this->columns;
        }
        {
                nlohmann::json arr = nlohmann::json::array();
            for (auto& item : this->indexes) arr.push_back(item.toJson());
            j["indexes"] = arr;
            }
        {
            j["bytesMax"] = this->bytesMax;
        }
        {
            j["bytesUsed"] = this->bytesUsed;
        }
        return j;
    }
};

/** @brief ADL hook for nlohmann::json */
inline void from_json(const nlohmann::json& j, Table& x) {
    x = Table::fromJson(j);
}

inline void to_json(nlohmann::json& j, const Table& x) {
    j = x.toJson();
}

/**
 * @brief Tables List
 */
struct TableList {
    /** @brief Total number of tables that matched your query. */
    int64_t total;
    /** @brief List of tables. */
    std::vector<appwrite::models::Table> tables;

    /** @brief Deserialize from JSON */
    static TableList fromJson(const nlohmann::json& j) {
        TableList obj{}; // value-init: zeroes bools, ints, enums
        if (j.contains("total")) {
            if (j["total"].is_null()) {
                throw appwrite::DeserializationException("Required field 'total' is null");
            } else {
                obj.total = j["total"].get<int64_t>();
            }
        }
        if (j.contains("tables")) {
            if (j["tables"].is_null()) {
                throw appwrite::DeserializationException("Required field 'tables' is null");
            } else {
                                            for (auto& item : j["tables"]) {
                                    obj.tables.push_back(Table::fromJson(item));
                                }
                        }
        }
        return obj;
    }

    /** @brief Serialize to JSON */
    [[nodiscard]] nlohmann::json toJson() const {
        nlohmann::json j = nlohmann::json::object();
        {
            j["total"] = this->total;
        }
        {
                nlohmann::json arr = nlohmann::json::array();
            for (auto& item : this->tables) arr.push_back(item.toJson());
            j["tables"] = arr;
            }
        return j;
    }
};

/** @brief ADL hook for nlohmann::json */
inline void from_json(const nlohmann::json& j, TableList& x) {
    x = TableList::fromJson(j);
}

inline void to_json(nlohmann::json& j, const TableList& x) {
    j = x.toJson();
}

/**
 * @brief Index
 */
struct Index {
    /** @brief Index ID. */
    std::string id;
    /** @brief Index creation date in ISO 8601 format. */
    std::string createdAt;
    /** @brief Index update date in ISO 8601 format. */
    std::string updatedAt;
    /** @brief Index key. */
    std::string key;
    /** @brief Index type. */
    std::string type;
    /** @brief Index status. Possible values: `available`, `processing`, `deleting`, `stuck`, or `failed` */
    appwrite::enums::IndexStatus status;
    /** @brief Error message. Displays error generated on failure of creating or deleting an index. */
    std::string error;
    /** @brief Index attributes. */
    std::vector<std::string> attributes;
    /** @brief Index attributes length. */
    std::vector<int64_t> lengths;
    /** @brief Index orders. */
    std::optional<std::vector<std::string>> orders = std::nullopt;

    /** @brief Deserialize from JSON */
    static Index fromJson(const nlohmann::json& j) {
        Index obj{}; // value-init: zeroes bools, ints, enums
        if (j.contains("$id")) {
            if (j["$id"].is_null()) {
                throw appwrite::DeserializationException("Required field '$id' is null");
            } else {
                obj.id = j["$id"].get<std::string>();
            }
        }
        if (j.contains("$createdAt")) {
            if (j["$createdAt"].is_null()) {
                throw appwrite::DeserializationException("Required field '$createdAt' is null");
            } else {
                obj.createdAt = j["$createdAt"].get<std::string>();
            }
        }
        if (j.contains("$updatedAt")) {
            if (j["$updatedAt"].is_null()) {
                throw appwrite::DeserializationException("Required field '$updatedAt' is null");
            } else {
                obj.updatedAt = j["$updatedAt"].get<std::string>();
            }
        }
        if (j.contains("key")) {
            if (j["key"].is_null()) {
                throw appwrite::DeserializationException("Required field 'key' is null");
            } else {
                obj.key = j["key"].get<std::string>();
            }
        }
        if (j.contains("type")) {
            if (j["type"].is_null()) {
                throw appwrite::DeserializationException("Required field 'type' is null");
            } else {
                obj.type = j["type"].get<std::string>();
            }
        }
        if (j.contains("status")) {
            if (j["status"].is_null()) {
                throw appwrite::DeserializationException("Required field 'status' is null");
            } else {
                obj.status = j["status"].get<appwrite::enums::IndexStatus>();
            }
        }
        if (j.contains("error")) {
            if (j["error"].is_null()) {
                throw appwrite::DeserializationException("Required field 'error' is null");
            } else {
                obj.error = j["error"].get<std::string>();
            }
        }
        if (j.contains("attributes")) {
            if (j["attributes"].is_null()) {
                throw appwrite::DeserializationException("Required field 'attributes' is null");
            } else {
                obj.attributes = j["attributes"].get<std::vector<std::string>>();
            }
        }
        if (j.contains("lengths")) {
            if (j["lengths"].is_null()) {
                throw appwrite::DeserializationException("Required field 'lengths' is null");
            } else {
                obj.lengths = j["lengths"].get<std::vector<int64_t>>();
            }
        }
        if (j.contains("orders")) {
            if (j["orders"].is_null()) {
                obj.orders = std::nullopt;
            } else {
                obj.orders = j["orders"].get<std::optional<std::vector<std::string>>>();
            }
        }
        return obj;
    }

    /** @brief Serialize to JSON */
    [[nodiscard]] nlohmann::json toJson() const {
        nlohmann::json j = nlohmann::json::object();
        {
            j["$id"] = this->id;
        }
        {
            j["$createdAt"] = this->createdAt;
        }
        {
            j["$updatedAt"] = this->updatedAt;
        }
        {
            j["key"] = this->key;
        }
        {
            j["type"] = this->type;
        }
        {
            j["status"] = appwrite::enums::toString(this->status);
        }
        {
            j["error"] = this->error;
        }
        {
            j["attributes"] = this->attributes;
        }
        {
            j["lengths"] = this->lengths;
        }
        if (this->orders.has_value()) {
            j["orders"] = this->orders.value();
        }
        return j;
    }
};

/** @brief ADL hook for nlohmann::json */
inline void from_json(const nlohmann::json& j, Index& x) {
    x = Index::fromJson(j);
}

inline void to_json(nlohmann::json& j, const Index& x) {
    j = x.toJson();
}

/**
 * @brief Collection
 */
struct Collection {
    /** @brief Collection ID. */
    std::string id;
    /** @brief Collection creation date in ISO 8601 format. */
    std::string createdAt;
    /** @brief Collection update date in ISO 8601 format. */
    std::string updatedAt;
    /** @brief Collection permissions. [Learn more about permissions](https://appwrite.io/docs/permissions). */
    std::vector<std::string> permissions;
    /** @brief Database ID. */
    std::string databaseId;
    /** @brief Collection name. */
    std::string name;
    /** @brief Collection enabled. Can be 'enabled' or 'disabled'. When disabled, the collection is inaccessible to users, but remains accessible to Server SDKs using API keys. */
    bool enabled;
    /** @brief Whether document-level permissions are enabled. [Learn more about permissions](https://appwrite.io/docs/permissions). */
    bool documentSecurity;
    /** @brief Collection attributes. */
    std::vector<std::string> attributes;
    /** @brief Collection indexes. */
    std::vector<appwrite::models::Index> indexes;
    /** @brief Maximum document size in bytes. Returns 0 when no limit applies. */
    int64_t bytesMax;
    /** @brief Currently used document size in bytes based on defined attributes. */
    int64_t bytesUsed;

    /** @brief Deserialize from JSON */
    static Collection fromJson(const nlohmann::json& j) {
        Collection obj{}; // value-init: zeroes bools, ints, enums
        if (j.contains("$id")) {
            if (j["$id"].is_null()) {
                throw appwrite::DeserializationException("Required field '$id' is null");
            } else {
                obj.id = j["$id"].get<std::string>();
            }
        }
        if (j.contains("$createdAt")) {
            if (j["$createdAt"].is_null()) {
                throw appwrite::DeserializationException("Required field '$createdAt' is null");
            } else {
                obj.createdAt = j["$createdAt"].get<std::string>();
            }
        }
        if (j.contains("$updatedAt")) {
            if (j["$updatedAt"].is_null()) {
                throw appwrite::DeserializationException("Required field '$updatedAt' is null");
            } else {
                obj.updatedAt = j["$updatedAt"].get<std::string>();
            }
        }
        if (j.contains("$permissions")) {
            if (j["$permissions"].is_null()) {
                throw appwrite::DeserializationException("Required field '$permissions' is null");
            } else {
                obj.permissions = j["$permissions"].get<std::vector<std::string>>();
            }
        }
        if (j.contains("databaseId")) {
            if (j["databaseId"].is_null()) {
                throw appwrite::DeserializationException("Required field 'databaseId' is null");
            } else {
                obj.databaseId = j["databaseId"].get<std::string>();
            }
        }
        if (j.contains("name")) {
            if (j["name"].is_null()) {
                throw appwrite::DeserializationException("Required field 'name' is null");
            } else {
                obj.name = j["name"].get<std::string>();
            }
        }
        if (j.contains("enabled")) {
            if (j["enabled"].is_null()) {
                throw appwrite::DeserializationException("Required field 'enabled' is null");
            } else {
                obj.enabled = j["enabled"].get<bool>();
            }
        }
        if (j.contains("documentSecurity")) {
            if (j["documentSecurity"].is_null()) {
                throw appwrite::DeserializationException("Required field 'documentSecurity' is null");
            } else {
                obj.documentSecurity = j["documentSecurity"].get<bool>();
            }
        }
        if (j.contains("attributes")) {
            if (j["attributes"].is_null()) {
                throw appwrite::DeserializationException("Required field 'attributes' is null");
            } else {
                obj.attributes = j["attributes"].get<std::vector<std::string>>();
            }
        }
        if (j.contains("indexes")) {
            if (j["indexes"].is_null()) {
                throw appwrite::DeserializationException("Required field 'indexes' is null");
            } else {
                                            for (auto& item : j["indexes"]) {
                                    obj.indexes.push_back(Index::fromJson(item));
                                }
                        }
        }
        if (j.contains("bytesMax")) {
            if (j["bytesMax"].is_null()) {
                throw appwrite::DeserializationException("Required field 'bytesMax' is null");
            } else {
                obj.bytesMax = j["bytesMax"].get<int64_t>();
            }
        }
        if (j.contains("bytesUsed")) {
            if (j["bytesUsed"].is_null()) {
                throw appwrite::DeserializationException("Required field 'bytesUsed' is null");
            } else {
                obj.bytesUsed = j["bytesUsed"].get<int64_t>();
            }
        }
        return obj;
    }

    /** @brief Serialize to JSON */
    [[nodiscard]] nlohmann::json toJson() const {
        nlohmann::json j = nlohmann::json::object();
        {
            j["$id"] = this->id;
        }
        {
            j["$createdAt"] = this->createdAt;
        }
        {
            j["$updatedAt"] = this->updatedAt;
        }
        {
            j["$permissions"] = this->permissions;
        }
        {
            j["databaseId"] = this->databaseId;
        }
        {
            j["name"] = this->name;
        }
        {
            j["enabled"] = this->enabled;
        }
        {
            j["documentSecurity"] = this->documentSecurity;
        }
        {
            j["attributes"] = this->attributes;
        }
        {
                nlohmann::json arr = nlohmann::json::array();
            for (auto& item : this->indexes) arr.push_back(item.toJson());
            j["indexes"] = arr;
            }
        {
            j["bytesMax"] = this->bytesMax;
        }
        {
            j["bytesUsed"] = this->bytesUsed;
        }
        return j;
    }
};

/** @brief ADL hook for nlohmann::json */
inline void from_json(const nlohmann::json& j, Collection& x) {
    x = Collection::fromJson(j);
}

inline void to_json(nlohmann::json& j, const Collection& x) {
    j = x.toJson();
}

/**
 * @brief Collections List
 */
struct CollectionList {
    /** @brief Total number of collections that matched your query. */
    int64_t total;
    /** @brief List of collections. */
    std::vector<appwrite::models::Collection> collections;

    /** @brief Deserialize from JSON */
    static CollectionList fromJson(const nlohmann::json& j) {
        CollectionList obj{}; // value-init: zeroes bools, ints, enums
        if (j.contains("total")) {
            if (j["total"].is_null()) {
                throw appwrite::DeserializationException("Required field 'total' is null");
            } else {
                obj.total = j["total"].get<int64_t>();
            }
        }
        if (j.contains("collections")) {
            if (j["collections"].is_null()) {
                throw appwrite::DeserializationException("Required field 'collections' is null");
            } else {
                                            for (auto& item : j["collections"]) {
                                    obj.collections.push_back(Collection::fromJson(item));
                                }
                        }
        }
        return obj;
    }

    /** @brief Serialize to JSON */
    [[nodiscard]] nlohmann::json toJson() const {
        nlohmann::json j = nlohmann::json::object();
        {
            j["total"] = this->total;
        }
        {
                nlohmann::json arr = nlohmann::json::array();
            for (auto& item : this->collections) arr.push_back(item.toJson());
            j["collections"] = arr;
            }
        return j;
    }
};

/** @brief ADL hook for nlohmann::json */
inline void from_json(const nlohmann::json& j, CollectionList& x) {
    x = CollectionList::fromJson(j);
}

inline void to_json(nlohmann::json& j, const CollectionList& x) {
    j = x.toJson();
}

/**
 * @brief Database
 */
struct Database {
    /** @brief Database ID. */
    std::string id;
    /** @brief Database name. */
    std::string name;
    /** @brief Database creation date in ISO 8601 format. */
    std::string createdAt;
    /** @brief Database update date in ISO 8601 format. */
    std::string updatedAt;
    /** @brief If database is enabled. Can be 'enabled' or 'disabled'. When disabled, the database is inaccessible to users, but remains accessible to Server SDKs using API keys. */
    bool enabled;
    /** @brief Database type. */
    appwrite::enums::DatabaseType type;
    /** @brief Database backup policies. */
    std::vector<appwrite::models::Index> policies;
    /** @brief Database backup archives. */
    std::vector<appwrite::models::Collection> archives;

    /** @brief Deserialize from JSON */
    static Database fromJson(const nlohmann::json& j) {
        Database obj{}; // value-init: zeroes bools, ints, enums
        if (j.contains("$id")) {
            if (j["$id"].is_null()) {
                throw appwrite::DeserializationException("Required field '$id' is null");
            } else {
                obj.id = j["$id"].get<std::string>();
            }
        }
        if (j.contains("name")) {
            if (j["name"].is_null()) {
                throw appwrite::DeserializationException("Required field 'name' is null");
            } else {
                obj.name = j["name"].get<std::string>();
            }
        }
        if (j.contains("$createdAt")) {
            if (j["$createdAt"].is_null()) {
                throw appwrite::DeserializationException("Required field '$createdAt' is null");
            } else {
                obj.createdAt = j["$createdAt"].get<std::string>();
            }
        }
        if (j.contains("$updatedAt")) {
            if (j["$updatedAt"].is_null()) {
                throw appwrite::DeserializationException("Required field '$updatedAt' is null");
            } else {
                obj.updatedAt = j["$updatedAt"].get<std::string>();
            }
        }
        if (j.contains("enabled")) {
            if (j["enabled"].is_null()) {
                throw appwrite::DeserializationException("Required field 'enabled' is null");
            } else {
                obj.enabled = j["enabled"].get<bool>();
            }
        }
        if (j.contains("type")) {
            if (j["type"].is_null()) {
                throw appwrite::DeserializationException("Required field 'type' is null");
            } else {
                obj.type = j["type"].get<appwrite::enums::DatabaseType>();
            }
        }
        if (j.contains("policies")) {
            if (j["policies"].is_null()) {
                throw appwrite::DeserializationException("Required field 'policies' is null");
            } else {
                                            for (auto& item : j["policies"]) {
                                    obj.policies.push_back(Index::fromJson(item));
                                }
                        }
        }
        if (j.contains("archives")) {
            if (j["archives"].is_null()) {
                throw appwrite::DeserializationException("Required field 'archives' is null");
            } else {
                                            for (auto& item : j["archives"]) {
                                    obj.archives.push_back(Collection::fromJson(item));
                                }
                        }
        }
        return obj;
    }

    /** @brief Serialize to JSON */
    [[nodiscard]] nlohmann::json toJson() const {
        nlohmann::json j = nlohmann::json::object();
        {
            j["$id"] = this->id;
        }
        {
            j["name"] = this->name;
        }
        {
            j["$createdAt"] = this->createdAt;
        }
        {
            j["$updatedAt"] = this->updatedAt;
        }
        {
            j["enabled"] = this->enabled;
        }
        {
            j["type"] = appwrite::enums::toString(this->type);
        }
        {
                nlohmann::json arr = nlohmann::json::array();
            for (auto& item : this->policies) arr.push_back(item.toJson());
            j["policies"] = arr;
            }
        {
                nlohmann::json arr = nlohmann::json::array();
            for (auto& item : this->archives) arr.push_back(item.toJson());
            j["archives"] = arr;
            }
        return j;
    }
};

/** @brief ADL hook for nlohmann::json */
inline void from_json(const nlohmann::json& j, Database& x) {
    x = Database::fromJson(j);
}

inline void to_json(nlohmann::json& j, const Database& x) {
    j = x.toJson();
}

/**
 * @brief Databases List
 */
struct DatabaseList {
    /** @brief Total number of databases that matched your query. */
    int64_t total;
    /** @brief List of databases. */
    std::vector<appwrite::models::Database> databases;

    /** @brief Deserialize from JSON */
    static DatabaseList fromJson(const nlohmann::json& j) {
        DatabaseList obj{}; // value-init: zeroes bools, ints, enums
        if (j.contains("total")) {
            if (j["total"].is_null()) {
                throw appwrite::DeserializationException("Required field 'total' is null");
            } else {
                obj.total = j["total"].get<int64_t>();
            }
        }
        if (j.contains("databases")) {
            if (j["databases"].is_null()) {
                throw appwrite::DeserializationException("Required field 'databases' is null");
            } else {
                                            for (auto& item : j["databases"]) {
                                    obj.databases.push_back(Database::fromJson(item));
                                }
                        }
        }
        return obj;
    }

    /** @brief Serialize to JSON */
    [[nodiscard]] nlohmann::json toJson() const {
        nlohmann::json j = nlohmann::json::object();
        {
            j["total"] = this->total;
        }
        {
                nlohmann::json arr = nlohmann::json::array();
            for (auto& item : this->databases) arr.push_back(item.toJson());
            j["databases"] = arr;
            }
        return j;
    }
};

/** @brief ADL hook for nlohmann::json */
inline void from_json(const nlohmann::json& j, DatabaseList& x) {
    x = DatabaseList::fromJson(j);
}

inline void to_json(nlohmann::json& j, const DatabaseList& x) {
    j = x.toJson();
}

/**
 * @brief Indexes List
 */
struct IndexList {
    /** @brief Total number of indexes that matched your query. */
    int64_t total;
    /** @brief List of indexes. */
    std::vector<appwrite::models::Index> indexes;

    /** @brief Deserialize from JSON */
    static IndexList fromJson(const nlohmann::json& j) {
        IndexList obj{}; // value-init: zeroes bools, ints, enums
        if (j.contains("total")) {
            if (j["total"].is_null()) {
                throw appwrite::DeserializationException("Required field 'total' is null");
            } else {
                obj.total = j["total"].get<int64_t>();
            }
        }
        if (j.contains("indexes")) {
            if (j["indexes"].is_null()) {
                throw appwrite::DeserializationException("Required field 'indexes' is null");
            } else {
                                            for (auto& item : j["indexes"]) {
                                    obj.indexes.push_back(Index::fromJson(item));
                                }
                        }
        }
        return obj;
    }

    /** @brief Serialize to JSON */
    [[nodiscard]] nlohmann::json toJson() const {
        nlohmann::json j = nlohmann::json::object();
        {
            j["total"] = this->total;
        }
        {
                nlohmann::json arr = nlohmann::json::array();
            for (auto& item : this->indexes) arr.push_back(item.toJson());
            j["indexes"] = arr;
            }
        return j;
    }
};

/** @brief ADL hook for nlohmann::json */
inline void from_json(const nlohmann::json& j, IndexList& x) {
    x = IndexList::fromJson(j);
}

inline void to_json(nlohmann::json& j, const IndexList& x) {
    j = x.toJson();
}

/**
 * @brief Column Indexes List
 */
struct ColumnIndexList {
    /** @brief Total number of indexes that matched your query. */
    int64_t total;
    /** @brief List of indexes. */
    std::vector<appwrite::models::ColumnIndex> indexes;

    /** @brief Deserialize from JSON */
    static ColumnIndexList fromJson(const nlohmann::json& j) {
        ColumnIndexList obj{}; // value-init: zeroes bools, ints, enums
        if (j.contains("total")) {
            if (j["total"].is_null()) {
                throw appwrite::DeserializationException("Required field 'total' is null");
            } else {
                obj.total = j["total"].get<int64_t>();
            }
        }
        if (j.contains("indexes")) {
            if (j["indexes"].is_null()) {
                throw appwrite::DeserializationException("Required field 'indexes' is null");
            } else {
                                            for (auto& item : j["indexes"]) {
                                    obj.indexes.push_back(ColumnIndex::fromJson(item));
                                }
                        }
        }
        return obj;
    }

    /** @brief Serialize to JSON */
    [[nodiscard]] nlohmann::json toJson() const {
        nlohmann::json j = nlohmann::json::object();
        {
            j["total"] = this->total;
        }
        {
                nlohmann::json arr = nlohmann::json::array();
            for (auto& item : this->indexes) arr.push_back(item.toJson());
            j["indexes"] = arr;
            }
        return j;
    }
};

/** @brief ADL hook for nlohmann::json */
inline void from_json(const nlohmann::json& j, ColumnIndexList& x) {
    x = ColumnIndexList::fromJson(j);
}

inline void to_json(nlohmann::json& j, const ColumnIndexList& x) {
    j = x.toJson();
}

/**
 * @brief Preferences
 */
struct Preferences {
    nlohmann::json data = nlohmann::json::object();

    /** @brief Deserialize from JSON */
    static Preferences fromJson(const nlohmann::json& j) {
        Preferences obj;
        obj.data = j;
        return obj;
    }

    /** @brief Serialize to JSON */
    [[nodiscard]] nlohmann::json toJson() const {
        return data;
    }
};

/** @brief ADL hook for nlohmann::json */
inline void from_json(const nlohmann::json& j, Preferences& x) {
    x = Preferences::fromJson(j);
}

inline void to_json(nlohmann::json& j, const Preferences& x) {
    j = x.toJson();
}

/**
 * @brief Target
 */
struct Target {
    /** @brief Target ID. */
    std::string id;
    /** @brief Target creation time in ISO 8601 format. */
    std::string createdAt;
    /** @brief Target update date in ISO 8601 format. */
    std::string updatedAt;
    /** @brief Target Name. */
    std::string name;
    /** @brief User ID. */
    std::string userId;
    /** @brief Provider ID. */
    std::optional<std::string> providerId = std::nullopt;
    /** @brief The target provider type. Can be one of the following: `email`, `sms` or `push`. */
    std::string providerType;
    /** @brief The target identifier. */
    std::string identifier;
    /** @brief Is the target expired. */
    bool expired;

    /** @brief Deserialize from JSON */
    static Target fromJson(const nlohmann::json& j) {
        Target obj{}; // value-init: zeroes bools, ints, enums
        if (j.contains("$id")) {
            if (j["$id"].is_null()) {
                throw appwrite::DeserializationException("Required field '$id' is null");
            } else {
                obj.id = j["$id"].get<std::string>();
            }
        }
        if (j.contains("$createdAt")) {
            if (j["$createdAt"].is_null()) {
                throw appwrite::DeserializationException("Required field '$createdAt' is null");
            } else {
                obj.createdAt = j["$createdAt"].get<std::string>();
            }
        }
        if (j.contains("$updatedAt")) {
            if (j["$updatedAt"].is_null()) {
                throw appwrite::DeserializationException("Required field '$updatedAt' is null");
            } else {
                obj.updatedAt = j["$updatedAt"].get<std::string>();
            }
        }
        if (j.contains("name")) {
            if (j["name"].is_null()) {
                throw appwrite::DeserializationException("Required field 'name' is null");
            } else {
                obj.name = j["name"].get<std::string>();
            }
        }
        if (j.contains("userId")) {
            if (j["userId"].is_null()) {
                throw appwrite::DeserializationException("Required field 'userId' is null");
            } else {
                obj.userId = j["userId"].get<std::string>();
            }
        }
        if (j.contains("providerId")) {
            if (j["providerId"].is_null()) {
                obj.providerId = std::nullopt;
            } else {
                obj.providerId = j["providerId"].get<std::optional<std::string>>();
            }
        }
        if (j.contains("providerType")) {
            if (j["providerType"].is_null()) {
                throw appwrite::DeserializationException("Required field 'providerType' is null");
            } else {
                obj.providerType = j["providerType"].get<std::string>();
            }
        }
        if (j.contains("identifier")) {
            if (j["identifier"].is_null()) {
                throw appwrite::DeserializationException("Required field 'identifier' is null");
            } else {
                obj.identifier = j["identifier"].get<std::string>();
            }
        }
        if (j.contains("expired")) {
            if (j["expired"].is_null()) {
                throw appwrite::DeserializationException("Required field 'expired' is null");
            } else {
                obj.expired = j["expired"].get<bool>();
            }
        }
        return obj;
    }

    /** @brief Serialize to JSON */
    [[nodiscard]] nlohmann::json toJson() const {
        nlohmann::json j = nlohmann::json::object();
        {
            j["$id"] = this->id;
        }
        {
            j["$createdAt"] = this->createdAt;
        }
        {
            j["$updatedAt"] = this->updatedAt;
        }
        {
            j["name"] = this->name;
        }
        {
            j["userId"] = this->userId;
        }
        if (this->providerId.has_value()) {
            j["providerId"] = this->providerId.value();
        }
        {
            j["providerType"] = this->providerType;
        }
        {
            j["identifier"] = this->identifier;
        }
        {
            j["expired"] = this->expired;
        }
        return j;
    }
};

/** @brief ADL hook for nlohmann::json */
inline void from_json(const nlohmann::json& j, Target& x) {
    x = Target::fromJson(j);
}

inline void to_json(nlohmann::json& j, const Target& x) {
    j = x.toJson();
}

/**
 * @brief User
 */
struct User {
    /** @brief User ID. */
    std::string id;
    /** @brief User creation date in ISO 8601 format. */
    std::string createdAt;
    /** @brief User update date in ISO 8601 format. */
    std::string updatedAt;
    /** @brief User name. */
    std::string name;
    /** @brief Hashed user password. */
    std::optional<std::string> password = std::nullopt;
    /** @brief Password hashing algorithm. */
    std::optional<std::string> hash = std::nullopt;
    /** @brief Password hashing algorithm configuration. */
    std::optional<nlohmann::json> hashOptions = std::nullopt;
    /** @brief User registration date in ISO 8601 format. */
    std::string registration;
    /** @brief User status. Pass `true` for enabled and `false` for disabled. */
    bool status;
    /** @brief Labels for the user. */
    std::vector<std::string> labels;
    /** @brief Password update time in ISO 8601 format. */
    std::string passwordUpdate;
    /** @brief User email address. */
    std::string email;
    /** @brief User phone number in E.164 format. */
    std::string phone;
    /** @brief Email verification status. */
    bool emailVerification;
    /** @brief Phone verification status. */
    bool phoneVerification;
    /** @brief Multi factor authentication status. */
    bool mfa;
    /** @brief User preferences as a key-value object */
    appwrite::models::Preferences prefs;
    /** @brief A user-owned message receiver. A single user may have multiple e.g. emails, phones, and a browser. Each target is registered with a single provider. */
    std::vector<appwrite::models::Target> targets;
    /** @brief Most recent access date in ISO 8601 format. This attribute is only updated again after 24 hours. */
    std::string accessedAt;
    /** @brief Whether the user can impersonate other users. */
    std::optional<bool> impersonator = std::nullopt;
    /** @brief ID of the original actor performing the impersonation. Present only when the current request is impersonating another user. Internal audit logs attribute the action to this user, while the impersonated target is recorded only in internal audit payload data. */
    std::optional<std::string> impersonatorUserId = std::nullopt;

    /** @brief Deserialize from JSON */
    static User fromJson(const nlohmann::json& j) {
        User obj{}; // value-init: zeroes bools, ints, enums
        if (j.contains("$id")) {
            if (j["$id"].is_null()) {
                throw appwrite::DeserializationException("Required field '$id' is null");
            } else {
                obj.id = j["$id"].get<std::string>();
            }
        }
        if (j.contains("$createdAt")) {
            if (j["$createdAt"].is_null()) {
                throw appwrite::DeserializationException("Required field '$createdAt' is null");
            } else {
                obj.createdAt = j["$createdAt"].get<std::string>();
            }
        }
        if (j.contains("$updatedAt")) {
            if (j["$updatedAt"].is_null()) {
                throw appwrite::DeserializationException("Required field '$updatedAt' is null");
            } else {
                obj.updatedAt = j["$updatedAt"].get<std::string>();
            }
        }
        if (j.contains("name")) {
            if (j["name"].is_null()) {
                throw appwrite::DeserializationException("Required field 'name' is null");
            } else {
                obj.name = j["name"].get<std::string>();
            }
        }
        if (j.contains("password")) {
            if (j["password"].is_null()) {
                obj.password = std::nullopt;
            } else {
                obj.password = j["password"].get<std::optional<std::string>>();
            }
        }
        if (j.contains("hash")) {
            if (j["hash"].is_null()) {
                obj.hash = std::nullopt;
            } else {
                obj.hash = j["hash"].get<std::optional<std::string>>();
            }
        }
        if (j.contains("hashOptions")) {
            if (j["hashOptions"].is_null()) {
                obj.hashOptions = std::nullopt;
            } else {
                obj.hashOptions = j["hashOptions"].get<std::optional<nlohmann::json>>();
            }
        }
        if (j.contains("registration")) {
            if (j["registration"].is_null()) {
                throw appwrite::DeserializationException("Required field 'registration' is null");
            } else {
                obj.registration = j["registration"].get<std::string>();
            }
        }
        if (j.contains("status")) {
            if (j["status"].is_null()) {
                throw appwrite::DeserializationException("Required field 'status' is null");
            } else {
                obj.status = j["status"].get<bool>();
            }
        }
        if (j.contains("labels")) {
            if (j["labels"].is_null()) {
                throw appwrite::DeserializationException("Required field 'labels' is null");
            } else {
                obj.labels = j["labels"].get<std::vector<std::string>>();
            }
        }
        if (j.contains("passwordUpdate")) {
            if (j["passwordUpdate"].is_null()) {
                throw appwrite::DeserializationException("Required field 'passwordUpdate' is null");
            } else {
                obj.passwordUpdate = j["passwordUpdate"].get<std::string>();
            }
        }
        if (j.contains("email")) {
            if (j["email"].is_null()) {
                throw appwrite::DeserializationException("Required field 'email' is null");
            } else {
                obj.email = j["email"].get<std::string>();
            }
        }
        if (j.contains("phone")) {
            if (j["phone"].is_null()) {
                throw appwrite::DeserializationException("Required field 'phone' is null");
            } else {
                obj.phone = j["phone"].get<std::string>();
            }
        }
        if (j.contains("emailVerification")) {
            if (j["emailVerification"].is_null()) {
                throw appwrite::DeserializationException("Required field 'emailVerification' is null");
            } else {
                obj.emailVerification = j["emailVerification"].get<bool>();
            }
        }
        if (j.contains("phoneVerification")) {
            if (j["phoneVerification"].is_null()) {
                throw appwrite::DeserializationException("Required field 'phoneVerification' is null");
            } else {
                obj.phoneVerification = j["phoneVerification"].get<bool>();
            }
        }
        if (j.contains("mfa")) {
            if (j["mfa"].is_null()) {
                throw appwrite::DeserializationException("Required field 'mfa' is null");
            } else {
                obj.mfa = j["mfa"].get<bool>();
            }
        }
        if (j.contains("prefs")) {
            if (j["prefs"].is_null()) {
                throw appwrite::DeserializationException("Required field 'prefs' is null");
            } else {
                            obj.prefs = Preferences::fromJson(j["prefs"]);
                        }
        }
        if (j.contains("targets")) {
            if (j["targets"].is_null()) {
                throw appwrite::DeserializationException("Required field 'targets' is null");
            } else {
                                            for (auto& item : j["targets"]) {
                                    obj.targets.push_back(Target::fromJson(item));
                                }
                        }
        }
        if (j.contains("accessedAt")) {
            if (j["accessedAt"].is_null()) {
                throw appwrite::DeserializationException("Required field 'accessedAt' is null");
            } else {
                obj.accessedAt = j["accessedAt"].get<std::string>();
            }
        }
        if (j.contains("impersonator")) {
            if (j["impersonator"].is_null()) {
                obj.impersonator = std::nullopt;
            } else {
                obj.impersonator = j["impersonator"].get<std::optional<bool>>();
            }
        }
        if (j.contains("impersonatorUserId")) {
            if (j["impersonatorUserId"].is_null()) {
                obj.impersonatorUserId = std::nullopt;
            } else {
                obj.impersonatorUserId = j["impersonatorUserId"].get<std::optional<std::string>>();
            }
        }
        return obj;
    }

    /** @brief Serialize to JSON */
    [[nodiscard]] nlohmann::json toJson() const {
        nlohmann::json j = nlohmann::json::object();
        {
            j["$id"] = this->id;
        }
        {
            j["$createdAt"] = this->createdAt;
        }
        {
            j["$updatedAt"] = this->updatedAt;
        }
        {
            j["name"] = this->name;
        }
        if (this->password.has_value()) {
            j["password"] = this->password.value();
        }
        if (this->hash.has_value()) {
            j["hash"] = this->hash.value();
        }
        if (this->hashOptions.has_value()) {
            j["hashOptions"] = this->hashOptions.value();
        }
        {
            j["registration"] = this->registration;
        }
        {
            j["status"] = this->status;
        }
        {
            j["labels"] = this->labels;
        }
        {
            j["passwordUpdate"] = this->passwordUpdate;
        }
        {
            j["email"] = this->email;
        }
        {
            j["phone"] = this->phone;
        }
        {
            j["emailVerification"] = this->emailVerification;
        }
        {
            j["phoneVerification"] = this->phoneVerification;
        }
        {
            j["mfa"] = this->mfa;
        }
        {
                j["prefs"] = this->prefs.toJson();
            }
        {
                nlohmann::json arr = nlohmann::json::array();
            for (auto& item : this->targets) arr.push_back(item.toJson());
            j["targets"] = arr;
            }
        {
            j["accessedAt"] = this->accessedAt;
        }
        if (this->impersonator.has_value()) {
            j["impersonator"] = this->impersonator.value();
        }
        if (this->impersonatorUserId.has_value()) {
            j["impersonatorUserId"] = this->impersonatorUserId.value();
        }
        return j;
    }
};

/** @brief ADL hook for nlohmann::json */
inline void from_json(const nlohmann::json& j, User& x) {
    x = User::fromJson(j);
}

inline void to_json(nlohmann::json& j, const User& x) {
    j = x.toJson();
}

/**
 * @brief Users List
 */
struct UserList {
    /** @brief Total number of users that matched your query. */
    int64_t total;
    /** @brief List of users. */
    std::vector<appwrite::models::User> users;

    /** @brief Deserialize from JSON */
    static UserList fromJson(const nlohmann::json& j) {
        UserList obj{}; // value-init: zeroes bools, ints, enums
        if (j.contains("total")) {
            if (j["total"].is_null()) {
                throw appwrite::DeserializationException("Required field 'total' is null");
            } else {
                obj.total = j["total"].get<int64_t>();
            }
        }
        if (j.contains("users")) {
            if (j["users"].is_null()) {
                throw appwrite::DeserializationException("Required field 'users' is null");
            } else {
                                            for (auto& item : j["users"]) {
                                    obj.users.push_back(User::fromJson(item));
                                }
                        }
        }
        return obj;
    }

    /** @brief Serialize to JSON */
    [[nodiscard]] nlohmann::json toJson() const {
        nlohmann::json j = nlohmann::json::object();
        {
            j["total"] = this->total;
        }
        {
                nlohmann::json arr = nlohmann::json::array();
            for (auto& item : this->users) arr.push_back(item.toJson());
            j["users"] = arr;
            }
        return j;
    }
};

/** @brief ADL hook for nlohmann::json */
inline void from_json(const nlohmann::json& j, UserList& x) {
    x = UserList::fromJson(j);
}

inline void to_json(nlohmann::json& j, const UserList& x) {
    j = x.toJson();
}

/**
 * @brief Session
 */
struct Session {
    /** @brief Session ID. */
    std::string id;
    /** @brief Session creation date in ISO 8601 format. */
    std::string createdAt;
    /** @brief Session update date in ISO 8601 format. */
    std::string updatedAt;
    /** @brief User ID. */
    std::string userId;
    /** @brief Session expiration date in ISO 8601 format. */
    std::string expire;
    /** @brief Session Provider. */
    std::string provider;
    /** @brief Session Provider User ID. */
    std::string providerUid;
    /** @brief Session Provider Access Token. */
    std::string providerAccessToken;
    /** @brief The date of when the access token expires in ISO 8601 format. */
    std::string providerAccessTokenExpiry;
    /** @brief Session Provider Refresh Token. */
    std::string providerRefreshToken;
    /** @brief IP in use when the session was created. */
    std::string ip;
    /** @brief Operating system code name. View list of [available options](https://github.com/appwrite/appwrite/blob/master/docs/lists/os.json). */
    std::string osCode;
    /** @brief Operating system name. */
    std::string osName;
    /** @brief Operating system version. */
    std::string osVersion;
    /** @brief Client type. */
    std::string clientType;
    /** @brief Client code name. View list of [available options](https://github.com/appwrite/appwrite/blob/master/docs/lists/clients.json). */
    std::string clientCode;
    /** @brief Client name. */
    std::string clientName;
    /** @brief Client version. */
    std::string clientVersion;
    /** @brief Client engine name. */
    std::string clientEngine;
    /** @brief Client engine name. */
    std::string clientEngineVersion;
    /** @brief Device name. */
    std::string deviceName;
    /** @brief Device brand name. */
    std::string deviceBrand;
    /** @brief Device model name. */
    std::string deviceModel;
    /** @brief Country two-character ISO 3166-1 alpha code. */
    std::string countryCode;
    /** @brief Country name. */
    std::string countryName;
    /** @brief Returns true if this the current user session. */
    bool current;
    /** @brief Returns a list of active session factors. */
    std::vector<std::string> factors;
    /** @brief Secret used to authenticate the user. Only included if the request was made with an API key */
    std::string secret;
    /** @brief Most recent date in ISO 8601 format when the session successfully passed MFA challenge. */
    std::string mfaUpdatedAt;

    /** @brief Deserialize from JSON */
    static Session fromJson(const nlohmann::json& j) {
        Session obj{}; // value-init: zeroes bools, ints, enums
        if (j.contains("$id")) {
            if (j["$id"].is_null()) {
                throw appwrite::DeserializationException("Required field '$id' is null");
            } else {
                obj.id = j["$id"].get<std::string>();
            }
        }
        if (j.contains("$createdAt")) {
            if (j["$createdAt"].is_null()) {
                throw appwrite::DeserializationException("Required field '$createdAt' is null");
            } else {
                obj.createdAt = j["$createdAt"].get<std::string>();
            }
        }
        if (j.contains("$updatedAt")) {
            if (j["$updatedAt"].is_null()) {
                throw appwrite::DeserializationException("Required field '$updatedAt' is null");
            } else {
                obj.updatedAt = j["$updatedAt"].get<std::string>();
            }
        }
        if (j.contains("userId")) {
            if (j["userId"].is_null()) {
                throw appwrite::DeserializationException("Required field 'userId' is null");
            } else {
                obj.userId = j["userId"].get<std::string>();
            }
        }
        if (j.contains("expire")) {
            if (j["expire"].is_null()) {
                throw appwrite::DeserializationException("Required field 'expire' is null");
            } else {
                obj.expire = j["expire"].get<std::string>();
            }
        }
        if (j.contains("provider")) {
            if (j["provider"].is_null()) {
                throw appwrite::DeserializationException("Required field 'provider' is null");
            } else {
                obj.provider = j["provider"].get<std::string>();
            }
        }
        if (j.contains("providerUid")) {
            if (j["providerUid"].is_null()) {
                throw appwrite::DeserializationException("Required field 'providerUid' is null");
            } else {
                obj.providerUid = j["providerUid"].get<std::string>();
            }
        }
        if (j.contains("providerAccessToken")) {
            if (j["providerAccessToken"].is_null()) {
                throw appwrite::DeserializationException("Required field 'providerAccessToken' is null");
            } else {
                obj.providerAccessToken = j["providerAccessToken"].get<std::string>();
            }
        }
        if (j.contains("providerAccessTokenExpiry")) {
            if (j["providerAccessTokenExpiry"].is_null()) {
                throw appwrite::DeserializationException("Required field 'providerAccessTokenExpiry' is null");
            } else {
                obj.providerAccessTokenExpiry = j["providerAccessTokenExpiry"].get<std::string>();
            }
        }
        if (j.contains("providerRefreshToken")) {
            if (j["providerRefreshToken"].is_null()) {
                throw appwrite::DeserializationException("Required field 'providerRefreshToken' is null");
            } else {
                obj.providerRefreshToken = j["providerRefreshToken"].get<std::string>();
            }
        }
        if (j.contains("ip")) {
            if (j["ip"].is_null()) {
                throw appwrite::DeserializationException("Required field 'ip' is null");
            } else {
                obj.ip = j["ip"].get<std::string>();
            }
        }
        if (j.contains("osCode")) {
            if (j["osCode"].is_null()) {
                throw appwrite::DeserializationException("Required field 'osCode' is null");
            } else {
                obj.osCode = j["osCode"].get<std::string>();
            }
        }
        if (j.contains("osName")) {
            if (j["osName"].is_null()) {
                throw appwrite::DeserializationException("Required field 'osName' is null");
            } else {
                obj.osName = j["osName"].get<std::string>();
            }
        }
        if (j.contains("osVersion")) {
            if (j["osVersion"].is_null()) {
                throw appwrite::DeserializationException("Required field 'osVersion' is null");
            } else {
                obj.osVersion = j["osVersion"].get<std::string>();
            }
        }
        if (j.contains("clientType")) {
            if (j["clientType"].is_null()) {
                throw appwrite::DeserializationException("Required field 'clientType' is null");
            } else {
                obj.clientType = j["clientType"].get<std::string>();
            }
        }
        if (j.contains("clientCode")) {
            if (j["clientCode"].is_null()) {
                throw appwrite::DeserializationException("Required field 'clientCode' is null");
            } else {
                obj.clientCode = j["clientCode"].get<std::string>();
            }
        }
        if (j.contains("clientName")) {
            if (j["clientName"].is_null()) {
                throw appwrite::DeserializationException("Required field 'clientName' is null");
            } else {
                obj.clientName = j["clientName"].get<std::string>();
            }
        }
        if (j.contains("clientVersion")) {
            if (j["clientVersion"].is_null()) {
                throw appwrite::DeserializationException("Required field 'clientVersion' is null");
            } else {
                obj.clientVersion = j["clientVersion"].get<std::string>();
            }
        }
        if (j.contains("clientEngine")) {
            if (j["clientEngine"].is_null()) {
                throw appwrite::DeserializationException("Required field 'clientEngine' is null");
            } else {
                obj.clientEngine = j["clientEngine"].get<std::string>();
            }
        }
        if (j.contains("clientEngineVersion")) {
            if (j["clientEngineVersion"].is_null()) {
                throw appwrite::DeserializationException("Required field 'clientEngineVersion' is null");
            } else {
                obj.clientEngineVersion = j["clientEngineVersion"].get<std::string>();
            }
        }
        if (j.contains("deviceName")) {
            if (j["deviceName"].is_null()) {
                throw appwrite::DeserializationException("Required field 'deviceName' is null");
            } else {
                obj.deviceName = j["deviceName"].get<std::string>();
            }
        }
        if (j.contains("deviceBrand")) {
            if (j["deviceBrand"].is_null()) {
                throw appwrite::DeserializationException("Required field 'deviceBrand' is null");
            } else {
                obj.deviceBrand = j["deviceBrand"].get<std::string>();
            }
        }
        if (j.contains("deviceModel")) {
            if (j["deviceModel"].is_null()) {
                throw appwrite::DeserializationException("Required field 'deviceModel' is null");
            } else {
                obj.deviceModel = j["deviceModel"].get<std::string>();
            }
        }
        if (j.contains("countryCode")) {
            if (j["countryCode"].is_null()) {
                throw appwrite::DeserializationException("Required field 'countryCode' is null");
            } else {
                obj.countryCode = j["countryCode"].get<std::string>();
            }
        }
        if (j.contains("countryName")) {
            if (j["countryName"].is_null()) {
                throw appwrite::DeserializationException("Required field 'countryName' is null");
            } else {
                obj.countryName = j["countryName"].get<std::string>();
            }
        }
        if (j.contains("current")) {
            if (j["current"].is_null()) {
                throw appwrite::DeserializationException("Required field 'current' is null");
            } else {
                obj.current = j["current"].get<bool>();
            }
        }
        if (j.contains("factors")) {
            if (j["factors"].is_null()) {
                throw appwrite::DeserializationException("Required field 'factors' is null");
            } else {
                obj.factors = j["factors"].get<std::vector<std::string>>();
            }
        }
        if (j.contains("secret")) {
            if (j["secret"].is_null()) {
                throw appwrite::DeserializationException("Required field 'secret' is null");
            } else {
                obj.secret = j["secret"].get<std::string>();
            }
        }
        if (j.contains("mfaUpdatedAt")) {
            if (j["mfaUpdatedAt"].is_null()) {
                throw appwrite::DeserializationException("Required field 'mfaUpdatedAt' is null");
            } else {
                obj.mfaUpdatedAt = j["mfaUpdatedAt"].get<std::string>();
            }
        }
        return obj;
    }

    /** @brief Serialize to JSON */
    [[nodiscard]] nlohmann::json toJson() const {
        nlohmann::json j = nlohmann::json::object();
        {
            j["$id"] = this->id;
        }
        {
            j["$createdAt"] = this->createdAt;
        }
        {
            j["$updatedAt"] = this->updatedAt;
        }
        {
            j["userId"] = this->userId;
        }
        {
            j["expire"] = this->expire;
        }
        {
            j["provider"] = this->provider;
        }
        {
            j["providerUid"] = this->providerUid;
        }
        {
            j["providerAccessToken"] = this->providerAccessToken;
        }
        {
            j["providerAccessTokenExpiry"] = this->providerAccessTokenExpiry;
        }
        {
            j["providerRefreshToken"] = this->providerRefreshToken;
        }
        {
            j["ip"] = this->ip;
        }
        {
            j["osCode"] = this->osCode;
        }
        {
            j["osName"] = this->osName;
        }
        {
            j["osVersion"] = this->osVersion;
        }
        {
            j["clientType"] = this->clientType;
        }
        {
            j["clientCode"] = this->clientCode;
        }
        {
            j["clientName"] = this->clientName;
        }
        {
            j["clientVersion"] = this->clientVersion;
        }
        {
            j["clientEngine"] = this->clientEngine;
        }
        {
            j["clientEngineVersion"] = this->clientEngineVersion;
        }
        {
            j["deviceName"] = this->deviceName;
        }
        {
            j["deviceBrand"] = this->deviceBrand;
        }
        {
            j["deviceModel"] = this->deviceModel;
        }
        {
            j["countryCode"] = this->countryCode;
        }
        {
            j["countryName"] = this->countryName;
        }
        {
            j["current"] = this->current;
        }
        {
            j["factors"] = this->factors;
        }
        {
            j["secret"] = this->secret;
        }
        {
            j["mfaUpdatedAt"] = this->mfaUpdatedAt;
        }
        return j;
    }
};

/** @brief ADL hook for nlohmann::json */
inline void from_json(const nlohmann::json& j, Session& x) {
    x = Session::fromJson(j);
}

inline void to_json(nlohmann::json& j, const Session& x) {
    j = x.toJson();
}

/**
 * @brief Sessions List
 */
struct SessionList {
    /** @brief Total number of sessions that matched your query. */
    int64_t total;
    /** @brief List of sessions. */
    std::vector<appwrite::models::Session> sessions;

    /** @brief Deserialize from JSON */
    static SessionList fromJson(const nlohmann::json& j) {
        SessionList obj{}; // value-init: zeroes bools, ints, enums
        if (j.contains("total")) {
            if (j["total"].is_null()) {
                throw appwrite::DeserializationException("Required field 'total' is null");
            } else {
                obj.total = j["total"].get<int64_t>();
            }
        }
        if (j.contains("sessions")) {
            if (j["sessions"].is_null()) {
                throw appwrite::DeserializationException("Required field 'sessions' is null");
            } else {
                                            for (auto& item : j["sessions"]) {
                                    obj.sessions.push_back(Session::fromJson(item));
                                }
                        }
        }
        return obj;
    }

    /** @brief Serialize to JSON */
    [[nodiscard]] nlohmann::json toJson() const {
        nlohmann::json j = nlohmann::json::object();
        {
            j["total"] = this->total;
        }
        {
                nlohmann::json arr = nlohmann::json::array();
            for (auto& item : this->sessions) arr.push_back(item.toJson());
            j["sessions"] = arr;
            }
        return j;
    }
};

/** @brief ADL hook for nlohmann::json */
inline void from_json(const nlohmann::json& j, SessionList& x) {
    x = SessionList::fromJson(j);
}

inline void to_json(nlohmann::json& j, const SessionList& x) {
    j = x.toJson();
}

/**
 * @brief Identity
 */
struct Identity {
    /** @brief Identity ID. */
    std::string id;
    /** @brief Identity creation date in ISO 8601 format. */
    std::string createdAt;
    /** @brief Identity update date in ISO 8601 format. */
    std::string updatedAt;
    /** @brief User ID. */
    std::string userId;
    /** @brief Identity Provider. */
    std::string provider;
    /** @brief ID of the User in the Identity Provider. */
    std::string providerUid;
    /** @brief Email of the User in the Identity Provider. */
    std::string providerEmail;
    /** @brief Identity Provider Access Token. */
    std::string providerAccessToken;
    /** @brief The date of when the access token expires in ISO 8601 format. */
    std::string providerAccessTokenExpiry;
    /** @brief Identity Provider Refresh Token. */
    std::string providerRefreshToken;

    /** @brief Deserialize from JSON */
    static Identity fromJson(const nlohmann::json& j) {
        Identity obj{}; // value-init: zeroes bools, ints, enums
        if (j.contains("$id")) {
            if (j["$id"].is_null()) {
                throw appwrite::DeserializationException("Required field '$id' is null");
            } else {
                obj.id = j["$id"].get<std::string>();
            }
        }
        if (j.contains("$createdAt")) {
            if (j["$createdAt"].is_null()) {
                throw appwrite::DeserializationException("Required field '$createdAt' is null");
            } else {
                obj.createdAt = j["$createdAt"].get<std::string>();
            }
        }
        if (j.contains("$updatedAt")) {
            if (j["$updatedAt"].is_null()) {
                throw appwrite::DeserializationException("Required field '$updatedAt' is null");
            } else {
                obj.updatedAt = j["$updatedAt"].get<std::string>();
            }
        }
        if (j.contains("userId")) {
            if (j["userId"].is_null()) {
                throw appwrite::DeserializationException("Required field 'userId' is null");
            } else {
                obj.userId = j["userId"].get<std::string>();
            }
        }
        if (j.contains("provider")) {
            if (j["provider"].is_null()) {
                throw appwrite::DeserializationException("Required field 'provider' is null");
            } else {
                obj.provider = j["provider"].get<std::string>();
            }
        }
        if (j.contains("providerUid")) {
            if (j["providerUid"].is_null()) {
                throw appwrite::DeserializationException("Required field 'providerUid' is null");
            } else {
                obj.providerUid = j["providerUid"].get<std::string>();
            }
        }
        if (j.contains("providerEmail")) {
            if (j["providerEmail"].is_null()) {
                throw appwrite::DeserializationException("Required field 'providerEmail' is null");
            } else {
                obj.providerEmail = j["providerEmail"].get<std::string>();
            }
        }
        if (j.contains("providerAccessToken")) {
            if (j["providerAccessToken"].is_null()) {
                throw appwrite::DeserializationException("Required field 'providerAccessToken' is null");
            } else {
                obj.providerAccessToken = j["providerAccessToken"].get<std::string>();
            }
        }
        if (j.contains("providerAccessTokenExpiry")) {
            if (j["providerAccessTokenExpiry"].is_null()) {
                throw appwrite::DeserializationException("Required field 'providerAccessTokenExpiry' is null");
            } else {
                obj.providerAccessTokenExpiry = j["providerAccessTokenExpiry"].get<std::string>();
            }
        }
        if (j.contains("providerRefreshToken")) {
            if (j["providerRefreshToken"].is_null()) {
                throw appwrite::DeserializationException("Required field 'providerRefreshToken' is null");
            } else {
                obj.providerRefreshToken = j["providerRefreshToken"].get<std::string>();
            }
        }
        return obj;
    }

    /** @brief Serialize to JSON */
    [[nodiscard]] nlohmann::json toJson() const {
        nlohmann::json j = nlohmann::json::object();
        {
            j["$id"] = this->id;
        }
        {
            j["$createdAt"] = this->createdAt;
        }
        {
            j["$updatedAt"] = this->updatedAt;
        }
        {
            j["userId"] = this->userId;
        }
        {
            j["provider"] = this->provider;
        }
        {
            j["providerUid"] = this->providerUid;
        }
        {
            j["providerEmail"] = this->providerEmail;
        }
        {
            j["providerAccessToken"] = this->providerAccessToken;
        }
        {
            j["providerAccessTokenExpiry"] = this->providerAccessTokenExpiry;
        }
        {
            j["providerRefreshToken"] = this->providerRefreshToken;
        }
        return j;
    }
};

/** @brief ADL hook for nlohmann::json */
inline void from_json(const nlohmann::json& j, Identity& x) {
    x = Identity::fromJson(j);
}

inline void to_json(nlohmann::json& j, const Identity& x) {
    j = x.toJson();
}

/**
 * @brief Identities List
 */
struct IdentityList {
    /** @brief Total number of identities that matched your query. */
    int64_t total;
    /** @brief List of identities. */
    std::vector<appwrite::models::Identity> identities;

    /** @brief Deserialize from JSON */
    static IdentityList fromJson(const nlohmann::json& j) {
        IdentityList obj{}; // value-init: zeroes bools, ints, enums
        if (j.contains("total")) {
            if (j["total"].is_null()) {
                throw appwrite::DeserializationException("Required field 'total' is null");
            } else {
                obj.total = j["total"].get<int64_t>();
            }
        }
        if (j.contains("identities")) {
            if (j["identities"].is_null()) {
                throw appwrite::DeserializationException("Required field 'identities' is null");
            } else {
                                            for (auto& item : j["identities"]) {
                                    obj.identities.push_back(Identity::fromJson(item));
                                }
                        }
        }
        return obj;
    }

    /** @brief Serialize to JSON */
    [[nodiscard]] nlohmann::json toJson() const {
        nlohmann::json j = nlohmann::json::object();
        {
            j["total"] = this->total;
        }
        {
                nlohmann::json arr = nlohmann::json::array();
            for (auto& item : this->identities) arr.push_back(item.toJson());
            j["identities"] = arr;
            }
        return j;
    }
};

/** @brief ADL hook for nlohmann::json */
inline void from_json(const nlohmann::json& j, IdentityList& x) {
    x = IdentityList::fromJson(j);
}

inline void to_json(nlohmann::json& j, const IdentityList& x) {
    j = x.toJson();
}

/**
 * @brief Log
 */
struct Log {
    /** @brief Event name. */
    std::string event;
    /** @brief User ID of the actor recorded for this log. During impersonation, this is the original impersonator, not the impersonated target user. */
    std::string userId;
    /** @brief User email of the actor recorded for this log. During impersonation, this is the original impersonator. */
    std::string userEmail;
    /** @brief User name of the actor recorded for this log. During impersonation, this is the original impersonator. */
    std::string userName;
    /** @brief API mode when event triggered. */
    std::string mode;
    /** @brief User type who triggered the audit log. Possible values: user, admin, guest, keyProject, keyAccount, keyOrganization. */
    std::string userType;
    /** @brief IP session in use when the session was created. */
    std::string ip;
    /** @brief Log creation date in ISO 8601 format. */
    std::string time;
    /** @brief Operating system code name. View list of [available options](https://github.com/appwrite/appwrite/blob/master/docs/lists/os.json). */
    std::string osCode;
    /** @brief Operating system name. */
    std::string osName;
    /** @brief Operating system version. */
    std::string osVersion;
    /** @brief Client type. */
    std::string clientType;
    /** @brief Client code name. View list of [available options](https://github.com/appwrite/appwrite/blob/master/docs/lists/clients.json). */
    std::string clientCode;
    /** @brief Client name. */
    std::string clientName;
    /** @brief Client version. */
    std::string clientVersion;
    /** @brief Client engine name. */
    std::string clientEngine;
    /** @brief Client engine name. */
    std::string clientEngineVersion;
    /** @brief Device name. */
    std::string deviceName;
    /** @brief Device brand name. */
    std::string deviceBrand;
    /** @brief Device model name. */
    std::string deviceModel;
    /** @brief Country two-character ISO 3166-1 alpha code. */
    std::string countryCode;
    /** @brief Country name. */
    std::string countryName;

    /** @brief Deserialize from JSON */
    static Log fromJson(const nlohmann::json& j) {
        Log obj{}; // value-init: zeroes bools, ints, enums
        if (j.contains("event")) {
            if (j["event"].is_null()) {
                throw appwrite::DeserializationException("Required field 'event' is null");
            } else {
                obj.event = j["event"].get<std::string>();
            }
        }
        if (j.contains("userId")) {
            if (j["userId"].is_null()) {
                throw appwrite::DeserializationException("Required field 'userId' is null");
            } else {
                obj.userId = j["userId"].get<std::string>();
            }
        }
        if (j.contains("userEmail")) {
            if (j["userEmail"].is_null()) {
                throw appwrite::DeserializationException("Required field 'userEmail' is null");
            } else {
                obj.userEmail = j["userEmail"].get<std::string>();
            }
        }
        if (j.contains("userName")) {
            if (j["userName"].is_null()) {
                throw appwrite::DeserializationException("Required field 'userName' is null");
            } else {
                obj.userName = j["userName"].get<std::string>();
            }
        }
        if (j.contains("mode")) {
            if (j["mode"].is_null()) {
                throw appwrite::DeserializationException("Required field 'mode' is null");
            } else {
                obj.mode = j["mode"].get<std::string>();
            }
        }
        if (j.contains("userType")) {
            if (j["userType"].is_null()) {
                throw appwrite::DeserializationException("Required field 'userType' is null");
            } else {
                obj.userType = j["userType"].get<std::string>();
            }
        }
        if (j.contains("ip")) {
            if (j["ip"].is_null()) {
                throw appwrite::DeserializationException("Required field 'ip' is null");
            } else {
                obj.ip = j["ip"].get<std::string>();
            }
        }
        if (j.contains("time")) {
            if (j["time"].is_null()) {
                throw appwrite::DeserializationException("Required field 'time' is null");
            } else {
                obj.time = j["time"].get<std::string>();
            }
        }
        if (j.contains("osCode")) {
            if (j["osCode"].is_null()) {
                throw appwrite::DeserializationException("Required field 'osCode' is null");
            } else {
                obj.osCode = j["osCode"].get<std::string>();
            }
        }
        if (j.contains("osName")) {
            if (j["osName"].is_null()) {
                throw appwrite::DeserializationException("Required field 'osName' is null");
            } else {
                obj.osName = j["osName"].get<std::string>();
            }
        }
        if (j.contains("osVersion")) {
            if (j["osVersion"].is_null()) {
                throw appwrite::DeserializationException("Required field 'osVersion' is null");
            } else {
                obj.osVersion = j["osVersion"].get<std::string>();
            }
        }
        if (j.contains("clientType")) {
            if (j["clientType"].is_null()) {
                throw appwrite::DeserializationException("Required field 'clientType' is null");
            } else {
                obj.clientType = j["clientType"].get<std::string>();
            }
        }
        if (j.contains("clientCode")) {
            if (j["clientCode"].is_null()) {
                throw appwrite::DeserializationException("Required field 'clientCode' is null");
            } else {
                obj.clientCode = j["clientCode"].get<std::string>();
            }
        }
        if (j.contains("clientName")) {
            if (j["clientName"].is_null()) {
                throw appwrite::DeserializationException("Required field 'clientName' is null");
            } else {
                obj.clientName = j["clientName"].get<std::string>();
            }
        }
        if (j.contains("clientVersion")) {
            if (j["clientVersion"].is_null()) {
                throw appwrite::DeserializationException("Required field 'clientVersion' is null");
            } else {
                obj.clientVersion = j["clientVersion"].get<std::string>();
            }
        }
        if (j.contains("clientEngine")) {
            if (j["clientEngine"].is_null()) {
                throw appwrite::DeserializationException("Required field 'clientEngine' is null");
            } else {
                obj.clientEngine = j["clientEngine"].get<std::string>();
            }
        }
        if (j.contains("clientEngineVersion")) {
            if (j["clientEngineVersion"].is_null()) {
                throw appwrite::DeserializationException("Required field 'clientEngineVersion' is null");
            } else {
                obj.clientEngineVersion = j["clientEngineVersion"].get<std::string>();
            }
        }
        if (j.contains("deviceName")) {
            if (j["deviceName"].is_null()) {
                throw appwrite::DeserializationException("Required field 'deviceName' is null");
            } else {
                obj.deviceName = j["deviceName"].get<std::string>();
            }
        }
        if (j.contains("deviceBrand")) {
            if (j["deviceBrand"].is_null()) {
                throw appwrite::DeserializationException("Required field 'deviceBrand' is null");
            } else {
                obj.deviceBrand = j["deviceBrand"].get<std::string>();
            }
        }
        if (j.contains("deviceModel")) {
            if (j["deviceModel"].is_null()) {
                throw appwrite::DeserializationException("Required field 'deviceModel' is null");
            } else {
                obj.deviceModel = j["deviceModel"].get<std::string>();
            }
        }
        if (j.contains("countryCode")) {
            if (j["countryCode"].is_null()) {
                throw appwrite::DeserializationException("Required field 'countryCode' is null");
            } else {
                obj.countryCode = j["countryCode"].get<std::string>();
            }
        }
        if (j.contains("countryName")) {
            if (j["countryName"].is_null()) {
                throw appwrite::DeserializationException("Required field 'countryName' is null");
            } else {
                obj.countryName = j["countryName"].get<std::string>();
            }
        }
        return obj;
    }

    /** @brief Serialize to JSON */
    [[nodiscard]] nlohmann::json toJson() const {
        nlohmann::json j = nlohmann::json::object();
        {
            j["event"] = this->event;
        }
        {
            j["userId"] = this->userId;
        }
        {
            j["userEmail"] = this->userEmail;
        }
        {
            j["userName"] = this->userName;
        }
        {
            j["mode"] = this->mode;
        }
        {
            j["userType"] = this->userType;
        }
        {
            j["ip"] = this->ip;
        }
        {
            j["time"] = this->time;
        }
        {
            j["osCode"] = this->osCode;
        }
        {
            j["osName"] = this->osName;
        }
        {
            j["osVersion"] = this->osVersion;
        }
        {
            j["clientType"] = this->clientType;
        }
        {
            j["clientCode"] = this->clientCode;
        }
        {
            j["clientName"] = this->clientName;
        }
        {
            j["clientVersion"] = this->clientVersion;
        }
        {
            j["clientEngine"] = this->clientEngine;
        }
        {
            j["clientEngineVersion"] = this->clientEngineVersion;
        }
        {
            j["deviceName"] = this->deviceName;
        }
        {
            j["deviceBrand"] = this->deviceBrand;
        }
        {
            j["deviceModel"] = this->deviceModel;
        }
        {
            j["countryCode"] = this->countryCode;
        }
        {
            j["countryName"] = this->countryName;
        }
        return j;
    }
};

/** @brief ADL hook for nlohmann::json */
inline void from_json(const nlohmann::json& j, Log& x) {
    x = Log::fromJson(j);
}

inline void to_json(nlohmann::json& j, const Log& x) {
    j = x.toJson();
}

/**
 * @brief Logs List
 */
struct LogList {
    /** @brief Total number of logs that matched your query. */
    int64_t total;
    /** @brief List of logs. */
    std::vector<appwrite::models::Log> logs;

    /** @brief Deserialize from JSON */
    static LogList fromJson(const nlohmann::json& j) {
        LogList obj{}; // value-init: zeroes bools, ints, enums
        if (j.contains("total")) {
            if (j["total"].is_null()) {
                throw appwrite::DeserializationException("Required field 'total' is null");
            } else {
                obj.total = j["total"].get<int64_t>();
            }
        }
        if (j.contains("logs")) {
            if (j["logs"].is_null()) {
                throw appwrite::DeserializationException("Required field 'logs' is null");
            } else {
                                            for (auto& item : j["logs"]) {
                                    obj.logs.push_back(Log::fromJson(item));
                                }
                        }
        }
        return obj;
    }

    /** @brief Serialize to JSON */
    [[nodiscard]] nlohmann::json toJson() const {
        nlohmann::json j = nlohmann::json::object();
        {
            j["total"] = this->total;
        }
        {
                nlohmann::json arr = nlohmann::json::array();
            for (auto& item : this->logs) arr.push_back(item.toJson());
            j["logs"] = arr;
            }
        return j;
    }
};

/** @brief ADL hook for nlohmann::json */
inline void from_json(const nlohmann::json& j, LogList& x) {
    x = LogList::fromJson(j);
}

inline void to_json(nlohmann::json& j, const LogList& x) {
    j = x.toJson();
}

/**
 * @brief File
 */
struct File {
    /** @brief File ID. */
    std::string id;
    /** @brief Bucket ID. */
    std::string bucketId;
    /** @brief File creation date in ISO 8601 format. */
    std::string createdAt;
    /** @brief File update date in ISO 8601 format. */
    std::string updatedAt;
    /** @brief File permissions. [Learn more about permissions](https://appwrite.io/docs/permissions). */
    std::vector<std::string> permissions;
    /** @brief File name. */
    std::string name;
    /** @brief File MD5 signature. */
    std::string signature;
    /** @brief File mime type. */
    std::string mimeType;
    /** @brief File original size in bytes. */
    int64_t sizeOriginal;
    /** @brief Total number of chunks available */
    int64_t chunksTotal;
    /** @brief Total number of chunks uploaded */
    int64_t chunksUploaded;
    /** @brief Whether file contents are encrypted at rest. */
    bool encryption;
    /** @brief Compression algorithm used for the file. Will be one of none, [gzip](https://en.wikipedia.org/wiki/Gzip), or [zstd](https://en.wikipedia.org/wiki/Zstd). */
    std::string compression;

    /** @brief Deserialize from JSON */
    static File fromJson(const nlohmann::json& j) {
        File obj{}; // value-init: zeroes bools, ints, enums
        if (j.contains("$id")) {
            if (j["$id"].is_null()) {
                throw appwrite::DeserializationException("Required field '$id' is null");
            } else {
                obj.id = j["$id"].get<std::string>();
            }
        }
        if (j.contains("bucketId")) {
            if (j["bucketId"].is_null()) {
                throw appwrite::DeserializationException("Required field 'bucketId' is null");
            } else {
                obj.bucketId = j["bucketId"].get<std::string>();
            }
        }
        if (j.contains("$createdAt")) {
            if (j["$createdAt"].is_null()) {
                throw appwrite::DeserializationException("Required field '$createdAt' is null");
            } else {
                obj.createdAt = j["$createdAt"].get<std::string>();
            }
        }
        if (j.contains("$updatedAt")) {
            if (j["$updatedAt"].is_null()) {
                throw appwrite::DeserializationException("Required field '$updatedAt' is null");
            } else {
                obj.updatedAt = j["$updatedAt"].get<std::string>();
            }
        }
        if (j.contains("$permissions")) {
            if (j["$permissions"].is_null()) {
                throw appwrite::DeserializationException("Required field '$permissions' is null");
            } else {
                obj.permissions = j["$permissions"].get<std::vector<std::string>>();
            }
        }
        if (j.contains("name")) {
            if (j["name"].is_null()) {
                throw appwrite::DeserializationException("Required field 'name' is null");
            } else {
                obj.name = j["name"].get<std::string>();
            }
        }
        if (j.contains("signature")) {
            if (j["signature"].is_null()) {
                throw appwrite::DeserializationException("Required field 'signature' is null");
            } else {
                obj.signature = j["signature"].get<std::string>();
            }
        }
        if (j.contains("mimeType")) {
            if (j["mimeType"].is_null()) {
                throw appwrite::DeserializationException("Required field 'mimeType' is null");
            } else {
                obj.mimeType = j["mimeType"].get<std::string>();
            }
        }
        if (j.contains("sizeOriginal")) {
            if (j["sizeOriginal"].is_null()) {
                throw appwrite::DeserializationException("Required field 'sizeOriginal' is null");
            } else {
                obj.sizeOriginal = j["sizeOriginal"].get<int64_t>();
            }
        }
        if (j.contains("chunksTotal")) {
            if (j["chunksTotal"].is_null()) {
                throw appwrite::DeserializationException("Required field 'chunksTotal' is null");
            } else {
                obj.chunksTotal = j["chunksTotal"].get<int64_t>();
            }
        }
        if (j.contains("chunksUploaded")) {
            if (j["chunksUploaded"].is_null()) {
                throw appwrite::DeserializationException("Required field 'chunksUploaded' is null");
            } else {
                obj.chunksUploaded = j["chunksUploaded"].get<int64_t>();
            }
        }
        if (j.contains("encryption")) {
            if (j["encryption"].is_null()) {
                throw appwrite::DeserializationException("Required field 'encryption' is null");
            } else {
                obj.encryption = j["encryption"].get<bool>();
            }
        }
        if (j.contains("compression")) {
            if (j["compression"].is_null()) {
                throw appwrite::DeserializationException("Required field 'compression' is null");
            } else {
                obj.compression = j["compression"].get<std::string>();
            }
        }
        return obj;
    }

    /** @brief Serialize to JSON */
    [[nodiscard]] nlohmann::json toJson() const {
        nlohmann::json j = nlohmann::json::object();
        {
            j["$id"] = this->id;
        }
        {
            j["bucketId"] = this->bucketId;
        }
        {
            j["$createdAt"] = this->createdAt;
        }
        {
            j["$updatedAt"] = this->updatedAt;
        }
        {
            j["$permissions"] = this->permissions;
        }
        {
            j["name"] = this->name;
        }
        {
            j["signature"] = this->signature;
        }
        {
            j["mimeType"] = this->mimeType;
        }
        {
            j["sizeOriginal"] = this->sizeOriginal;
        }
        {
            j["chunksTotal"] = this->chunksTotal;
        }
        {
            j["chunksUploaded"] = this->chunksUploaded;
        }
        {
            j["encryption"] = this->encryption;
        }
        {
            j["compression"] = this->compression;
        }
        return j;
    }
};

/** @brief ADL hook for nlohmann::json */
inline void from_json(const nlohmann::json& j, File& x) {
    x = File::fromJson(j);
}

inline void to_json(nlohmann::json& j, const File& x) {
    j = x.toJson();
}

/**
 * @brief Files List
 */
struct FileList {
    /** @brief Total number of files that matched your query. */
    int64_t total;
    /** @brief List of files. */
    std::vector<appwrite::models::File> files;

    /** @brief Deserialize from JSON */
    static FileList fromJson(const nlohmann::json& j) {
        FileList obj{}; // value-init: zeroes bools, ints, enums
        if (j.contains("total")) {
            if (j["total"].is_null()) {
                throw appwrite::DeserializationException("Required field 'total' is null");
            } else {
                obj.total = j["total"].get<int64_t>();
            }
        }
        if (j.contains("files")) {
            if (j["files"].is_null()) {
                throw appwrite::DeserializationException("Required field 'files' is null");
            } else {
                                            for (auto& item : j["files"]) {
                                    obj.files.push_back(File::fromJson(item));
                                }
                        }
        }
        return obj;
    }

    /** @brief Serialize to JSON */
    [[nodiscard]] nlohmann::json toJson() const {
        nlohmann::json j = nlohmann::json::object();
        {
            j["total"] = this->total;
        }
        {
                nlohmann::json arr = nlohmann::json::array();
            for (auto& item : this->files) arr.push_back(item.toJson());
            j["files"] = arr;
            }
        return j;
    }
};

/** @brief ADL hook for nlohmann::json */
inline void from_json(const nlohmann::json& j, FileList& x) {
    x = FileList::fromJson(j);
}

inline void to_json(nlohmann::json& j, const FileList& x) {
    j = x.toJson();
}

/**
 * @brief Bucket
 */
struct Bucket {
    /** @brief Bucket ID. */
    std::string id;
    /** @brief Bucket creation time in ISO 8601 format. */
    std::string createdAt;
    /** @brief Bucket update date in ISO 8601 format. */
    std::string updatedAt;
    /** @brief Bucket permissions. [Learn more about permissions](https://appwrite.io/docs/permissions). */
    std::vector<std::string> permissions;
    /** @brief Whether file-level security is enabled. [Learn more about permissions](https://appwrite.io/docs/permissions). */
    bool fileSecurity;
    /** @brief Bucket name. */
    std::string name;
    /** @brief Bucket enabled. */
    bool enabled;
    /** @brief Maximum file size supported. */
    int64_t maximumFileSize;
    /** @brief Allowed file extensions. */
    std::vector<std::string> allowedFileExtensions;
    /** @brief Compression algorithm chosen for compression. Will be one of none, [gzip](https://en.wikipedia.org/wiki/Gzip), or [zstd](https://en.wikipedia.org/wiki/Zstd). */
    std::string compression;
    /** @brief Bucket is encrypted. */
    bool encryption;
    /** @brief Virus scanning is enabled. */
    bool antivirus;
    /** @brief Image transformations are enabled. */
    bool transformations;
    /** @brief Total size of this bucket in bytes. */
    int64_t totalSize;

    /** @brief Deserialize from JSON */
    static Bucket fromJson(const nlohmann::json& j) {
        Bucket obj{}; // value-init: zeroes bools, ints, enums
        if (j.contains("$id")) {
            if (j["$id"].is_null()) {
                throw appwrite::DeserializationException("Required field '$id' is null");
            } else {
                obj.id = j["$id"].get<std::string>();
            }
        }
        if (j.contains("$createdAt")) {
            if (j["$createdAt"].is_null()) {
                throw appwrite::DeserializationException("Required field '$createdAt' is null");
            } else {
                obj.createdAt = j["$createdAt"].get<std::string>();
            }
        }
        if (j.contains("$updatedAt")) {
            if (j["$updatedAt"].is_null()) {
                throw appwrite::DeserializationException("Required field '$updatedAt' is null");
            } else {
                obj.updatedAt = j["$updatedAt"].get<std::string>();
            }
        }
        if (j.contains("$permissions")) {
            if (j["$permissions"].is_null()) {
                throw appwrite::DeserializationException("Required field '$permissions' is null");
            } else {
                obj.permissions = j["$permissions"].get<std::vector<std::string>>();
            }
        }
        if (j.contains("fileSecurity")) {
            if (j["fileSecurity"].is_null()) {
                throw appwrite::DeserializationException("Required field 'fileSecurity' is null");
            } else {
                obj.fileSecurity = j["fileSecurity"].get<bool>();
            }
        }
        if (j.contains("name")) {
            if (j["name"].is_null()) {
                throw appwrite::DeserializationException("Required field 'name' is null");
            } else {
                obj.name = j["name"].get<std::string>();
            }
        }
        if (j.contains("enabled")) {
            if (j["enabled"].is_null()) {
                throw appwrite::DeserializationException("Required field 'enabled' is null");
            } else {
                obj.enabled = j["enabled"].get<bool>();
            }
        }
        if (j.contains("maximumFileSize")) {
            if (j["maximumFileSize"].is_null()) {
                throw appwrite::DeserializationException("Required field 'maximumFileSize' is null");
            } else {
                obj.maximumFileSize = j["maximumFileSize"].get<int64_t>();
            }
        }
        if (j.contains("allowedFileExtensions")) {
            if (j["allowedFileExtensions"].is_null()) {
                throw appwrite::DeserializationException("Required field 'allowedFileExtensions' is null");
            } else {
                obj.allowedFileExtensions = j["allowedFileExtensions"].get<std::vector<std::string>>();
            }
        }
        if (j.contains("compression")) {
            if (j["compression"].is_null()) {
                throw appwrite::DeserializationException("Required field 'compression' is null");
            } else {
                obj.compression = j["compression"].get<std::string>();
            }
        }
        if (j.contains("encryption")) {
            if (j["encryption"].is_null()) {
                throw appwrite::DeserializationException("Required field 'encryption' is null");
            } else {
                obj.encryption = j["encryption"].get<bool>();
            }
        }
        if (j.contains("antivirus")) {
            if (j["antivirus"].is_null()) {
                throw appwrite::DeserializationException("Required field 'antivirus' is null");
            } else {
                obj.antivirus = j["antivirus"].get<bool>();
            }
        }
        if (j.contains("transformations")) {
            if (j["transformations"].is_null()) {
                throw appwrite::DeserializationException("Required field 'transformations' is null");
            } else {
                obj.transformations = j["transformations"].get<bool>();
            }
        }
        if (j.contains("totalSize")) {
            if (j["totalSize"].is_null()) {
                throw appwrite::DeserializationException("Required field 'totalSize' is null");
            } else {
                obj.totalSize = j["totalSize"].get<int64_t>();
            }
        }
        return obj;
    }

    /** @brief Serialize to JSON */
    [[nodiscard]] nlohmann::json toJson() const {
        nlohmann::json j = nlohmann::json::object();
        {
            j["$id"] = this->id;
        }
        {
            j["$createdAt"] = this->createdAt;
        }
        {
            j["$updatedAt"] = this->updatedAt;
        }
        {
            j["$permissions"] = this->permissions;
        }
        {
            j["fileSecurity"] = this->fileSecurity;
        }
        {
            j["name"] = this->name;
        }
        {
            j["enabled"] = this->enabled;
        }
        {
            j["maximumFileSize"] = this->maximumFileSize;
        }
        {
            j["allowedFileExtensions"] = this->allowedFileExtensions;
        }
        {
            j["compression"] = this->compression;
        }
        {
            j["encryption"] = this->encryption;
        }
        {
            j["antivirus"] = this->antivirus;
        }
        {
            j["transformations"] = this->transformations;
        }
        {
            j["totalSize"] = this->totalSize;
        }
        return j;
    }
};

/** @brief ADL hook for nlohmann::json */
inline void from_json(const nlohmann::json& j, Bucket& x) {
    x = Bucket::fromJson(j);
}

inline void to_json(nlohmann::json& j, const Bucket& x) {
    j = x.toJson();
}

/**
 * @brief Buckets List
 */
struct BucketList {
    /** @brief Total number of buckets that matched your query. */
    int64_t total;
    /** @brief List of buckets. */
    std::vector<appwrite::models::Bucket> buckets;

    /** @brief Deserialize from JSON */
    static BucketList fromJson(const nlohmann::json& j) {
        BucketList obj{}; // value-init: zeroes bools, ints, enums
        if (j.contains("total")) {
            if (j["total"].is_null()) {
                throw appwrite::DeserializationException("Required field 'total' is null");
            } else {
                obj.total = j["total"].get<int64_t>();
            }
        }
        if (j.contains("buckets")) {
            if (j["buckets"].is_null()) {
                throw appwrite::DeserializationException("Required field 'buckets' is null");
            } else {
                                            for (auto& item : j["buckets"]) {
                                    obj.buckets.push_back(Bucket::fromJson(item));
                                }
                        }
        }
        return obj;
    }

    /** @brief Serialize to JSON */
    [[nodiscard]] nlohmann::json toJson() const {
        nlohmann::json j = nlohmann::json::object();
        {
            j["total"] = this->total;
        }
        {
                nlohmann::json arr = nlohmann::json::array();
            for (auto& item : this->buckets) arr.push_back(item.toJson());
            j["buckets"] = arr;
            }
        return j;
    }
};

/** @brief ADL hook for nlohmann::json */
inline void from_json(const nlohmann::json& j, BucketList& x) {
    x = BucketList::fromJson(j);
}

inline void to_json(nlohmann::json& j, const BucketList& x) {
    j = x.toJson();
}

/**
 * @brief Team
 */
struct Team {
    /** @brief Team ID. */
    std::string id;
    /** @brief Team creation date in ISO 8601 format. */
    std::string createdAt;
    /** @brief Team update date in ISO 8601 format. */
    std::string updatedAt;
    /** @brief Team name. */
    std::string name;
    /** @brief Total number of team members. */
    int64_t total;
    /** @brief Team preferences as a key-value object */
    appwrite::models::Preferences prefs;

    /** @brief Deserialize from JSON */
    static Team fromJson(const nlohmann::json& j) {
        Team obj{}; // value-init: zeroes bools, ints, enums
        if (j.contains("$id")) {
            if (j["$id"].is_null()) {
                throw appwrite::DeserializationException("Required field '$id' is null");
            } else {
                obj.id = j["$id"].get<std::string>();
            }
        }
        if (j.contains("$createdAt")) {
            if (j["$createdAt"].is_null()) {
                throw appwrite::DeserializationException("Required field '$createdAt' is null");
            } else {
                obj.createdAt = j["$createdAt"].get<std::string>();
            }
        }
        if (j.contains("$updatedAt")) {
            if (j["$updatedAt"].is_null()) {
                throw appwrite::DeserializationException("Required field '$updatedAt' is null");
            } else {
                obj.updatedAt = j["$updatedAt"].get<std::string>();
            }
        }
        if (j.contains("name")) {
            if (j["name"].is_null()) {
                throw appwrite::DeserializationException("Required field 'name' is null");
            } else {
                obj.name = j["name"].get<std::string>();
            }
        }
        if (j.contains("total")) {
            if (j["total"].is_null()) {
                throw appwrite::DeserializationException("Required field 'total' is null");
            } else {
                obj.total = j["total"].get<int64_t>();
            }
        }
        if (j.contains("prefs")) {
            if (j["prefs"].is_null()) {
                throw appwrite::DeserializationException("Required field 'prefs' is null");
            } else {
                            obj.prefs = Preferences::fromJson(j["prefs"]);
                        }
        }
        return obj;
    }

    /** @brief Serialize to JSON */
    [[nodiscard]] nlohmann::json toJson() const {
        nlohmann::json j = nlohmann::json::object();
        {
            j["$id"] = this->id;
        }
        {
            j["$createdAt"] = this->createdAt;
        }
        {
            j["$updatedAt"] = this->updatedAt;
        }
        {
            j["name"] = this->name;
        }
        {
            j["total"] = this->total;
        }
        {
                j["prefs"] = this->prefs.toJson();
            }
        return j;
    }
};

/** @brief ADL hook for nlohmann::json */
inline void from_json(const nlohmann::json& j, Team& x) {
    x = Team::fromJson(j);
}

inline void to_json(nlohmann::json& j, const Team& x) {
    j = x.toJson();
}

/**
 * @brief Teams List
 */
struct TeamList {
    /** @brief Total number of teams that matched your query. */
    int64_t total;
    /** @brief List of teams. */
    std::vector<appwrite::models::Team> teams;

    /** @brief Deserialize from JSON */
    static TeamList fromJson(const nlohmann::json& j) {
        TeamList obj{}; // value-init: zeroes bools, ints, enums
        if (j.contains("total")) {
            if (j["total"].is_null()) {
                throw appwrite::DeserializationException("Required field 'total' is null");
            } else {
                obj.total = j["total"].get<int64_t>();
            }
        }
        if (j.contains("teams")) {
            if (j["teams"].is_null()) {
                throw appwrite::DeserializationException("Required field 'teams' is null");
            } else {
                                            for (auto& item : j["teams"]) {
                                    obj.teams.push_back(Team::fromJson(item));
                                }
                        }
        }
        return obj;
    }

    /** @brief Serialize to JSON */
    [[nodiscard]] nlohmann::json toJson() const {
        nlohmann::json j = nlohmann::json::object();
        {
            j["total"] = this->total;
        }
        {
                nlohmann::json arr = nlohmann::json::array();
            for (auto& item : this->teams) arr.push_back(item.toJson());
            j["teams"] = arr;
            }
        return j;
    }
};

/** @brief ADL hook for nlohmann::json */
inline void from_json(const nlohmann::json& j, TeamList& x) {
    x = TeamList::fromJson(j);
}

inline void to_json(nlohmann::json& j, const TeamList& x) {
    j = x.toJson();
}

/**
 * @brief Membership
 */
struct Membership {
    /** @brief Membership ID. */
    std::string id;
    /** @brief Membership creation date in ISO 8601 format. */
    std::string createdAt;
    /** @brief Membership update date in ISO 8601 format. */
    std::string updatedAt;
    /** @brief User ID. */
    std::string userId;
    /** @brief User name. Hide this attribute by toggling membership privacy in the Console. */
    std::string userName;
    /** @brief User email address. Hide this attribute by toggling membership privacy in the Console. */
    std::string userEmail;
    /** @brief Team ID. */
    std::string teamId;
    /** @brief Team name. */
    std::string teamName;
    /** @brief Date, the user has been invited to join the team in ISO 8601 format. */
    std::string invited;
    /** @brief Date, the user has accepted the invitation to join the team in ISO 8601 format. */
    std::string joined;
    /** @brief User confirmation status, true if the user has joined the team or false otherwise. */
    bool confirm;
    /** @brief Multi factor authentication status, true if the user has MFA enabled or false otherwise. Hide this attribute by toggling membership privacy in the Console. */
    bool mfa;
    /** @brief User list of roles */
    std::vector<std::string> roles;

    /** @brief Deserialize from JSON */
    static Membership fromJson(const nlohmann::json& j) {
        Membership obj{}; // value-init: zeroes bools, ints, enums
        if (j.contains("$id")) {
            if (j["$id"].is_null()) {
                throw appwrite::DeserializationException("Required field '$id' is null");
            } else {
                obj.id = j["$id"].get<std::string>();
            }
        }
        if (j.contains("$createdAt")) {
            if (j["$createdAt"].is_null()) {
                throw appwrite::DeserializationException("Required field '$createdAt' is null");
            } else {
                obj.createdAt = j["$createdAt"].get<std::string>();
            }
        }
        if (j.contains("$updatedAt")) {
            if (j["$updatedAt"].is_null()) {
                throw appwrite::DeserializationException("Required field '$updatedAt' is null");
            } else {
                obj.updatedAt = j["$updatedAt"].get<std::string>();
            }
        }
        if (j.contains("userId")) {
            if (j["userId"].is_null()) {
                throw appwrite::DeserializationException("Required field 'userId' is null");
            } else {
                obj.userId = j["userId"].get<std::string>();
            }
        }
        if (j.contains("userName")) {
            if (j["userName"].is_null()) {
                throw appwrite::DeserializationException("Required field 'userName' is null");
            } else {
                obj.userName = j["userName"].get<std::string>();
            }
        }
        if (j.contains("userEmail")) {
            if (j["userEmail"].is_null()) {
                throw appwrite::DeserializationException("Required field 'userEmail' is null");
            } else {
                obj.userEmail = j["userEmail"].get<std::string>();
            }
        }
        if (j.contains("teamId")) {
            if (j["teamId"].is_null()) {
                throw appwrite::DeserializationException("Required field 'teamId' is null");
            } else {
                obj.teamId = j["teamId"].get<std::string>();
            }
        }
        if (j.contains("teamName")) {
            if (j["teamName"].is_null()) {
                throw appwrite::DeserializationException("Required field 'teamName' is null");
            } else {
                obj.teamName = j["teamName"].get<std::string>();
            }
        }
        if (j.contains("invited")) {
            if (j["invited"].is_null()) {
                throw appwrite::DeserializationException("Required field 'invited' is null");
            } else {
                obj.invited = j["invited"].get<std::string>();
            }
        }
        if (j.contains("joined")) {
            if (j["joined"].is_null()) {
                throw appwrite::DeserializationException("Required field 'joined' is null");
            } else {
                obj.joined = j["joined"].get<std::string>();
            }
        }
        if (j.contains("confirm")) {
            if (j["confirm"].is_null()) {
                throw appwrite::DeserializationException("Required field 'confirm' is null");
            } else {
                obj.confirm = j["confirm"].get<bool>();
            }
        }
        if (j.contains("mfa")) {
            if (j["mfa"].is_null()) {
                throw appwrite::DeserializationException("Required field 'mfa' is null");
            } else {
                obj.mfa = j["mfa"].get<bool>();
            }
        }
        if (j.contains("roles")) {
            if (j["roles"].is_null()) {
                throw appwrite::DeserializationException("Required field 'roles' is null");
            } else {
                obj.roles = j["roles"].get<std::vector<std::string>>();
            }
        }
        return obj;
    }

    /** @brief Serialize to JSON */
    [[nodiscard]] nlohmann::json toJson() const {
        nlohmann::json j = nlohmann::json::object();
        {
            j["$id"] = this->id;
        }
        {
            j["$createdAt"] = this->createdAt;
        }
        {
            j["$updatedAt"] = this->updatedAt;
        }
        {
            j["userId"] = this->userId;
        }
        {
            j["userName"] = this->userName;
        }
        {
            j["userEmail"] = this->userEmail;
        }
        {
            j["teamId"] = this->teamId;
        }
        {
            j["teamName"] = this->teamName;
        }
        {
            j["invited"] = this->invited;
        }
        {
            j["joined"] = this->joined;
        }
        {
            j["confirm"] = this->confirm;
        }
        {
            j["mfa"] = this->mfa;
        }
        {
            j["roles"] = this->roles;
        }
        return j;
    }
};

/** @brief ADL hook for nlohmann::json */
inline void from_json(const nlohmann::json& j, Membership& x) {
    x = Membership::fromJson(j);
}

inline void to_json(nlohmann::json& j, const Membership& x) {
    j = x.toJson();
}

/**
 * @brief Memberships List
 */
struct MembershipList {
    /** @brief Total number of memberships that matched your query. */
    int64_t total;
    /** @brief List of memberships. */
    std::vector<appwrite::models::Membership> memberships;

    /** @brief Deserialize from JSON */
    static MembershipList fromJson(const nlohmann::json& j) {
        MembershipList obj{}; // value-init: zeroes bools, ints, enums
        if (j.contains("total")) {
            if (j["total"].is_null()) {
                throw appwrite::DeserializationException("Required field 'total' is null");
            } else {
                obj.total = j["total"].get<int64_t>();
            }
        }
        if (j.contains("memberships")) {
            if (j["memberships"].is_null()) {
                throw appwrite::DeserializationException("Required field 'memberships' is null");
            } else {
                                            for (auto& item : j["memberships"]) {
                                    obj.memberships.push_back(Membership::fromJson(item));
                                }
                        }
        }
        return obj;
    }

    /** @brief Serialize to JSON */
    [[nodiscard]] nlohmann::json toJson() const {
        nlohmann::json j = nlohmann::json::object();
        {
            j["total"] = this->total;
        }
        {
                nlohmann::json arr = nlohmann::json::array();
            for (auto& item : this->memberships) arr.push_back(item.toJson());
            j["memberships"] = arr;
            }
        return j;
    }
};

/** @brief ADL hook for nlohmann::json */
inline void from_json(const nlohmann::json& j, MembershipList& x) {
    x = MembershipList::fromJson(j);
}

inline void to_json(nlohmann::json& j, const MembershipList& x) {
    j = x.toJson();
}

/**
 * @brief Variable
 */
struct Variable {
    /** @brief Variable ID. */
    std::string id;
    /** @brief Variable creation date in ISO 8601 format. */
    std::string createdAt;
    /** @brief Variable creation date in ISO 8601 format. */
    std::string updatedAt;
    /** @brief Variable key. */
    std::string key;
    /** @brief Variable value. */
    std::string value;
    /** @brief Variable secret flag. Secret variables can only be updated or deleted, but never read. */
    bool secret;
    /** @brief Service to which the variable belongs. Possible values are "project", "function" */
    std::string resourceType;
    /** @brief ID of resource to which the variable belongs. If resourceType is "project", it is empty. If resourceType is "function", it is ID of the function. */
    std::string resourceId;

    /** @brief Deserialize from JSON */
    static Variable fromJson(const nlohmann::json& j) {
        Variable obj{}; // value-init: zeroes bools, ints, enums
        if (j.contains("$id")) {
            if (j["$id"].is_null()) {
                throw appwrite::DeserializationException("Required field '$id' is null");
            } else {
                obj.id = j["$id"].get<std::string>();
            }
        }
        if (j.contains("$createdAt")) {
            if (j["$createdAt"].is_null()) {
                throw appwrite::DeserializationException("Required field '$createdAt' is null");
            } else {
                obj.createdAt = j["$createdAt"].get<std::string>();
            }
        }
        if (j.contains("$updatedAt")) {
            if (j["$updatedAt"].is_null()) {
                throw appwrite::DeserializationException("Required field '$updatedAt' is null");
            } else {
                obj.updatedAt = j["$updatedAt"].get<std::string>();
            }
        }
        if (j.contains("key")) {
            if (j["key"].is_null()) {
                throw appwrite::DeserializationException("Required field 'key' is null");
            } else {
                obj.key = j["key"].get<std::string>();
            }
        }
        if (j.contains("value")) {
            if (j["value"].is_null()) {
                throw appwrite::DeserializationException("Required field 'value' is null");
            } else {
                obj.value = j["value"].get<std::string>();
            }
        }
        if (j.contains("secret")) {
            if (j["secret"].is_null()) {
                throw appwrite::DeserializationException("Required field 'secret' is null");
            } else {
                obj.secret = j["secret"].get<bool>();
            }
        }
        if (j.contains("resourceType")) {
            if (j["resourceType"].is_null()) {
                throw appwrite::DeserializationException("Required field 'resourceType' is null");
            } else {
                obj.resourceType = j["resourceType"].get<std::string>();
            }
        }
        if (j.contains("resourceId")) {
            if (j["resourceId"].is_null()) {
                throw appwrite::DeserializationException("Required field 'resourceId' is null");
            } else {
                obj.resourceId = j["resourceId"].get<std::string>();
            }
        }
        return obj;
    }

    /** @brief Serialize to JSON */
    [[nodiscard]] nlohmann::json toJson() const {
        nlohmann::json j = nlohmann::json::object();
        {
            j["$id"] = this->id;
        }
        {
            j["$createdAt"] = this->createdAt;
        }
        {
            j["$updatedAt"] = this->updatedAt;
        }
        {
            j["key"] = this->key;
        }
        {
            j["value"] = this->value;
        }
        {
            j["secret"] = this->secret;
        }
        {
            j["resourceType"] = this->resourceType;
        }
        {
            j["resourceId"] = this->resourceId;
        }
        return j;
    }
};

/** @brief ADL hook for nlohmann::json */
inline void from_json(const nlohmann::json& j, Variable& x) {
    x = Variable::fromJson(j);
}

inline void to_json(nlohmann::json& j, const Variable& x) {
    j = x.toJson();
}

/**
 * @brief Function
 */
struct Function {
    /** @brief Function ID. */
    std::string id;
    /** @brief Function creation date in ISO 8601 format. */
    std::string createdAt;
    /** @brief Function update date in ISO 8601 format. */
    std::string updatedAt;
    /** @brief Execution permissions. */
    std::vector<std::string> execute;
    /** @brief Function name. */
    std::string name;
    /** @brief Function enabled. */
    bool enabled;
    /** @brief Is the function deployed with the latest configuration? This is set to false if you've changed an environment variables, entrypoint, commands, or other settings that needs redeploy to be applied. When the value is false, redeploy the function to update it with the latest configuration. */
    bool live;
    /** @brief When disabled, executions will exclude logs and errors, and will be slightly faster. */
    bool logging;
    /** @brief Function execution and build runtime. */
    std::string runtime;
    /** @brief How many days to keep the non-active deployments before they will be automatically deleted. */
    int64_t deploymentRetention;
    /** @brief Function's active deployment ID. */
    std::string deploymentId;
    /** @brief Active deployment creation date in ISO 8601 format. */
    std::string deploymentCreatedAt;
    /** @brief Function's latest deployment ID. */
    std::string latestDeploymentId;
    /** @brief Latest deployment creation date in ISO 8601 format. */
    std::string latestDeploymentCreatedAt;
    /** @brief Status of latest deployment. Possible values are "waiting", "processing", "building", "ready", and "failed". */
    std::string latestDeploymentStatus;
    /** @brief Allowed permission scopes. */
    std::vector<std::string> scopes;
    /** @brief Function variables. */
    std::vector<appwrite::models::Variable> vars;
    /** @brief Function trigger events. */
    std::vector<std::string> events;
    /** @brief Function execution schedule in CRON format. */
    std::string schedule;
    /** @brief Function execution timeout in seconds. */
    int64_t timeout;
    /** @brief The entrypoint file used to execute the deployment. */
    std::string entrypoint;
    /** @brief The build command used to build the deployment. */
    std::string commands;
    /** @brief Version of Open Runtimes used for the function. */
    std::string version;
    /** @brief Function VCS (Version Control System) installation id. */
    std::string installationId;
    /** @brief VCS (Version Control System) Repository ID */
    std::string providerRepositoryId;
    /** @brief VCS (Version Control System) branch name */
    std::string providerBranch;
    /** @brief Path to function in VCS (Version Control System) repository */
    std::string providerRootDirectory;
    /** @brief Is VCS (Version Control System) connection is in silent mode? When in silence mode, no comments will be posted on the repository pull or merge requests */
    bool providerSilentMode;
    /** @brief Machine specification for deployment builds. */
    std::string buildSpecification;
    /** @brief Machine specification for executions. */
    std::string runtimeSpecification;

    /** @brief Deserialize from JSON */
    static Function fromJson(const nlohmann::json& j) {
        Function obj{}; // value-init: zeroes bools, ints, enums
        if (j.contains("$id")) {
            if (j["$id"].is_null()) {
                throw appwrite::DeserializationException("Required field '$id' is null");
            } else {
                obj.id = j["$id"].get<std::string>();
            }
        }
        if (j.contains("$createdAt")) {
            if (j["$createdAt"].is_null()) {
                throw appwrite::DeserializationException("Required field '$createdAt' is null");
            } else {
                obj.createdAt = j["$createdAt"].get<std::string>();
            }
        }
        if (j.contains("$updatedAt")) {
            if (j["$updatedAt"].is_null()) {
                throw appwrite::DeserializationException("Required field '$updatedAt' is null");
            } else {
                obj.updatedAt = j["$updatedAt"].get<std::string>();
            }
        }
        if (j.contains("execute")) {
            if (j["execute"].is_null()) {
                throw appwrite::DeserializationException("Required field 'execute' is null");
            } else {
                obj.execute = j["execute"].get<std::vector<std::string>>();
            }
        }
        if (j.contains("name")) {
            if (j["name"].is_null()) {
                throw appwrite::DeserializationException("Required field 'name' is null");
            } else {
                obj.name = j["name"].get<std::string>();
            }
        }
        if (j.contains("enabled")) {
            if (j["enabled"].is_null()) {
                throw appwrite::DeserializationException("Required field 'enabled' is null");
            } else {
                obj.enabled = j["enabled"].get<bool>();
            }
        }
        if (j.contains("live")) {
            if (j["live"].is_null()) {
                throw appwrite::DeserializationException("Required field 'live' is null");
            } else {
                obj.live = j["live"].get<bool>();
            }
        }
        if (j.contains("logging")) {
            if (j["logging"].is_null()) {
                throw appwrite::DeserializationException("Required field 'logging' is null");
            } else {
                obj.logging = j["logging"].get<bool>();
            }
        }
        if (j.contains("runtime")) {
            if (j["runtime"].is_null()) {
                throw appwrite::DeserializationException("Required field 'runtime' is null");
            } else {
                obj.runtime = j["runtime"].get<std::string>();
            }
        }
        if (j.contains("deploymentRetention")) {
            if (j["deploymentRetention"].is_null()) {
                throw appwrite::DeserializationException("Required field 'deploymentRetention' is null");
            } else {
                obj.deploymentRetention = j["deploymentRetention"].get<int64_t>();
            }
        }
        if (j.contains("deploymentId")) {
            if (j["deploymentId"].is_null()) {
                throw appwrite::DeserializationException("Required field 'deploymentId' is null");
            } else {
                obj.deploymentId = j["deploymentId"].get<std::string>();
            }
        }
        if (j.contains("deploymentCreatedAt")) {
            if (j["deploymentCreatedAt"].is_null()) {
                throw appwrite::DeserializationException("Required field 'deploymentCreatedAt' is null");
            } else {
                obj.deploymentCreatedAt = j["deploymentCreatedAt"].get<std::string>();
            }
        }
        if (j.contains("latestDeploymentId")) {
            if (j["latestDeploymentId"].is_null()) {
                throw appwrite::DeserializationException("Required field 'latestDeploymentId' is null");
            } else {
                obj.latestDeploymentId = j["latestDeploymentId"].get<std::string>();
            }
        }
        if (j.contains("latestDeploymentCreatedAt")) {
            if (j["latestDeploymentCreatedAt"].is_null()) {
                throw appwrite::DeserializationException("Required field 'latestDeploymentCreatedAt' is null");
            } else {
                obj.latestDeploymentCreatedAt = j["latestDeploymentCreatedAt"].get<std::string>();
            }
        }
        if (j.contains("latestDeploymentStatus")) {
            if (j["latestDeploymentStatus"].is_null()) {
                throw appwrite::DeserializationException("Required field 'latestDeploymentStatus' is null");
            } else {
                obj.latestDeploymentStatus = j["latestDeploymentStatus"].get<std::string>();
            }
        }
        if (j.contains("scopes")) {
            if (j["scopes"].is_null()) {
                throw appwrite::DeserializationException("Required field 'scopes' is null");
            } else {
                obj.scopes = j["scopes"].get<std::vector<std::string>>();
            }
        }
        if (j.contains("vars")) {
            if (j["vars"].is_null()) {
                throw appwrite::DeserializationException("Required field 'vars' is null");
            } else {
                                            for (auto& item : j["vars"]) {
                                    obj.vars.push_back(Variable::fromJson(item));
                                }
                        }
        }
        if (j.contains("events")) {
            if (j["events"].is_null()) {
                throw appwrite::DeserializationException("Required field 'events' is null");
            } else {
                obj.events = j["events"].get<std::vector<std::string>>();
            }
        }
        if (j.contains("schedule")) {
            if (j["schedule"].is_null()) {
                throw appwrite::DeserializationException("Required field 'schedule' is null");
            } else {
                obj.schedule = j["schedule"].get<std::string>();
            }
        }
        if (j.contains("timeout")) {
            if (j["timeout"].is_null()) {
                throw appwrite::DeserializationException("Required field 'timeout' is null");
            } else {
                obj.timeout = j["timeout"].get<int64_t>();
            }
        }
        if (j.contains("entrypoint")) {
            if (j["entrypoint"].is_null()) {
                throw appwrite::DeserializationException("Required field 'entrypoint' is null");
            } else {
                obj.entrypoint = j["entrypoint"].get<std::string>();
            }
        }
        if (j.contains("commands")) {
            if (j["commands"].is_null()) {
                throw appwrite::DeserializationException("Required field 'commands' is null");
            } else {
                obj.commands = j["commands"].get<std::string>();
            }
        }
        if (j.contains("version")) {
            if (j["version"].is_null()) {
                throw appwrite::DeserializationException("Required field 'version' is null");
            } else {
                obj.version = j["version"].get<std::string>();
            }
        }
        if (j.contains("installationId")) {
            if (j["installationId"].is_null()) {
                throw appwrite::DeserializationException("Required field 'installationId' is null");
            } else {
                obj.installationId = j["installationId"].get<std::string>();
            }
        }
        if (j.contains("providerRepositoryId")) {
            if (j["providerRepositoryId"].is_null()) {
                throw appwrite::DeserializationException("Required field 'providerRepositoryId' is null");
            } else {
                obj.providerRepositoryId = j["providerRepositoryId"].get<std::string>();
            }
        }
        if (j.contains("providerBranch")) {
            if (j["providerBranch"].is_null()) {
                throw appwrite::DeserializationException("Required field 'providerBranch' is null");
            } else {
                obj.providerBranch = j["providerBranch"].get<std::string>();
            }
        }
        if (j.contains("providerRootDirectory")) {
            if (j["providerRootDirectory"].is_null()) {
                throw appwrite::DeserializationException("Required field 'providerRootDirectory' is null");
            } else {
                obj.providerRootDirectory = j["providerRootDirectory"].get<std::string>();
            }
        }
        if (j.contains("providerSilentMode")) {
            if (j["providerSilentMode"].is_null()) {
                throw appwrite::DeserializationException("Required field 'providerSilentMode' is null");
            } else {
                obj.providerSilentMode = j["providerSilentMode"].get<bool>();
            }
        }
        if (j.contains("buildSpecification")) {
            if (j["buildSpecification"].is_null()) {
                throw appwrite::DeserializationException("Required field 'buildSpecification' is null");
            } else {
                obj.buildSpecification = j["buildSpecification"].get<std::string>();
            }
        }
        if (j.contains("runtimeSpecification")) {
            if (j["runtimeSpecification"].is_null()) {
                throw appwrite::DeserializationException("Required field 'runtimeSpecification' is null");
            } else {
                obj.runtimeSpecification = j["runtimeSpecification"].get<std::string>();
            }
        }
        return obj;
    }

    /** @brief Serialize to JSON */
    [[nodiscard]] nlohmann::json toJson() const {
        nlohmann::json j = nlohmann::json::object();
        {
            j["$id"] = this->id;
        }
        {
            j["$createdAt"] = this->createdAt;
        }
        {
            j["$updatedAt"] = this->updatedAt;
        }
        {
            j["execute"] = this->execute;
        }
        {
            j["name"] = this->name;
        }
        {
            j["enabled"] = this->enabled;
        }
        {
            j["live"] = this->live;
        }
        {
            j["logging"] = this->logging;
        }
        {
            j["runtime"] = this->runtime;
        }
        {
            j["deploymentRetention"] = this->deploymentRetention;
        }
        {
            j["deploymentId"] = this->deploymentId;
        }
        {
            j["deploymentCreatedAt"] = this->deploymentCreatedAt;
        }
        {
            j["latestDeploymentId"] = this->latestDeploymentId;
        }
        {
            j["latestDeploymentCreatedAt"] = this->latestDeploymentCreatedAt;
        }
        {
            j["latestDeploymentStatus"] = this->latestDeploymentStatus;
        }
        {
            j["scopes"] = this->scopes;
        }
        {
                nlohmann::json arr = nlohmann::json::array();
            for (auto& item : this->vars) arr.push_back(item.toJson());
            j["vars"] = arr;
            }
        {
            j["events"] = this->events;
        }
        {
            j["schedule"] = this->schedule;
        }
        {
            j["timeout"] = this->timeout;
        }
        {
            j["entrypoint"] = this->entrypoint;
        }
        {
            j["commands"] = this->commands;
        }
        {
            j["version"] = this->version;
        }
        {
            j["installationId"] = this->installationId;
        }
        {
            j["providerRepositoryId"] = this->providerRepositoryId;
        }
        {
            j["providerBranch"] = this->providerBranch;
        }
        {
            j["providerRootDirectory"] = this->providerRootDirectory;
        }
        {
            j["providerSilentMode"] = this->providerSilentMode;
        }
        {
            j["buildSpecification"] = this->buildSpecification;
        }
        {
            j["runtimeSpecification"] = this->runtimeSpecification;
        }
        return j;
    }
};

/** @brief ADL hook for nlohmann::json */
inline void from_json(const nlohmann::json& j, Function& x) {
    x = Function::fromJson(j);
}

inline void to_json(nlohmann::json& j, const Function& x) {
    j = x.toJson();
}

/**
 * @brief Functions List
 */
struct FunctionList {
    /** @brief Total number of functions that matched your query. */
    int64_t total;
    /** @brief List of functions. */
    std::vector<appwrite::models::Function> functions;

    /** @brief Deserialize from JSON */
    static FunctionList fromJson(const nlohmann::json& j) {
        FunctionList obj{}; // value-init: zeroes bools, ints, enums
        if (j.contains("total")) {
            if (j["total"].is_null()) {
                throw appwrite::DeserializationException("Required field 'total' is null");
            } else {
                obj.total = j["total"].get<int64_t>();
            }
        }
        if (j.contains("functions")) {
            if (j["functions"].is_null()) {
                throw appwrite::DeserializationException("Required field 'functions' is null");
            } else {
                                            for (auto& item : j["functions"]) {
                                    obj.functions.push_back(Function::fromJson(item));
                                }
                        }
        }
        return obj;
    }

    /** @brief Serialize to JSON */
    [[nodiscard]] nlohmann::json toJson() const {
        nlohmann::json j = nlohmann::json::object();
        {
            j["total"] = this->total;
        }
        {
                nlohmann::json arr = nlohmann::json::array();
            for (auto& item : this->functions) arr.push_back(item.toJson());
            j["functions"] = arr;
            }
        return j;
    }
};

/** @brief ADL hook for nlohmann::json */
inline void from_json(const nlohmann::json& j, FunctionList& x) {
    x = FunctionList::fromJson(j);
}

inline void to_json(nlohmann::json& j, const FunctionList& x) {
    j = x.toJson();
}

/**
 * @brief Runtime
 */
struct Runtime {
    /** @brief Runtime ID. */
    std::string id;
    /** @brief Parent runtime key. */
    std::string key;
    /** @brief Runtime Name. */
    std::string name;
    /** @brief Runtime version. */
    std::string version;
    /** @brief Base Docker image used to build the runtime. */
    std::string base;
    /** @brief Image name of Docker Hub. */
    std::string image;
    /** @brief Name of the logo image. */
    std::string logo;
    /** @brief List of supported architectures. */
    std::vector<std::string> supports;

    /** @brief Deserialize from JSON */
    static Runtime fromJson(const nlohmann::json& j) {
        Runtime obj{}; // value-init: zeroes bools, ints, enums
        if (j.contains("$id")) {
            if (j["$id"].is_null()) {
                throw appwrite::DeserializationException("Required field '$id' is null");
            } else {
                obj.id = j["$id"].get<std::string>();
            }
        }
        if (j.contains("key")) {
            if (j["key"].is_null()) {
                throw appwrite::DeserializationException("Required field 'key' is null");
            } else {
                obj.key = j["key"].get<std::string>();
            }
        }
        if (j.contains("name")) {
            if (j["name"].is_null()) {
                throw appwrite::DeserializationException("Required field 'name' is null");
            } else {
                obj.name = j["name"].get<std::string>();
            }
        }
        if (j.contains("version")) {
            if (j["version"].is_null()) {
                throw appwrite::DeserializationException("Required field 'version' is null");
            } else {
                obj.version = j["version"].get<std::string>();
            }
        }
        if (j.contains("base")) {
            if (j["base"].is_null()) {
                throw appwrite::DeserializationException("Required field 'base' is null");
            } else {
                obj.base = j["base"].get<std::string>();
            }
        }
        if (j.contains("image")) {
            if (j["image"].is_null()) {
                throw appwrite::DeserializationException("Required field 'image' is null");
            } else {
                obj.image = j["image"].get<std::string>();
            }
        }
        if (j.contains("logo")) {
            if (j["logo"].is_null()) {
                throw appwrite::DeserializationException("Required field 'logo' is null");
            } else {
                obj.logo = j["logo"].get<std::string>();
            }
        }
        if (j.contains("supports")) {
            if (j["supports"].is_null()) {
                throw appwrite::DeserializationException("Required field 'supports' is null");
            } else {
                obj.supports = j["supports"].get<std::vector<std::string>>();
            }
        }
        return obj;
    }

    /** @brief Serialize to JSON */
    [[nodiscard]] nlohmann::json toJson() const {
        nlohmann::json j = nlohmann::json::object();
        {
            j["$id"] = this->id;
        }
        {
            j["key"] = this->key;
        }
        {
            j["name"] = this->name;
        }
        {
            j["version"] = this->version;
        }
        {
            j["base"] = this->base;
        }
        {
            j["image"] = this->image;
        }
        {
            j["logo"] = this->logo;
        }
        {
            j["supports"] = this->supports;
        }
        return j;
    }
};

/** @brief ADL hook for nlohmann::json */
inline void from_json(const nlohmann::json& j, Runtime& x) {
    x = Runtime::fromJson(j);
}

inline void to_json(nlohmann::json& j, const Runtime& x) {
    j = x.toJson();
}

/**
 * @brief Runtimes List
 */
struct RuntimeList {
    /** @brief Total number of runtimes that matched your query. */
    int64_t total;
    /** @brief List of runtimes. */
    std::vector<appwrite::models::Runtime> runtimes;

    /** @brief Deserialize from JSON */
    static RuntimeList fromJson(const nlohmann::json& j) {
        RuntimeList obj{}; // value-init: zeroes bools, ints, enums
        if (j.contains("total")) {
            if (j["total"].is_null()) {
                throw appwrite::DeserializationException("Required field 'total' is null");
            } else {
                obj.total = j["total"].get<int64_t>();
            }
        }
        if (j.contains("runtimes")) {
            if (j["runtimes"].is_null()) {
                throw appwrite::DeserializationException("Required field 'runtimes' is null");
            } else {
                                            for (auto& item : j["runtimes"]) {
                                    obj.runtimes.push_back(Runtime::fromJson(item));
                                }
                        }
        }
        return obj;
    }

    /** @brief Serialize to JSON */
    [[nodiscard]] nlohmann::json toJson() const {
        nlohmann::json j = nlohmann::json::object();
        {
            j["total"] = this->total;
        }
        {
                nlohmann::json arr = nlohmann::json::array();
            for (auto& item : this->runtimes) arr.push_back(item.toJson());
            j["runtimes"] = arr;
            }
        return j;
    }
};

/** @brief ADL hook for nlohmann::json */
inline void from_json(const nlohmann::json& j, RuntimeList& x) {
    x = RuntimeList::fromJson(j);
}

inline void to_json(nlohmann::json& j, const RuntimeList& x) {
    j = x.toJson();
}

/**
 * @brief Deployment
 */
struct Deployment {
    /** @brief Deployment ID. */
    std::string id;
    /** @brief Deployment creation date in ISO 8601 format. */
    std::string createdAt;
    /** @brief Deployment update date in ISO 8601 format. */
    std::string updatedAt;
    /** @brief Type of deployment. */
    std::string type;
    /** @brief Resource ID. */
    std::string resourceId;
    /** @brief Resource type. */
    std::string resourceType;
    /** @brief The entrypoint file to use to execute the deployment code. */
    std::string entrypoint;
    /** @brief The code size in bytes. */
    int64_t sourceSize;
    /** @brief The build output size in bytes. */
    int64_t buildSize;
    /** @brief The total size in bytes (source and build output). */
    int64_t totalSize;
    /** @brief The current build ID. */
    std::string buildId;
    /** @brief Whether the deployment should be automatically activated. */
    bool activate;
    /** @brief Screenshot with light theme preference file ID. */
    std::string screenshotLight;
    /** @brief Screenshot with dark theme preference file ID. */
    std::string screenshotDark;
    /** @brief The deployment status. Possible values are "waiting", "processing", "building", "ready", "canceled" and "failed". */
    appwrite::enums::DeploymentStatus status;
    /** @brief The build logs. */
    std::string buildLogs;
    /** @brief The current build time in seconds. */
    int64_t buildDuration;
    /** @brief The name of the vcs provider repository */
    std::string providerRepositoryName;
    /** @brief The name of the vcs provider repository owner */
    std::string providerRepositoryOwner;
    /** @brief The url of the vcs provider repository */
    std::string providerRepositoryUrl;
    /** @brief The commit hash of the vcs commit */
    std::string providerCommitHash;
    /** @brief The url of vcs commit author */
    std::string providerCommitAuthorUrl;
    /** @brief The name of vcs commit author */
    std::string providerCommitAuthor;
    /** @brief The commit message */
    std::string providerCommitMessage;
    /** @brief The url of the vcs commit */
    std::string providerCommitUrl;
    /** @brief The branch of the vcs repository */
    std::string providerBranch;
    /** @brief The branch of the vcs repository */
    std::string providerBranchUrl;

    /** @brief Deserialize from JSON */
    static Deployment fromJson(const nlohmann::json& j) {
        Deployment obj{}; // value-init: zeroes bools, ints, enums
        if (j.contains("$id")) {
            if (j["$id"].is_null()) {
                throw appwrite::DeserializationException("Required field '$id' is null");
            } else {
                obj.id = j["$id"].get<std::string>();
            }
        }
        if (j.contains("$createdAt")) {
            if (j["$createdAt"].is_null()) {
                throw appwrite::DeserializationException("Required field '$createdAt' is null");
            } else {
                obj.createdAt = j["$createdAt"].get<std::string>();
            }
        }
        if (j.contains("$updatedAt")) {
            if (j["$updatedAt"].is_null()) {
                throw appwrite::DeserializationException("Required field '$updatedAt' is null");
            } else {
                obj.updatedAt = j["$updatedAt"].get<std::string>();
            }
        }
        if (j.contains("type")) {
            if (j["type"].is_null()) {
                throw appwrite::DeserializationException("Required field 'type' is null");
            } else {
                obj.type = j["type"].get<std::string>();
            }
        }
        if (j.contains("resourceId")) {
            if (j["resourceId"].is_null()) {
                throw appwrite::DeserializationException("Required field 'resourceId' is null");
            } else {
                obj.resourceId = j["resourceId"].get<std::string>();
            }
        }
        if (j.contains("resourceType")) {
            if (j["resourceType"].is_null()) {
                throw appwrite::DeserializationException("Required field 'resourceType' is null");
            } else {
                obj.resourceType = j["resourceType"].get<std::string>();
            }
        }
        if (j.contains("entrypoint")) {
            if (j["entrypoint"].is_null()) {
                throw appwrite::DeserializationException("Required field 'entrypoint' is null");
            } else {
                obj.entrypoint = j["entrypoint"].get<std::string>();
            }
        }
        if (j.contains("sourceSize")) {
            if (j["sourceSize"].is_null()) {
                throw appwrite::DeserializationException("Required field 'sourceSize' is null");
            } else {
                obj.sourceSize = j["sourceSize"].get<int64_t>();
            }
        }
        if (j.contains("buildSize")) {
            if (j["buildSize"].is_null()) {
                throw appwrite::DeserializationException("Required field 'buildSize' is null");
            } else {
                obj.buildSize = j["buildSize"].get<int64_t>();
            }
        }
        if (j.contains("totalSize")) {
            if (j["totalSize"].is_null()) {
                throw appwrite::DeserializationException("Required field 'totalSize' is null");
            } else {
                obj.totalSize = j["totalSize"].get<int64_t>();
            }
        }
        if (j.contains("buildId")) {
            if (j["buildId"].is_null()) {
                throw appwrite::DeserializationException("Required field 'buildId' is null");
            } else {
                obj.buildId = j["buildId"].get<std::string>();
            }
        }
        if (j.contains("activate")) {
            if (j["activate"].is_null()) {
                throw appwrite::DeserializationException("Required field 'activate' is null");
            } else {
                obj.activate = j["activate"].get<bool>();
            }
        }
        if (j.contains("screenshotLight")) {
            if (j["screenshotLight"].is_null()) {
                throw appwrite::DeserializationException("Required field 'screenshotLight' is null");
            } else {
                obj.screenshotLight = j["screenshotLight"].get<std::string>();
            }
        }
        if (j.contains("screenshotDark")) {
            if (j["screenshotDark"].is_null()) {
                throw appwrite::DeserializationException("Required field 'screenshotDark' is null");
            } else {
                obj.screenshotDark = j["screenshotDark"].get<std::string>();
            }
        }
        if (j.contains("status")) {
            if (j["status"].is_null()) {
                throw appwrite::DeserializationException("Required field 'status' is null");
            } else {
                obj.status = j["status"].get<appwrite::enums::DeploymentStatus>();
            }
        }
        if (j.contains("buildLogs")) {
            if (j["buildLogs"].is_null()) {
                throw appwrite::DeserializationException("Required field 'buildLogs' is null");
            } else {
                obj.buildLogs = j["buildLogs"].get<std::string>();
            }
        }
        if (j.contains("buildDuration")) {
            if (j["buildDuration"].is_null()) {
                throw appwrite::DeserializationException("Required field 'buildDuration' is null");
            } else {
                obj.buildDuration = j["buildDuration"].get<int64_t>();
            }
        }
        if (j.contains("providerRepositoryName")) {
            if (j["providerRepositoryName"].is_null()) {
                throw appwrite::DeserializationException("Required field 'providerRepositoryName' is null");
            } else {
                obj.providerRepositoryName = j["providerRepositoryName"].get<std::string>();
            }
        }
        if (j.contains("providerRepositoryOwner")) {
            if (j["providerRepositoryOwner"].is_null()) {
                throw appwrite::DeserializationException("Required field 'providerRepositoryOwner' is null");
            } else {
                obj.providerRepositoryOwner = j["providerRepositoryOwner"].get<std::string>();
            }
        }
        if (j.contains("providerRepositoryUrl")) {
            if (j["providerRepositoryUrl"].is_null()) {
                throw appwrite::DeserializationException("Required field 'providerRepositoryUrl' is null");
            } else {
                obj.providerRepositoryUrl = j["providerRepositoryUrl"].get<std::string>();
            }
        }
        if (j.contains("providerCommitHash")) {
            if (j["providerCommitHash"].is_null()) {
                throw appwrite::DeserializationException("Required field 'providerCommitHash' is null");
            } else {
                obj.providerCommitHash = j["providerCommitHash"].get<std::string>();
            }
        }
        if (j.contains("providerCommitAuthorUrl")) {
            if (j["providerCommitAuthorUrl"].is_null()) {
                throw appwrite::DeserializationException("Required field 'providerCommitAuthorUrl' is null");
            } else {
                obj.providerCommitAuthorUrl = j["providerCommitAuthorUrl"].get<std::string>();
            }
        }
        if (j.contains("providerCommitAuthor")) {
            if (j["providerCommitAuthor"].is_null()) {
                throw appwrite::DeserializationException("Required field 'providerCommitAuthor' is null");
            } else {
                obj.providerCommitAuthor = j["providerCommitAuthor"].get<std::string>();
            }
        }
        if (j.contains("providerCommitMessage")) {
            if (j["providerCommitMessage"].is_null()) {
                throw appwrite::DeserializationException("Required field 'providerCommitMessage' is null");
            } else {
                obj.providerCommitMessage = j["providerCommitMessage"].get<std::string>();
            }
        }
        if (j.contains("providerCommitUrl")) {
            if (j["providerCommitUrl"].is_null()) {
                throw appwrite::DeserializationException("Required field 'providerCommitUrl' is null");
            } else {
                obj.providerCommitUrl = j["providerCommitUrl"].get<std::string>();
            }
        }
        if (j.contains("providerBranch")) {
            if (j["providerBranch"].is_null()) {
                throw appwrite::DeserializationException("Required field 'providerBranch' is null");
            } else {
                obj.providerBranch = j["providerBranch"].get<std::string>();
            }
        }
        if (j.contains("providerBranchUrl")) {
            if (j["providerBranchUrl"].is_null()) {
                throw appwrite::DeserializationException("Required field 'providerBranchUrl' is null");
            } else {
                obj.providerBranchUrl = j["providerBranchUrl"].get<std::string>();
            }
        }
        return obj;
    }

    /** @brief Serialize to JSON */
    [[nodiscard]] nlohmann::json toJson() const {
        nlohmann::json j = nlohmann::json::object();
        {
            j["$id"] = this->id;
        }
        {
            j["$createdAt"] = this->createdAt;
        }
        {
            j["$updatedAt"] = this->updatedAt;
        }
        {
            j["type"] = this->type;
        }
        {
            j["resourceId"] = this->resourceId;
        }
        {
            j["resourceType"] = this->resourceType;
        }
        {
            j["entrypoint"] = this->entrypoint;
        }
        {
            j["sourceSize"] = this->sourceSize;
        }
        {
            j["buildSize"] = this->buildSize;
        }
        {
            j["totalSize"] = this->totalSize;
        }
        {
            j["buildId"] = this->buildId;
        }
        {
            j["activate"] = this->activate;
        }
        {
            j["screenshotLight"] = this->screenshotLight;
        }
        {
            j["screenshotDark"] = this->screenshotDark;
        }
        {
            j["status"] = appwrite::enums::toString(this->status);
        }
        {
            j["buildLogs"] = this->buildLogs;
        }
        {
            j["buildDuration"] = this->buildDuration;
        }
        {
            j["providerRepositoryName"] = this->providerRepositoryName;
        }
        {
            j["providerRepositoryOwner"] = this->providerRepositoryOwner;
        }
        {
            j["providerRepositoryUrl"] = this->providerRepositoryUrl;
        }
        {
            j["providerCommitHash"] = this->providerCommitHash;
        }
        {
            j["providerCommitAuthorUrl"] = this->providerCommitAuthorUrl;
        }
        {
            j["providerCommitAuthor"] = this->providerCommitAuthor;
        }
        {
            j["providerCommitMessage"] = this->providerCommitMessage;
        }
        {
            j["providerCommitUrl"] = this->providerCommitUrl;
        }
        {
            j["providerBranch"] = this->providerBranch;
        }
        {
            j["providerBranchUrl"] = this->providerBranchUrl;
        }
        return j;
    }
};

/** @brief ADL hook for nlohmann::json */
inline void from_json(const nlohmann::json& j, Deployment& x) {
    x = Deployment::fromJson(j);
}

inline void to_json(nlohmann::json& j, const Deployment& x) {
    j = x.toJson();
}

/**
 * @brief Deployments List
 */
struct DeploymentList {
    /** @brief Total number of deployments that matched your query. */
    int64_t total;
    /** @brief List of deployments. */
    std::vector<appwrite::models::Deployment> deployments;

    /** @brief Deserialize from JSON */
    static DeploymentList fromJson(const nlohmann::json& j) {
        DeploymentList obj{}; // value-init: zeroes bools, ints, enums
        if (j.contains("total")) {
            if (j["total"].is_null()) {
                throw appwrite::DeserializationException("Required field 'total' is null");
            } else {
                obj.total = j["total"].get<int64_t>();
            }
        }
        if (j.contains("deployments")) {
            if (j["deployments"].is_null()) {
                throw appwrite::DeserializationException("Required field 'deployments' is null");
            } else {
                                            for (auto& item : j["deployments"]) {
                                    obj.deployments.push_back(Deployment::fromJson(item));
                                }
                        }
        }
        return obj;
    }

    /** @brief Serialize to JSON */
    [[nodiscard]] nlohmann::json toJson() const {
        nlohmann::json j = nlohmann::json::object();
        {
            j["total"] = this->total;
        }
        {
                nlohmann::json arr = nlohmann::json::array();
            for (auto& item : this->deployments) arr.push_back(item.toJson());
            j["deployments"] = arr;
            }
        return j;
    }
};

/** @brief ADL hook for nlohmann::json */
inline void from_json(const nlohmann::json& j, DeploymentList& x) {
    x = DeploymentList::fromJson(j);
}

inline void to_json(nlohmann::json& j, const DeploymentList& x) {
    j = x.toJson();
}

/**
 * @brief Headers
 */
struct Headers {
    /** @brief Header name. */
    std::string name;
    /** @brief Header value. */
    std::string value;

    /** @brief Deserialize from JSON */
    static Headers fromJson(const nlohmann::json& j) {
        Headers obj{}; // value-init: zeroes bools, ints, enums
        if (j.contains("name")) {
            if (j["name"].is_null()) {
                throw appwrite::DeserializationException("Required field 'name' is null");
            } else {
                obj.name = j["name"].get<std::string>();
            }
        }
        if (j.contains("value")) {
            if (j["value"].is_null()) {
                throw appwrite::DeserializationException("Required field 'value' is null");
            } else {
                obj.value = j["value"].get<std::string>();
            }
        }
        return obj;
    }

    /** @brief Serialize to JSON */
    [[nodiscard]] nlohmann::json toJson() const {
        nlohmann::json j = nlohmann::json::object();
        {
            j["name"] = this->name;
        }
        {
            j["value"] = this->value;
        }
        return j;
    }
};

/** @brief ADL hook for nlohmann::json */
inline void from_json(const nlohmann::json& j, Headers& x) {
    x = Headers::fromJson(j);
}

inline void to_json(nlohmann::json& j, const Headers& x) {
    j = x.toJson();
}

/**
 * @brief Execution
 */
struct Execution {
    /** @brief Execution ID. */
    std::string id;
    /** @brief Execution creation date in ISO 8601 format. */
    std::string createdAt;
    /** @brief Execution update date in ISO 8601 format. */
    std::string updatedAt;
    /** @brief Execution roles. */
    std::vector<std::string> permissions;
    /** @brief Function ID. */
    std::string functionId;
    /** @brief Function's deployment ID used to create the execution. */
    std::string deploymentId;
    /** @brief The trigger that caused the function to execute. Possible values can be: `http`, `schedule`, or `event`. */
    appwrite::enums::ExecutionTrigger trigger;
    /** @brief The status of the function execution. Possible values can be: `waiting`, `processing`, `completed`, `failed`, or `scheduled`. */
    appwrite::enums::ExecutionStatus status;
    /** @brief HTTP request method type. */
    std::string requestMethod;
    /** @brief HTTP request path and query. */
    std::string requestPath;
    /** @brief HTTP request headers as a key-value object. This will return only whitelisted headers. All headers are returned if execution is created as synchronous. */
    std::vector<appwrite::models::Headers> requestHeaders;
    /** @brief HTTP response status code. */
    int64_t responseStatusCode;
    /** @brief HTTP response body. This will return empty unless execution is created as synchronous. */
    std::string responseBody;
    /** @brief HTTP response headers as a key-value object. This will return only whitelisted headers. All headers are returned if execution is created as synchronous. */
    std::vector<appwrite::models::Headers> responseHeaders;
    /** @brief Function logs. Includes the last 4,000 characters. This will return an empty string unless the response is returned using an API key or as part of a webhook payload. */
    std::string logs;
    /** @brief Function errors. Includes the last 4,000 characters. This will return an empty string unless the response is returned using an API key or as part of a webhook payload. */
    std::string errors;
    /** @brief Resource(function/site) execution duration in seconds. */
    double duration;
    /** @brief The scheduled time for execution. If left empty, execution will be queued immediately. */
    std::optional<std::string> scheduledAt = std::nullopt;

    /** @brief Deserialize from JSON */
    static Execution fromJson(const nlohmann::json& j) {
        Execution obj{}; // value-init: zeroes bools, ints, enums
        if (j.contains("$id")) {
            if (j["$id"].is_null()) {
                throw appwrite::DeserializationException("Required field '$id' is null");
            } else {
                obj.id = j["$id"].get<std::string>();
            }
        }
        if (j.contains("$createdAt")) {
            if (j["$createdAt"].is_null()) {
                throw appwrite::DeserializationException("Required field '$createdAt' is null");
            } else {
                obj.createdAt = j["$createdAt"].get<std::string>();
            }
        }
        if (j.contains("$updatedAt")) {
            if (j["$updatedAt"].is_null()) {
                throw appwrite::DeserializationException("Required field '$updatedAt' is null");
            } else {
                obj.updatedAt = j["$updatedAt"].get<std::string>();
            }
        }
        if (j.contains("$permissions")) {
            if (j["$permissions"].is_null()) {
                throw appwrite::DeserializationException("Required field '$permissions' is null");
            } else {
                obj.permissions = j["$permissions"].get<std::vector<std::string>>();
            }
        }
        if (j.contains("functionId")) {
            if (j["functionId"].is_null()) {
                throw appwrite::DeserializationException("Required field 'functionId' is null");
            } else {
                obj.functionId = j["functionId"].get<std::string>();
            }
        }
        if (j.contains("deploymentId")) {
            if (j["deploymentId"].is_null()) {
                throw appwrite::DeserializationException("Required field 'deploymentId' is null");
            } else {
                obj.deploymentId = j["deploymentId"].get<std::string>();
            }
        }
        if (j.contains("trigger")) {
            if (j["trigger"].is_null()) {
                throw appwrite::DeserializationException("Required field 'trigger' is null");
            } else {
                obj.trigger = j["trigger"].get<appwrite::enums::ExecutionTrigger>();
            }
        }
        if (j.contains("status")) {
            if (j["status"].is_null()) {
                throw appwrite::DeserializationException("Required field 'status' is null");
            } else {
                obj.status = j["status"].get<appwrite::enums::ExecutionStatus>();
            }
        }
        if (j.contains("requestMethod")) {
            if (j["requestMethod"].is_null()) {
                throw appwrite::DeserializationException("Required field 'requestMethod' is null");
            } else {
                obj.requestMethod = j["requestMethod"].get<std::string>();
            }
        }
        if (j.contains("requestPath")) {
            if (j["requestPath"].is_null()) {
                throw appwrite::DeserializationException("Required field 'requestPath' is null");
            } else {
                obj.requestPath = j["requestPath"].get<std::string>();
            }
        }
        if (j.contains("requestHeaders")) {
            if (j["requestHeaders"].is_null()) {
                throw appwrite::DeserializationException("Required field 'requestHeaders' is null");
            } else {
                                            for (auto& item : j["requestHeaders"]) {
                                    obj.requestHeaders.push_back(Headers::fromJson(item));
                                }
                        }
        }
        if (j.contains("responseStatusCode")) {
            if (j["responseStatusCode"].is_null()) {
                throw appwrite::DeserializationException("Required field 'responseStatusCode' is null");
            } else {
                obj.responseStatusCode = j["responseStatusCode"].get<int64_t>();
            }
        }
        if (j.contains("responseBody")) {
            if (j["responseBody"].is_null()) {
                throw appwrite::DeserializationException("Required field 'responseBody' is null");
            } else {
                obj.responseBody = j["responseBody"].get<std::string>();
            }
        }
        if (j.contains("responseHeaders")) {
            if (j["responseHeaders"].is_null()) {
                throw appwrite::DeserializationException("Required field 'responseHeaders' is null");
            } else {
                                            for (auto& item : j["responseHeaders"]) {
                                    obj.responseHeaders.push_back(Headers::fromJson(item));
                                }
                        }
        }
        if (j.contains("logs")) {
            if (j["logs"].is_null()) {
                throw appwrite::DeserializationException("Required field 'logs' is null");
            } else {
                obj.logs = j["logs"].get<std::string>();
            }
        }
        if (j.contains("errors")) {
            if (j["errors"].is_null()) {
                throw appwrite::DeserializationException("Required field 'errors' is null");
            } else {
                obj.errors = j["errors"].get<std::string>();
            }
        }
        if (j.contains("duration")) {
            if (j["duration"].is_null()) {
                throw appwrite::DeserializationException("Required field 'duration' is null");
            } else {
                obj.duration = j["duration"].get<double>();
            }
        }
        if (j.contains("scheduledAt")) {
            if (j["scheduledAt"].is_null()) {
                obj.scheduledAt = std::nullopt;
            } else {
                obj.scheduledAt = j["scheduledAt"].get<std::optional<std::string>>();
            }
        }
        return obj;
    }

    /** @brief Serialize to JSON */
    [[nodiscard]] nlohmann::json toJson() const {
        nlohmann::json j = nlohmann::json::object();
        {
            j["$id"] = this->id;
        }
        {
            j["$createdAt"] = this->createdAt;
        }
        {
            j["$updatedAt"] = this->updatedAt;
        }
        {
            j["$permissions"] = this->permissions;
        }
        {
            j["functionId"] = this->functionId;
        }
        {
            j["deploymentId"] = this->deploymentId;
        }
        {
            j["trigger"] = appwrite::enums::toString(this->trigger);
        }
        {
            j["status"] = appwrite::enums::toString(this->status);
        }
        {
            j["requestMethod"] = this->requestMethod;
        }
        {
            j["requestPath"] = this->requestPath;
        }
        {
                nlohmann::json arr = nlohmann::json::array();
            for (auto& item : this->requestHeaders) arr.push_back(item.toJson());
            j["requestHeaders"] = arr;
            }
        {
            j["responseStatusCode"] = this->responseStatusCode;
        }
        {
            j["responseBody"] = this->responseBody;
        }
        {
                nlohmann::json arr = nlohmann::json::array();
            for (auto& item : this->responseHeaders) arr.push_back(item.toJson());
            j["responseHeaders"] = arr;
            }
        {
            j["logs"] = this->logs;
        }
        {
            j["errors"] = this->errors;
        }
        {
            j["duration"] = this->duration;
        }
        if (this->scheduledAt.has_value()) {
            j["scheduledAt"] = this->scheduledAt.value();
        }
        return j;
    }
};

/** @brief ADL hook for nlohmann::json */
inline void from_json(const nlohmann::json& j, Execution& x) {
    x = Execution::fromJson(j);
}

inline void to_json(nlohmann::json& j, const Execution& x) {
    j = x.toJson();
}

/**
 * @brief Executions List
 */
struct ExecutionList {
    /** @brief Total number of executions that matched your query. */
    int64_t total;
    /** @brief List of executions. */
    std::vector<appwrite::models::Execution> executions;

    /** @brief Deserialize from JSON */
    static ExecutionList fromJson(const nlohmann::json& j) {
        ExecutionList obj{}; // value-init: zeroes bools, ints, enums
        if (j.contains("total")) {
            if (j["total"].is_null()) {
                throw appwrite::DeserializationException("Required field 'total' is null");
            } else {
                obj.total = j["total"].get<int64_t>();
            }
        }
        if (j.contains("executions")) {
            if (j["executions"].is_null()) {
                throw appwrite::DeserializationException("Required field 'executions' is null");
            } else {
                                            for (auto& item : j["executions"]) {
                                    obj.executions.push_back(Execution::fromJson(item));
                                }
                        }
        }
        return obj;
    }

    /** @brief Serialize to JSON */
    [[nodiscard]] nlohmann::json toJson() const {
        nlohmann::json j = nlohmann::json::object();
        {
            j["total"] = this->total;
        }
        {
                nlohmann::json arr = nlohmann::json::array();
            for (auto& item : this->executions) arr.push_back(item.toJson());
            j["executions"] = arr;
            }
        return j;
    }
};

/** @brief ADL hook for nlohmann::json */
inline void from_json(const nlohmann::json& j, ExecutionList& x) {
    x = ExecutionList::fromJson(j);
}

inline void to_json(nlohmann::json& j, const ExecutionList& x) {
    j = x.toJson();
}

/**
 * @brief Webhook
 */
struct Webhook {
    /** @brief Webhook ID. */
    std::string id;
    /** @brief Webhook creation date in ISO 8601 format. */
    std::string createdAt;
    /** @brief Webhook update date in ISO 8601 format. */
    std::string updatedAt;
    /** @brief Webhook name. */
    std::string name;
    /** @brief Webhook URL endpoint. */
    std::string url;
    /** @brief Webhook trigger events. */
    std::vector<std::string> events;
    /** @brief Indicates if SSL / TLS certificate verification is enabled. */
    bool tls;
    /** @brief HTTP basic authentication username. */
    std::string authUsername;
    /** @brief HTTP basic authentication password. */
    std::string authPassword;
    /** @brief Signature key which can be used to validate incoming webhook payloads. Only returned on creation and secret rotation. */
    std::string secret;
    /** @brief Indicates if this webhook is enabled. */
    bool enabled;
    /** @brief Webhook error logs from the most recent failure. */
    std::string logs;
    /** @brief Number of consecutive failed webhook attempts. */
    int64_t attempts;

    /** @brief Deserialize from JSON */
    static Webhook fromJson(const nlohmann::json& j) {
        Webhook obj{}; // value-init: zeroes bools, ints, enums
        if (j.contains("$id")) {
            if (j["$id"].is_null()) {
                throw appwrite::DeserializationException("Required field '$id' is null");
            } else {
                obj.id = j["$id"].get<std::string>();
            }
        }
        if (j.contains("$createdAt")) {
            if (j["$createdAt"].is_null()) {
                throw appwrite::DeserializationException("Required field '$createdAt' is null");
            } else {
                obj.createdAt = j["$createdAt"].get<std::string>();
            }
        }
        if (j.contains("$updatedAt")) {
            if (j["$updatedAt"].is_null()) {
                throw appwrite::DeserializationException("Required field '$updatedAt' is null");
            } else {
                obj.updatedAt = j["$updatedAt"].get<std::string>();
            }
        }
        if (j.contains("name")) {
            if (j["name"].is_null()) {
                throw appwrite::DeserializationException("Required field 'name' is null");
            } else {
                obj.name = j["name"].get<std::string>();
            }
        }
        if (j.contains("url")) {
            if (j["url"].is_null()) {
                throw appwrite::DeserializationException("Required field 'url' is null");
            } else {
                obj.url = j["url"].get<std::string>();
            }
        }
        if (j.contains("events")) {
            if (j["events"].is_null()) {
                throw appwrite::DeserializationException("Required field 'events' is null");
            } else {
                obj.events = j["events"].get<std::vector<std::string>>();
            }
        }
        if (j.contains("tls")) {
            if (j["tls"].is_null()) {
                throw appwrite::DeserializationException("Required field 'tls' is null");
            } else {
                obj.tls = j["tls"].get<bool>();
            }
        }
        if (j.contains("authUsername")) {
            if (j["authUsername"].is_null()) {
                throw appwrite::DeserializationException("Required field 'authUsername' is null");
            } else {
                obj.authUsername = j["authUsername"].get<std::string>();
            }
        }
        if (j.contains("authPassword")) {
            if (j["authPassword"].is_null()) {
                throw appwrite::DeserializationException("Required field 'authPassword' is null");
            } else {
                obj.authPassword = j["authPassword"].get<std::string>();
            }
        }
        if (j.contains("secret")) {
            if (j["secret"].is_null()) {
                throw appwrite::DeserializationException("Required field 'secret' is null");
            } else {
                obj.secret = j["secret"].get<std::string>();
            }
        }
        if (j.contains("enabled")) {
            if (j["enabled"].is_null()) {
                throw appwrite::DeserializationException("Required field 'enabled' is null");
            } else {
                obj.enabled = j["enabled"].get<bool>();
            }
        }
        if (j.contains("logs")) {
            if (j["logs"].is_null()) {
                throw appwrite::DeserializationException("Required field 'logs' is null");
            } else {
                obj.logs = j["logs"].get<std::string>();
            }
        }
        if (j.contains("attempts")) {
            if (j["attempts"].is_null()) {
                throw appwrite::DeserializationException("Required field 'attempts' is null");
            } else {
                obj.attempts = j["attempts"].get<int64_t>();
            }
        }
        return obj;
    }

    /** @brief Serialize to JSON */
    [[nodiscard]] nlohmann::json toJson() const {
        nlohmann::json j = nlohmann::json::object();
        {
            j["$id"] = this->id;
        }
        {
            j["$createdAt"] = this->createdAt;
        }
        {
            j["$updatedAt"] = this->updatedAt;
        }
        {
            j["name"] = this->name;
        }
        {
            j["url"] = this->url;
        }
        {
            j["events"] = this->events;
        }
        {
            j["tls"] = this->tls;
        }
        {
            j["authUsername"] = this->authUsername;
        }
        {
            j["authPassword"] = this->authPassword;
        }
        {
            j["secret"] = this->secret;
        }
        {
            j["enabled"] = this->enabled;
        }
        {
            j["logs"] = this->logs;
        }
        {
            j["attempts"] = this->attempts;
        }
        return j;
    }
};

/** @brief ADL hook for nlohmann::json */
inline void from_json(const nlohmann::json& j, Webhook& x) {
    x = Webhook::fromJson(j);
}

inline void to_json(nlohmann::json& j, const Webhook& x) {
    j = x.toJson();
}

/**
 * @brief Webhooks List
 */
struct WebhookList {
    /** @brief Total number of webhooks that matched your query. */
    int64_t total;
    /** @brief List of webhooks. */
    std::vector<appwrite::models::Webhook> webhooks;

    /** @brief Deserialize from JSON */
    static WebhookList fromJson(const nlohmann::json& j) {
        WebhookList obj{}; // value-init: zeroes bools, ints, enums
        if (j.contains("total")) {
            if (j["total"].is_null()) {
                throw appwrite::DeserializationException("Required field 'total' is null");
            } else {
                obj.total = j["total"].get<int64_t>();
            }
        }
        if (j.contains("webhooks")) {
            if (j["webhooks"].is_null()) {
                throw appwrite::DeserializationException("Required field 'webhooks' is null");
            } else {
                                            for (auto& item : j["webhooks"]) {
                                    obj.webhooks.push_back(Webhook::fromJson(item));
                                }
                        }
        }
        return obj;
    }

    /** @brief Serialize to JSON */
    [[nodiscard]] nlohmann::json toJson() const {
        nlohmann::json j = nlohmann::json::object();
        {
            j["total"] = this->total;
        }
        {
                nlohmann::json arr = nlohmann::json::array();
            for (auto& item : this->webhooks) arr.push_back(item.toJson());
            j["webhooks"] = arr;
            }
        return j;
    }
};

/** @brief ADL hook for nlohmann::json */
inline void from_json(const nlohmann::json& j, WebhookList& x) {
    x = WebhookList::fromJson(j);
}

inline void to_json(nlohmann::json& j, const WebhookList& x) {
    j = x.toJson();
}

/**
 * @brief Variables List
 */
struct VariableList {
    /** @brief Total number of variables that matched your query. */
    int64_t total;
    /** @brief List of variables. */
    std::vector<appwrite::models::Variable> variables;

    /** @brief Deserialize from JSON */
    static VariableList fromJson(const nlohmann::json& j) {
        VariableList obj{}; // value-init: zeroes bools, ints, enums
        if (j.contains("total")) {
            if (j["total"].is_null()) {
                throw appwrite::DeserializationException("Required field 'total' is null");
            } else {
                obj.total = j["total"].get<int64_t>();
            }
        }
        if (j.contains("variables")) {
            if (j["variables"].is_null()) {
                throw appwrite::DeserializationException("Required field 'variables' is null");
            } else {
                                            for (auto& item : j["variables"]) {
                                    obj.variables.push_back(Variable::fromJson(item));
                                }
                        }
        }
        return obj;
    }

    /** @brief Serialize to JSON */
    [[nodiscard]] nlohmann::json toJson() const {
        nlohmann::json j = nlohmann::json::object();
        {
            j["total"] = this->total;
        }
        {
                nlohmann::json arr = nlohmann::json::array();
            for (auto& item : this->variables) arr.push_back(item.toJson());
            j["variables"] = arr;
            }
        return j;
    }
};

/** @brief ADL hook for nlohmann::json */
inline void from_json(const nlohmann::json& j, VariableList& x) {
    x = VariableList::fromJson(j);
}

inline void to_json(nlohmann::json& j, const VariableList& x) {
    j = x.toJson();
}

/**
 * @brief Target list
 */
struct TargetList {
    /** @brief Total number of targets that matched your query. */
    int64_t total;
    /** @brief List of targets. */
    std::vector<appwrite::models::Target> targets;

    /** @brief Deserialize from JSON */
    static TargetList fromJson(const nlohmann::json& j) {
        TargetList obj{}; // value-init: zeroes bools, ints, enums
        if (j.contains("total")) {
            if (j["total"].is_null()) {
                throw appwrite::DeserializationException("Required field 'total' is null");
            } else {
                obj.total = j["total"].get<int64_t>();
            }
        }
        if (j.contains("targets")) {
            if (j["targets"].is_null()) {
                throw appwrite::DeserializationException("Required field 'targets' is null");
            } else {
                                            for (auto& item : j["targets"]) {
                                    obj.targets.push_back(Target::fromJson(item));
                                }
                        }
        }
        return obj;
    }

    /** @brief Serialize to JSON */
    [[nodiscard]] nlohmann::json toJson() const {
        nlohmann::json j = nlohmann::json::object();
        {
            j["total"] = this->total;
        }
        {
                nlohmann::json arr = nlohmann::json::array();
            for (auto& item : this->targets) arr.push_back(item.toJson());
            j["targets"] = arr;
            }
        return j;
    }
};

/** @brief ADL hook for nlohmann::json */
inline void from_json(const nlohmann::json& j, TargetList& x) {
    x = TargetList::fromJson(j);
}

inline void to_json(nlohmann::json& j, const TargetList& x) {
    j = x.toJson();
}

/**
 * @brief Transaction
 */
struct Transaction {
    /** @brief Transaction ID. */
    std::string id;
    /** @brief Transaction creation time in ISO 8601 format. */
    std::string createdAt;
    /** @brief Transaction update date in ISO 8601 format. */
    std::string updatedAt;
    /** @brief Current status of the transaction. One of: pending, committing, committed, rolled_back, failed. */
    std::string status;
    /** @brief Number of operations in the transaction. */
    int64_t operations;
    /** @brief Expiration time in ISO 8601 format. */
    std::string expiresAt;

    /** @brief Deserialize from JSON */
    static Transaction fromJson(const nlohmann::json& j) {
        Transaction obj{}; // value-init: zeroes bools, ints, enums
        if (j.contains("$id")) {
            if (j["$id"].is_null()) {
                throw appwrite::DeserializationException("Required field '$id' is null");
            } else {
                obj.id = j["$id"].get<std::string>();
            }
        }
        if (j.contains("$createdAt")) {
            if (j["$createdAt"].is_null()) {
                throw appwrite::DeserializationException("Required field '$createdAt' is null");
            } else {
                obj.createdAt = j["$createdAt"].get<std::string>();
            }
        }
        if (j.contains("$updatedAt")) {
            if (j["$updatedAt"].is_null()) {
                throw appwrite::DeserializationException("Required field '$updatedAt' is null");
            } else {
                obj.updatedAt = j["$updatedAt"].get<std::string>();
            }
        }
        if (j.contains("status")) {
            if (j["status"].is_null()) {
                throw appwrite::DeserializationException("Required field 'status' is null");
            } else {
                obj.status = j["status"].get<std::string>();
            }
        }
        if (j.contains("operations")) {
            if (j["operations"].is_null()) {
                throw appwrite::DeserializationException("Required field 'operations' is null");
            } else {
                obj.operations = j["operations"].get<int64_t>();
            }
        }
        if (j.contains("expiresAt")) {
            if (j["expiresAt"].is_null()) {
                throw appwrite::DeserializationException("Required field 'expiresAt' is null");
            } else {
                obj.expiresAt = j["expiresAt"].get<std::string>();
            }
        }
        return obj;
    }

    /** @brief Serialize to JSON */
    [[nodiscard]] nlohmann::json toJson() const {
        nlohmann::json j = nlohmann::json::object();
        {
            j["$id"] = this->id;
        }
        {
            j["$createdAt"] = this->createdAt;
        }
        {
            j["$updatedAt"] = this->updatedAt;
        }
        {
            j["status"] = this->status;
        }
        {
            j["operations"] = this->operations;
        }
        {
            j["expiresAt"] = this->expiresAt;
        }
        return j;
    }
};

/** @brief ADL hook for nlohmann::json */
inline void from_json(const nlohmann::json& j, Transaction& x) {
    x = Transaction::fromJson(j);
}

inline void to_json(nlohmann::json& j, const Transaction& x) {
    j = x.toJson();
}

/**
 * @brief Transaction List
 */
struct TransactionList {
    /** @brief Total number of transactions that matched your query. */
    int64_t total;
    /** @brief List of transactions. */
    std::vector<appwrite::models::Transaction> transactions;

    /** @brief Deserialize from JSON */
    static TransactionList fromJson(const nlohmann::json& j) {
        TransactionList obj{}; // value-init: zeroes bools, ints, enums
        if (j.contains("total")) {
            if (j["total"].is_null()) {
                throw appwrite::DeserializationException("Required field 'total' is null");
            } else {
                obj.total = j["total"].get<int64_t>();
            }
        }
        if (j.contains("transactions")) {
            if (j["transactions"].is_null()) {
                throw appwrite::DeserializationException("Required field 'transactions' is null");
            } else {
                                            for (auto& item : j["transactions"]) {
                                    obj.transactions.push_back(Transaction::fromJson(item));
                                }
                        }
        }
        return obj;
    }

    /** @brief Serialize to JSON */
    [[nodiscard]] nlohmann::json toJson() const {
        nlohmann::json j = nlohmann::json::object();
        {
            j["total"] = this->total;
        }
        {
                nlohmann::json arr = nlohmann::json::array();
            for (auto& item : this->transactions) arr.push_back(item.toJson());
            j["transactions"] = arr;
            }
        return j;
    }
};

/** @brief ADL hook for nlohmann::json */
inline void from_json(const nlohmann::json& j, TransactionList& x) {
    x = TransactionList::fromJson(j);
}

inline void to_json(nlohmann::json& j, const TransactionList& x) {
    j = x.toJson();
}

/**
 * @brief Specification
 */
struct Specification {
    /** @brief Memory size in MB. */
    int64_t memory;
    /** @brief Number of CPUs. */
    double cpus;
    /** @brief Is size enabled. */
    bool enabled;
    /** @brief Size slug. */
    std::string slug;

    /** @brief Deserialize from JSON */
    static Specification fromJson(const nlohmann::json& j) {
        Specification obj{}; // value-init: zeroes bools, ints, enums
        if (j.contains("memory")) {
            if (j["memory"].is_null()) {
                throw appwrite::DeserializationException("Required field 'memory' is null");
            } else {
                obj.memory = j["memory"].get<int64_t>();
            }
        }
        if (j.contains("cpus")) {
            if (j["cpus"].is_null()) {
                throw appwrite::DeserializationException("Required field 'cpus' is null");
            } else {
                obj.cpus = j["cpus"].get<double>();
            }
        }
        if (j.contains("enabled")) {
            if (j["enabled"].is_null()) {
                throw appwrite::DeserializationException("Required field 'enabled' is null");
            } else {
                obj.enabled = j["enabled"].get<bool>();
            }
        }
        if (j.contains("slug")) {
            if (j["slug"].is_null()) {
                throw appwrite::DeserializationException("Required field 'slug' is null");
            } else {
                obj.slug = j["slug"].get<std::string>();
            }
        }
        return obj;
    }

    /** @brief Serialize to JSON */
    [[nodiscard]] nlohmann::json toJson() const {
        nlohmann::json j = nlohmann::json::object();
        {
            j["memory"] = this->memory;
        }
        {
            j["cpus"] = this->cpus;
        }
        {
            j["enabled"] = this->enabled;
        }
        {
            j["slug"] = this->slug;
        }
        return j;
    }
};

/** @brief ADL hook for nlohmann::json */
inline void from_json(const nlohmann::json& j, Specification& x) {
    x = Specification::fromJson(j);
}

inline void to_json(nlohmann::json& j, const Specification& x) {
    j = x.toJson();
}

/**
 * @brief Specifications List
 */
struct SpecificationList {
    /** @brief Total number of specifications that matched your query. */
    int64_t total;
    /** @brief List of specifications. */
    std::vector<appwrite::models::Specification> specifications;

    /** @brief Deserialize from JSON */
    static SpecificationList fromJson(const nlohmann::json& j) {
        SpecificationList obj{}; // value-init: zeroes bools, ints, enums
        if (j.contains("total")) {
            if (j["total"].is_null()) {
                throw appwrite::DeserializationException("Required field 'total' is null");
            } else {
                obj.total = j["total"].get<int64_t>();
            }
        }
        if (j.contains("specifications")) {
            if (j["specifications"].is_null()) {
                throw appwrite::DeserializationException("Required field 'specifications' is null");
            } else {
                                            for (auto& item : j["specifications"]) {
                                    obj.specifications.push_back(Specification::fromJson(item));
                                }
                        }
        }
        return obj;
    }

    /** @brief Serialize to JSON */
    [[nodiscard]] nlohmann::json toJson() const {
        nlohmann::json j = nlohmann::json::object();
        {
            j["total"] = this->total;
        }
        {
                nlohmann::json arr = nlohmann::json::array();
            for (auto& item : this->specifications) arr.push_back(item.toJson());
            j["specifications"] = arr;
            }
        return j;
    }
};

/** @brief ADL hook for nlohmann::json */
inline void from_json(const nlohmann::json& j, SpecificationList& x) {
    x = SpecificationList::fromJson(j);
}

inline void to_json(nlohmann::json& j, const SpecificationList& x) {
    j = x.toJson();
}

/**
 * @brief Attributes List
 */
struct AttributeList {
    /** @brief Total number of attributes in the given collection. */
    int64_t total;
    /** @brief List of attributes. */
    std::vector<std::string> attributes;

    /** @brief Deserialize from JSON */
    static AttributeList fromJson(const nlohmann::json& j) {
        AttributeList obj{}; // value-init: zeroes bools, ints, enums
        if (j.contains("total")) {
            if (j["total"].is_null()) {
                throw appwrite::DeserializationException("Required field 'total' is null");
            } else {
                obj.total = j["total"].get<int64_t>();
            }
        }
        if (j.contains("attributes")) {
            if (j["attributes"].is_null()) {
                throw appwrite::DeserializationException("Required field 'attributes' is null");
            } else {
                obj.attributes = j["attributes"].get<std::vector<std::string>>();
            }
        }
        return obj;
    }

    /** @brief Serialize to JSON */
    [[nodiscard]] nlohmann::json toJson() const {
        nlohmann::json j = nlohmann::json::object();
        {
            j["total"] = this->total;
        }
        {
            j["attributes"] = this->attributes;
        }
        return j;
    }
};

/** @brief ADL hook for nlohmann::json */
inline void from_json(const nlohmann::json& j, AttributeList& x) {
    x = AttributeList::fromJson(j);
}

inline void to_json(nlohmann::json& j, const AttributeList& x) {
    j = x.toJson();
}

/**
 * @brief AttributeString
 */
struct AttributeString {
    /** @brief Attribute Key. */
    std::string key;
    /** @brief Attribute type. */
    std::string type;
    /** @brief Attribute status. Possible values: `available`, `processing`, `deleting`, `stuck`, or `failed` */
    appwrite::enums::AttributeStatus status;
    /** @brief Error message. Displays error generated on failure of creating or deleting an attribute. */
    std::string error;
    /** @brief Is attribute required? */
    bool required;
    /** @brief Is attribute an array? */
    std::optional<bool> array = std::nullopt;
    /** @brief Attribute creation date in ISO 8601 format. */
    std::string createdAt;
    /** @brief Attribute update date in ISO 8601 format. */
    std::string updatedAt;
    /** @brief Attribute size. */
    int64_t size;
    /** @brief Default value for attribute when not provided. Cannot be set when attribute is required. */
    std::optional<std::string> default_ = std::nullopt;
    /** @brief Defines whether this attribute is encrypted or not. */
    std::optional<bool> encrypt = std::nullopt;

    /** @brief Deserialize from JSON */
    static AttributeString fromJson(const nlohmann::json& j) {
        AttributeString obj{}; // value-init: zeroes bools, ints, enums
        if (j.contains("key")) {
            if (j["key"].is_null()) {
                throw appwrite::DeserializationException("Required field 'key' is null");
            } else {
                obj.key = j["key"].get<std::string>();
            }
        }
        if (j.contains("type")) {
            if (j["type"].is_null()) {
                throw appwrite::DeserializationException("Required field 'type' is null");
            } else {
                obj.type = j["type"].get<std::string>();
            }
        }
        if (j.contains("status")) {
            if (j["status"].is_null()) {
                throw appwrite::DeserializationException("Required field 'status' is null");
            } else {
                obj.status = j["status"].get<appwrite::enums::AttributeStatus>();
            }
        }
        if (j.contains("error")) {
            if (j["error"].is_null()) {
                throw appwrite::DeserializationException("Required field 'error' is null");
            } else {
                obj.error = j["error"].get<std::string>();
            }
        }
        if (j.contains("required")) {
            if (j["required"].is_null()) {
                throw appwrite::DeserializationException("Required field 'required' is null");
            } else {
                obj.required = j["required"].get<bool>();
            }
        }
        if (j.contains("array")) {
            if (j["array"].is_null()) {
                obj.array = std::nullopt;
            } else {
                obj.array = j["array"].get<std::optional<bool>>();
            }
        }
        if (j.contains("$createdAt")) {
            if (j["$createdAt"].is_null()) {
                throw appwrite::DeserializationException("Required field '$createdAt' is null");
            } else {
                obj.createdAt = j["$createdAt"].get<std::string>();
            }
        }
        if (j.contains("$updatedAt")) {
            if (j["$updatedAt"].is_null()) {
                throw appwrite::DeserializationException("Required field '$updatedAt' is null");
            } else {
                obj.updatedAt = j["$updatedAt"].get<std::string>();
            }
        }
        if (j.contains("size")) {
            if (j["size"].is_null()) {
                throw appwrite::DeserializationException("Required field 'size' is null");
            } else {
                obj.size = j["size"].get<int64_t>();
            }
        }
        if (j.contains("default")) {
            if (j["default"].is_null()) {
                obj.default_ = std::nullopt;
            } else {
                obj.default_ = j["default"].get<std::optional<std::string>>();
            }
        }
        if (j.contains("encrypt")) {
            if (j["encrypt"].is_null()) {
                obj.encrypt = std::nullopt;
            } else {
                obj.encrypt = j["encrypt"].get<std::optional<bool>>();
            }
        }
        return obj;
    }

    /** @brief Serialize to JSON */
    [[nodiscard]] nlohmann::json toJson() const {
        nlohmann::json j = nlohmann::json::object();
        {
            j["key"] = this->key;
        }
        {
            j["type"] = this->type;
        }
        {
            j["status"] = appwrite::enums::toString(this->status);
        }
        {
            j["error"] = this->error;
        }
        {
            j["required"] = this->required;
        }
        if (this->array.has_value()) {
            j["array"] = this->array.value();
        }
        {
            j["$createdAt"] = this->createdAt;
        }
        {
            j["$updatedAt"] = this->updatedAt;
        }
        {
            j["size"] = this->size;
        }
        if (this->default_.has_value()) {
            j["default"] = this->default_.value();
        }
        if (this->encrypt.has_value()) {
            j["encrypt"] = this->encrypt.value();
        }
        return j;
    }
};

/** @brief ADL hook for nlohmann::json */
inline void from_json(const nlohmann::json& j, AttributeString& x) {
    x = AttributeString::fromJson(j);
}

inline void to_json(nlohmann::json& j, const AttributeString& x) {
    j = x.toJson();
}

/**
 * @brief AttributeInteger
 */
struct AttributeInteger {
    /** @brief Attribute Key. */
    std::string key;
    /** @brief Attribute type. */
    std::string type;
    /** @brief Attribute status. Possible values: `available`, `processing`, `deleting`, `stuck`, or `failed` */
    appwrite::enums::AttributeStatus status;
    /** @brief Error message. Displays error generated on failure of creating or deleting an attribute. */
    std::string error;
    /** @brief Is attribute required? */
    bool required;
    /** @brief Is attribute an array? */
    std::optional<bool> array = std::nullopt;
    /** @brief Attribute creation date in ISO 8601 format. */
    std::string createdAt;
    /** @brief Attribute update date in ISO 8601 format. */
    std::string updatedAt;
    /** @brief Minimum value to enforce for new documents. */
    std::optional<int64_t> min = std::nullopt;
    /** @brief Maximum value to enforce for new documents. */
    std::optional<int64_t> max = std::nullopt;
    /** @brief Default value for attribute when not provided. Cannot be set when attribute is required. */
    std::optional<int64_t> default_ = std::nullopt;

    /** @brief Deserialize from JSON */
    static AttributeInteger fromJson(const nlohmann::json& j) {
        AttributeInteger obj{}; // value-init: zeroes bools, ints, enums
        if (j.contains("key")) {
            if (j["key"].is_null()) {
                throw appwrite::DeserializationException("Required field 'key' is null");
            } else {
                obj.key = j["key"].get<std::string>();
            }
        }
        if (j.contains("type")) {
            if (j["type"].is_null()) {
                throw appwrite::DeserializationException("Required field 'type' is null");
            } else {
                obj.type = j["type"].get<std::string>();
            }
        }
        if (j.contains("status")) {
            if (j["status"].is_null()) {
                throw appwrite::DeserializationException("Required field 'status' is null");
            } else {
                obj.status = j["status"].get<appwrite::enums::AttributeStatus>();
            }
        }
        if (j.contains("error")) {
            if (j["error"].is_null()) {
                throw appwrite::DeserializationException("Required field 'error' is null");
            } else {
                obj.error = j["error"].get<std::string>();
            }
        }
        if (j.contains("required")) {
            if (j["required"].is_null()) {
                throw appwrite::DeserializationException("Required field 'required' is null");
            } else {
                obj.required = j["required"].get<bool>();
            }
        }
        if (j.contains("array")) {
            if (j["array"].is_null()) {
                obj.array = std::nullopt;
            } else {
                obj.array = j["array"].get<std::optional<bool>>();
            }
        }
        if (j.contains("$createdAt")) {
            if (j["$createdAt"].is_null()) {
                throw appwrite::DeserializationException("Required field '$createdAt' is null");
            } else {
                obj.createdAt = j["$createdAt"].get<std::string>();
            }
        }
        if (j.contains("$updatedAt")) {
            if (j["$updatedAt"].is_null()) {
                throw appwrite::DeserializationException("Required field '$updatedAt' is null");
            } else {
                obj.updatedAt = j["$updatedAt"].get<std::string>();
            }
        }
        if (j.contains("min")) {
            if (j["min"].is_null()) {
                obj.min = std::nullopt;
            } else {
                obj.min = j["min"].get<std::optional<int64_t>>();
            }
        }
        if (j.contains("max")) {
            if (j["max"].is_null()) {
                obj.max = std::nullopt;
            } else {
                obj.max = j["max"].get<std::optional<int64_t>>();
            }
        }
        if (j.contains("default")) {
            if (j["default"].is_null()) {
                obj.default_ = std::nullopt;
            } else {
                obj.default_ = j["default"].get<std::optional<int64_t>>();
            }
        }
        return obj;
    }

    /** @brief Serialize to JSON */
    [[nodiscard]] nlohmann::json toJson() const {
        nlohmann::json j = nlohmann::json::object();
        {
            j["key"] = this->key;
        }
        {
            j["type"] = this->type;
        }
        {
            j["status"] = appwrite::enums::toString(this->status);
        }
        {
            j["error"] = this->error;
        }
        {
            j["required"] = this->required;
        }
        if (this->array.has_value()) {
            j["array"] = this->array.value();
        }
        {
            j["$createdAt"] = this->createdAt;
        }
        {
            j["$updatedAt"] = this->updatedAt;
        }
        if (this->min.has_value()) {
            j["min"] = this->min.value();
        }
        if (this->max.has_value()) {
            j["max"] = this->max.value();
        }
        if (this->default_.has_value()) {
            j["default"] = this->default_.value();
        }
        return j;
    }
};

/** @brief ADL hook for nlohmann::json */
inline void from_json(const nlohmann::json& j, AttributeInteger& x) {
    x = AttributeInteger::fromJson(j);
}

inline void to_json(nlohmann::json& j, const AttributeInteger& x) {
    j = x.toJson();
}

/**
 * @brief AttributeFloat
 */
struct AttributeFloat {
    /** @brief Attribute Key. */
    std::string key;
    /** @brief Attribute type. */
    std::string type;
    /** @brief Attribute status. Possible values: `available`, `processing`, `deleting`, `stuck`, or `failed` */
    appwrite::enums::AttributeStatus status;
    /** @brief Error message. Displays error generated on failure of creating or deleting an attribute. */
    std::string error;
    /** @brief Is attribute required? */
    bool required;
    /** @brief Is attribute an array? */
    std::optional<bool> array = std::nullopt;
    /** @brief Attribute creation date in ISO 8601 format. */
    std::string createdAt;
    /** @brief Attribute update date in ISO 8601 format. */
    std::string updatedAt;
    /** @brief Minimum value to enforce for new documents. */
    std::optional<double> min = std::nullopt;
    /** @brief Maximum value to enforce for new documents. */
    std::optional<double> max = std::nullopt;
    /** @brief Default value for attribute when not provided. Cannot be set when attribute is required. */
    std::optional<double> default_ = std::nullopt;

    /** @brief Deserialize from JSON */
    static AttributeFloat fromJson(const nlohmann::json& j) {
        AttributeFloat obj{}; // value-init: zeroes bools, ints, enums
        if (j.contains("key")) {
            if (j["key"].is_null()) {
                throw appwrite::DeserializationException("Required field 'key' is null");
            } else {
                obj.key = j["key"].get<std::string>();
            }
        }
        if (j.contains("type")) {
            if (j["type"].is_null()) {
                throw appwrite::DeserializationException("Required field 'type' is null");
            } else {
                obj.type = j["type"].get<std::string>();
            }
        }
        if (j.contains("status")) {
            if (j["status"].is_null()) {
                throw appwrite::DeserializationException("Required field 'status' is null");
            } else {
                obj.status = j["status"].get<appwrite::enums::AttributeStatus>();
            }
        }
        if (j.contains("error")) {
            if (j["error"].is_null()) {
                throw appwrite::DeserializationException("Required field 'error' is null");
            } else {
                obj.error = j["error"].get<std::string>();
            }
        }
        if (j.contains("required")) {
            if (j["required"].is_null()) {
                throw appwrite::DeserializationException("Required field 'required' is null");
            } else {
                obj.required = j["required"].get<bool>();
            }
        }
        if (j.contains("array")) {
            if (j["array"].is_null()) {
                obj.array = std::nullopt;
            } else {
                obj.array = j["array"].get<std::optional<bool>>();
            }
        }
        if (j.contains("$createdAt")) {
            if (j["$createdAt"].is_null()) {
                throw appwrite::DeserializationException("Required field '$createdAt' is null");
            } else {
                obj.createdAt = j["$createdAt"].get<std::string>();
            }
        }
        if (j.contains("$updatedAt")) {
            if (j["$updatedAt"].is_null()) {
                throw appwrite::DeserializationException("Required field '$updatedAt' is null");
            } else {
                obj.updatedAt = j["$updatedAt"].get<std::string>();
            }
        }
        if (j.contains("min")) {
            if (j["min"].is_null()) {
                obj.min = std::nullopt;
            } else {
                obj.min = j["min"].get<std::optional<double>>();
            }
        }
        if (j.contains("max")) {
            if (j["max"].is_null()) {
                obj.max = std::nullopt;
            } else {
                obj.max = j["max"].get<std::optional<double>>();
            }
        }
        if (j.contains("default")) {
            if (j["default"].is_null()) {
                obj.default_ = std::nullopt;
            } else {
                obj.default_ = j["default"].get<std::optional<double>>();
            }
        }
        return obj;
    }

    /** @brief Serialize to JSON */
    [[nodiscard]] nlohmann::json toJson() const {
        nlohmann::json j = nlohmann::json::object();
        {
            j["key"] = this->key;
        }
        {
            j["type"] = this->type;
        }
        {
            j["status"] = appwrite::enums::toString(this->status);
        }
        {
            j["error"] = this->error;
        }
        {
            j["required"] = this->required;
        }
        if (this->array.has_value()) {
            j["array"] = this->array.value();
        }
        {
            j["$createdAt"] = this->createdAt;
        }
        {
            j["$updatedAt"] = this->updatedAt;
        }
        if (this->min.has_value()) {
            j["min"] = this->min.value();
        }
        if (this->max.has_value()) {
            j["max"] = this->max.value();
        }
        if (this->default_.has_value()) {
            j["default"] = this->default_.value();
        }
        return j;
    }
};

/** @brief ADL hook for nlohmann::json */
inline void from_json(const nlohmann::json& j, AttributeFloat& x) {
    x = AttributeFloat::fromJson(j);
}

inline void to_json(nlohmann::json& j, const AttributeFloat& x) {
    j = x.toJson();
}

/**
 * @brief AttributeBoolean
 */
struct AttributeBoolean {
    /** @brief Attribute Key. */
    std::string key;
    /** @brief Attribute type. */
    std::string type;
    /** @brief Attribute status. Possible values: `available`, `processing`, `deleting`, `stuck`, or `failed` */
    appwrite::enums::AttributeStatus status;
    /** @brief Error message. Displays error generated on failure of creating or deleting an attribute. */
    std::string error;
    /** @brief Is attribute required? */
    bool required;
    /** @brief Is attribute an array? */
    std::optional<bool> array = std::nullopt;
    /** @brief Attribute creation date in ISO 8601 format. */
    std::string createdAt;
    /** @brief Attribute update date in ISO 8601 format. */
    std::string updatedAt;
    /** @brief Default value for attribute when not provided. Cannot be set when attribute is required. */
    std::optional<bool> default_ = std::nullopt;

    /** @brief Deserialize from JSON */
    static AttributeBoolean fromJson(const nlohmann::json& j) {
        AttributeBoolean obj{}; // value-init: zeroes bools, ints, enums
        if (j.contains("key")) {
            if (j["key"].is_null()) {
                throw appwrite::DeserializationException("Required field 'key' is null");
            } else {
                obj.key = j["key"].get<std::string>();
            }
        }
        if (j.contains("type")) {
            if (j["type"].is_null()) {
                throw appwrite::DeserializationException("Required field 'type' is null");
            } else {
                obj.type = j["type"].get<std::string>();
            }
        }
        if (j.contains("status")) {
            if (j["status"].is_null()) {
                throw appwrite::DeserializationException("Required field 'status' is null");
            } else {
                obj.status = j["status"].get<appwrite::enums::AttributeStatus>();
            }
        }
        if (j.contains("error")) {
            if (j["error"].is_null()) {
                throw appwrite::DeserializationException("Required field 'error' is null");
            } else {
                obj.error = j["error"].get<std::string>();
            }
        }
        if (j.contains("required")) {
            if (j["required"].is_null()) {
                throw appwrite::DeserializationException("Required field 'required' is null");
            } else {
                obj.required = j["required"].get<bool>();
            }
        }
        if (j.contains("array")) {
            if (j["array"].is_null()) {
                obj.array = std::nullopt;
            } else {
                obj.array = j["array"].get<std::optional<bool>>();
            }
        }
        if (j.contains("$createdAt")) {
            if (j["$createdAt"].is_null()) {
                throw appwrite::DeserializationException("Required field '$createdAt' is null");
            } else {
                obj.createdAt = j["$createdAt"].get<std::string>();
            }
        }
        if (j.contains("$updatedAt")) {
            if (j["$updatedAt"].is_null()) {
                throw appwrite::DeserializationException("Required field '$updatedAt' is null");
            } else {
                obj.updatedAt = j["$updatedAt"].get<std::string>();
            }
        }
        if (j.contains("default")) {
            if (j["default"].is_null()) {
                obj.default_ = std::nullopt;
            } else {
                obj.default_ = j["default"].get<std::optional<bool>>();
            }
        }
        return obj;
    }

    /** @brief Serialize to JSON */
    [[nodiscard]] nlohmann::json toJson() const {
        nlohmann::json j = nlohmann::json::object();
        {
            j["key"] = this->key;
        }
        {
            j["type"] = this->type;
        }
        {
            j["status"] = appwrite::enums::toString(this->status);
        }
        {
            j["error"] = this->error;
        }
        {
            j["required"] = this->required;
        }
        if (this->array.has_value()) {
            j["array"] = this->array.value();
        }
        {
            j["$createdAt"] = this->createdAt;
        }
        {
            j["$updatedAt"] = this->updatedAt;
        }
        if (this->default_.has_value()) {
            j["default"] = this->default_.value();
        }
        return j;
    }
};

/** @brief ADL hook for nlohmann::json */
inline void from_json(const nlohmann::json& j, AttributeBoolean& x) {
    x = AttributeBoolean::fromJson(j);
}

inline void to_json(nlohmann::json& j, const AttributeBoolean& x) {
    j = x.toJson();
}

/**
 * @brief AttributeEmail
 */
struct AttributeEmail {
    /** @brief Attribute Key. */
    std::string key;
    /** @brief Attribute type. */
    std::string type;
    /** @brief Attribute status. Possible values: `available`, `processing`, `deleting`, `stuck`, or `failed` */
    appwrite::enums::AttributeStatus status;
    /** @brief Error message. Displays error generated on failure of creating or deleting an attribute. */
    std::string error;
    /** @brief Is attribute required? */
    bool required;
    /** @brief Is attribute an array? */
    std::optional<bool> array = std::nullopt;
    /** @brief Attribute creation date in ISO 8601 format. */
    std::string createdAt;
    /** @brief Attribute update date in ISO 8601 format. */
    std::string updatedAt;
    /** @brief String format. */
    std::string format;
    /** @brief Default value for attribute when not provided. Cannot be set when attribute is required. */
    std::optional<std::string> default_ = std::nullopt;

    /** @brief Deserialize from JSON */
    static AttributeEmail fromJson(const nlohmann::json& j) {
        AttributeEmail obj{}; // value-init: zeroes bools, ints, enums
        if (j.contains("key")) {
            if (j["key"].is_null()) {
                throw appwrite::DeserializationException("Required field 'key' is null");
            } else {
                obj.key = j["key"].get<std::string>();
            }
        }
        if (j.contains("type")) {
            if (j["type"].is_null()) {
                throw appwrite::DeserializationException("Required field 'type' is null");
            } else {
                obj.type = j["type"].get<std::string>();
            }
        }
        if (j.contains("status")) {
            if (j["status"].is_null()) {
                throw appwrite::DeserializationException("Required field 'status' is null");
            } else {
                obj.status = j["status"].get<appwrite::enums::AttributeStatus>();
            }
        }
        if (j.contains("error")) {
            if (j["error"].is_null()) {
                throw appwrite::DeserializationException("Required field 'error' is null");
            } else {
                obj.error = j["error"].get<std::string>();
            }
        }
        if (j.contains("required")) {
            if (j["required"].is_null()) {
                throw appwrite::DeserializationException("Required field 'required' is null");
            } else {
                obj.required = j["required"].get<bool>();
            }
        }
        if (j.contains("array")) {
            if (j["array"].is_null()) {
                obj.array = std::nullopt;
            } else {
                obj.array = j["array"].get<std::optional<bool>>();
            }
        }
        if (j.contains("$createdAt")) {
            if (j["$createdAt"].is_null()) {
                throw appwrite::DeserializationException("Required field '$createdAt' is null");
            } else {
                obj.createdAt = j["$createdAt"].get<std::string>();
            }
        }
        if (j.contains("$updatedAt")) {
            if (j["$updatedAt"].is_null()) {
                throw appwrite::DeserializationException("Required field '$updatedAt' is null");
            } else {
                obj.updatedAt = j["$updatedAt"].get<std::string>();
            }
        }
        if (j.contains("format")) {
            if (j["format"].is_null()) {
                throw appwrite::DeserializationException("Required field 'format' is null");
            } else {
                obj.format = j["format"].get<std::string>();
            }
        }
        if (j.contains("default")) {
            if (j["default"].is_null()) {
                obj.default_ = std::nullopt;
            } else {
                obj.default_ = j["default"].get<std::optional<std::string>>();
            }
        }
        return obj;
    }

    /** @brief Serialize to JSON */
    [[nodiscard]] nlohmann::json toJson() const {
        nlohmann::json j = nlohmann::json::object();
        {
            j["key"] = this->key;
        }
        {
            j["type"] = this->type;
        }
        {
            j["status"] = appwrite::enums::toString(this->status);
        }
        {
            j["error"] = this->error;
        }
        {
            j["required"] = this->required;
        }
        if (this->array.has_value()) {
            j["array"] = this->array.value();
        }
        {
            j["$createdAt"] = this->createdAt;
        }
        {
            j["$updatedAt"] = this->updatedAt;
        }
        {
            j["format"] = this->format;
        }
        if (this->default_.has_value()) {
            j["default"] = this->default_.value();
        }
        return j;
    }
};

/** @brief ADL hook for nlohmann::json */
inline void from_json(const nlohmann::json& j, AttributeEmail& x) {
    x = AttributeEmail::fromJson(j);
}

inline void to_json(nlohmann::json& j, const AttributeEmail& x) {
    j = x.toJson();
}

/**
 * @brief AttributeEnum
 */
struct AttributeEnum {
    /** @brief Attribute Key. */
    std::string key;
    /** @brief Attribute type. */
    std::string type;
    /** @brief Attribute status. Possible values: `available`, `processing`, `deleting`, `stuck`, or `failed` */
    appwrite::enums::AttributeStatus status;
    /** @brief Error message. Displays error generated on failure of creating or deleting an attribute. */
    std::string error;
    /** @brief Is attribute required? */
    bool required;
    /** @brief Is attribute an array? */
    std::optional<bool> array = std::nullopt;
    /** @brief Attribute creation date in ISO 8601 format. */
    std::string createdAt;
    /** @brief Attribute update date in ISO 8601 format. */
    std::string updatedAt;
    /** @brief Array of elements in enumerated type. */
    std::vector<std::string> elements;
    /** @brief String format. */
    std::string format;
    /** @brief Default value for attribute when not provided. Cannot be set when attribute is required. */
    std::optional<std::string> default_ = std::nullopt;

    /** @brief Deserialize from JSON */
    static AttributeEnum fromJson(const nlohmann::json& j) {
        AttributeEnum obj{}; // value-init: zeroes bools, ints, enums
        if (j.contains("key")) {
            if (j["key"].is_null()) {
                throw appwrite::DeserializationException("Required field 'key' is null");
            } else {
                obj.key = j["key"].get<std::string>();
            }
        }
        if (j.contains("type")) {
            if (j["type"].is_null()) {
                throw appwrite::DeserializationException("Required field 'type' is null");
            } else {
                obj.type = j["type"].get<std::string>();
            }
        }
        if (j.contains("status")) {
            if (j["status"].is_null()) {
                throw appwrite::DeserializationException("Required field 'status' is null");
            } else {
                obj.status = j["status"].get<appwrite::enums::AttributeStatus>();
            }
        }
        if (j.contains("error")) {
            if (j["error"].is_null()) {
                throw appwrite::DeserializationException("Required field 'error' is null");
            } else {
                obj.error = j["error"].get<std::string>();
            }
        }
        if (j.contains("required")) {
            if (j["required"].is_null()) {
                throw appwrite::DeserializationException("Required field 'required' is null");
            } else {
                obj.required = j["required"].get<bool>();
            }
        }
        if (j.contains("array")) {
            if (j["array"].is_null()) {
                obj.array = std::nullopt;
            } else {
                obj.array = j["array"].get<std::optional<bool>>();
            }
        }
        if (j.contains("$createdAt")) {
            if (j["$createdAt"].is_null()) {
                throw appwrite::DeserializationException("Required field '$createdAt' is null");
            } else {
                obj.createdAt = j["$createdAt"].get<std::string>();
            }
        }
        if (j.contains("$updatedAt")) {
            if (j["$updatedAt"].is_null()) {
                throw appwrite::DeserializationException("Required field '$updatedAt' is null");
            } else {
                obj.updatedAt = j["$updatedAt"].get<std::string>();
            }
        }
        if (j.contains("elements")) {
            if (j["elements"].is_null()) {
                throw appwrite::DeserializationException("Required field 'elements' is null");
            } else {
                obj.elements = j["elements"].get<std::vector<std::string>>();
            }
        }
        if (j.contains("format")) {
            if (j["format"].is_null()) {
                throw appwrite::DeserializationException("Required field 'format' is null");
            } else {
                obj.format = j["format"].get<std::string>();
            }
        }
        if (j.contains("default")) {
            if (j["default"].is_null()) {
                obj.default_ = std::nullopt;
            } else {
                obj.default_ = j["default"].get<std::optional<std::string>>();
            }
        }
        return obj;
    }

    /** @brief Serialize to JSON */
    [[nodiscard]] nlohmann::json toJson() const {
        nlohmann::json j = nlohmann::json::object();
        {
            j["key"] = this->key;
        }
        {
            j["type"] = this->type;
        }
        {
            j["status"] = appwrite::enums::toString(this->status);
        }
        {
            j["error"] = this->error;
        }
        {
            j["required"] = this->required;
        }
        if (this->array.has_value()) {
            j["array"] = this->array.value();
        }
        {
            j["$createdAt"] = this->createdAt;
        }
        {
            j["$updatedAt"] = this->updatedAt;
        }
        {
            j["elements"] = this->elements;
        }
        {
            j["format"] = this->format;
        }
        if (this->default_.has_value()) {
            j["default"] = this->default_.value();
        }
        return j;
    }
};

/** @brief ADL hook for nlohmann::json */
inline void from_json(const nlohmann::json& j, AttributeEnum& x) {
    x = AttributeEnum::fromJson(j);
}

inline void to_json(nlohmann::json& j, const AttributeEnum& x) {
    j = x.toJson();
}

/**
 * @brief AttributeIP
 */
struct AttributeIp {
    /** @brief Attribute Key. */
    std::string key;
    /** @brief Attribute type. */
    std::string type;
    /** @brief Attribute status. Possible values: `available`, `processing`, `deleting`, `stuck`, or `failed` */
    appwrite::enums::AttributeStatus status;
    /** @brief Error message. Displays error generated on failure of creating or deleting an attribute. */
    std::string error;
    /** @brief Is attribute required? */
    bool required;
    /** @brief Is attribute an array? */
    std::optional<bool> array = std::nullopt;
    /** @brief Attribute creation date in ISO 8601 format. */
    std::string createdAt;
    /** @brief Attribute update date in ISO 8601 format. */
    std::string updatedAt;
    /** @brief String format. */
    std::string format;
    /** @brief Default value for attribute when not provided. Cannot be set when attribute is required. */
    std::optional<std::string> default_ = std::nullopt;

    /** @brief Deserialize from JSON */
    static AttributeIp fromJson(const nlohmann::json& j) {
        AttributeIp obj{}; // value-init: zeroes bools, ints, enums
        if (j.contains("key")) {
            if (j["key"].is_null()) {
                throw appwrite::DeserializationException("Required field 'key' is null");
            } else {
                obj.key = j["key"].get<std::string>();
            }
        }
        if (j.contains("type")) {
            if (j["type"].is_null()) {
                throw appwrite::DeserializationException("Required field 'type' is null");
            } else {
                obj.type = j["type"].get<std::string>();
            }
        }
        if (j.contains("status")) {
            if (j["status"].is_null()) {
                throw appwrite::DeserializationException("Required field 'status' is null");
            } else {
                obj.status = j["status"].get<appwrite::enums::AttributeStatus>();
            }
        }
        if (j.contains("error")) {
            if (j["error"].is_null()) {
                throw appwrite::DeserializationException("Required field 'error' is null");
            } else {
                obj.error = j["error"].get<std::string>();
            }
        }
        if (j.contains("required")) {
            if (j["required"].is_null()) {
                throw appwrite::DeserializationException("Required field 'required' is null");
            } else {
                obj.required = j["required"].get<bool>();
            }
        }
        if (j.contains("array")) {
            if (j["array"].is_null()) {
                obj.array = std::nullopt;
            } else {
                obj.array = j["array"].get<std::optional<bool>>();
            }
        }
        if (j.contains("$createdAt")) {
            if (j["$createdAt"].is_null()) {
                throw appwrite::DeserializationException("Required field '$createdAt' is null");
            } else {
                obj.createdAt = j["$createdAt"].get<std::string>();
            }
        }
        if (j.contains("$updatedAt")) {
            if (j["$updatedAt"].is_null()) {
                throw appwrite::DeserializationException("Required field '$updatedAt' is null");
            } else {
                obj.updatedAt = j["$updatedAt"].get<std::string>();
            }
        }
        if (j.contains("format")) {
            if (j["format"].is_null()) {
                throw appwrite::DeserializationException("Required field 'format' is null");
            } else {
                obj.format = j["format"].get<std::string>();
            }
        }
        if (j.contains("default")) {
            if (j["default"].is_null()) {
                obj.default_ = std::nullopt;
            } else {
                obj.default_ = j["default"].get<std::optional<std::string>>();
            }
        }
        return obj;
    }

    /** @brief Serialize to JSON */
    [[nodiscard]] nlohmann::json toJson() const {
        nlohmann::json j = nlohmann::json::object();
        {
            j["key"] = this->key;
        }
        {
            j["type"] = this->type;
        }
        {
            j["status"] = appwrite::enums::toString(this->status);
        }
        {
            j["error"] = this->error;
        }
        {
            j["required"] = this->required;
        }
        if (this->array.has_value()) {
            j["array"] = this->array.value();
        }
        {
            j["$createdAt"] = this->createdAt;
        }
        {
            j["$updatedAt"] = this->updatedAt;
        }
        {
            j["format"] = this->format;
        }
        if (this->default_.has_value()) {
            j["default"] = this->default_.value();
        }
        return j;
    }
};

/** @brief ADL hook for nlohmann::json */
inline void from_json(const nlohmann::json& j, AttributeIp& x) {
    x = AttributeIp::fromJson(j);
}

inline void to_json(nlohmann::json& j, const AttributeIp& x) {
    j = x.toJson();
}

/**
 * @brief AttributeURL
 */
struct AttributeUrl {
    /** @brief Attribute Key. */
    std::string key;
    /** @brief Attribute type. */
    std::string type;
    /** @brief Attribute status. Possible values: `available`, `processing`, `deleting`, `stuck`, or `failed` */
    appwrite::enums::AttributeStatus status;
    /** @brief Error message. Displays error generated on failure of creating or deleting an attribute. */
    std::string error;
    /** @brief Is attribute required? */
    bool required;
    /** @brief Is attribute an array? */
    std::optional<bool> array = std::nullopt;
    /** @brief Attribute creation date in ISO 8601 format. */
    std::string createdAt;
    /** @brief Attribute update date in ISO 8601 format. */
    std::string updatedAt;
    /** @brief String format. */
    std::string format;
    /** @brief Default value for attribute when not provided. Cannot be set when attribute is required. */
    std::optional<std::string> default_ = std::nullopt;

    /** @brief Deserialize from JSON */
    static AttributeUrl fromJson(const nlohmann::json& j) {
        AttributeUrl obj{}; // value-init: zeroes bools, ints, enums
        if (j.contains("key")) {
            if (j["key"].is_null()) {
                throw appwrite::DeserializationException("Required field 'key' is null");
            } else {
                obj.key = j["key"].get<std::string>();
            }
        }
        if (j.contains("type")) {
            if (j["type"].is_null()) {
                throw appwrite::DeserializationException("Required field 'type' is null");
            } else {
                obj.type = j["type"].get<std::string>();
            }
        }
        if (j.contains("status")) {
            if (j["status"].is_null()) {
                throw appwrite::DeserializationException("Required field 'status' is null");
            } else {
                obj.status = j["status"].get<appwrite::enums::AttributeStatus>();
            }
        }
        if (j.contains("error")) {
            if (j["error"].is_null()) {
                throw appwrite::DeserializationException("Required field 'error' is null");
            } else {
                obj.error = j["error"].get<std::string>();
            }
        }
        if (j.contains("required")) {
            if (j["required"].is_null()) {
                throw appwrite::DeserializationException("Required field 'required' is null");
            } else {
                obj.required = j["required"].get<bool>();
            }
        }
        if (j.contains("array")) {
            if (j["array"].is_null()) {
                obj.array = std::nullopt;
            } else {
                obj.array = j["array"].get<std::optional<bool>>();
            }
        }
        if (j.contains("$createdAt")) {
            if (j["$createdAt"].is_null()) {
                throw appwrite::DeserializationException("Required field '$createdAt' is null");
            } else {
                obj.createdAt = j["$createdAt"].get<std::string>();
            }
        }
        if (j.contains("$updatedAt")) {
            if (j["$updatedAt"].is_null()) {
                throw appwrite::DeserializationException("Required field '$updatedAt' is null");
            } else {
                obj.updatedAt = j["$updatedAt"].get<std::string>();
            }
        }
        if (j.contains("format")) {
            if (j["format"].is_null()) {
                throw appwrite::DeserializationException("Required field 'format' is null");
            } else {
                obj.format = j["format"].get<std::string>();
            }
        }
        if (j.contains("default")) {
            if (j["default"].is_null()) {
                obj.default_ = std::nullopt;
            } else {
                obj.default_ = j["default"].get<std::optional<std::string>>();
            }
        }
        return obj;
    }

    /** @brief Serialize to JSON */
    [[nodiscard]] nlohmann::json toJson() const {
        nlohmann::json j = nlohmann::json::object();
        {
            j["key"] = this->key;
        }
        {
            j["type"] = this->type;
        }
        {
            j["status"] = appwrite::enums::toString(this->status);
        }
        {
            j["error"] = this->error;
        }
        {
            j["required"] = this->required;
        }
        if (this->array.has_value()) {
            j["array"] = this->array.value();
        }
        {
            j["$createdAt"] = this->createdAt;
        }
        {
            j["$updatedAt"] = this->updatedAt;
        }
        {
            j["format"] = this->format;
        }
        if (this->default_.has_value()) {
            j["default"] = this->default_.value();
        }
        return j;
    }
};

/** @brief ADL hook for nlohmann::json */
inline void from_json(const nlohmann::json& j, AttributeUrl& x) {
    x = AttributeUrl::fromJson(j);
}

inline void to_json(nlohmann::json& j, const AttributeUrl& x) {
    j = x.toJson();
}

/**
 * @brief AttributeDatetime
 */
struct AttributeDatetime {
    /** @brief Attribute Key. */
    std::string key;
    /** @brief Attribute type. */
    std::string type;
    /** @brief Attribute status. Possible values: `available`, `processing`, `deleting`, `stuck`, or `failed` */
    appwrite::enums::AttributeStatus status;
    /** @brief Error message. Displays error generated on failure of creating or deleting an attribute. */
    std::string error;
    /** @brief Is attribute required? */
    bool required;
    /** @brief Is attribute an array? */
    std::optional<bool> array = std::nullopt;
    /** @brief Attribute creation date in ISO 8601 format. */
    std::string createdAt;
    /** @brief Attribute update date in ISO 8601 format. */
    std::string updatedAt;
    /** @brief ISO 8601 format. */
    std::string format;
    /** @brief Default value for attribute when not provided. Only null is optional */
    std::optional<std::string> default_ = std::nullopt;

    /** @brief Deserialize from JSON */
    static AttributeDatetime fromJson(const nlohmann::json& j) {
        AttributeDatetime obj{}; // value-init: zeroes bools, ints, enums
        if (j.contains("key")) {
            if (j["key"].is_null()) {
                throw appwrite::DeserializationException("Required field 'key' is null");
            } else {
                obj.key = j["key"].get<std::string>();
            }
        }
        if (j.contains("type")) {
            if (j["type"].is_null()) {
                throw appwrite::DeserializationException("Required field 'type' is null");
            } else {
                obj.type = j["type"].get<std::string>();
            }
        }
        if (j.contains("status")) {
            if (j["status"].is_null()) {
                throw appwrite::DeserializationException("Required field 'status' is null");
            } else {
                obj.status = j["status"].get<appwrite::enums::AttributeStatus>();
            }
        }
        if (j.contains("error")) {
            if (j["error"].is_null()) {
                throw appwrite::DeserializationException("Required field 'error' is null");
            } else {
                obj.error = j["error"].get<std::string>();
            }
        }
        if (j.contains("required")) {
            if (j["required"].is_null()) {
                throw appwrite::DeserializationException("Required field 'required' is null");
            } else {
                obj.required = j["required"].get<bool>();
            }
        }
        if (j.contains("array")) {
            if (j["array"].is_null()) {
                obj.array = std::nullopt;
            } else {
                obj.array = j["array"].get<std::optional<bool>>();
            }
        }
        if (j.contains("$createdAt")) {
            if (j["$createdAt"].is_null()) {
                throw appwrite::DeserializationException("Required field '$createdAt' is null");
            } else {
                obj.createdAt = j["$createdAt"].get<std::string>();
            }
        }
        if (j.contains("$updatedAt")) {
            if (j["$updatedAt"].is_null()) {
                throw appwrite::DeserializationException("Required field '$updatedAt' is null");
            } else {
                obj.updatedAt = j["$updatedAt"].get<std::string>();
            }
        }
        if (j.contains("format")) {
            if (j["format"].is_null()) {
                throw appwrite::DeserializationException("Required field 'format' is null");
            } else {
                obj.format = j["format"].get<std::string>();
            }
        }
        if (j.contains("default")) {
            if (j["default"].is_null()) {
                obj.default_ = std::nullopt;
            } else {
                obj.default_ = j["default"].get<std::optional<std::string>>();
            }
        }
        return obj;
    }

    /** @brief Serialize to JSON */
    [[nodiscard]] nlohmann::json toJson() const {
        nlohmann::json j = nlohmann::json::object();
        {
            j["key"] = this->key;
        }
        {
            j["type"] = this->type;
        }
        {
            j["status"] = appwrite::enums::toString(this->status);
        }
        {
            j["error"] = this->error;
        }
        {
            j["required"] = this->required;
        }
        if (this->array.has_value()) {
            j["array"] = this->array.value();
        }
        {
            j["$createdAt"] = this->createdAt;
        }
        {
            j["$updatedAt"] = this->updatedAt;
        }
        {
            j["format"] = this->format;
        }
        if (this->default_.has_value()) {
            j["default"] = this->default_.value();
        }
        return j;
    }
};

/** @brief ADL hook for nlohmann::json */
inline void from_json(const nlohmann::json& j, AttributeDatetime& x) {
    x = AttributeDatetime::fromJson(j);
}

inline void to_json(nlohmann::json& j, const AttributeDatetime& x) {
    j = x.toJson();
}

/**
 * @brief AttributeRelationship
 */
struct AttributeRelationship {
    /** @brief Attribute Key. */
    std::string key;
    /** @brief Attribute type. */
    std::string type;
    /** @brief Attribute status. Possible values: `available`, `processing`, `deleting`, `stuck`, or `failed` */
    appwrite::enums::AttributeStatus status;
    /** @brief Error message. Displays error generated on failure of creating or deleting an attribute. */
    std::string error;
    /** @brief Is attribute required? */
    bool required;
    /** @brief Is attribute an array? */
    std::optional<bool> array = std::nullopt;
    /** @brief Attribute creation date in ISO 8601 format. */
    std::string createdAt;
    /** @brief Attribute update date in ISO 8601 format. */
    std::string updatedAt;
    /** @brief The ID of the related collection. */
    std::string relatedCollection;
    /** @brief The type of the relationship. */
    std::string relationType;
    /** @brief Is the relationship two-way? */
    bool twoWay;
    /** @brief The key of the two-way relationship. */
    std::string twoWayKey;
    /** @brief How deleting the parent document will propagate to child documents. */
    std::string onDelete;
    /** @brief Whether this is the parent or child side of the relationship */
    std::string side;

    /** @brief Deserialize from JSON */
    static AttributeRelationship fromJson(const nlohmann::json& j) {
        AttributeRelationship obj{}; // value-init: zeroes bools, ints, enums
        if (j.contains("key")) {
            if (j["key"].is_null()) {
                throw appwrite::DeserializationException("Required field 'key' is null");
            } else {
                obj.key = j["key"].get<std::string>();
            }
        }
        if (j.contains("type")) {
            if (j["type"].is_null()) {
                throw appwrite::DeserializationException("Required field 'type' is null");
            } else {
                obj.type = j["type"].get<std::string>();
            }
        }
        if (j.contains("status")) {
            if (j["status"].is_null()) {
                throw appwrite::DeserializationException("Required field 'status' is null");
            } else {
                obj.status = j["status"].get<appwrite::enums::AttributeStatus>();
            }
        }
        if (j.contains("error")) {
            if (j["error"].is_null()) {
                throw appwrite::DeserializationException("Required field 'error' is null");
            } else {
                obj.error = j["error"].get<std::string>();
            }
        }
        if (j.contains("required")) {
            if (j["required"].is_null()) {
                throw appwrite::DeserializationException("Required field 'required' is null");
            } else {
                obj.required = j["required"].get<bool>();
            }
        }
        if (j.contains("array")) {
            if (j["array"].is_null()) {
                obj.array = std::nullopt;
            } else {
                obj.array = j["array"].get<std::optional<bool>>();
            }
        }
        if (j.contains("$createdAt")) {
            if (j["$createdAt"].is_null()) {
                throw appwrite::DeserializationException("Required field '$createdAt' is null");
            } else {
                obj.createdAt = j["$createdAt"].get<std::string>();
            }
        }
        if (j.contains("$updatedAt")) {
            if (j["$updatedAt"].is_null()) {
                throw appwrite::DeserializationException("Required field '$updatedAt' is null");
            } else {
                obj.updatedAt = j["$updatedAt"].get<std::string>();
            }
        }
        if (j.contains("relatedCollection")) {
            if (j["relatedCollection"].is_null()) {
                throw appwrite::DeserializationException("Required field 'relatedCollection' is null");
            } else {
                obj.relatedCollection = j["relatedCollection"].get<std::string>();
            }
        }
        if (j.contains("relationType")) {
            if (j["relationType"].is_null()) {
                throw appwrite::DeserializationException("Required field 'relationType' is null");
            } else {
                obj.relationType = j["relationType"].get<std::string>();
            }
        }
        if (j.contains("twoWay")) {
            if (j["twoWay"].is_null()) {
                throw appwrite::DeserializationException("Required field 'twoWay' is null");
            } else {
                obj.twoWay = j["twoWay"].get<bool>();
            }
        }
        if (j.contains("twoWayKey")) {
            if (j["twoWayKey"].is_null()) {
                throw appwrite::DeserializationException("Required field 'twoWayKey' is null");
            } else {
                obj.twoWayKey = j["twoWayKey"].get<std::string>();
            }
        }
        if (j.contains("onDelete")) {
            if (j["onDelete"].is_null()) {
                throw appwrite::DeserializationException("Required field 'onDelete' is null");
            } else {
                obj.onDelete = j["onDelete"].get<std::string>();
            }
        }
        if (j.contains("side")) {
            if (j["side"].is_null()) {
                throw appwrite::DeserializationException("Required field 'side' is null");
            } else {
                obj.side = j["side"].get<std::string>();
            }
        }
        return obj;
    }

    /** @brief Serialize to JSON */
    [[nodiscard]] nlohmann::json toJson() const {
        nlohmann::json j = nlohmann::json::object();
        {
            j["key"] = this->key;
        }
        {
            j["type"] = this->type;
        }
        {
            j["status"] = appwrite::enums::toString(this->status);
        }
        {
            j["error"] = this->error;
        }
        {
            j["required"] = this->required;
        }
        if (this->array.has_value()) {
            j["array"] = this->array.value();
        }
        {
            j["$createdAt"] = this->createdAt;
        }
        {
            j["$updatedAt"] = this->updatedAt;
        }
        {
            j["relatedCollection"] = this->relatedCollection;
        }
        {
            j["relationType"] = this->relationType;
        }
        {
            j["twoWay"] = this->twoWay;
        }
        {
            j["twoWayKey"] = this->twoWayKey;
        }
        {
            j["onDelete"] = this->onDelete;
        }
        {
            j["side"] = this->side;
        }
        return j;
    }
};

/** @brief ADL hook for nlohmann::json */
inline void from_json(const nlohmann::json& j, AttributeRelationship& x) {
    x = AttributeRelationship::fromJson(j);
}

inline void to_json(nlohmann::json& j, const AttributeRelationship& x) {
    j = x.toJson();
}

/**
 * @brief AttributePoint
 */
struct AttributePoint {
    /** @brief Attribute Key. */
    std::string key;
    /** @brief Attribute type. */
    std::string type;
    /** @brief Attribute status. Possible values: `available`, `processing`, `deleting`, `stuck`, or `failed` */
    appwrite::enums::AttributeStatus status;
    /** @brief Error message. Displays error generated on failure of creating or deleting an attribute. */
    std::string error;
    /** @brief Is attribute required? */
    bool required;
    /** @brief Is attribute an array? */
    std::optional<bool> array = std::nullopt;
    /** @brief Attribute creation date in ISO 8601 format. */
    std::string createdAt;
    /** @brief Attribute update date in ISO 8601 format. */
    std::string updatedAt;
    /** @brief Default value for attribute when not provided. Cannot be set when attribute is required. */
    std::optional<std::vector<std::string>> default_ = std::nullopt;

    /** @brief Deserialize from JSON */
    static AttributePoint fromJson(const nlohmann::json& j) {
        AttributePoint obj{}; // value-init: zeroes bools, ints, enums
        if (j.contains("key")) {
            if (j["key"].is_null()) {
                throw appwrite::DeserializationException("Required field 'key' is null");
            } else {
                obj.key = j["key"].get<std::string>();
            }
        }
        if (j.contains("type")) {
            if (j["type"].is_null()) {
                throw appwrite::DeserializationException("Required field 'type' is null");
            } else {
                obj.type = j["type"].get<std::string>();
            }
        }
        if (j.contains("status")) {
            if (j["status"].is_null()) {
                throw appwrite::DeserializationException("Required field 'status' is null");
            } else {
                obj.status = j["status"].get<appwrite::enums::AttributeStatus>();
            }
        }
        if (j.contains("error")) {
            if (j["error"].is_null()) {
                throw appwrite::DeserializationException("Required field 'error' is null");
            } else {
                obj.error = j["error"].get<std::string>();
            }
        }
        if (j.contains("required")) {
            if (j["required"].is_null()) {
                throw appwrite::DeserializationException("Required field 'required' is null");
            } else {
                obj.required = j["required"].get<bool>();
            }
        }
        if (j.contains("array")) {
            if (j["array"].is_null()) {
                obj.array = std::nullopt;
            } else {
                obj.array = j["array"].get<std::optional<bool>>();
            }
        }
        if (j.contains("$createdAt")) {
            if (j["$createdAt"].is_null()) {
                throw appwrite::DeserializationException("Required field '$createdAt' is null");
            } else {
                obj.createdAt = j["$createdAt"].get<std::string>();
            }
        }
        if (j.contains("$updatedAt")) {
            if (j["$updatedAt"].is_null()) {
                throw appwrite::DeserializationException("Required field '$updatedAt' is null");
            } else {
                obj.updatedAt = j["$updatedAt"].get<std::string>();
            }
        }
        if (j.contains("default")) {
            if (j["default"].is_null()) {
                obj.default_ = std::nullopt;
            } else {
                obj.default_ = j["default"].get<std::optional<std::vector<std::string>>>();
            }
        }
        return obj;
    }

    /** @brief Serialize to JSON */
    [[nodiscard]] nlohmann::json toJson() const {
        nlohmann::json j = nlohmann::json::object();
        {
            j["key"] = this->key;
        }
        {
            j["type"] = this->type;
        }
        {
            j["status"] = appwrite::enums::toString(this->status);
        }
        {
            j["error"] = this->error;
        }
        {
            j["required"] = this->required;
        }
        if (this->array.has_value()) {
            j["array"] = this->array.value();
        }
        {
            j["$createdAt"] = this->createdAt;
        }
        {
            j["$updatedAt"] = this->updatedAt;
        }
        if (this->default_.has_value()) {
            j["default"] = this->default_.value();
        }
        return j;
    }
};

/** @brief ADL hook for nlohmann::json */
inline void from_json(const nlohmann::json& j, AttributePoint& x) {
    x = AttributePoint::fromJson(j);
}

inline void to_json(nlohmann::json& j, const AttributePoint& x) {
    j = x.toJson();
}

/**
 * @brief AttributeLine
 */
struct AttributeLine {
    /** @brief Attribute Key. */
    std::string key;
    /** @brief Attribute type. */
    std::string type;
    /** @brief Attribute status. Possible values: `available`, `processing`, `deleting`, `stuck`, or `failed` */
    appwrite::enums::AttributeStatus status;
    /** @brief Error message. Displays error generated on failure of creating or deleting an attribute. */
    std::string error;
    /** @brief Is attribute required? */
    bool required;
    /** @brief Is attribute an array? */
    std::optional<bool> array = std::nullopt;
    /** @brief Attribute creation date in ISO 8601 format. */
    std::string createdAt;
    /** @brief Attribute update date in ISO 8601 format. */
    std::string updatedAt;
    /** @brief Default value for attribute when not provided. Cannot be set when attribute is required. */
    std::optional<std::vector<std::string>> default_ = std::nullopt;

    /** @brief Deserialize from JSON */
    static AttributeLine fromJson(const nlohmann::json& j) {
        AttributeLine obj{}; // value-init: zeroes bools, ints, enums
        if (j.contains("key")) {
            if (j["key"].is_null()) {
                throw appwrite::DeserializationException("Required field 'key' is null");
            } else {
                obj.key = j["key"].get<std::string>();
            }
        }
        if (j.contains("type")) {
            if (j["type"].is_null()) {
                throw appwrite::DeserializationException("Required field 'type' is null");
            } else {
                obj.type = j["type"].get<std::string>();
            }
        }
        if (j.contains("status")) {
            if (j["status"].is_null()) {
                throw appwrite::DeserializationException("Required field 'status' is null");
            } else {
                obj.status = j["status"].get<appwrite::enums::AttributeStatus>();
            }
        }
        if (j.contains("error")) {
            if (j["error"].is_null()) {
                throw appwrite::DeserializationException("Required field 'error' is null");
            } else {
                obj.error = j["error"].get<std::string>();
            }
        }
        if (j.contains("required")) {
            if (j["required"].is_null()) {
                throw appwrite::DeserializationException("Required field 'required' is null");
            } else {
                obj.required = j["required"].get<bool>();
            }
        }
        if (j.contains("array")) {
            if (j["array"].is_null()) {
                obj.array = std::nullopt;
            } else {
                obj.array = j["array"].get<std::optional<bool>>();
            }
        }
        if (j.contains("$createdAt")) {
            if (j["$createdAt"].is_null()) {
                throw appwrite::DeserializationException("Required field '$createdAt' is null");
            } else {
                obj.createdAt = j["$createdAt"].get<std::string>();
            }
        }
        if (j.contains("$updatedAt")) {
            if (j["$updatedAt"].is_null()) {
                throw appwrite::DeserializationException("Required field '$updatedAt' is null");
            } else {
                obj.updatedAt = j["$updatedAt"].get<std::string>();
            }
        }
        if (j.contains("default")) {
            if (j["default"].is_null()) {
                obj.default_ = std::nullopt;
            } else {
                obj.default_ = j["default"].get<std::optional<std::vector<std::string>>>();
            }
        }
        return obj;
    }

    /** @brief Serialize to JSON */
    [[nodiscard]] nlohmann::json toJson() const {
        nlohmann::json j = nlohmann::json::object();
        {
            j["key"] = this->key;
        }
        {
            j["type"] = this->type;
        }
        {
            j["status"] = appwrite::enums::toString(this->status);
        }
        {
            j["error"] = this->error;
        }
        {
            j["required"] = this->required;
        }
        if (this->array.has_value()) {
            j["array"] = this->array.value();
        }
        {
            j["$createdAt"] = this->createdAt;
        }
        {
            j["$updatedAt"] = this->updatedAt;
        }
        if (this->default_.has_value()) {
            j["default"] = this->default_.value();
        }
        return j;
    }
};

/** @brief ADL hook for nlohmann::json */
inline void from_json(const nlohmann::json& j, AttributeLine& x) {
    x = AttributeLine::fromJson(j);
}

inline void to_json(nlohmann::json& j, const AttributeLine& x) {
    j = x.toJson();
}

/**
 * @brief AttributePolygon
 */
struct AttributePolygon {
    /** @brief Attribute Key. */
    std::string key;
    /** @brief Attribute type. */
    std::string type;
    /** @brief Attribute status. Possible values: `available`, `processing`, `deleting`, `stuck`, or `failed` */
    appwrite::enums::AttributeStatus status;
    /** @brief Error message. Displays error generated on failure of creating or deleting an attribute. */
    std::string error;
    /** @brief Is attribute required? */
    bool required;
    /** @brief Is attribute an array? */
    std::optional<bool> array = std::nullopt;
    /** @brief Attribute creation date in ISO 8601 format. */
    std::string createdAt;
    /** @brief Attribute update date in ISO 8601 format. */
    std::string updatedAt;
    /** @brief Default value for attribute when not provided. Cannot be set when attribute is required. */
    std::optional<std::vector<std::string>> default_ = std::nullopt;

    /** @brief Deserialize from JSON */
    static AttributePolygon fromJson(const nlohmann::json& j) {
        AttributePolygon obj{}; // value-init: zeroes bools, ints, enums
        if (j.contains("key")) {
            if (j["key"].is_null()) {
                throw appwrite::DeserializationException("Required field 'key' is null");
            } else {
                obj.key = j["key"].get<std::string>();
            }
        }
        if (j.contains("type")) {
            if (j["type"].is_null()) {
                throw appwrite::DeserializationException("Required field 'type' is null");
            } else {
                obj.type = j["type"].get<std::string>();
            }
        }
        if (j.contains("status")) {
            if (j["status"].is_null()) {
                throw appwrite::DeserializationException("Required field 'status' is null");
            } else {
                obj.status = j["status"].get<appwrite::enums::AttributeStatus>();
            }
        }
        if (j.contains("error")) {
            if (j["error"].is_null()) {
                throw appwrite::DeserializationException("Required field 'error' is null");
            } else {
                obj.error = j["error"].get<std::string>();
            }
        }
        if (j.contains("required")) {
            if (j["required"].is_null()) {
                throw appwrite::DeserializationException("Required field 'required' is null");
            } else {
                obj.required = j["required"].get<bool>();
            }
        }
        if (j.contains("array")) {
            if (j["array"].is_null()) {
                obj.array = std::nullopt;
            } else {
                obj.array = j["array"].get<std::optional<bool>>();
            }
        }
        if (j.contains("$createdAt")) {
            if (j["$createdAt"].is_null()) {
                throw appwrite::DeserializationException("Required field '$createdAt' is null");
            } else {
                obj.createdAt = j["$createdAt"].get<std::string>();
            }
        }
        if (j.contains("$updatedAt")) {
            if (j["$updatedAt"].is_null()) {
                throw appwrite::DeserializationException("Required field '$updatedAt' is null");
            } else {
                obj.updatedAt = j["$updatedAt"].get<std::string>();
            }
        }
        if (j.contains("default")) {
            if (j["default"].is_null()) {
                obj.default_ = std::nullopt;
            } else {
                obj.default_ = j["default"].get<std::optional<std::vector<std::string>>>();
            }
        }
        return obj;
    }

    /** @brief Serialize to JSON */
    [[nodiscard]] nlohmann::json toJson() const {
        nlohmann::json j = nlohmann::json::object();
        {
            j["key"] = this->key;
        }
        {
            j["type"] = this->type;
        }
        {
            j["status"] = appwrite::enums::toString(this->status);
        }
        {
            j["error"] = this->error;
        }
        {
            j["required"] = this->required;
        }
        if (this->array.has_value()) {
            j["array"] = this->array.value();
        }
        {
            j["$createdAt"] = this->createdAt;
        }
        {
            j["$updatedAt"] = this->updatedAt;
        }
        if (this->default_.has_value()) {
            j["default"] = this->default_.value();
        }
        return j;
    }
};

/** @brief ADL hook for nlohmann::json */
inline void from_json(const nlohmann::json& j, AttributePolygon& x) {
    x = AttributePolygon::fromJson(j);
}

inline void to_json(nlohmann::json& j, const AttributePolygon& x) {
    j = x.toJson();
}

/**
 * @brief AttributeVarchar
 */
struct AttributeVarchar {
    /** @brief Attribute Key. */
    std::string key;
    /** @brief Attribute type. */
    std::string type;
    /** @brief Attribute status. Possible values: `available`, `processing`, `deleting`, `stuck`, or `failed` */
    appwrite::enums::AttributeStatus status;
    /** @brief Error message. Displays error generated on failure of creating or deleting an attribute. */
    std::string error;
    /** @brief Is attribute required? */
    bool required;
    /** @brief Is attribute an array? */
    std::optional<bool> array = std::nullopt;
    /** @brief Attribute creation date in ISO 8601 format. */
    std::string createdAt;
    /** @brief Attribute update date in ISO 8601 format. */
    std::string updatedAt;
    /** @brief Attribute size. */
    int64_t size;
    /** @brief Default value for attribute when not provided. Cannot be set when attribute is required. */
    std::optional<std::string> default_ = std::nullopt;
    /** @brief Defines whether this attribute is encrypted or not. */
    std::optional<bool> encrypt = std::nullopt;

    /** @brief Deserialize from JSON */
    static AttributeVarchar fromJson(const nlohmann::json& j) {
        AttributeVarchar obj{}; // value-init: zeroes bools, ints, enums
        if (j.contains("key")) {
            if (j["key"].is_null()) {
                throw appwrite::DeserializationException("Required field 'key' is null");
            } else {
                obj.key = j["key"].get<std::string>();
            }
        }
        if (j.contains("type")) {
            if (j["type"].is_null()) {
                throw appwrite::DeserializationException("Required field 'type' is null");
            } else {
                obj.type = j["type"].get<std::string>();
            }
        }
        if (j.contains("status")) {
            if (j["status"].is_null()) {
                throw appwrite::DeserializationException("Required field 'status' is null");
            } else {
                obj.status = j["status"].get<appwrite::enums::AttributeStatus>();
            }
        }
        if (j.contains("error")) {
            if (j["error"].is_null()) {
                throw appwrite::DeserializationException("Required field 'error' is null");
            } else {
                obj.error = j["error"].get<std::string>();
            }
        }
        if (j.contains("required")) {
            if (j["required"].is_null()) {
                throw appwrite::DeserializationException("Required field 'required' is null");
            } else {
                obj.required = j["required"].get<bool>();
            }
        }
        if (j.contains("array")) {
            if (j["array"].is_null()) {
                obj.array = std::nullopt;
            } else {
                obj.array = j["array"].get<std::optional<bool>>();
            }
        }
        if (j.contains("$createdAt")) {
            if (j["$createdAt"].is_null()) {
                throw appwrite::DeserializationException("Required field '$createdAt' is null");
            } else {
                obj.createdAt = j["$createdAt"].get<std::string>();
            }
        }
        if (j.contains("$updatedAt")) {
            if (j["$updatedAt"].is_null()) {
                throw appwrite::DeserializationException("Required field '$updatedAt' is null");
            } else {
                obj.updatedAt = j["$updatedAt"].get<std::string>();
            }
        }
        if (j.contains("size")) {
            if (j["size"].is_null()) {
                throw appwrite::DeserializationException("Required field 'size' is null");
            } else {
                obj.size = j["size"].get<int64_t>();
            }
        }
        if (j.contains("default")) {
            if (j["default"].is_null()) {
                obj.default_ = std::nullopt;
            } else {
                obj.default_ = j["default"].get<std::optional<std::string>>();
            }
        }
        if (j.contains("encrypt")) {
            if (j["encrypt"].is_null()) {
                obj.encrypt = std::nullopt;
            } else {
                obj.encrypt = j["encrypt"].get<std::optional<bool>>();
            }
        }
        return obj;
    }

    /** @brief Serialize to JSON */
    [[nodiscard]] nlohmann::json toJson() const {
        nlohmann::json j = nlohmann::json::object();
        {
            j["key"] = this->key;
        }
        {
            j["type"] = this->type;
        }
        {
            j["status"] = appwrite::enums::toString(this->status);
        }
        {
            j["error"] = this->error;
        }
        {
            j["required"] = this->required;
        }
        if (this->array.has_value()) {
            j["array"] = this->array.value();
        }
        {
            j["$createdAt"] = this->createdAt;
        }
        {
            j["$updatedAt"] = this->updatedAt;
        }
        {
            j["size"] = this->size;
        }
        if (this->default_.has_value()) {
            j["default"] = this->default_.value();
        }
        if (this->encrypt.has_value()) {
            j["encrypt"] = this->encrypt.value();
        }
        return j;
    }
};

/** @brief ADL hook for nlohmann::json */
inline void from_json(const nlohmann::json& j, AttributeVarchar& x) {
    x = AttributeVarchar::fromJson(j);
}

inline void to_json(nlohmann::json& j, const AttributeVarchar& x) {
    j = x.toJson();
}

/**
 * @brief AttributeText
 */
struct AttributeText {
    /** @brief Attribute Key. */
    std::string key;
    /** @brief Attribute type. */
    std::string type;
    /** @brief Attribute status. Possible values: `available`, `processing`, `deleting`, `stuck`, or `failed` */
    appwrite::enums::AttributeStatus status;
    /** @brief Error message. Displays error generated on failure of creating or deleting an attribute. */
    std::string error;
    /** @brief Is attribute required? */
    bool required;
    /** @brief Is attribute an array? */
    std::optional<bool> array = std::nullopt;
    /** @brief Attribute creation date in ISO 8601 format. */
    std::string createdAt;
    /** @brief Attribute update date in ISO 8601 format. */
    std::string updatedAt;
    /** @brief Default value for attribute when not provided. Cannot be set when attribute is required. */
    std::optional<std::string> default_ = std::nullopt;
    /** @brief Defines whether this attribute is encrypted or not. */
    std::optional<bool> encrypt = std::nullopt;

    /** @brief Deserialize from JSON */
    static AttributeText fromJson(const nlohmann::json& j) {
        AttributeText obj{}; // value-init: zeroes bools, ints, enums
        if (j.contains("key")) {
            if (j["key"].is_null()) {
                throw appwrite::DeserializationException("Required field 'key' is null");
            } else {
                obj.key = j["key"].get<std::string>();
            }
        }
        if (j.contains("type")) {
            if (j["type"].is_null()) {
                throw appwrite::DeserializationException("Required field 'type' is null");
            } else {
                obj.type = j["type"].get<std::string>();
            }
        }
        if (j.contains("status")) {
            if (j["status"].is_null()) {
                throw appwrite::DeserializationException("Required field 'status' is null");
            } else {
                obj.status = j["status"].get<appwrite::enums::AttributeStatus>();
            }
        }
        if (j.contains("error")) {
            if (j["error"].is_null()) {
                throw appwrite::DeserializationException("Required field 'error' is null");
            } else {
                obj.error = j["error"].get<std::string>();
            }
        }
        if (j.contains("required")) {
            if (j["required"].is_null()) {
                throw appwrite::DeserializationException("Required field 'required' is null");
            } else {
                obj.required = j["required"].get<bool>();
            }
        }
        if (j.contains("array")) {
            if (j["array"].is_null()) {
                obj.array = std::nullopt;
            } else {
                obj.array = j["array"].get<std::optional<bool>>();
            }
        }
        if (j.contains("$createdAt")) {
            if (j["$createdAt"].is_null()) {
                throw appwrite::DeserializationException("Required field '$createdAt' is null");
            } else {
                obj.createdAt = j["$createdAt"].get<std::string>();
            }
        }
        if (j.contains("$updatedAt")) {
            if (j["$updatedAt"].is_null()) {
                throw appwrite::DeserializationException("Required field '$updatedAt' is null");
            } else {
                obj.updatedAt = j["$updatedAt"].get<std::string>();
            }
        }
        if (j.contains("default")) {
            if (j["default"].is_null()) {
                obj.default_ = std::nullopt;
            } else {
                obj.default_ = j["default"].get<std::optional<std::string>>();
            }
        }
        if (j.contains("encrypt")) {
            if (j["encrypt"].is_null()) {
                obj.encrypt = std::nullopt;
            } else {
                obj.encrypt = j["encrypt"].get<std::optional<bool>>();
            }
        }
        return obj;
    }

    /** @brief Serialize to JSON */
    [[nodiscard]] nlohmann::json toJson() const {
        nlohmann::json j = nlohmann::json::object();
        {
            j["key"] = this->key;
        }
        {
            j["type"] = this->type;
        }
        {
            j["status"] = appwrite::enums::toString(this->status);
        }
        {
            j["error"] = this->error;
        }
        {
            j["required"] = this->required;
        }
        if (this->array.has_value()) {
            j["array"] = this->array.value();
        }
        {
            j["$createdAt"] = this->createdAt;
        }
        {
            j["$updatedAt"] = this->updatedAt;
        }
        if (this->default_.has_value()) {
            j["default"] = this->default_.value();
        }
        if (this->encrypt.has_value()) {
            j["encrypt"] = this->encrypt.value();
        }
        return j;
    }
};

/** @brief ADL hook for nlohmann::json */
inline void from_json(const nlohmann::json& j, AttributeText& x) {
    x = AttributeText::fromJson(j);
}

inline void to_json(nlohmann::json& j, const AttributeText& x) {
    j = x.toJson();
}

/**
 * @brief AttributeMediumtext
 */
struct AttributeMediumtext {
    /** @brief Attribute Key. */
    std::string key;
    /** @brief Attribute type. */
    std::string type;
    /** @brief Attribute status. Possible values: `available`, `processing`, `deleting`, `stuck`, or `failed` */
    appwrite::enums::AttributeStatus status;
    /** @brief Error message. Displays error generated on failure of creating or deleting an attribute. */
    std::string error;
    /** @brief Is attribute required? */
    bool required;
    /** @brief Is attribute an array? */
    std::optional<bool> array = std::nullopt;
    /** @brief Attribute creation date in ISO 8601 format. */
    std::string createdAt;
    /** @brief Attribute update date in ISO 8601 format. */
    std::string updatedAt;
    /** @brief Default value for attribute when not provided. Cannot be set when attribute is required. */
    std::optional<std::string> default_ = std::nullopt;
    /** @brief Defines whether this attribute is encrypted or not. */
    std::optional<bool> encrypt = std::nullopt;

    /** @brief Deserialize from JSON */
    static AttributeMediumtext fromJson(const nlohmann::json& j) {
        AttributeMediumtext obj{}; // value-init: zeroes bools, ints, enums
        if (j.contains("key")) {
            if (j["key"].is_null()) {
                throw appwrite::DeserializationException("Required field 'key' is null");
            } else {
                obj.key = j["key"].get<std::string>();
            }
        }
        if (j.contains("type")) {
            if (j["type"].is_null()) {
                throw appwrite::DeserializationException("Required field 'type' is null");
            } else {
                obj.type = j["type"].get<std::string>();
            }
        }
        if (j.contains("status")) {
            if (j["status"].is_null()) {
                throw appwrite::DeserializationException("Required field 'status' is null");
            } else {
                obj.status = j["status"].get<appwrite::enums::AttributeStatus>();
            }
        }
        if (j.contains("error")) {
            if (j["error"].is_null()) {
                throw appwrite::DeserializationException("Required field 'error' is null");
            } else {
                obj.error = j["error"].get<std::string>();
            }
        }
        if (j.contains("required")) {
            if (j["required"].is_null()) {
                throw appwrite::DeserializationException("Required field 'required' is null");
            } else {
                obj.required = j["required"].get<bool>();
            }
        }
        if (j.contains("array")) {
            if (j["array"].is_null()) {
                obj.array = std::nullopt;
            } else {
                obj.array = j["array"].get<std::optional<bool>>();
            }
        }
        if (j.contains("$createdAt")) {
            if (j["$createdAt"].is_null()) {
                throw appwrite::DeserializationException("Required field '$createdAt' is null");
            } else {
                obj.createdAt = j["$createdAt"].get<std::string>();
            }
        }
        if (j.contains("$updatedAt")) {
            if (j["$updatedAt"].is_null()) {
                throw appwrite::DeserializationException("Required field '$updatedAt' is null");
            } else {
                obj.updatedAt = j["$updatedAt"].get<std::string>();
            }
        }
        if (j.contains("default")) {
            if (j["default"].is_null()) {
                obj.default_ = std::nullopt;
            } else {
                obj.default_ = j["default"].get<std::optional<std::string>>();
            }
        }
        if (j.contains("encrypt")) {
            if (j["encrypt"].is_null()) {
                obj.encrypt = std::nullopt;
            } else {
                obj.encrypt = j["encrypt"].get<std::optional<bool>>();
            }
        }
        return obj;
    }

    /** @brief Serialize to JSON */
    [[nodiscard]] nlohmann::json toJson() const {
        nlohmann::json j = nlohmann::json::object();
        {
            j["key"] = this->key;
        }
        {
            j["type"] = this->type;
        }
        {
            j["status"] = appwrite::enums::toString(this->status);
        }
        {
            j["error"] = this->error;
        }
        {
            j["required"] = this->required;
        }
        if (this->array.has_value()) {
            j["array"] = this->array.value();
        }
        {
            j["$createdAt"] = this->createdAt;
        }
        {
            j["$updatedAt"] = this->updatedAt;
        }
        if (this->default_.has_value()) {
            j["default"] = this->default_.value();
        }
        if (this->encrypt.has_value()) {
            j["encrypt"] = this->encrypt.value();
        }
        return j;
    }
};

/** @brief ADL hook for nlohmann::json */
inline void from_json(const nlohmann::json& j, AttributeMediumtext& x) {
    x = AttributeMediumtext::fromJson(j);
}

inline void to_json(nlohmann::json& j, const AttributeMediumtext& x) {
    j = x.toJson();
}

/**
 * @brief AttributeLongtext
 */
struct AttributeLongtext {
    /** @brief Attribute Key. */
    std::string key;
    /** @brief Attribute type. */
    std::string type;
    /** @brief Attribute status. Possible values: `available`, `processing`, `deleting`, `stuck`, or `failed` */
    appwrite::enums::AttributeStatus status;
    /** @brief Error message. Displays error generated on failure of creating or deleting an attribute. */
    std::string error;
    /** @brief Is attribute required? */
    bool required;
    /** @brief Is attribute an array? */
    std::optional<bool> array = std::nullopt;
    /** @brief Attribute creation date in ISO 8601 format. */
    std::string createdAt;
    /** @brief Attribute update date in ISO 8601 format. */
    std::string updatedAt;
    /** @brief Default value for attribute when not provided. Cannot be set when attribute is required. */
    std::optional<std::string> default_ = std::nullopt;
    /** @brief Defines whether this attribute is encrypted or not. */
    std::optional<bool> encrypt = std::nullopt;

    /** @brief Deserialize from JSON */
    static AttributeLongtext fromJson(const nlohmann::json& j) {
        AttributeLongtext obj{}; // value-init: zeroes bools, ints, enums
        if (j.contains("key")) {
            if (j["key"].is_null()) {
                throw appwrite::DeserializationException("Required field 'key' is null");
            } else {
                obj.key = j["key"].get<std::string>();
            }
        }
        if (j.contains("type")) {
            if (j["type"].is_null()) {
                throw appwrite::DeserializationException("Required field 'type' is null");
            } else {
                obj.type = j["type"].get<std::string>();
            }
        }
        if (j.contains("status")) {
            if (j["status"].is_null()) {
                throw appwrite::DeserializationException("Required field 'status' is null");
            } else {
                obj.status = j["status"].get<appwrite::enums::AttributeStatus>();
            }
        }
        if (j.contains("error")) {
            if (j["error"].is_null()) {
                throw appwrite::DeserializationException("Required field 'error' is null");
            } else {
                obj.error = j["error"].get<std::string>();
            }
        }
        if (j.contains("required")) {
            if (j["required"].is_null()) {
                throw appwrite::DeserializationException("Required field 'required' is null");
            } else {
                obj.required = j["required"].get<bool>();
            }
        }
        if (j.contains("array")) {
            if (j["array"].is_null()) {
                obj.array = std::nullopt;
            } else {
                obj.array = j["array"].get<std::optional<bool>>();
            }
        }
        if (j.contains("$createdAt")) {
            if (j["$createdAt"].is_null()) {
                throw appwrite::DeserializationException("Required field '$createdAt' is null");
            } else {
                obj.createdAt = j["$createdAt"].get<std::string>();
            }
        }
        if (j.contains("$updatedAt")) {
            if (j["$updatedAt"].is_null()) {
                throw appwrite::DeserializationException("Required field '$updatedAt' is null");
            } else {
                obj.updatedAt = j["$updatedAt"].get<std::string>();
            }
        }
        if (j.contains("default")) {
            if (j["default"].is_null()) {
                obj.default_ = std::nullopt;
            } else {
                obj.default_ = j["default"].get<std::optional<std::string>>();
            }
        }
        if (j.contains("encrypt")) {
            if (j["encrypt"].is_null()) {
                obj.encrypt = std::nullopt;
            } else {
                obj.encrypt = j["encrypt"].get<std::optional<bool>>();
            }
        }
        return obj;
    }

    /** @brief Serialize to JSON */
    [[nodiscard]] nlohmann::json toJson() const {
        nlohmann::json j = nlohmann::json::object();
        {
            j["key"] = this->key;
        }
        {
            j["type"] = this->type;
        }
        {
            j["status"] = appwrite::enums::toString(this->status);
        }
        {
            j["error"] = this->error;
        }
        {
            j["required"] = this->required;
        }
        if (this->array.has_value()) {
            j["array"] = this->array.value();
        }
        {
            j["$createdAt"] = this->createdAt;
        }
        {
            j["$updatedAt"] = this->updatedAt;
        }
        if (this->default_.has_value()) {
            j["default"] = this->default_.value();
        }
        if (this->encrypt.has_value()) {
            j["encrypt"] = this->encrypt.value();
        }
        return j;
    }
};

/** @brief ADL hook for nlohmann::json */
inline void from_json(const nlohmann::json& j, AttributeLongtext& x) {
    x = AttributeLongtext::fromJson(j);
}

inline void to_json(nlohmann::json& j, const AttributeLongtext& x) {
    j = x.toJson();
}

/**
 * @brief Columns List
 */
struct ColumnList {
    /** @brief Total number of columns in the given table. */
    int64_t total;
    /** @brief List of columns. */
    std::vector<std::string> columns;

    /** @brief Deserialize from JSON */
    static ColumnList fromJson(const nlohmann::json& j) {
        ColumnList obj{}; // value-init: zeroes bools, ints, enums
        if (j.contains("total")) {
            if (j["total"].is_null()) {
                throw appwrite::DeserializationException("Required field 'total' is null");
            } else {
                obj.total = j["total"].get<int64_t>();
            }
        }
        if (j.contains("columns")) {
            if (j["columns"].is_null()) {
                throw appwrite::DeserializationException("Required field 'columns' is null");
            } else {
                obj.columns = j["columns"].get<std::vector<std::string>>();
            }
        }
        return obj;
    }

    /** @brief Serialize to JSON */
    [[nodiscard]] nlohmann::json toJson() const {
        nlohmann::json j = nlohmann::json::object();
        {
            j["total"] = this->total;
        }
        {
            j["columns"] = this->columns;
        }
        return j;
    }
};

/** @brief ADL hook for nlohmann::json */
inline void from_json(const nlohmann::json& j, ColumnList& x) {
    x = ColumnList::fromJson(j);
}

inline void to_json(nlohmann::json& j, const ColumnList& x) {
    j = x.toJson();
}

/**
 * @brief ColumnString
 */
struct ColumnString {
    /** @brief Column Key. */
    std::string key;
    /** @brief Column type. */
    std::string type;
    /** @brief Column status. Possible values: `available`, `processing`, `deleting`, `stuck`, or `failed` */
    appwrite::enums::ColumnStatus status;
    /** @brief Error message. Displays error generated on failure of creating or deleting an column. */
    std::string error;
    /** @brief Is column required? */
    bool required;
    /** @brief Is column an array? */
    std::optional<bool> array = std::nullopt;
    /** @brief Column creation date in ISO 8601 format. */
    std::string createdAt;
    /** @brief Column update date in ISO 8601 format. */
    std::string updatedAt;
    /** @brief Column size. */
    int64_t size;
    /** @brief Default value for column when not provided. Cannot be set when column is required. */
    std::optional<std::string> default_ = std::nullopt;
    /** @brief Defines whether this column is encrypted or not. */
    std::optional<bool> encrypt = std::nullopt;

    /** @brief Deserialize from JSON */
    static ColumnString fromJson(const nlohmann::json& j) {
        ColumnString obj{}; // value-init: zeroes bools, ints, enums
        if (j.contains("key")) {
            if (j["key"].is_null()) {
                throw appwrite::DeserializationException("Required field 'key' is null");
            } else {
                obj.key = j["key"].get<std::string>();
            }
        }
        if (j.contains("type")) {
            if (j["type"].is_null()) {
                throw appwrite::DeserializationException("Required field 'type' is null");
            } else {
                obj.type = j["type"].get<std::string>();
            }
        }
        if (j.contains("status")) {
            if (j["status"].is_null()) {
                throw appwrite::DeserializationException("Required field 'status' is null");
            } else {
                obj.status = j["status"].get<appwrite::enums::ColumnStatus>();
            }
        }
        if (j.contains("error")) {
            if (j["error"].is_null()) {
                throw appwrite::DeserializationException("Required field 'error' is null");
            } else {
                obj.error = j["error"].get<std::string>();
            }
        }
        if (j.contains("required")) {
            if (j["required"].is_null()) {
                throw appwrite::DeserializationException("Required field 'required' is null");
            } else {
                obj.required = j["required"].get<bool>();
            }
        }
        if (j.contains("array")) {
            if (j["array"].is_null()) {
                obj.array = std::nullopt;
            } else {
                obj.array = j["array"].get<std::optional<bool>>();
            }
        }
        if (j.contains("$createdAt")) {
            if (j["$createdAt"].is_null()) {
                throw appwrite::DeserializationException("Required field '$createdAt' is null");
            } else {
                obj.createdAt = j["$createdAt"].get<std::string>();
            }
        }
        if (j.contains("$updatedAt")) {
            if (j["$updatedAt"].is_null()) {
                throw appwrite::DeserializationException("Required field '$updatedAt' is null");
            } else {
                obj.updatedAt = j["$updatedAt"].get<std::string>();
            }
        }
        if (j.contains("size")) {
            if (j["size"].is_null()) {
                throw appwrite::DeserializationException("Required field 'size' is null");
            } else {
                obj.size = j["size"].get<int64_t>();
            }
        }
        if (j.contains("default")) {
            if (j["default"].is_null()) {
                obj.default_ = std::nullopt;
            } else {
                obj.default_ = j["default"].get<std::optional<std::string>>();
            }
        }
        if (j.contains("encrypt")) {
            if (j["encrypt"].is_null()) {
                obj.encrypt = std::nullopt;
            } else {
                obj.encrypt = j["encrypt"].get<std::optional<bool>>();
            }
        }
        return obj;
    }

    /** @brief Serialize to JSON */
    [[nodiscard]] nlohmann::json toJson() const {
        nlohmann::json j = nlohmann::json::object();
        {
            j["key"] = this->key;
        }
        {
            j["type"] = this->type;
        }
        {
            j["status"] = appwrite::enums::toString(this->status);
        }
        {
            j["error"] = this->error;
        }
        {
            j["required"] = this->required;
        }
        if (this->array.has_value()) {
            j["array"] = this->array.value();
        }
        {
            j["$createdAt"] = this->createdAt;
        }
        {
            j["$updatedAt"] = this->updatedAt;
        }
        {
            j["size"] = this->size;
        }
        if (this->default_.has_value()) {
            j["default"] = this->default_.value();
        }
        if (this->encrypt.has_value()) {
            j["encrypt"] = this->encrypt.value();
        }
        return j;
    }
};

/** @brief ADL hook for nlohmann::json */
inline void from_json(const nlohmann::json& j, ColumnString& x) {
    x = ColumnString::fromJson(j);
}

inline void to_json(nlohmann::json& j, const ColumnString& x) {
    j = x.toJson();
}

/**
 * @brief ColumnInteger
 */
struct ColumnInteger {
    /** @brief Column Key. */
    std::string key;
    /** @brief Column type. */
    std::string type;
    /** @brief Column status. Possible values: `available`, `processing`, `deleting`, `stuck`, or `failed` */
    appwrite::enums::ColumnStatus status;
    /** @brief Error message. Displays error generated on failure of creating or deleting an column. */
    std::string error;
    /** @brief Is column required? */
    bool required;
    /** @brief Is column an array? */
    std::optional<bool> array = std::nullopt;
    /** @brief Column creation date in ISO 8601 format. */
    std::string createdAt;
    /** @brief Column update date in ISO 8601 format. */
    std::string updatedAt;
    /** @brief Minimum value to enforce for new documents. */
    std::optional<int64_t> min = std::nullopt;
    /** @brief Maximum value to enforce for new documents. */
    std::optional<int64_t> max = std::nullopt;
    /** @brief Default value for column when not provided. Cannot be set when column is required. */
    std::optional<int64_t> default_ = std::nullopt;

    /** @brief Deserialize from JSON */
    static ColumnInteger fromJson(const nlohmann::json& j) {
        ColumnInteger obj{}; // value-init: zeroes bools, ints, enums
        if (j.contains("key")) {
            if (j["key"].is_null()) {
                throw appwrite::DeserializationException("Required field 'key' is null");
            } else {
                obj.key = j["key"].get<std::string>();
            }
        }
        if (j.contains("type")) {
            if (j["type"].is_null()) {
                throw appwrite::DeserializationException("Required field 'type' is null");
            } else {
                obj.type = j["type"].get<std::string>();
            }
        }
        if (j.contains("status")) {
            if (j["status"].is_null()) {
                throw appwrite::DeserializationException("Required field 'status' is null");
            } else {
                obj.status = j["status"].get<appwrite::enums::ColumnStatus>();
            }
        }
        if (j.contains("error")) {
            if (j["error"].is_null()) {
                throw appwrite::DeserializationException("Required field 'error' is null");
            } else {
                obj.error = j["error"].get<std::string>();
            }
        }
        if (j.contains("required")) {
            if (j["required"].is_null()) {
                throw appwrite::DeserializationException("Required field 'required' is null");
            } else {
                obj.required = j["required"].get<bool>();
            }
        }
        if (j.contains("array")) {
            if (j["array"].is_null()) {
                obj.array = std::nullopt;
            } else {
                obj.array = j["array"].get<std::optional<bool>>();
            }
        }
        if (j.contains("$createdAt")) {
            if (j["$createdAt"].is_null()) {
                throw appwrite::DeserializationException("Required field '$createdAt' is null");
            } else {
                obj.createdAt = j["$createdAt"].get<std::string>();
            }
        }
        if (j.contains("$updatedAt")) {
            if (j["$updatedAt"].is_null()) {
                throw appwrite::DeserializationException("Required field '$updatedAt' is null");
            } else {
                obj.updatedAt = j["$updatedAt"].get<std::string>();
            }
        }
        if (j.contains("min")) {
            if (j["min"].is_null()) {
                obj.min = std::nullopt;
            } else {
                obj.min = j["min"].get<std::optional<int64_t>>();
            }
        }
        if (j.contains("max")) {
            if (j["max"].is_null()) {
                obj.max = std::nullopt;
            } else {
                obj.max = j["max"].get<std::optional<int64_t>>();
            }
        }
        if (j.contains("default")) {
            if (j["default"].is_null()) {
                obj.default_ = std::nullopt;
            } else {
                obj.default_ = j["default"].get<std::optional<int64_t>>();
            }
        }
        return obj;
    }

    /** @brief Serialize to JSON */
    [[nodiscard]] nlohmann::json toJson() const {
        nlohmann::json j = nlohmann::json::object();
        {
            j["key"] = this->key;
        }
        {
            j["type"] = this->type;
        }
        {
            j["status"] = appwrite::enums::toString(this->status);
        }
        {
            j["error"] = this->error;
        }
        {
            j["required"] = this->required;
        }
        if (this->array.has_value()) {
            j["array"] = this->array.value();
        }
        {
            j["$createdAt"] = this->createdAt;
        }
        {
            j["$updatedAt"] = this->updatedAt;
        }
        if (this->min.has_value()) {
            j["min"] = this->min.value();
        }
        if (this->max.has_value()) {
            j["max"] = this->max.value();
        }
        if (this->default_.has_value()) {
            j["default"] = this->default_.value();
        }
        return j;
    }
};

/** @brief ADL hook for nlohmann::json */
inline void from_json(const nlohmann::json& j, ColumnInteger& x) {
    x = ColumnInteger::fromJson(j);
}

inline void to_json(nlohmann::json& j, const ColumnInteger& x) {
    j = x.toJson();
}

/**
 * @brief ColumnFloat
 */
struct ColumnFloat {
    /** @brief Column Key. */
    std::string key;
    /** @brief Column type. */
    std::string type;
    /** @brief Column status. Possible values: `available`, `processing`, `deleting`, `stuck`, or `failed` */
    appwrite::enums::ColumnStatus status;
    /** @brief Error message. Displays error generated on failure of creating or deleting an column. */
    std::string error;
    /** @brief Is column required? */
    bool required;
    /** @brief Is column an array? */
    std::optional<bool> array = std::nullopt;
    /** @brief Column creation date in ISO 8601 format. */
    std::string createdAt;
    /** @brief Column update date in ISO 8601 format. */
    std::string updatedAt;
    /** @brief Minimum value to enforce for new documents. */
    std::optional<double> min = std::nullopt;
    /** @brief Maximum value to enforce for new documents. */
    std::optional<double> max = std::nullopt;
    /** @brief Default value for column when not provided. Cannot be set when column is required. */
    std::optional<double> default_ = std::nullopt;

    /** @brief Deserialize from JSON */
    static ColumnFloat fromJson(const nlohmann::json& j) {
        ColumnFloat obj{}; // value-init: zeroes bools, ints, enums
        if (j.contains("key")) {
            if (j["key"].is_null()) {
                throw appwrite::DeserializationException("Required field 'key' is null");
            } else {
                obj.key = j["key"].get<std::string>();
            }
        }
        if (j.contains("type")) {
            if (j["type"].is_null()) {
                throw appwrite::DeserializationException("Required field 'type' is null");
            } else {
                obj.type = j["type"].get<std::string>();
            }
        }
        if (j.contains("status")) {
            if (j["status"].is_null()) {
                throw appwrite::DeserializationException("Required field 'status' is null");
            } else {
                obj.status = j["status"].get<appwrite::enums::ColumnStatus>();
            }
        }
        if (j.contains("error")) {
            if (j["error"].is_null()) {
                throw appwrite::DeserializationException("Required field 'error' is null");
            } else {
                obj.error = j["error"].get<std::string>();
            }
        }
        if (j.contains("required")) {
            if (j["required"].is_null()) {
                throw appwrite::DeserializationException("Required field 'required' is null");
            } else {
                obj.required = j["required"].get<bool>();
            }
        }
        if (j.contains("array")) {
            if (j["array"].is_null()) {
                obj.array = std::nullopt;
            } else {
                obj.array = j["array"].get<std::optional<bool>>();
            }
        }
        if (j.contains("$createdAt")) {
            if (j["$createdAt"].is_null()) {
                throw appwrite::DeserializationException("Required field '$createdAt' is null");
            } else {
                obj.createdAt = j["$createdAt"].get<std::string>();
            }
        }
        if (j.contains("$updatedAt")) {
            if (j["$updatedAt"].is_null()) {
                throw appwrite::DeserializationException("Required field '$updatedAt' is null");
            } else {
                obj.updatedAt = j["$updatedAt"].get<std::string>();
            }
        }
        if (j.contains("min")) {
            if (j["min"].is_null()) {
                obj.min = std::nullopt;
            } else {
                obj.min = j["min"].get<std::optional<double>>();
            }
        }
        if (j.contains("max")) {
            if (j["max"].is_null()) {
                obj.max = std::nullopt;
            } else {
                obj.max = j["max"].get<std::optional<double>>();
            }
        }
        if (j.contains("default")) {
            if (j["default"].is_null()) {
                obj.default_ = std::nullopt;
            } else {
                obj.default_ = j["default"].get<std::optional<double>>();
            }
        }
        return obj;
    }

    /** @brief Serialize to JSON */
    [[nodiscard]] nlohmann::json toJson() const {
        nlohmann::json j = nlohmann::json::object();
        {
            j["key"] = this->key;
        }
        {
            j["type"] = this->type;
        }
        {
            j["status"] = appwrite::enums::toString(this->status);
        }
        {
            j["error"] = this->error;
        }
        {
            j["required"] = this->required;
        }
        if (this->array.has_value()) {
            j["array"] = this->array.value();
        }
        {
            j["$createdAt"] = this->createdAt;
        }
        {
            j["$updatedAt"] = this->updatedAt;
        }
        if (this->min.has_value()) {
            j["min"] = this->min.value();
        }
        if (this->max.has_value()) {
            j["max"] = this->max.value();
        }
        if (this->default_.has_value()) {
            j["default"] = this->default_.value();
        }
        return j;
    }
};

/** @brief ADL hook for nlohmann::json */
inline void from_json(const nlohmann::json& j, ColumnFloat& x) {
    x = ColumnFloat::fromJson(j);
}

inline void to_json(nlohmann::json& j, const ColumnFloat& x) {
    j = x.toJson();
}

/**
 * @brief ColumnBoolean
 */
struct ColumnBoolean {
    /** @brief Column Key. */
    std::string key;
    /** @brief Column type. */
    std::string type;
    /** @brief Column status. Possible values: `available`, `processing`, `deleting`, `stuck`, or `failed` */
    appwrite::enums::ColumnStatus status;
    /** @brief Error message. Displays error generated on failure of creating or deleting an column. */
    std::string error;
    /** @brief Is column required? */
    bool required;
    /** @brief Is column an array? */
    std::optional<bool> array = std::nullopt;
    /** @brief Column creation date in ISO 8601 format. */
    std::string createdAt;
    /** @brief Column update date in ISO 8601 format. */
    std::string updatedAt;
    /** @brief Default value for column when not provided. Cannot be set when column is required. */
    std::optional<bool> default_ = std::nullopt;

    /** @brief Deserialize from JSON */
    static ColumnBoolean fromJson(const nlohmann::json& j) {
        ColumnBoolean obj{}; // value-init: zeroes bools, ints, enums
        if (j.contains("key")) {
            if (j["key"].is_null()) {
                throw appwrite::DeserializationException("Required field 'key' is null");
            } else {
                obj.key = j["key"].get<std::string>();
            }
        }
        if (j.contains("type")) {
            if (j["type"].is_null()) {
                throw appwrite::DeserializationException("Required field 'type' is null");
            } else {
                obj.type = j["type"].get<std::string>();
            }
        }
        if (j.contains("status")) {
            if (j["status"].is_null()) {
                throw appwrite::DeserializationException("Required field 'status' is null");
            } else {
                obj.status = j["status"].get<appwrite::enums::ColumnStatus>();
            }
        }
        if (j.contains("error")) {
            if (j["error"].is_null()) {
                throw appwrite::DeserializationException("Required field 'error' is null");
            } else {
                obj.error = j["error"].get<std::string>();
            }
        }
        if (j.contains("required")) {
            if (j["required"].is_null()) {
                throw appwrite::DeserializationException("Required field 'required' is null");
            } else {
                obj.required = j["required"].get<bool>();
            }
        }
        if (j.contains("array")) {
            if (j["array"].is_null()) {
                obj.array = std::nullopt;
            } else {
                obj.array = j["array"].get<std::optional<bool>>();
            }
        }
        if (j.contains("$createdAt")) {
            if (j["$createdAt"].is_null()) {
                throw appwrite::DeserializationException("Required field '$createdAt' is null");
            } else {
                obj.createdAt = j["$createdAt"].get<std::string>();
            }
        }
        if (j.contains("$updatedAt")) {
            if (j["$updatedAt"].is_null()) {
                throw appwrite::DeserializationException("Required field '$updatedAt' is null");
            } else {
                obj.updatedAt = j["$updatedAt"].get<std::string>();
            }
        }
        if (j.contains("default")) {
            if (j["default"].is_null()) {
                obj.default_ = std::nullopt;
            } else {
                obj.default_ = j["default"].get<std::optional<bool>>();
            }
        }
        return obj;
    }

    /** @brief Serialize to JSON */
    [[nodiscard]] nlohmann::json toJson() const {
        nlohmann::json j = nlohmann::json::object();
        {
            j["key"] = this->key;
        }
        {
            j["type"] = this->type;
        }
        {
            j["status"] = appwrite::enums::toString(this->status);
        }
        {
            j["error"] = this->error;
        }
        {
            j["required"] = this->required;
        }
        if (this->array.has_value()) {
            j["array"] = this->array.value();
        }
        {
            j["$createdAt"] = this->createdAt;
        }
        {
            j["$updatedAt"] = this->updatedAt;
        }
        if (this->default_.has_value()) {
            j["default"] = this->default_.value();
        }
        return j;
    }
};

/** @brief ADL hook for nlohmann::json */
inline void from_json(const nlohmann::json& j, ColumnBoolean& x) {
    x = ColumnBoolean::fromJson(j);
}

inline void to_json(nlohmann::json& j, const ColumnBoolean& x) {
    j = x.toJson();
}

/**
 * @brief ColumnEmail
 */
struct ColumnEmail {
    /** @brief Column Key. */
    std::string key;
    /** @brief Column type. */
    std::string type;
    /** @brief Column status. Possible values: `available`, `processing`, `deleting`, `stuck`, or `failed` */
    appwrite::enums::ColumnStatus status;
    /** @brief Error message. Displays error generated on failure of creating or deleting an column. */
    std::string error;
    /** @brief Is column required? */
    bool required;
    /** @brief Is column an array? */
    std::optional<bool> array = std::nullopt;
    /** @brief Column creation date in ISO 8601 format. */
    std::string createdAt;
    /** @brief Column update date in ISO 8601 format. */
    std::string updatedAt;
    /** @brief String format. */
    std::string format;
    /** @brief Default value for column when not provided. Cannot be set when column is required. */
    std::optional<std::string> default_ = std::nullopt;

    /** @brief Deserialize from JSON */
    static ColumnEmail fromJson(const nlohmann::json& j) {
        ColumnEmail obj{}; // value-init: zeroes bools, ints, enums
        if (j.contains("key")) {
            if (j["key"].is_null()) {
                throw appwrite::DeserializationException("Required field 'key' is null");
            } else {
                obj.key = j["key"].get<std::string>();
            }
        }
        if (j.contains("type")) {
            if (j["type"].is_null()) {
                throw appwrite::DeserializationException("Required field 'type' is null");
            } else {
                obj.type = j["type"].get<std::string>();
            }
        }
        if (j.contains("status")) {
            if (j["status"].is_null()) {
                throw appwrite::DeserializationException("Required field 'status' is null");
            } else {
                obj.status = j["status"].get<appwrite::enums::ColumnStatus>();
            }
        }
        if (j.contains("error")) {
            if (j["error"].is_null()) {
                throw appwrite::DeserializationException("Required field 'error' is null");
            } else {
                obj.error = j["error"].get<std::string>();
            }
        }
        if (j.contains("required")) {
            if (j["required"].is_null()) {
                throw appwrite::DeserializationException("Required field 'required' is null");
            } else {
                obj.required = j["required"].get<bool>();
            }
        }
        if (j.contains("array")) {
            if (j["array"].is_null()) {
                obj.array = std::nullopt;
            } else {
                obj.array = j["array"].get<std::optional<bool>>();
            }
        }
        if (j.contains("$createdAt")) {
            if (j["$createdAt"].is_null()) {
                throw appwrite::DeserializationException("Required field '$createdAt' is null");
            } else {
                obj.createdAt = j["$createdAt"].get<std::string>();
            }
        }
        if (j.contains("$updatedAt")) {
            if (j["$updatedAt"].is_null()) {
                throw appwrite::DeserializationException("Required field '$updatedAt' is null");
            } else {
                obj.updatedAt = j["$updatedAt"].get<std::string>();
            }
        }
        if (j.contains("format")) {
            if (j["format"].is_null()) {
                throw appwrite::DeserializationException("Required field 'format' is null");
            } else {
                obj.format = j["format"].get<std::string>();
            }
        }
        if (j.contains("default")) {
            if (j["default"].is_null()) {
                obj.default_ = std::nullopt;
            } else {
                obj.default_ = j["default"].get<std::optional<std::string>>();
            }
        }
        return obj;
    }

    /** @brief Serialize to JSON */
    [[nodiscard]] nlohmann::json toJson() const {
        nlohmann::json j = nlohmann::json::object();
        {
            j["key"] = this->key;
        }
        {
            j["type"] = this->type;
        }
        {
            j["status"] = appwrite::enums::toString(this->status);
        }
        {
            j["error"] = this->error;
        }
        {
            j["required"] = this->required;
        }
        if (this->array.has_value()) {
            j["array"] = this->array.value();
        }
        {
            j["$createdAt"] = this->createdAt;
        }
        {
            j["$updatedAt"] = this->updatedAt;
        }
        {
            j["format"] = this->format;
        }
        if (this->default_.has_value()) {
            j["default"] = this->default_.value();
        }
        return j;
    }
};

/** @brief ADL hook for nlohmann::json */
inline void from_json(const nlohmann::json& j, ColumnEmail& x) {
    x = ColumnEmail::fromJson(j);
}

inline void to_json(nlohmann::json& j, const ColumnEmail& x) {
    j = x.toJson();
}

/**
 * @brief ColumnEnum
 */
struct ColumnEnum {
    /** @brief Column Key. */
    std::string key;
    /** @brief Column type. */
    std::string type;
    /** @brief Column status. Possible values: `available`, `processing`, `deleting`, `stuck`, or `failed` */
    appwrite::enums::ColumnStatus status;
    /** @brief Error message. Displays error generated on failure of creating or deleting an column. */
    std::string error;
    /** @brief Is column required? */
    bool required;
    /** @brief Is column an array? */
    std::optional<bool> array = std::nullopt;
    /** @brief Column creation date in ISO 8601 format. */
    std::string createdAt;
    /** @brief Column update date in ISO 8601 format. */
    std::string updatedAt;
    /** @brief Array of elements in enumerated type. */
    std::vector<std::string> elements;
    /** @brief String format. */
    std::string format;
    /** @brief Default value for column when not provided. Cannot be set when column is required. */
    std::optional<std::string> default_ = std::nullopt;

    /** @brief Deserialize from JSON */
    static ColumnEnum fromJson(const nlohmann::json& j) {
        ColumnEnum obj{}; // value-init: zeroes bools, ints, enums
        if (j.contains("key")) {
            if (j["key"].is_null()) {
                throw appwrite::DeserializationException("Required field 'key' is null");
            } else {
                obj.key = j["key"].get<std::string>();
            }
        }
        if (j.contains("type")) {
            if (j["type"].is_null()) {
                throw appwrite::DeserializationException("Required field 'type' is null");
            } else {
                obj.type = j["type"].get<std::string>();
            }
        }
        if (j.contains("status")) {
            if (j["status"].is_null()) {
                throw appwrite::DeserializationException("Required field 'status' is null");
            } else {
                obj.status = j["status"].get<appwrite::enums::ColumnStatus>();
            }
        }
        if (j.contains("error")) {
            if (j["error"].is_null()) {
                throw appwrite::DeserializationException("Required field 'error' is null");
            } else {
                obj.error = j["error"].get<std::string>();
            }
        }
        if (j.contains("required")) {
            if (j["required"].is_null()) {
                throw appwrite::DeserializationException("Required field 'required' is null");
            } else {
                obj.required = j["required"].get<bool>();
            }
        }
        if (j.contains("array")) {
            if (j["array"].is_null()) {
                obj.array = std::nullopt;
            } else {
                obj.array = j["array"].get<std::optional<bool>>();
            }
        }
        if (j.contains("$createdAt")) {
            if (j["$createdAt"].is_null()) {
                throw appwrite::DeserializationException("Required field '$createdAt' is null");
            } else {
                obj.createdAt = j["$createdAt"].get<std::string>();
            }
        }
        if (j.contains("$updatedAt")) {
            if (j["$updatedAt"].is_null()) {
                throw appwrite::DeserializationException("Required field '$updatedAt' is null");
            } else {
                obj.updatedAt = j["$updatedAt"].get<std::string>();
            }
        }
        if (j.contains("elements")) {
            if (j["elements"].is_null()) {
                throw appwrite::DeserializationException("Required field 'elements' is null");
            } else {
                obj.elements = j["elements"].get<std::vector<std::string>>();
            }
        }
        if (j.contains("format")) {
            if (j["format"].is_null()) {
                throw appwrite::DeserializationException("Required field 'format' is null");
            } else {
                obj.format = j["format"].get<std::string>();
            }
        }
        if (j.contains("default")) {
            if (j["default"].is_null()) {
                obj.default_ = std::nullopt;
            } else {
                obj.default_ = j["default"].get<std::optional<std::string>>();
            }
        }
        return obj;
    }

    /** @brief Serialize to JSON */
    [[nodiscard]] nlohmann::json toJson() const {
        nlohmann::json j = nlohmann::json::object();
        {
            j["key"] = this->key;
        }
        {
            j["type"] = this->type;
        }
        {
            j["status"] = appwrite::enums::toString(this->status);
        }
        {
            j["error"] = this->error;
        }
        {
            j["required"] = this->required;
        }
        if (this->array.has_value()) {
            j["array"] = this->array.value();
        }
        {
            j["$createdAt"] = this->createdAt;
        }
        {
            j["$updatedAt"] = this->updatedAt;
        }
        {
            j["elements"] = this->elements;
        }
        {
            j["format"] = this->format;
        }
        if (this->default_.has_value()) {
            j["default"] = this->default_.value();
        }
        return j;
    }
};

/** @brief ADL hook for nlohmann::json */
inline void from_json(const nlohmann::json& j, ColumnEnum& x) {
    x = ColumnEnum::fromJson(j);
}

inline void to_json(nlohmann::json& j, const ColumnEnum& x) {
    j = x.toJson();
}

/**
 * @brief ColumnIP
 */
struct ColumnIp {
    /** @brief Column Key. */
    std::string key;
    /** @brief Column type. */
    std::string type;
    /** @brief Column status. Possible values: `available`, `processing`, `deleting`, `stuck`, or `failed` */
    appwrite::enums::ColumnStatus status;
    /** @brief Error message. Displays error generated on failure of creating or deleting an column. */
    std::string error;
    /** @brief Is column required? */
    bool required;
    /** @brief Is column an array? */
    std::optional<bool> array = std::nullopt;
    /** @brief Column creation date in ISO 8601 format. */
    std::string createdAt;
    /** @brief Column update date in ISO 8601 format. */
    std::string updatedAt;
    /** @brief String format. */
    std::string format;
    /** @brief Default value for column when not provided. Cannot be set when column is required. */
    std::optional<std::string> default_ = std::nullopt;

    /** @brief Deserialize from JSON */
    static ColumnIp fromJson(const nlohmann::json& j) {
        ColumnIp obj{}; // value-init: zeroes bools, ints, enums
        if (j.contains("key")) {
            if (j["key"].is_null()) {
                throw appwrite::DeserializationException("Required field 'key' is null");
            } else {
                obj.key = j["key"].get<std::string>();
            }
        }
        if (j.contains("type")) {
            if (j["type"].is_null()) {
                throw appwrite::DeserializationException("Required field 'type' is null");
            } else {
                obj.type = j["type"].get<std::string>();
            }
        }
        if (j.contains("status")) {
            if (j["status"].is_null()) {
                throw appwrite::DeserializationException("Required field 'status' is null");
            } else {
                obj.status = j["status"].get<appwrite::enums::ColumnStatus>();
            }
        }
        if (j.contains("error")) {
            if (j["error"].is_null()) {
                throw appwrite::DeserializationException("Required field 'error' is null");
            } else {
                obj.error = j["error"].get<std::string>();
            }
        }
        if (j.contains("required")) {
            if (j["required"].is_null()) {
                throw appwrite::DeserializationException("Required field 'required' is null");
            } else {
                obj.required = j["required"].get<bool>();
            }
        }
        if (j.contains("array")) {
            if (j["array"].is_null()) {
                obj.array = std::nullopt;
            } else {
                obj.array = j["array"].get<std::optional<bool>>();
            }
        }
        if (j.contains("$createdAt")) {
            if (j["$createdAt"].is_null()) {
                throw appwrite::DeserializationException("Required field '$createdAt' is null");
            } else {
                obj.createdAt = j["$createdAt"].get<std::string>();
            }
        }
        if (j.contains("$updatedAt")) {
            if (j["$updatedAt"].is_null()) {
                throw appwrite::DeserializationException("Required field '$updatedAt' is null");
            } else {
                obj.updatedAt = j["$updatedAt"].get<std::string>();
            }
        }
        if (j.contains("format")) {
            if (j["format"].is_null()) {
                throw appwrite::DeserializationException("Required field 'format' is null");
            } else {
                obj.format = j["format"].get<std::string>();
            }
        }
        if (j.contains("default")) {
            if (j["default"].is_null()) {
                obj.default_ = std::nullopt;
            } else {
                obj.default_ = j["default"].get<std::optional<std::string>>();
            }
        }
        return obj;
    }

    /** @brief Serialize to JSON */
    [[nodiscard]] nlohmann::json toJson() const {
        nlohmann::json j = nlohmann::json::object();
        {
            j["key"] = this->key;
        }
        {
            j["type"] = this->type;
        }
        {
            j["status"] = appwrite::enums::toString(this->status);
        }
        {
            j["error"] = this->error;
        }
        {
            j["required"] = this->required;
        }
        if (this->array.has_value()) {
            j["array"] = this->array.value();
        }
        {
            j["$createdAt"] = this->createdAt;
        }
        {
            j["$updatedAt"] = this->updatedAt;
        }
        {
            j["format"] = this->format;
        }
        if (this->default_.has_value()) {
            j["default"] = this->default_.value();
        }
        return j;
    }
};

/** @brief ADL hook for nlohmann::json */
inline void from_json(const nlohmann::json& j, ColumnIp& x) {
    x = ColumnIp::fromJson(j);
}

inline void to_json(nlohmann::json& j, const ColumnIp& x) {
    j = x.toJson();
}

/**
 * @brief ColumnURL
 */
struct ColumnUrl {
    /** @brief Column Key. */
    std::string key;
    /** @brief Column type. */
    std::string type;
    /** @brief Column status. Possible values: `available`, `processing`, `deleting`, `stuck`, or `failed` */
    appwrite::enums::ColumnStatus status;
    /** @brief Error message. Displays error generated on failure of creating or deleting an column. */
    std::string error;
    /** @brief Is column required? */
    bool required;
    /** @brief Is column an array? */
    std::optional<bool> array = std::nullopt;
    /** @brief Column creation date in ISO 8601 format. */
    std::string createdAt;
    /** @brief Column update date in ISO 8601 format. */
    std::string updatedAt;
    /** @brief String format. */
    std::string format;
    /** @brief Default value for column when not provided. Cannot be set when column is required. */
    std::optional<std::string> default_ = std::nullopt;

    /** @brief Deserialize from JSON */
    static ColumnUrl fromJson(const nlohmann::json& j) {
        ColumnUrl obj{}; // value-init: zeroes bools, ints, enums
        if (j.contains("key")) {
            if (j["key"].is_null()) {
                throw appwrite::DeserializationException("Required field 'key' is null");
            } else {
                obj.key = j["key"].get<std::string>();
            }
        }
        if (j.contains("type")) {
            if (j["type"].is_null()) {
                throw appwrite::DeserializationException("Required field 'type' is null");
            } else {
                obj.type = j["type"].get<std::string>();
            }
        }
        if (j.contains("status")) {
            if (j["status"].is_null()) {
                throw appwrite::DeserializationException("Required field 'status' is null");
            } else {
                obj.status = j["status"].get<appwrite::enums::ColumnStatus>();
            }
        }
        if (j.contains("error")) {
            if (j["error"].is_null()) {
                throw appwrite::DeserializationException("Required field 'error' is null");
            } else {
                obj.error = j["error"].get<std::string>();
            }
        }
        if (j.contains("required")) {
            if (j["required"].is_null()) {
                throw appwrite::DeserializationException("Required field 'required' is null");
            } else {
                obj.required = j["required"].get<bool>();
            }
        }
        if (j.contains("array")) {
            if (j["array"].is_null()) {
                obj.array = std::nullopt;
            } else {
                obj.array = j["array"].get<std::optional<bool>>();
            }
        }
        if (j.contains("$createdAt")) {
            if (j["$createdAt"].is_null()) {
                throw appwrite::DeserializationException("Required field '$createdAt' is null");
            } else {
                obj.createdAt = j["$createdAt"].get<std::string>();
            }
        }
        if (j.contains("$updatedAt")) {
            if (j["$updatedAt"].is_null()) {
                throw appwrite::DeserializationException("Required field '$updatedAt' is null");
            } else {
                obj.updatedAt = j["$updatedAt"].get<std::string>();
            }
        }
        if (j.contains("format")) {
            if (j["format"].is_null()) {
                throw appwrite::DeserializationException("Required field 'format' is null");
            } else {
                obj.format = j["format"].get<std::string>();
            }
        }
        if (j.contains("default")) {
            if (j["default"].is_null()) {
                obj.default_ = std::nullopt;
            } else {
                obj.default_ = j["default"].get<std::optional<std::string>>();
            }
        }
        return obj;
    }

    /** @brief Serialize to JSON */
    [[nodiscard]] nlohmann::json toJson() const {
        nlohmann::json j = nlohmann::json::object();
        {
            j["key"] = this->key;
        }
        {
            j["type"] = this->type;
        }
        {
            j["status"] = appwrite::enums::toString(this->status);
        }
        {
            j["error"] = this->error;
        }
        {
            j["required"] = this->required;
        }
        if (this->array.has_value()) {
            j["array"] = this->array.value();
        }
        {
            j["$createdAt"] = this->createdAt;
        }
        {
            j["$updatedAt"] = this->updatedAt;
        }
        {
            j["format"] = this->format;
        }
        if (this->default_.has_value()) {
            j["default"] = this->default_.value();
        }
        return j;
    }
};

/** @brief ADL hook for nlohmann::json */
inline void from_json(const nlohmann::json& j, ColumnUrl& x) {
    x = ColumnUrl::fromJson(j);
}

inline void to_json(nlohmann::json& j, const ColumnUrl& x) {
    j = x.toJson();
}

/**
 * @brief ColumnDatetime
 */
struct ColumnDatetime {
    /** @brief Column Key. */
    std::string key;
    /** @brief Column type. */
    std::string type;
    /** @brief Column status. Possible values: `available`, `processing`, `deleting`, `stuck`, or `failed` */
    appwrite::enums::ColumnStatus status;
    /** @brief Error message. Displays error generated on failure of creating or deleting an column. */
    std::string error;
    /** @brief Is column required? */
    bool required;
    /** @brief Is column an array? */
    std::optional<bool> array = std::nullopt;
    /** @brief Column creation date in ISO 8601 format. */
    std::string createdAt;
    /** @brief Column update date in ISO 8601 format. */
    std::string updatedAt;
    /** @brief ISO 8601 format. */
    std::string format;
    /** @brief Default value for column when not provided. Only null is optional */
    std::optional<std::string> default_ = std::nullopt;

    /** @brief Deserialize from JSON */
    static ColumnDatetime fromJson(const nlohmann::json& j) {
        ColumnDatetime obj{}; // value-init: zeroes bools, ints, enums
        if (j.contains("key")) {
            if (j["key"].is_null()) {
                throw appwrite::DeserializationException("Required field 'key' is null");
            } else {
                obj.key = j["key"].get<std::string>();
            }
        }
        if (j.contains("type")) {
            if (j["type"].is_null()) {
                throw appwrite::DeserializationException("Required field 'type' is null");
            } else {
                obj.type = j["type"].get<std::string>();
            }
        }
        if (j.contains("status")) {
            if (j["status"].is_null()) {
                throw appwrite::DeserializationException("Required field 'status' is null");
            } else {
                obj.status = j["status"].get<appwrite::enums::ColumnStatus>();
            }
        }
        if (j.contains("error")) {
            if (j["error"].is_null()) {
                throw appwrite::DeserializationException("Required field 'error' is null");
            } else {
                obj.error = j["error"].get<std::string>();
            }
        }
        if (j.contains("required")) {
            if (j["required"].is_null()) {
                throw appwrite::DeserializationException("Required field 'required' is null");
            } else {
                obj.required = j["required"].get<bool>();
            }
        }
        if (j.contains("array")) {
            if (j["array"].is_null()) {
                obj.array = std::nullopt;
            } else {
                obj.array = j["array"].get<std::optional<bool>>();
            }
        }
        if (j.contains("$createdAt")) {
            if (j["$createdAt"].is_null()) {
                throw appwrite::DeserializationException("Required field '$createdAt' is null");
            } else {
                obj.createdAt = j["$createdAt"].get<std::string>();
            }
        }
        if (j.contains("$updatedAt")) {
            if (j["$updatedAt"].is_null()) {
                throw appwrite::DeserializationException("Required field '$updatedAt' is null");
            } else {
                obj.updatedAt = j["$updatedAt"].get<std::string>();
            }
        }
        if (j.contains("format")) {
            if (j["format"].is_null()) {
                throw appwrite::DeserializationException("Required field 'format' is null");
            } else {
                obj.format = j["format"].get<std::string>();
            }
        }
        if (j.contains("default")) {
            if (j["default"].is_null()) {
                obj.default_ = std::nullopt;
            } else {
                obj.default_ = j["default"].get<std::optional<std::string>>();
            }
        }
        return obj;
    }

    /** @brief Serialize to JSON */
    [[nodiscard]] nlohmann::json toJson() const {
        nlohmann::json j = nlohmann::json::object();
        {
            j["key"] = this->key;
        }
        {
            j["type"] = this->type;
        }
        {
            j["status"] = appwrite::enums::toString(this->status);
        }
        {
            j["error"] = this->error;
        }
        {
            j["required"] = this->required;
        }
        if (this->array.has_value()) {
            j["array"] = this->array.value();
        }
        {
            j["$createdAt"] = this->createdAt;
        }
        {
            j["$updatedAt"] = this->updatedAt;
        }
        {
            j["format"] = this->format;
        }
        if (this->default_.has_value()) {
            j["default"] = this->default_.value();
        }
        return j;
    }
};

/** @brief ADL hook for nlohmann::json */
inline void from_json(const nlohmann::json& j, ColumnDatetime& x) {
    x = ColumnDatetime::fromJson(j);
}

inline void to_json(nlohmann::json& j, const ColumnDatetime& x) {
    j = x.toJson();
}

/**
 * @brief ColumnRelationship
 */
struct ColumnRelationship {
    /** @brief Column Key. */
    std::string key;
    /** @brief Column type. */
    std::string type;
    /** @brief Column status. Possible values: `available`, `processing`, `deleting`, `stuck`, or `failed` */
    appwrite::enums::ColumnStatus status;
    /** @brief Error message. Displays error generated on failure of creating or deleting an column. */
    std::string error;
    /** @brief Is column required? */
    bool required;
    /** @brief Is column an array? */
    std::optional<bool> array = std::nullopt;
    /** @brief Column creation date in ISO 8601 format. */
    std::string createdAt;
    /** @brief Column update date in ISO 8601 format. */
    std::string updatedAt;
    /** @brief The ID of the related table. */
    std::string relatedTable;
    /** @brief The type of the relationship. */
    std::string relationType;
    /** @brief Is the relationship two-way? */
    bool twoWay;
    /** @brief The key of the two-way relationship. */
    std::string twoWayKey;
    /** @brief How deleting the parent document will propagate to child documents. */
    std::string onDelete;
    /** @brief Whether this is the parent or child side of the relationship */
    std::string side;

    /** @brief Deserialize from JSON */
    static ColumnRelationship fromJson(const nlohmann::json& j) {
        ColumnRelationship obj{}; // value-init: zeroes bools, ints, enums
        if (j.contains("key")) {
            if (j["key"].is_null()) {
                throw appwrite::DeserializationException("Required field 'key' is null");
            } else {
                obj.key = j["key"].get<std::string>();
            }
        }
        if (j.contains("type")) {
            if (j["type"].is_null()) {
                throw appwrite::DeserializationException("Required field 'type' is null");
            } else {
                obj.type = j["type"].get<std::string>();
            }
        }
        if (j.contains("status")) {
            if (j["status"].is_null()) {
                throw appwrite::DeserializationException("Required field 'status' is null");
            } else {
                obj.status = j["status"].get<appwrite::enums::ColumnStatus>();
            }
        }
        if (j.contains("error")) {
            if (j["error"].is_null()) {
                throw appwrite::DeserializationException("Required field 'error' is null");
            } else {
                obj.error = j["error"].get<std::string>();
            }
        }
        if (j.contains("required")) {
            if (j["required"].is_null()) {
                throw appwrite::DeserializationException("Required field 'required' is null");
            } else {
                obj.required = j["required"].get<bool>();
            }
        }
        if (j.contains("array")) {
            if (j["array"].is_null()) {
                obj.array = std::nullopt;
            } else {
                obj.array = j["array"].get<std::optional<bool>>();
            }
        }
        if (j.contains("$createdAt")) {
            if (j["$createdAt"].is_null()) {
                throw appwrite::DeserializationException("Required field '$createdAt' is null");
            } else {
                obj.createdAt = j["$createdAt"].get<std::string>();
            }
        }
        if (j.contains("$updatedAt")) {
            if (j["$updatedAt"].is_null()) {
                throw appwrite::DeserializationException("Required field '$updatedAt' is null");
            } else {
                obj.updatedAt = j["$updatedAt"].get<std::string>();
            }
        }
        if (j.contains("relatedTable")) {
            if (j["relatedTable"].is_null()) {
                throw appwrite::DeserializationException("Required field 'relatedTable' is null");
            } else {
                obj.relatedTable = j["relatedTable"].get<std::string>();
            }
        }
        if (j.contains("relationType")) {
            if (j["relationType"].is_null()) {
                throw appwrite::DeserializationException("Required field 'relationType' is null");
            } else {
                obj.relationType = j["relationType"].get<std::string>();
            }
        }
        if (j.contains("twoWay")) {
            if (j["twoWay"].is_null()) {
                throw appwrite::DeserializationException("Required field 'twoWay' is null");
            } else {
                obj.twoWay = j["twoWay"].get<bool>();
            }
        }
        if (j.contains("twoWayKey")) {
            if (j["twoWayKey"].is_null()) {
                throw appwrite::DeserializationException("Required field 'twoWayKey' is null");
            } else {
                obj.twoWayKey = j["twoWayKey"].get<std::string>();
            }
        }
        if (j.contains("onDelete")) {
            if (j["onDelete"].is_null()) {
                throw appwrite::DeserializationException("Required field 'onDelete' is null");
            } else {
                obj.onDelete = j["onDelete"].get<std::string>();
            }
        }
        if (j.contains("side")) {
            if (j["side"].is_null()) {
                throw appwrite::DeserializationException("Required field 'side' is null");
            } else {
                obj.side = j["side"].get<std::string>();
            }
        }
        return obj;
    }

    /** @brief Serialize to JSON */
    [[nodiscard]] nlohmann::json toJson() const {
        nlohmann::json j = nlohmann::json::object();
        {
            j["key"] = this->key;
        }
        {
            j["type"] = this->type;
        }
        {
            j["status"] = appwrite::enums::toString(this->status);
        }
        {
            j["error"] = this->error;
        }
        {
            j["required"] = this->required;
        }
        if (this->array.has_value()) {
            j["array"] = this->array.value();
        }
        {
            j["$createdAt"] = this->createdAt;
        }
        {
            j["$updatedAt"] = this->updatedAt;
        }
        {
            j["relatedTable"] = this->relatedTable;
        }
        {
            j["relationType"] = this->relationType;
        }
        {
            j["twoWay"] = this->twoWay;
        }
        {
            j["twoWayKey"] = this->twoWayKey;
        }
        {
            j["onDelete"] = this->onDelete;
        }
        {
            j["side"] = this->side;
        }
        return j;
    }
};

/** @brief ADL hook for nlohmann::json */
inline void from_json(const nlohmann::json& j, ColumnRelationship& x) {
    x = ColumnRelationship::fromJson(j);
}

inline void to_json(nlohmann::json& j, const ColumnRelationship& x) {
    j = x.toJson();
}

/**
 * @brief ColumnPoint
 */
struct ColumnPoint {
    /** @brief Column Key. */
    std::string key;
    /** @brief Column type. */
    std::string type;
    /** @brief Column status. Possible values: `available`, `processing`, `deleting`, `stuck`, or `failed` */
    appwrite::enums::ColumnStatus status;
    /** @brief Error message. Displays error generated on failure of creating or deleting an column. */
    std::string error;
    /** @brief Is column required? */
    bool required;
    /** @brief Is column an array? */
    std::optional<bool> array = std::nullopt;
    /** @brief Column creation date in ISO 8601 format. */
    std::string createdAt;
    /** @brief Column update date in ISO 8601 format. */
    std::string updatedAt;
    /** @brief Default value for column when not provided. Cannot be set when column is required. */
    std::optional<std::vector<std::string>> default_ = std::nullopt;

    /** @brief Deserialize from JSON */
    static ColumnPoint fromJson(const nlohmann::json& j) {
        ColumnPoint obj{}; // value-init: zeroes bools, ints, enums
        if (j.contains("key")) {
            if (j["key"].is_null()) {
                throw appwrite::DeserializationException("Required field 'key' is null");
            } else {
                obj.key = j["key"].get<std::string>();
            }
        }
        if (j.contains("type")) {
            if (j["type"].is_null()) {
                throw appwrite::DeserializationException("Required field 'type' is null");
            } else {
                obj.type = j["type"].get<std::string>();
            }
        }
        if (j.contains("status")) {
            if (j["status"].is_null()) {
                throw appwrite::DeserializationException("Required field 'status' is null");
            } else {
                obj.status = j["status"].get<appwrite::enums::ColumnStatus>();
            }
        }
        if (j.contains("error")) {
            if (j["error"].is_null()) {
                throw appwrite::DeserializationException("Required field 'error' is null");
            } else {
                obj.error = j["error"].get<std::string>();
            }
        }
        if (j.contains("required")) {
            if (j["required"].is_null()) {
                throw appwrite::DeserializationException("Required field 'required' is null");
            } else {
                obj.required = j["required"].get<bool>();
            }
        }
        if (j.contains("array")) {
            if (j["array"].is_null()) {
                obj.array = std::nullopt;
            } else {
                obj.array = j["array"].get<std::optional<bool>>();
            }
        }
        if (j.contains("$createdAt")) {
            if (j["$createdAt"].is_null()) {
                throw appwrite::DeserializationException("Required field '$createdAt' is null");
            } else {
                obj.createdAt = j["$createdAt"].get<std::string>();
            }
        }
        if (j.contains("$updatedAt")) {
            if (j["$updatedAt"].is_null()) {
                throw appwrite::DeserializationException("Required field '$updatedAt' is null");
            } else {
                obj.updatedAt = j["$updatedAt"].get<std::string>();
            }
        }
        if (j.contains("default")) {
            if (j["default"].is_null()) {
                obj.default_ = std::nullopt;
            } else {
                obj.default_ = j["default"].get<std::optional<std::vector<std::string>>>();
            }
        }
        return obj;
    }

    /** @brief Serialize to JSON */
    [[nodiscard]] nlohmann::json toJson() const {
        nlohmann::json j = nlohmann::json::object();
        {
            j["key"] = this->key;
        }
        {
            j["type"] = this->type;
        }
        {
            j["status"] = appwrite::enums::toString(this->status);
        }
        {
            j["error"] = this->error;
        }
        {
            j["required"] = this->required;
        }
        if (this->array.has_value()) {
            j["array"] = this->array.value();
        }
        {
            j["$createdAt"] = this->createdAt;
        }
        {
            j["$updatedAt"] = this->updatedAt;
        }
        if (this->default_.has_value()) {
            j["default"] = this->default_.value();
        }
        return j;
    }
};

/** @brief ADL hook for nlohmann::json */
inline void from_json(const nlohmann::json& j, ColumnPoint& x) {
    x = ColumnPoint::fromJson(j);
}

inline void to_json(nlohmann::json& j, const ColumnPoint& x) {
    j = x.toJson();
}

/**
 * @brief ColumnLine
 */
struct ColumnLine {
    /** @brief Column Key. */
    std::string key;
    /** @brief Column type. */
    std::string type;
    /** @brief Column status. Possible values: `available`, `processing`, `deleting`, `stuck`, or `failed` */
    appwrite::enums::ColumnStatus status;
    /** @brief Error message. Displays error generated on failure of creating or deleting an column. */
    std::string error;
    /** @brief Is column required? */
    bool required;
    /** @brief Is column an array? */
    std::optional<bool> array = std::nullopt;
    /** @brief Column creation date in ISO 8601 format. */
    std::string createdAt;
    /** @brief Column update date in ISO 8601 format. */
    std::string updatedAt;
    /** @brief Default value for column when not provided. Cannot be set when column is required. */
    std::optional<std::vector<std::string>> default_ = std::nullopt;

    /** @brief Deserialize from JSON */
    static ColumnLine fromJson(const nlohmann::json& j) {
        ColumnLine obj{}; // value-init: zeroes bools, ints, enums
        if (j.contains("key")) {
            if (j["key"].is_null()) {
                throw appwrite::DeserializationException("Required field 'key' is null");
            } else {
                obj.key = j["key"].get<std::string>();
            }
        }
        if (j.contains("type")) {
            if (j["type"].is_null()) {
                throw appwrite::DeserializationException("Required field 'type' is null");
            } else {
                obj.type = j["type"].get<std::string>();
            }
        }
        if (j.contains("status")) {
            if (j["status"].is_null()) {
                throw appwrite::DeserializationException("Required field 'status' is null");
            } else {
                obj.status = j["status"].get<appwrite::enums::ColumnStatus>();
            }
        }
        if (j.contains("error")) {
            if (j["error"].is_null()) {
                throw appwrite::DeserializationException("Required field 'error' is null");
            } else {
                obj.error = j["error"].get<std::string>();
            }
        }
        if (j.contains("required")) {
            if (j["required"].is_null()) {
                throw appwrite::DeserializationException("Required field 'required' is null");
            } else {
                obj.required = j["required"].get<bool>();
            }
        }
        if (j.contains("array")) {
            if (j["array"].is_null()) {
                obj.array = std::nullopt;
            } else {
                obj.array = j["array"].get<std::optional<bool>>();
            }
        }
        if (j.contains("$createdAt")) {
            if (j["$createdAt"].is_null()) {
                throw appwrite::DeserializationException("Required field '$createdAt' is null");
            } else {
                obj.createdAt = j["$createdAt"].get<std::string>();
            }
        }
        if (j.contains("$updatedAt")) {
            if (j["$updatedAt"].is_null()) {
                throw appwrite::DeserializationException("Required field '$updatedAt' is null");
            } else {
                obj.updatedAt = j["$updatedAt"].get<std::string>();
            }
        }
        if (j.contains("default")) {
            if (j["default"].is_null()) {
                obj.default_ = std::nullopt;
            } else {
                obj.default_ = j["default"].get<std::optional<std::vector<std::string>>>();
            }
        }
        return obj;
    }

    /** @brief Serialize to JSON */
    [[nodiscard]] nlohmann::json toJson() const {
        nlohmann::json j = nlohmann::json::object();
        {
            j["key"] = this->key;
        }
        {
            j["type"] = this->type;
        }
        {
            j["status"] = appwrite::enums::toString(this->status);
        }
        {
            j["error"] = this->error;
        }
        {
            j["required"] = this->required;
        }
        if (this->array.has_value()) {
            j["array"] = this->array.value();
        }
        {
            j["$createdAt"] = this->createdAt;
        }
        {
            j["$updatedAt"] = this->updatedAt;
        }
        if (this->default_.has_value()) {
            j["default"] = this->default_.value();
        }
        return j;
    }
};

/** @brief ADL hook for nlohmann::json */
inline void from_json(const nlohmann::json& j, ColumnLine& x) {
    x = ColumnLine::fromJson(j);
}

inline void to_json(nlohmann::json& j, const ColumnLine& x) {
    j = x.toJson();
}

/**
 * @brief ColumnPolygon
 */
struct ColumnPolygon {
    /** @brief Column Key. */
    std::string key;
    /** @brief Column type. */
    std::string type;
    /** @brief Column status. Possible values: `available`, `processing`, `deleting`, `stuck`, or `failed` */
    appwrite::enums::ColumnStatus status;
    /** @brief Error message. Displays error generated on failure of creating or deleting an column. */
    std::string error;
    /** @brief Is column required? */
    bool required;
    /** @brief Is column an array? */
    std::optional<bool> array = std::nullopt;
    /** @brief Column creation date in ISO 8601 format. */
    std::string createdAt;
    /** @brief Column update date in ISO 8601 format. */
    std::string updatedAt;
    /** @brief Default value for column when not provided. Cannot be set when column is required. */
    std::optional<std::vector<std::string>> default_ = std::nullopt;

    /** @brief Deserialize from JSON */
    static ColumnPolygon fromJson(const nlohmann::json& j) {
        ColumnPolygon obj{}; // value-init: zeroes bools, ints, enums
        if (j.contains("key")) {
            if (j["key"].is_null()) {
                throw appwrite::DeserializationException("Required field 'key' is null");
            } else {
                obj.key = j["key"].get<std::string>();
            }
        }
        if (j.contains("type")) {
            if (j["type"].is_null()) {
                throw appwrite::DeserializationException("Required field 'type' is null");
            } else {
                obj.type = j["type"].get<std::string>();
            }
        }
        if (j.contains("status")) {
            if (j["status"].is_null()) {
                throw appwrite::DeserializationException("Required field 'status' is null");
            } else {
                obj.status = j["status"].get<appwrite::enums::ColumnStatus>();
            }
        }
        if (j.contains("error")) {
            if (j["error"].is_null()) {
                throw appwrite::DeserializationException("Required field 'error' is null");
            } else {
                obj.error = j["error"].get<std::string>();
            }
        }
        if (j.contains("required")) {
            if (j["required"].is_null()) {
                throw appwrite::DeserializationException("Required field 'required' is null");
            } else {
                obj.required = j["required"].get<bool>();
            }
        }
        if (j.contains("array")) {
            if (j["array"].is_null()) {
                obj.array = std::nullopt;
            } else {
                obj.array = j["array"].get<std::optional<bool>>();
            }
        }
        if (j.contains("$createdAt")) {
            if (j["$createdAt"].is_null()) {
                throw appwrite::DeserializationException("Required field '$createdAt' is null");
            } else {
                obj.createdAt = j["$createdAt"].get<std::string>();
            }
        }
        if (j.contains("$updatedAt")) {
            if (j["$updatedAt"].is_null()) {
                throw appwrite::DeserializationException("Required field '$updatedAt' is null");
            } else {
                obj.updatedAt = j["$updatedAt"].get<std::string>();
            }
        }
        if (j.contains("default")) {
            if (j["default"].is_null()) {
                obj.default_ = std::nullopt;
            } else {
                obj.default_ = j["default"].get<std::optional<std::vector<std::string>>>();
            }
        }
        return obj;
    }

    /** @brief Serialize to JSON */
    [[nodiscard]] nlohmann::json toJson() const {
        nlohmann::json j = nlohmann::json::object();
        {
            j["key"] = this->key;
        }
        {
            j["type"] = this->type;
        }
        {
            j["status"] = appwrite::enums::toString(this->status);
        }
        {
            j["error"] = this->error;
        }
        {
            j["required"] = this->required;
        }
        if (this->array.has_value()) {
            j["array"] = this->array.value();
        }
        {
            j["$createdAt"] = this->createdAt;
        }
        {
            j["$updatedAt"] = this->updatedAt;
        }
        if (this->default_.has_value()) {
            j["default"] = this->default_.value();
        }
        return j;
    }
};

/** @brief ADL hook for nlohmann::json */
inline void from_json(const nlohmann::json& j, ColumnPolygon& x) {
    x = ColumnPolygon::fromJson(j);
}

inline void to_json(nlohmann::json& j, const ColumnPolygon& x) {
    j = x.toJson();
}

/**
 * @brief ColumnVarchar
 */
struct ColumnVarchar {
    /** @brief Column Key. */
    std::string key;
    /** @brief Column type. */
    std::string type;
    /** @brief Column status. Possible values: `available`, `processing`, `deleting`, `stuck`, or `failed` */
    appwrite::enums::ColumnStatus status;
    /** @brief Error message. Displays error generated on failure of creating or deleting an column. */
    std::string error;
    /** @brief Is column required? */
    bool required;
    /** @brief Is column an array? */
    std::optional<bool> array = std::nullopt;
    /** @brief Column creation date in ISO 8601 format. */
    std::string createdAt;
    /** @brief Column update date in ISO 8601 format. */
    std::string updatedAt;
    /** @brief Column size. */
    int64_t size;
    /** @brief Default value for column when not provided. Cannot be set when column is required. */
    std::optional<std::string> default_ = std::nullopt;
    /** @brief Defines whether this column is encrypted or not. */
    std::optional<bool> encrypt = std::nullopt;

    /** @brief Deserialize from JSON */
    static ColumnVarchar fromJson(const nlohmann::json& j) {
        ColumnVarchar obj{}; // value-init: zeroes bools, ints, enums
        if (j.contains("key")) {
            if (j["key"].is_null()) {
                throw appwrite::DeserializationException("Required field 'key' is null");
            } else {
                obj.key = j["key"].get<std::string>();
            }
        }
        if (j.contains("type")) {
            if (j["type"].is_null()) {
                throw appwrite::DeserializationException("Required field 'type' is null");
            } else {
                obj.type = j["type"].get<std::string>();
            }
        }
        if (j.contains("status")) {
            if (j["status"].is_null()) {
                throw appwrite::DeserializationException("Required field 'status' is null");
            } else {
                obj.status = j["status"].get<appwrite::enums::ColumnStatus>();
            }
        }
        if (j.contains("error")) {
            if (j["error"].is_null()) {
                throw appwrite::DeserializationException("Required field 'error' is null");
            } else {
                obj.error = j["error"].get<std::string>();
            }
        }
        if (j.contains("required")) {
            if (j["required"].is_null()) {
                throw appwrite::DeserializationException("Required field 'required' is null");
            } else {
                obj.required = j["required"].get<bool>();
            }
        }
        if (j.contains("array")) {
            if (j["array"].is_null()) {
                obj.array = std::nullopt;
            } else {
                obj.array = j["array"].get<std::optional<bool>>();
            }
        }
        if (j.contains("$createdAt")) {
            if (j["$createdAt"].is_null()) {
                throw appwrite::DeserializationException("Required field '$createdAt' is null");
            } else {
                obj.createdAt = j["$createdAt"].get<std::string>();
            }
        }
        if (j.contains("$updatedAt")) {
            if (j["$updatedAt"].is_null()) {
                throw appwrite::DeserializationException("Required field '$updatedAt' is null");
            } else {
                obj.updatedAt = j["$updatedAt"].get<std::string>();
            }
        }
        if (j.contains("size")) {
            if (j["size"].is_null()) {
                throw appwrite::DeserializationException("Required field 'size' is null");
            } else {
                obj.size = j["size"].get<int64_t>();
            }
        }
        if (j.contains("default")) {
            if (j["default"].is_null()) {
                obj.default_ = std::nullopt;
            } else {
                obj.default_ = j["default"].get<std::optional<std::string>>();
            }
        }
        if (j.contains("encrypt")) {
            if (j["encrypt"].is_null()) {
                obj.encrypt = std::nullopt;
            } else {
                obj.encrypt = j["encrypt"].get<std::optional<bool>>();
            }
        }
        return obj;
    }

    /** @brief Serialize to JSON */
    [[nodiscard]] nlohmann::json toJson() const {
        nlohmann::json j = nlohmann::json::object();
        {
            j["key"] = this->key;
        }
        {
            j["type"] = this->type;
        }
        {
            j["status"] = appwrite::enums::toString(this->status);
        }
        {
            j["error"] = this->error;
        }
        {
            j["required"] = this->required;
        }
        if (this->array.has_value()) {
            j["array"] = this->array.value();
        }
        {
            j["$createdAt"] = this->createdAt;
        }
        {
            j["$updatedAt"] = this->updatedAt;
        }
        {
            j["size"] = this->size;
        }
        if (this->default_.has_value()) {
            j["default"] = this->default_.value();
        }
        if (this->encrypt.has_value()) {
            j["encrypt"] = this->encrypt.value();
        }
        return j;
    }
};

/** @brief ADL hook for nlohmann::json */
inline void from_json(const nlohmann::json& j, ColumnVarchar& x) {
    x = ColumnVarchar::fromJson(j);
}

inline void to_json(nlohmann::json& j, const ColumnVarchar& x) {
    j = x.toJson();
}

/**
 * @brief ColumnText
 */
struct ColumnText {
    /** @brief Column Key. */
    std::string key;
    /** @brief Column type. */
    std::string type;
    /** @brief Column status. Possible values: `available`, `processing`, `deleting`, `stuck`, or `failed` */
    appwrite::enums::ColumnStatus status;
    /** @brief Error message. Displays error generated on failure of creating or deleting an column. */
    std::string error;
    /** @brief Is column required? */
    bool required;
    /** @brief Is column an array? */
    std::optional<bool> array = std::nullopt;
    /** @brief Column creation date in ISO 8601 format. */
    std::string createdAt;
    /** @brief Column update date in ISO 8601 format. */
    std::string updatedAt;
    /** @brief Default value for column when not provided. Cannot be set when column is required. */
    std::optional<std::string> default_ = std::nullopt;
    /** @brief Defines whether this column is encrypted or not. */
    std::optional<bool> encrypt = std::nullopt;

    /** @brief Deserialize from JSON */
    static ColumnText fromJson(const nlohmann::json& j) {
        ColumnText obj{}; // value-init: zeroes bools, ints, enums
        if (j.contains("key")) {
            if (j["key"].is_null()) {
                throw appwrite::DeserializationException("Required field 'key' is null");
            } else {
                obj.key = j["key"].get<std::string>();
            }
        }
        if (j.contains("type")) {
            if (j["type"].is_null()) {
                throw appwrite::DeserializationException("Required field 'type' is null");
            } else {
                obj.type = j["type"].get<std::string>();
            }
        }
        if (j.contains("status")) {
            if (j["status"].is_null()) {
                throw appwrite::DeserializationException("Required field 'status' is null");
            } else {
                obj.status = j["status"].get<appwrite::enums::ColumnStatus>();
            }
        }
        if (j.contains("error")) {
            if (j["error"].is_null()) {
                throw appwrite::DeserializationException("Required field 'error' is null");
            } else {
                obj.error = j["error"].get<std::string>();
            }
        }
        if (j.contains("required")) {
            if (j["required"].is_null()) {
                throw appwrite::DeserializationException("Required field 'required' is null");
            } else {
                obj.required = j["required"].get<bool>();
            }
        }
        if (j.contains("array")) {
            if (j["array"].is_null()) {
                obj.array = std::nullopt;
            } else {
                obj.array = j["array"].get<std::optional<bool>>();
            }
        }
        if (j.contains("$createdAt")) {
            if (j["$createdAt"].is_null()) {
                throw appwrite::DeserializationException("Required field '$createdAt' is null");
            } else {
                obj.createdAt = j["$createdAt"].get<std::string>();
            }
        }
        if (j.contains("$updatedAt")) {
            if (j["$updatedAt"].is_null()) {
                throw appwrite::DeserializationException("Required field '$updatedAt' is null");
            } else {
                obj.updatedAt = j["$updatedAt"].get<std::string>();
            }
        }
        if (j.contains("default")) {
            if (j["default"].is_null()) {
                obj.default_ = std::nullopt;
            } else {
                obj.default_ = j["default"].get<std::optional<std::string>>();
            }
        }
        if (j.contains("encrypt")) {
            if (j["encrypt"].is_null()) {
                obj.encrypt = std::nullopt;
            } else {
                obj.encrypt = j["encrypt"].get<std::optional<bool>>();
            }
        }
        return obj;
    }

    /** @brief Serialize to JSON */
    [[nodiscard]] nlohmann::json toJson() const {
        nlohmann::json j = nlohmann::json::object();
        {
            j["key"] = this->key;
        }
        {
            j["type"] = this->type;
        }
        {
            j["status"] = appwrite::enums::toString(this->status);
        }
        {
            j["error"] = this->error;
        }
        {
            j["required"] = this->required;
        }
        if (this->array.has_value()) {
            j["array"] = this->array.value();
        }
        {
            j["$createdAt"] = this->createdAt;
        }
        {
            j["$updatedAt"] = this->updatedAt;
        }
        if (this->default_.has_value()) {
            j["default"] = this->default_.value();
        }
        if (this->encrypt.has_value()) {
            j["encrypt"] = this->encrypt.value();
        }
        return j;
    }
};

/** @brief ADL hook for nlohmann::json */
inline void from_json(const nlohmann::json& j, ColumnText& x) {
    x = ColumnText::fromJson(j);
}

inline void to_json(nlohmann::json& j, const ColumnText& x) {
    j = x.toJson();
}

/**
 * @brief ColumnMediumtext
 */
struct ColumnMediumtext {
    /** @brief Column Key. */
    std::string key;
    /** @brief Column type. */
    std::string type;
    /** @brief Column status. Possible values: `available`, `processing`, `deleting`, `stuck`, or `failed` */
    appwrite::enums::ColumnStatus status;
    /** @brief Error message. Displays error generated on failure of creating or deleting an column. */
    std::string error;
    /** @brief Is column required? */
    bool required;
    /** @brief Is column an array? */
    std::optional<bool> array = std::nullopt;
    /** @brief Column creation date in ISO 8601 format. */
    std::string createdAt;
    /** @brief Column update date in ISO 8601 format. */
    std::string updatedAt;
    /** @brief Default value for column when not provided. Cannot be set when column is required. */
    std::optional<std::string> default_ = std::nullopt;
    /** @brief Defines whether this column is encrypted or not. */
    std::optional<bool> encrypt = std::nullopt;

    /** @brief Deserialize from JSON */
    static ColumnMediumtext fromJson(const nlohmann::json& j) {
        ColumnMediumtext obj{}; // value-init: zeroes bools, ints, enums
        if (j.contains("key")) {
            if (j["key"].is_null()) {
                throw appwrite::DeserializationException("Required field 'key' is null");
            } else {
                obj.key = j["key"].get<std::string>();
            }
        }
        if (j.contains("type")) {
            if (j["type"].is_null()) {
                throw appwrite::DeserializationException("Required field 'type' is null");
            } else {
                obj.type = j["type"].get<std::string>();
            }
        }
        if (j.contains("status")) {
            if (j["status"].is_null()) {
                throw appwrite::DeserializationException("Required field 'status' is null");
            } else {
                obj.status = j["status"].get<appwrite::enums::ColumnStatus>();
            }
        }
        if (j.contains("error")) {
            if (j["error"].is_null()) {
                throw appwrite::DeserializationException("Required field 'error' is null");
            } else {
                obj.error = j["error"].get<std::string>();
            }
        }
        if (j.contains("required")) {
            if (j["required"].is_null()) {
                throw appwrite::DeserializationException("Required field 'required' is null");
            } else {
                obj.required = j["required"].get<bool>();
            }
        }
        if (j.contains("array")) {
            if (j["array"].is_null()) {
                obj.array = std::nullopt;
            } else {
                obj.array = j["array"].get<std::optional<bool>>();
            }
        }
        if (j.contains("$createdAt")) {
            if (j["$createdAt"].is_null()) {
                throw appwrite::DeserializationException("Required field '$createdAt' is null");
            } else {
                obj.createdAt = j["$createdAt"].get<std::string>();
            }
        }
        if (j.contains("$updatedAt")) {
            if (j["$updatedAt"].is_null()) {
                throw appwrite::DeserializationException("Required field '$updatedAt' is null");
            } else {
                obj.updatedAt = j["$updatedAt"].get<std::string>();
            }
        }
        if (j.contains("default")) {
            if (j["default"].is_null()) {
                obj.default_ = std::nullopt;
            } else {
                obj.default_ = j["default"].get<std::optional<std::string>>();
            }
        }
        if (j.contains("encrypt")) {
            if (j["encrypt"].is_null()) {
                obj.encrypt = std::nullopt;
            } else {
                obj.encrypt = j["encrypt"].get<std::optional<bool>>();
            }
        }
        return obj;
    }

    /** @brief Serialize to JSON */
    [[nodiscard]] nlohmann::json toJson() const {
        nlohmann::json j = nlohmann::json::object();
        {
            j["key"] = this->key;
        }
        {
            j["type"] = this->type;
        }
        {
            j["status"] = appwrite::enums::toString(this->status);
        }
        {
            j["error"] = this->error;
        }
        {
            j["required"] = this->required;
        }
        if (this->array.has_value()) {
            j["array"] = this->array.value();
        }
        {
            j["$createdAt"] = this->createdAt;
        }
        {
            j["$updatedAt"] = this->updatedAt;
        }
        if (this->default_.has_value()) {
            j["default"] = this->default_.value();
        }
        if (this->encrypt.has_value()) {
            j["encrypt"] = this->encrypt.value();
        }
        return j;
    }
};

/** @brief ADL hook for nlohmann::json */
inline void from_json(const nlohmann::json& j, ColumnMediumtext& x) {
    x = ColumnMediumtext::fromJson(j);
}

inline void to_json(nlohmann::json& j, const ColumnMediumtext& x) {
    j = x.toJson();
}

/**
 * @brief ColumnLongtext
 */
struct ColumnLongtext {
    /** @brief Column Key. */
    std::string key;
    /** @brief Column type. */
    std::string type;
    /** @brief Column status. Possible values: `available`, `processing`, `deleting`, `stuck`, or `failed` */
    appwrite::enums::ColumnStatus status;
    /** @brief Error message. Displays error generated on failure of creating or deleting an column. */
    std::string error;
    /** @brief Is column required? */
    bool required;
    /** @brief Is column an array? */
    std::optional<bool> array = std::nullopt;
    /** @brief Column creation date in ISO 8601 format. */
    std::string createdAt;
    /** @brief Column update date in ISO 8601 format. */
    std::string updatedAt;
    /** @brief Default value for column when not provided. Cannot be set when column is required. */
    std::optional<std::string> default_ = std::nullopt;
    /** @brief Defines whether this column is encrypted or not. */
    std::optional<bool> encrypt = std::nullopt;

    /** @brief Deserialize from JSON */
    static ColumnLongtext fromJson(const nlohmann::json& j) {
        ColumnLongtext obj{}; // value-init: zeroes bools, ints, enums
        if (j.contains("key")) {
            if (j["key"].is_null()) {
                throw appwrite::DeserializationException("Required field 'key' is null");
            } else {
                obj.key = j["key"].get<std::string>();
            }
        }
        if (j.contains("type")) {
            if (j["type"].is_null()) {
                throw appwrite::DeserializationException("Required field 'type' is null");
            } else {
                obj.type = j["type"].get<std::string>();
            }
        }
        if (j.contains("status")) {
            if (j["status"].is_null()) {
                throw appwrite::DeserializationException("Required field 'status' is null");
            } else {
                obj.status = j["status"].get<appwrite::enums::ColumnStatus>();
            }
        }
        if (j.contains("error")) {
            if (j["error"].is_null()) {
                throw appwrite::DeserializationException("Required field 'error' is null");
            } else {
                obj.error = j["error"].get<std::string>();
            }
        }
        if (j.contains("required")) {
            if (j["required"].is_null()) {
                throw appwrite::DeserializationException("Required field 'required' is null");
            } else {
                obj.required = j["required"].get<bool>();
            }
        }
        if (j.contains("array")) {
            if (j["array"].is_null()) {
                obj.array = std::nullopt;
            } else {
                obj.array = j["array"].get<std::optional<bool>>();
            }
        }
        if (j.contains("$createdAt")) {
            if (j["$createdAt"].is_null()) {
                throw appwrite::DeserializationException("Required field '$createdAt' is null");
            } else {
                obj.createdAt = j["$createdAt"].get<std::string>();
            }
        }
        if (j.contains("$updatedAt")) {
            if (j["$updatedAt"].is_null()) {
                throw appwrite::DeserializationException("Required field '$updatedAt' is null");
            } else {
                obj.updatedAt = j["$updatedAt"].get<std::string>();
            }
        }
        if (j.contains("default")) {
            if (j["default"].is_null()) {
                obj.default_ = std::nullopt;
            } else {
                obj.default_ = j["default"].get<std::optional<std::string>>();
            }
        }
        if (j.contains("encrypt")) {
            if (j["encrypt"].is_null()) {
                obj.encrypt = std::nullopt;
            } else {
                obj.encrypt = j["encrypt"].get<std::optional<bool>>();
            }
        }
        return obj;
    }

    /** @brief Serialize to JSON */
    [[nodiscard]] nlohmann::json toJson() const {
        nlohmann::json j = nlohmann::json::object();
        {
            j["key"] = this->key;
        }
        {
            j["type"] = this->type;
        }
        {
            j["status"] = appwrite::enums::toString(this->status);
        }
        {
            j["error"] = this->error;
        }
        {
            j["required"] = this->required;
        }
        if (this->array.has_value()) {
            j["array"] = this->array.value();
        }
        {
            j["$createdAt"] = this->createdAt;
        }
        {
            j["$updatedAt"] = this->updatedAt;
        }
        if (this->default_.has_value()) {
            j["default"] = this->default_.value();
        }
        if (this->encrypt.has_value()) {
            j["encrypt"] = this->encrypt.value();
        }
        return j;
    }
};

/** @brief ADL hook for nlohmann::json */
inline void from_json(const nlohmann::json& j, ColumnLongtext& x) {
    x = ColumnLongtext::fromJson(j);
}

inline void to_json(nlohmann::json& j, const ColumnLongtext& x) {
    j = x.toJson();
}

/**
 * @brief AlgoMD5
 */
struct AlgoMd5 {
    /** @brief Algo type. */
    std::string type;

    /** @brief Deserialize from JSON */
    static AlgoMd5 fromJson(const nlohmann::json& j) {
        AlgoMd5 obj{}; // value-init: zeroes bools, ints, enums
        if (j.contains("type")) {
            if (j["type"].is_null()) {
                throw appwrite::DeserializationException("Required field 'type' is null");
            } else {
                obj.type = j["type"].get<std::string>();
            }
        }
        return obj;
    }

    /** @brief Serialize to JSON */
    [[nodiscard]] nlohmann::json toJson() const {
        nlohmann::json j = nlohmann::json::object();
        {
            j["type"] = this->type;
        }
        return j;
    }
};

/** @brief ADL hook for nlohmann::json */
inline void from_json(const nlohmann::json& j, AlgoMd5& x) {
    x = AlgoMd5::fromJson(j);
}

inline void to_json(nlohmann::json& j, const AlgoMd5& x) {
    j = x.toJson();
}

/**
 * @brief AlgoSHA
 */
struct AlgoSha {
    /** @brief Algo type. */
    std::string type;

    /** @brief Deserialize from JSON */
    static AlgoSha fromJson(const nlohmann::json& j) {
        AlgoSha obj{}; // value-init: zeroes bools, ints, enums
        if (j.contains("type")) {
            if (j["type"].is_null()) {
                throw appwrite::DeserializationException("Required field 'type' is null");
            } else {
                obj.type = j["type"].get<std::string>();
            }
        }
        return obj;
    }

    /** @brief Serialize to JSON */
    [[nodiscard]] nlohmann::json toJson() const {
        nlohmann::json j = nlohmann::json::object();
        {
            j["type"] = this->type;
        }
        return j;
    }
};

/** @brief ADL hook for nlohmann::json */
inline void from_json(const nlohmann::json& j, AlgoSha& x) {
    x = AlgoSha::fromJson(j);
}

inline void to_json(nlohmann::json& j, const AlgoSha& x) {
    j = x.toJson();
}

/**
 * @brief AlgoPHPass
 */
struct AlgoPhpass {
    /** @brief Algo type. */
    std::string type;

    /** @brief Deserialize from JSON */
    static AlgoPhpass fromJson(const nlohmann::json& j) {
        AlgoPhpass obj{}; // value-init: zeroes bools, ints, enums
        if (j.contains("type")) {
            if (j["type"].is_null()) {
                throw appwrite::DeserializationException("Required field 'type' is null");
            } else {
                obj.type = j["type"].get<std::string>();
            }
        }
        return obj;
    }

    /** @brief Serialize to JSON */
    [[nodiscard]] nlohmann::json toJson() const {
        nlohmann::json j = nlohmann::json::object();
        {
            j["type"] = this->type;
        }
        return j;
    }
};

/** @brief ADL hook for nlohmann::json */
inline void from_json(const nlohmann::json& j, AlgoPhpass& x) {
    x = AlgoPhpass::fromJson(j);
}

inline void to_json(nlohmann::json& j, const AlgoPhpass& x) {
    j = x.toJson();
}

/**
 * @brief AlgoBcrypt
 */
struct AlgoBcrypt {
    /** @brief Algo type. */
    std::string type;

    /** @brief Deserialize from JSON */
    static AlgoBcrypt fromJson(const nlohmann::json& j) {
        AlgoBcrypt obj{}; // value-init: zeroes bools, ints, enums
        if (j.contains("type")) {
            if (j["type"].is_null()) {
                throw appwrite::DeserializationException("Required field 'type' is null");
            } else {
                obj.type = j["type"].get<std::string>();
            }
        }
        return obj;
    }

    /** @brief Serialize to JSON */
    [[nodiscard]] nlohmann::json toJson() const {
        nlohmann::json j = nlohmann::json::object();
        {
            j["type"] = this->type;
        }
        return j;
    }
};

/** @brief ADL hook for nlohmann::json */
inline void from_json(const nlohmann::json& j, AlgoBcrypt& x) {
    x = AlgoBcrypt::fromJson(j);
}

inline void to_json(nlohmann::json& j, const AlgoBcrypt& x) {
    j = x.toJson();
}

/**
 * @brief AlgoScrypt
 */
struct AlgoScrypt {
    /** @brief Algo type. */
    std::string type;
    /** @brief CPU complexity of computed hash. */
    int64_t costCpu;
    /** @brief Memory complexity of computed hash. */
    int64_t costMemory;
    /** @brief Parallelization of computed hash. */
    int64_t costParallel;
    /** @brief Length used to compute hash. */
    int64_t length;

    /** @brief Deserialize from JSON */
    static AlgoScrypt fromJson(const nlohmann::json& j) {
        AlgoScrypt obj{}; // value-init: zeroes bools, ints, enums
        if (j.contains("type")) {
            if (j["type"].is_null()) {
                throw appwrite::DeserializationException("Required field 'type' is null");
            } else {
                obj.type = j["type"].get<std::string>();
            }
        }
        if (j.contains("costCpu")) {
            if (j["costCpu"].is_null()) {
                throw appwrite::DeserializationException("Required field 'costCpu' is null");
            } else {
                obj.costCpu = j["costCpu"].get<int64_t>();
            }
        }
        if (j.contains("costMemory")) {
            if (j["costMemory"].is_null()) {
                throw appwrite::DeserializationException("Required field 'costMemory' is null");
            } else {
                obj.costMemory = j["costMemory"].get<int64_t>();
            }
        }
        if (j.contains("costParallel")) {
            if (j["costParallel"].is_null()) {
                throw appwrite::DeserializationException("Required field 'costParallel' is null");
            } else {
                obj.costParallel = j["costParallel"].get<int64_t>();
            }
        }
        if (j.contains("length")) {
            if (j["length"].is_null()) {
                throw appwrite::DeserializationException("Required field 'length' is null");
            } else {
                obj.length = j["length"].get<int64_t>();
            }
        }
        return obj;
    }

    /** @brief Serialize to JSON */
    [[nodiscard]] nlohmann::json toJson() const {
        nlohmann::json j = nlohmann::json::object();
        {
            j["type"] = this->type;
        }
        {
            j["costCpu"] = this->costCpu;
        }
        {
            j["costMemory"] = this->costMemory;
        }
        {
            j["costParallel"] = this->costParallel;
        }
        {
            j["length"] = this->length;
        }
        return j;
    }
};

/** @brief ADL hook for nlohmann::json */
inline void from_json(const nlohmann::json& j, AlgoScrypt& x) {
    x = AlgoScrypt::fromJson(j);
}

inline void to_json(nlohmann::json& j, const AlgoScrypt& x) {
    j = x.toJson();
}

/**
 * @brief AlgoScryptModified
 */
struct AlgoScryptModified {
    /** @brief Algo type. */
    std::string type;
    /** @brief Salt used to compute hash. */
    std::string salt;
    /** @brief Separator used to compute hash. */
    std::string saltSeparator;
    /** @brief Key used to compute hash. */
    std::string signerKey;

    /** @brief Deserialize from JSON */
    static AlgoScryptModified fromJson(const nlohmann::json& j) {
        AlgoScryptModified obj{}; // value-init: zeroes bools, ints, enums
        if (j.contains("type")) {
            if (j["type"].is_null()) {
                throw appwrite::DeserializationException("Required field 'type' is null");
            } else {
                obj.type = j["type"].get<std::string>();
            }
        }
        if (j.contains("salt")) {
            if (j["salt"].is_null()) {
                throw appwrite::DeserializationException("Required field 'salt' is null");
            } else {
                obj.salt = j["salt"].get<std::string>();
            }
        }
        if (j.contains("saltSeparator")) {
            if (j["saltSeparator"].is_null()) {
                throw appwrite::DeserializationException("Required field 'saltSeparator' is null");
            } else {
                obj.saltSeparator = j["saltSeparator"].get<std::string>();
            }
        }
        if (j.contains("signerKey")) {
            if (j["signerKey"].is_null()) {
                throw appwrite::DeserializationException("Required field 'signerKey' is null");
            } else {
                obj.signerKey = j["signerKey"].get<std::string>();
            }
        }
        return obj;
    }

    /** @brief Serialize to JSON */
    [[nodiscard]] nlohmann::json toJson() const {
        nlohmann::json j = nlohmann::json::object();
        {
            j["type"] = this->type;
        }
        {
            j["salt"] = this->salt;
        }
        {
            j["saltSeparator"] = this->saltSeparator;
        }
        {
            j["signerKey"] = this->signerKey;
        }
        return j;
    }
};

/** @brief ADL hook for nlohmann::json */
inline void from_json(const nlohmann::json& j, AlgoScryptModified& x) {
    x = AlgoScryptModified::fromJson(j);
}

inline void to_json(nlohmann::json& j, const AlgoScryptModified& x) {
    j = x.toJson();
}

/**
 * @brief AlgoArgon2
 */
struct AlgoArgon2 {
    /** @brief Algo type. */
    std::string type;
    /** @brief Memory used to compute hash. */
    int64_t memoryCost;
    /** @brief Amount of time consumed to compute hash */
    int64_t timeCost;
    /** @brief Number of threads used to compute hash. */
    int64_t threads;

    /** @brief Deserialize from JSON */
    static AlgoArgon2 fromJson(const nlohmann::json& j) {
        AlgoArgon2 obj{}; // value-init: zeroes bools, ints, enums
        if (j.contains("type")) {
            if (j["type"].is_null()) {
                throw appwrite::DeserializationException("Required field 'type' is null");
            } else {
                obj.type = j["type"].get<std::string>();
            }
        }
        if (j.contains("memoryCost")) {
            if (j["memoryCost"].is_null()) {
                throw appwrite::DeserializationException("Required field 'memoryCost' is null");
            } else {
                obj.memoryCost = j["memoryCost"].get<int64_t>();
            }
        }
        if (j.contains("timeCost")) {
            if (j["timeCost"].is_null()) {
                throw appwrite::DeserializationException("Required field 'timeCost' is null");
            } else {
                obj.timeCost = j["timeCost"].get<int64_t>();
            }
        }
        if (j.contains("threads")) {
            if (j["threads"].is_null()) {
                throw appwrite::DeserializationException("Required field 'threads' is null");
            } else {
                obj.threads = j["threads"].get<int64_t>();
            }
        }
        return obj;
    }

    /** @brief Serialize to JSON */
    [[nodiscard]] nlohmann::json toJson() const {
        nlohmann::json j = nlohmann::json::object();
        {
            j["type"] = this->type;
        }
        {
            j["memoryCost"] = this->memoryCost;
        }
        {
            j["timeCost"] = this->timeCost;
        }
        {
            j["threads"] = this->threads;
        }
        return j;
    }
};

/** @brief ADL hook for nlohmann::json */
inline void from_json(const nlohmann::json& j, AlgoArgon2& x) {
    x = AlgoArgon2::fromJson(j);
}

inline void to_json(nlohmann::json& j, const AlgoArgon2& x) {
    j = x.toJson();
}

/**
 * @brief Token
 */
struct Token {
    /** @brief Token ID. */
    std::string id;
    /** @brief Token creation date in ISO 8601 format. */
    std::string createdAt;
    /** @brief User ID. */
    std::string userId;
    /** @brief Token secret key. This will return an empty string unless the response is returned using an API key or as part of a webhook payload. */
    std::string secret;
    /** @brief Token expiration date in ISO 8601 format. */
    std::string expire;
    /** @brief Security phrase of a token. Empty if security phrase was not requested when creating a token. It includes randomly generated phrase which is also sent in the external resource such as email. */
    std::string phrase;

    /** @brief Deserialize from JSON */
    static Token fromJson(const nlohmann::json& j) {
        Token obj{}; // value-init: zeroes bools, ints, enums
        if (j.contains("$id")) {
            if (j["$id"].is_null()) {
                throw appwrite::DeserializationException("Required field '$id' is null");
            } else {
                obj.id = j["$id"].get<std::string>();
            }
        }
        if (j.contains("$createdAt")) {
            if (j["$createdAt"].is_null()) {
                throw appwrite::DeserializationException("Required field '$createdAt' is null");
            } else {
                obj.createdAt = j["$createdAt"].get<std::string>();
            }
        }
        if (j.contains("userId")) {
            if (j["userId"].is_null()) {
                throw appwrite::DeserializationException("Required field 'userId' is null");
            } else {
                obj.userId = j["userId"].get<std::string>();
            }
        }
        if (j.contains("secret")) {
            if (j["secret"].is_null()) {
                throw appwrite::DeserializationException("Required field 'secret' is null");
            } else {
                obj.secret = j["secret"].get<std::string>();
            }
        }
        if (j.contains("expire")) {
            if (j["expire"].is_null()) {
                throw appwrite::DeserializationException("Required field 'expire' is null");
            } else {
                obj.expire = j["expire"].get<std::string>();
            }
        }
        if (j.contains("phrase")) {
            if (j["phrase"].is_null()) {
                throw appwrite::DeserializationException("Required field 'phrase' is null");
            } else {
                obj.phrase = j["phrase"].get<std::string>();
            }
        }
        return obj;
    }

    /** @brief Serialize to JSON */
    [[nodiscard]] nlohmann::json toJson() const {
        nlohmann::json j = nlohmann::json::object();
        {
            j["$id"] = this->id;
        }
        {
            j["$createdAt"] = this->createdAt;
        }
        {
            j["userId"] = this->userId;
        }
        {
            j["secret"] = this->secret;
        }
        {
            j["expire"] = this->expire;
        }
        {
            j["phrase"] = this->phrase;
        }
        return j;
    }
};

/** @brief ADL hook for nlohmann::json */
inline void from_json(const nlohmann::json& j, Token& x) {
    x = Token::fromJson(j);
}

inline void to_json(nlohmann::json& j, const Token& x) {
    j = x.toJson();
}

/**
 * @brief JWT
 */
struct Jwt {
    /** @brief JWT encoded string. */
    std::string jwt;

    /** @brief Deserialize from JSON */
    static Jwt fromJson(const nlohmann::json& j) {
        Jwt obj{}; // value-init: zeroes bools, ints, enums
        if (j.contains("jwt")) {
            if (j["jwt"].is_null()) {
                throw appwrite::DeserializationException("Required field 'jwt' is null");
            } else {
                obj.jwt = j["jwt"].get<std::string>();
            }
        }
        return obj;
    }

    /** @brief Serialize to JSON */
    [[nodiscard]] nlohmann::json toJson() const {
        nlohmann::json j = nlohmann::json::object();
        {
            j["jwt"] = this->jwt;
        }
        return j;
    }
};

/** @brief ADL hook for nlohmann::json */
inline void from_json(const nlohmann::json& j, Jwt& x) {
    x = Jwt::fromJson(j);
}

inline void to_json(nlohmann::json& j, const Jwt& x) {
    j = x.toJson();
}

/**
 * @brief MFA Challenge
 */
struct MfaChallenge {
    /** @brief Token ID. */
    std::string id;
    /** @brief Token creation date in ISO 8601 format. */
    std::string createdAt;
    /** @brief User ID. */
    std::string userId;
    /** @brief Token expiration date in ISO 8601 format. */
    std::string expire;

    /** @brief Deserialize from JSON */
    static MfaChallenge fromJson(const nlohmann::json& j) {
        MfaChallenge obj{}; // value-init: zeroes bools, ints, enums
        if (j.contains("$id")) {
            if (j["$id"].is_null()) {
                throw appwrite::DeserializationException("Required field '$id' is null");
            } else {
                obj.id = j["$id"].get<std::string>();
            }
        }
        if (j.contains("$createdAt")) {
            if (j["$createdAt"].is_null()) {
                throw appwrite::DeserializationException("Required field '$createdAt' is null");
            } else {
                obj.createdAt = j["$createdAt"].get<std::string>();
            }
        }
        if (j.contains("userId")) {
            if (j["userId"].is_null()) {
                throw appwrite::DeserializationException("Required field 'userId' is null");
            } else {
                obj.userId = j["userId"].get<std::string>();
            }
        }
        if (j.contains("expire")) {
            if (j["expire"].is_null()) {
                throw appwrite::DeserializationException("Required field 'expire' is null");
            } else {
                obj.expire = j["expire"].get<std::string>();
            }
        }
        return obj;
    }

    /** @brief Serialize to JSON */
    [[nodiscard]] nlohmann::json toJson() const {
        nlohmann::json j = nlohmann::json::object();
        {
            j["$id"] = this->id;
        }
        {
            j["$createdAt"] = this->createdAt;
        }
        {
            j["userId"] = this->userId;
        }
        {
            j["expire"] = this->expire;
        }
        return j;
    }
};

/** @brief ADL hook for nlohmann::json */
inline void from_json(const nlohmann::json& j, MfaChallenge& x) {
    x = MfaChallenge::fromJson(j);
}

inline void to_json(nlohmann::json& j, const MfaChallenge& x) {
    j = x.toJson();
}

/**
 * @brief MFA Recovery Codes
 */
struct MfaRecoveryCodes {
    /** @brief Recovery codes. */
    std::vector<std::string> recoveryCodes;

    /** @brief Deserialize from JSON */
    static MfaRecoveryCodes fromJson(const nlohmann::json& j) {
        MfaRecoveryCodes obj{}; // value-init: zeroes bools, ints, enums
        if (j.contains("recoveryCodes")) {
            if (j["recoveryCodes"].is_null()) {
                throw appwrite::DeserializationException("Required field 'recoveryCodes' is null");
            } else {
                obj.recoveryCodes = j["recoveryCodes"].get<std::vector<std::string>>();
            }
        }
        return obj;
    }

    /** @brief Serialize to JSON */
    [[nodiscard]] nlohmann::json toJson() const {
        nlohmann::json j = nlohmann::json::object();
        {
            j["recoveryCodes"] = this->recoveryCodes;
        }
        return j;
    }
};

/** @brief ADL hook for nlohmann::json */
inline void from_json(const nlohmann::json& j, MfaRecoveryCodes& x) {
    x = MfaRecoveryCodes::fromJson(j);
}

inline void to_json(nlohmann::json& j, const MfaRecoveryCodes& x) {
    j = x.toJson();
}

/**
 * @brief MFAType
 */
struct MfaType {
    /** @brief Secret token used for TOTP factor. */
    std::string secret;
    /** @brief URI for authenticator apps. */
    std::string uri;

    /** @brief Deserialize from JSON */
    static MfaType fromJson(const nlohmann::json& j) {
        MfaType obj{}; // value-init: zeroes bools, ints, enums
        if (j.contains("secret")) {
            if (j["secret"].is_null()) {
                throw appwrite::DeserializationException("Required field 'secret' is null");
            } else {
                obj.secret = j["secret"].get<std::string>();
            }
        }
        if (j.contains("uri")) {
            if (j["uri"].is_null()) {
                throw appwrite::DeserializationException("Required field 'uri' is null");
            } else {
                obj.uri = j["uri"].get<std::string>();
            }
        }
        return obj;
    }

    /** @brief Serialize to JSON */
    [[nodiscard]] nlohmann::json toJson() const {
        nlohmann::json j = nlohmann::json::object();
        {
            j["secret"] = this->secret;
        }
        {
            j["uri"] = this->uri;
        }
        return j;
    }
};

/** @brief ADL hook for nlohmann::json */
inline void from_json(const nlohmann::json& j, MfaType& x) {
    x = MfaType::fromJson(j);
}

inline void to_json(nlohmann::json& j, const MfaType& x) {
    j = x.toJson();
}

/**
 * @brief MFAFactors
 */
struct MfaFactors {
    /** @brief Can TOTP be used for MFA challenge for this account. */
    bool totp;
    /** @brief Can phone (SMS) be used for MFA challenge for this account. */
    bool phone;
    /** @brief Can email be used for MFA challenge for this account. */
    bool email;
    /** @brief Can recovery code be used for MFA challenge for this account. */
    bool recoveryCode;

    /** @brief Deserialize from JSON */
    static MfaFactors fromJson(const nlohmann::json& j) {
        MfaFactors obj{}; // value-init: zeroes bools, ints, enums
        if (j.contains("totp")) {
            if (j["totp"].is_null()) {
                throw appwrite::DeserializationException("Required field 'totp' is null");
            } else {
                obj.totp = j["totp"].get<bool>();
            }
        }
        if (j.contains("phone")) {
            if (j["phone"].is_null()) {
                throw appwrite::DeserializationException("Required field 'phone' is null");
            } else {
                obj.phone = j["phone"].get<bool>();
            }
        }
        if (j.contains("email")) {
            if (j["email"].is_null()) {
                throw appwrite::DeserializationException("Required field 'email' is null");
            } else {
                obj.email = j["email"].get<bool>();
            }
        }
        if (j.contains("recoveryCode")) {
            if (j["recoveryCode"].is_null()) {
                throw appwrite::DeserializationException("Required field 'recoveryCode' is null");
            } else {
                obj.recoveryCode = j["recoveryCode"].get<bool>();
            }
        }
        return obj;
    }

    /** @brief Serialize to JSON */
    [[nodiscard]] nlohmann::json toJson() const {
        nlohmann::json j = nlohmann::json::object();
        {
            j["totp"] = this->totp;
        }
        {
            j["phone"] = this->phone;
        }
        {
            j["email"] = this->email;
        }
        {
            j["recoveryCode"] = this->recoveryCode;
        }
        return j;
    }
};

/** @brief ADL hook for nlohmann::json */
inline void from_json(const nlohmann::json& j, MfaFactors& x) {
    x = MfaFactors::fromJson(j);
}

inline void to_json(nlohmann::json& j, const MfaFactors& x) {
    j = x.toJson();
}

} // namespace models
} // namespace appwrite
