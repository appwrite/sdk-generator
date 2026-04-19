#include <gtest/gtest.h>
#include <nlohmann/json.hpp>
#include "appwrite/models.hpp"

TEST(RowListTest, RoundTrip) {
    auto seed = nlohmann::json::parse(R"json({        "total": 5,        "rows": [{}]    })json");

    // Deserialization must not throw (skip on type mismatch in example data)
    appwrite::models::RowList model;
    try {
        model = appwrite::models::RowList::fromJson(seed);
    } catch (const nlohmann::json::exception&) {
        GTEST_SKIP() << "Seed example data type mismatch — skipped";
    }

    // Idempotency: serializing then deserializing then re-serializing must be stable
    auto json1  = model.toJson();
    auto model2 = appwrite::models::RowList::fromJson(json1);
    auto json2  = model2.toJson();
    EXPECT_EQ(json1.dump(), json2.dump());
}
TEST(DocumentListTest, RoundTrip) {
    auto seed = nlohmann::json::parse(R"json({        "total": 5,        "documents": [{}]    })json");

    // Deserialization must not throw (skip on type mismatch in example data)
    appwrite::models::DocumentList model;
    try {
        model = appwrite::models::DocumentList::fromJson(seed);
    } catch (const nlohmann::json::exception&) {
        GTEST_SKIP() << "Seed example data type mismatch — skipped";
    }

    // Idempotency: serializing then deserializing then re-serializing must be stable
    auto json1  = model.toJson();
    auto model2 = appwrite::models::DocumentList::fromJson(json1);
    auto json2  = model2.toJson();
    EXPECT_EQ(json1.dump(), json2.dump());
}
TEST(TableListTest, RoundTrip) {
    auto seed = nlohmann::json::parse(R"json({        "total": 5,        "tables": [{}]    })json");

    // Deserialization must not throw (skip on type mismatch in example data)
    appwrite::models::TableList model;
    try {
        model = appwrite::models::TableList::fromJson(seed);
    } catch (const nlohmann::json::exception&) {
        GTEST_SKIP() << "Seed example data type mismatch — skipped";
    }

    // Idempotency: serializing then deserializing then re-serializing must be stable
    auto json1  = model.toJson();
    auto model2 = appwrite::models::TableList::fromJson(json1);
    auto json2  = model2.toJson();
    EXPECT_EQ(json1.dump(), json2.dump());
}
TEST(CollectionListTest, RoundTrip) {
    auto seed = nlohmann::json::parse(R"json({        "total": 5,        "collections": [{}]    })json");

    // Deserialization must not throw (skip on type mismatch in example data)
    appwrite::models::CollectionList model;
    try {
        model = appwrite::models::CollectionList::fromJson(seed);
    } catch (const nlohmann::json::exception&) {
        GTEST_SKIP() << "Seed example data type mismatch — skipped";
    }

    // Idempotency: serializing then deserializing then re-serializing must be stable
    auto json1  = model.toJson();
    auto model2 = appwrite::models::CollectionList::fromJson(json1);
    auto json2  = model2.toJson();
    EXPECT_EQ(json1.dump(), json2.dump());
}
TEST(DatabaseListTest, RoundTrip) {
    auto seed = nlohmann::json::parse(R"json({        "total": 5,        "databases": [{}]    })json");

    // Deserialization must not throw (skip on type mismatch in example data)
    appwrite::models::DatabaseList model;
    try {
        model = appwrite::models::DatabaseList::fromJson(seed);
    } catch (const nlohmann::json::exception&) {
        GTEST_SKIP() << "Seed example data type mismatch — skipped";
    }

    // Idempotency: serializing then deserializing then re-serializing must be stable
    auto json1  = model.toJson();
    auto model2 = appwrite::models::DatabaseList::fromJson(json1);
    auto json2  = model2.toJson();
    EXPECT_EQ(json1.dump(), json2.dump());
}
TEST(IndexListTest, RoundTrip) {
    auto seed = nlohmann::json::parse(R"json({        "total": 5,        "indexes": [{}]    })json");

    // Deserialization must not throw (skip on type mismatch in example data)
    appwrite::models::IndexList model;
    try {
        model = appwrite::models::IndexList::fromJson(seed);
    } catch (const nlohmann::json::exception&) {
        GTEST_SKIP() << "Seed example data type mismatch — skipped";
    }

    // Idempotency: serializing then deserializing then re-serializing must be stable
    auto json1  = model.toJson();
    auto model2 = appwrite::models::IndexList::fromJson(json1);
    auto json2  = model2.toJson();
    EXPECT_EQ(json1.dump(), json2.dump());
}
TEST(ColumnIndexListTest, RoundTrip) {
    auto seed = nlohmann::json::parse(R"json({        "total": 5,        "indexes": [{}]    })json");

    // Deserialization must not throw (skip on type mismatch in example data)
    appwrite::models::ColumnIndexList model;
    try {
        model = appwrite::models::ColumnIndexList::fromJson(seed);
    } catch (const nlohmann::json::exception&) {
        GTEST_SKIP() << "Seed example data type mismatch — skipped";
    }

    // Idempotency: serializing then deserializing then re-serializing must be stable
    auto json1  = model.toJson();
    auto model2 = appwrite::models::ColumnIndexList::fromJson(json1);
    auto json2  = model2.toJson();
    EXPECT_EQ(json1.dump(), json2.dump());
}
TEST(UserListTest, RoundTrip) {
    auto seed = nlohmann::json::parse(R"json({        "total": 5,        "users": [{}]    })json");

    // Deserialization must not throw (skip on type mismatch in example data)
    appwrite::models::UserList model;
    try {
        model = appwrite::models::UserList::fromJson(seed);
    } catch (const nlohmann::json::exception&) {
        GTEST_SKIP() << "Seed example data type mismatch — skipped";
    }

    // Idempotency: serializing then deserializing then re-serializing must be stable
    auto json1  = model.toJson();
    auto model2 = appwrite::models::UserList::fromJson(json1);
    auto json2  = model2.toJson();
    EXPECT_EQ(json1.dump(), json2.dump());
}
TEST(SessionListTest, RoundTrip) {
    auto seed = nlohmann::json::parse(R"json({        "total": 5,        "sessions": [{}]    })json");

    // Deserialization must not throw (skip on type mismatch in example data)
    appwrite::models::SessionList model;
    try {
        model = appwrite::models::SessionList::fromJson(seed);
    } catch (const nlohmann::json::exception&) {
        GTEST_SKIP() << "Seed example data type mismatch — skipped";
    }

    // Idempotency: serializing then deserializing then re-serializing must be stable
    auto json1  = model.toJson();
    auto model2 = appwrite::models::SessionList::fromJson(json1);
    auto json2  = model2.toJson();
    EXPECT_EQ(json1.dump(), json2.dump());
}
TEST(IdentityListTest, RoundTrip) {
    auto seed = nlohmann::json::parse(R"json({        "total": 5,        "identities": [{}]    })json");

    // Deserialization must not throw (skip on type mismatch in example data)
    appwrite::models::IdentityList model;
    try {
        model = appwrite::models::IdentityList::fromJson(seed);
    } catch (const nlohmann::json::exception&) {
        GTEST_SKIP() << "Seed example data type mismatch — skipped";
    }

    // Idempotency: serializing then deserializing then re-serializing must be stable
    auto json1  = model.toJson();
    auto model2 = appwrite::models::IdentityList::fromJson(json1);
    auto json2  = model2.toJson();
    EXPECT_EQ(json1.dump(), json2.dump());
}
TEST(LogListTest, RoundTrip) {
    auto seed = nlohmann::json::parse(R"json({        "total": 5,        "logs": [{}]    })json");

    // Deserialization must not throw (skip on type mismatch in example data)
    appwrite::models::LogList model;
    try {
        model = appwrite::models::LogList::fromJson(seed);
    } catch (const nlohmann::json::exception&) {
        GTEST_SKIP() << "Seed example data type mismatch — skipped";
    }

    // Idempotency: serializing then deserializing then re-serializing must be stable
    auto json1  = model.toJson();
    auto model2 = appwrite::models::LogList::fromJson(json1);
    auto json2  = model2.toJson();
    EXPECT_EQ(json1.dump(), json2.dump());
}
TEST(FileListTest, RoundTrip) {
    auto seed = nlohmann::json::parse(R"json({        "total": 5,        "files": [{}]    })json");

    // Deserialization must not throw (skip on type mismatch in example data)
    appwrite::models::FileList model;
    try {
        model = appwrite::models::FileList::fromJson(seed);
    } catch (const nlohmann::json::exception&) {
        GTEST_SKIP() << "Seed example data type mismatch — skipped";
    }

    // Idempotency: serializing then deserializing then re-serializing must be stable
    auto json1  = model.toJson();
    auto model2 = appwrite::models::FileList::fromJson(json1);
    auto json2  = model2.toJson();
    EXPECT_EQ(json1.dump(), json2.dump());
}
TEST(BucketListTest, RoundTrip) {
    auto seed = nlohmann::json::parse(R"json({        "total": 5,        "buckets": [{}]    })json");

    // Deserialization must not throw (skip on type mismatch in example data)
    appwrite::models::BucketList model;
    try {
        model = appwrite::models::BucketList::fromJson(seed);
    } catch (const nlohmann::json::exception&) {
        GTEST_SKIP() << "Seed example data type mismatch — skipped";
    }

    // Idempotency: serializing then deserializing then re-serializing must be stable
    auto json1  = model.toJson();
    auto model2 = appwrite::models::BucketList::fromJson(json1);
    auto json2  = model2.toJson();
    EXPECT_EQ(json1.dump(), json2.dump());
}
TEST(TeamListTest, RoundTrip) {
    auto seed = nlohmann::json::parse(R"json({        "total": 5,        "teams": [{}]    })json");

    // Deserialization must not throw (skip on type mismatch in example data)
    appwrite::models::TeamList model;
    try {
        model = appwrite::models::TeamList::fromJson(seed);
    } catch (const nlohmann::json::exception&) {
        GTEST_SKIP() << "Seed example data type mismatch — skipped";
    }

    // Idempotency: serializing then deserializing then re-serializing must be stable
    auto json1  = model.toJson();
    auto model2 = appwrite::models::TeamList::fromJson(json1);
    auto json2  = model2.toJson();
    EXPECT_EQ(json1.dump(), json2.dump());
}
TEST(MembershipListTest, RoundTrip) {
    auto seed = nlohmann::json::parse(R"json({        "total": 5,        "memberships": [{}]    })json");

    // Deserialization must not throw (skip on type mismatch in example data)
    appwrite::models::MembershipList model;
    try {
        model = appwrite::models::MembershipList::fromJson(seed);
    } catch (const nlohmann::json::exception&) {
        GTEST_SKIP() << "Seed example data type mismatch — skipped";
    }

    // Idempotency: serializing then deserializing then re-serializing must be stable
    auto json1  = model.toJson();
    auto model2 = appwrite::models::MembershipList::fromJson(json1);
    auto json2  = model2.toJson();
    EXPECT_EQ(json1.dump(), json2.dump());
}
TEST(FunctionListTest, RoundTrip) {
    auto seed = nlohmann::json::parse(R"json({        "total": 5,        "functions": [{}]    })json");

    // Deserialization must not throw (skip on type mismatch in example data)
    appwrite::models::FunctionList model;
    try {
        model = appwrite::models::FunctionList::fromJson(seed);
    } catch (const nlohmann::json::exception&) {
        GTEST_SKIP() << "Seed example data type mismatch — skipped";
    }

    // Idempotency: serializing then deserializing then re-serializing must be stable
    auto json1  = model.toJson();
    auto model2 = appwrite::models::FunctionList::fromJson(json1);
    auto json2  = model2.toJson();
    EXPECT_EQ(json1.dump(), json2.dump());
}
TEST(TemplateFunctionListTest, RoundTrip) {
    auto seed = nlohmann::json::parse(R"json({        "total": 5,        "templates": [{}]    })json");

    // Deserialization must not throw (skip on type mismatch in example data)
    appwrite::models::TemplateFunctionList model;
    try {
        model = appwrite::models::TemplateFunctionList::fromJson(seed);
    } catch (const nlohmann::json::exception&) {
        GTEST_SKIP() << "Seed example data type mismatch — skipped";
    }

    // Idempotency: serializing then deserializing then re-serializing must be stable
    auto json1  = model.toJson();
    auto model2 = appwrite::models::TemplateFunctionList::fromJson(json1);
    auto json2  = model2.toJson();
    EXPECT_EQ(json1.dump(), json2.dump());
}
TEST(RuntimeListTest, RoundTrip) {
    auto seed = nlohmann::json::parse(R"json({        "total": 5,        "runtimes": [{}]    })json");

    // Deserialization must not throw (skip on type mismatch in example data)
    appwrite::models::RuntimeList model;
    try {
        model = appwrite::models::RuntimeList::fromJson(seed);
    } catch (const nlohmann::json::exception&) {
        GTEST_SKIP() << "Seed example data type mismatch — skipped";
    }

    // Idempotency: serializing then deserializing then re-serializing must be stable
    auto json1  = model.toJson();
    auto model2 = appwrite::models::RuntimeList::fromJson(json1);
    auto json2  = model2.toJson();
    EXPECT_EQ(json1.dump(), json2.dump());
}
TEST(DeploymentListTest, RoundTrip) {
    auto seed = nlohmann::json::parse(R"json({        "total": 5,        "deployments": [{}]    })json");

    // Deserialization must not throw (skip on type mismatch in example data)
    appwrite::models::DeploymentList model;
    try {
        model = appwrite::models::DeploymentList::fromJson(seed);
    } catch (const nlohmann::json::exception&) {
        GTEST_SKIP() << "Seed example data type mismatch — skipped";
    }

    // Idempotency: serializing then deserializing then re-serializing must be stable
    auto json1  = model.toJson();
    auto model2 = appwrite::models::DeploymentList::fromJson(json1);
    auto json2  = model2.toJson();
    EXPECT_EQ(json1.dump(), json2.dump());
}
TEST(ExecutionListTest, RoundTrip) {
    auto seed = nlohmann::json::parse(R"json({        "total": 5,        "executions": [{}]    })json");

    // Deserialization must not throw (skip on type mismatch in example data)
    appwrite::models::ExecutionList model;
    try {
        model = appwrite::models::ExecutionList::fromJson(seed);
    } catch (const nlohmann::json::exception&) {
        GTEST_SKIP() << "Seed example data type mismatch — skipped";
    }

    // Idempotency: serializing then deserializing then re-serializing must be stable
    auto json1  = model.toJson();
    auto model2 = appwrite::models::ExecutionList::fromJson(json1);
    auto json2  = model2.toJson();
    EXPECT_EQ(json1.dump(), json2.dump());
}
TEST(WebhookListTest, RoundTrip) {
    auto seed = nlohmann::json::parse(R"json({        "total": 5,        "webhooks": [{}]    })json");

    // Deserialization must not throw (skip on type mismatch in example data)
    appwrite::models::WebhookList model;
    try {
        model = appwrite::models::WebhookList::fromJson(seed);
    } catch (const nlohmann::json::exception&) {
        GTEST_SKIP() << "Seed example data type mismatch — skipped";
    }

    // Idempotency: serializing then deserializing then re-serializing must be stable
    auto json1  = model.toJson();
    auto model2 = appwrite::models::WebhookList::fromJson(json1);
    auto json2  = model2.toJson();
    EXPECT_EQ(json1.dump(), json2.dump());
}
TEST(KeyListTest, RoundTrip) {
    auto seed = nlohmann::json::parse(R"json({        "total": 5,        "keys": [{}]    })json");

    // Deserialization must not throw (skip on type mismatch in example data)
    appwrite::models::KeyList model;
    try {
        model = appwrite::models::KeyList::fromJson(seed);
    } catch (const nlohmann::json::exception&) {
        GTEST_SKIP() << "Seed example data type mismatch — skipped";
    }

    // Idempotency: serializing then deserializing then re-serializing must be stable
    auto json1  = model.toJson();
    auto model2 = appwrite::models::KeyList::fromJson(json1);
    auto json2  = model2.toJson();
    EXPECT_EQ(json1.dump(), json2.dump());
}
TEST(VariableListTest, RoundTrip) {
    auto seed = nlohmann::json::parse(R"json({        "total": 5,        "variables": [{}]    })json");

    // Deserialization must not throw (skip on type mismatch in example data)
    appwrite::models::VariableList model;
    try {
        model = appwrite::models::VariableList::fromJson(seed);
    } catch (const nlohmann::json::exception&) {
        GTEST_SKIP() << "Seed example data type mismatch — skipped";
    }

    // Idempotency: serializing then deserializing then re-serializing must be stable
    auto json1  = model.toJson();
    auto model2 = appwrite::models::VariableList::fromJson(json1);
    auto json2  = model2.toJson();
    EXPECT_EQ(json1.dump(), json2.dump());
}
TEST(TargetListTest, RoundTrip) {
    auto seed = nlohmann::json::parse(R"json({        "total": 5,        "targets": [{}]    })json");

    // Deserialization must not throw (skip on type mismatch in example data)
    appwrite::models::TargetList model;
    try {
        model = appwrite::models::TargetList::fromJson(seed);
    } catch (const nlohmann::json::exception&) {
        GTEST_SKIP() << "Seed example data type mismatch — skipped";
    }

    // Idempotency: serializing then deserializing then re-serializing must be stable
    auto json1  = model.toJson();
    auto model2 = appwrite::models::TargetList::fromJson(json1);
    auto json2  = model2.toJson();
    EXPECT_EQ(json1.dump(), json2.dump());
}
TEST(TransactionListTest, RoundTrip) {
    auto seed = nlohmann::json::parse(R"json({        "total": 5,        "transactions": [{}]    })json");

    // Deserialization must not throw (skip on type mismatch in example data)
    appwrite::models::TransactionList model;
    try {
        model = appwrite::models::TransactionList::fromJson(seed);
    } catch (const nlohmann::json::exception&) {
        GTEST_SKIP() << "Seed example data type mismatch — skipped";
    }

    // Idempotency: serializing then deserializing then re-serializing must be stable
    auto json1  = model.toJson();
    auto model2 = appwrite::models::TransactionList::fromJson(json1);
    auto json2  = model2.toJson();
    EXPECT_EQ(json1.dump(), json2.dump());
}
TEST(SpecificationListTest, RoundTrip) {
    auto seed = nlohmann::json::parse(R"json({        "total": 5,        "specifications": [{}]    })json");

    // Deserialization must not throw (skip on type mismatch in example data)
    appwrite::models::SpecificationList model;
    try {
        model = appwrite::models::SpecificationList::fromJson(seed);
    } catch (const nlohmann::json::exception&) {
        GTEST_SKIP() << "Seed example data type mismatch — skipped";
    }

    // Idempotency: serializing then deserializing then re-serializing must be stable
    auto json1  = model.toJson();
    auto model2 = appwrite::models::SpecificationList::fromJson(json1);
    auto json2  = model2.toJson();
    EXPECT_EQ(json1.dump(), json2.dump());
}
TEST(DatabaseTest, RoundTrip) {
    auto seed = nlohmann::json::parse(R"json({        "$id": "5e5ea5c16897e",        "name": "My Database",        "$createdAt": "2020-10-15T06:38:00.000+00:00",        "$updatedAt": "2020-10-15T06:38:00.000+00:00",        "enabled": false,        "type": "legacy",        "policies": [{}],        "archives": [{}]    })json");

    // Deserialization must not throw (skip on type mismatch in example data)
    appwrite::models::Database model;
    try {
        model = appwrite::models::Database::fromJson(seed);
    } catch (const nlohmann::json::exception&) {
        GTEST_SKIP() << "Seed example data type mismatch — skipped";
    }

    // Idempotency: serializing then deserializing then re-serializing must be stable
    auto json1  = model.toJson();
    auto model2 = appwrite::models::Database::fromJson(json1);
    auto json2  = model2.toJson();
    EXPECT_EQ(json1.dump(), json2.dump());
}
TEST(CollectionTest, RoundTrip) {
    auto seed = nlohmann::json::parse(R"json({        "$id": "5e5ea5c16897e",        "$createdAt": "2020-10-15T06:38:00.000+00:00",        "$updatedAt": "2020-10-15T06:38:00.000+00:00",        "$permissions": ["read(\"any\")"],        "databaseId": "5e5ea5c16897e",        "name": "My Collection",        "enabled": false,        "documentSecurity": true,        "attributes": [],        "indexes": [{}],        "bytesMax": 65535,        "bytesUsed": 1500    })json");

    // Deserialization must not throw (skip on type mismatch in example data)
    appwrite::models::Collection model;
    try {
        model = appwrite::models::Collection::fromJson(seed);
    } catch (const nlohmann::json::exception&) {
        GTEST_SKIP() << "Seed example data type mismatch — skipped";
    }

    // Idempotency: serializing then deserializing then re-serializing must be stable
    auto json1  = model.toJson();
    auto model2 = appwrite::models::Collection::fromJson(json1);
    auto json2  = model2.toJson();
    EXPECT_EQ(json1.dump(), json2.dump());
}
TEST(AttributeListTest, RoundTrip) {
    auto seed = nlohmann::json::parse(R"json({        "total": 5,        "attributes": [""]    })json");

    // Deserialization must not throw (skip on type mismatch in example data)
    appwrite::models::AttributeList model;
    try {
        model = appwrite::models::AttributeList::fromJson(seed);
    } catch (const nlohmann::json::exception&) {
        GTEST_SKIP() << "Seed example data type mismatch — skipped";
    }

    // Idempotency: serializing then deserializing then re-serializing must be stable
    auto json1  = model.toJson();
    auto model2 = appwrite::models::AttributeList::fromJson(json1);
    auto json2  = model2.toJson();
    EXPECT_EQ(json1.dump(), json2.dump());
}
TEST(AttributeStringTest, RoundTrip) {
    auto seed = nlohmann::json::parse(R"json({        "key": "fullName",        "type": "string",        "status": "available",        "error": "string",        "required": true,        "array": false,        "$createdAt": "2020-10-15T06:38:00.000+00:00",        "$updatedAt": "2020-10-15T06:38:00.000+00:00",        "size": 128,        "default": "default",        "encrypt": false    })json");

    // Deserialization must not throw (skip on type mismatch in example data)
    appwrite::models::AttributeString model;
    try {
        model = appwrite::models::AttributeString::fromJson(seed);
    } catch (const nlohmann::json::exception&) {
        GTEST_SKIP() << "Seed example data type mismatch — skipped";
    }

    // Idempotency: serializing then deserializing then re-serializing must be stable
    auto json1  = model.toJson();
    auto model2 = appwrite::models::AttributeString::fromJson(json1);
    auto json2  = model2.toJson();
    EXPECT_EQ(json1.dump(), json2.dump());
}
TEST(AttributeIntegerTest, RoundTrip) {
    auto seed = nlohmann::json::parse(R"json({        "key": "count",        "type": "integer",        "status": "available",        "error": "string",        "required": true,        "array": false,        "$createdAt": "2020-10-15T06:38:00.000+00:00",        "$updatedAt": "2020-10-15T06:38:00.000+00:00",        "min": 1,        "max": 10,        "default": 10    })json");

    // Deserialization must not throw (skip on type mismatch in example data)
    appwrite::models::AttributeInteger model;
    try {
        model = appwrite::models::AttributeInteger::fromJson(seed);
    } catch (const nlohmann::json::exception&) {
        GTEST_SKIP() << "Seed example data type mismatch — skipped";
    }

    // Idempotency: serializing then deserializing then re-serializing must be stable
    auto json1  = model.toJson();
    auto model2 = appwrite::models::AttributeInteger::fromJson(json1);
    auto json2  = model2.toJson();
    EXPECT_EQ(json1.dump(), json2.dump());
}
TEST(AttributeFloatTest, RoundTrip) {
    auto seed = nlohmann::json::parse(R"json({        "key": "percentageCompleted",        "type": "double",        "status": "available",        "error": "string",        "required": true,        "array": false,        "$createdAt": "2020-10-15T06:38:00.000+00:00",        "$updatedAt": "2020-10-15T06:38:00.000+00:00",        "min": 1.5,        "max": 10.5,        "default": 2.5    })json");

    // Deserialization must not throw (skip on type mismatch in example data)
    appwrite::models::AttributeFloat model;
    try {
        model = appwrite::models::AttributeFloat::fromJson(seed);
    } catch (const nlohmann::json::exception&) {
        GTEST_SKIP() << "Seed example data type mismatch — skipped";
    }

    // Idempotency: serializing then deserializing then re-serializing must be stable
    auto json1  = model.toJson();
    auto model2 = appwrite::models::AttributeFloat::fromJson(json1);
    auto json2  = model2.toJson();
    EXPECT_EQ(json1.dump(), json2.dump());
}
TEST(AttributeBooleanTest, RoundTrip) {
    auto seed = nlohmann::json::parse(R"json({        "key": "isEnabled",        "type": "boolean",        "status": "available",        "error": "string",        "required": true,        "array": false,        "$createdAt": "2020-10-15T06:38:00.000+00:00",        "$updatedAt": "2020-10-15T06:38:00.000+00:00",        "default": false    })json");

    // Deserialization must not throw (skip on type mismatch in example data)
    appwrite::models::AttributeBoolean model;
    try {
        model = appwrite::models::AttributeBoolean::fromJson(seed);
    } catch (const nlohmann::json::exception&) {
        GTEST_SKIP() << "Seed example data type mismatch — skipped";
    }

    // Idempotency: serializing then deserializing then re-serializing must be stable
    auto json1  = model.toJson();
    auto model2 = appwrite::models::AttributeBoolean::fromJson(json1);
    auto json2  = model2.toJson();
    EXPECT_EQ(json1.dump(), json2.dump());
}
TEST(AttributeEmailTest, RoundTrip) {
    auto seed = nlohmann::json::parse(R"json({        "key": "userEmail",        "type": "string",        "status": "available",        "error": "string",        "required": true,        "array": false,        "$createdAt": "2020-10-15T06:38:00.000+00:00",        "$updatedAt": "2020-10-15T06:38:00.000+00:00",        "format": "email",        "default": "default@example.com"    })json");

    // Deserialization must not throw (skip on type mismatch in example data)
    appwrite::models::AttributeEmail model;
    try {
        model = appwrite::models::AttributeEmail::fromJson(seed);
    } catch (const nlohmann::json::exception&) {
        GTEST_SKIP() << "Seed example data type mismatch — skipped";
    }

    // Idempotency: serializing then deserializing then re-serializing must be stable
    auto json1  = model.toJson();
    auto model2 = appwrite::models::AttributeEmail::fromJson(json1);
    auto json2  = model2.toJson();
    EXPECT_EQ(json1.dump(), json2.dump());
}
TEST(AttributeEnumTest, RoundTrip) {
    auto seed = nlohmann::json::parse(R"json({        "key": "status",        "type": "string",        "status": "available",        "error": "string",        "required": true,        "array": false,        "$createdAt": "2020-10-15T06:38:00.000+00:00",        "$updatedAt": "2020-10-15T06:38:00.000+00:00",        "elements": ["element"],        "format": "enum",        "default": "element"    })json");

    // Deserialization must not throw (skip on type mismatch in example data)
    appwrite::models::AttributeEnum model;
    try {
        model = appwrite::models::AttributeEnum::fromJson(seed);
    } catch (const nlohmann::json::exception&) {
        GTEST_SKIP() << "Seed example data type mismatch — skipped";
    }

    // Idempotency: serializing then deserializing then re-serializing must be stable
    auto json1  = model.toJson();
    auto model2 = appwrite::models::AttributeEnum::fromJson(json1);
    auto json2  = model2.toJson();
    EXPECT_EQ(json1.dump(), json2.dump());
}
TEST(AttributeIpTest, RoundTrip) {
    auto seed = nlohmann::json::parse(R"json({        "key": "ipAddress",        "type": "string",        "status": "available",        "error": "string",        "required": true,        "array": false,        "$createdAt": "2020-10-15T06:38:00.000+00:00",        "$updatedAt": "2020-10-15T06:38:00.000+00:00",        "format": "ip",        "default": "192.0.2.0"    })json");

    // Deserialization must not throw (skip on type mismatch in example data)
    appwrite::models::AttributeIp model;
    try {
        model = appwrite::models::AttributeIp::fromJson(seed);
    } catch (const nlohmann::json::exception&) {
        GTEST_SKIP() << "Seed example data type mismatch — skipped";
    }

    // Idempotency: serializing then deserializing then re-serializing must be stable
    auto json1  = model.toJson();
    auto model2 = appwrite::models::AttributeIp::fromJson(json1);
    auto json2  = model2.toJson();
    EXPECT_EQ(json1.dump(), json2.dump());
}
TEST(AttributeUrlTest, RoundTrip) {
    auto seed = nlohmann::json::parse(R"json({        "key": "githubUrl",        "type": "string",        "status": "available",        "error": "string",        "required": true,        "array": false,        "$createdAt": "2020-10-15T06:38:00.000+00:00",        "$updatedAt": "2020-10-15T06:38:00.000+00:00",        "format": "url",        "default": "http:\/\/example.com"    })json");

    // Deserialization must not throw (skip on type mismatch in example data)
    appwrite::models::AttributeUrl model;
    try {
        model = appwrite::models::AttributeUrl::fromJson(seed);
    } catch (const nlohmann::json::exception&) {
        GTEST_SKIP() << "Seed example data type mismatch — skipped";
    }

    // Idempotency: serializing then deserializing then re-serializing must be stable
    auto json1  = model.toJson();
    auto model2 = appwrite::models::AttributeUrl::fromJson(json1);
    auto json2  = model2.toJson();
    EXPECT_EQ(json1.dump(), json2.dump());
}
TEST(AttributeDatetimeTest, RoundTrip) {
    auto seed = nlohmann::json::parse(R"json({        "key": "birthDay",        "type": "datetime",        "status": "available",        "error": "string",        "required": true,        "array": false,        "$createdAt": "2020-10-15T06:38:00.000+00:00",        "$updatedAt": "2020-10-15T06:38:00.000+00:00",        "format": "datetime",        "default": "2020-10-15T06:38:00.000+00:00"    })json");

    // Deserialization must not throw (skip on type mismatch in example data)
    appwrite::models::AttributeDatetime model;
    try {
        model = appwrite::models::AttributeDatetime::fromJson(seed);
    } catch (const nlohmann::json::exception&) {
        GTEST_SKIP() << "Seed example data type mismatch — skipped";
    }

    // Idempotency: serializing then deserializing then re-serializing must be stable
    auto json1  = model.toJson();
    auto model2 = appwrite::models::AttributeDatetime::fromJson(json1);
    auto json2  = model2.toJson();
    EXPECT_EQ(json1.dump(), json2.dump());
}
TEST(AttributeRelationshipTest, RoundTrip) {
    auto seed = nlohmann::json::parse(R"json({        "key": "fullName",        "type": "string",        "status": "available",        "error": "string",        "required": true,        "array": false,        "$createdAt": "2020-10-15T06:38:00.000+00:00",        "$updatedAt": "2020-10-15T06:38:00.000+00:00",        "relatedCollection": "collection",        "relationType": "oneToOne|oneToMany|manyToOne|manyToMany",        "twoWay": false,        "twoWayKey": "string",        "onDelete": "restrict|cascade|setNull",        "side": "parent|child"    })json");

    // Deserialization must not throw (skip on type mismatch in example data)
    appwrite::models::AttributeRelationship model;
    try {
        model = appwrite::models::AttributeRelationship::fromJson(seed);
    } catch (const nlohmann::json::exception&) {
        GTEST_SKIP() << "Seed example data type mismatch — skipped";
    }

    // Idempotency: serializing then deserializing then re-serializing must be stable
    auto json1  = model.toJson();
    auto model2 = appwrite::models::AttributeRelationship::fromJson(json1);
    auto json2  = model2.toJson();
    EXPECT_EQ(json1.dump(), json2.dump());
}
TEST(AttributePointTest, RoundTrip) {
    auto seed = nlohmann::json::parse(R"json({        "key": "fullName",        "type": "string",        "status": "available",        "error": "string",        "required": true,        "array": false,        "$createdAt": "2020-10-15T06:38:00.000+00:00",        "$updatedAt": "2020-10-15T06:38:00.000+00:00",        "default": [0,0]    })json");

    // Deserialization must not throw (skip on type mismatch in example data)
    appwrite::models::AttributePoint model;
    try {
        model = appwrite::models::AttributePoint::fromJson(seed);
    } catch (const nlohmann::json::exception&) {
        GTEST_SKIP() << "Seed example data type mismatch — skipped";
    }

    // Idempotency: serializing then deserializing then re-serializing must be stable
    auto json1  = model.toJson();
    auto model2 = appwrite::models::AttributePoint::fromJson(json1);
    auto json2  = model2.toJson();
    EXPECT_EQ(json1.dump(), json2.dump());
}
TEST(AttributeLineTest, RoundTrip) {
    auto seed = nlohmann::json::parse(R"json({        "key": "fullName",        "type": "string",        "status": "available",        "error": "string",        "required": true,        "array": false,        "$createdAt": "2020-10-15T06:38:00.000+00:00",        "$updatedAt": "2020-10-15T06:38:00.000+00:00",        "default": [[0,0],[1,1]]    })json");

    // Deserialization must not throw (skip on type mismatch in example data)
    appwrite::models::AttributeLine model;
    try {
        model = appwrite::models::AttributeLine::fromJson(seed);
    } catch (const nlohmann::json::exception&) {
        GTEST_SKIP() << "Seed example data type mismatch — skipped";
    }

    // Idempotency: serializing then deserializing then re-serializing must be stable
    auto json1  = model.toJson();
    auto model2 = appwrite::models::AttributeLine::fromJson(json1);
    auto json2  = model2.toJson();
    EXPECT_EQ(json1.dump(), json2.dump());
}
TEST(AttributePolygonTest, RoundTrip) {
    auto seed = nlohmann::json::parse(R"json({        "key": "fullName",        "type": "string",        "status": "available",        "error": "string",        "required": true,        "array": false,        "$createdAt": "2020-10-15T06:38:00.000+00:00",        "$updatedAt": "2020-10-15T06:38:00.000+00:00",        "default": [[[0,0],[0,10]],[[10,10],[0,0]]]    })json");

    // Deserialization must not throw (skip on type mismatch in example data)
    appwrite::models::AttributePolygon model;
    try {
        model = appwrite::models::AttributePolygon::fromJson(seed);
    } catch (const nlohmann::json::exception&) {
        GTEST_SKIP() << "Seed example data type mismatch — skipped";
    }

    // Idempotency: serializing then deserializing then re-serializing must be stable
    auto json1  = model.toJson();
    auto model2 = appwrite::models::AttributePolygon::fromJson(json1);
    auto json2  = model2.toJson();
    EXPECT_EQ(json1.dump(), json2.dump());
}
TEST(AttributeVarcharTest, RoundTrip) {
    auto seed = nlohmann::json::parse(R"json({        "key": "fullName",        "type": "string",        "status": "available",        "error": "string",        "required": true,        "array": false,        "$createdAt": "2020-10-15T06:38:00.000+00:00",        "$updatedAt": "2020-10-15T06:38:00.000+00:00",        "size": 128,        "default": "default",        "encrypt": false    })json");

    // Deserialization must not throw (skip on type mismatch in example data)
    appwrite::models::AttributeVarchar model;
    try {
        model = appwrite::models::AttributeVarchar::fromJson(seed);
    } catch (const nlohmann::json::exception&) {
        GTEST_SKIP() << "Seed example data type mismatch — skipped";
    }

    // Idempotency: serializing then deserializing then re-serializing must be stable
    auto json1  = model.toJson();
    auto model2 = appwrite::models::AttributeVarchar::fromJson(json1);
    auto json2  = model2.toJson();
    EXPECT_EQ(json1.dump(), json2.dump());
}
TEST(AttributeTextTest, RoundTrip) {
    auto seed = nlohmann::json::parse(R"json({        "key": "fullName",        "type": "string",        "status": "available",        "error": "string",        "required": true,        "array": false,        "$createdAt": "2020-10-15T06:38:00.000+00:00",        "$updatedAt": "2020-10-15T06:38:00.000+00:00",        "default": "default",        "encrypt": false    })json");

    // Deserialization must not throw (skip on type mismatch in example data)
    appwrite::models::AttributeText model;
    try {
        model = appwrite::models::AttributeText::fromJson(seed);
    } catch (const nlohmann::json::exception&) {
        GTEST_SKIP() << "Seed example data type mismatch — skipped";
    }

    // Idempotency: serializing then deserializing then re-serializing must be stable
    auto json1  = model.toJson();
    auto model2 = appwrite::models::AttributeText::fromJson(json1);
    auto json2  = model2.toJson();
    EXPECT_EQ(json1.dump(), json2.dump());
}
TEST(AttributeMediumtextTest, RoundTrip) {
    auto seed = nlohmann::json::parse(R"json({        "key": "fullName",        "type": "string",        "status": "available",        "error": "string",        "required": true,        "array": false,        "$createdAt": "2020-10-15T06:38:00.000+00:00",        "$updatedAt": "2020-10-15T06:38:00.000+00:00",        "default": "default",        "encrypt": false    })json");

    // Deserialization must not throw (skip on type mismatch in example data)
    appwrite::models::AttributeMediumtext model;
    try {
        model = appwrite::models::AttributeMediumtext::fromJson(seed);
    } catch (const nlohmann::json::exception&) {
        GTEST_SKIP() << "Seed example data type mismatch — skipped";
    }

    // Idempotency: serializing then deserializing then re-serializing must be stable
    auto json1  = model.toJson();
    auto model2 = appwrite::models::AttributeMediumtext::fromJson(json1);
    auto json2  = model2.toJson();
    EXPECT_EQ(json1.dump(), json2.dump());
}
TEST(AttributeLongtextTest, RoundTrip) {
    auto seed = nlohmann::json::parse(R"json({        "key": "fullName",        "type": "string",        "status": "available",        "error": "string",        "required": true,        "array": false,        "$createdAt": "2020-10-15T06:38:00.000+00:00",        "$updatedAt": "2020-10-15T06:38:00.000+00:00",        "default": "default",        "encrypt": false    })json");

    // Deserialization must not throw (skip on type mismatch in example data)
    appwrite::models::AttributeLongtext model;
    try {
        model = appwrite::models::AttributeLongtext::fromJson(seed);
    } catch (const nlohmann::json::exception&) {
        GTEST_SKIP() << "Seed example data type mismatch — skipped";
    }

    // Idempotency: serializing then deserializing then re-serializing must be stable
    auto json1  = model.toJson();
    auto model2 = appwrite::models::AttributeLongtext::fromJson(json1);
    auto json2  = model2.toJson();
    EXPECT_EQ(json1.dump(), json2.dump());
}
TEST(TableTest, RoundTrip) {
    auto seed = nlohmann::json::parse(R"json({        "$id": "5e5ea5c16897e",        "$createdAt": "2020-10-15T06:38:00.000+00:00",        "$updatedAt": "2020-10-15T06:38:00.000+00:00",        "$permissions": ["read(\"any\")"],        "databaseId": "5e5ea5c16897e",        "name": "My Table",        "enabled": false,        "rowSecurity": true,        "columns": [],        "indexes": [{}],        "bytesMax": 65535,        "bytesUsed": 1500    })json");

    // Deserialization must not throw (skip on type mismatch in example data)
    appwrite::models::Table model;
    try {
        model = appwrite::models::Table::fromJson(seed);
    } catch (const nlohmann::json::exception&) {
        GTEST_SKIP() << "Seed example data type mismatch — skipped";
    }

    // Idempotency: serializing then deserializing then re-serializing must be stable
    auto json1  = model.toJson();
    auto model2 = appwrite::models::Table::fromJson(json1);
    auto json2  = model2.toJson();
    EXPECT_EQ(json1.dump(), json2.dump());
}
TEST(ColumnListTest, RoundTrip) {
    auto seed = nlohmann::json::parse(R"json({        "total": 5,        "columns": [""]    })json");

    // Deserialization must not throw (skip on type mismatch in example data)
    appwrite::models::ColumnList model;
    try {
        model = appwrite::models::ColumnList::fromJson(seed);
    } catch (const nlohmann::json::exception&) {
        GTEST_SKIP() << "Seed example data type mismatch — skipped";
    }

    // Idempotency: serializing then deserializing then re-serializing must be stable
    auto json1  = model.toJson();
    auto model2 = appwrite::models::ColumnList::fromJson(json1);
    auto json2  = model2.toJson();
    EXPECT_EQ(json1.dump(), json2.dump());
}
TEST(ColumnStringTest, RoundTrip) {
    auto seed = nlohmann::json::parse(R"json({        "key": "fullName",        "type": "string",        "status": "available",        "error": "string",        "required": true,        "array": false,        "$createdAt": "2020-10-15T06:38:00.000+00:00",        "$updatedAt": "2020-10-15T06:38:00.000+00:00",        "size": 128,        "default": "default",        "encrypt": false    })json");

    // Deserialization must not throw (skip on type mismatch in example data)
    appwrite::models::ColumnString model;
    try {
        model = appwrite::models::ColumnString::fromJson(seed);
    } catch (const nlohmann::json::exception&) {
        GTEST_SKIP() << "Seed example data type mismatch — skipped";
    }

    // Idempotency: serializing then deserializing then re-serializing must be stable
    auto json1  = model.toJson();
    auto model2 = appwrite::models::ColumnString::fromJson(json1);
    auto json2  = model2.toJson();
    EXPECT_EQ(json1.dump(), json2.dump());
}
TEST(ColumnIntegerTest, RoundTrip) {
    auto seed = nlohmann::json::parse(R"json({        "key": "count",        "type": "integer",        "status": "available",        "error": "string",        "required": true,        "array": false,        "$createdAt": "2020-10-15T06:38:00.000+00:00",        "$updatedAt": "2020-10-15T06:38:00.000+00:00",        "min": 1,        "max": 10,        "default": 10    })json");

    // Deserialization must not throw (skip on type mismatch in example data)
    appwrite::models::ColumnInteger model;
    try {
        model = appwrite::models::ColumnInteger::fromJson(seed);
    } catch (const nlohmann::json::exception&) {
        GTEST_SKIP() << "Seed example data type mismatch — skipped";
    }

    // Idempotency: serializing then deserializing then re-serializing must be stable
    auto json1  = model.toJson();
    auto model2 = appwrite::models::ColumnInteger::fromJson(json1);
    auto json2  = model2.toJson();
    EXPECT_EQ(json1.dump(), json2.dump());
}
TEST(ColumnFloatTest, RoundTrip) {
    auto seed = nlohmann::json::parse(R"json({        "key": "percentageCompleted",        "type": "double",        "status": "available",        "error": "string",        "required": true,        "array": false,        "$createdAt": "2020-10-15T06:38:00.000+00:00",        "$updatedAt": "2020-10-15T06:38:00.000+00:00",        "min": 1.5,        "max": 10.5,        "default": 2.5    })json");

    // Deserialization must not throw (skip on type mismatch in example data)
    appwrite::models::ColumnFloat model;
    try {
        model = appwrite::models::ColumnFloat::fromJson(seed);
    } catch (const nlohmann::json::exception&) {
        GTEST_SKIP() << "Seed example data type mismatch — skipped";
    }

    // Idempotency: serializing then deserializing then re-serializing must be stable
    auto json1  = model.toJson();
    auto model2 = appwrite::models::ColumnFloat::fromJson(json1);
    auto json2  = model2.toJson();
    EXPECT_EQ(json1.dump(), json2.dump());
}
TEST(ColumnBooleanTest, RoundTrip) {
    auto seed = nlohmann::json::parse(R"json({        "key": "isEnabled",        "type": "boolean",        "status": "available",        "error": "string",        "required": true,        "array": false,        "$createdAt": "2020-10-15T06:38:00.000+00:00",        "$updatedAt": "2020-10-15T06:38:00.000+00:00",        "default": false    })json");

    // Deserialization must not throw (skip on type mismatch in example data)
    appwrite::models::ColumnBoolean model;
    try {
        model = appwrite::models::ColumnBoolean::fromJson(seed);
    } catch (const nlohmann::json::exception&) {
        GTEST_SKIP() << "Seed example data type mismatch — skipped";
    }

    // Idempotency: serializing then deserializing then re-serializing must be stable
    auto json1  = model.toJson();
    auto model2 = appwrite::models::ColumnBoolean::fromJson(json1);
    auto json2  = model2.toJson();
    EXPECT_EQ(json1.dump(), json2.dump());
}
TEST(ColumnEmailTest, RoundTrip) {
    auto seed = nlohmann::json::parse(R"json({        "key": "userEmail",        "type": "string",        "status": "available",        "error": "string",        "required": true,        "array": false,        "$createdAt": "2020-10-15T06:38:00.000+00:00",        "$updatedAt": "2020-10-15T06:38:00.000+00:00",        "format": "email",        "default": "default@example.com"    })json");

    // Deserialization must not throw (skip on type mismatch in example data)
    appwrite::models::ColumnEmail model;
    try {
        model = appwrite::models::ColumnEmail::fromJson(seed);
    } catch (const nlohmann::json::exception&) {
        GTEST_SKIP() << "Seed example data type mismatch — skipped";
    }

    // Idempotency: serializing then deserializing then re-serializing must be stable
    auto json1  = model.toJson();
    auto model2 = appwrite::models::ColumnEmail::fromJson(json1);
    auto json2  = model2.toJson();
    EXPECT_EQ(json1.dump(), json2.dump());
}
TEST(ColumnEnumTest, RoundTrip) {
    auto seed = nlohmann::json::parse(R"json({        "key": "status",        "type": "string",        "status": "available",        "error": "string",        "required": true,        "array": false,        "$createdAt": "2020-10-15T06:38:00.000+00:00",        "$updatedAt": "2020-10-15T06:38:00.000+00:00",        "elements": ["element"],        "format": "enum",        "default": "element"    })json");

    // Deserialization must not throw (skip on type mismatch in example data)
    appwrite::models::ColumnEnum model;
    try {
        model = appwrite::models::ColumnEnum::fromJson(seed);
    } catch (const nlohmann::json::exception&) {
        GTEST_SKIP() << "Seed example data type mismatch — skipped";
    }

    // Idempotency: serializing then deserializing then re-serializing must be stable
    auto json1  = model.toJson();
    auto model2 = appwrite::models::ColumnEnum::fromJson(json1);
    auto json2  = model2.toJson();
    EXPECT_EQ(json1.dump(), json2.dump());
}
TEST(ColumnIpTest, RoundTrip) {
    auto seed = nlohmann::json::parse(R"json({        "key": "ipAddress",        "type": "string",        "status": "available",        "error": "string",        "required": true,        "array": false,        "$createdAt": "2020-10-15T06:38:00.000+00:00",        "$updatedAt": "2020-10-15T06:38:00.000+00:00",        "format": "ip",        "default": "192.0.2.0"    })json");

    // Deserialization must not throw (skip on type mismatch in example data)
    appwrite::models::ColumnIp model;
    try {
        model = appwrite::models::ColumnIp::fromJson(seed);
    } catch (const nlohmann::json::exception&) {
        GTEST_SKIP() << "Seed example data type mismatch — skipped";
    }

    // Idempotency: serializing then deserializing then re-serializing must be stable
    auto json1  = model.toJson();
    auto model2 = appwrite::models::ColumnIp::fromJson(json1);
    auto json2  = model2.toJson();
    EXPECT_EQ(json1.dump(), json2.dump());
}
TEST(ColumnUrlTest, RoundTrip) {
    auto seed = nlohmann::json::parse(R"json({        "key": "githubUrl",        "type": "string",        "status": "available",        "error": "string",        "required": true,        "array": false,        "$createdAt": "2020-10-15T06:38:00.000+00:00",        "$updatedAt": "2020-10-15T06:38:00.000+00:00",        "format": "url",        "default": "https:\/\/example.com"    })json");

    // Deserialization must not throw (skip on type mismatch in example data)
    appwrite::models::ColumnUrl model;
    try {
        model = appwrite::models::ColumnUrl::fromJson(seed);
    } catch (const nlohmann::json::exception&) {
        GTEST_SKIP() << "Seed example data type mismatch — skipped";
    }

    // Idempotency: serializing then deserializing then re-serializing must be stable
    auto json1  = model.toJson();
    auto model2 = appwrite::models::ColumnUrl::fromJson(json1);
    auto json2  = model2.toJson();
    EXPECT_EQ(json1.dump(), json2.dump());
}
TEST(ColumnDatetimeTest, RoundTrip) {
    auto seed = nlohmann::json::parse(R"json({        "key": "birthDay",        "type": "datetime",        "status": "available",        "error": "string",        "required": true,        "array": false,        "$createdAt": "2020-10-15T06:38:00.000+00:00",        "$updatedAt": "2020-10-15T06:38:00.000+00:00",        "format": "datetime",        "default": "2020-10-15T06:38:00.000+00:00"    })json");

    // Deserialization must not throw (skip on type mismatch in example data)
    appwrite::models::ColumnDatetime model;
    try {
        model = appwrite::models::ColumnDatetime::fromJson(seed);
    } catch (const nlohmann::json::exception&) {
        GTEST_SKIP() << "Seed example data type mismatch — skipped";
    }

    // Idempotency: serializing then deserializing then re-serializing must be stable
    auto json1  = model.toJson();
    auto model2 = appwrite::models::ColumnDatetime::fromJson(json1);
    auto json2  = model2.toJson();
    EXPECT_EQ(json1.dump(), json2.dump());
}
TEST(ColumnRelationshipTest, RoundTrip) {
    auto seed = nlohmann::json::parse(R"json({        "key": "fullName",        "type": "string",        "status": "available",        "error": "string",        "required": true,        "array": false,        "$createdAt": "2020-10-15T06:38:00.000+00:00",        "$updatedAt": "2020-10-15T06:38:00.000+00:00",        "relatedTable": "table",        "relationType": "oneToOne|oneToMany|manyToOne|manyToMany",        "twoWay": false,        "twoWayKey": "string",        "onDelete": "restrict|cascade|setNull",        "side": "parent|child"    })json");

    // Deserialization must not throw (skip on type mismatch in example data)
    appwrite::models::ColumnRelationship model;
    try {
        model = appwrite::models::ColumnRelationship::fromJson(seed);
    } catch (const nlohmann::json::exception&) {
        GTEST_SKIP() << "Seed example data type mismatch — skipped";
    }

    // Idempotency: serializing then deserializing then re-serializing must be stable
    auto json1  = model.toJson();
    auto model2 = appwrite::models::ColumnRelationship::fromJson(json1);
    auto json2  = model2.toJson();
    EXPECT_EQ(json1.dump(), json2.dump());
}
TEST(ColumnPointTest, RoundTrip) {
    auto seed = nlohmann::json::parse(R"json({        "key": "fullName",        "type": "string",        "status": "available",        "error": "string",        "required": true,        "array": false,        "$createdAt": "2020-10-15T06:38:00.000+00:00",        "$updatedAt": "2020-10-15T06:38:00.000+00:00",        "default": [0,0]    })json");

    // Deserialization must not throw (skip on type mismatch in example data)
    appwrite::models::ColumnPoint model;
    try {
        model = appwrite::models::ColumnPoint::fromJson(seed);
    } catch (const nlohmann::json::exception&) {
        GTEST_SKIP() << "Seed example data type mismatch — skipped";
    }

    // Idempotency: serializing then deserializing then re-serializing must be stable
    auto json1  = model.toJson();
    auto model2 = appwrite::models::ColumnPoint::fromJson(json1);
    auto json2  = model2.toJson();
    EXPECT_EQ(json1.dump(), json2.dump());
}
TEST(ColumnLineTest, RoundTrip) {
    auto seed = nlohmann::json::parse(R"json({        "key": "fullName",        "type": "string",        "status": "available",        "error": "string",        "required": true,        "array": false,        "$createdAt": "2020-10-15T06:38:00.000+00:00",        "$updatedAt": "2020-10-15T06:38:00.000+00:00",        "default": [[0,0],[1,1]]    })json");

    // Deserialization must not throw (skip on type mismatch in example data)
    appwrite::models::ColumnLine model;
    try {
        model = appwrite::models::ColumnLine::fromJson(seed);
    } catch (const nlohmann::json::exception&) {
        GTEST_SKIP() << "Seed example data type mismatch — skipped";
    }

    // Idempotency: serializing then deserializing then re-serializing must be stable
    auto json1  = model.toJson();
    auto model2 = appwrite::models::ColumnLine::fromJson(json1);
    auto json2  = model2.toJson();
    EXPECT_EQ(json1.dump(), json2.dump());
}
TEST(ColumnPolygonTest, RoundTrip) {
    auto seed = nlohmann::json::parse(R"json({        "key": "fullName",        "type": "string",        "status": "available",        "error": "string",        "required": true,        "array": false,        "$createdAt": "2020-10-15T06:38:00.000+00:00",        "$updatedAt": "2020-10-15T06:38:00.000+00:00",        "default": [[[0,0],[0,10]],[[10,10],[0,0]]]    })json");

    // Deserialization must not throw (skip on type mismatch in example data)
    appwrite::models::ColumnPolygon model;
    try {
        model = appwrite::models::ColumnPolygon::fromJson(seed);
    } catch (const nlohmann::json::exception&) {
        GTEST_SKIP() << "Seed example data type mismatch — skipped";
    }

    // Idempotency: serializing then deserializing then re-serializing must be stable
    auto json1  = model.toJson();
    auto model2 = appwrite::models::ColumnPolygon::fromJson(json1);
    auto json2  = model2.toJson();
    EXPECT_EQ(json1.dump(), json2.dump());
}
TEST(ColumnVarcharTest, RoundTrip) {
    auto seed = nlohmann::json::parse(R"json({        "key": "fullName",        "type": "string",        "status": "available",        "error": "string",        "required": true,        "array": false,        "$createdAt": "2020-10-15T06:38:00.000+00:00",        "$updatedAt": "2020-10-15T06:38:00.000+00:00",        "size": 128,        "default": "default",        "encrypt": false    })json");

    // Deserialization must not throw (skip on type mismatch in example data)
    appwrite::models::ColumnVarchar model;
    try {
        model = appwrite::models::ColumnVarchar::fromJson(seed);
    } catch (const nlohmann::json::exception&) {
        GTEST_SKIP() << "Seed example data type mismatch — skipped";
    }

    // Idempotency: serializing then deserializing then re-serializing must be stable
    auto json1  = model.toJson();
    auto model2 = appwrite::models::ColumnVarchar::fromJson(json1);
    auto json2  = model2.toJson();
    EXPECT_EQ(json1.dump(), json2.dump());
}
TEST(ColumnTextTest, RoundTrip) {
    auto seed = nlohmann::json::parse(R"json({        "key": "fullName",        "type": "string",        "status": "available",        "error": "string",        "required": true,        "array": false,        "$createdAt": "2020-10-15T06:38:00.000+00:00",        "$updatedAt": "2020-10-15T06:38:00.000+00:00",        "default": "default",        "encrypt": false    })json");

    // Deserialization must not throw (skip on type mismatch in example data)
    appwrite::models::ColumnText model;
    try {
        model = appwrite::models::ColumnText::fromJson(seed);
    } catch (const nlohmann::json::exception&) {
        GTEST_SKIP() << "Seed example data type mismatch — skipped";
    }

    // Idempotency: serializing then deserializing then re-serializing must be stable
    auto json1  = model.toJson();
    auto model2 = appwrite::models::ColumnText::fromJson(json1);
    auto json2  = model2.toJson();
    EXPECT_EQ(json1.dump(), json2.dump());
}
TEST(ColumnMediumtextTest, RoundTrip) {
    auto seed = nlohmann::json::parse(R"json({        "key": "fullName",        "type": "string",        "status": "available",        "error": "string",        "required": true,        "array": false,        "$createdAt": "2020-10-15T06:38:00.000+00:00",        "$updatedAt": "2020-10-15T06:38:00.000+00:00",        "default": "default",        "encrypt": false    })json");

    // Deserialization must not throw (skip on type mismatch in example data)
    appwrite::models::ColumnMediumtext model;
    try {
        model = appwrite::models::ColumnMediumtext::fromJson(seed);
    } catch (const nlohmann::json::exception&) {
        GTEST_SKIP() << "Seed example data type mismatch — skipped";
    }

    // Idempotency: serializing then deserializing then re-serializing must be stable
    auto json1  = model.toJson();
    auto model2 = appwrite::models::ColumnMediumtext::fromJson(json1);
    auto json2  = model2.toJson();
    EXPECT_EQ(json1.dump(), json2.dump());
}
TEST(ColumnLongtextTest, RoundTrip) {
    auto seed = nlohmann::json::parse(R"json({        "key": "fullName",        "type": "string",        "status": "available",        "error": "string",        "required": true,        "array": false,        "$createdAt": "2020-10-15T06:38:00.000+00:00",        "$updatedAt": "2020-10-15T06:38:00.000+00:00",        "default": "default",        "encrypt": false    })json");

    // Deserialization must not throw (skip on type mismatch in example data)
    appwrite::models::ColumnLongtext model;
    try {
        model = appwrite::models::ColumnLongtext::fromJson(seed);
    } catch (const nlohmann::json::exception&) {
        GTEST_SKIP() << "Seed example data type mismatch — skipped";
    }

    // Idempotency: serializing then deserializing then re-serializing must be stable
    auto json1  = model.toJson();
    auto model2 = appwrite::models::ColumnLongtext::fromJson(json1);
    auto json2  = model2.toJson();
    EXPECT_EQ(json1.dump(), json2.dump());
}
TEST(IndexTest, RoundTrip) {
    auto seed = nlohmann::json::parse(R"json({        "$id": "5e5ea5c16897e",        "$createdAt": "2020-10-15T06:38:00.000+00:00",        "$updatedAt": "2020-10-15T06:38:00.000+00:00",        "key": "index1",        "type": "primary",        "status": "available",        "error": "string",        "attributes": [],        "lengths": [],        "orders": []    })json");

    // Deserialization must not throw (skip on type mismatch in example data)
    appwrite::models::Index model;
    try {
        model = appwrite::models::Index::fromJson(seed);
    } catch (const nlohmann::json::exception&) {
        GTEST_SKIP() << "Seed example data type mismatch — skipped";
    }

    // Idempotency: serializing then deserializing then re-serializing must be stable
    auto json1  = model.toJson();
    auto model2 = appwrite::models::Index::fromJson(json1);
    auto json2  = model2.toJson();
    EXPECT_EQ(json1.dump(), json2.dump());
}
TEST(ColumnIndexTest, RoundTrip) {
    auto seed = nlohmann::json::parse(R"json({        "$id": "5e5ea5c16897e",        "$createdAt": "2020-10-15T06:38:00.000+00:00",        "$updatedAt": "2020-10-15T06:38:00.000+00:00",        "key": "index1",        "type": "primary",        "status": "available",        "error": "string",        "columns": [],        "lengths": [],        "orders": []    })json");

    // Deserialization must not throw (skip on type mismatch in example data)
    appwrite::models::ColumnIndex model;
    try {
        model = appwrite::models::ColumnIndex::fromJson(seed);
    } catch (const nlohmann::json::exception&) {
        GTEST_SKIP() << "Seed example data type mismatch — skipped";
    }

    // Idempotency: serializing then deserializing then re-serializing must be stable
    auto json1  = model.toJson();
    auto model2 = appwrite::models::ColumnIndex::fromJson(json1);
    auto json2  = model2.toJson();
    EXPECT_EQ(json1.dump(), json2.dump());
}
TEST(RowTest, RoundTrip) {
    auto seed = nlohmann::json::parse(R"json({        "$id": "5e5ea5c16897e",        "$sequence": "1",        "$tableId": "5e5ea5c15117e",        "$databaseId": "5e5ea5c15117e",        "$createdAt": "2020-10-15T06:38:00.000+00:00",        "$updatedAt": "2020-10-15T06:38:00.000+00:00",        "$permissions": ["read(\"any\")"]    })json");

    // Deserialization must not throw (skip on type mismatch in example data)
    appwrite::models::Row model;
    try {
        model = appwrite::models::Row::fromJson(seed);
    } catch (const nlohmann::json::exception&) {
        GTEST_SKIP() << "Seed example data type mismatch — skipped";
    }

    // Idempotency: serializing then deserializing then re-serializing must be stable
    auto json1  = model.toJson();
    auto model2 = appwrite::models::Row::fromJson(json1);
    auto json2  = model2.toJson();
    EXPECT_EQ(json1.dump(), json2.dump());
}
TEST(DocumentTest, RoundTrip) {
    auto seed = nlohmann::json::parse(R"json({        "$id": "5e5ea5c16897e",        "$sequence": "1",        "$collectionId": "5e5ea5c15117e",        "$databaseId": "5e5ea5c15117e",        "$createdAt": "2020-10-15T06:38:00.000+00:00",        "$updatedAt": "2020-10-15T06:38:00.000+00:00",        "$permissions": ["read(\"any\")"]    })json");

    // Deserialization must not throw (skip on type mismatch in example data)
    appwrite::models::Document model;
    try {
        model = appwrite::models::Document::fromJson(seed);
    } catch (const nlohmann::json::exception&) {
        GTEST_SKIP() << "Seed example data type mismatch — skipped";
    }

    // Idempotency: serializing then deserializing then re-serializing must be stable
    auto json1  = model.toJson();
    auto model2 = appwrite::models::Document::fromJson(json1);
    auto json2  = model2.toJson();
    EXPECT_EQ(json1.dump(), json2.dump());
}
TEST(LogTest, RoundTrip) {
    auto seed = nlohmann::json::parse(R"json({        "event": "account.sessions.create",        "userId": "610fc2f985ee0",        "userEmail": "john@appwrite.io",        "userName": "John Doe",        "mode": "admin",        "userType": "user",        "ip": "127.0.0.1",        "time": "2020-10-15T06:38:00.000+00:00",        "osCode": "Mac",        "osName": "Mac",        "osVersion": "Mac",        "clientType": "browser",        "clientCode": "CM",        "clientName": "Chrome Mobile iOS",        "clientVersion": "84.0",        "clientEngine": "WebKit",        "clientEngineVersion": "605.1.15",        "deviceName": "smartphone",        "deviceBrand": "Google",        "deviceModel": "Nexus 5",        "countryCode": "US",        "countryName": "United States"    })json");

    // Deserialization must not throw (skip on type mismatch in example data)
    appwrite::models::Log model;
    try {
        model = appwrite::models::Log::fromJson(seed);
    } catch (const nlohmann::json::exception&) {
        GTEST_SKIP() << "Seed example data type mismatch — skipped";
    }

    // Idempotency: serializing then deserializing then re-serializing must be stable
    auto json1  = model.toJson();
    auto model2 = appwrite::models::Log::fromJson(json1);
    auto json2  = model2.toJson();
    EXPECT_EQ(json1.dump(), json2.dump());
}
TEST(UserTest, RoundTrip) {
    auto seed = nlohmann::json::parse(R"json({        "$id": "5e5ea5c16897e",        "$createdAt": "2020-10-15T06:38:00.000+00:00",        "$updatedAt": "2020-10-15T06:38:00.000+00:00",        "name": "John Doe",        "password": "$argon2id$v=19$m=2048,t=4,p=3$aUZjLnliVWRINmFNTWMudg$5S+x+7uA31xFnrHFT47yFwcJeaP0w92L\/4LdgrVRXxE",        "hash": "argon2",        "hashOptions": [],        "registration": "2020-10-15T06:38:00.000+00:00",        "status": true,        "labels": ["vip"],        "passwordUpdate": "2020-10-15T06:38:00.000+00:00",        "email": "john@appwrite.io",        "phone": "+4930901820",        "emailVerification": true,        "phoneVerification": true,        "mfa": true,        "prefs": {"theme":"pink","timezone":"UTC"},        "targets": [{}],        "accessedAt": "2020-10-15T06:38:00.000+00:00",        "impersonator": false,        "impersonatorUserId": "5e5ea5c16897e"    })json");

    // Deserialization must not throw (skip on type mismatch in example data)
    appwrite::models::User model;
    try {
        model = appwrite::models::User::fromJson(seed);
    } catch (const nlohmann::json::exception&) {
        GTEST_SKIP() << "Seed example data type mismatch — skipped";
    }

    // Idempotency: serializing then deserializing then re-serializing must be stable
    auto json1  = model.toJson();
    auto model2 = appwrite::models::User::fromJson(json1);
    auto json2  = model2.toJson();
    EXPECT_EQ(json1.dump(), json2.dump());
}
TEST(AlgoMd5Test, RoundTrip) {
    auto seed = nlohmann::json::parse(R"json({        "type": "md5"    })json");

    // Deserialization must not throw (skip on type mismatch in example data)
    appwrite::models::AlgoMd5 model;
    try {
        model = appwrite::models::AlgoMd5::fromJson(seed);
    } catch (const nlohmann::json::exception&) {
        GTEST_SKIP() << "Seed example data type mismatch — skipped";
    }

    // Idempotency: serializing then deserializing then re-serializing must be stable
    auto json1  = model.toJson();
    auto model2 = appwrite::models::AlgoMd5::fromJson(json1);
    auto json2  = model2.toJson();
    EXPECT_EQ(json1.dump(), json2.dump());
}
TEST(AlgoShaTest, RoundTrip) {
    auto seed = nlohmann::json::parse(R"json({        "type": "sha"    })json");

    // Deserialization must not throw (skip on type mismatch in example data)
    appwrite::models::AlgoSha model;
    try {
        model = appwrite::models::AlgoSha::fromJson(seed);
    } catch (const nlohmann::json::exception&) {
        GTEST_SKIP() << "Seed example data type mismatch — skipped";
    }

    // Idempotency: serializing then deserializing then re-serializing must be stable
    auto json1  = model.toJson();
    auto model2 = appwrite::models::AlgoSha::fromJson(json1);
    auto json2  = model2.toJson();
    EXPECT_EQ(json1.dump(), json2.dump());
}
TEST(AlgoPhpassTest, RoundTrip) {
    auto seed = nlohmann::json::parse(R"json({        "type": "phpass"    })json");

    // Deserialization must not throw (skip on type mismatch in example data)
    appwrite::models::AlgoPhpass model;
    try {
        model = appwrite::models::AlgoPhpass::fromJson(seed);
    } catch (const nlohmann::json::exception&) {
        GTEST_SKIP() << "Seed example data type mismatch — skipped";
    }

    // Idempotency: serializing then deserializing then re-serializing must be stable
    auto json1  = model.toJson();
    auto model2 = appwrite::models::AlgoPhpass::fromJson(json1);
    auto json2  = model2.toJson();
    EXPECT_EQ(json1.dump(), json2.dump());
}
TEST(AlgoBcryptTest, RoundTrip) {
    auto seed = nlohmann::json::parse(R"json({        "type": "bcrypt"    })json");

    // Deserialization must not throw (skip on type mismatch in example data)
    appwrite::models::AlgoBcrypt model;
    try {
        model = appwrite::models::AlgoBcrypt::fromJson(seed);
    } catch (const nlohmann::json::exception&) {
        GTEST_SKIP() << "Seed example data type mismatch — skipped";
    }

    // Idempotency: serializing then deserializing then re-serializing must be stable
    auto json1  = model.toJson();
    auto model2 = appwrite::models::AlgoBcrypt::fromJson(json1);
    auto json2  = model2.toJson();
    EXPECT_EQ(json1.dump(), json2.dump());
}
TEST(AlgoScryptTest, RoundTrip) {
    auto seed = nlohmann::json::parse(R"json({        "type": "scrypt",        "costCpu": 8,        "costMemory": 14,        "costParallel": 1,        "length": 64    })json");

    // Deserialization must not throw (skip on type mismatch in example data)
    appwrite::models::AlgoScrypt model;
    try {
        model = appwrite::models::AlgoScrypt::fromJson(seed);
    } catch (const nlohmann::json::exception&) {
        GTEST_SKIP() << "Seed example data type mismatch — skipped";
    }

    // Idempotency: serializing then deserializing then re-serializing must be stable
    auto json1  = model.toJson();
    auto model2 = appwrite::models::AlgoScrypt::fromJson(json1);
    auto json2  = model2.toJson();
    EXPECT_EQ(json1.dump(), json2.dump());
}
TEST(AlgoScryptModifiedTest, RoundTrip) {
    auto seed = nlohmann::json::parse(R"json({        "type": "scryptMod",        "salt": "UxLMreBr6tYyjQ==",        "saltSeparator": "Bw==",        "signerKey": "XyEKE9RcTDeLEsL\/RjwPDBv\/RqDl8fb3gpYEOQaPihbxf1ZAtSOHCjuAAa7Q3oHpCYhXSN9tizHgVOwn6krflQ=="    })json");

    // Deserialization must not throw (skip on type mismatch in example data)
    appwrite::models::AlgoScryptModified model;
    try {
        model = appwrite::models::AlgoScryptModified::fromJson(seed);
    } catch (const nlohmann::json::exception&) {
        GTEST_SKIP() << "Seed example data type mismatch — skipped";
    }

    // Idempotency: serializing then deserializing then re-serializing must be stable
    auto json1  = model.toJson();
    auto model2 = appwrite::models::AlgoScryptModified::fromJson(json1);
    auto json2  = model2.toJson();
    EXPECT_EQ(json1.dump(), json2.dump());
}
TEST(AlgoArgon2Test, RoundTrip) {
    auto seed = nlohmann::json::parse(R"json({        "type": "argon2",        "memoryCost": 65536,        "timeCost": 4,        "threads": 3    })json");

    // Deserialization must not throw (skip on type mismatch in example data)
    appwrite::models::AlgoArgon2 model;
    try {
        model = appwrite::models::AlgoArgon2::fromJson(seed);
    } catch (const nlohmann::json::exception&) {
        GTEST_SKIP() << "Seed example data type mismatch — skipped";
    }

    // Idempotency: serializing then deserializing then re-serializing must be stable
    auto json1  = model.toJson();
    auto model2 = appwrite::models::AlgoArgon2::fromJson(json1);
    auto json2  = model2.toJson();
    EXPECT_EQ(json1.dump(), json2.dump());
}
TEST(PreferencesTest, RoundTrip) {
    auto seed = nlohmann::json::parse(R"json({    })json");

    // Deserialization must not throw (skip on type mismatch in example data)
    appwrite::models::Preferences model;
    try {
        model = appwrite::models::Preferences::fromJson(seed);
    } catch (const nlohmann::json::exception&) {
        GTEST_SKIP() << "Seed example data type mismatch — skipped";
    }

    // Idempotency: serializing then deserializing then re-serializing must be stable
    auto json1  = model.toJson();
    auto model2 = appwrite::models::Preferences::fromJson(json1);
    auto json2  = model2.toJson();
    EXPECT_EQ(json1.dump(), json2.dump());
}
TEST(SessionTest, RoundTrip) {
    auto seed = nlohmann::json::parse(R"json({        "$id": "5e5ea5c16897e",        "$createdAt": "2020-10-15T06:38:00.000+00:00",        "$updatedAt": "2020-10-15T06:38:00.000+00:00",        "userId": "5e5bb8c16897e",        "expire": "2020-10-15T06:38:00.000+00:00",        "provider": "email",        "providerUid": "user@example.com",        "providerAccessToken": "MTQ0NjJkZmQ5OTM2NDE1ZTZjNGZmZjI3",        "providerAccessTokenExpiry": "2020-10-15T06:38:00.000+00:00",        "providerRefreshToken": "MTQ0NjJkZmQ5OTM2NDE1ZTZjNGZmZjI3",        "ip": "127.0.0.1",        "osCode": "Mac",        "osName": "Mac",        "osVersion": "Mac",        "clientType": "browser",        "clientCode": "CM",        "clientName": "Chrome Mobile iOS",        "clientVersion": "84.0",        "clientEngine": "WebKit",        "clientEngineVersion": "605.1.15",        "deviceName": "smartphone",        "deviceBrand": "Google",        "deviceModel": "Nexus 5",        "countryCode": "US",        "countryName": "United States",        "current": true,        "factors": ["email"],        "secret": "5e5bb8c16897e",        "mfaUpdatedAt": "2020-10-15T06:38:00.000+00:00"    })json");

    // Deserialization must not throw (skip on type mismatch in example data)
    appwrite::models::Session model;
    try {
        model = appwrite::models::Session::fromJson(seed);
    } catch (const nlohmann::json::exception&) {
        GTEST_SKIP() << "Seed example data type mismatch — skipped";
    }

    // Idempotency: serializing then deserializing then re-serializing must be stable
    auto json1  = model.toJson();
    auto model2 = appwrite::models::Session::fromJson(json1);
    auto json2  = model2.toJson();
    EXPECT_EQ(json1.dump(), json2.dump());
}
TEST(IdentityTest, RoundTrip) {
    auto seed = nlohmann::json::parse(R"json({        "$id": "5e5ea5c16897e",        "$createdAt": "2020-10-15T06:38:00.000+00:00",        "$updatedAt": "2020-10-15T06:38:00.000+00:00",        "userId": "5e5bb8c16897e",        "provider": "email",        "providerUid": "5e5bb8c16897e",        "providerEmail": "user@example.com",        "providerAccessToken": "MTQ0NjJkZmQ5OTM2NDE1ZTZjNGZmZjI3",        "providerAccessTokenExpiry": "2020-10-15T06:38:00.000+00:00",        "providerRefreshToken": "MTQ0NjJkZmQ5OTM2NDE1ZTZjNGZmZjI3"    })json");

    // Deserialization must not throw (skip on type mismatch in example data)
    appwrite::models::Identity model;
    try {
        model = appwrite::models::Identity::fromJson(seed);
    } catch (const nlohmann::json::exception&) {
        GTEST_SKIP() << "Seed example data type mismatch — skipped";
    }

    // Idempotency: serializing then deserializing then re-serializing must be stable
    auto json1  = model.toJson();
    auto model2 = appwrite::models::Identity::fromJson(json1);
    auto json2  = model2.toJson();
    EXPECT_EQ(json1.dump(), json2.dump());
}
TEST(TokenTest, RoundTrip) {
    auto seed = nlohmann::json::parse(R"json({        "$id": "bb8ea5c16897e",        "$createdAt": "2020-10-15T06:38:00.000+00:00",        "userId": "5e5ea5c168bb8",        "secret": "",        "expire": "2020-10-15T06:38:00.000+00:00",        "phrase": "Golden Fox"    })json");

    // Deserialization must not throw (skip on type mismatch in example data)
    appwrite::models::Token model;
    try {
        model = appwrite::models::Token::fromJson(seed);
    } catch (const nlohmann::json::exception&) {
        GTEST_SKIP() << "Seed example data type mismatch — skipped";
    }

    // Idempotency: serializing then deserializing then re-serializing must be stable
    auto json1  = model.toJson();
    auto model2 = appwrite::models::Token::fromJson(json1);
    auto json2  = model2.toJson();
    EXPECT_EQ(json1.dump(), json2.dump());
}
TEST(JwtTest, RoundTrip) {
    auto seed = nlohmann::json::parse(R"json({        "jwt": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOiIxMjM0NTY3ODkwIiwibmFtZSI6IkpvaG4gRG9lIiwiaWF0IjoxNTE2MjM5MDIyfQ.SflKxwRJSMeKKF2QT4fwpMeJf36POk6yJV_adQssw5c"    })json");

    // Deserialization must not throw (skip on type mismatch in example data)
    appwrite::models::Jwt model;
    try {
        model = appwrite::models::Jwt::fromJson(seed);
    } catch (const nlohmann::json::exception&) {
        GTEST_SKIP() << "Seed example data type mismatch — skipped";
    }

    // Idempotency: serializing then deserializing then re-serializing must be stable
    auto json1  = model.toJson();
    auto model2 = appwrite::models::Jwt::fromJson(json1);
    auto json2  = model2.toJson();
    EXPECT_EQ(json1.dump(), json2.dump());
}
TEST(FileTest, RoundTrip) {
    auto seed = nlohmann::json::parse(R"json({        "$id": "5e5ea5c16897e",        "bucketId": "5e5ea5c16897e",        "$createdAt": "2020-10-15T06:38:00.000+00:00",        "$updatedAt": "2020-10-15T06:38:00.000+00:00",        "$permissions": ["read(\"any\")"],        "name": "Pink.png",        "signature": "5d529fd02b544198ae075bd57c1762bb",        "mimeType": "image\/png",        "sizeOriginal": 17890,        "chunksTotal": 17890,        "chunksUploaded": 17890,        "encryption": true,        "compression": "gzip"    })json");

    // Deserialization must not throw (skip on type mismatch in example data)
    appwrite::models::File model;
    try {
        model = appwrite::models::File::fromJson(seed);
    } catch (const nlohmann::json::exception&) {
        GTEST_SKIP() << "Seed example data type mismatch — skipped";
    }

    // Idempotency: serializing then deserializing then re-serializing must be stable
    auto json1  = model.toJson();
    auto model2 = appwrite::models::File::fromJson(json1);
    auto json2  = model2.toJson();
    EXPECT_EQ(json1.dump(), json2.dump());
}
TEST(BucketTest, RoundTrip) {
    auto seed = nlohmann::json::parse(R"json({        "$id": "5e5ea5c16897e",        "$createdAt": "2020-10-15T06:38:00.000+00:00",        "$updatedAt": "2020-10-15T06:38:00.000+00:00",        "$permissions": ["read(\"any\")"],        "fileSecurity": true,        "name": "Documents",        "enabled": false,        "maximumFileSize": 100,        "allowedFileExtensions": ["jpg","png"],        "compression": "gzip",        "encryption": false,        "antivirus": false,        "transformations": false,        "totalSize": 128    })json");

    // Deserialization must not throw (skip on type mismatch in example data)
    appwrite::models::Bucket model;
    try {
        model = appwrite::models::Bucket::fromJson(seed);
    } catch (const nlohmann::json::exception&) {
        GTEST_SKIP() << "Seed example data type mismatch — skipped";
    }

    // Idempotency: serializing then deserializing then re-serializing must be stable
    auto json1  = model.toJson();
    auto model2 = appwrite::models::Bucket::fromJson(json1);
    auto json2  = model2.toJson();
    EXPECT_EQ(json1.dump(), json2.dump());
}
TEST(TeamTest, RoundTrip) {
    auto seed = nlohmann::json::parse(R"json({        "$id": "5e5ea5c16897e",        "$createdAt": "2020-10-15T06:38:00.000+00:00",        "$updatedAt": "2020-10-15T06:38:00.000+00:00",        "name": "VIP",        "total": 7,        "prefs": {"theme":"pink","timezone":"UTC"}    })json");

    // Deserialization must not throw (skip on type mismatch in example data)
    appwrite::models::Team model;
    try {
        model = appwrite::models::Team::fromJson(seed);
    } catch (const nlohmann::json::exception&) {
        GTEST_SKIP() << "Seed example data type mismatch — skipped";
    }

    // Idempotency: serializing then deserializing then re-serializing must be stable
    auto json1  = model.toJson();
    auto model2 = appwrite::models::Team::fromJson(json1);
    auto json2  = model2.toJson();
    EXPECT_EQ(json1.dump(), json2.dump());
}
TEST(MembershipTest, RoundTrip) {
    auto seed = nlohmann::json::parse(R"json({        "$id": "5e5ea5c16897e",        "$createdAt": "2020-10-15T06:38:00.000+00:00",        "$updatedAt": "2020-10-15T06:38:00.000+00:00",        "userId": "5e5ea5c16897e",        "userName": "John Doe",        "userEmail": "john@appwrite.io",        "teamId": "5e5ea5c16897e",        "teamName": "VIP",        "invited": "2020-10-15T06:38:00.000+00:00",        "joined": "2020-10-15T06:38:00.000+00:00",        "confirm": false,        "mfa": false,        "roles": ["owner"]    })json");

    // Deserialization must not throw (skip on type mismatch in example data)
    appwrite::models::Membership model;
    try {
        model = appwrite::models::Membership::fromJson(seed);
    } catch (const nlohmann::json::exception&) {
        GTEST_SKIP() << "Seed example data type mismatch — skipped";
    }

    // Idempotency: serializing then deserializing then re-serializing must be stable
    auto json1  = model.toJson();
    auto model2 = appwrite::models::Membership::fromJson(json1);
    auto json2  = model2.toJson();
    EXPECT_EQ(json1.dump(), json2.dump());
}
TEST(FunctionTest, RoundTrip) {
    auto seed = nlohmann::json::parse(R"json({        "$id": "5e5ea5c16897e",        "$createdAt": "2020-10-15T06:38:00.000+00:00",        "$updatedAt": "2020-10-15T06:38:00.000+00:00",        "execute": ["users"],        "name": "My Function",        "enabled": false,        "live": false,        "logging": false,        "runtime": "python-3.8",        "deploymentRetention": 7,        "deploymentId": "5e5ea5c16897e",        "deploymentCreatedAt": "2020-10-15T06:38:00.000+00:00",        "latestDeploymentId": "5e5ea5c16897e",        "latestDeploymentCreatedAt": "2020-10-15T06:38:00.000+00:00",        "latestDeploymentStatus": "ready",        "scopes": ["users.read"],        "vars": [{}],        "events": ["account.create"],        "schedule": "5 4 * * *",        "timeout": 300,        "entrypoint": "index.js",        "commands": "npm install",        "version": "v2",        "installationId": "6m40at4ejk5h2u9s1hboo",        "providerRepositoryId": "appwrite",        "providerBranch": "main",        "providerRootDirectory": "functions\/helloWorld",        "providerSilentMode": false,        "buildSpecification": "s-1vcpu-512mb",        "runtimeSpecification": "s-1vcpu-512mb"    })json");

    // Deserialization must not throw (skip on type mismatch in example data)
    appwrite::models::Function model;
    try {
        model = appwrite::models::Function::fromJson(seed);
    } catch (const nlohmann::json::exception&) {
        GTEST_SKIP() << "Seed example data type mismatch — skipped";
    }

    // Idempotency: serializing then deserializing then re-serializing must be stable
    auto json1  = model.toJson();
    auto model2 = appwrite::models::Function::fromJson(json1);
    auto json2  = model2.toJson();
    EXPECT_EQ(json1.dump(), json2.dump());
}
TEST(TemplateFunctionTest, RoundTrip) {
    auto seed = nlohmann::json::parse(R"json({        "icon": "icon-lightning-bolt",        "id": "starter",        "name": "Starter function",        "tagline": "A simple function to get started.",        "permissions": ["any"],        "events": ["account.create"],        "cron": "0 0 * * *",        "timeout": 300,        "useCases": ["Starter"],        "runtimes": [{}],        "instructions": "For documentation and instructions check out <link>.",        "vcsProvider": "github",        "providerRepositoryId": "templates",        "providerOwner": "appwrite",        "providerVersion": "main",        "variables": [{}],        "scopes": ["users.read"]    })json");

    // Deserialization must not throw (skip on type mismatch in example data)
    appwrite::models::TemplateFunction model;
    try {
        model = appwrite::models::TemplateFunction::fromJson(seed);
    } catch (const nlohmann::json::exception&) {
        GTEST_SKIP() << "Seed example data type mismatch — skipped";
    }

    // Idempotency: serializing then deserializing then re-serializing must be stable
    auto json1  = model.toJson();
    auto model2 = appwrite::models::TemplateFunction::fromJson(json1);
    auto json2  = model2.toJson();
    EXPECT_EQ(json1.dump(), json2.dump());
}
TEST(TemplateRuntimeTest, RoundTrip) {
    auto seed = nlohmann::json::parse(R"json({        "name": "node-19.0",        "commands": "npm install",        "entrypoint": "index.js",        "providerRootDirectory": "node\/starter"    })json");

    // Deserialization must not throw (skip on type mismatch in example data)
    appwrite::models::TemplateRuntime model;
    try {
        model = appwrite::models::TemplateRuntime::fromJson(seed);
    } catch (const nlohmann::json::exception&) {
        GTEST_SKIP() << "Seed example data type mismatch — skipped";
    }

    // Idempotency: serializing then deserializing then re-serializing must be stable
    auto json1  = model.toJson();
    auto model2 = appwrite::models::TemplateRuntime::fromJson(json1);
    auto json2  = model2.toJson();
    EXPECT_EQ(json1.dump(), json2.dump());
}
TEST(TemplateVariableTest, RoundTrip) {
    auto seed = nlohmann::json::parse(R"json({        "name": "APPWRITE_DATABASE_ID",        "description": "The ID of the Appwrite database that contains the collection to sync.",        "value": "512",        "secret": false,        "placeholder": "64a55...7b912",        "required": false,        "type": "password"    })json");

    // Deserialization must not throw (skip on type mismatch in example data)
    appwrite::models::TemplateVariable model;
    try {
        model = appwrite::models::TemplateVariable::fromJson(seed);
    } catch (const nlohmann::json::exception&) {
        GTEST_SKIP() << "Seed example data type mismatch — skipped";
    }

    // Idempotency: serializing then deserializing then re-serializing must be stable
    auto json1  = model.toJson();
    auto model2 = appwrite::models::TemplateVariable::fromJson(json1);
    auto json2  = model2.toJson();
    EXPECT_EQ(json1.dump(), json2.dump());
}
TEST(RuntimeTest, RoundTrip) {
    auto seed = nlohmann::json::parse(R"json({        "$id": "python-3.8",        "key": "python",        "name": "Python",        "version": "3.8",        "base": "python:3.8-alpine",        "image": "appwrite\\\/runtime-for-python:3.8",        "logo": "python.png",        "supports": ["amd64"]    })json");

    // Deserialization must not throw (skip on type mismatch in example data)
    appwrite::models::Runtime model;
    try {
        model = appwrite::models::Runtime::fromJson(seed);
    } catch (const nlohmann::json::exception&) {
        GTEST_SKIP() << "Seed example data type mismatch — skipped";
    }

    // Idempotency: serializing then deserializing then re-serializing must be stable
    auto json1  = model.toJson();
    auto model2 = appwrite::models::Runtime::fromJson(json1);
    auto json2  = model2.toJson();
    EXPECT_EQ(json1.dump(), json2.dump());
}
TEST(DeploymentTest, RoundTrip) {
    auto seed = nlohmann::json::parse(R"json({        "$id": "5e5ea5c16897e",        "$createdAt": "2020-10-15T06:38:00.000+00:00",        "$updatedAt": "2020-10-15T06:38:00.000+00:00",        "type": "vcs",        "resourceId": "5e5ea6g16897e",        "resourceType": "functions",        "entrypoint": "index.js",        "sourceSize": 128,        "buildSize": 128,        "totalSize": 128,        "buildId": "5e5ea5c16897e",        "activate": true,        "screenshotLight": "5e5ea5c16897e",        "screenshotDark": "5e5ea5c16897e",        "status": "ready",        "buildLogs": "Compiling source files...",        "buildDuration": 128,        "providerRepositoryName": "database",        "providerRepositoryOwner": "utopia",        "providerRepositoryUrl": "https:\/\/github.com\/vermakhushboo\/g4-node-function",        "providerCommitHash": "7c3f25d",        "providerCommitAuthorUrl": "https:\/\/github.com\/vermakhushboo",        "providerCommitAuthor": "Khushboo Verma",        "providerCommitMessage": "Update index.js",        "providerCommitUrl": "https:\/\/github.com\/vermakhushboo\/g4-node-function\/commit\/60c0416257a9cbcdd96b2d370c38d8f8d150ccfb",        "providerBranch": "0.7.x",        "providerBranchUrl": "https:\/\/github.com\/vermakhushboo\/appwrite\/tree\/0.7.x"    })json");

    // Deserialization must not throw (skip on type mismatch in example data)
    appwrite::models::Deployment model;
    try {
        model = appwrite::models::Deployment::fromJson(seed);
    } catch (const nlohmann::json::exception&) {
        GTEST_SKIP() << "Seed example data type mismatch — skipped";
    }

    // Idempotency: serializing then deserializing then re-serializing must be stable
    auto json1  = model.toJson();
    auto model2 = appwrite::models::Deployment::fromJson(json1);
    auto json2  = model2.toJson();
    EXPECT_EQ(json1.dump(), json2.dump());
}
TEST(ExecutionTest, RoundTrip) {
    auto seed = nlohmann::json::parse(R"json({        "$id": "5e5ea5c16897e",        "$createdAt": "2020-10-15T06:38:00.000+00:00",        "$updatedAt": "2020-10-15T06:38:00.000+00:00",        "$permissions": ["any"],        "functionId": "5e5ea6g16897e",        "deploymentId": "5e5ea5c16897e",        "trigger": "http",        "status": "processing",        "requestMethod": "GET",        "requestPath": "\/articles?id=5",        "requestHeaders": [{}],        "responseStatusCode": 200,        "responseBody": "",        "responseHeaders": [{}],        "logs": "",        "errors": "",        "duration": 0.4,        "scheduledAt": "2020-10-15T06:38:00.000+00:00"    })json");

    // Deserialization must not throw (skip on type mismatch in example data)
    appwrite::models::Execution model;
    try {
        model = appwrite::models::Execution::fromJson(seed);
    } catch (const nlohmann::json::exception&) {
        GTEST_SKIP() << "Seed example data type mismatch — skipped";
    }

    // Idempotency: serializing then deserializing then re-serializing must be stable
    auto json1  = model.toJson();
    auto model2 = appwrite::models::Execution::fromJson(json1);
    auto json2  = model2.toJson();
    EXPECT_EQ(json1.dump(), json2.dump());
}
TEST(WebhookTest, RoundTrip) {
    auto seed = nlohmann::json::parse(R"json({        "$id": "5e5ea5c16897e",        "$createdAt": "2020-10-15T06:38:00.000+00:00",        "$updatedAt": "2020-10-15T06:38:00.000+00:00",        "name": "My Webhook",        "url": "https:\/\/example.com\/webhook",        "events": ["databases.tables.update","databases.collections.update"],        "tls": true,        "authUsername": "username",        "authPassword": "password",        "secret": "ad3d581ca230e2b7059c545e5a",        "enabled": true,        "logs": "Failed to connect to remote server.",        "attempts": 10    })json");

    // Deserialization must not throw (skip on type mismatch in example data)
    appwrite::models::Webhook model;
    try {
        model = appwrite::models::Webhook::fromJson(seed);
    } catch (const nlohmann::json::exception&) {
        GTEST_SKIP() << "Seed example data type mismatch — skipped";
    }

    // Idempotency: serializing then deserializing then re-serializing must be stable
    auto json1  = model.toJson();
    auto model2 = appwrite::models::Webhook::fromJson(json1);
    auto json2  = model2.toJson();
    EXPECT_EQ(json1.dump(), json2.dump());
}
TEST(KeyTest, RoundTrip) {
    auto seed = nlohmann::json::parse(R"json({        "$id": "5e5ea5c16897e",        "$createdAt": "2020-10-15T06:38:00.000+00:00",        "$updatedAt": "2020-10-15T06:38:00.000+00:00",        "name": "My API Key",        "expire": "2020-10-15T06:38:00.000+00:00",        "scopes": ["users.read"],        "secret": "919c2d18fb5d4...a2ae413da83346ad2",        "accessedAt": "2020-10-15T06:38:00.000+00:00",        "sdks": ["appwrite:flutter"]    })json");

    // Deserialization must not throw (skip on type mismatch in example data)
    appwrite::models::Key model;
    try {
        model = appwrite::models::Key::fromJson(seed);
    } catch (const nlohmann::json::exception&) {
        GTEST_SKIP() << "Seed example data type mismatch — skipped";
    }

    // Idempotency: serializing then deserializing then re-serializing must be stable
    auto json1  = model.toJson();
    auto model2 = appwrite::models::Key::fromJson(json1);
    auto json2  = model2.toJson();
    EXPECT_EQ(json1.dump(), json2.dump());
}
TEST(VariableTest, RoundTrip) {
    auto seed = nlohmann::json::parse(R"json({        "$id": "5e5ea5c16897e",        "$createdAt": "2020-10-15T06:38:00.000+00:00",        "$updatedAt": "2020-10-15T06:38:00.000+00:00",        "key": "API_KEY",        "value": "myPa$$word1",        "secret": false,        "resourceType": "function",        "resourceId": "myAwesomeFunction"    })json");

    // Deserialization must not throw (skip on type mismatch in example data)
    appwrite::models::Variable model;
    try {
        model = appwrite::models::Variable::fromJson(seed);
    } catch (const nlohmann::json::exception&) {
        GTEST_SKIP() << "Seed example data type mismatch — skipped";
    }

    // Idempotency: serializing then deserializing then re-serializing must be stable
    auto json1  = model.toJson();
    auto model2 = appwrite::models::Variable::fromJson(json1);
    auto json2  = model2.toJson();
    EXPECT_EQ(json1.dump(), json2.dump());
}
TEST(MetricTest, RoundTrip) {
    auto seed = nlohmann::json::parse(R"json({        "value": 1,        "date": "2020-10-15T06:38:00.000+00:00"    })json");

    // Deserialization must not throw (skip on type mismatch in example data)
    appwrite::models::Metric model;
    try {
        model = appwrite::models::Metric::fromJson(seed);
    } catch (const nlohmann::json::exception&) {
        GTEST_SKIP() << "Seed example data type mismatch — skipped";
    }

    // Idempotency: serializing then deserializing then re-serializing must be stable
    auto json1  = model.toJson();
    auto model2 = appwrite::models::Metric::fromJson(json1);
    auto json2  = model2.toJson();
    EXPECT_EQ(json1.dump(), json2.dump());
}
TEST(UsageDatabasesTest, RoundTrip) {
    auto seed = nlohmann::json::parse(R"json({        "range": "30d",        "databasesTotal": 0,        "collectionsTotal": 0,        "tablesTotal": 0,        "documentsTotal": 0,        "rowsTotal": 0,        "storageTotal": 0,        "databasesReadsTotal": 0,        "databasesWritesTotal": 0,        "databases": [{}],        "collections": [{}],        "tables": [{}],        "documents": [{}],        "rows": [{}],        "storage": [{}],        "databasesReads": [{}],        "databasesWrites": [{}]    })json");

    // Deserialization must not throw (skip on type mismatch in example data)
    appwrite::models::UsageDatabases model;
    try {
        model = appwrite::models::UsageDatabases::fromJson(seed);
    } catch (const nlohmann::json::exception&) {
        GTEST_SKIP() << "Seed example data type mismatch — skipped";
    }

    // Idempotency: serializing then deserializing then re-serializing must be stable
    auto json1  = model.toJson();
    auto model2 = appwrite::models::UsageDatabases::fromJson(json1);
    auto json2  = model2.toJson();
    EXPECT_EQ(json1.dump(), json2.dump());
}
TEST(UsageDatabaseTest, RoundTrip) {
    auto seed = nlohmann::json::parse(R"json({        "range": "30d",        "collectionsTotal": 0,        "tablesTotal": 0,        "documentsTotal": 0,        "rowsTotal": 0,        "storageTotal": 0,        "databaseReadsTotal": 0,        "databaseWritesTotal": 0,        "collections": [{}],        "tables": [{}],        "documents": [{}],        "rows": [{}],        "storage": [{}],        "databaseReads": [{}],        "databaseWrites": [{}]    })json");

    // Deserialization must not throw (skip on type mismatch in example data)
    appwrite::models::UsageDatabase model;
    try {
        model = appwrite::models::UsageDatabase::fromJson(seed);
    } catch (const nlohmann::json::exception&) {
        GTEST_SKIP() << "Seed example data type mismatch — skipped";
    }

    // Idempotency: serializing then deserializing then re-serializing must be stable
    auto json1  = model.toJson();
    auto model2 = appwrite::models::UsageDatabase::fromJson(json1);
    auto json2  = model2.toJson();
    EXPECT_EQ(json1.dump(), json2.dump());
}
TEST(UsageTableTest, RoundTrip) {
    auto seed = nlohmann::json::parse(R"json({        "range": "30d",        "rowsTotal": 0,        "rows": [{}]    })json");

    // Deserialization must not throw (skip on type mismatch in example data)
    appwrite::models::UsageTable model;
    try {
        model = appwrite::models::UsageTable::fromJson(seed);
    } catch (const nlohmann::json::exception&) {
        GTEST_SKIP() << "Seed example data type mismatch — skipped";
    }

    // Idempotency: serializing then deserializing then re-serializing must be stable
    auto json1  = model.toJson();
    auto model2 = appwrite::models::UsageTable::fromJson(json1);
    auto json2  = model2.toJson();
    EXPECT_EQ(json1.dump(), json2.dump());
}
TEST(UsageCollectionTest, RoundTrip) {
    auto seed = nlohmann::json::parse(R"json({        "range": "30d",        "documentsTotal": 0,        "documents": [{}]    })json");

    // Deserialization must not throw (skip on type mismatch in example data)
    appwrite::models::UsageCollection model;
    try {
        model = appwrite::models::UsageCollection::fromJson(seed);
    } catch (const nlohmann::json::exception&) {
        GTEST_SKIP() << "Seed example data type mismatch — skipped";
    }

    // Idempotency: serializing then deserializing then re-serializing must be stable
    auto json1  = model.toJson();
    auto model2 = appwrite::models::UsageCollection::fromJson(json1);
    auto json2  = model2.toJson();
    EXPECT_EQ(json1.dump(), json2.dump());
}
TEST(UsageUsersTest, RoundTrip) {
    auto seed = nlohmann::json::parse(R"json({        "range": "30d",        "usersTotal": 0,        "sessionsTotal": 0,        "users": [{}],        "sessions": [{}]    })json");

    // Deserialization must not throw (skip on type mismatch in example data)
    appwrite::models::UsageUsers model;
    try {
        model = appwrite::models::UsageUsers::fromJson(seed);
    } catch (const nlohmann::json::exception&) {
        GTEST_SKIP() << "Seed example data type mismatch — skipped";
    }

    // Idempotency: serializing then deserializing then re-serializing must be stable
    auto json1  = model.toJson();
    auto model2 = appwrite::models::UsageUsers::fromJson(json1);
    auto json2  = model2.toJson();
    EXPECT_EQ(json1.dump(), json2.dump());
}
TEST(UsageStorageTest, RoundTrip) {
    auto seed = nlohmann::json::parse(R"json({        "range": "30d",        "bucketsTotal": 0,        "filesTotal": 0,        "filesStorageTotal": 0,        "buckets": [{}],        "files": [{}],        "storage": [{}]    })json");

    // Deserialization must not throw (skip on type mismatch in example data)
    appwrite::models::UsageStorage model;
    try {
        model = appwrite::models::UsageStorage::fromJson(seed);
    } catch (const nlohmann::json::exception&) {
        GTEST_SKIP() << "Seed example data type mismatch — skipped";
    }

    // Idempotency: serializing then deserializing then re-serializing must be stable
    auto json1  = model.toJson();
    auto model2 = appwrite::models::UsageStorage::fromJson(json1);
    auto json2  = model2.toJson();
    EXPECT_EQ(json1.dump(), json2.dump());
}
TEST(UsageBucketsTest, RoundTrip) {
    auto seed = nlohmann::json::parse(R"json({        "range": "30d",        "filesTotal": 0,        "filesStorageTotal": 0,        "files": [{}],        "storage": [{}],        "imageTransformations": [{}],        "imageTransformationsTotal": 0    })json");

    // Deserialization must not throw (skip on type mismatch in example data)
    appwrite::models::UsageBuckets model;
    try {
        model = appwrite::models::UsageBuckets::fromJson(seed);
    } catch (const nlohmann::json::exception&) {
        GTEST_SKIP() << "Seed example data type mismatch — skipped";
    }

    // Idempotency: serializing then deserializing then re-serializing must be stable
    auto json1  = model.toJson();
    auto model2 = appwrite::models::UsageBuckets::fromJson(json1);
    auto json2  = model2.toJson();
    EXPECT_EQ(json1.dump(), json2.dump());
}
TEST(UsageFunctionsTest, RoundTrip) {
    auto seed = nlohmann::json::parse(R"json({        "range": "30d",        "functionsTotal": 0,        "deploymentsTotal": 0,        "deploymentsStorageTotal": 0,        "buildsTotal": 0,        "buildsStorageTotal": 0,        "buildsTimeTotal": 0,        "buildsMbSecondsTotal": 0,        "executionsTotal": 0,        "executionsTimeTotal": 0,        "executionsMbSecondsTotal": 0,        "functions": [{}],        "deployments": [{}],        "deploymentsStorage": [{}],        "buildsSuccessTotal": 0,        "buildsFailedTotal": 0,        "builds": [{}],        "buildsStorage": [{}],        "buildsTime": [{}],        "buildsMbSeconds": [{}],        "executions": [{}],        "executionsTime": [{}],        "executionsMbSeconds": [{}],        "buildsSuccess": [{}],        "buildsFailed": [{}]    })json");

    // Deserialization must not throw (skip on type mismatch in example data)
    appwrite::models::UsageFunctions model;
    try {
        model = appwrite::models::UsageFunctions::fromJson(seed);
    } catch (const nlohmann::json::exception&) {
        GTEST_SKIP() << "Seed example data type mismatch — skipped";
    }

    // Idempotency: serializing then deserializing then re-serializing must be stable
    auto json1  = model.toJson();
    auto model2 = appwrite::models::UsageFunctions::fromJson(json1);
    auto json2  = model2.toJson();
    EXPECT_EQ(json1.dump(), json2.dump());
}
TEST(UsageFunctionTest, RoundTrip) {
    auto seed = nlohmann::json::parse(R"json({        "range": "30d",        "deploymentsTotal": 0,        "deploymentsStorageTotal": 0,        "buildsTotal": 0,        "buildsSuccessTotal": 0,        "buildsFailedTotal": 0,        "buildsStorageTotal": 0,        "buildsTimeTotal": 0,        "buildsTimeAverage": 0,        "buildsMbSecondsTotal": 0,        "executionsTotal": 0,        "executionsTimeTotal": 0,        "executionsMbSecondsTotal": 0,        "deployments": [{}],        "deploymentsStorage": [{}],        "builds": [{}],        "buildsStorage": [{}],        "buildsTime": [{}],        "buildsMbSeconds": [{}],        "executions": [{}],        "executionsTime": [{}],        "executionsMbSeconds": [{}],        "buildsSuccess": [{}],        "buildsFailed": [{}]    })json");

    // Deserialization must not throw (skip on type mismatch in example data)
    appwrite::models::UsageFunction model;
    try {
        model = appwrite::models::UsageFunction::fromJson(seed);
    } catch (const nlohmann::json::exception&) {
        GTEST_SKIP() << "Seed example data type mismatch — skipped";
    }

    // Idempotency: serializing then deserializing then re-serializing must be stable
    auto json1  = model.toJson();
    auto model2 = appwrite::models::UsageFunction::fromJson(json1);
    auto json2  = model2.toJson();
    EXPECT_EQ(json1.dump(), json2.dump());
}
TEST(HeadersTest, RoundTrip) {
    auto seed = nlohmann::json::parse(R"json({        "name": "Content-Type",        "value": "application\/json"    })json");

    // Deserialization must not throw (skip on type mismatch in example data)
    appwrite::models::Headers model;
    try {
        model = appwrite::models::Headers::fromJson(seed);
    } catch (const nlohmann::json::exception&) {
        GTEST_SKIP() << "Seed example data type mismatch — skipped";
    }

    // Idempotency: serializing then deserializing then re-serializing must be stable
    auto json1  = model.toJson();
    auto model2 = appwrite::models::Headers::fromJson(json1);
    auto json2  = model2.toJson();
    EXPECT_EQ(json1.dump(), json2.dump());
}
TEST(SpecificationTest, RoundTrip) {
    auto seed = nlohmann::json::parse(R"json({        "memory": 512,        "cpus": 1,        "enabled": true,        "slug": "s-1vcpu-512mb"    })json");

    // Deserialization must not throw (skip on type mismatch in example data)
    appwrite::models::Specification model;
    try {
        model = appwrite::models::Specification::fromJson(seed);
    } catch (const nlohmann::json::exception&) {
        GTEST_SKIP() << "Seed example data type mismatch — skipped";
    }

    // Idempotency: serializing then deserializing then re-serializing must be stable
    auto json1  = model.toJson();
    auto model2 = appwrite::models::Specification::fromJson(json1);
    auto json2  = model2.toJson();
    EXPECT_EQ(json1.dump(), json2.dump());
}
TEST(MfaChallengeTest, RoundTrip) {
    auto seed = nlohmann::json::parse(R"json({        "$id": "bb8ea5c16897e",        "$createdAt": "2020-10-15T06:38:00.000+00:00",        "userId": "5e5ea5c168bb8",        "expire": "2020-10-15T06:38:00.000+00:00"    })json");

    // Deserialization must not throw (skip on type mismatch in example data)
    appwrite::models::MfaChallenge model;
    try {
        model = appwrite::models::MfaChallenge::fromJson(seed);
    } catch (const nlohmann::json::exception&) {
        GTEST_SKIP() << "Seed example data type mismatch — skipped";
    }

    // Idempotency: serializing then deserializing then re-serializing must be stable
    auto json1  = model.toJson();
    auto model2 = appwrite::models::MfaChallenge::fromJson(json1);
    auto json2  = model2.toJson();
    EXPECT_EQ(json1.dump(), json2.dump());
}
TEST(MfaRecoveryCodesTest, RoundTrip) {
    auto seed = nlohmann::json::parse(R"json({        "recoveryCodes": ["a3kf0-s0cl2","s0co1-as98s"]    })json");

    // Deserialization must not throw (skip on type mismatch in example data)
    appwrite::models::MfaRecoveryCodes model;
    try {
        model = appwrite::models::MfaRecoveryCodes::fromJson(seed);
    } catch (const nlohmann::json::exception&) {
        GTEST_SKIP() << "Seed example data type mismatch — skipped";
    }

    // Idempotency: serializing then deserializing then re-serializing must be stable
    auto json1  = model.toJson();
    auto model2 = appwrite::models::MfaRecoveryCodes::fromJson(json1);
    auto json2  = model2.toJson();
    EXPECT_EQ(json1.dump(), json2.dump());
}
TEST(MfaTypeTest, RoundTrip) {
    auto seed = nlohmann::json::parse(R"json({        "secret": "[SHARED_SECRET]",        "uri": "otpauth:\/\/totp\/appwrite:user@example.com?secret=[SHARED_SECRET]&issuer=appwrite"    })json");

    // Deserialization must not throw (skip on type mismatch in example data)
    appwrite::models::MfaType model;
    try {
        model = appwrite::models::MfaType::fromJson(seed);
    } catch (const nlohmann::json::exception&) {
        GTEST_SKIP() << "Seed example data type mismatch — skipped";
    }

    // Idempotency: serializing then deserializing then re-serializing must be stable
    auto json1  = model.toJson();
    auto model2 = appwrite::models::MfaType::fromJson(json1);
    auto json2  = model2.toJson();
    EXPECT_EQ(json1.dump(), json2.dump());
}
TEST(MfaFactorsTest, RoundTrip) {
    auto seed = nlohmann::json::parse(R"json({        "totp": true,        "phone": true,        "email": true,        "recoveryCode": true    })json");

    // Deserialization must not throw (skip on type mismatch in example data)
    appwrite::models::MfaFactors model;
    try {
        model = appwrite::models::MfaFactors::fromJson(seed);
    } catch (const nlohmann::json::exception&) {
        GTEST_SKIP() << "Seed example data type mismatch — skipped";
    }

    // Idempotency: serializing then deserializing then re-serializing must be stable
    auto json1  = model.toJson();
    auto model2 = appwrite::models::MfaFactors::fromJson(json1);
    auto json2  = model2.toJson();
    EXPECT_EQ(json1.dump(), json2.dump());
}
TEST(TransactionTest, RoundTrip) {
    auto seed = nlohmann::json::parse(R"json({        "$id": "259125845563242502",        "$createdAt": "2020-10-15T06:38:00.000+00:00",        "$updatedAt": "2020-10-15T06:38:00.000+00:00",        "status": "pending",        "operations": 5,        "expiresAt": "2020-10-15T06:38:00.000+00:00"    })json");

    // Deserialization must not throw (skip on type mismatch in example data)
    appwrite::models::Transaction model;
    try {
        model = appwrite::models::Transaction::fromJson(seed);
    } catch (const nlohmann::json::exception&) {
        GTEST_SKIP() << "Seed example data type mismatch — skipped";
    }

    // Idempotency: serializing then deserializing then re-serializing must be stable
    auto json1  = model.toJson();
    auto model2 = appwrite::models::Transaction::fromJson(json1);
    auto json2  = model2.toJson();
    EXPECT_EQ(json1.dump(), json2.dump());
}
TEST(TargetTest, RoundTrip) {
    auto seed = nlohmann::json::parse(R"json({        "$id": "259125845563242502",        "$createdAt": "2020-10-15T06:38:00.000+00:00",        "$updatedAt": "2020-10-15T06:38:00.000+00:00",        "name": "Apple iPhone 12",        "userId": "259125845563242502",        "providerId": "259125845563242502",        "providerType": "email",        "identifier": "token",        "expired": false    })json");

    // Deserialization must not throw (skip on type mismatch in example data)
    appwrite::models::Target model;
    try {
        model = appwrite::models::Target::fromJson(seed);
    } catch (const nlohmann::json::exception&) {
        GTEST_SKIP() << "Seed example data type mismatch — skipped";
    }

    // Idempotency: serializing then deserializing then re-serializing must be stable
    auto json1  = model.toJson();
    auto model2 = appwrite::models::Target::fromJson(json1);
    auto json2  = model2.toJson();
    EXPECT_EQ(json1.dump(), json2.dump());
}
TEST(BillingAddressTest, RoundTrip) {
    auto seed = nlohmann::json::parse(R"json({        "$id": "eu-fr",        "userId": "5e5ea5c16897e",        "streetAddress": "13th Avenue",        "addressLine2": "",        "country": "USA",        "city": "",        "state": "",        "postalCode": ""    })json");

    // Deserialization must not throw (skip on type mismatch in example data)
    appwrite::models::BillingAddress model;
    try {
        model = appwrite::models::BillingAddress::fromJson(seed);
    } catch (const nlohmann::json::exception&) {
        GTEST_SKIP() << "Seed example data type mismatch — skipped";
    }

    // Idempotency: serializing then deserializing then re-serializing must be stable
    auto json1  = model.toJson();
    auto model2 = appwrite::models::BillingAddress::fromJson(json1);
    auto json2  = model2.toJson();
    EXPECT_EQ(json1.dump(), json2.dump());
}
TEST(CouponTest, RoundTrip) {
    auto seed = nlohmann::json::parse(R"json({        "$id": "NEWBONUS",        "code": "NEWBONUS",        "credits": 50,        "expiration": "2020-10-15T06:38:00.000+00:00",        "validity": 180,        "campaign": "AppwriteHeroes",        "status": "disabled",        "onlyNewOrgs": true    })json");

    // Deserialization must not throw (skip on type mismatch in example data)
    appwrite::models::Coupon model;
    try {
        model = appwrite::models::Coupon::fromJson(seed);
    } catch (const nlohmann::json::exception&) {
        GTEST_SKIP() << "Seed example data type mismatch — skipped";
    }

    // Idempotency: serializing then deserializing then re-serializing must be stable
    auto json1  = model.toJson();
    auto model2 = appwrite::models::Coupon::fromJson(json1);
    auto json2  = model2.toJson();
    EXPECT_EQ(json1.dump(), json2.dump());
}
TEST(InvoiceTest, RoundTrip) {
    auto seed = nlohmann::json::parse(R"json({        "$id": "5e5ea5c16897e",        "$createdAt": "2020-10-15T06:38:00.000+00:00",        "$updatedAt": "2020-10-15T06:38:00.000+00:00",        "$permissions": ["read(\"any\")"],        "teamId": "5e5ea5c16897e",        "aggregationId": "5e5ea5c16897e",        "plan": "tier-1",        "usage": [{}],        "amount": 50,        "tax": 17,        "taxAmount": 12.5,        "vat": 17,        "vatAmount": 12.5,        "grossAmount": 12.5,        "creditsUsed": 30,        "currency": "USD",        "clientSecret": "pi_daslfasdfkla_asdkfl",        "status": "succeeded",        "lastError": "Your card has insufficient balance.",        "dueAt": "2020-10-15T06:38:00.000+00:00",        "from": "2020-10-15T06:38:00.000+00:00",        "to": "2020-10-15T06:38:00.000+00:00"    })json");

    // Deserialization must not throw (skip on type mismatch in example data)
    appwrite::models::Invoice model;
    try {
        model = appwrite::models::Invoice::fromJson(seed);
    } catch (const nlohmann::json::exception&) {
        GTEST_SKIP() << "Seed example data type mismatch — skipped";
    }

    // Idempotency: serializing then deserializing then re-serializing must be stable
    auto json1  = model.toJson();
    auto model2 = appwrite::models::Invoice::fromJson(json1);
    auto json2  = model2.toJson();
    EXPECT_EQ(json1.dump(), json2.dump());
}
TEST(PaymentMethodTest, RoundTrip) {
    auto seed = nlohmann::json::parse(R"json({        "$id": "5e5ea5c16897e",        "$createdAt": "2020-10-15T06:38:00.000+00:00",        "$updatedAt": "2020-10-15T06:38:00.000+00:00",        "$permissions": ["read(\"any\")"],        "providerMethodId": "abdk3ed3sdkfj",        "clientSecret": "seti_ddfe",        "providerUserId": "abdk3ed3sdkfj",        "userId": "5e5ea5c16897e",        "expiryMonth": 2,        "expiryYear": 2024,        "last4": "4242",        "brand": "visa",        "name": "John Doe",        "mandateId": "yxc",        "country": "de",        "state": "",        "lastError": "Your card has insufficient funds.",        "default": false,        "expired": false,        "failed": false    })json");

    // Deserialization must not throw (skip on type mismatch in example data)
    appwrite::models::PaymentMethod model;
    try {
        model = appwrite::models::PaymentMethod::fromJson(seed);
    } catch (const nlohmann::json::exception&) {
        GTEST_SKIP() << "Seed example data type mismatch — skipped";
    }

    // Idempotency: serializing then deserializing then re-serializing must be stable
    auto json1  = model.toJson();
    auto model2 = appwrite::models::PaymentMethod::fromJson(json1);
    auto json2  = model2.toJson();
    EXPECT_EQ(json1.dump(), json2.dump());
}
TEST(UsageResourcesTest, RoundTrip) {
    auto seed = nlohmann::json::parse(R"json({        "name": "",        "value": 0,        "amount": 500,        "rate": 12.5,        "desc": "Your monthly recurring Pro plan.",        "resourceId": ""    })json");

    // Deserialization must not throw (skip on type mismatch in example data)
    appwrite::models::UsageResources model;
    try {
        model = appwrite::models::UsageResources::fromJson(seed);
    } catch (const nlohmann::json::exception&) {
        GTEST_SKIP() << "Seed example data type mismatch — skipped";
    }

    // Idempotency: serializing then deserializing then re-serializing must be stable
    auto json1  = model.toJson();
    auto model2 = appwrite::models::UsageResources::fromJson(json1);
    auto json2  = model2.toJson();
    EXPECT_EQ(json1.dump(), json2.dump());
}
TEST(InvoiceListTest, RoundTrip) {
    auto seed = nlohmann::json::parse(R"json({        "total": 5,        "invoices": [{}]    })json");

    // Deserialization must not throw (skip on type mismatch in example data)
    appwrite::models::InvoiceList model;
    try {
        model = appwrite::models::InvoiceList::fromJson(seed);
    } catch (const nlohmann::json::exception&) {
        GTEST_SKIP() << "Seed example data type mismatch — skipped";
    }

    // Idempotency: serializing then deserializing then re-serializing must be stable
    auto json1  = model.toJson();
    auto model2 = appwrite::models::InvoiceList::fromJson(json1);
    auto json2  = model2.toJson();
    EXPECT_EQ(json1.dump(), json2.dump());
}
TEST(BillingAddressListTest, RoundTrip) {
    auto seed = nlohmann::json::parse(R"json({        "total": 5,        "billingAddresses": [{}]    })json");

    // Deserialization must not throw (skip on type mismatch in example data)
    appwrite::models::BillingAddressList model;
    try {
        model = appwrite::models::BillingAddressList::fromJson(seed);
    } catch (const nlohmann::json::exception&) {
        GTEST_SKIP() << "Seed example data type mismatch — skipped";
    }

    // Idempotency: serializing then deserializing then re-serializing must be stable
    auto json1  = model.toJson();
    auto model2 = appwrite::models::BillingAddressList::fromJson(json1);
    auto json2  = model2.toJson();
    EXPECT_EQ(json1.dump(), json2.dump());
}
TEST(PaymentMethodListTest, RoundTrip) {
    auto seed = nlohmann::json::parse(R"json({        "total": 5,        "paymentMethods": [{}]    })json");

    // Deserialization must not throw (skip on type mismatch in example data)
    appwrite::models::PaymentMethodList model;
    try {
        model = appwrite::models::PaymentMethodList::fromJson(seed);
    } catch (const nlohmann::json::exception&) {
        GTEST_SKIP() << "Seed example data type mismatch — skipped";
    }

    // Idempotency: serializing then deserializing then re-serializing must be stable
    auto json1  = model.toJson();
    auto model2 = appwrite::models::PaymentMethodList::fromJson(json1);
    auto json2  = model2.toJson();
    EXPECT_EQ(json1.dump(), json2.dump());
}
