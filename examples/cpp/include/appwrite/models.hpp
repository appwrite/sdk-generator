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
struct TemplateRuntime;
struct TemplateVariable;
struct TemplateFunction;
struct TemplateFunctionList;
struct Runtime;
struct RuntimeList;
struct Deployment;
struct DeploymentList;
struct Headers;
struct Execution;
struct ExecutionList;
struct Webhook;
struct WebhookList;
struct Key;
struct KeyList;
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
struct Metric;
struct UsageDatabases;
struct UsageDatabase;
struct UsageTable;
struct UsageCollection;
struct UsageUsers;
struct UsageStorage;
struct UsageBuckets;
struct UsageFunctions;
struct UsageFunction;
struct MfaChallenge;
struct MfaRecoveryCodes;
struct MfaType;
struct MfaFactors;
struct BillingAddress;
struct Coupon;
struct UsageResources;
struct Invoice;
struct PaymentMethod;
struct InvoiceList;
struct BillingAddressList;
struct PaymentMethodList;

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
        if (j.contains("$id") && !j["$id"].is_null()) {
            obj.id = j["$id"].get<std::string>();
        }
        if (j.contains("$sequence") && !j["$sequence"].is_null()) {
            obj.sequence = j["$sequence"].get<std::string>();
        }
        if (j.contains("$tableId") && !j["$tableId"].is_null()) {
            obj.tableId = j["$tableId"].get<std::string>();
        }
        if (j.contains("$databaseId") && !j["$databaseId"].is_null()) {
            obj.databaseId = j["$databaseId"].get<std::string>();
        }
        if (j.contains("$createdAt") && !j["$createdAt"].is_null()) {
            obj.createdAt = j["$createdAt"].get<std::string>();
        }
        if (j.contains("$updatedAt") && !j["$updatedAt"].is_null()) {
            obj.updatedAt = j["$updatedAt"].get<std::string>();
        }
        if (j.contains("$permissions") && !j["$permissions"].is_null()) {
            obj.permissions = j["$permissions"].get<std::vector<std::string>>();
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
        if (j.contains("total") && !j["total"].is_null()) {
            obj.total = j["total"].get<int64_t>();
        }
        if (j.contains("rows") && !j["rows"].is_null()) {
                                        for (auto& item : j["rows"]) {
                                obj.rows.push_back(Row::fromJson(item));
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
        if (j.contains("$id") && !j["$id"].is_null()) {
            obj.id = j["$id"].get<std::string>();
        }
        if (j.contains("$sequence") && !j["$sequence"].is_null()) {
            obj.sequence = j["$sequence"].get<std::string>();
        }
        if (j.contains("$collectionId") && !j["$collectionId"].is_null()) {
            obj.collectionId = j["$collectionId"].get<std::string>();
        }
        if (j.contains("$databaseId") && !j["$databaseId"].is_null()) {
            obj.databaseId = j["$databaseId"].get<std::string>();
        }
        if (j.contains("$createdAt") && !j["$createdAt"].is_null()) {
            obj.createdAt = j["$createdAt"].get<std::string>();
        }
        if (j.contains("$updatedAt") && !j["$updatedAt"].is_null()) {
            obj.updatedAt = j["$updatedAt"].get<std::string>();
        }
        if (j.contains("$permissions") && !j["$permissions"].is_null()) {
            obj.permissions = j["$permissions"].get<std::vector<std::string>>();
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
        if (j.contains("total") && !j["total"].is_null()) {
            obj.total = j["total"].get<int64_t>();
        }
        if (j.contains("documents") && !j["documents"].is_null()) {
                                        for (auto& item : j["documents"]) {
                                obj.documents.push_back(Document::fromJson(item));
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
        if (j.contains("$id") && !j["$id"].is_null()) {
            obj.id = j["$id"].get<std::string>();
        }
        if (j.contains("$createdAt") && !j["$createdAt"].is_null()) {
            obj.createdAt = j["$createdAt"].get<std::string>();
        }
        if (j.contains("$updatedAt") && !j["$updatedAt"].is_null()) {
            obj.updatedAt = j["$updatedAt"].get<std::string>();
        }
        if (j.contains("key") && !j["key"].is_null()) {
            obj.key = j["key"].get<std::string>();
        }
        if (j.contains("type") && !j["type"].is_null()) {
            obj.type = j["type"].get<std::string>();
        }
        if (j.contains("status") && !j["status"].is_null()) {
            obj.status = j["status"].get<std::string>();
        }
        if (j.contains("error") && !j["error"].is_null()) {
            obj.error = j["error"].get<std::string>();
        }
        if (j.contains("columns") && !j["columns"].is_null()) {
            obj.columns = j["columns"].get<std::vector<std::string>>();
        }
        if (j.contains("lengths") && !j["lengths"].is_null()) {
            obj.lengths = j["lengths"].get<std::vector<int64_t>>();
        }
        if (j.contains("orders") && !j["orders"].is_null()) {
            obj.orders = j["orders"].get<std::optional<std::vector<std::string>>>();
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
        if (j.contains("$id") && !j["$id"].is_null()) {
            obj.id = j["$id"].get<std::string>();
        }
        if (j.contains("$createdAt") && !j["$createdAt"].is_null()) {
            obj.createdAt = j["$createdAt"].get<std::string>();
        }
        if (j.contains("$updatedAt") && !j["$updatedAt"].is_null()) {
            obj.updatedAt = j["$updatedAt"].get<std::string>();
        }
        if (j.contains("$permissions") && !j["$permissions"].is_null()) {
            obj.permissions = j["$permissions"].get<std::vector<std::string>>();
        }
        if (j.contains("databaseId") && !j["databaseId"].is_null()) {
            obj.databaseId = j["databaseId"].get<std::string>();
        }
        if (j.contains("name") && !j["name"].is_null()) {
            obj.name = j["name"].get<std::string>();
        }
        if (j.contains("enabled") && !j["enabled"].is_null()) {
            obj.enabled = j["enabled"].get<bool>();
        }
        if (j.contains("rowSecurity") && !j["rowSecurity"].is_null()) {
            obj.rowSecurity = j["rowSecurity"].get<bool>();
        }
        if (j.contains("columns") && !j["columns"].is_null()) {
            obj.columns = j["columns"].get<std::vector<std::string>>();
        }
        if (j.contains("indexes") && !j["indexes"].is_null()) {
                                        for (auto& item : j["indexes"]) {
                                obj.indexes.push_back(ColumnIndex::fromJson(item));
                            }
                    }
        if (j.contains("bytesMax") && !j["bytesMax"].is_null()) {
            obj.bytesMax = j["bytesMax"].get<int64_t>();
        }
        if (j.contains("bytesUsed") && !j["bytesUsed"].is_null()) {
            obj.bytesUsed = j["bytesUsed"].get<int64_t>();
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
        if (j.contains("total") && !j["total"].is_null()) {
            obj.total = j["total"].get<int64_t>();
        }
        if (j.contains("tables") && !j["tables"].is_null()) {
                                        for (auto& item : j["tables"]) {
                                obj.tables.push_back(Table::fromJson(item));
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
        if (j.contains("$id") && !j["$id"].is_null()) {
            obj.id = j["$id"].get<std::string>();
        }
        if (j.contains("$createdAt") && !j["$createdAt"].is_null()) {
            obj.createdAt = j["$createdAt"].get<std::string>();
        }
        if (j.contains("$updatedAt") && !j["$updatedAt"].is_null()) {
            obj.updatedAt = j["$updatedAt"].get<std::string>();
        }
        if (j.contains("key") && !j["key"].is_null()) {
            obj.key = j["key"].get<std::string>();
        }
        if (j.contains("type") && !j["type"].is_null()) {
            obj.type = j["type"].get<std::string>();
        }
        if (j.contains("status") && !j["status"].is_null()) {
            obj.status = j["status"].get<appwrite::enums::IndexStatus>();
        }
        if (j.contains("error") && !j["error"].is_null()) {
            obj.error = j["error"].get<std::string>();
        }
        if (j.contains("attributes") && !j["attributes"].is_null()) {
            obj.attributes = j["attributes"].get<std::vector<std::string>>();
        }
        if (j.contains("lengths") && !j["lengths"].is_null()) {
            obj.lengths = j["lengths"].get<std::vector<int64_t>>();
        }
        if (j.contains("orders") && !j["orders"].is_null()) {
            obj.orders = j["orders"].get<std::optional<std::vector<std::string>>>();
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
        if (j.contains("$id") && !j["$id"].is_null()) {
            obj.id = j["$id"].get<std::string>();
        }
        if (j.contains("$createdAt") && !j["$createdAt"].is_null()) {
            obj.createdAt = j["$createdAt"].get<std::string>();
        }
        if (j.contains("$updatedAt") && !j["$updatedAt"].is_null()) {
            obj.updatedAt = j["$updatedAt"].get<std::string>();
        }
        if (j.contains("$permissions") && !j["$permissions"].is_null()) {
            obj.permissions = j["$permissions"].get<std::vector<std::string>>();
        }
        if (j.contains("databaseId") && !j["databaseId"].is_null()) {
            obj.databaseId = j["databaseId"].get<std::string>();
        }
        if (j.contains("name") && !j["name"].is_null()) {
            obj.name = j["name"].get<std::string>();
        }
        if (j.contains("enabled") && !j["enabled"].is_null()) {
            obj.enabled = j["enabled"].get<bool>();
        }
        if (j.contains("documentSecurity") && !j["documentSecurity"].is_null()) {
            obj.documentSecurity = j["documentSecurity"].get<bool>();
        }
        if (j.contains("attributes") && !j["attributes"].is_null()) {
            obj.attributes = j["attributes"].get<std::vector<std::string>>();
        }
        if (j.contains("indexes") && !j["indexes"].is_null()) {
                                        for (auto& item : j["indexes"]) {
                                obj.indexes.push_back(Index::fromJson(item));
                            }
                    }
        if (j.contains("bytesMax") && !j["bytesMax"].is_null()) {
            obj.bytesMax = j["bytesMax"].get<int64_t>();
        }
        if (j.contains("bytesUsed") && !j["bytesUsed"].is_null()) {
            obj.bytesUsed = j["bytesUsed"].get<int64_t>();
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
        if (j.contains("total") && !j["total"].is_null()) {
            obj.total = j["total"].get<int64_t>();
        }
        if (j.contains("collections") && !j["collections"].is_null()) {
                                        for (auto& item : j["collections"]) {
                                obj.collections.push_back(Collection::fromJson(item));
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
        if (j.contains("$id") && !j["$id"].is_null()) {
            obj.id = j["$id"].get<std::string>();
        }
        if (j.contains("name") && !j["name"].is_null()) {
            obj.name = j["name"].get<std::string>();
        }
        if (j.contains("$createdAt") && !j["$createdAt"].is_null()) {
            obj.createdAt = j["$createdAt"].get<std::string>();
        }
        if (j.contains("$updatedAt") && !j["$updatedAt"].is_null()) {
            obj.updatedAt = j["$updatedAt"].get<std::string>();
        }
        if (j.contains("enabled") && !j["enabled"].is_null()) {
            obj.enabled = j["enabled"].get<bool>();
        }
        if (j.contains("type") && !j["type"].is_null()) {
            obj.type = j["type"].get<appwrite::enums::DatabaseType>();
        }
        if (j.contains("policies") && !j["policies"].is_null()) {
                                        for (auto& item : j["policies"]) {
                                obj.policies.push_back(Index::fromJson(item));
                            }
                    }
        if (j.contains("archives") && !j["archives"].is_null()) {
                                        for (auto& item : j["archives"]) {
                                obj.archives.push_back(Collection::fromJson(item));
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
        if (j.contains("total") && !j["total"].is_null()) {
            obj.total = j["total"].get<int64_t>();
        }
        if (j.contains("databases") && !j["databases"].is_null()) {
                                        for (auto& item : j["databases"]) {
                                obj.databases.push_back(Database::fromJson(item));
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
        if (j.contains("total") && !j["total"].is_null()) {
            obj.total = j["total"].get<int64_t>();
        }
        if (j.contains("indexes") && !j["indexes"].is_null()) {
                                        for (auto& item : j["indexes"]) {
                                obj.indexes.push_back(Index::fromJson(item));
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
        if (j.contains("total") && !j["total"].is_null()) {
            obj.total = j["total"].get<int64_t>();
        }
        if (j.contains("indexes") && !j["indexes"].is_null()) {
                                        for (auto& item : j["indexes"]) {
                                obj.indexes.push_back(ColumnIndex::fromJson(item));
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
        if (j.contains("$id") && !j["$id"].is_null()) {
            obj.id = j["$id"].get<std::string>();
        }
        if (j.contains("$createdAt") && !j["$createdAt"].is_null()) {
            obj.createdAt = j["$createdAt"].get<std::string>();
        }
        if (j.contains("$updatedAt") && !j["$updatedAt"].is_null()) {
            obj.updatedAt = j["$updatedAt"].get<std::string>();
        }
        if (j.contains("name") && !j["name"].is_null()) {
            obj.name = j["name"].get<std::string>();
        }
        if (j.contains("userId") && !j["userId"].is_null()) {
            obj.userId = j["userId"].get<std::string>();
        }
        if (j.contains("providerId") && !j["providerId"].is_null()) {
            obj.providerId = j["providerId"].get<std::optional<std::string>>();
        }
        if (j.contains("providerType") && !j["providerType"].is_null()) {
            obj.providerType = j["providerType"].get<std::string>();
        }
        if (j.contains("identifier") && !j["identifier"].is_null()) {
            obj.identifier = j["identifier"].get<std::string>();
        }
        if (j.contains("expired") && !j["expired"].is_null()) {
            obj.expired = j["expired"].get<bool>();
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
        if (j.contains("$id") && !j["$id"].is_null()) {
            obj.id = j["$id"].get<std::string>();
        }
        if (j.contains("$createdAt") && !j["$createdAt"].is_null()) {
            obj.createdAt = j["$createdAt"].get<std::string>();
        }
        if (j.contains("$updatedAt") && !j["$updatedAt"].is_null()) {
            obj.updatedAt = j["$updatedAt"].get<std::string>();
        }
        if (j.contains("name") && !j["name"].is_null()) {
            obj.name = j["name"].get<std::string>();
        }
        if (j.contains("password") && !j["password"].is_null()) {
            obj.password = j["password"].get<std::optional<std::string>>();
        }
        if (j.contains("hash") && !j["hash"].is_null()) {
            obj.hash = j["hash"].get<std::optional<std::string>>();
        }
        if (j.contains("hashOptions") && !j["hashOptions"].is_null()) {
            obj.hashOptions = j["hashOptions"].get<std::optional<nlohmann::json>>();
        }
        if (j.contains("registration") && !j["registration"].is_null()) {
            obj.registration = j["registration"].get<std::string>();
        }
        if (j.contains("status") && !j["status"].is_null()) {
            obj.status = j["status"].get<bool>();
        }
        if (j.contains("labels") && !j["labels"].is_null()) {
            obj.labels = j["labels"].get<std::vector<std::string>>();
        }
        if (j.contains("passwordUpdate") && !j["passwordUpdate"].is_null()) {
            obj.passwordUpdate = j["passwordUpdate"].get<std::string>();
        }
        if (j.contains("email") && !j["email"].is_null()) {
            obj.email = j["email"].get<std::string>();
        }
        if (j.contains("phone") && !j["phone"].is_null()) {
            obj.phone = j["phone"].get<std::string>();
        }
        if (j.contains("emailVerification") && !j["emailVerification"].is_null()) {
            obj.emailVerification = j["emailVerification"].get<bool>();
        }
        if (j.contains("phoneVerification") && !j["phoneVerification"].is_null()) {
            obj.phoneVerification = j["phoneVerification"].get<bool>();
        }
        if (j.contains("mfa") && !j["mfa"].is_null()) {
            obj.mfa = j["mfa"].get<bool>();
        }
        if (j.contains("prefs") && !j["prefs"].is_null()) {
                        obj.prefs = Preferences::fromJson(j["prefs"]);
                    }
        if (j.contains("targets") && !j["targets"].is_null()) {
                                        for (auto& item : j["targets"]) {
                                obj.targets.push_back(Target::fromJson(item));
                            }
                    }
        if (j.contains("accessedAt") && !j["accessedAt"].is_null()) {
            obj.accessedAt = j["accessedAt"].get<std::string>();
        }
        if (j.contains("impersonator") && !j["impersonator"].is_null()) {
            obj.impersonator = j["impersonator"].get<std::optional<bool>>();
        }
        if (j.contains("impersonatorUserId") && !j["impersonatorUserId"].is_null()) {
            obj.impersonatorUserId = j["impersonatorUserId"].get<std::optional<std::string>>();
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
        if (j.contains("total") && !j["total"].is_null()) {
            obj.total = j["total"].get<int64_t>();
        }
        if (j.contains("users") && !j["users"].is_null()) {
                                        for (auto& item : j["users"]) {
                                obj.users.push_back(User::fromJson(item));
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
        if (j.contains("$id") && !j["$id"].is_null()) {
            obj.id = j["$id"].get<std::string>();
        }
        if (j.contains("$createdAt") && !j["$createdAt"].is_null()) {
            obj.createdAt = j["$createdAt"].get<std::string>();
        }
        if (j.contains("$updatedAt") && !j["$updatedAt"].is_null()) {
            obj.updatedAt = j["$updatedAt"].get<std::string>();
        }
        if (j.contains("userId") && !j["userId"].is_null()) {
            obj.userId = j["userId"].get<std::string>();
        }
        if (j.contains("expire") && !j["expire"].is_null()) {
            obj.expire = j["expire"].get<std::string>();
        }
        if (j.contains("provider") && !j["provider"].is_null()) {
            obj.provider = j["provider"].get<std::string>();
        }
        if (j.contains("providerUid") && !j["providerUid"].is_null()) {
            obj.providerUid = j["providerUid"].get<std::string>();
        }
        if (j.contains("providerAccessToken") && !j["providerAccessToken"].is_null()) {
            obj.providerAccessToken = j["providerAccessToken"].get<std::string>();
        }
        if (j.contains("providerAccessTokenExpiry") && !j["providerAccessTokenExpiry"].is_null()) {
            obj.providerAccessTokenExpiry = j["providerAccessTokenExpiry"].get<std::string>();
        }
        if (j.contains("providerRefreshToken") && !j["providerRefreshToken"].is_null()) {
            obj.providerRefreshToken = j["providerRefreshToken"].get<std::string>();
        }
        if (j.contains("ip") && !j["ip"].is_null()) {
            obj.ip = j["ip"].get<std::string>();
        }
        if (j.contains("osCode") && !j["osCode"].is_null()) {
            obj.osCode = j["osCode"].get<std::string>();
        }
        if (j.contains("osName") && !j["osName"].is_null()) {
            obj.osName = j["osName"].get<std::string>();
        }
        if (j.contains("osVersion") && !j["osVersion"].is_null()) {
            obj.osVersion = j["osVersion"].get<std::string>();
        }
        if (j.contains("clientType") && !j["clientType"].is_null()) {
            obj.clientType = j["clientType"].get<std::string>();
        }
        if (j.contains("clientCode") && !j["clientCode"].is_null()) {
            obj.clientCode = j["clientCode"].get<std::string>();
        }
        if (j.contains("clientName") && !j["clientName"].is_null()) {
            obj.clientName = j["clientName"].get<std::string>();
        }
        if (j.contains("clientVersion") && !j["clientVersion"].is_null()) {
            obj.clientVersion = j["clientVersion"].get<std::string>();
        }
        if (j.contains("clientEngine") && !j["clientEngine"].is_null()) {
            obj.clientEngine = j["clientEngine"].get<std::string>();
        }
        if (j.contains("clientEngineVersion") && !j["clientEngineVersion"].is_null()) {
            obj.clientEngineVersion = j["clientEngineVersion"].get<std::string>();
        }
        if (j.contains("deviceName") && !j["deviceName"].is_null()) {
            obj.deviceName = j["deviceName"].get<std::string>();
        }
        if (j.contains("deviceBrand") && !j["deviceBrand"].is_null()) {
            obj.deviceBrand = j["deviceBrand"].get<std::string>();
        }
        if (j.contains("deviceModel") && !j["deviceModel"].is_null()) {
            obj.deviceModel = j["deviceModel"].get<std::string>();
        }
        if (j.contains("countryCode") && !j["countryCode"].is_null()) {
            obj.countryCode = j["countryCode"].get<std::string>();
        }
        if (j.contains("countryName") && !j["countryName"].is_null()) {
            obj.countryName = j["countryName"].get<std::string>();
        }
        if (j.contains("current") && !j["current"].is_null()) {
            obj.current = j["current"].get<bool>();
        }
        if (j.contains("factors") && !j["factors"].is_null()) {
            obj.factors = j["factors"].get<std::vector<std::string>>();
        }
        if (j.contains("secret") && !j["secret"].is_null()) {
            obj.secret = j["secret"].get<std::string>();
        }
        if (j.contains("mfaUpdatedAt") && !j["mfaUpdatedAt"].is_null()) {
            obj.mfaUpdatedAt = j["mfaUpdatedAt"].get<std::string>();
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
        if (j.contains("total") && !j["total"].is_null()) {
            obj.total = j["total"].get<int64_t>();
        }
        if (j.contains("sessions") && !j["sessions"].is_null()) {
                                        for (auto& item : j["sessions"]) {
                                obj.sessions.push_back(Session::fromJson(item));
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
        if (j.contains("$id") && !j["$id"].is_null()) {
            obj.id = j["$id"].get<std::string>();
        }
        if (j.contains("$createdAt") && !j["$createdAt"].is_null()) {
            obj.createdAt = j["$createdAt"].get<std::string>();
        }
        if (j.contains("$updatedAt") && !j["$updatedAt"].is_null()) {
            obj.updatedAt = j["$updatedAt"].get<std::string>();
        }
        if (j.contains("userId") && !j["userId"].is_null()) {
            obj.userId = j["userId"].get<std::string>();
        }
        if (j.contains("provider") && !j["provider"].is_null()) {
            obj.provider = j["provider"].get<std::string>();
        }
        if (j.contains("providerUid") && !j["providerUid"].is_null()) {
            obj.providerUid = j["providerUid"].get<std::string>();
        }
        if (j.contains("providerEmail") && !j["providerEmail"].is_null()) {
            obj.providerEmail = j["providerEmail"].get<std::string>();
        }
        if (j.contains("providerAccessToken") && !j["providerAccessToken"].is_null()) {
            obj.providerAccessToken = j["providerAccessToken"].get<std::string>();
        }
        if (j.contains("providerAccessTokenExpiry") && !j["providerAccessTokenExpiry"].is_null()) {
            obj.providerAccessTokenExpiry = j["providerAccessTokenExpiry"].get<std::string>();
        }
        if (j.contains("providerRefreshToken") && !j["providerRefreshToken"].is_null()) {
            obj.providerRefreshToken = j["providerRefreshToken"].get<std::string>();
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
        if (j.contains("total") && !j["total"].is_null()) {
            obj.total = j["total"].get<int64_t>();
        }
        if (j.contains("identities") && !j["identities"].is_null()) {
                                        for (auto& item : j["identities"]) {
                                obj.identities.push_back(Identity::fromJson(item));
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
        if (j.contains("event") && !j["event"].is_null()) {
            obj.event = j["event"].get<std::string>();
        }
        if (j.contains("userId") && !j["userId"].is_null()) {
            obj.userId = j["userId"].get<std::string>();
        }
        if (j.contains("userEmail") && !j["userEmail"].is_null()) {
            obj.userEmail = j["userEmail"].get<std::string>();
        }
        if (j.contains("userName") && !j["userName"].is_null()) {
            obj.userName = j["userName"].get<std::string>();
        }
        if (j.contains("mode") && !j["mode"].is_null()) {
            obj.mode = j["mode"].get<std::string>();
        }
        if (j.contains("userType") && !j["userType"].is_null()) {
            obj.userType = j["userType"].get<std::string>();
        }
        if (j.contains("ip") && !j["ip"].is_null()) {
            obj.ip = j["ip"].get<std::string>();
        }
        if (j.contains("time") && !j["time"].is_null()) {
            obj.time = j["time"].get<std::string>();
        }
        if (j.contains("osCode") && !j["osCode"].is_null()) {
            obj.osCode = j["osCode"].get<std::string>();
        }
        if (j.contains("osName") && !j["osName"].is_null()) {
            obj.osName = j["osName"].get<std::string>();
        }
        if (j.contains("osVersion") && !j["osVersion"].is_null()) {
            obj.osVersion = j["osVersion"].get<std::string>();
        }
        if (j.contains("clientType") && !j["clientType"].is_null()) {
            obj.clientType = j["clientType"].get<std::string>();
        }
        if (j.contains("clientCode") && !j["clientCode"].is_null()) {
            obj.clientCode = j["clientCode"].get<std::string>();
        }
        if (j.contains("clientName") && !j["clientName"].is_null()) {
            obj.clientName = j["clientName"].get<std::string>();
        }
        if (j.contains("clientVersion") && !j["clientVersion"].is_null()) {
            obj.clientVersion = j["clientVersion"].get<std::string>();
        }
        if (j.contains("clientEngine") && !j["clientEngine"].is_null()) {
            obj.clientEngine = j["clientEngine"].get<std::string>();
        }
        if (j.contains("clientEngineVersion") && !j["clientEngineVersion"].is_null()) {
            obj.clientEngineVersion = j["clientEngineVersion"].get<std::string>();
        }
        if (j.contains("deviceName") && !j["deviceName"].is_null()) {
            obj.deviceName = j["deviceName"].get<std::string>();
        }
        if (j.contains("deviceBrand") && !j["deviceBrand"].is_null()) {
            obj.deviceBrand = j["deviceBrand"].get<std::string>();
        }
        if (j.contains("deviceModel") && !j["deviceModel"].is_null()) {
            obj.deviceModel = j["deviceModel"].get<std::string>();
        }
        if (j.contains("countryCode") && !j["countryCode"].is_null()) {
            obj.countryCode = j["countryCode"].get<std::string>();
        }
        if (j.contains("countryName") && !j["countryName"].is_null()) {
            obj.countryName = j["countryName"].get<std::string>();
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
        if (j.contains("total") && !j["total"].is_null()) {
            obj.total = j["total"].get<int64_t>();
        }
        if (j.contains("logs") && !j["logs"].is_null()) {
                                        for (auto& item : j["logs"]) {
                                obj.logs.push_back(Log::fromJson(item));
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
        if (j.contains("$id") && !j["$id"].is_null()) {
            obj.id = j["$id"].get<std::string>();
        }
        if (j.contains("bucketId") && !j["bucketId"].is_null()) {
            obj.bucketId = j["bucketId"].get<std::string>();
        }
        if (j.contains("$createdAt") && !j["$createdAt"].is_null()) {
            obj.createdAt = j["$createdAt"].get<std::string>();
        }
        if (j.contains("$updatedAt") && !j["$updatedAt"].is_null()) {
            obj.updatedAt = j["$updatedAt"].get<std::string>();
        }
        if (j.contains("$permissions") && !j["$permissions"].is_null()) {
            obj.permissions = j["$permissions"].get<std::vector<std::string>>();
        }
        if (j.contains("name") && !j["name"].is_null()) {
            obj.name = j["name"].get<std::string>();
        }
        if (j.contains("signature") && !j["signature"].is_null()) {
            obj.signature = j["signature"].get<std::string>();
        }
        if (j.contains("mimeType") && !j["mimeType"].is_null()) {
            obj.mimeType = j["mimeType"].get<std::string>();
        }
        if (j.contains("sizeOriginal") && !j["sizeOriginal"].is_null()) {
            obj.sizeOriginal = j["sizeOriginal"].get<int64_t>();
        }
        if (j.contains("chunksTotal") && !j["chunksTotal"].is_null()) {
            obj.chunksTotal = j["chunksTotal"].get<int64_t>();
        }
        if (j.contains("chunksUploaded") && !j["chunksUploaded"].is_null()) {
            obj.chunksUploaded = j["chunksUploaded"].get<int64_t>();
        }
        if (j.contains("encryption") && !j["encryption"].is_null()) {
            obj.encryption = j["encryption"].get<bool>();
        }
        if (j.contains("compression") && !j["compression"].is_null()) {
            obj.compression = j["compression"].get<std::string>();
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
        if (j.contains("total") && !j["total"].is_null()) {
            obj.total = j["total"].get<int64_t>();
        }
        if (j.contains("files") && !j["files"].is_null()) {
                                        for (auto& item : j["files"]) {
                                obj.files.push_back(File::fromJson(item));
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
        if (j.contains("$id") && !j["$id"].is_null()) {
            obj.id = j["$id"].get<std::string>();
        }
        if (j.contains("$createdAt") && !j["$createdAt"].is_null()) {
            obj.createdAt = j["$createdAt"].get<std::string>();
        }
        if (j.contains("$updatedAt") && !j["$updatedAt"].is_null()) {
            obj.updatedAt = j["$updatedAt"].get<std::string>();
        }
        if (j.contains("$permissions") && !j["$permissions"].is_null()) {
            obj.permissions = j["$permissions"].get<std::vector<std::string>>();
        }
        if (j.contains("fileSecurity") && !j["fileSecurity"].is_null()) {
            obj.fileSecurity = j["fileSecurity"].get<bool>();
        }
        if (j.contains("name") && !j["name"].is_null()) {
            obj.name = j["name"].get<std::string>();
        }
        if (j.contains("enabled") && !j["enabled"].is_null()) {
            obj.enabled = j["enabled"].get<bool>();
        }
        if (j.contains("maximumFileSize") && !j["maximumFileSize"].is_null()) {
            obj.maximumFileSize = j["maximumFileSize"].get<int64_t>();
        }
        if (j.contains("allowedFileExtensions") && !j["allowedFileExtensions"].is_null()) {
            obj.allowedFileExtensions = j["allowedFileExtensions"].get<std::vector<std::string>>();
        }
        if (j.contains("compression") && !j["compression"].is_null()) {
            obj.compression = j["compression"].get<std::string>();
        }
        if (j.contains("encryption") && !j["encryption"].is_null()) {
            obj.encryption = j["encryption"].get<bool>();
        }
        if (j.contains("antivirus") && !j["antivirus"].is_null()) {
            obj.antivirus = j["antivirus"].get<bool>();
        }
        if (j.contains("transformations") && !j["transformations"].is_null()) {
            obj.transformations = j["transformations"].get<bool>();
        }
        if (j.contains("totalSize") && !j["totalSize"].is_null()) {
            obj.totalSize = j["totalSize"].get<int64_t>();
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
        if (j.contains("total") && !j["total"].is_null()) {
            obj.total = j["total"].get<int64_t>();
        }
        if (j.contains("buckets") && !j["buckets"].is_null()) {
                                        for (auto& item : j["buckets"]) {
                                obj.buckets.push_back(Bucket::fromJson(item));
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
        if (j.contains("$id") && !j["$id"].is_null()) {
            obj.id = j["$id"].get<std::string>();
        }
        if (j.contains("$createdAt") && !j["$createdAt"].is_null()) {
            obj.createdAt = j["$createdAt"].get<std::string>();
        }
        if (j.contains("$updatedAt") && !j["$updatedAt"].is_null()) {
            obj.updatedAt = j["$updatedAt"].get<std::string>();
        }
        if (j.contains("name") && !j["name"].is_null()) {
            obj.name = j["name"].get<std::string>();
        }
        if (j.contains("total") && !j["total"].is_null()) {
            obj.total = j["total"].get<int64_t>();
        }
        if (j.contains("prefs") && !j["prefs"].is_null()) {
                        obj.prefs = Preferences::fromJson(j["prefs"]);
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
        if (j.contains("total") && !j["total"].is_null()) {
            obj.total = j["total"].get<int64_t>();
        }
        if (j.contains("teams") && !j["teams"].is_null()) {
                                        for (auto& item : j["teams"]) {
                                obj.teams.push_back(Team::fromJson(item));
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
        if (j.contains("$id") && !j["$id"].is_null()) {
            obj.id = j["$id"].get<std::string>();
        }
        if (j.contains("$createdAt") && !j["$createdAt"].is_null()) {
            obj.createdAt = j["$createdAt"].get<std::string>();
        }
        if (j.contains("$updatedAt") && !j["$updatedAt"].is_null()) {
            obj.updatedAt = j["$updatedAt"].get<std::string>();
        }
        if (j.contains("userId") && !j["userId"].is_null()) {
            obj.userId = j["userId"].get<std::string>();
        }
        if (j.contains("userName") && !j["userName"].is_null()) {
            obj.userName = j["userName"].get<std::string>();
        }
        if (j.contains("userEmail") && !j["userEmail"].is_null()) {
            obj.userEmail = j["userEmail"].get<std::string>();
        }
        if (j.contains("teamId") && !j["teamId"].is_null()) {
            obj.teamId = j["teamId"].get<std::string>();
        }
        if (j.contains("teamName") && !j["teamName"].is_null()) {
            obj.teamName = j["teamName"].get<std::string>();
        }
        if (j.contains("invited") && !j["invited"].is_null()) {
            obj.invited = j["invited"].get<std::string>();
        }
        if (j.contains("joined") && !j["joined"].is_null()) {
            obj.joined = j["joined"].get<std::string>();
        }
        if (j.contains("confirm") && !j["confirm"].is_null()) {
            obj.confirm = j["confirm"].get<bool>();
        }
        if (j.contains("mfa") && !j["mfa"].is_null()) {
            obj.mfa = j["mfa"].get<bool>();
        }
        if (j.contains("roles") && !j["roles"].is_null()) {
            obj.roles = j["roles"].get<std::vector<std::string>>();
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
        if (j.contains("total") && !j["total"].is_null()) {
            obj.total = j["total"].get<int64_t>();
        }
        if (j.contains("memberships") && !j["memberships"].is_null()) {
                                        for (auto& item : j["memberships"]) {
                                obj.memberships.push_back(Membership::fromJson(item));
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
        if (j.contains("$id") && !j["$id"].is_null()) {
            obj.id = j["$id"].get<std::string>();
        }
        if (j.contains("$createdAt") && !j["$createdAt"].is_null()) {
            obj.createdAt = j["$createdAt"].get<std::string>();
        }
        if (j.contains("$updatedAt") && !j["$updatedAt"].is_null()) {
            obj.updatedAt = j["$updatedAt"].get<std::string>();
        }
        if (j.contains("key") && !j["key"].is_null()) {
            obj.key = j["key"].get<std::string>();
        }
        if (j.contains("value") && !j["value"].is_null()) {
            obj.value = j["value"].get<std::string>();
        }
        if (j.contains("secret") && !j["secret"].is_null()) {
            obj.secret = j["secret"].get<bool>();
        }
        if (j.contains("resourceType") && !j["resourceType"].is_null()) {
            obj.resourceType = j["resourceType"].get<std::string>();
        }
        if (j.contains("resourceId") && !j["resourceId"].is_null()) {
            obj.resourceId = j["resourceId"].get<std::string>();
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
        if (j.contains("$id") && !j["$id"].is_null()) {
            obj.id = j["$id"].get<std::string>();
        }
        if (j.contains("$createdAt") && !j["$createdAt"].is_null()) {
            obj.createdAt = j["$createdAt"].get<std::string>();
        }
        if (j.contains("$updatedAt") && !j["$updatedAt"].is_null()) {
            obj.updatedAt = j["$updatedAt"].get<std::string>();
        }
        if (j.contains("execute") && !j["execute"].is_null()) {
            obj.execute = j["execute"].get<std::vector<std::string>>();
        }
        if (j.contains("name") && !j["name"].is_null()) {
            obj.name = j["name"].get<std::string>();
        }
        if (j.contains("enabled") && !j["enabled"].is_null()) {
            obj.enabled = j["enabled"].get<bool>();
        }
        if (j.contains("live") && !j["live"].is_null()) {
            obj.live = j["live"].get<bool>();
        }
        if (j.contains("logging") && !j["logging"].is_null()) {
            obj.logging = j["logging"].get<bool>();
        }
        if (j.contains("runtime") && !j["runtime"].is_null()) {
            obj.runtime = j["runtime"].get<std::string>();
        }
        if (j.contains("deploymentRetention") && !j["deploymentRetention"].is_null()) {
            obj.deploymentRetention = j["deploymentRetention"].get<int64_t>();
        }
        if (j.contains("deploymentId") && !j["deploymentId"].is_null()) {
            obj.deploymentId = j["deploymentId"].get<std::string>();
        }
        if (j.contains("deploymentCreatedAt") && !j["deploymentCreatedAt"].is_null()) {
            obj.deploymentCreatedAt = j["deploymentCreatedAt"].get<std::string>();
        }
        if (j.contains("latestDeploymentId") && !j["latestDeploymentId"].is_null()) {
            obj.latestDeploymentId = j["latestDeploymentId"].get<std::string>();
        }
        if (j.contains("latestDeploymentCreatedAt") && !j["latestDeploymentCreatedAt"].is_null()) {
            obj.latestDeploymentCreatedAt = j["latestDeploymentCreatedAt"].get<std::string>();
        }
        if (j.contains("latestDeploymentStatus") && !j["latestDeploymentStatus"].is_null()) {
            obj.latestDeploymentStatus = j["latestDeploymentStatus"].get<std::string>();
        }
        if (j.contains("scopes") && !j["scopes"].is_null()) {
            obj.scopes = j["scopes"].get<std::vector<std::string>>();
        }
        if (j.contains("vars") && !j["vars"].is_null()) {
                                        for (auto& item : j["vars"]) {
                                obj.vars.push_back(Variable::fromJson(item));
                            }
                    }
        if (j.contains("events") && !j["events"].is_null()) {
            obj.events = j["events"].get<std::vector<std::string>>();
        }
        if (j.contains("schedule") && !j["schedule"].is_null()) {
            obj.schedule = j["schedule"].get<std::string>();
        }
        if (j.contains("timeout") && !j["timeout"].is_null()) {
            obj.timeout = j["timeout"].get<int64_t>();
        }
        if (j.contains("entrypoint") && !j["entrypoint"].is_null()) {
            obj.entrypoint = j["entrypoint"].get<std::string>();
        }
        if (j.contains("commands") && !j["commands"].is_null()) {
            obj.commands = j["commands"].get<std::string>();
        }
        if (j.contains("version") && !j["version"].is_null()) {
            obj.version = j["version"].get<std::string>();
        }
        if (j.contains("installationId") && !j["installationId"].is_null()) {
            obj.installationId = j["installationId"].get<std::string>();
        }
        if (j.contains("providerRepositoryId") && !j["providerRepositoryId"].is_null()) {
            obj.providerRepositoryId = j["providerRepositoryId"].get<std::string>();
        }
        if (j.contains("providerBranch") && !j["providerBranch"].is_null()) {
            obj.providerBranch = j["providerBranch"].get<std::string>();
        }
        if (j.contains("providerRootDirectory") && !j["providerRootDirectory"].is_null()) {
            obj.providerRootDirectory = j["providerRootDirectory"].get<std::string>();
        }
        if (j.contains("providerSilentMode") && !j["providerSilentMode"].is_null()) {
            obj.providerSilentMode = j["providerSilentMode"].get<bool>();
        }
        if (j.contains("buildSpecification") && !j["buildSpecification"].is_null()) {
            obj.buildSpecification = j["buildSpecification"].get<std::string>();
        }
        if (j.contains("runtimeSpecification") && !j["runtimeSpecification"].is_null()) {
            obj.runtimeSpecification = j["runtimeSpecification"].get<std::string>();
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
        if (j.contains("total") && !j["total"].is_null()) {
            obj.total = j["total"].get<int64_t>();
        }
        if (j.contains("functions") && !j["functions"].is_null()) {
                                        for (auto& item : j["functions"]) {
                                obj.functions.push_back(Function::fromJson(item));
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
 * @brief Template Runtime
 */
struct TemplateRuntime {
    /** @brief Runtime Name. */
    std::string name;
    /** @brief The build command used to build the deployment. */
    std::string commands;
    /** @brief The entrypoint file used to execute the deployment. */
    std::string entrypoint;
    /** @brief Path to function in VCS (Version Control System) repository */
    std::string providerRootDirectory;

    /** @brief Deserialize from JSON */
    static TemplateRuntime fromJson(const nlohmann::json& j) {
        TemplateRuntime obj{}; // value-init: zeroes bools, ints, enums
        if (j.contains("name") && !j["name"].is_null()) {
            obj.name = j["name"].get<std::string>();
        }
        if (j.contains("commands") && !j["commands"].is_null()) {
            obj.commands = j["commands"].get<std::string>();
        }
        if (j.contains("entrypoint") && !j["entrypoint"].is_null()) {
            obj.entrypoint = j["entrypoint"].get<std::string>();
        }
        if (j.contains("providerRootDirectory") && !j["providerRootDirectory"].is_null()) {
            obj.providerRootDirectory = j["providerRootDirectory"].get<std::string>();
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
            j["commands"] = this->commands;
        }
        {
            j["entrypoint"] = this->entrypoint;
        }
        {
            j["providerRootDirectory"] = this->providerRootDirectory;
        }
        return j;
    }
};

/** @brief ADL hook for nlohmann::json */
inline void from_json(const nlohmann::json& j, TemplateRuntime& x) {
    x = TemplateRuntime::fromJson(j);
}

inline void to_json(nlohmann::json& j, const TemplateRuntime& x) {
    j = x.toJson();
}

/**
 * @brief Template Variable
 */
struct TemplateVariable {
    /** @brief Variable Name. */
    std::string name;
    /** @brief Variable Description. */
    std::string description;
    /** @brief Variable Value. */
    std::string value;
    /** @brief Variable secret flag. Secret variables can only be updated or deleted, but never read. */
    bool secret;
    /** @brief Variable Placeholder. */
    std::string placeholder;
    /** @brief Is the variable required? */
    bool required;
    /** @brief Variable Type. */
    std::string type;

    /** @brief Deserialize from JSON */
    static TemplateVariable fromJson(const nlohmann::json& j) {
        TemplateVariable obj{}; // value-init: zeroes bools, ints, enums
        if (j.contains("name") && !j["name"].is_null()) {
            obj.name = j["name"].get<std::string>();
        }
        if (j.contains("description") && !j["description"].is_null()) {
            obj.description = j["description"].get<std::string>();
        }
        if (j.contains("value") && !j["value"].is_null()) {
            obj.value = j["value"].get<std::string>();
        }
        if (j.contains("secret") && !j["secret"].is_null()) {
            obj.secret = j["secret"].get<bool>();
        }
        if (j.contains("placeholder") && !j["placeholder"].is_null()) {
            obj.placeholder = j["placeholder"].get<std::string>();
        }
        if (j.contains("required") && !j["required"].is_null()) {
            obj.required = j["required"].get<bool>();
        }
        if (j.contains("type") && !j["type"].is_null()) {
            obj.type = j["type"].get<std::string>();
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
            j["description"] = this->description;
        }
        {
            j["value"] = this->value;
        }
        {
            j["secret"] = this->secret;
        }
        {
            j["placeholder"] = this->placeholder;
        }
        {
            j["required"] = this->required;
        }
        {
            j["type"] = this->type;
        }
        return j;
    }
};

/** @brief ADL hook for nlohmann::json */
inline void from_json(const nlohmann::json& j, TemplateVariable& x) {
    x = TemplateVariable::fromJson(j);
}

inline void to_json(nlohmann::json& j, const TemplateVariable& x) {
    j = x.toJson();
}

/**
 * @brief Template Function
 */
struct TemplateFunction {
    /** @brief Function Template Icon. */
    std::string icon;
    /** @brief Function Template ID. */
    std::string id;
    /** @brief Function Template Name. */
    std::string name;
    /** @brief Function Template Tagline. */
    std::string tagline;
    /** @brief Execution permissions. */
    std::vector<std::string> permissions;
    /** @brief Function trigger events. */
    std::vector<std::string> events;
    /** @brief Function execution schedult in CRON format. */
    std::string cron;
    /** @brief Function execution timeout in seconds. */
    int64_t timeout;
    /** @brief Function use cases. */
    std::vector<std::string> useCases;
    /** @brief List of runtimes that can be used with this template. */
    std::vector<appwrite::models::TemplateRuntime> runtimes;
    /** @brief Function Template Instructions. */
    std::string instructions;
    /** @brief VCS (Version Control System) Provider. */
    std::string vcsProvider;
    /** @brief VCS (Version Control System) Repository ID */
    std::string providerRepositoryId;
    /** @brief VCS (Version Control System) Owner. */
    std::string providerOwner;
    /** @brief VCS (Version Control System) branch version (tag). */
    std::string providerVersion;
    /** @brief Function variables. */
    std::vector<appwrite::models::TemplateVariable> variables;
    /** @brief Function scopes. */
    std::vector<std::string> scopes;

    /** @brief Deserialize from JSON */
    static TemplateFunction fromJson(const nlohmann::json& j) {
        TemplateFunction obj{}; // value-init: zeroes bools, ints, enums
        if (j.contains("icon") && !j["icon"].is_null()) {
            obj.icon = j["icon"].get<std::string>();
        }
        if (j.contains("id") && !j["id"].is_null()) {
            obj.id = j["id"].get<std::string>();
        }
        if (j.contains("name") && !j["name"].is_null()) {
            obj.name = j["name"].get<std::string>();
        }
        if (j.contains("tagline") && !j["tagline"].is_null()) {
            obj.tagline = j["tagline"].get<std::string>();
        }
        if (j.contains("permissions") && !j["permissions"].is_null()) {
            obj.permissions = j["permissions"].get<std::vector<std::string>>();
        }
        if (j.contains("events") && !j["events"].is_null()) {
            obj.events = j["events"].get<std::vector<std::string>>();
        }
        if (j.contains("cron") && !j["cron"].is_null()) {
            obj.cron = j["cron"].get<std::string>();
        }
        if (j.contains("timeout") && !j["timeout"].is_null()) {
            obj.timeout = j["timeout"].get<int64_t>();
        }
        if (j.contains("useCases") && !j["useCases"].is_null()) {
            obj.useCases = j["useCases"].get<std::vector<std::string>>();
        }
        if (j.contains("runtimes") && !j["runtimes"].is_null()) {
                                        for (auto& item : j["runtimes"]) {
                                obj.runtimes.push_back(TemplateRuntime::fromJson(item));
                            }
                    }
        if (j.contains("instructions") && !j["instructions"].is_null()) {
            obj.instructions = j["instructions"].get<std::string>();
        }
        if (j.contains("vcsProvider") && !j["vcsProvider"].is_null()) {
            obj.vcsProvider = j["vcsProvider"].get<std::string>();
        }
        if (j.contains("providerRepositoryId") && !j["providerRepositoryId"].is_null()) {
            obj.providerRepositoryId = j["providerRepositoryId"].get<std::string>();
        }
        if (j.contains("providerOwner") && !j["providerOwner"].is_null()) {
            obj.providerOwner = j["providerOwner"].get<std::string>();
        }
        if (j.contains("providerVersion") && !j["providerVersion"].is_null()) {
            obj.providerVersion = j["providerVersion"].get<std::string>();
        }
        if (j.contains("variables") && !j["variables"].is_null()) {
                                        for (auto& item : j["variables"]) {
                                obj.variables.push_back(TemplateVariable::fromJson(item));
                            }
                    }
        if (j.contains("scopes") && !j["scopes"].is_null()) {
            obj.scopes = j["scopes"].get<std::vector<std::string>>();
        }
        return obj;
    }

    /** @brief Serialize to JSON */
    [[nodiscard]] nlohmann::json toJson() const {
        nlohmann::json j = nlohmann::json::object();
        {
            j["icon"] = this->icon;
        }
        {
            j["id"] = this->id;
        }
        {
            j["name"] = this->name;
        }
        {
            j["tagline"] = this->tagline;
        }
        {
            j["permissions"] = this->permissions;
        }
        {
            j["events"] = this->events;
        }
        {
            j["cron"] = this->cron;
        }
        {
            j["timeout"] = this->timeout;
        }
        {
            j["useCases"] = this->useCases;
        }
        {
                nlohmann::json arr = nlohmann::json::array();
            for (auto& item : this->runtimes) arr.push_back(item.toJson());
            j["runtimes"] = arr;
            }
        {
            j["instructions"] = this->instructions;
        }
        {
            j["vcsProvider"] = this->vcsProvider;
        }
        {
            j["providerRepositoryId"] = this->providerRepositoryId;
        }
        {
            j["providerOwner"] = this->providerOwner;
        }
        {
            j["providerVersion"] = this->providerVersion;
        }
        {
                nlohmann::json arr = nlohmann::json::array();
            for (auto& item : this->variables) arr.push_back(item.toJson());
            j["variables"] = arr;
            }
        {
            j["scopes"] = this->scopes;
        }
        return j;
    }
};

/** @brief ADL hook for nlohmann::json */
inline void from_json(const nlohmann::json& j, TemplateFunction& x) {
    x = TemplateFunction::fromJson(j);
}

inline void to_json(nlohmann::json& j, const TemplateFunction& x) {
    j = x.toJson();
}

/**
 * @brief Function Templates List
 */
struct TemplateFunctionList {
    /** @brief Total number of templates that matched your query. */
    int64_t total;
    /** @brief List of templates. */
    std::vector<appwrite::models::TemplateFunction> templates;

    /** @brief Deserialize from JSON */
    static TemplateFunctionList fromJson(const nlohmann::json& j) {
        TemplateFunctionList obj{}; // value-init: zeroes bools, ints, enums
        if (j.contains("total") && !j["total"].is_null()) {
            obj.total = j["total"].get<int64_t>();
        }
        if (j.contains("templates") && !j["templates"].is_null()) {
                                        for (auto& item : j["templates"]) {
                                obj.templates.push_back(TemplateFunction::fromJson(item));
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
            for (auto& item : this->templates) arr.push_back(item.toJson());
            j["templates"] = arr;
            }
        return j;
    }
};

/** @brief ADL hook for nlohmann::json */
inline void from_json(const nlohmann::json& j, TemplateFunctionList& x) {
    x = TemplateFunctionList::fromJson(j);
}

inline void to_json(nlohmann::json& j, const TemplateFunctionList& x) {
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
        if (j.contains("$id") && !j["$id"].is_null()) {
            obj.id = j["$id"].get<std::string>();
        }
        if (j.contains("key") && !j["key"].is_null()) {
            obj.key = j["key"].get<std::string>();
        }
        if (j.contains("name") && !j["name"].is_null()) {
            obj.name = j["name"].get<std::string>();
        }
        if (j.contains("version") && !j["version"].is_null()) {
            obj.version = j["version"].get<std::string>();
        }
        if (j.contains("base") && !j["base"].is_null()) {
            obj.base = j["base"].get<std::string>();
        }
        if (j.contains("image") && !j["image"].is_null()) {
            obj.image = j["image"].get<std::string>();
        }
        if (j.contains("logo") && !j["logo"].is_null()) {
            obj.logo = j["logo"].get<std::string>();
        }
        if (j.contains("supports") && !j["supports"].is_null()) {
            obj.supports = j["supports"].get<std::vector<std::string>>();
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
        if (j.contains("total") && !j["total"].is_null()) {
            obj.total = j["total"].get<int64_t>();
        }
        if (j.contains("runtimes") && !j["runtimes"].is_null()) {
                                        for (auto& item : j["runtimes"]) {
                                obj.runtimes.push_back(Runtime::fromJson(item));
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
        if (j.contains("$id") && !j["$id"].is_null()) {
            obj.id = j["$id"].get<std::string>();
        }
        if (j.contains("$createdAt") && !j["$createdAt"].is_null()) {
            obj.createdAt = j["$createdAt"].get<std::string>();
        }
        if (j.contains("$updatedAt") && !j["$updatedAt"].is_null()) {
            obj.updatedAt = j["$updatedAt"].get<std::string>();
        }
        if (j.contains("type") && !j["type"].is_null()) {
            obj.type = j["type"].get<std::string>();
        }
        if (j.contains("resourceId") && !j["resourceId"].is_null()) {
            obj.resourceId = j["resourceId"].get<std::string>();
        }
        if (j.contains("resourceType") && !j["resourceType"].is_null()) {
            obj.resourceType = j["resourceType"].get<std::string>();
        }
        if (j.contains("entrypoint") && !j["entrypoint"].is_null()) {
            obj.entrypoint = j["entrypoint"].get<std::string>();
        }
        if (j.contains("sourceSize") && !j["sourceSize"].is_null()) {
            obj.sourceSize = j["sourceSize"].get<int64_t>();
        }
        if (j.contains("buildSize") && !j["buildSize"].is_null()) {
            obj.buildSize = j["buildSize"].get<int64_t>();
        }
        if (j.contains("totalSize") && !j["totalSize"].is_null()) {
            obj.totalSize = j["totalSize"].get<int64_t>();
        }
        if (j.contains("buildId") && !j["buildId"].is_null()) {
            obj.buildId = j["buildId"].get<std::string>();
        }
        if (j.contains("activate") && !j["activate"].is_null()) {
            obj.activate = j["activate"].get<bool>();
        }
        if (j.contains("screenshotLight") && !j["screenshotLight"].is_null()) {
            obj.screenshotLight = j["screenshotLight"].get<std::string>();
        }
        if (j.contains("screenshotDark") && !j["screenshotDark"].is_null()) {
            obj.screenshotDark = j["screenshotDark"].get<std::string>();
        }
        if (j.contains("status") && !j["status"].is_null()) {
            obj.status = j["status"].get<appwrite::enums::DeploymentStatus>();
        }
        if (j.contains("buildLogs") && !j["buildLogs"].is_null()) {
            obj.buildLogs = j["buildLogs"].get<std::string>();
        }
        if (j.contains("buildDuration") && !j["buildDuration"].is_null()) {
            obj.buildDuration = j["buildDuration"].get<int64_t>();
        }
        if (j.contains("providerRepositoryName") && !j["providerRepositoryName"].is_null()) {
            obj.providerRepositoryName = j["providerRepositoryName"].get<std::string>();
        }
        if (j.contains("providerRepositoryOwner") && !j["providerRepositoryOwner"].is_null()) {
            obj.providerRepositoryOwner = j["providerRepositoryOwner"].get<std::string>();
        }
        if (j.contains("providerRepositoryUrl") && !j["providerRepositoryUrl"].is_null()) {
            obj.providerRepositoryUrl = j["providerRepositoryUrl"].get<std::string>();
        }
        if (j.contains("providerCommitHash") && !j["providerCommitHash"].is_null()) {
            obj.providerCommitHash = j["providerCommitHash"].get<std::string>();
        }
        if (j.contains("providerCommitAuthorUrl") && !j["providerCommitAuthorUrl"].is_null()) {
            obj.providerCommitAuthorUrl = j["providerCommitAuthorUrl"].get<std::string>();
        }
        if (j.contains("providerCommitAuthor") && !j["providerCommitAuthor"].is_null()) {
            obj.providerCommitAuthor = j["providerCommitAuthor"].get<std::string>();
        }
        if (j.contains("providerCommitMessage") && !j["providerCommitMessage"].is_null()) {
            obj.providerCommitMessage = j["providerCommitMessage"].get<std::string>();
        }
        if (j.contains("providerCommitUrl") && !j["providerCommitUrl"].is_null()) {
            obj.providerCommitUrl = j["providerCommitUrl"].get<std::string>();
        }
        if (j.contains("providerBranch") && !j["providerBranch"].is_null()) {
            obj.providerBranch = j["providerBranch"].get<std::string>();
        }
        if (j.contains("providerBranchUrl") && !j["providerBranchUrl"].is_null()) {
            obj.providerBranchUrl = j["providerBranchUrl"].get<std::string>();
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
        if (j.contains("total") && !j["total"].is_null()) {
            obj.total = j["total"].get<int64_t>();
        }
        if (j.contains("deployments") && !j["deployments"].is_null()) {
                                        for (auto& item : j["deployments"]) {
                                obj.deployments.push_back(Deployment::fromJson(item));
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
        if (j.contains("name") && !j["name"].is_null()) {
            obj.name = j["name"].get<std::string>();
        }
        if (j.contains("value") && !j["value"].is_null()) {
            obj.value = j["value"].get<std::string>();
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
        if (j.contains("$id") && !j["$id"].is_null()) {
            obj.id = j["$id"].get<std::string>();
        }
        if (j.contains("$createdAt") && !j["$createdAt"].is_null()) {
            obj.createdAt = j["$createdAt"].get<std::string>();
        }
        if (j.contains("$updatedAt") && !j["$updatedAt"].is_null()) {
            obj.updatedAt = j["$updatedAt"].get<std::string>();
        }
        if (j.contains("$permissions") && !j["$permissions"].is_null()) {
            obj.permissions = j["$permissions"].get<std::vector<std::string>>();
        }
        if (j.contains("functionId") && !j["functionId"].is_null()) {
            obj.functionId = j["functionId"].get<std::string>();
        }
        if (j.contains("deploymentId") && !j["deploymentId"].is_null()) {
            obj.deploymentId = j["deploymentId"].get<std::string>();
        }
        if (j.contains("trigger") && !j["trigger"].is_null()) {
            obj.trigger = j["trigger"].get<appwrite::enums::ExecutionTrigger>();
        }
        if (j.contains("status") && !j["status"].is_null()) {
            obj.status = j["status"].get<appwrite::enums::ExecutionStatus>();
        }
        if (j.contains("requestMethod") && !j["requestMethod"].is_null()) {
            obj.requestMethod = j["requestMethod"].get<std::string>();
        }
        if (j.contains("requestPath") && !j["requestPath"].is_null()) {
            obj.requestPath = j["requestPath"].get<std::string>();
        }
        if (j.contains("requestHeaders") && !j["requestHeaders"].is_null()) {
                                        for (auto& item : j["requestHeaders"]) {
                                obj.requestHeaders.push_back(Headers::fromJson(item));
                            }
                    }
        if (j.contains("responseStatusCode") && !j["responseStatusCode"].is_null()) {
            obj.responseStatusCode = j["responseStatusCode"].get<int64_t>();
        }
        if (j.contains("responseBody") && !j["responseBody"].is_null()) {
            obj.responseBody = j["responseBody"].get<std::string>();
        }
        if (j.contains("responseHeaders") && !j["responseHeaders"].is_null()) {
                                        for (auto& item : j["responseHeaders"]) {
                                obj.responseHeaders.push_back(Headers::fromJson(item));
                            }
                    }
        if (j.contains("logs") && !j["logs"].is_null()) {
            obj.logs = j["logs"].get<std::string>();
        }
        if (j.contains("errors") && !j["errors"].is_null()) {
            obj.errors = j["errors"].get<std::string>();
        }
        if (j.contains("duration") && !j["duration"].is_null()) {
            obj.duration = j["duration"].get<double>();
        }
        if (j.contains("scheduledAt") && !j["scheduledAt"].is_null()) {
            obj.scheduledAt = j["scheduledAt"].get<std::optional<std::string>>();
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
        if (j.contains("total") && !j["total"].is_null()) {
            obj.total = j["total"].get<int64_t>();
        }
        if (j.contains("executions") && !j["executions"].is_null()) {
                                        for (auto& item : j["executions"]) {
                                obj.executions.push_back(Execution::fromJson(item));
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
        if (j.contains("$id") && !j["$id"].is_null()) {
            obj.id = j["$id"].get<std::string>();
        }
        if (j.contains("$createdAt") && !j["$createdAt"].is_null()) {
            obj.createdAt = j["$createdAt"].get<std::string>();
        }
        if (j.contains("$updatedAt") && !j["$updatedAt"].is_null()) {
            obj.updatedAt = j["$updatedAt"].get<std::string>();
        }
        if (j.contains("name") && !j["name"].is_null()) {
            obj.name = j["name"].get<std::string>();
        }
        if (j.contains("url") && !j["url"].is_null()) {
            obj.url = j["url"].get<std::string>();
        }
        if (j.contains("events") && !j["events"].is_null()) {
            obj.events = j["events"].get<std::vector<std::string>>();
        }
        if (j.contains("tls") && !j["tls"].is_null()) {
            obj.tls = j["tls"].get<bool>();
        }
        if (j.contains("authUsername") && !j["authUsername"].is_null()) {
            obj.authUsername = j["authUsername"].get<std::string>();
        }
        if (j.contains("authPassword") && !j["authPassword"].is_null()) {
            obj.authPassword = j["authPassword"].get<std::string>();
        }
        if (j.contains("secret") && !j["secret"].is_null()) {
            obj.secret = j["secret"].get<std::string>();
        }
        if (j.contains("enabled") && !j["enabled"].is_null()) {
            obj.enabled = j["enabled"].get<bool>();
        }
        if (j.contains("logs") && !j["logs"].is_null()) {
            obj.logs = j["logs"].get<std::string>();
        }
        if (j.contains("attempts") && !j["attempts"].is_null()) {
            obj.attempts = j["attempts"].get<int64_t>();
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
        if (j.contains("total") && !j["total"].is_null()) {
            obj.total = j["total"].get<int64_t>();
        }
        if (j.contains("webhooks") && !j["webhooks"].is_null()) {
                                        for (auto& item : j["webhooks"]) {
                                obj.webhooks.push_back(Webhook::fromJson(item));
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
 * @brief Key
 */
struct Key {
    /** @brief Key ID. */
    std::string id;
    /** @brief Key creation date in ISO 8601 format. */
    std::string createdAt;
    /** @brief Key update date in ISO 8601 format. */
    std::string updatedAt;
    /** @brief Key name. */
    std::string name;
    /** @brief Key expiration date in ISO 8601 format. */
    std::string expire;
    /** @brief Allowed permission scopes. */
    std::vector<std::string> scopes;
    /** @brief Secret key. */
    std::string secret;
    /** @brief Most recent access date in ISO 8601 format. This attribute is only updated again after 24 hours. */
    std::string accessedAt;
    /** @brief List of SDK user agents that used this key. */
    std::vector<std::string> sdks;

    /** @brief Deserialize from JSON */
    static Key fromJson(const nlohmann::json& j) {
        Key obj{}; // value-init: zeroes bools, ints, enums
        if (j.contains("$id") && !j["$id"].is_null()) {
            obj.id = j["$id"].get<std::string>();
        }
        if (j.contains("$createdAt") && !j["$createdAt"].is_null()) {
            obj.createdAt = j["$createdAt"].get<std::string>();
        }
        if (j.contains("$updatedAt") && !j["$updatedAt"].is_null()) {
            obj.updatedAt = j["$updatedAt"].get<std::string>();
        }
        if (j.contains("name") && !j["name"].is_null()) {
            obj.name = j["name"].get<std::string>();
        }
        if (j.contains("expire") && !j["expire"].is_null()) {
            obj.expire = j["expire"].get<std::string>();
        }
        if (j.contains("scopes") && !j["scopes"].is_null()) {
            obj.scopes = j["scopes"].get<std::vector<std::string>>();
        }
        if (j.contains("secret") && !j["secret"].is_null()) {
            obj.secret = j["secret"].get<std::string>();
        }
        if (j.contains("accessedAt") && !j["accessedAt"].is_null()) {
            obj.accessedAt = j["accessedAt"].get<std::string>();
        }
        if (j.contains("sdks") && !j["sdks"].is_null()) {
            obj.sdks = j["sdks"].get<std::vector<std::string>>();
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
            j["expire"] = this->expire;
        }
        {
            j["scopes"] = this->scopes;
        }
        {
            j["secret"] = this->secret;
        }
        {
            j["accessedAt"] = this->accessedAt;
        }
        {
            j["sdks"] = this->sdks;
        }
        return j;
    }
};

/** @brief ADL hook for nlohmann::json */
inline void from_json(const nlohmann::json& j, Key& x) {
    x = Key::fromJson(j);
}

inline void to_json(nlohmann::json& j, const Key& x) {
    j = x.toJson();
}

/**
 * @brief API Keys List
 */
struct KeyList {
    /** @brief Total number of keys that matched your query. */
    int64_t total;
    /** @brief List of keys. */
    std::vector<appwrite::models::Key> keys;

    /** @brief Deserialize from JSON */
    static KeyList fromJson(const nlohmann::json& j) {
        KeyList obj{}; // value-init: zeroes bools, ints, enums
        if (j.contains("total") && !j["total"].is_null()) {
            obj.total = j["total"].get<int64_t>();
        }
        if (j.contains("keys") && !j["keys"].is_null()) {
                                        for (auto& item : j["keys"]) {
                                obj.keys.push_back(Key::fromJson(item));
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
            for (auto& item : this->keys) arr.push_back(item.toJson());
            j["keys"] = arr;
            }
        return j;
    }
};

/** @brief ADL hook for nlohmann::json */
inline void from_json(const nlohmann::json& j, KeyList& x) {
    x = KeyList::fromJson(j);
}

inline void to_json(nlohmann::json& j, const KeyList& x) {
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
        if (j.contains("total") && !j["total"].is_null()) {
            obj.total = j["total"].get<int64_t>();
        }
        if (j.contains("variables") && !j["variables"].is_null()) {
                                        for (auto& item : j["variables"]) {
                                obj.variables.push_back(Variable::fromJson(item));
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
        if (j.contains("total") && !j["total"].is_null()) {
            obj.total = j["total"].get<int64_t>();
        }
        if (j.contains("targets") && !j["targets"].is_null()) {
                                        for (auto& item : j["targets"]) {
                                obj.targets.push_back(Target::fromJson(item));
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
        if (j.contains("$id") && !j["$id"].is_null()) {
            obj.id = j["$id"].get<std::string>();
        }
        if (j.contains("$createdAt") && !j["$createdAt"].is_null()) {
            obj.createdAt = j["$createdAt"].get<std::string>();
        }
        if (j.contains("$updatedAt") && !j["$updatedAt"].is_null()) {
            obj.updatedAt = j["$updatedAt"].get<std::string>();
        }
        if (j.contains("status") && !j["status"].is_null()) {
            obj.status = j["status"].get<std::string>();
        }
        if (j.contains("operations") && !j["operations"].is_null()) {
            obj.operations = j["operations"].get<int64_t>();
        }
        if (j.contains("expiresAt") && !j["expiresAt"].is_null()) {
            obj.expiresAt = j["expiresAt"].get<std::string>();
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
        if (j.contains("total") && !j["total"].is_null()) {
            obj.total = j["total"].get<int64_t>();
        }
        if (j.contains("transactions") && !j["transactions"].is_null()) {
                                        for (auto& item : j["transactions"]) {
                                obj.transactions.push_back(Transaction::fromJson(item));
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
        if (j.contains("memory") && !j["memory"].is_null()) {
            obj.memory = j["memory"].get<int64_t>();
        }
        if (j.contains("cpus") && !j["cpus"].is_null()) {
            obj.cpus = j["cpus"].get<double>();
        }
        if (j.contains("enabled") && !j["enabled"].is_null()) {
            obj.enabled = j["enabled"].get<bool>();
        }
        if (j.contains("slug") && !j["slug"].is_null()) {
            obj.slug = j["slug"].get<std::string>();
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
        if (j.contains("total") && !j["total"].is_null()) {
            obj.total = j["total"].get<int64_t>();
        }
        if (j.contains("specifications") && !j["specifications"].is_null()) {
                                        for (auto& item : j["specifications"]) {
                                obj.specifications.push_back(Specification::fromJson(item));
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
        if (j.contains("total") && !j["total"].is_null()) {
            obj.total = j["total"].get<int64_t>();
        }
        if (j.contains("attributes") && !j["attributes"].is_null()) {
            obj.attributes = j["attributes"].get<std::vector<std::string>>();
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
        if (j.contains("key") && !j["key"].is_null()) {
            obj.key = j["key"].get<std::string>();
        }
        if (j.contains("type") && !j["type"].is_null()) {
            obj.type = j["type"].get<std::string>();
        }
        if (j.contains("status") && !j["status"].is_null()) {
            obj.status = j["status"].get<appwrite::enums::AttributeStatus>();
        }
        if (j.contains("error") && !j["error"].is_null()) {
            obj.error = j["error"].get<std::string>();
        }
        if (j.contains("required") && !j["required"].is_null()) {
            obj.required = j["required"].get<bool>();
        }
        if (j.contains("array") && !j["array"].is_null()) {
            obj.array = j["array"].get<std::optional<bool>>();
        }
        if (j.contains("$createdAt") && !j["$createdAt"].is_null()) {
            obj.createdAt = j["$createdAt"].get<std::string>();
        }
        if (j.contains("$updatedAt") && !j["$updatedAt"].is_null()) {
            obj.updatedAt = j["$updatedAt"].get<std::string>();
        }
        if (j.contains("size") && !j["size"].is_null()) {
            obj.size = j["size"].get<int64_t>();
        }
        if (j.contains("default") && !j["default"].is_null()) {
            obj.default_ = j["default"].get<std::optional<std::string>>();
        }
        if (j.contains("encrypt") && !j["encrypt"].is_null()) {
            obj.encrypt = j["encrypt"].get<std::optional<bool>>();
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
        if (j.contains("key") && !j["key"].is_null()) {
            obj.key = j["key"].get<std::string>();
        }
        if (j.contains("type") && !j["type"].is_null()) {
            obj.type = j["type"].get<std::string>();
        }
        if (j.contains("status") && !j["status"].is_null()) {
            obj.status = j["status"].get<appwrite::enums::AttributeStatus>();
        }
        if (j.contains("error") && !j["error"].is_null()) {
            obj.error = j["error"].get<std::string>();
        }
        if (j.contains("required") && !j["required"].is_null()) {
            obj.required = j["required"].get<bool>();
        }
        if (j.contains("array") && !j["array"].is_null()) {
            obj.array = j["array"].get<std::optional<bool>>();
        }
        if (j.contains("$createdAt") && !j["$createdAt"].is_null()) {
            obj.createdAt = j["$createdAt"].get<std::string>();
        }
        if (j.contains("$updatedAt") && !j["$updatedAt"].is_null()) {
            obj.updatedAt = j["$updatedAt"].get<std::string>();
        }
        if (j.contains("min") && !j["min"].is_null()) {
            obj.min = j["min"].get<std::optional<int64_t>>();
        }
        if (j.contains("max") && !j["max"].is_null()) {
            obj.max = j["max"].get<std::optional<int64_t>>();
        }
        if (j.contains("default") && !j["default"].is_null()) {
            obj.default_ = j["default"].get<std::optional<int64_t>>();
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
        if (j.contains("key") && !j["key"].is_null()) {
            obj.key = j["key"].get<std::string>();
        }
        if (j.contains("type") && !j["type"].is_null()) {
            obj.type = j["type"].get<std::string>();
        }
        if (j.contains("status") && !j["status"].is_null()) {
            obj.status = j["status"].get<appwrite::enums::AttributeStatus>();
        }
        if (j.contains("error") && !j["error"].is_null()) {
            obj.error = j["error"].get<std::string>();
        }
        if (j.contains("required") && !j["required"].is_null()) {
            obj.required = j["required"].get<bool>();
        }
        if (j.contains("array") && !j["array"].is_null()) {
            obj.array = j["array"].get<std::optional<bool>>();
        }
        if (j.contains("$createdAt") && !j["$createdAt"].is_null()) {
            obj.createdAt = j["$createdAt"].get<std::string>();
        }
        if (j.contains("$updatedAt") && !j["$updatedAt"].is_null()) {
            obj.updatedAt = j["$updatedAt"].get<std::string>();
        }
        if (j.contains("min") && !j["min"].is_null()) {
            obj.min = j["min"].get<std::optional<double>>();
        }
        if (j.contains("max") && !j["max"].is_null()) {
            obj.max = j["max"].get<std::optional<double>>();
        }
        if (j.contains("default") && !j["default"].is_null()) {
            obj.default_ = j["default"].get<std::optional<double>>();
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
        if (j.contains("key") && !j["key"].is_null()) {
            obj.key = j["key"].get<std::string>();
        }
        if (j.contains("type") && !j["type"].is_null()) {
            obj.type = j["type"].get<std::string>();
        }
        if (j.contains("status") && !j["status"].is_null()) {
            obj.status = j["status"].get<appwrite::enums::AttributeStatus>();
        }
        if (j.contains("error") && !j["error"].is_null()) {
            obj.error = j["error"].get<std::string>();
        }
        if (j.contains("required") && !j["required"].is_null()) {
            obj.required = j["required"].get<bool>();
        }
        if (j.contains("array") && !j["array"].is_null()) {
            obj.array = j["array"].get<std::optional<bool>>();
        }
        if (j.contains("$createdAt") && !j["$createdAt"].is_null()) {
            obj.createdAt = j["$createdAt"].get<std::string>();
        }
        if (j.contains("$updatedAt") && !j["$updatedAt"].is_null()) {
            obj.updatedAt = j["$updatedAt"].get<std::string>();
        }
        if (j.contains("default") && !j["default"].is_null()) {
            obj.default_ = j["default"].get<std::optional<bool>>();
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
        if (j.contains("key") && !j["key"].is_null()) {
            obj.key = j["key"].get<std::string>();
        }
        if (j.contains("type") && !j["type"].is_null()) {
            obj.type = j["type"].get<std::string>();
        }
        if (j.contains("status") && !j["status"].is_null()) {
            obj.status = j["status"].get<appwrite::enums::AttributeStatus>();
        }
        if (j.contains("error") && !j["error"].is_null()) {
            obj.error = j["error"].get<std::string>();
        }
        if (j.contains("required") && !j["required"].is_null()) {
            obj.required = j["required"].get<bool>();
        }
        if (j.contains("array") && !j["array"].is_null()) {
            obj.array = j["array"].get<std::optional<bool>>();
        }
        if (j.contains("$createdAt") && !j["$createdAt"].is_null()) {
            obj.createdAt = j["$createdAt"].get<std::string>();
        }
        if (j.contains("$updatedAt") && !j["$updatedAt"].is_null()) {
            obj.updatedAt = j["$updatedAt"].get<std::string>();
        }
        if (j.contains("format") && !j["format"].is_null()) {
            obj.format = j["format"].get<std::string>();
        }
        if (j.contains("default") && !j["default"].is_null()) {
            obj.default_ = j["default"].get<std::optional<std::string>>();
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
        if (j.contains("key") && !j["key"].is_null()) {
            obj.key = j["key"].get<std::string>();
        }
        if (j.contains("type") && !j["type"].is_null()) {
            obj.type = j["type"].get<std::string>();
        }
        if (j.contains("status") && !j["status"].is_null()) {
            obj.status = j["status"].get<appwrite::enums::AttributeStatus>();
        }
        if (j.contains("error") && !j["error"].is_null()) {
            obj.error = j["error"].get<std::string>();
        }
        if (j.contains("required") && !j["required"].is_null()) {
            obj.required = j["required"].get<bool>();
        }
        if (j.contains("array") && !j["array"].is_null()) {
            obj.array = j["array"].get<std::optional<bool>>();
        }
        if (j.contains("$createdAt") && !j["$createdAt"].is_null()) {
            obj.createdAt = j["$createdAt"].get<std::string>();
        }
        if (j.contains("$updatedAt") && !j["$updatedAt"].is_null()) {
            obj.updatedAt = j["$updatedAt"].get<std::string>();
        }
        if (j.contains("elements") && !j["elements"].is_null()) {
            obj.elements = j["elements"].get<std::vector<std::string>>();
        }
        if (j.contains("format") && !j["format"].is_null()) {
            obj.format = j["format"].get<std::string>();
        }
        if (j.contains("default") && !j["default"].is_null()) {
            obj.default_ = j["default"].get<std::optional<std::string>>();
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
        if (j.contains("key") && !j["key"].is_null()) {
            obj.key = j["key"].get<std::string>();
        }
        if (j.contains("type") && !j["type"].is_null()) {
            obj.type = j["type"].get<std::string>();
        }
        if (j.contains("status") && !j["status"].is_null()) {
            obj.status = j["status"].get<appwrite::enums::AttributeStatus>();
        }
        if (j.contains("error") && !j["error"].is_null()) {
            obj.error = j["error"].get<std::string>();
        }
        if (j.contains("required") && !j["required"].is_null()) {
            obj.required = j["required"].get<bool>();
        }
        if (j.contains("array") && !j["array"].is_null()) {
            obj.array = j["array"].get<std::optional<bool>>();
        }
        if (j.contains("$createdAt") && !j["$createdAt"].is_null()) {
            obj.createdAt = j["$createdAt"].get<std::string>();
        }
        if (j.contains("$updatedAt") && !j["$updatedAt"].is_null()) {
            obj.updatedAt = j["$updatedAt"].get<std::string>();
        }
        if (j.contains("format") && !j["format"].is_null()) {
            obj.format = j["format"].get<std::string>();
        }
        if (j.contains("default") && !j["default"].is_null()) {
            obj.default_ = j["default"].get<std::optional<std::string>>();
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
        if (j.contains("key") && !j["key"].is_null()) {
            obj.key = j["key"].get<std::string>();
        }
        if (j.contains("type") && !j["type"].is_null()) {
            obj.type = j["type"].get<std::string>();
        }
        if (j.contains("status") && !j["status"].is_null()) {
            obj.status = j["status"].get<appwrite::enums::AttributeStatus>();
        }
        if (j.contains("error") && !j["error"].is_null()) {
            obj.error = j["error"].get<std::string>();
        }
        if (j.contains("required") && !j["required"].is_null()) {
            obj.required = j["required"].get<bool>();
        }
        if (j.contains("array") && !j["array"].is_null()) {
            obj.array = j["array"].get<std::optional<bool>>();
        }
        if (j.contains("$createdAt") && !j["$createdAt"].is_null()) {
            obj.createdAt = j["$createdAt"].get<std::string>();
        }
        if (j.contains("$updatedAt") && !j["$updatedAt"].is_null()) {
            obj.updatedAt = j["$updatedAt"].get<std::string>();
        }
        if (j.contains("format") && !j["format"].is_null()) {
            obj.format = j["format"].get<std::string>();
        }
        if (j.contains("default") && !j["default"].is_null()) {
            obj.default_ = j["default"].get<std::optional<std::string>>();
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
        if (j.contains("key") && !j["key"].is_null()) {
            obj.key = j["key"].get<std::string>();
        }
        if (j.contains("type") && !j["type"].is_null()) {
            obj.type = j["type"].get<std::string>();
        }
        if (j.contains("status") && !j["status"].is_null()) {
            obj.status = j["status"].get<appwrite::enums::AttributeStatus>();
        }
        if (j.contains("error") && !j["error"].is_null()) {
            obj.error = j["error"].get<std::string>();
        }
        if (j.contains("required") && !j["required"].is_null()) {
            obj.required = j["required"].get<bool>();
        }
        if (j.contains("array") && !j["array"].is_null()) {
            obj.array = j["array"].get<std::optional<bool>>();
        }
        if (j.contains("$createdAt") && !j["$createdAt"].is_null()) {
            obj.createdAt = j["$createdAt"].get<std::string>();
        }
        if (j.contains("$updatedAt") && !j["$updatedAt"].is_null()) {
            obj.updatedAt = j["$updatedAt"].get<std::string>();
        }
        if (j.contains("format") && !j["format"].is_null()) {
            obj.format = j["format"].get<std::string>();
        }
        if (j.contains("default") && !j["default"].is_null()) {
            obj.default_ = j["default"].get<std::optional<std::string>>();
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
        if (j.contains("key") && !j["key"].is_null()) {
            obj.key = j["key"].get<std::string>();
        }
        if (j.contains("type") && !j["type"].is_null()) {
            obj.type = j["type"].get<std::string>();
        }
        if (j.contains("status") && !j["status"].is_null()) {
            obj.status = j["status"].get<appwrite::enums::AttributeStatus>();
        }
        if (j.contains("error") && !j["error"].is_null()) {
            obj.error = j["error"].get<std::string>();
        }
        if (j.contains("required") && !j["required"].is_null()) {
            obj.required = j["required"].get<bool>();
        }
        if (j.contains("array") && !j["array"].is_null()) {
            obj.array = j["array"].get<std::optional<bool>>();
        }
        if (j.contains("$createdAt") && !j["$createdAt"].is_null()) {
            obj.createdAt = j["$createdAt"].get<std::string>();
        }
        if (j.contains("$updatedAt") && !j["$updatedAt"].is_null()) {
            obj.updatedAt = j["$updatedAt"].get<std::string>();
        }
        if (j.contains("relatedCollection") && !j["relatedCollection"].is_null()) {
            obj.relatedCollection = j["relatedCollection"].get<std::string>();
        }
        if (j.contains("relationType") && !j["relationType"].is_null()) {
            obj.relationType = j["relationType"].get<std::string>();
        }
        if (j.contains("twoWay") && !j["twoWay"].is_null()) {
            obj.twoWay = j["twoWay"].get<bool>();
        }
        if (j.contains("twoWayKey") && !j["twoWayKey"].is_null()) {
            obj.twoWayKey = j["twoWayKey"].get<std::string>();
        }
        if (j.contains("onDelete") && !j["onDelete"].is_null()) {
            obj.onDelete = j["onDelete"].get<std::string>();
        }
        if (j.contains("side") && !j["side"].is_null()) {
            obj.side = j["side"].get<std::string>();
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
        if (j.contains("key") && !j["key"].is_null()) {
            obj.key = j["key"].get<std::string>();
        }
        if (j.contains("type") && !j["type"].is_null()) {
            obj.type = j["type"].get<std::string>();
        }
        if (j.contains("status") && !j["status"].is_null()) {
            obj.status = j["status"].get<appwrite::enums::AttributeStatus>();
        }
        if (j.contains("error") && !j["error"].is_null()) {
            obj.error = j["error"].get<std::string>();
        }
        if (j.contains("required") && !j["required"].is_null()) {
            obj.required = j["required"].get<bool>();
        }
        if (j.contains("array") && !j["array"].is_null()) {
            obj.array = j["array"].get<std::optional<bool>>();
        }
        if (j.contains("$createdAt") && !j["$createdAt"].is_null()) {
            obj.createdAt = j["$createdAt"].get<std::string>();
        }
        if (j.contains("$updatedAt") && !j["$updatedAt"].is_null()) {
            obj.updatedAt = j["$updatedAt"].get<std::string>();
        }
        if (j.contains("default") && !j["default"].is_null()) {
            obj.default_ = j["default"].get<std::optional<std::vector<std::string>>>();
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
        if (j.contains("key") && !j["key"].is_null()) {
            obj.key = j["key"].get<std::string>();
        }
        if (j.contains("type") && !j["type"].is_null()) {
            obj.type = j["type"].get<std::string>();
        }
        if (j.contains("status") && !j["status"].is_null()) {
            obj.status = j["status"].get<appwrite::enums::AttributeStatus>();
        }
        if (j.contains("error") && !j["error"].is_null()) {
            obj.error = j["error"].get<std::string>();
        }
        if (j.contains("required") && !j["required"].is_null()) {
            obj.required = j["required"].get<bool>();
        }
        if (j.contains("array") && !j["array"].is_null()) {
            obj.array = j["array"].get<std::optional<bool>>();
        }
        if (j.contains("$createdAt") && !j["$createdAt"].is_null()) {
            obj.createdAt = j["$createdAt"].get<std::string>();
        }
        if (j.contains("$updatedAt") && !j["$updatedAt"].is_null()) {
            obj.updatedAt = j["$updatedAt"].get<std::string>();
        }
        if (j.contains("default") && !j["default"].is_null()) {
            obj.default_ = j["default"].get<std::optional<std::vector<std::string>>>();
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
        if (j.contains("key") && !j["key"].is_null()) {
            obj.key = j["key"].get<std::string>();
        }
        if (j.contains("type") && !j["type"].is_null()) {
            obj.type = j["type"].get<std::string>();
        }
        if (j.contains("status") && !j["status"].is_null()) {
            obj.status = j["status"].get<appwrite::enums::AttributeStatus>();
        }
        if (j.contains("error") && !j["error"].is_null()) {
            obj.error = j["error"].get<std::string>();
        }
        if (j.contains("required") && !j["required"].is_null()) {
            obj.required = j["required"].get<bool>();
        }
        if (j.contains("array") && !j["array"].is_null()) {
            obj.array = j["array"].get<std::optional<bool>>();
        }
        if (j.contains("$createdAt") && !j["$createdAt"].is_null()) {
            obj.createdAt = j["$createdAt"].get<std::string>();
        }
        if (j.contains("$updatedAt") && !j["$updatedAt"].is_null()) {
            obj.updatedAt = j["$updatedAt"].get<std::string>();
        }
        if (j.contains("default") && !j["default"].is_null()) {
            obj.default_ = j["default"].get<std::optional<std::vector<std::string>>>();
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
        if (j.contains("key") && !j["key"].is_null()) {
            obj.key = j["key"].get<std::string>();
        }
        if (j.contains("type") && !j["type"].is_null()) {
            obj.type = j["type"].get<std::string>();
        }
        if (j.contains("status") && !j["status"].is_null()) {
            obj.status = j["status"].get<appwrite::enums::AttributeStatus>();
        }
        if (j.contains("error") && !j["error"].is_null()) {
            obj.error = j["error"].get<std::string>();
        }
        if (j.contains("required") && !j["required"].is_null()) {
            obj.required = j["required"].get<bool>();
        }
        if (j.contains("array") && !j["array"].is_null()) {
            obj.array = j["array"].get<std::optional<bool>>();
        }
        if (j.contains("$createdAt") && !j["$createdAt"].is_null()) {
            obj.createdAt = j["$createdAt"].get<std::string>();
        }
        if (j.contains("$updatedAt") && !j["$updatedAt"].is_null()) {
            obj.updatedAt = j["$updatedAt"].get<std::string>();
        }
        if (j.contains("size") && !j["size"].is_null()) {
            obj.size = j["size"].get<int64_t>();
        }
        if (j.contains("default") && !j["default"].is_null()) {
            obj.default_ = j["default"].get<std::optional<std::string>>();
        }
        if (j.contains("encrypt") && !j["encrypt"].is_null()) {
            obj.encrypt = j["encrypt"].get<std::optional<bool>>();
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
        if (j.contains("key") && !j["key"].is_null()) {
            obj.key = j["key"].get<std::string>();
        }
        if (j.contains("type") && !j["type"].is_null()) {
            obj.type = j["type"].get<std::string>();
        }
        if (j.contains("status") && !j["status"].is_null()) {
            obj.status = j["status"].get<appwrite::enums::AttributeStatus>();
        }
        if (j.contains("error") && !j["error"].is_null()) {
            obj.error = j["error"].get<std::string>();
        }
        if (j.contains("required") && !j["required"].is_null()) {
            obj.required = j["required"].get<bool>();
        }
        if (j.contains("array") && !j["array"].is_null()) {
            obj.array = j["array"].get<std::optional<bool>>();
        }
        if (j.contains("$createdAt") && !j["$createdAt"].is_null()) {
            obj.createdAt = j["$createdAt"].get<std::string>();
        }
        if (j.contains("$updatedAt") && !j["$updatedAt"].is_null()) {
            obj.updatedAt = j["$updatedAt"].get<std::string>();
        }
        if (j.contains("default") && !j["default"].is_null()) {
            obj.default_ = j["default"].get<std::optional<std::string>>();
        }
        if (j.contains("encrypt") && !j["encrypt"].is_null()) {
            obj.encrypt = j["encrypt"].get<std::optional<bool>>();
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
        if (j.contains("key") && !j["key"].is_null()) {
            obj.key = j["key"].get<std::string>();
        }
        if (j.contains("type") && !j["type"].is_null()) {
            obj.type = j["type"].get<std::string>();
        }
        if (j.contains("status") && !j["status"].is_null()) {
            obj.status = j["status"].get<appwrite::enums::AttributeStatus>();
        }
        if (j.contains("error") && !j["error"].is_null()) {
            obj.error = j["error"].get<std::string>();
        }
        if (j.contains("required") && !j["required"].is_null()) {
            obj.required = j["required"].get<bool>();
        }
        if (j.contains("array") && !j["array"].is_null()) {
            obj.array = j["array"].get<std::optional<bool>>();
        }
        if (j.contains("$createdAt") && !j["$createdAt"].is_null()) {
            obj.createdAt = j["$createdAt"].get<std::string>();
        }
        if (j.contains("$updatedAt") && !j["$updatedAt"].is_null()) {
            obj.updatedAt = j["$updatedAt"].get<std::string>();
        }
        if (j.contains("default") && !j["default"].is_null()) {
            obj.default_ = j["default"].get<std::optional<std::string>>();
        }
        if (j.contains("encrypt") && !j["encrypt"].is_null()) {
            obj.encrypt = j["encrypt"].get<std::optional<bool>>();
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
        if (j.contains("key") && !j["key"].is_null()) {
            obj.key = j["key"].get<std::string>();
        }
        if (j.contains("type") && !j["type"].is_null()) {
            obj.type = j["type"].get<std::string>();
        }
        if (j.contains("status") && !j["status"].is_null()) {
            obj.status = j["status"].get<appwrite::enums::AttributeStatus>();
        }
        if (j.contains("error") && !j["error"].is_null()) {
            obj.error = j["error"].get<std::string>();
        }
        if (j.contains("required") && !j["required"].is_null()) {
            obj.required = j["required"].get<bool>();
        }
        if (j.contains("array") && !j["array"].is_null()) {
            obj.array = j["array"].get<std::optional<bool>>();
        }
        if (j.contains("$createdAt") && !j["$createdAt"].is_null()) {
            obj.createdAt = j["$createdAt"].get<std::string>();
        }
        if (j.contains("$updatedAt") && !j["$updatedAt"].is_null()) {
            obj.updatedAt = j["$updatedAt"].get<std::string>();
        }
        if (j.contains("default") && !j["default"].is_null()) {
            obj.default_ = j["default"].get<std::optional<std::string>>();
        }
        if (j.contains("encrypt") && !j["encrypt"].is_null()) {
            obj.encrypt = j["encrypt"].get<std::optional<bool>>();
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
        if (j.contains("total") && !j["total"].is_null()) {
            obj.total = j["total"].get<int64_t>();
        }
        if (j.contains("columns") && !j["columns"].is_null()) {
            obj.columns = j["columns"].get<std::vector<std::string>>();
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
        if (j.contains("key") && !j["key"].is_null()) {
            obj.key = j["key"].get<std::string>();
        }
        if (j.contains("type") && !j["type"].is_null()) {
            obj.type = j["type"].get<std::string>();
        }
        if (j.contains("status") && !j["status"].is_null()) {
            obj.status = j["status"].get<appwrite::enums::ColumnStatus>();
        }
        if (j.contains("error") && !j["error"].is_null()) {
            obj.error = j["error"].get<std::string>();
        }
        if (j.contains("required") && !j["required"].is_null()) {
            obj.required = j["required"].get<bool>();
        }
        if (j.contains("array") && !j["array"].is_null()) {
            obj.array = j["array"].get<std::optional<bool>>();
        }
        if (j.contains("$createdAt") && !j["$createdAt"].is_null()) {
            obj.createdAt = j["$createdAt"].get<std::string>();
        }
        if (j.contains("$updatedAt") && !j["$updatedAt"].is_null()) {
            obj.updatedAt = j["$updatedAt"].get<std::string>();
        }
        if (j.contains("size") && !j["size"].is_null()) {
            obj.size = j["size"].get<int64_t>();
        }
        if (j.contains("default") && !j["default"].is_null()) {
            obj.default_ = j["default"].get<std::optional<std::string>>();
        }
        if (j.contains("encrypt") && !j["encrypt"].is_null()) {
            obj.encrypt = j["encrypt"].get<std::optional<bool>>();
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
        if (j.contains("key") && !j["key"].is_null()) {
            obj.key = j["key"].get<std::string>();
        }
        if (j.contains("type") && !j["type"].is_null()) {
            obj.type = j["type"].get<std::string>();
        }
        if (j.contains("status") && !j["status"].is_null()) {
            obj.status = j["status"].get<appwrite::enums::ColumnStatus>();
        }
        if (j.contains("error") && !j["error"].is_null()) {
            obj.error = j["error"].get<std::string>();
        }
        if (j.contains("required") && !j["required"].is_null()) {
            obj.required = j["required"].get<bool>();
        }
        if (j.contains("array") && !j["array"].is_null()) {
            obj.array = j["array"].get<std::optional<bool>>();
        }
        if (j.contains("$createdAt") && !j["$createdAt"].is_null()) {
            obj.createdAt = j["$createdAt"].get<std::string>();
        }
        if (j.contains("$updatedAt") && !j["$updatedAt"].is_null()) {
            obj.updatedAt = j["$updatedAt"].get<std::string>();
        }
        if (j.contains("min") && !j["min"].is_null()) {
            obj.min = j["min"].get<std::optional<int64_t>>();
        }
        if (j.contains("max") && !j["max"].is_null()) {
            obj.max = j["max"].get<std::optional<int64_t>>();
        }
        if (j.contains("default") && !j["default"].is_null()) {
            obj.default_ = j["default"].get<std::optional<int64_t>>();
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
        if (j.contains("key") && !j["key"].is_null()) {
            obj.key = j["key"].get<std::string>();
        }
        if (j.contains("type") && !j["type"].is_null()) {
            obj.type = j["type"].get<std::string>();
        }
        if (j.contains("status") && !j["status"].is_null()) {
            obj.status = j["status"].get<appwrite::enums::ColumnStatus>();
        }
        if (j.contains("error") && !j["error"].is_null()) {
            obj.error = j["error"].get<std::string>();
        }
        if (j.contains("required") && !j["required"].is_null()) {
            obj.required = j["required"].get<bool>();
        }
        if (j.contains("array") && !j["array"].is_null()) {
            obj.array = j["array"].get<std::optional<bool>>();
        }
        if (j.contains("$createdAt") && !j["$createdAt"].is_null()) {
            obj.createdAt = j["$createdAt"].get<std::string>();
        }
        if (j.contains("$updatedAt") && !j["$updatedAt"].is_null()) {
            obj.updatedAt = j["$updatedAt"].get<std::string>();
        }
        if (j.contains("min") && !j["min"].is_null()) {
            obj.min = j["min"].get<std::optional<double>>();
        }
        if (j.contains("max") && !j["max"].is_null()) {
            obj.max = j["max"].get<std::optional<double>>();
        }
        if (j.contains("default") && !j["default"].is_null()) {
            obj.default_ = j["default"].get<std::optional<double>>();
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
        if (j.contains("key") && !j["key"].is_null()) {
            obj.key = j["key"].get<std::string>();
        }
        if (j.contains("type") && !j["type"].is_null()) {
            obj.type = j["type"].get<std::string>();
        }
        if (j.contains("status") && !j["status"].is_null()) {
            obj.status = j["status"].get<appwrite::enums::ColumnStatus>();
        }
        if (j.contains("error") && !j["error"].is_null()) {
            obj.error = j["error"].get<std::string>();
        }
        if (j.contains("required") && !j["required"].is_null()) {
            obj.required = j["required"].get<bool>();
        }
        if (j.contains("array") && !j["array"].is_null()) {
            obj.array = j["array"].get<std::optional<bool>>();
        }
        if (j.contains("$createdAt") && !j["$createdAt"].is_null()) {
            obj.createdAt = j["$createdAt"].get<std::string>();
        }
        if (j.contains("$updatedAt") && !j["$updatedAt"].is_null()) {
            obj.updatedAt = j["$updatedAt"].get<std::string>();
        }
        if (j.contains("default") && !j["default"].is_null()) {
            obj.default_ = j["default"].get<std::optional<bool>>();
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
        if (j.contains("key") && !j["key"].is_null()) {
            obj.key = j["key"].get<std::string>();
        }
        if (j.contains("type") && !j["type"].is_null()) {
            obj.type = j["type"].get<std::string>();
        }
        if (j.contains("status") && !j["status"].is_null()) {
            obj.status = j["status"].get<appwrite::enums::ColumnStatus>();
        }
        if (j.contains("error") && !j["error"].is_null()) {
            obj.error = j["error"].get<std::string>();
        }
        if (j.contains("required") && !j["required"].is_null()) {
            obj.required = j["required"].get<bool>();
        }
        if (j.contains("array") && !j["array"].is_null()) {
            obj.array = j["array"].get<std::optional<bool>>();
        }
        if (j.contains("$createdAt") && !j["$createdAt"].is_null()) {
            obj.createdAt = j["$createdAt"].get<std::string>();
        }
        if (j.contains("$updatedAt") && !j["$updatedAt"].is_null()) {
            obj.updatedAt = j["$updatedAt"].get<std::string>();
        }
        if (j.contains("format") && !j["format"].is_null()) {
            obj.format = j["format"].get<std::string>();
        }
        if (j.contains("default") && !j["default"].is_null()) {
            obj.default_ = j["default"].get<std::optional<std::string>>();
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
        if (j.contains("key") && !j["key"].is_null()) {
            obj.key = j["key"].get<std::string>();
        }
        if (j.contains("type") && !j["type"].is_null()) {
            obj.type = j["type"].get<std::string>();
        }
        if (j.contains("status") && !j["status"].is_null()) {
            obj.status = j["status"].get<appwrite::enums::ColumnStatus>();
        }
        if (j.contains("error") && !j["error"].is_null()) {
            obj.error = j["error"].get<std::string>();
        }
        if (j.contains("required") && !j["required"].is_null()) {
            obj.required = j["required"].get<bool>();
        }
        if (j.contains("array") && !j["array"].is_null()) {
            obj.array = j["array"].get<std::optional<bool>>();
        }
        if (j.contains("$createdAt") && !j["$createdAt"].is_null()) {
            obj.createdAt = j["$createdAt"].get<std::string>();
        }
        if (j.contains("$updatedAt") && !j["$updatedAt"].is_null()) {
            obj.updatedAt = j["$updatedAt"].get<std::string>();
        }
        if (j.contains("elements") && !j["elements"].is_null()) {
            obj.elements = j["elements"].get<std::vector<std::string>>();
        }
        if (j.contains("format") && !j["format"].is_null()) {
            obj.format = j["format"].get<std::string>();
        }
        if (j.contains("default") && !j["default"].is_null()) {
            obj.default_ = j["default"].get<std::optional<std::string>>();
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
        if (j.contains("key") && !j["key"].is_null()) {
            obj.key = j["key"].get<std::string>();
        }
        if (j.contains("type") && !j["type"].is_null()) {
            obj.type = j["type"].get<std::string>();
        }
        if (j.contains("status") && !j["status"].is_null()) {
            obj.status = j["status"].get<appwrite::enums::ColumnStatus>();
        }
        if (j.contains("error") && !j["error"].is_null()) {
            obj.error = j["error"].get<std::string>();
        }
        if (j.contains("required") && !j["required"].is_null()) {
            obj.required = j["required"].get<bool>();
        }
        if (j.contains("array") && !j["array"].is_null()) {
            obj.array = j["array"].get<std::optional<bool>>();
        }
        if (j.contains("$createdAt") && !j["$createdAt"].is_null()) {
            obj.createdAt = j["$createdAt"].get<std::string>();
        }
        if (j.contains("$updatedAt") && !j["$updatedAt"].is_null()) {
            obj.updatedAt = j["$updatedAt"].get<std::string>();
        }
        if (j.contains("format") && !j["format"].is_null()) {
            obj.format = j["format"].get<std::string>();
        }
        if (j.contains("default") && !j["default"].is_null()) {
            obj.default_ = j["default"].get<std::optional<std::string>>();
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
        if (j.contains("key") && !j["key"].is_null()) {
            obj.key = j["key"].get<std::string>();
        }
        if (j.contains("type") && !j["type"].is_null()) {
            obj.type = j["type"].get<std::string>();
        }
        if (j.contains("status") && !j["status"].is_null()) {
            obj.status = j["status"].get<appwrite::enums::ColumnStatus>();
        }
        if (j.contains("error") && !j["error"].is_null()) {
            obj.error = j["error"].get<std::string>();
        }
        if (j.contains("required") && !j["required"].is_null()) {
            obj.required = j["required"].get<bool>();
        }
        if (j.contains("array") && !j["array"].is_null()) {
            obj.array = j["array"].get<std::optional<bool>>();
        }
        if (j.contains("$createdAt") && !j["$createdAt"].is_null()) {
            obj.createdAt = j["$createdAt"].get<std::string>();
        }
        if (j.contains("$updatedAt") && !j["$updatedAt"].is_null()) {
            obj.updatedAt = j["$updatedAt"].get<std::string>();
        }
        if (j.contains("format") && !j["format"].is_null()) {
            obj.format = j["format"].get<std::string>();
        }
        if (j.contains("default") && !j["default"].is_null()) {
            obj.default_ = j["default"].get<std::optional<std::string>>();
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
        if (j.contains("key") && !j["key"].is_null()) {
            obj.key = j["key"].get<std::string>();
        }
        if (j.contains("type") && !j["type"].is_null()) {
            obj.type = j["type"].get<std::string>();
        }
        if (j.contains("status") && !j["status"].is_null()) {
            obj.status = j["status"].get<appwrite::enums::ColumnStatus>();
        }
        if (j.contains("error") && !j["error"].is_null()) {
            obj.error = j["error"].get<std::string>();
        }
        if (j.contains("required") && !j["required"].is_null()) {
            obj.required = j["required"].get<bool>();
        }
        if (j.contains("array") && !j["array"].is_null()) {
            obj.array = j["array"].get<std::optional<bool>>();
        }
        if (j.contains("$createdAt") && !j["$createdAt"].is_null()) {
            obj.createdAt = j["$createdAt"].get<std::string>();
        }
        if (j.contains("$updatedAt") && !j["$updatedAt"].is_null()) {
            obj.updatedAt = j["$updatedAt"].get<std::string>();
        }
        if (j.contains("format") && !j["format"].is_null()) {
            obj.format = j["format"].get<std::string>();
        }
        if (j.contains("default") && !j["default"].is_null()) {
            obj.default_ = j["default"].get<std::optional<std::string>>();
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
        if (j.contains("key") && !j["key"].is_null()) {
            obj.key = j["key"].get<std::string>();
        }
        if (j.contains("type") && !j["type"].is_null()) {
            obj.type = j["type"].get<std::string>();
        }
        if (j.contains("status") && !j["status"].is_null()) {
            obj.status = j["status"].get<appwrite::enums::ColumnStatus>();
        }
        if (j.contains("error") && !j["error"].is_null()) {
            obj.error = j["error"].get<std::string>();
        }
        if (j.contains("required") && !j["required"].is_null()) {
            obj.required = j["required"].get<bool>();
        }
        if (j.contains("array") && !j["array"].is_null()) {
            obj.array = j["array"].get<std::optional<bool>>();
        }
        if (j.contains("$createdAt") && !j["$createdAt"].is_null()) {
            obj.createdAt = j["$createdAt"].get<std::string>();
        }
        if (j.contains("$updatedAt") && !j["$updatedAt"].is_null()) {
            obj.updatedAt = j["$updatedAt"].get<std::string>();
        }
        if (j.contains("relatedTable") && !j["relatedTable"].is_null()) {
            obj.relatedTable = j["relatedTable"].get<std::string>();
        }
        if (j.contains("relationType") && !j["relationType"].is_null()) {
            obj.relationType = j["relationType"].get<std::string>();
        }
        if (j.contains("twoWay") && !j["twoWay"].is_null()) {
            obj.twoWay = j["twoWay"].get<bool>();
        }
        if (j.contains("twoWayKey") && !j["twoWayKey"].is_null()) {
            obj.twoWayKey = j["twoWayKey"].get<std::string>();
        }
        if (j.contains("onDelete") && !j["onDelete"].is_null()) {
            obj.onDelete = j["onDelete"].get<std::string>();
        }
        if (j.contains("side") && !j["side"].is_null()) {
            obj.side = j["side"].get<std::string>();
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
        if (j.contains("key") && !j["key"].is_null()) {
            obj.key = j["key"].get<std::string>();
        }
        if (j.contains("type") && !j["type"].is_null()) {
            obj.type = j["type"].get<std::string>();
        }
        if (j.contains("status") && !j["status"].is_null()) {
            obj.status = j["status"].get<appwrite::enums::ColumnStatus>();
        }
        if (j.contains("error") && !j["error"].is_null()) {
            obj.error = j["error"].get<std::string>();
        }
        if (j.contains("required") && !j["required"].is_null()) {
            obj.required = j["required"].get<bool>();
        }
        if (j.contains("array") && !j["array"].is_null()) {
            obj.array = j["array"].get<std::optional<bool>>();
        }
        if (j.contains("$createdAt") && !j["$createdAt"].is_null()) {
            obj.createdAt = j["$createdAt"].get<std::string>();
        }
        if (j.contains("$updatedAt") && !j["$updatedAt"].is_null()) {
            obj.updatedAt = j["$updatedAt"].get<std::string>();
        }
        if (j.contains("default") && !j["default"].is_null()) {
            obj.default_ = j["default"].get<std::optional<std::vector<std::string>>>();
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
        if (j.contains("key") && !j["key"].is_null()) {
            obj.key = j["key"].get<std::string>();
        }
        if (j.contains("type") && !j["type"].is_null()) {
            obj.type = j["type"].get<std::string>();
        }
        if (j.contains("status") && !j["status"].is_null()) {
            obj.status = j["status"].get<appwrite::enums::ColumnStatus>();
        }
        if (j.contains("error") && !j["error"].is_null()) {
            obj.error = j["error"].get<std::string>();
        }
        if (j.contains("required") && !j["required"].is_null()) {
            obj.required = j["required"].get<bool>();
        }
        if (j.contains("array") && !j["array"].is_null()) {
            obj.array = j["array"].get<std::optional<bool>>();
        }
        if (j.contains("$createdAt") && !j["$createdAt"].is_null()) {
            obj.createdAt = j["$createdAt"].get<std::string>();
        }
        if (j.contains("$updatedAt") && !j["$updatedAt"].is_null()) {
            obj.updatedAt = j["$updatedAt"].get<std::string>();
        }
        if (j.contains("default") && !j["default"].is_null()) {
            obj.default_ = j["default"].get<std::optional<std::vector<std::string>>>();
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
        if (j.contains("key") && !j["key"].is_null()) {
            obj.key = j["key"].get<std::string>();
        }
        if (j.contains("type") && !j["type"].is_null()) {
            obj.type = j["type"].get<std::string>();
        }
        if (j.contains("status") && !j["status"].is_null()) {
            obj.status = j["status"].get<appwrite::enums::ColumnStatus>();
        }
        if (j.contains("error") && !j["error"].is_null()) {
            obj.error = j["error"].get<std::string>();
        }
        if (j.contains("required") && !j["required"].is_null()) {
            obj.required = j["required"].get<bool>();
        }
        if (j.contains("array") && !j["array"].is_null()) {
            obj.array = j["array"].get<std::optional<bool>>();
        }
        if (j.contains("$createdAt") && !j["$createdAt"].is_null()) {
            obj.createdAt = j["$createdAt"].get<std::string>();
        }
        if (j.contains("$updatedAt") && !j["$updatedAt"].is_null()) {
            obj.updatedAt = j["$updatedAt"].get<std::string>();
        }
        if (j.contains("default") && !j["default"].is_null()) {
            obj.default_ = j["default"].get<std::optional<std::vector<std::string>>>();
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
        if (j.contains("key") && !j["key"].is_null()) {
            obj.key = j["key"].get<std::string>();
        }
        if (j.contains("type") && !j["type"].is_null()) {
            obj.type = j["type"].get<std::string>();
        }
        if (j.contains("status") && !j["status"].is_null()) {
            obj.status = j["status"].get<appwrite::enums::ColumnStatus>();
        }
        if (j.contains("error") && !j["error"].is_null()) {
            obj.error = j["error"].get<std::string>();
        }
        if (j.contains("required") && !j["required"].is_null()) {
            obj.required = j["required"].get<bool>();
        }
        if (j.contains("array") && !j["array"].is_null()) {
            obj.array = j["array"].get<std::optional<bool>>();
        }
        if (j.contains("$createdAt") && !j["$createdAt"].is_null()) {
            obj.createdAt = j["$createdAt"].get<std::string>();
        }
        if (j.contains("$updatedAt") && !j["$updatedAt"].is_null()) {
            obj.updatedAt = j["$updatedAt"].get<std::string>();
        }
        if (j.contains("size") && !j["size"].is_null()) {
            obj.size = j["size"].get<int64_t>();
        }
        if (j.contains("default") && !j["default"].is_null()) {
            obj.default_ = j["default"].get<std::optional<std::string>>();
        }
        if (j.contains("encrypt") && !j["encrypt"].is_null()) {
            obj.encrypt = j["encrypt"].get<std::optional<bool>>();
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
        if (j.contains("key") && !j["key"].is_null()) {
            obj.key = j["key"].get<std::string>();
        }
        if (j.contains("type") && !j["type"].is_null()) {
            obj.type = j["type"].get<std::string>();
        }
        if (j.contains("status") && !j["status"].is_null()) {
            obj.status = j["status"].get<appwrite::enums::ColumnStatus>();
        }
        if (j.contains("error") && !j["error"].is_null()) {
            obj.error = j["error"].get<std::string>();
        }
        if (j.contains("required") && !j["required"].is_null()) {
            obj.required = j["required"].get<bool>();
        }
        if (j.contains("array") && !j["array"].is_null()) {
            obj.array = j["array"].get<std::optional<bool>>();
        }
        if (j.contains("$createdAt") && !j["$createdAt"].is_null()) {
            obj.createdAt = j["$createdAt"].get<std::string>();
        }
        if (j.contains("$updatedAt") && !j["$updatedAt"].is_null()) {
            obj.updatedAt = j["$updatedAt"].get<std::string>();
        }
        if (j.contains("default") && !j["default"].is_null()) {
            obj.default_ = j["default"].get<std::optional<std::string>>();
        }
        if (j.contains("encrypt") && !j["encrypt"].is_null()) {
            obj.encrypt = j["encrypt"].get<std::optional<bool>>();
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
        if (j.contains("key") && !j["key"].is_null()) {
            obj.key = j["key"].get<std::string>();
        }
        if (j.contains("type") && !j["type"].is_null()) {
            obj.type = j["type"].get<std::string>();
        }
        if (j.contains("status") && !j["status"].is_null()) {
            obj.status = j["status"].get<appwrite::enums::ColumnStatus>();
        }
        if (j.contains("error") && !j["error"].is_null()) {
            obj.error = j["error"].get<std::string>();
        }
        if (j.contains("required") && !j["required"].is_null()) {
            obj.required = j["required"].get<bool>();
        }
        if (j.contains("array") && !j["array"].is_null()) {
            obj.array = j["array"].get<std::optional<bool>>();
        }
        if (j.contains("$createdAt") && !j["$createdAt"].is_null()) {
            obj.createdAt = j["$createdAt"].get<std::string>();
        }
        if (j.contains("$updatedAt") && !j["$updatedAt"].is_null()) {
            obj.updatedAt = j["$updatedAt"].get<std::string>();
        }
        if (j.contains("default") && !j["default"].is_null()) {
            obj.default_ = j["default"].get<std::optional<std::string>>();
        }
        if (j.contains("encrypt") && !j["encrypt"].is_null()) {
            obj.encrypt = j["encrypt"].get<std::optional<bool>>();
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
        if (j.contains("key") && !j["key"].is_null()) {
            obj.key = j["key"].get<std::string>();
        }
        if (j.contains("type") && !j["type"].is_null()) {
            obj.type = j["type"].get<std::string>();
        }
        if (j.contains("status") && !j["status"].is_null()) {
            obj.status = j["status"].get<appwrite::enums::ColumnStatus>();
        }
        if (j.contains("error") && !j["error"].is_null()) {
            obj.error = j["error"].get<std::string>();
        }
        if (j.contains("required") && !j["required"].is_null()) {
            obj.required = j["required"].get<bool>();
        }
        if (j.contains("array") && !j["array"].is_null()) {
            obj.array = j["array"].get<std::optional<bool>>();
        }
        if (j.contains("$createdAt") && !j["$createdAt"].is_null()) {
            obj.createdAt = j["$createdAt"].get<std::string>();
        }
        if (j.contains("$updatedAt") && !j["$updatedAt"].is_null()) {
            obj.updatedAt = j["$updatedAt"].get<std::string>();
        }
        if (j.contains("default") && !j["default"].is_null()) {
            obj.default_ = j["default"].get<std::optional<std::string>>();
        }
        if (j.contains("encrypt") && !j["encrypt"].is_null()) {
            obj.encrypt = j["encrypt"].get<std::optional<bool>>();
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
        if (j.contains("type") && !j["type"].is_null()) {
            obj.type = j["type"].get<std::string>();
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
        if (j.contains("type") && !j["type"].is_null()) {
            obj.type = j["type"].get<std::string>();
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
        if (j.contains("type") && !j["type"].is_null()) {
            obj.type = j["type"].get<std::string>();
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
        if (j.contains("type") && !j["type"].is_null()) {
            obj.type = j["type"].get<std::string>();
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
        if (j.contains("type") && !j["type"].is_null()) {
            obj.type = j["type"].get<std::string>();
        }
        if (j.contains("costCpu") && !j["costCpu"].is_null()) {
            obj.costCpu = j["costCpu"].get<int64_t>();
        }
        if (j.contains("costMemory") && !j["costMemory"].is_null()) {
            obj.costMemory = j["costMemory"].get<int64_t>();
        }
        if (j.contains("costParallel") && !j["costParallel"].is_null()) {
            obj.costParallel = j["costParallel"].get<int64_t>();
        }
        if (j.contains("length") && !j["length"].is_null()) {
            obj.length = j["length"].get<int64_t>();
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
        if (j.contains("type") && !j["type"].is_null()) {
            obj.type = j["type"].get<std::string>();
        }
        if (j.contains("salt") && !j["salt"].is_null()) {
            obj.salt = j["salt"].get<std::string>();
        }
        if (j.contains("saltSeparator") && !j["saltSeparator"].is_null()) {
            obj.saltSeparator = j["saltSeparator"].get<std::string>();
        }
        if (j.contains("signerKey") && !j["signerKey"].is_null()) {
            obj.signerKey = j["signerKey"].get<std::string>();
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
        if (j.contains("type") && !j["type"].is_null()) {
            obj.type = j["type"].get<std::string>();
        }
        if (j.contains("memoryCost") && !j["memoryCost"].is_null()) {
            obj.memoryCost = j["memoryCost"].get<int64_t>();
        }
        if (j.contains("timeCost") && !j["timeCost"].is_null()) {
            obj.timeCost = j["timeCost"].get<int64_t>();
        }
        if (j.contains("threads") && !j["threads"].is_null()) {
            obj.threads = j["threads"].get<int64_t>();
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
        if (j.contains("$id") && !j["$id"].is_null()) {
            obj.id = j["$id"].get<std::string>();
        }
        if (j.contains("$createdAt") && !j["$createdAt"].is_null()) {
            obj.createdAt = j["$createdAt"].get<std::string>();
        }
        if (j.contains("userId") && !j["userId"].is_null()) {
            obj.userId = j["userId"].get<std::string>();
        }
        if (j.contains("secret") && !j["secret"].is_null()) {
            obj.secret = j["secret"].get<std::string>();
        }
        if (j.contains("expire") && !j["expire"].is_null()) {
            obj.expire = j["expire"].get<std::string>();
        }
        if (j.contains("phrase") && !j["phrase"].is_null()) {
            obj.phrase = j["phrase"].get<std::string>();
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
        if (j.contains("jwt") && !j["jwt"].is_null()) {
            obj.jwt = j["jwt"].get<std::string>();
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
 * @brief Metric
 */
struct Metric {
    /** @brief The value of this metric at the timestamp. */
    int64_t value;
    /** @brief The date at which this metric was aggregated in ISO 8601 format. */
    std::string date;

    /** @brief Deserialize from JSON */
    static Metric fromJson(const nlohmann::json& j) {
        Metric obj{}; // value-init: zeroes bools, ints, enums
        if (j.contains("value") && !j["value"].is_null()) {
            obj.value = j["value"].get<int64_t>();
        }
        if (j.contains("date") && !j["date"].is_null()) {
            obj.date = j["date"].get<std::string>();
        }
        return obj;
    }

    /** @brief Serialize to JSON */
    [[nodiscard]] nlohmann::json toJson() const {
        nlohmann::json j = nlohmann::json::object();
        {
            j["value"] = this->value;
        }
        {
            j["date"] = this->date;
        }
        return j;
    }
};

/** @brief ADL hook for nlohmann::json */
inline void from_json(const nlohmann::json& j, Metric& x) {
    x = Metric::fromJson(j);
}

inline void to_json(nlohmann::json& j, const Metric& x) {
    j = x.toJson();
}

/**
 * @brief UsageDatabases
 */
struct UsageDatabases {
    /** @brief Time range of the usage stats. */
    std::string range;
    /** @brief Total aggregated number of databases. */
    int64_t databasesTotal;
    /** @brief Total aggregated number  of collections. */
    int64_t collectionsTotal;
    /** @brief Total aggregated number  of tables. */
    int64_t tablesTotal;
    /** @brief Total aggregated number of documents. */
    int64_t documentsTotal;
    /** @brief Total aggregated number of rows. */
    int64_t rowsTotal;
    /** @brief Total aggregated number of total databases storage in bytes. */
    int64_t storageTotal;
    /** @brief Total number of databases reads. */
    int64_t databasesReadsTotal;
    /** @brief Total number of databases writes. */
    int64_t databasesWritesTotal;
    /** @brief Aggregated number of databases per period. */
    std::vector<appwrite::models::Metric> databases;
    /** @brief Aggregated number of collections per period. */
    std::vector<appwrite::models::Metric> collections;
    /** @brief Aggregated number of tables per period. */
    std::vector<appwrite::models::Metric> tables;
    /** @brief Aggregated number of documents per period. */
    std::vector<appwrite::models::Metric> documents;
    /** @brief Aggregated number of rows per period. */
    std::vector<appwrite::models::Metric> rows;
    /** @brief An array of the aggregated number of databases storage in bytes per period. */
    std::vector<appwrite::models::Metric> storage;
    /** @brief An array of aggregated number of database reads. */
    std::vector<appwrite::models::Metric> databasesReads;
    /** @brief An array of aggregated number of database writes. */
    std::vector<appwrite::models::Metric> databasesWrites;

    /** @brief Deserialize from JSON */
    static UsageDatabases fromJson(const nlohmann::json& j) {
        UsageDatabases obj{}; // value-init: zeroes bools, ints, enums
        if (j.contains("range") && !j["range"].is_null()) {
            obj.range = j["range"].get<std::string>();
        }
        if (j.contains("databasesTotal") && !j["databasesTotal"].is_null()) {
            obj.databasesTotal = j["databasesTotal"].get<int64_t>();
        }
        if (j.contains("collectionsTotal") && !j["collectionsTotal"].is_null()) {
            obj.collectionsTotal = j["collectionsTotal"].get<int64_t>();
        }
        if (j.contains("tablesTotal") && !j["tablesTotal"].is_null()) {
            obj.tablesTotal = j["tablesTotal"].get<int64_t>();
        }
        if (j.contains("documentsTotal") && !j["documentsTotal"].is_null()) {
            obj.documentsTotal = j["documentsTotal"].get<int64_t>();
        }
        if (j.contains("rowsTotal") && !j["rowsTotal"].is_null()) {
            obj.rowsTotal = j["rowsTotal"].get<int64_t>();
        }
        if (j.contains("storageTotal") && !j["storageTotal"].is_null()) {
            obj.storageTotal = j["storageTotal"].get<int64_t>();
        }
        if (j.contains("databasesReadsTotal") && !j["databasesReadsTotal"].is_null()) {
            obj.databasesReadsTotal = j["databasesReadsTotal"].get<int64_t>();
        }
        if (j.contains("databasesWritesTotal") && !j["databasesWritesTotal"].is_null()) {
            obj.databasesWritesTotal = j["databasesWritesTotal"].get<int64_t>();
        }
        if (j.contains("databases") && !j["databases"].is_null()) {
                                        for (auto& item : j["databases"]) {
                                obj.databases.push_back(Metric::fromJson(item));
                            }
                    }
        if (j.contains("collections") && !j["collections"].is_null()) {
                                        for (auto& item : j["collections"]) {
                                obj.collections.push_back(Metric::fromJson(item));
                            }
                    }
        if (j.contains("tables") && !j["tables"].is_null()) {
                                        for (auto& item : j["tables"]) {
                                obj.tables.push_back(Metric::fromJson(item));
                            }
                    }
        if (j.contains("documents") && !j["documents"].is_null()) {
                                        for (auto& item : j["documents"]) {
                                obj.documents.push_back(Metric::fromJson(item));
                            }
                    }
        if (j.contains("rows") && !j["rows"].is_null()) {
                                        for (auto& item : j["rows"]) {
                                obj.rows.push_back(Metric::fromJson(item));
                            }
                    }
        if (j.contains("storage") && !j["storage"].is_null()) {
                                        for (auto& item : j["storage"]) {
                                obj.storage.push_back(Metric::fromJson(item));
                            }
                    }
        if (j.contains("databasesReads") && !j["databasesReads"].is_null()) {
                                        for (auto& item : j["databasesReads"]) {
                                obj.databasesReads.push_back(Metric::fromJson(item));
                            }
                    }
        if (j.contains("databasesWrites") && !j["databasesWrites"].is_null()) {
                                        for (auto& item : j["databasesWrites"]) {
                                obj.databasesWrites.push_back(Metric::fromJson(item));
                            }
                    }
        return obj;
    }

    /** @brief Serialize to JSON */
    [[nodiscard]] nlohmann::json toJson() const {
        nlohmann::json j = nlohmann::json::object();
        {
            j["range"] = this->range;
        }
        {
            j["databasesTotal"] = this->databasesTotal;
        }
        {
            j["collectionsTotal"] = this->collectionsTotal;
        }
        {
            j["tablesTotal"] = this->tablesTotal;
        }
        {
            j["documentsTotal"] = this->documentsTotal;
        }
        {
            j["rowsTotal"] = this->rowsTotal;
        }
        {
            j["storageTotal"] = this->storageTotal;
        }
        {
            j["databasesReadsTotal"] = this->databasesReadsTotal;
        }
        {
            j["databasesWritesTotal"] = this->databasesWritesTotal;
        }
        {
                nlohmann::json arr = nlohmann::json::array();
            for (auto& item : this->databases) arr.push_back(item.toJson());
            j["databases"] = arr;
            }
        {
                nlohmann::json arr = nlohmann::json::array();
            for (auto& item : this->collections) arr.push_back(item.toJson());
            j["collections"] = arr;
            }
        {
                nlohmann::json arr = nlohmann::json::array();
            for (auto& item : this->tables) arr.push_back(item.toJson());
            j["tables"] = arr;
            }
        {
                nlohmann::json arr = nlohmann::json::array();
            for (auto& item : this->documents) arr.push_back(item.toJson());
            j["documents"] = arr;
            }
        {
                nlohmann::json arr = nlohmann::json::array();
            for (auto& item : this->rows) arr.push_back(item.toJson());
            j["rows"] = arr;
            }
        {
                nlohmann::json arr = nlohmann::json::array();
            for (auto& item : this->storage) arr.push_back(item.toJson());
            j["storage"] = arr;
            }
        {
                nlohmann::json arr = nlohmann::json::array();
            for (auto& item : this->databasesReads) arr.push_back(item.toJson());
            j["databasesReads"] = arr;
            }
        {
                nlohmann::json arr = nlohmann::json::array();
            for (auto& item : this->databasesWrites) arr.push_back(item.toJson());
            j["databasesWrites"] = arr;
            }
        return j;
    }
};

/** @brief ADL hook for nlohmann::json */
inline void from_json(const nlohmann::json& j, UsageDatabases& x) {
    x = UsageDatabases::fromJson(j);
}

inline void to_json(nlohmann::json& j, const UsageDatabases& x) {
    j = x.toJson();
}

/**
 * @brief UsageDatabase
 */
struct UsageDatabase {
    /** @brief Time range of the usage stats. */
    std::string range;
    /** @brief Total aggregated number of collections. */
    int64_t collectionsTotal;
    /** @brief Total aggregated number of tables. */
    int64_t tablesTotal;
    /** @brief Total aggregated number of documents. */
    int64_t documentsTotal;
    /** @brief Total aggregated number of rows. */
    int64_t rowsTotal;
    /** @brief Total aggregated number of total storage used in bytes. */
    int64_t storageTotal;
    /** @brief Total number of databases reads. */
    int64_t databaseReadsTotal;
    /** @brief Total number of databases writes. */
    int64_t databaseWritesTotal;
    /** @brief Aggregated  number of collections per period. */
    std::vector<appwrite::models::Metric> collections;
    /** @brief Aggregated  number of tables per period. */
    std::vector<appwrite::models::Metric> tables;
    /** @brief Aggregated  number of documents per period. */
    std::vector<appwrite::models::Metric> documents;
    /** @brief Aggregated  number of rows per period. */
    std::vector<appwrite::models::Metric> rows;
    /** @brief Aggregated storage used in bytes per period. */
    std::vector<appwrite::models::Metric> storage;
    /** @brief An array of aggregated number of database reads. */
    std::vector<appwrite::models::Metric> databaseReads;
    /** @brief An array of aggregated number of database writes. */
    std::vector<appwrite::models::Metric> databaseWrites;

    /** @brief Deserialize from JSON */
    static UsageDatabase fromJson(const nlohmann::json& j) {
        UsageDatabase obj{}; // value-init: zeroes bools, ints, enums
        if (j.contains("range") && !j["range"].is_null()) {
            obj.range = j["range"].get<std::string>();
        }
        if (j.contains("collectionsTotal") && !j["collectionsTotal"].is_null()) {
            obj.collectionsTotal = j["collectionsTotal"].get<int64_t>();
        }
        if (j.contains("tablesTotal") && !j["tablesTotal"].is_null()) {
            obj.tablesTotal = j["tablesTotal"].get<int64_t>();
        }
        if (j.contains("documentsTotal") && !j["documentsTotal"].is_null()) {
            obj.documentsTotal = j["documentsTotal"].get<int64_t>();
        }
        if (j.contains("rowsTotal") && !j["rowsTotal"].is_null()) {
            obj.rowsTotal = j["rowsTotal"].get<int64_t>();
        }
        if (j.contains("storageTotal") && !j["storageTotal"].is_null()) {
            obj.storageTotal = j["storageTotal"].get<int64_t>();
        }
        if (j.contains("databaseReadsTotal") && !j["databaseReadsTotal"].is_null()) {
            obj.databaseReadsTotal = j["databaseReadsTotal"].get<int64_t>();
        }
        if (j.contains("databaseWritesTotal") && !j["databaseWritesTotal"].is_null()) {
            obj.databaseWritesTotal = j["databaseWritesTotal"].get<int64_t>();
        }
        if (j.contains("collections") && !j["collections"].is_null()) {
                                        for (auto& item : j["collections"]) {
                                obj.collections.push_back(Metric::fromJson(item));
                            }
                    }
        if (j.contains("tables") && !j["tables"].is_null()) {
                                        for (auto& item : j["tables"]) {
                                obj.tables.push_back(Metric::fromJson(item));
                            }
                    }
        if (j.contains("documents") && !j["documents"].is_null()) {
                                        for (auto& item : j["documents"]) {
                                obj.documents.push_back(Metric::fromJson(item));
                            }
                    }
        if (j.contains("rows") && !j["rows"].is_null()) {
                                        for (auto& item : j["rows"]) {
                                obj.rows.push_back(Metric::fromJson(item));
                            }
                    }
        if (j.contains("storage") && !j["storage"].is_null()) {
                                        for (auto& item : j["storage"]) {
                                obj.storage.push_back(Metric::fromJson(item));
                            }
                    }
        if (j.contains("databaseReads") && !j["databaseReads"].is_null()) {
                                        for (auto& item : j["databaseReads"]) {
                                obj.databaseReads.push_back(Metric::fromJson(item));
                            }
                    }
        if (j.contains("databaseWrites") && !j["databaseWrites"].is_null()) {
                                        for (auto& item : j["databaseWrites"]) {
                                obj.databaseWrites.push_back(Metric::fromJson(item));
                            }
                    }
        return obj;
    }

    /** @brief Serialize to JSON */
    [[nodiscard]] nlohmann::json toJson() const {
        nlohmann::json j = nlohmann::json::object();
        {
            j["range"] = this->range;
        }
        {
            j["collectionsTotal"] = this->collectionsTotal;
        }
        {
            j["tablesTotal"] = this->tablesTotal;
        }
        {
            j["documentsTotal"] = this->documentsTotal;
        }
        {
            j["rowsTotal"] = this->rowsTotal;
        }
        {
            j["storageTotal"] = this->storageTotal;
        }
        {
            j["databaseReadsTotal"] = this->databaseReadsTotal;
        }
        {
            j["databaseWritesTotal"] = this->databaseWritesTotal;
        }
        {
                nlohmann::json arr = nlohmann::json::array();
            for (auto& item : this->collections) arr.push_back(item.toJson());
            j["collections"] = arr;
            }
        {
                nlohmann::json arr = nlohmann::json::array();
            for (auto& item : this->tables) arr.push_back(item.toJson());
            j["tables"] = arr;
            }
        {
                nlohmann::json arr = nlohmann::json::array();
            for (auto& item : this->documents) arr.push_back(item.toJson());
            j["documents"] = arr;
            }
        {
                nlohmann::json arr = nlohmann::json::array();
            for (auto& item : this->rows) arr.push_back(item.toJson());
            j["rows"] = arr;
            }
        {
                nlohmann::json arr = nlohmann::json::array();
            for (auto& item : this->storage) arr.push_back(item.toJson());
            j["storage"] = arr;
            }
        {
                nlohmann::json arr = nlohmann::json::array();
            for (auto& item : this->databaseReads) arr.push_back(item.toJson());
            j["databaseReads"] = arr;
            }
        {
                nlohmann::json arr = nlohmann::json::array();
            for (auto& item : this->databaseWrites) arr.push_back(item.toJson());
            j["databaseWrites"] = arr;
            }
        return j;
    }
};

/** @brief ADL hook for nlohmann::json */
inline void from_json(const nlohmann::json& j, UsageDatabase& x) {
    x = UsageDatabase::fromJson(j);
}

inline void to_json(nlohmann::json& j, const UsageDatabase& x) {
    j = x.toJson();
}

/**
 * @brief UsageTable
 */
struct UsageTable {
    /** @brief Time range of the usage stats. */
    std::string range;
    /** @brief Total aggregated number of of rows. */
    int64_t rowsTotal;
    /** @brief Aggregated  number of rows per period. */
    std::vector<appwrite::models::Metric> rows;

    /** @brief Deserialize from JSON */
    static UsageTable fromJson(const nlohmann::json& j) {
        UsageTable obj{}; // value-init: zeroes bools, ints, enums
        if (j.contains("range") && !j["range"].is_null()) {
            obj.range = j["range"].get<std::string>();
        }
        if (j.contains("rowsTotal") && !j["rowsTotal"].is_null()) {
            obj.rowsTotal = j["rowsTotal"].get<int64_t>();
        }
        if (j.contains("rows") && !j["rows"].is_null()) {
                                        for (auto& item : j["rows"]) {
                                obj.rows.push_back(Metric::fromJson(item));
                            }
                    }
        return obj;
    }

    /** @brief Serialize to JSON */
    [[nodiscard]] nlohmann::json toJson() const {
        nlohmann::json j = nlohmann::json::object();
        {
            j["range"] = this->range;
        }
        {
            j["rowsTotal"] = this->rowsTotal;
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
inline void from_json(const nlohmann::json& j, UsageTable& x) {
    x = UsageTable::fromJson(j);
}

inline void to_json(nlohmann::json& j, const UsageTable& x) {
    j = x.toJson();
}

/**
 * @brief UsageCollection
 */
struct UsageCollection {
    /** @brief Time range of the usage stats. */
    std::string range;
    /** @brief Total aggregated number of of documents. */
    int64_t documentsTotal;
    /** @brief Aggregated  number of documents per period. */
    std::vector<appwrite::models::Metric> documents;

    /** @brief Deserialize from JSON */
    static UsageCollection fromJson(const nlohmann::json& j) {
        UsageCollection obj{}; // value-init: zeroes bools, ints, enums
        if (j.contains("range") && !j["range"].is_null()) {
            obj.range = j["range"].get<std::string>();
        }
        if (j.contains("documentsTotal") && !j["documentsTotal"].is_null()) {
            obj.documentsTotal = j["documentsTotal"].get<int64_t>();
        }
        if (j.contains("documents") && !j["documents"].is_null()) {
                                        for (auto& item : j["documents"]) {
                                obj.documents.push_back(Metric::fromJson(item));
                            }
                    }
        return obj;
    }

    /** @brief Serialize to JSON */
    [[nodiscard]] nlohmann::json toJson() const {
        nlohmann::json j = nlohmann::json::object();
        {
            j["range"] = this->range;
        }
        {
            j["documentsTotal"] = this->documentsTotal;
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
inline void from_json(const nlohmann::json& j, UsageCollection& x) {
    x = UsageCollection::fromJson(j);
}

inline void to_json(nlohmann::json& j, const UsageCollection& x) {
    j = x.toJson();
}

/**
 * @brief UsageUsers
 */
struct UsageUsers {
    /** @brief Time range of the usage stats. */
    std::string range;
    /** @brief Total aggregated number of statistics of users. */
    int64_t usersTotal;
    /** @brief Total aggregated number of active sessions. */
    int64_t sessionsTotal;
    /** @brief Aggregated number of users per period. */
    std::vector<appwrite::models::Metric> users;
    /** @brief Aggregated number of active sessions  per period. */
    std::vector<appwrite::models::Metric> sessions;

    /** @brief Deserialize from JSON */
    static UsageUsers fromJson(const nlohmann::json& j) {
        UsageUsers obj{}; // value-init: zeroes bools, ints, enums
        if (j.contains("range") && !j["range"].is_null()) {
            obj.range = j["range"].get<std::string>();
        }
        if (j.contains("usersTotal") && !j["usersTotal"].is_null()) {
            obj.usersTotal = j["usersTotal"].get<int64_t>();
        }
        if (j.contains("sessionsTotal") && !j["sessionsTotal"].is_null()) {
            obj.sessionsTotal = j["sessionsTotal"].get<int64_t>();
        }
        if (j.contains("users") && !j["users"].is_null()) {
                                        for (auto& item : j["users"]) {
                                obj.users.push_back(Metric::fromJson(item));
                            }
                    }
        if (j.contains("sessions") && !j["sessions"].is_null()) {
                                        for (auto& item : j["sessions"]) {
                                obj.sessions.push_back(Metric::fromJson(item));
                            }
                    }
        return obj;
    }

    /** @brief Serialize to JSON */
    [[nodiscard]] nlohmann::json toJson() const {
        nlohmann::json j = nlohmann::json::object();
        {
            j["range"] = this->range;
        }
        {
            j["usersTotal"] = this->usersTotal;
        }
        {
            j["sessionsTotal"] = this->sessionsTotal;
        }
        {
                nlohmann::json arr = nlohmann::json::array();
            for (auto& item : this->users) arr.push_back(item.toJson());
            j["users"] = arr;
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
inline void from_json(const nlohmann::json& j, UsageUsers& x) {
    x = UsageUsers::fromJson(j);
}

inline void to_json(nlohmann::json& j, const UsageUsers& x) {
    j = x.toJson();
}

/**
 * @brief StorageUsage
 */
struct UsageStorage {
    /** @brief Time range of the usage stats. */
    std::string range;
    /** @brief Total aggregated number of buckets */
    int64_t bucketsTotal;
    /** @brief Total aggregated number of files. */
    int64_t filesTotal;
    /** @brief Total aggregated number of files storage (in bytes). */
    int64_t filesStorageTotal;
    /** @brief Aggregated number of buckets per period. */
    std::vector<appwrite::models::Metric> buckets;
    /** @brief Aggregated number of files per period. */
    std::vector<appwrite::models::Metric> files;
    /** @brief Aggregated number of files storage (in bytes) per period . */
    std::vector<appwrite::models::Metric> storage;

    /** @brief Deserialize from JSON */
    static UsageStorage fromJson(const nlohmann::json& j) {
        UsageStorage obj{}; // value-init: zeroes bools, ints, enums
        if (j.contains("range") && !j["range"].is_null()) {
            obj.range = j["range"].get<std::string>();
        }
        if (j.contains("bucketsTotal") && !j["bucketsTotal"].is_null()) {
            obj.bucketsTotal = j["bucketsTotal"].get<int64_t>();
        }
        if (j.contains("filesTotal") && !j["filesTotal"].is_null()) {
            obj.filesTotal = j["filesTotal"].get<int64_t>();
        }
        if (j.contains("filesStorageTotal") && !j["filesStorageTotal"].is_null()) {
            obj.filesStorageTotal = j["filesStorageTotal"].get<int64_t>();
        }
        if (j.contains("buckets") && !j["buckets"].is_null()) {
                                        for (auto& item : j["buckets"]) {
                                obj.buckets.push_back(Metric::fromJson(item));
                            }
                    }
        if (j.contains("files") && !j["files"].is_null()) {
                                        for (auto& item : j["files"]) {
                                obj.files.push_back(Metric::fromJson(item));
                            }
                    }
        if (j.contains("storage") && !j["storage"].is_null()) {
                                        for (auto& item : j["storage"]) {
                                obj.storage.push_back(Metric::fromJson(item));
                            }
                    }
        return obj;
    }

    /** @brief Serialize to JSON */
    [[nodiscard]] nlohmann::json toJson() const {
        nlohmann::json j = nlohmann::json::object();
        {
            j["range"] = this->range;
        }
        {
            j["bucketsTotal"] = this->bucketsTotal;
        }
        {
            j["filesTotal"] = this->filesTotal;
        }
        {
            j["filesStorageTotal"] = this->filesStorageTotal;
        }
        {
                nlohmann::json arr = nlohmann::json::array();
            for (auto& item : this->buckets) arr.push_back(item.toJson());
            j["buckets"] = arr;
            }
        {
                nlohmann::json arr = nlohmann::json::array();
            for (auto& item : this->files) arr.push_back(item.toJson());
            j["files"] = arr;
            }
        {
                nlohmann::json arr = nlohmann::json::array();
            for (auto& item : this->storage) arr.push_back(item.toJson());
            j["storage"] = arr;
            }
        return j;
    }
};

/** @brief ADL hook for nlohmann::json */
inline void from_json(const nlohmann::json& j, UsageStorage& x) {
    x = UsageStorage::fromJson(j);
}

inline void to_json(nlohmann::json& j, const UsageStorage& x) {
    j = x.toJson();
}

/**
 * @brief UsageBuckets
 */
struct UsageBuckets {
    /** @brief Time range of the usage stats. */
    std::string range;
    /** @brief Total aggregated number of bucket files. */
    int64_t filesTotal;
    /** @brief Total aggregated number of bucket files storage (in bytes). */
    int64_t filesStorageTotal;
    /** @brief Aggregated  number of bucket files per period. */
    std::vector<appwrite::models::Metric> files;
    /** @brief Aggregated  number of bucket storage files (in bytes) per period. */
    std::vector<appwrite::models::Metric> storage;
    /** @brief Aggregated number of files transformations per period. */
    std::vector<appwrite::models::Metric> imageTransformations;
    /** @brief Total aggregated number of files transformations. */
    int64_t imageTransformationsTotal;

    /** @brief Deserialize from JSON */
    static UsageBuckets fromJson(const nlohmann::json& j) {
        UsageBuckets obj{}; // value-init: zeroes bools, ints, enums
        if (j.contains("range") && !j["range"].is_null()) {
            obj.range = j["range"].get<std::string>();
        }
        if (j.contains("filesTotal") && !j["filesTotal"].is_null()) {
            obj.filesTotal = j["filesTotal"].get<int64_t>();
        }
        if (j.contains("filesStorageTotal") && !j["filesStorageTotal"].is_null()) {
            obj.filesStorageTotal = j["filesStorageTotal"].get<int64_t>();
        }
        if (j.contains("files") && !j["files"].is_null()) {
                                        for (auto& item : j["files"]) {
                                obj.files.push_back(Metric::fromJson(item));
                            }
                    }
        if (j.contains("storage") && !j["storage"].is_null()) {
                                        for (auto& item : j["storage"]) {
                                obj.storage.push_back(Metric::fromJson(item));
                            }
                    }
        if (j.contains("imageTransformations") && !j["imageTransformations"].is_null()) {
                                        for (auto& item : j["imageTransformations"]) {
                                obj.imageTransformations.push_back(Metric::fromJson(item));
                            }
                    }
        if (j.contains("imageTransformationsTotal") && !j["imageTransformationsTotal"].is_null()) {
            obj.imageTransformationsTotal = j["imageTransformationsTotal"].get<int64_t>();
        }
        return obj;
    }

    /** @brief Serialize to JSON */
    [[nodiscard]] nlohmann::json toJson() const {
        nlohmann::json j = nlohmann::json::object();
        {
            j["range"] = this->range;
        }
        {
            j["filesTotal"] = this->filesTotal;
        }
        {
            j["filesStorageTotal"] = this->filesStorageTotal;
        }
        {
                nlohmann::json arr = nlohmann::json::array();
            for (auto& item : this->files) arr.push_back(item.toJson());
            j["files"] = arr;
            }
        {
                nlohmann::json arr = nlohmann::json::array();
            for (auto& item : this->storage) arr.push_back(item.toJson());
            j["storage"] = arr;
            }
        {
                nlohmann::json arr = nlohmann::json::array();
            for (auto& item : this->imageTransformations) arr.push_back(item.toJson());
            j["imageTransformations"] = arr;
            }
        {
            j["imageTransformationsTotal"] = this->imageTransformationsTotal;
        }
        return j;
    }
};

/** @brief ADL hook for nlohmann::json */
inline void from_json(const nlohmann::json& j, UsageBuckets& x) {
    x = UsageBuckets::fromJson(j);
}

inline void to_json(nlohmann::json& j, const UsageBuckets& x) {
    j = x.toJson();
}

/**
 * @brief UsageFunctions
 */
struct UsageFunctions {
    /** @brief Time range of the usage stats. */
    std::string range;
    /** @brief Total aggregated number of functions. */
    int64_t functionsTotal;
    /** @brief Total aggregated number of functions deployments. */
    int64_t deploymentsTotal;
    /** @brief Total aggregated sum of functions deployment storage. */
    int64_t deploymentsStorageTotal;
    /** @brief Total aggregated number of functions build. */
    int64_t buildsTotal;
    /** @brief total aggregated sum of functions build storage. */
    int64_t buildsStorageTotal;
    /** @brief Total aggregated sum of functions build compute time. */
    int64_t buildsTimeTotal;
    /** @brief Total aggregated sum of functions build mbSeconds. */
    int64_t buildsMbSecondsTotal;
    /** @brief Total  aggregated number of functions execution. */
    int64_t executionsTotal;
    /** @brief Total aggregated sum of functions  execution compute time. */
    int64_t executionsTimeTotal;
    /** @brief Total aggregated sum of functions execution mbSeconds. */
    int64_t executionsMbSecondsTotal;
    /** @brief Aggregated number of functions per period. */
    std::vector<appwrite::models::Metric> functions;
    /** @brief Aggregated number of functions deployment per period. */
    std::vector<appwrite::models::Metric> deployments;
    /** @brief Aggregated number of  functions deployment storage per period. */
    std::vector<appwrite::models::Metric> deploymentsStorage;
    /** @brief Total aggregated number of successful function builds. */
    int64_t buildsSuccessTotal;
    /** @brief Total aggregated number of failed function builds. */
    int64_t buildsFailedTotal;
    /** @brief Aggregated number of functions build per period. */
    std::vector<appwrite::models::Metric> builds;
    /** @brief Aggregated sum of functions build storage per period. */
    std::vector<appwrite::models::Metric> buildsStorage;
    /** @brief Aggregated sum of  functions build compute time per period. */
    std::vector<appwrite::models::Metric> buildsTime;
    /** @brief Aggregated sum of functions build mbSeconds per period. */
    std::vector<appwrite::models::Metric> buildsMbSeconds;
    /** @brief Aggregated number of  functions execution per period. */
    std::vector<appwrite::models::Metric> executions;
    /** @brief Aggregated number of functions execution compute time per period. */
    std::vector<appwrite::models::Metric> executionsTime;
    /** @brief Aggregated number of functions mbSeconds per period. */
    std::vector<appwrite::models::Metric> executionsMbSeconds;
    /** @brief Aggregated number of successful function builds per period. */
    std::vector<appwrite::models::Metric> buildsSuccess;
    /** @brief Aggregated number of failed function builds per period. */
    std::vector<appwrite::models::Metric> buildsFailed;

    /** @brief Deserialize from JSON */
    static UsageFunctions fromJson(const nlohmann::json& j) {
        UsageFunctions obj{}; // value-init: zeroes bools, ints, enums
        if (j.contains("range") && !j["range"].is_null()) {
            obj.range = j["range"].get<std::string>();
        }
        if (j.contains("functionsTotal") && !j["functionsTotal"].is_null()) {
            obj.functionsTotal = j["functionsTotal"].get<int64_t>();
        }
        if (j.contains("deploymentsTotal") && !j["deploymentsTotal"].is_null()) {
            obj.deploymentsTotal = j["deploymentsTotal"].get<int64_t>();
        }
        if (j.contains("deploymentsStorageTotal") && !j["deploymentsStorageTotal"].is_null()) {
            obj.deploymentsStorageTotal = j["deploymentsStorageTotal"].get<int64_t>();
        }
        if (j.contains("buildsTotal") && !j["buildsTotal"].is_null()) {
            obj.buildsTotal = j["buildsTotal"].get<int64_t>();
        }
        if (j.contains("buildsStorageTotal") && !j["buildsStorageTotal"].is_null()) {
            obj.buildsStorageTotal = j["buildsStorageTotal"].get<int64_t>();
        }
        if (j.contains("buildsTimeTotal") && !j["buildsTimeTotal"].is_null()) {
            obj.buildsTimeTotal = j["buildsTimeTotal"].get<int64_t>();
        }
        if (j.contains("buildsMbSecondsTotal") && !j["buildsMbSecondsTotal"].is_null()) {
            obj.buildsMbSecondsTotal = j["buildsMbSecondsTotal"].get<int64_t>();
        }
        if (j.contains("executionsTotal") && !j["executionsTotal"].is_null()) {
            obj.executionsTotal = j["executionsTotal"].get<int64_t>();
        }
        if (j.contains("executionsTimeTotal") && !j["executionsTimeTotal"].is_null()) {
            obj.executionsTimeTotal = j["executionsTimeTotal"].get<int64_t>();
        }
        if (j.contains("executionsMbSecondsTotal") && !j["executionsMbSecondsTotal"].is_null()) {
            obj.executionsMbSecondsTotal = j["executionsMbSecondsTotal"].get<int64_t>();
        }
        if (j.contains("functions") && !j["functions"].is_null()) {
                                        for (auto& item : j["functions"]) {
                                obj.functions.push_back(Metric::fromJson(item));
                            }
                    }
        if (j.contains("deployments") && !j["deployments"].is_null()) {
                                        for (auto& item : j["deployments"]) {
                                obj.deployments.push_back(Metric::fromJson(item));
                            }
                    }
        if (j.contains("deploymentsStorage") && !j["deploymentsStorage"].is_null()) {
                                        for (auto& item : j["deploymentsStorage"]) {
                                obj.deploymentsStorage.push_back(Metric::fromJson(item));
                            }
                    }
        if (j.contains("buildsSuccessTotal") && !j["buildsSuccessTotal"].is_null()) {
            obj.buildsSuccessTotal = j["buildsSuccessTotal"].get<int64_t>();
        }
        if (j.contains("buildsFailedTotal") && !j["buildsFailedTotal"].is_null()) {
            obj.buildsFailedTotal = j["buildsFailedTotal"].get<int64_t>();
        }
        if (j.contains("builds") && !j["builds"].is_null()) {
                                        for (auto& item : j["builds"]) {
                                obj.builds.push_back(Metric::fromJson(item));
                            }
                    }
        if (j.contains("buildsStorage") && !j["buildsStorage"].is_null()) {
                                        for (auto& item : j["buildsStorage"]) {
                                obj.buildsStorage.push_back(Metric::fromJson(item));
                            }
                    }
        if (j.contains("buildsTime") && !j["buildsTime"].is_null()) {
                                        for (auto& item : j["buildsTime"]) {
                                obj.buildsTime.push_back(Metric::fromJson(item));
                            }
                    }
        if (j.contains("buildsMbSeconds") && !j["buildsMbSeconds"].is_null()) {
                                        for (auto& item : j["buildsMbSeconds"]) {
                                obj.buildsMbSeconds.push_back(Metric::fromJson(item));
                            }
                    }
        if (j.contains("executions") && !j["executions"].is_null()) {
                                        for (auto& item : j["executions"]) {
                                obj.executions.push_back(Metric::fromJson(item));
                            }
                    }
        if (j.contains("executionsTime") && !j["executionsTime"].is_null()) {
                                        for (auto& item : j["executionsTime"]) {
                                obj.executionsTime.push_back(Metric::fromJson(item));
                            }
                    }
        if (j.contains("executionsMbSeconds") && !j["executionsMbSeconds"].is_null()) {
                                        for (auto& item : j["executionsMbSeconds"]) {
                                obj.executionsMbSeconds.push_back(Metric::fromJson(item));
                            }
                    }
        if (j.contains("buildsSuccess") && !j["buildsSuccess"].is_null()) {
                                        for (auto& item : j["buildsSuccess"]) {
                                obj.buildsSuccess.push_back(Metric::fromJson(item));
                            }
                    }
        if (j.contains("buildsFailed") && !j["buildsFailed"].is_null()) {
                                        for (auto& item : j["buildsFailed"]) {
                                obj.buildsFailed.push_back(Metric::fromJson(item));
                            }
                    }
        return obj;
    }

    /** @brief Serialize to JSON */
    [[nodiscard]] nlohmann::json toJson() const {
        nlohmann::json j = nlohmann::json::object();
        {
            j["range"] = this->range;
        }
        {
            j["functionsTotal"] = this->functionsTotal;
        }
        {
            j["deploymentsTotal"] = this->deploymentsTotal;
        }
        {
            j["deploymentsStorageTotal"] = this->deploymentsStorageTotal;
        }
        {
            j["buildsTotal"] = this->buildsTotal;
        }
        {
            j["buildsStorageTotal"] = this->buildsStorageTotal;
        }
        {
            j["buildsTimeTotal"] = this->buildsTimeTotal;
        }
        {
            j["buildsMbSecondsTotal"] = this->buildsMbSecondsTotal;
        }
        {
            j["executionsTotal"] = this->executionsTotal;
        }
        {
            j["executionsTimeTotal"] = this->executionsTimeTotal;
        }
        {
            j["executionsMbSecondsTotal"] = this->executionsMbSecondsTotal;
        }
        {
                nlohmann::json arr = nlohmann::json::array();
            for (auto& item : this->functions) arr.push_back(item.toJson());
            j["functions"] = arr;
            }
        {
                nlohmann::json arr = nlohmann::json::array();
            for (auto& item : this->deployments) arr.push_back(item.toJson());
            j["deployments"] = arr;
            }
        {
                nlohmann::json arr = nlohmann::json::array();
            for (auto& item : this->deploymentsStorage) arr.push_back(item.toJson());
            j["deploymentsStorage"] = arr;
            }
        {
            j["buildsSuccessTotal"] = this->buildsSuccessTotal;
        }
        {
            j["buildsFailedTotal"] = this->buildsFailedTotal;
        }
        {
                nlohmann::json arr = nlohmann::json::array();
            for (auto& item : this->builds) arr.push_back(item.toJson());
            j["builds"] = arr;
            }
        {
                nlohmann::json arr = nlohmann::json::array();
            for (auto& item : this->buildsStorage) arr.push_back(item.toJson());
            j["buildsStorage"] = arr;
            }
        {
                nlohmann::json arr = nlohmann::json::array();
            for (auto& item : this->buildsTime) arr.push_back(item.toJson());
            j["buildsTime"] = arr;
            }
        {
                nlohmann::json arr = nlohmann::json::array();
            for (auto& item : this->buildsMbSeconds) arr.push_back(item.toJson());
            j["buildsMbSeconds"] = arr;
            }
        {
                nlohmann::json arr = nlohmann::json::array();
            for (auto& item : this->executions) arr.push_back(item.toJson());
            j["executions"] = arr;
            }
        {
                nlohmann::json arr = nlohmann::json::array();
            for (auto& item : this->executionsTime) arr.push_back(item.toJson());
            j["executionsTime"] = arr;
            }
        {
                nlohmann::json arr = nlohmann::json::array();
            for (auto& item : this->executionsMbSeconds) arr.push_back(item.toJson());
            j["executionsMbSeconds"] = arr;
            }
        {
                nlohmann::json arr = nlohmann::json::array();
            for (auto& item : this->buildsSuccess) arr.push_back(item.toJson());
            j["buildsSuccess"] = arr;
            }
        {
                nlohmann::json arr = nlohmann::json::array();
            for (auto& item : this->buildsFailed) arr.push_back(item.toJson());
            j["buildsFailed"] = arr;
            }
        return j;
    }
};

/** @brief ADL hook for nlohmann::json */
inline void from_json(const nlohmann::json& j, UsageFunctions& x) {
    x = UsageFunctions::fromJson(j);
}

inline void to_json(nlohmann::json& j, const UsageFunctions& x) {
    j = x.toJson();
}

/**
 * @brief UsageFunction
 */
struct UsageFunction {
    /** @brief The time range of the usage stats. */
    std::string range;
    /** @brief Total aggregated number of function deployments. */
    int64_t deploymentsTotal;
    /** @brief Total aggregated sum of function deployments storage. */
    int64_t deploymentsStorageTotal;
    /** @brief Total aggregated number of function builds. */
    int64_t buildsTotal;
    /** @brief Total aggregated number of successful function builds. */
    int64_t buildsSuccessTotal;
    /** @brief Total aggregated number of failed function builds. */
    int64_t buildsFailedTotal;
    /** @brief total aggregated sum of function builds storage. */
    int64_t buildsStorageTotal;
    /** @brief Total aggregated sum of function builds compute time. */
    int64_t buildsTimeTotal;
    /** @brief Average builds compute time. */
    int64_t buildsTimeAverage;
    /** @brief Total aggregated sum of function builds mbSeconds. */
    int64_t buildsMbSecondsTotal;
    /** @brief Total  aggregated number of function executions. */
    int64_t executionsTotal;
    /** @brief Total aggregated sum of function  executions compute time. */
    int64_t executionsTimeTotal;
    /** @brief Total aggregated sum of function executions mbSeconds. */
    int64_t executionsMbSecondsTotal;
    /** @brief Aggregated number of function deployments per period. */
    std::vector<appwrite::models::Metric> deployments;
    /** @brief Aggregated number of  function deployments storage per period. */
    std::vector<appwrite::models::Metric> deploymentsStorage;
    /** @brief Aggregated number of function builds per period. */
    std::vector<appwrite::models::Metric> builds;
    /** @brief Aggregated sum of function builds storage per period. */
    std::vector<appwrite::models::Metric> buildsStorage;
    /** @brief Aggregated sum of function builds compute time per period. */
    std::vector<appwrite::models::Metric> buildsTime;
    /** @brief Aggregated number of function builds mbSeconds per period. */
    std::vector<appwrite::models::Metric> buildsMbSeconds;
    /** @brief Aggregated number of function executions per period. */
    std::vector<appwrite::models::Metric> executions;
    /** @brief Aggregated number of function executions compute time per period. */
    std::vector<appwrite::models::Metric> executionsTime;
    /** @brief Aggregated number of function mbSeconds per period. */
    std::vector<appwrite::models::Metric> executionsMbSeconds;
    /** @brief Aggregated number of successful builds per period. */
    std::vector<appwrite::models::Metric> buildsSuccess;
    /** @brief Aggregated number of failed builds per period. */
    std::vector<appwrite::models::Metric> buildsFailed;

    /** @brief Deserialize from JSON */
    static UsageFunction fromJson(const nlohmann::json& j) {
        UsageFunction obj{}; // value-init: zeroes bools, ints, enums
        if (j.contains("range") && !j["range"].is_null()) {
            obj.range = j["range"].get<std::string>();
        }
        if (j.contains("deploymentsTotal") && !j["deploymentsTotal"].is_null()) {
            obj.deploymentsTotal = j["deploymentsTotal"].get<int64_t>();
        }
        if (j.contains("deploymentsStorageTotal") && !j["deploymentsStorageTotal"].is_null()) {
            obj.deploymentsStorageTotal = j["deploymentsStorageTotal"].get<int64_t>();
        }
        if (j.contains("buildsTotal") && !j["buildsTotal"].is_null()) {
            obj.buildsTotal = j["buildsTotal"].get<int64_t>();
        }
        if (j.contains("buildsSuccessTotal") && !j["buildsSuccessTotal"].is_null()) {
            obj.buildsSuccessTotal = j["buildsSuccessTotal"].get<int64_t>();
        }
        if (j.contains("buildsFailedTotal") && !j["buildsFailedTotal"].is_null()) {
            obj.buildsFailedTotal = j["buildsFailedTotal"].get<int64_t>();
        }
        if (j.contains("buildsStorageTotal") && !j["buildsStorageTotal"].is_null()) {
            obj.buildsStorageTotal = j["buildsStorageTotal"].get<int64_t>();
        }
        if (j.contains("buildsTimeTotal") && !j["buildsTimeTotal"].is_null()) {
            obj.buildsTimeTotal = j["buildsTimeTotal"].get<int64_t>();
        }
        if (j.contains("buildsTimeAverage") && !j["buildsTimeAverage"].is_null()) {
            obj.buildsTimeAverage = j["buildsTimeAverage"].get<int64_t>();
        }
        if (j.contains("buildsMbSecondsTotal") && !j["buildsMbSecondsTotal"].is_null()) {
            obj.buildsMbSecondsTotal = j["buildsMbSecondsTotal"].get<int64_t>();
        }
        if (j.contains("executionsTotal") && !j["executionsTotal"].is_null()) {
            obj.executionsTotal = j["executionsTotal"].get<int64_t>();
        }
        if (j.contains("executionsTimeTotal") && !j["executionsTimeTotal"].is_null()) {
            obj.executionsTimeTotal = j["executionsTimeTotal"].get<int64_t>();
        }
        if (j.contains("executionsMbSecondsTotal") && !j["executionsMbSecondsTotal"].is_null()) {
            obj.executionsMbSecondsTotal = j["executionsMbSecondsTotal"].get<int64_t>();
        }
        if (j.contains("deployments") && !j["deployments"].is_null()) {
                                        for (auto& item : j["deployments"]) {
                                obj.deployments.push_back(Metric::fromJson(item));
                            }
                    }
        if (j.contains("deploymentsStorage") && !j["deploymentsStorage"].is_null()) {
                                        for (auto& item : j["deploymentsStorage"]) {
                                obj.deploymentsStorage.push_back(Metric::fromJson(item));
                            }
                    }
        if (j.contains("builds") && !j["builds"].is_null()) {
                                        for (auto& item : j["builds"]) {
                                obj.builds.push_back(Metric::fromJson(item));
                            }
                    }
        if (j.contains("buildsStorage") && !j["buildsStorage"].is_null()) {
                                        for (auto& item : j["buildsStorage"]) {
                                obj.buildsStorage.push_back(Metric::fromJson(item));
                            }
                    }
        if (j.contains("buildsTime") && !j["buildsTime"].is_null()) {
                                        for (auto& item : j["buildsTime"]) {
                                obj.buildsTime.push_back(Metric::fromJson(item));
                            }
                    }
        if (j.contains("buildsMbSeconds") && !j["buildsMbSeconds"].is_null()) {
                                        for (auto& item : j["buildsMbSeconds"]) {
                                obj.buildsMbSeconds.push_back(Metric::fromJson(item));
                            }
                    }
        if (j.contains("executions") && !j["executions"].is_null()) {
                                        for (auto& item : j["executions"]) {
                                obj.executions.push_back(Metric::fromJson(item));
                            }
                    }
        if (j.contains("executionsTime") && !j["executionsTime"].is_null()) {
                                        for (auto& item : j["executionsTime"]) {
                                obj.executionsTime.push_back(Metric::fromJson(item));
                            }
                    }
        if (j.contains("executionsMbSeconds") && !j["executionsMbSeconds"].is_null()) {
                                        for (auto& item : j["executionsMbSeconds"]) {
                                obj.executionsMbSeconds.push_back(Metric::fromJson(item));
                            }
                    }
        if (j.contains("buildsSuccess") && !j["buildsSuccess"].is_null()) {
                                        for (auto& item : j["buildsSuccess"]) {
                                obj.buildsSuccess.push_back(Metric::fromJson(item));
                            }
                    }
        if (j.contains("buildsFailed") && !j["buildsFailed"].is_null()) {
                                        for (auto& item : j["buildsFailed"]) {
                                obj.buildsFailed.push_back(Metric::fromJson(item));
                            }
                    }
        return obj;
    }

    /** @brief Serialize to JSON */
    [[nodiscard]] nlohmann::json toJson() const {
        nlohmann::json j = nlohmann::json::object();
        {
            j["range"] = this->range;
        }
        {
            j["deploymentsTotal"] = this->deploymentsTotal;
        }
        {
            j["deploymentsStorageTotal"] = this->deploymentsStorageTotal;
        }
        {
            j["buildsTotal"] = this->buildsTotal;
        }
        {
            j["buildsSuccessTotal"] = this->buildsSuccessTotal;
        }
        {
            j["buildsFailedTotal"] = this->buildsFailedTotal;
        }
        {
            j["buildsStorageTotal"] = this->buildsStorageTotal;
        }
        {
            j["buildsTimeTotal"] = this->buildsTimeTotal;
        }
        {
            j["buildsTimeAverage"] = this->buildsTimeAverage;
        }
        {
            j["buildsMbSecondsTotal"] = this->buildsMbSecondsTotal;
        }
        {
            j["executionsTotal"] = this->executionsTotal;
        }
        {
            j["executionsTimeTotal"] = this->executionsTimeTotal;
        }
        {
            j["executionsMbSecondsTotal"] = this->executionsMbSecondsTotal;
        }
        {
                nlohmann::json arr = nlohmann::json::array();
            for (auto& item : this->deployments) arr.push_back(item.toJson());
            j["deployments"] = arr;
            }
        {
                nlohmann::json arr = nlohmann::json::array();
            for (auto& item : this->deploymentsStorage) arr.push_back(item.toJson());
            j["deploymentsStorage"] = arr;
            }
        {
                nlohmann::json arr = nlohmann::json::array();
            for (auto& item : this->builds) arr.push_back(item.toJson());
            j["builds"] = arr;
            }
        {
                nlohmann::json arr = nlohmann::json::array();
            for (auto& item : this->buildsStorage) arr.push_back(item.toJson());
            j["buildsStorage"] = arr;
            }
        {
                nlohmann::json arr = nlohmann::json::array();
            for (auto& item : this->buildsTime) arr.push_back(item.toJson());
            j["buildsTime"] = arr;
            }
        {
                nlohmann::json arr = nlohmann::json::array();
            for (auto& item : this->buildsMbSeconds) arr.push_back(item.toJson());
            j["buildsMbSeconds"] = arr;
            }
        {
                nlohmann::json arr = nlohmann::json::array();
            for (auto& item : this->executions) arr.push_back(item.toJson());
            j["executions"] = arr;
            }
        {
                nlohmann::json arr = nlohmann::json::array();
            for (auto& item : this->executionsTime) arr.push_back(item.toJson());
            j["executionsTime"] = arr;
            }
        {
                nlohmann::json arr = nlohmann::json::array();
            for (auto& item : this->executionsMbSeconds) arr.push_back(item.toJson());
            j["executionsMbSeconds"] = arr;
            }
        {
                nlohmann::json arr = nlohmann::json::array();
            for (auto& item : this->buildsSuccess) arr.push_back(item.toJson());
            j["buildsSuccess"] = arr;
            }
        {
                nlohmann::json arr = nlohmann::json::array();
            for (auto& item : this->buildsFailed) arr.push_back(item.toJson());
            j["buildsFailed"] = arr;
            }
        return j;
    }
};

/** @brief ADL hook for nlohmann::json */
inline void from_json(const nlohmann::json& j, UsageFunction& x) {
    x = UsageFunction::fromJson(j);
}

inline void to_json(nlohmann::json& j, const UsageFunction& x) {
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
        if (j.contains("$id") && !j["$id"].is_null()) {
            obj.id = j["$id"].get<std::string>();
        }
        if (j.contains("$createdAt") && !j["$createdAt"].is_null()) {
            obj.createdAt = j["$createdAt"].get<std::string>();
        }
        if (j.contains("userId") && !j["userId"].is_null()) {
            obj.userId = j["userId"].get<std::string>();
        }
        if (j.contains("expire") && !j["expire"].is_null()) {
            obj.expire = j["expire"].get<std::string>();
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
        if (j.contains("recoveryCodes") && !j["recoveryCodes"].is_null()) {
            obj.recoveryCodes = j["recoveryCodes"].get<std::vector<std::string>>();
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
        if (j.contains("secret") && !j["secret"].is_null()) {
            obj.secret = j["secret"].get<std::string>();
        }
        if (j.contains("uri") && !j["uri"].is_null()) {
            obj.uri = j["uri"].get<std::string>();
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
        if (j.contains("totp") && !j["totp"].is_null()) {
            obj.totp = j["totp"].get<bool>();
        }
        if (j.contains("phone") && !j["phone"].is_null()) {
            obj.phone = j["phone"].get<bool>();
        }
        if (j.contains("email") && !j["email"].is_null()) {
            obj.email = j["email"].get<bool>();
        }
        if (j.contains("recoveryCode") && !j["recoveryCode"].is_null()) {
            obj.recoveryCode = j["recoveryCode"].get<bool>();
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

/**
 * @brief BillingAddress
 */
struct BillingAddress {
    /** @brief Region ID */
    std::string id;
    /** @brief User ID */
    std::string userId;
    /** @brief Street address */
    std::string streetAddress;
    /** @brief Address line 2 */
    std::string addressLine2;
    /** @brief Address country */
    std::string country;
    /** @brief city */
    std::string city;
    /** @brief state */
    std::string state;
    /** @brief postal code */
    std::string postalCode;

    /** @brief Deserialize from JSON */
    static BillingAddress fromJson(const nlohmann::json& j) {
        BillingAddress obj{}; // value-init: zeroes bools, ints, enums
        if (j.contains("$id") && !j["$id"].is_null()) {
            obj.id = j["$id"].get<std::string>();
        }
        if (j.contains("userId") && !j["userId"].is_null()) {
            obj.userId = j["userId"].get<std::string>();
        }
        if (j.contains("streetAddress") && !j["streetAddress"].is_null()) {
            obj.streetAddress = j["streetAddress"].get<std::string>();
        }
        if (j.contains("addressLine2") && !j["addressLine2"].is_null()) {
            obj.addressLine2 = j["addressLine2"].get<std::string>();
        }
        if (j.contains("country") && !j["country"].is_null()) {
            obj.country = j["country"].get<std::string>();
        }
        if (j.contains("city") && !j["city"].is_null()) {
            obj.city = j["city"].get<std::string>();
        }
        if (j.contains("state") && !j["state"].is_null()) {
            obj.state = j["state"].get<std::string>();
        }
        if (j.contains("postalCode") && !j["postalCode"].is_null()) {
            obj.postalCode = j["postalCode"].get<std::string>();
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
            j["userId"] = this->userId;
        }
        {
            j["streetAddress"] = this->streetAddress;
        }
        {
            j["addressLine2"] = this->addressLine2;
        }
        {
            j["country"] = this->country;
        }
        {
            j["city"] = this->city;
        }
        {
            j["state"] = this->state;
        }
        {
            j["postalCode"] = this->postalCode;
        }
        return j;
    }
};

/** @brief ADL hook for nlohmann::json */
inline void from_json(const nlohmann::json& j, BillingAddress& x) {
    x = BillingAddress::fromJson(j);
}

inline void to_json(nlohmann::json& j, const BillingAddress& x) {
    j = x.toJson();
}

/**
 * @brief Coupon
 */
struct Coupon {
    /** @brief coupon ID */
    std::string id;
    /** @brief coupon ID */
    std::string code;
    /** @brief Provided credit amount */
    double credits;
    /** @brief Coupon expiration time in ISO 8601 format. */
    std::string expiration;
    /** @brief Credit validity in days. */
    int64_t validity;
    /** @brief Campaign the coupon is associated with`. */
    std::string campaign;
    /** @brief Status of the coupon. Can be one of `disabled`, `active` or `expired`. */
    std::string status;
    /** @brief If the coupon is only valid for new organizations or not. */
    bool onlyNewOrgs;

    /** @brief Deserialize from JSON */
    static Coupon fromJson(const nlohmann::json& j) {
        Coupon obj{}; // value-init: zeroes bools, ints, enums
        if (j.contains("$id") && !j["$id"].is_null()) {
            obj.id = j["$id"].get<std::string>();
        }
        if (j.contains("code") && !j["code"].is_null()) {
            obj.code = j["code"].get<std::string>();
        }
        if (j.contains("credits") && !j["credits"].is_null()) {
            obj.credits = j["credits"].get<double>();
        }
        if (j.contains("expiration") && !j["expiration"].is_null()) {
            obj.expiration = j["expiration"].get<std::string>();
        }
        if (j.contains("validity") && !j["validity"].is_null()) {
            obj.validity = j["validity"].get<int64_t>();
        }
        if (j.contains("campaign") && !j["campaign"].is_null()) {
            obj.campaign = j["campaign"].get<std::string>();
        }
        if (j.contains("status") && !j["status"].is_null()) {
            obj.status = j["status"].get<std::string>();
        }
        if (j.contains("onlyNewOrgs") && !j["onlyNewOrgs"].is_null()) {
            obj.onlyNewOrgs = j["onlyNewOrgs"].get<bool>();
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
            j["code"] = this->code;
        }
        {
            j["credits"] = this->credits;
        }
        {
            j["expiration"] = this->expiration;
        }
        {
            j["validity"] = this->validity;
        }
        {
            j["campaign"] = this->campaign;
        }
        {
            j["status"] = this->status;
        }
        {
            j["onlyNewOrgs"] = this->onlyNewOrgs;
        }
        return j;
    }
};

/** @brief ADL hook for nlohmann::json */
inline void from_json(const nlohmann::json& j, Coupon& x) {
    x = Coupon::fromJson(j);
}

inline void to_json(nlohmann::json& j, const Coupon& x) {
    j = x.toJson();
}

/**
 * @brief UsageResource
 */
struct UsageResources {
    /** @brief Invoice name */
    std::string name;
    /** @brief Invoice value */
    int64_t value;
    /** @brief Invoice amount */
    double amount;
    /** @brief Invoice rate */
    double rate;
    /** @brief Invoice description */
    std::string desc;
    /** @brief Resource ID */
    std::string resourceId;

    /** @brief Deserialize from JSON */
    static UsageResources fromJson(const nlohmann::json& j) {
        UsageResources obj{}; // value-init: zeroes bools, ints, enums
        if (j.contains("name") && !j["name"].is_null()) {
            obj.name = j["name"].get<std::string>();
        }
        if (j.contains("value") && !j["value"].is_null()) {
            obj.value = j["value"].get<int64_t>();
        }
        if (j.contains("amount") && !j["amount"].is_null()) {
            obj.amount = j["amount"].get<double>();
        }
        if (j.contains("rate") && !j["rate"].is_null()) {
            obj.rate = j["rate"].get<double>();
        }
        if (j.contains("desc") && !j["desc"].is_null()) {
            obj.desc = j["desc"].get<std::string>();
        }
        if (j.contains("resourceId") && !j["resourceId"].is_null()) {
            obj.resourceId = j["resourceId"].get<std::string>();
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
        {
            j["amount"] = this->amount;
        }
        {
            j["rate"] = this->rate;
        }
        {
            j["desc"] = this->desc;
        }
        {
            j["resourceId"] = this->resourceId;
        }
        return j;
    }
};

/** @brief ADL hook for nlohmann::json */
inline void from_json(const nlohmann::json& j, UsageResources& x) {
    x = UsageResources::fromJson(j);
}

inline void to_json(nlohmann::json& j, const UsageResources& x) {
    j = x.toJson();
}

/**
 * @brief Invoice
 */
struct Invoice {
    /** @brief Invoice ID. */
    std::string id;
    /** @brief Invoice creation time in ISO 8601 format. */
    std::string createdAt;
    /** @brief Invoice update date in ISO 8601 format. */
    std::string updatedAt;
    /** @brief Invoice permissions. [Learn more about permissions](/docs/permissions). */
    std::vector<std::string> permissions;
    /** @brief Project ID */
    std::string teamId;
    /** @brief Aggregation ID */
    std::string aggregationId;
    /** @brief Billing plan selected. Can be one of `tier-0`, `tier-1` or `tier-2`. */
    std::string plan;
    /** @brief Usage breakdown per resource */
    std::vector<appwrite::models::UsageResources> usage;
    /** @brief Invoice Amount */
    double amount;
    /** @brief Tax percentage */
    double tax;
    /** @brief Tax amount */
    double taxAmount;
    /** @brief VAT percentage */
    double vat;
    /** @brief VAT amount */
    double vatAmount;
    /** @brief Gross amount after vat, tax, and discounts applied. */
    double grossAmount;
    /** @brief Credits used. */
    double creditsUsed;
    /** @brief Currency the invoice is in */
    std::string currency;
    /** @brief Client secret for processing failed payments in front-end */
    std::string clientSecret;
    /** @brief Invoice status */
    std::string status;
    /** @brief Last payment error associated with the invoice */
    std::string lastError;
    /** @brief Invoice due date. */
    std::string dueAt;
    /** @brief Beginning date of the invoice */
    std::string from;
    /** @brief End date of the invoice */
    std::string to;

    /** @brief Deserialize from JSON */
    static Invoice fromJson(const nlohmann::json& j) {
        Invoice obj{}; // value-init: zeroes bools, ints, enums
        if (j.contains("$id") && !j["$id"].is_null()) {
            obj.id = j["$id"].get<std::string>();
        }
        if (j.contains("$createdAt") && !j["$createdAt"].is_null()) {
            obj.createdAt = j["$createdAt"].get<std::string>();
        }
        if (j.contains("$updatedAt") && !j["$updatedAt"].is_null()) {
            obj.updatedAt = j["$updatedAt"].get<std::string>();
        }
        if (j.contains("$permissions") && !j["$permissions"].is_null()) {
            obj.permissions = j["$permissions"].get<std::vector<std::string>>();
        }
        if (j.contains("teamId") && !j["teamId"].is_null()) {
            obj.teamId = j["teamId"].get<std::string>();
        }
        if (j.contains("aggregationId") && !j["aggregationId"].is_null()) {
            obj.aggregationId = j["aggregationId"].get<std::string>();
        }
        if (j.contains("plan") && !j["plan"].is_null()) {
            obj.plan = j["plan"].get<std::string>();
        }
        if (j.contains("usage") && !j["usage"].is_null()) {
                                        for (auto& item : j["usage"]) {
                                obj.usage.push_back(UsageResources::fromJson(item));
                            }
                    }
        if (j.contains("amount") && !j["amount"].is_null()) {
            obj.amount = j["amount"].get<double>();
        }
        if (j.contains("tax") && !j["tax"].is_null()) {
            obj.tax = j["tax"].get<double>();
        }
        if (j.contains("taxAmount") && !j["taxAmount"].is_null()) {
            obj.taxAmount = j["taxAmount"].get<double>();
        }
        if (j.contains("vat") && !j["vat"].is_null()) {
            obj.vat = j["vat"].get<double>();
        }
        if (j.contains("vatAmount") && !j["vatAmount"].is_null()) {
            obj.vatAmount = j["vatAmount"].get<double>();
        }
        if (j.contains("grossAmount") && !j["grossAmount"].is_null()) {
            obj.grossAmount = j["grossAmount"].get<double>();
        }
        if (j.contains("creditsUsed") && !j["creditsUsed"].is_null()) {
            obj.creditsUsed = j["creditsUsed"].get<double>();
        }
        if (j.contains("currency") && !j["currency"].is_null()) {
            obj.currency = j["currency"].get<std::string>();
        }
        if (j.contains("clientSecret") && !j["clientSecret"].is_null()) {
            obj.clientSecret = j["clientSecret"].get<std::string>();
        }
        if (j.contains("status") && !j["status"].is_null()) {
            obj.status = j["status"].get<std::string>();
        }
        if (j.contains("lastError") && !j["lastError"].is_null()) {
            obj.lastError = j["lastError"].get<std::string>();
        }
        if (j.contains("dueAt") && !j["dueAt"].is_null()) {
            obj.dueAt = j["dueAt"].get<std::string>();
        }
        if (j.contains("from") && !j["from"].is_null()) {
            obj.from = j["from"].get<std::string>();
        }
        if (j.contains("to") && !j["to"].is_null()) {
            obj.to = j["to"].get<std::string>();
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
            j["teamId"] = this->teamId;
        }
        {
            j["aggregationId"] = this->aggregationId;
        }
        {
            j["plan"] = this->plan;
        }
        {
                nlohmann::json arr = nlohmann::json::array();
            for (auto& item : this->usage) arr.push_back(item.toJson());
            j["usage"] = arr;
            }
        {
            j["amount"] = this->amount;
        }
        {
            j["tax"] = this->tax;
        }
        {
            j["taxAmount"] = this->taxAmount;
        }
        {
            j["vat"] = this->vat;
        }
        {
            j["vatAmount"] = this->vatAmount;
        }
        {
            j["grossAmount"] = this->grossAmount;
        }
        {
            j["creditsUsed"] = this->creditsUsed;
        }
        {
            j["currency"] = this->currency;
        }
        {
            j["clientSecret"] = this->clientSecret;
        }
        {
            j["status"] = this->status;
        }
        {
            j["lastError"] = this->lastError;
        }
        {
            j["dueAt"] = this->dueAt;
        }
        {
            j["from"] = this->from;
        }
        {
            j["to"] = this->to;
        }
        return j;
    }
};

/** @brief ADL hook for nlohmann::json */
inline void from_json(const nlohmann::json& j, Invoice& x) {
    x = Invoice::fromJson(j);
}

inline void to_json(nlohmann::json& j, const Invoice& x) {
    j = x.toJson();
}

/**
 * @brief paymentMethod
 */
struct PaymentMethod {
    /** @brief Payment Method ID. */
    std::string id;
    /** @brief Payment method creation time in ISO 8601 format. */
    std::string createdAt;
    /** @brief Payment method update date in ISO 8601 format. */
    std::string updatedAt;
    /** @brief Payment method permissions. [Learn more about permissions](/docs/permissions). */
    std::vector<std::string> permissions;
    /** @brief Payment method ID from the payment provider */
    std::string providerMethodId;
    /** @brief Client secret hash for payment setup */
    std::string clientSecret;
    /** @brief User ID from the payment provider. */
    std::string providerUserId;
    /** @brief ID of the Team. */
    std::string userId;
    /** @brief Expiry month of the payment method. */
    int64_t expiryMonth;
    /** @brief Expiry year of the payment method. */
    int64_t expiryYear;
    /** @brief Last 4 digit of the payment method */
    std::string last4;
    /** @brief Payment method brand */
    std::string brand;
    /** @brief Name of the owner */
    std::string name;
    /** @brief Mandate ID of the payment method */
    std::string mandateId;
    /** @brief Country of the payment method */
    std::string country;
    /** @brief State of the payment method */
    std::string state;
    /** @brief Last payment error associated with the payment method. */
    std::string lastError;
    /** @brief True when it's the default payment method. */
    bool default_;
    /** @brief True when payment method has expired. */
    bool expired;
    /** @brief True when payment method has failed to process multiple times. */
    bool failed;

    /** @brief Deserialize from JSON */
    static PaymentMethod fromJson(const nlohmann::json& j) {
        PaymentMethod obj{}; // value-init: zeroes bools, ints, enums
        if (j.contains("$id") && !j["$id"].is_null()) {
            obj.id = j["$id"].get<std::string>();
        }
        if (j.contains("$createdAt") && !j["$createdAt"].is_null()) {
            obj.createdAt = j["$createdAt"].get<std::string>();
        }
        if (j.contains("$updatedAt") && !j["$updatedAt"].is_null()) {
            obj.updatedAt = j["$updatedAt"].get<std::string>();
        }
        if (j.contains("$permissions") && !j["$permissions"].is_null()) {
            obj.permissions = j["$permissions"].get<std::vector<std::string>>();
        }
        if (j.contains("providerMethodId") && !j["providerMethodId"].is_null()) {
            obj.providerMethodId = j["providerMethodId"].get<std::string>();
        }
        if (j.contains("clientSecret") && !j["clientSecret"].is_null()) {
            obj.clientSecret = j["clientSecret"].get<std::string>();
        }
        if (j.contains("providerUserId") && !j["providerUserId"].is_null()) {
            obj.providerUserId = j["providerUserId"].get<std::string>();
        }
        if (j.contains("userId") && !j["userId"].is_null()) {
            obj.userId = j["userId"].get<std::string>();
        }
        if (j.contains("expiryMonth") && !j["expiryMonth"].is_null()) {
            obj.expiryMonth = j["expiryMonth"].get<int64_t>();
        }
        if (j.contains("expiryYear") && !j["expiryYear"].is_null()) {
            obj.expiryYear = j["expiryYear"].get<int64_t>();
        }
        if (j.contains("last4") && !j["last4"].is_null()) {
            obj.last4 = j["last4"].get<std::string>();
        }
        if (j.contains("brand") && !j["brand"].is_null()) {
            obj.brand = j["brand"].get<std::string>();
        }
        if (j.contains("name") && !j["name"].is_null()) {
            obj.name = j["name"].get<std::string>();
        }
        if (j.contains("mandateId") && !j["mandateId"].is_null()) {
            obj.mandateId = j["mandateId"].get<std::string>();
        }
        if (j.contains("country") && !j["country"].is_null()) {
            obj.country = j["country"].get<std::string>();
        }
        if (j.contains("state") && !j["state"].is_null()) {
            obj.state = j["state"].get<std::string>();
        }
        if (j.contains("lastError") && !j["lastError"].is_null()) {
            obj.lastError = j["lastError"].get<std::string>();
        }
        if (j.contains("default") && !j["default"].is_null()) {
            obj.default_ = j["default"].get<bool>();
        }
        if (j.contains("expired") && !j["expired"].is_null()) {
            obj.expired = j["expired"].get<bool>();
        }
        if (j.contains("failed") && !j["failed"].is_null()) {
            obj.failed = j["failed"].get<bool>();
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
            j["providerMethodId"] = this->providerMethodId;
        }
        {
            j["clientSecret"] = this->clientSecret;
        }
        {
            j["providerUserId"] = this->providerUserId;
        }
        {
            j["userId"] = this->userId;
        }
        {
            j["expiryMonth"] = this->expiryMonth;
        }
        {
            j["expiryYear"] = this->expiryYear;
        }
        {
            j["last4"] = this->last4;
        }
        {
            j["brand"] = this->brand;
        }
        {
            j["name"] = this->name;
        }
        {
            j["mandateId"] = this->mandateId;
        }
        {
            j["country"] = this->country;
        }
        {
            j["state"] = this->state;
        }
        {
            j["lastError"] = this->lastError;
        }
        {
            j["default"] = this->default_;
        }
        {
            j["expired"] = this->expired;
        }
        {
            j["failed"] = this->failed;
        }
        return j;
    }
};

/** @brief ADL hook for nlohmann::json */
inline void from_json(const nlohmann::json& j, PaymentMethod& x) {
    x = PaymentMethod::fromJson(j);
}

inline void to_json(nlohmann::json& j, const PaymentMethod& x) {
    j = x.toJson();
}

/**
 * @brief Billing invoices list
 */
struct InvoiceList {
    /** @brief Total number of invoices that matched your query. */
    int64_t total;
    /** @brief List of invoices. */
    std::vector<appwrite::models::Invoice> invoices;

    /** @brief Deserialize from JSON */
    static InvoiceList fromJson(const nlohmann::json& j) {
        InvoiceList obj{}; // value-init: zeroes bools, ints, enums
        if (j.contains("total") && !j["total"].is_null()) {
            obj.total = j["total"].get<int64_t>();
        }
        if (j.contains("invoices") && !j["invoices"].is_null()) {
                                        for (auto& item : j["invoices"]) {
                                obj.invoices.push_back(Invoice::fromJson(item));
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
            for (auto& item : this->invoices) arr.push_back(item.toJson());
            j["invoices"] = arr;
            }
        return j;
    }
};

/** @brief ADL hook for nlohmann::json */
inline void from_json(const nlohmann::json& j, InvoiceList& x) {
    x = InvoiceList::fromJson(j);
}

inline void to_json(nlohmann::json& j, const InvoiceList& x) {
    j = x.toJson();
}

/**
 * @brief Billing address list
 */
struct BillingAddressList {
    /** @brief Total number of billingAddresses that matched your query. */
    int64_t total;
    /** @brief List of billingAddresses. */
    std::vector<appwrite::models::BillingAddress> billingAddresses;

    /** @brief Deserialize from JSON */
    static BillingAddressList fromJson(const nlohmann::json& j) {
        BillingAddressList obj{}; // value-init: zeroes bools, ints, enums
        if (j.contains("total") && !j["total"].is_null()) {
            obj.total = j["total"].get<int64_t>();
        }
        if (j.contains("billingAddresses") && !j["billingAddresses"].is_null()) {
                                        for (auto& item : j["billingAddresses"]) {
                                obj.billingAddresses.push_back(BillingAddress::fromJson(item));
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
            for (auto& item : this->billingAddresses) arr.push_back(item.toJson());
            j["billingAddresses"] = arr;
            }
        return j;
    }
};

/** @brief ADL hook for nlohmann::json */
inline void from_json(const nlohmann::json& j, BillingAddressList& x) {
    x = BillingAddressList::fromJson(j);
}

inline void to_json(nlohmann::json& j, const BillingAddressList& x) {
    j = x.toJson();
}

/**
 * @brief Payment methods list
 */
struct PaymentMethodList {
    /** @brief Total number of paymentMethods that matched your query. */
    int64_t total;
    /** @brief List of paymentMethods. */
    std::vector<appwrite::models::PaymentMethod> paymentMethods;

    /** @brief Deserialize from JSON */
    static PaymentMethodList fromJson(const nlohmann::json& j) {
        PaymentMethodList obj{}; // value-init: zeroes bools, ints, enums
        if (j.contains("total") && !j["total"].is_null()) {
            obj.total = j["total"].get<int64_t>();
        }
        if (j.contains("paymentMethods") && !j["paymentMethods"].is_null()) {
                                        for (auto& item : j["paymentMethods"]) {
                                obj.paymentMethods.push_back(PaymentMethod::fromJson(item));
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
            for (auto& item : this->paymentMethods) arr.push_back(item.toJson());
            j["paymentMethods"] = arr;
            }
        return j;
    }
};

/** @brief ADL hook for nlohmann::json */
inline void from_json(const nlohmann::json& j, PaymentMethodList& x) {
    x = PaymentMethodList::fromJson(j);
}

inline void to_json(nlohmann::json& j, const PaymentMethodList& x) {
    j = x.toJson();
}

} // namespace models
} // namespace appwrite
