// Enums umbrella header for the Appwrite C++ SDK
// Auto-generated — do not edit manually
#pragma once

#include <string>
#include <stdexcept>
#include <nlohmann/json.hpp>

namespace appwrite {
namespace enums {

enum class Scopes {
    ACCOUNT,
    TEAMS_READ,
    TEAMS_WRITE,
    SESSIONS_WRITE,
    USERS_READ,
    USERS_WRITE,
    DATABASES_READ,
    DATABASES_WRITE,
    COLLECTIONS_READ,
    COLLECTIONS_WRITE,
    TABLES_READ,
    TABLES_WRITE,
    ATTRIBUTES_READ,
    ATTRIBUTES_WRITE,
    COLUMNS_READ,
    COLUMNS_WRITE,
    INDEXES_READ,
    INDEXES_WRITE,
    DOCUMENTS_READ,
    DOCUMENTS_WRITE,
    ROWS_READ,
    ROWS_WRITE,
    FILES_READ,
    FILES_WRITE,
    BUCKETS_READ,
    BUCKETS_WRITE,
    FUNCTIONS_READ,
    FUNCTIONS_WRITE,
    SITES_READ,
    SITES_WRITE,
    LOG_READ,
    LOG_WRITE,
    EXECUTION_READ,
    EXECUTION_WRITE,
    LOCALE_READ,
    AVATARS_READ,
    HEALTH_READ,
    PROVIDERS_READ,
    PROVIDERS_WRITE,
    MESSAGES_READ,
    MESSAGES_WRITE,
    TOPICS_READ,
    TOPICS_WRITE,
    SUBSCRIBERS_READ,
    SUBSCRIBERS_WRITE,
    TARGETS_READ,
    TARGETS_WRITE,
    RULES_READ,
    RULES_WRITE,
    SCHEDULES_READ,
    SCHEDULES_WRITE,
    MIGRATIONS_READ,
    MIGRATIONS_WRITE,
    VCS_READ,
    VCS_WRITE,
    ASSISTANT_READ,
    TOKENS_READ,
    TOKENS_WRITE,
    WEBHOOKS_READ,
    WEBHOOKS_WRITE,
    PROJECT_READ,
    PROJECT_WRITE,
    KEYS_READ,
    KEYS_WRITE,
    PLATFORMS_READ,
    PLATFORMS_WRITE,
    POLICIES_WRITE,
    POLICIES_READ,
    ARCHIVES_READ,
    ARCHIVES_WRITE,
    RESTORATIONS_READ,
    RESTORATIONS_WRITE,
    DOMAINS_READ,
    DOMAINS_WRITE,
    EVENTS_READ
};

inline std::string toString(Scopes value) {
    switch (value) {
        case Scopes :: ACCOUNT: return "account";
        case Scopes :: TEAMS_READ: return "teams.read";
        case Scopes :: TEAMS_WRITE: return "teams.write";
        case Scopes :: SESSIONS_WRITE: return "sessions.write";
        case Scopes :: USERS_READ: return "users.read";
        case Scopes :: USERS_WRITE: return "users.write";
        case Scopes :: DATABASES_READ: return "databases.read";
        case Scopes :: DATABASES_WRITE: return "databases.write";
        case Scopes :: COLLECTIONS_READ: return "collections.read";
        case Scopes :: COLLECTIONS_WRITE: return "collections.write";
        case Scopes :: TABLES_READ: return "tables.read";
        case Scopes :: TABLES_WRITE: return "tables.write";
        case Scopes :: ATTRIBUTES_READ: return "attributes.read";
        case Scopes :: ATTRIBUTES_WRITE: return "attributes.write";
        case Scopes :: COLUMNS_READ: return "columns.read";
        case Scopes :: COLUMNS_WRITE: return "columns.write";
        case Scopes :: INDEXES_READ: return "indexes.read";
        case Scopes :: INDEXES_WRITE: return "indexes.write";
        case Scopes :: DOCUMENTS_READ: return "documents.read";
        case Scopes :: DOCUMENTS_WRITE: return "documents.write";
        case Scopes :: ROWS_READ: return "rows.read";
        case Scopes :: ROWS_WRITE: return "rows.write";
        case Scopes :: FILES_READ: return "files.read";
        case Scopes :: FILES_WRITE: return "files.write";
        case Scopes :: BUCKETS_READ: return "buckets.read";
        case Scopes :: BUCKETS_WRITE: return "buckets.write";
        case Scopes :: FUNCTIONS_READ: return "functions.read";
        case Scopes :: FUNCTIONS_WRITE: return "functions.write";
        case Scopes :: SITES_READ: return "sites.read";
        case Scopes :: SITES_WRITE: return "sites.write";
        case Scopes :: LOG_READ: return "log.read";
        case Scopes :: LOG_WRITE: return "log.write";
        case Scopes :: EXECUTION_READ: return "execution.read";
        case Scopes :: EXECUTION_WRITE: return "execution.write";
        case Scopes :: LOCALE_READ: return "locale.read";
        case Scopes :: AVATARS_READ: return "avatars.read";
        case Scopes :: HEALTH_READ: return "health.read";
        case Scopes :: PROVIDERS_READ: return "providers.read";
        case Scopes :: PROVIDERS_WRITE: return "providers.write";
        case Scopes :: MESSAGES_READ: return "messages.read";
        case Scopes :: MESSAGES_WRITE: return "messages.write";
        case Scopes :: TOPICS_READ: return "topics.read";
        case Scopes :: TOPICS_WRITE: return "topics.write";
        case Scopes :: SUBSCRIBERS_READ: return "subscribers.read";
        case Scopes :: SUBSCRIBERS_WRITE: return "subscribers.write";
        case Scopes :: TARGETS_READ: return "targets.read";
        case Scopes :: TARGETS_WRITE: return "targets.write";
        case Scopes :: RULES_READ: return "rules.read";
        case Scopes :: RULES_WRITE: return "rules.write";
        case Scopes :: SCHEDULES_READ: return "schedules.read";
        case Scopes :: SCHEDULES_WRITE: return "schedules.write";
        case Scopes :: MIGRATIONS_READ: return "migrations.read";
        case Scopes :: MIGRATIONS_WRITE: return "migrations.write";
        case Scopes :: VCS_READ: return "vcs.read";
        case Scopes :: VCS_WRITE: return "vcs.write";
        case Scopes :: ASSISTANT_READ: return "assistant.read";
        case Scopes :: TOKENS_READ: return "tokens.read";
        case Scopes :: TOKENS_WRITE: return "tokens.write";
        case Scopes :: WEBHOOKS_READ: return "webhooks.read";
        case Scopes :: WEBHOOKS_WRITE: return "webhooks.write";
        case Scopes :: PROJECT_READ: return "project.read";
        case Scopes :: PROJECT_WRITE: return "project.write";
        case Scopes :: KEYS_READ: return "keys.read";
        case Scopes :: KEYS_WRITE: return "keys.write";
        case Scopes :: PLATFORMS_READ: return "platforms.read";
        case Scopes :: PLATFORMS_WRITE: return "platforms.write";
        case Scopes :: POLICIES_WRITE: return "policies.write";
        case Scopes :: POLICIES_READ: return "policies.read";
        case Scopes :: ARCHIVES_READ: return "archives.read";
        case Scopes :: ARCHIVES_WRITE: return "archives.write";
        case Scopes :: RESTORATIONS_READ: return "restorations.read";
        case Scopes :: RESTORATIONS_WRITE: return "restorations.write";
        case Scopes :: DOMAINS_READ: return "domains.read";
        case Scopes :: DOMAINS_WRITE: return "domains.write";
        case Scopes :: EVENTS_READ: return "events.read";
        default: throw std::invalid_argument("Unknown Scopes enum value");
    }
}

inline Scopes scopesFromString(const std::string& s) {
    if (s == "account") return Scopes::ACCOUNT;
    if (s == "teams.read") return Scopes::TEAMS_READ;
    if (s == "teams.write") return Scopes::TEAMS_WRITE;
    if (s == "sessions.write") return Scopes::SESSIONS_WRITE;
    if (s == "users.read") return Scopes::USERS_READ;
    if (s == "users.write") return Scopes::USERS_WRITE;
    if (s == "databases.read") return Scopes::DATABASES_READ;
    if (s == "databases.write") return Scopes::DATABASES_WRITE;
    if (s == "collections.read") return Scopes::COLLECTIONS_READ;
    if (s == "collections.write") return Scopes::COLLECTIONS_WRITE;
    if (s == "tables.read") return Scopes::TABLES_READ;
    if (s == "tables.write") return Scopes::TABLES_WRITE;
    if (s == "attributes.read") return Scopes::ATTRIBUTES_READ;
    if (s == "attributes.write") return Scopes::ATTRIBUTES_WRITE;
    if (s == "columns.read") return Scopes::COLUMNS_READ;
    if (s == "columns.write") return Scopes::COLUMNS_WRITE;
    if (s == "indexes.read") return Scopes::INDEXES_READ;
    if (s == "indexes.write") return Scopes::INDEXES_WRITE;
    if (s == "documents.read") return Scopes::DOCUMENTS_READ;
    if (s == "documents.write") return Scopes::DOCUMENTS_WRITE;
    if (s == "rows.read") return Scopes::ROWS_READ;
    if (s == "rows.write") return Scopes::ROWS_WRITE;
    if (s == "files.read") return Scopes::FILES_READ;
    if (s == "files.write") return Scopes::FILES_WRITE;
    if (s == "buckets.read") return Scopes::BUCKETS_READ;
    if (s == "buckets.write") return Scopes::BUCKETS_WRITE;
    if (s == "functions.read") return Scopes::FUNCTIONS_READ;
    if (s == "functions.write") return Scopes::FUNCTIONS_WRITE;
    if (s == "sites.read") return Scopes::SITES_READ;
    if (s == "sites.write") return Scopes::SITES_WRITE;
    if (s == "log.read") return Scopes::LOG_READ;
    if (s == "log.write") return Scopes::LOG_WRITE;
    if (s == "execution.read") return Scopes::EXECUTION_READ;
    if (s == "execution.write") return Scopes::EXECUTION_WRITE;
    if (s == "locale.read") return Scopes::LOCALE_READ;
    if (s == "avatars.read") return Scopes::AVATARS_READ;
    if (s == "health.read") return Scopes::HEALTH_READ;
    if (s == "providers.read") return Scopes::PROVIDERS_READ;
    if (s == "providers.write") return Scopes::PROVIDERS_WRITE;
    if (s == "messages.read") return Scopes::MESSAGES_READ;
    if (s == "messages.write") return Scopes::MESSAGES_WRITE;
    if (s == "topics.read") return Scopes::TOPICS_READ;
    if (s == "topics.write") return Scopes::TOPICS_WRITE;
    if (s == "subscribers.read") return Scopes::SUBSCRIBERS_READ;
    if (s == "subscribers.write") return Scopes::SUBSCRIBERS_WRITE;
    if (s == "targets.read") return Scopes::TARGETS_READ;
    if (s == "targets.write") return Scopes::TARGETS_WRITE;
    if (s == "rules.read") return Scopes::RULES_READ;
    if (s == "rules.write") return Scopes::RULES_WRITE;
    if (s == "schedules.read") return Scopes::SCHEDULES_READ;
    if (s == "schedules.write") return Scopes::SCHEDULES_WRITE;
    if (s == "migrations.read") return Scopes::MIGRATIONS_READ;
    if (s == "migrations.write") return Scopes::MIGRATIONS_WRITE;
    if (s == "vcs.read") return Scopes::VCS_READ;
    if (s == "vcs.write") return Scopes::VCS_WRITE;
    if (s == "assistant.read") return Scopes::ASSISTANT_READ;
    if (s == "tokens.read") return Scopes::TOKENS_READ;
    if (s == "tokens.write") return Scopes::TOKENS_WRITE;
    if (s == "webhooks.read") return Scopes::WEBHOOKS_READ;
    if (s == "webhooks.write") return Scopes::WEBHOOKS_WRITE;
    if (s == "project.read") return Scopes::PROJECT_READ;
    if (s == "project.write") return Scopes::PROJECT_WRITE;
    if (s == "keys.read") return Scopes::KEYS_READ;
    if (s == "keys.write") return Scopes::KEYS_WRITE;
    if (s == "platforms.read") return Scopes::PLATFORMS_READ;
    if (s == "platforms.write") return Scopes::PLATFORMS_WRITE;
    if (s == "policies.write") return Scopes::POLICIES_WRITE;
    if (s == "policies.read") return Scopes::POLICIES_READ;
    if (s == "archives.read") return Scopes::ARCHIVES_READ;
    if (s == "archives.write") return Scopes::ARCHIVES_WRITE;
    if (s == "restorations.read") return Scopes::RESTORATIONS_READ;
    if (s == "restorations.write") return Scopes::RESTORATIONS_WRITE;
    if (s == "domains.read") return Scopes::DOMAINS_READ;
    if (s == "domains.write") return Scopes::DOMAINS_WRITE;
    if (s == "events.read") return Scopes::EVENTS_READ;
    throw std::invalid_argument("Unknown Scopes value: " + s);
}

inline void from_json(const nlohmann::json& j, Scopes& v) {
    v = scopesFromString(j.get<std::string>());
}

inline void to_json(nlohmann::json& j, const Scopes& v) {
    j = toString(v);
}

enum class AuthenticatorType {
    TOTP
};

inline std::string toString(AuthenticatorType value) {
    switch (value) {
        case AuthenticatorType :: TOTP: return "totp";
        default: throw std::invalid_argument("Unknown AuthenticatorType enum value");
    }
}

inline AuthenticatorType authenticatorTypeFromString(const std::string& s) {
    if (s == "totp") return AuthenticatorType::TOTP;
    throw std::invalid_argument("Unknown AuthenticatorType value: " + s);
}

inline void from_json(const nlohmann::json& j, AuthenticatorType& v) {
    v = authenticatorTypeFromString(j.get<std::string>());
}

inline void to_json(nlohmann::json& j, const AuthenticatorType& v) {
    j = toString(v);
}

enum class AuthenticationFactor {
    EMAIL,
    PHONE,
    TOTP,
    RECOVERYCODE
};

inline std::string toString(AuthenticationFactor value) {
    switch (value) {
        case AuthenticationFactor :: EMAIL: return "email";
        case AuthenticationFactor :: PHONE: return "phone";
        case AuthenticationFactor :: TOTP: return "totp";
        case AuthenticationFactor :: RECOVERYCODE: return "recoverycode";
        default: throw std::invalid_argument("Unknown AuthenticationFactor enum value");
    }
}

inline AuthenticationFactor authenticationFactorFromString(const std::string& s) {
    if (s == "email") return AuthenticationFactor::EMAIL;
    if (s == "phone") return AuthenticationFactor::PHONE;
    if (s == "totp") return AuthenticationFactor::TOTP;
    if (s == "recoverycode") return AuthenticationFactor::RECOVERYCODE;
    throw std::invalid_argument("Unknown AuthenticationFactor value: " + s);
}

inline void from_json(const nlohmann::json& j, AuthenticationFactor& v) {
    v = authenticationFactorFromString(j.get<std::string>());
}

inline void to_json(nlohmann::json& j, const AuthenticationFactor& v) {
    j = toString(v);
}

enum class OAuthProvider {
    AMAZON,
    APPLE,
    AUTH0,
    AUTHENTIK,
    AUTODESK,
    BITBUCKET,
    BITLY,
    BOX,
    DAILYMOTION,
    DISCORD,
    DISQUS,
    DROPBOX,
    ETSY,
    FACEBOOK,
    FIGMA,
    GITHUB,
    GITLAB,
    GOOGLE,
    LINKEDIN,
    MICROSOFT,
    NOTION,
    OIDC,
    OKTA,
    PAYPAL,
    PAYPALSANDBOX,
    PODIO,
    SALESFORCE,
    SLACK,
    SPOTIFY,
    STRIPE,
    TRADESHIFT,
    TRADESHIFTBOX,
    TWITCH,
    WORDPRESS,
    X,
    YAHOO,
    YAMMER,
    YANDEX,
    ZOHO,
    ZOOM
};

inline std::string toString(OAuthProvider value) {
    switch (value) {
        case OAuthProvider :: AMAZON: return "amazon";
        case OAuthProvider :: APPLE: return "apple";
        case OAuthProvider :: AUTH0: return "auth0";
        case OAuthProvider :: AUTHENTIK: return "authentik";
        case OAuthProvider :: AUTODESK: return "autodesk";
        case OAuthProvider :: BITBUCKET: return "bitbucket";
        case OAuthProvider :: BITLY: return "bitly";
        case OAuthProvider :: BOX: return "box";
        case OAuthProvider :: DAILYMOTION: return "dailymotion";
        case OAuthProvider :: DISCORD: return "discord";
        case OAuthProvider :: DISQUS: return "disqus";
        case OAuthProvider :: DROPBOX: return "dropbox";
        case OAuthProvider :: ETSY: return "etsy";
        case OAuthProvider :: FACEBOOK: return "facebook";
        case OAuthProvider :: FIGMA: return "figma";
        case OAuthProvider :: GITHUB: return "github";
        case OAuthProvider :: GITLAB: return "gitlab";
        case OAuthProvider :: GOOGLE: return "google";
        case OAuthProvider :: LINKEDIN: return "linkedin";
        case OAuthProvider :: MICROSOFT: return "microsoft";
        case OAuthProvider :: NOTION: return "notion";
        case OAuthProvider :: OIDC: return "oidc";
        case OAuthProvider :: OKTA: return "okta";
        case OAuthProvider :: PAYPAL: return "paypal";
        case OAuthProvider :: PAYPALSANDBOX: return "paypalSandbox";
        case OAuthProvider :: PODIO: return "podio";
        case OAuthProvider :: SALESFORCE: return "salesforce";
        case OAuthProvider :: SLACK: return "slack";
        case OAuthProvider :: SPOTIFY: return "spotify";
        case OAuthProvider :: STRIPE: return "stripe";
        case OAuthProvider :: TRADESHIFT: return "tradeshift";
        case OAuthProvider :: TRADESHIFTBOX: return "tradeshiftBox";
        case OAuthProvider :: TWITCH: return "twitch";
        case OAuthProvider :: WORDPRESS: return "wordpress";
        case OAuthProvider :: X: return "x";
        case OAuthProvider :: YAHOO: return "yahoo";
        case OAuthProvider :: YAMMER: return "yammer";
        case OAuthProvider :: YANDEX: return "yandex";
        case OAuthProvider :: ZOHO: return "zoho";
        case OAuthProvider :: ZOOM: return "zoom";
        default: throw std::invalid_argument("Unknown OAuthProvider enum value");
    }
}

inline OAuthProvider oAuthProviderFromString(const std::string& s) {
    if (s == "amazon") return OAuthProvider::AMAZON;
    if (s == "apple") return OAuthProvider::APPLE;
    if (s == "auth0") return OAuthProvider::AUTH0;
    if (s == "authentik") return OAuthProvider::AUTHENTIK;
    if (s == "autodesk") return OAuthProvider::AUTODESK;
    if (s == "bitbucket") return OAuthProvider::BITBUCKET;
    if (s == "bitly") return OAuthProvider::BITLY;
    if (s == "box") return OAuthProvider::BOX;
    if (s == "dailymotion") return OAuthProvider::DAILYMOTION;
    if (s == "discord") return OAuthProvider::DISCORD;
    if (s == "disqus") return OAuthProvider::DISQUS;
    if (s == "dropbox") return OAuthProvider::DROPBOX;
    if (s == "etsy") return OAuthProvider::ETSY;
    if (s == "facebook") return OAuthProvider::FACEBOOK;
    if (s == "figma") return OAuthProvider::FIGMA;
    if (s == "github") return OAuthProvider::GITHUB;
    if (s == "gitlab") return OAuthProvider::GITLAB;
    if (s == "google") return OAuthProvider::GOOGLE;
    if (s == "linkedin") return OAuthProvider::LINKEDIN;
    if (s == "microsoft") return OAuthProvider::MICROSOFT;
    if (s == "notion") return OAuthProvider::NOTION;
    if (s == "oidc") return OAuthProvider::OIDC;
    if (s == "okta") return OAuthProvider::OKTA;
    if (s == "paypal") return OAuthProvider::PAYPAL;
    if (s == "paypalSandbox") return OAuthProvider::PAYPALSANDBOX;
    if (s == "podio") return OAuthProvider::PODIO;
    if (s == "salesforce") return OAuthProvider::SALESFORCE;
    if (s == "slack") return OAuthProvider::SLACK;
    if (s == "spotify") return OAuthProvider::SPOTIFY;
    if (s == "stripe") return OAuthProvider::STRIPE;
    if (s == "tradeshift") return OAuthProvider::TRADESHIFT;
    if (s == "tradeshiftBox") return OAuthProvider::TRADESHIFTBOX;
    if (s == "twitch") return OAuthProvider::TWITCH;
    if (s == "wordpress") return OAuthProvider::WORDPRESS;
    if (s == "x") return OAuthProvider::X;
    if (s == "yahoo") return OAuthProvider::YAHOO;
    if (s == "yammer") return OAuthProvider::YAMMER;
    if (s == "yandex") return OAuthProvider::YANDEX;
    if (s == "zoho") return OAuthProvider::ZOHO;
    if (s == "zoom") return OAuthProvider::ZOOM;
    throw std::invalid_argument("Unknown OAuthProvider value: " + s);
}

inline void from_json(const nlohmann::json& j, OAuthProvider& v) {
    v = oAuthProviderFromString(j.get<std::string>());
}

inline void to_json(nlohmann::json& j, const OAuthProvider& v) {
    j = toString(v);
}

enum class UsageRange {
    TWENTY_FOUR_HOURS,
    THIRTY_DAYS,
    NINETY_DAYS
};

inline std::string toString(UsageRange value) {
    switch (value) {
        case UsageRange :: TWENTY_FOUR_HOURS: return "24h";
        case UsageRange :: THIRTY_DAYS: return "30d";
        case UsageRange :: NINETY_DAYS: return "90d";
        default: throw std::invalid_argument("Unknown UsageRange enum value");
    }
}

inline UsageRange usageRangeFromString(const std::string& s) {
    if (s == "24h") return UsageRange::TWENTY_FOUR_HOURS;
    if (s == "30d") return UsageRange::THIRTY_DAYS;
    if (s == "90d") return UsageRange::NINETY_DAYS;
    throw std::invalid_argument("Unknown UsageRange value: " + s);
}

inline void from_json(const nlohmann::json& j, UsageRange& v) {
    v = usageRangeFromString(j.get<std::string>());
}

inline void to_json(nlohmann::json& j, const UsageRange& v) {
    j = toString(v);
}

enum class RelationshipType {
    ONETOONE,
    MANYTOONE,
    MANYTOMANY,
    ONETOMANY
};

inline std::string toString(RelationshipType value) {
    switch (value) {
        case RelationshipType :: ONETOONE: return "oneToOne";
        case RelationshipType :: MANYTOONE: return "manyToOne";
        case RelationshipType :: MANYTOMANY: return "manyToMany";
        case RelationshipType :: ONETOMANY: return "oneToMany";
        default: throw std::invalid_argument("Unknown RelationshipType enum value");
    }
}

inline RelationshipType relationshipTypeFromString(const std::string& s) {
    if (s == "oneToOne") return RelationshipType::ONETOONE;
    if (s == "manyToOne") return RelationshipType::MANYTOONE;
    if (s == "manyToMany") return RelationshipType::MANYTOMANY;
    if (s == "oneToMany") return RelationshipType::ONETOMANY;
    throw std::invalid_argument("Unknown RelationshipType value: " + s);
}

inline void from_json(const nlohmann::json& j, RelationshipType& v) {
    v = relationshipTypeFromString(j.get<std::string>());
}

inline void to_json(nlohmann::json& j, const RelationshipType& v) {
    j = toString(v);
}

enum class RelationMutate {
    CASCADE,
    RESTRICT,
    SETNULL
};

inline std::string toString(RelationMutate value) {
    switch (value) {
        case RelationMutate :: CASCADE: return "cascade";
        case RelationMutate :: RESTRICT: return "restrict";
        case RelationMutate :: SETNULL: return "setNull";
        default: throw std::invalid_argument("Unknown RelationMutate enum value");
    }
}

inline RelationMutate relationMutateFromString(const std::string& s) {
    if (s == "cascade") return RelationMutate::CASCADE;
    if (s == "restrict") return RelationMutate::RESTRICT;
    if (s == "setNull") return RelationMutate::SETNULL;
    throw std::invalid_argument("Unknown RelationMutate value: " + s);
}

inline void from_json(const nlohmann::json& j, RelationMutate& v) {
    v = relationMutateFromString(j.get<std::string>());
}

inline void to_json(nlohmann::json& j, const RelationMutate& v) {
    j = toString(v);
}

enum class DatabasesIndexType {
    KEY,
    FULLTEXT,
    UNIQUE,
    SPATIAL
};

inline std::string toString(DatabasesIndexType value) {
    switch (value) {
        case DatabasesIndexType :: KEY: return "key";
        case DatabasesIndexType :: FULLTEXT: return "fulltext";
        case DatabasesIndexType :: UNIQUE: return "unique";
        case DatabasesIndexType :: SPATIAL: return "spatial";
        default: throw std::invalid_argument("Unknown DatabasesIndexType enum value");
    }
}

inline DatabasesIndexType databasesIndexTypeFromString(const std::string& s) {
    if (s == "key") return DatabasesIndexType::KEY;
    if (s == "fulltext") return DatabasesIndexType::FULLTEXT;
    if (s == "unique") return DatabasesIndexType::UNIQUE;
    if (s == "spatial") return DatabasesIndexType::SPATIAL;
    throw std::invalid_argument("Unknown DatabasesIndexType value: " + s);
}

inline void from_json(const nlohmann::json& j, DatabasesIndexType& v) {
    v = databasesIndexTypeFromString(j.get<std::string>());
}

inline void to_json(nlohmann::json& j, const DatabasesIndexType& v) {
    j = toString(v);
}

enum class OrderBy {
    ASC,
    DESC
};

inline std::string toString(OrderBy value) {
    switch (value) {
        case OrderBy :: ASC: return "asc";
        case OrderBy :: DESC: return "desc";
        default: throw std::invalid_argument("Unknown OrderBy enum value");
    }
}

inline OrderBy orderByFromString(const std::string& s) {
    if (s == "asc") return OrderBy::ASC;
    if (s == "desc") return OrderBy::DESC;
    throw std::invalid_argument("Unknown OrderBy value: " + s);
}

inline void from_json(const nlohmann::json& j, OrderBy& v) {
    v = orderByFromString(j.get<std::string>());
}

inline void to_json(nlohmann::json& j, const OrderBy& v) {
    j = toString(v);
}

enum class Runtime {
    NODE_14_5,
    NODE_16_0,
    NODE_18_0,
    NODE_19_0,
    NODE_20_0,
    NODE_21_0,
    NODE_22,
    NODE_23,
    NODE_24,
    NODE_25,
    PHP_8_0,
    PHP_8_1,
    PHP_8_2,
    PHP_8_3,
    PHP_8_4,
    RUBY_3_0,
    RUBY_3_1,
    RUBY_3_2,
    RUBY_3_3,
    RUBY_3_4,
    RUBY_4_0,
    PYTHON_3_8,
    PYTHON_3_9,
    PYTHON_3_10,
    PYTHON_3_11,
    PYTHON_3_12,
    PYTHON_3_13,
    PYTHON_3_14,
    PYTHON_ML_3_11,
    PYTHON_ML_3_12,
    PYTHON_ML_3_13,
    DENO_1_21,
    DENO_1_24,
    DENO_1_35,
    DENO_1_40,
    DENO_1_46,
    DENO_2_0,
    DENO_2_5,
    DENO_2_6,
    DART_2_15,
    DART_2_16,
    DART_2_17,
    DART_2_18,
    DART_2_19,
    DART_3_0,
    DART_3_1,
    DART_3_3,
    DART_3_5,
    DART_3_8,
    DART_3_9,
    DART_3_10,
    DART_3_11,
    DOTNET_6_0,
    DOTNET_7_0,
    DOTNET_8_0,
    DOTNET_10,
    JAVA_8_0,
    JAVA_11_0,
    JAVA_17_0,
    JAVA_18_0,
    JAVA_21_0,
    JAVA_22,
    JAVA_25,
    SWIFT_5_5,
    SWIFT_5_8,
    SWIFT_5_9,
    SWIFT_5_10,
    SWIFT_6_2,
    KOTLIN_1_6,
    KOTLIN_1_8,
    KOTLIN_1_9,
    KOTLIN_2_0,
    KOTLIN_2_3,
    CPP_17,
    CPP_20,
    BUN_1_0,
    BUN_1_1,
    BUN_1_2,
    BUN_1_3,
    GO_1_23,
    GO_1_24,
    GO_1_25,
    GO_1_26,
    STATIC_1,
    FLUTTER_3_24,
    FLUTTER_3_27,
    FLUTTER_3_29,
    FLUTTER_3_32,
    FLUTTER_3_35,
    FLUTTER_3_38,
    FLUTTER_3_41
};

inline std::string toString(Runtime value) {
    switch (value) {
        case Runtime :: NODE_14_5: return "node-14.5";
        case Runtime :: NODE_16_0: return "node-16.0";
        case Runtime :: NODE_18_0: return "node-18.0";
        case Runtime :: NODE_19_0: return "node-19.0";
        case Runtime :: NODE_20_0: return "node-20.0";
        case Runtime :: NODE_21_0: return "node-21.0";
        case Runtime :: NODE_22: return "node-22";
        case Runtime :: NODE_23: return "node-23";
        case Runtime :: NODE_24: return "node-24";
        case Runtime :: NODE_25: return "node-25";
        case Runtime :: PHP_8_0: return "php-8.0";
        case Runtime :: PHP_8_1: return "php-8.1";
        case Runtime :: PHP_8_2: return "php-8.2";
        case Runtime :: PHP_8_3: return "php-8.3";
        case Runtime :: PHP_8_4: return "php-8.4";
        case Runtime :: RUBY_3_0: return "ruby-3.0";
        case Runtime :: RUBY_3_1: return "ruby-3.1";
        case Runtime :: RUBY_3_2: return "ruby-3.2";
        case Runtime :: RUBY_3_3: return "ruby-3.3";
        case Runtime :: RUBY_3_4: return "ruby-3.4";
        case Runtime :: RUBY_4_0: return "ruby-4.0";
        case Runtime :: PYTHON_3_8: return "python-3.8";
        case Runtime :: PYTHON_3_9: return "python-3.9";
        case Runtime :: PYTHON_3_10: return "python-3.10";
        case Runtime :: PYTHON_3_11: return "python-3.11";
        case Runtime :: PYTHON_3_12: return "python-3.12";
        case Runtime :: PYTHON_3_13: return "python-3.13";
        case Runtime :: PYTHON_3_14: return "python-3.14";
        case Runtime :: PYTHON_ML_3_11: return "python-ml-3.11";
        case Runtime :: PYTHON_ML_3_12: return "python-ml-3.12";
        case Runtime :: PYTHON_ML_3_13: return "python-ml-3.13";
        case Runtime :: DENO_1_21: return "deno-1.21";
        case Runtime :: DENO_1_24: return "deno-1.24";
        case Runtime :: DENO_1_35: return "deno-1.35";
        case Runtime :: DENO_1_40: return "deno-1.40";
        case Runtime :: DENO_1_46: return "deno-1.46";
        case Runtime :: DENO_2_0: return "deno-2.0";
        case Runtime :: DENO_2_5: return "deno-2.5";
        case Runtime :: DENO_2_6: return "deno-2.6";
        case Runtime :: DART_2_15: return "dart-2.15";
        case Runtime :: DART_2_16: return "dart-2.16";
        case Runtime :: DART_2_17: return "dart-2.17";
        case Runtime :: DART_2_18: return "dart-2.18";
        case Runtime :: DART_2_19: return "dart-2.19";
        case Runtime :: DART_3_0: return "dart-3.0";
        case Runtime :: DART_3_1: return "dart-3.1";
        case Runtime :: DART_3_3: return "dart-3.3";
        case Runtime :: DART_3_5: return "dart-3.5";
        case Runtime :: DART_3_8: return "dart-3.8";
        case Runtime :: DART_3_9: return "dart-3.9";
        case Runtime :: DART_3_10: return "dart-3.10";
        case Runtime :: DART_3_11: return "dart-3.11";
        case Runtime :: DOTNET_6_0: return "dotnet-6.0";
        case Runtime :: DOTNET_7_0: return "dotnet-7.0";
        case Runtime :: DOTNET_8_0: return "dotnet-8.0";
        case Runtime :: DOTNET_10: return "dotnet-10";
        case Runtime :: JAVA_8_0: return "java-8.0";
        case Runtime :: JAVA_11_0: return "java-11.0";
        case Runtime :: JAVA_17_0: return "java-17.0";
        case Runtime :: JAVA_18_0: return "java-18.0";
        case Runtime :: JAVA_21_0: return "java-21.0";
        case Runtime :: JAVA_22: return "java-22";
        case Runtime :: JAVA_25: return "java-25";
        case Runtime :: SWIFT_5_5: return "swift-5.5";
        case Runtime :: SWIFT_5_8: return "swift-5.8";
        case Runtime :: SWIFT_5_9: return "swift-5.9";
        case Runtime :: SWIFT_5_10: return "swift-5.10";
        case Runtime :: SWIFT_6_2: return "swift-6.2";
        case Runtime :: KOTLIN_1_6: return "kotlin-1.6";
        case Runtime :: KOTLIN_1_8: return "kotlin-1.8";
        case Runtime :: KOTLIN_1_9: return "kotlin-1.9";
        case Runtime :: KOTLIN_2_0: return "kotlin-2.0";
        case Runtime :: KOTLIN_2_3: return "kotlin-2.3";
        case Runtime :: CPP_17: return "cpp-17";
        case Runtime :: CPP_20: return "cpp-20";
        case Runtime :: BUN_1_0: return "bun-1.0";
        case Runtime :: BUN_1_1: return "bun-1.1";
        case Runtime :: BUN_1_2: return "bun-1.2";
        case Runtime :: BUN_1_3: return "bun-1.3";
        case Runtime :: GO_1_23: return "go-1.23";
        case Runtime :: GO_1_24: return "go-1.24";
        case Runtime :: GO_1_25: return "go-1.25";
        case Runtime :: GO_1_26: return "go-1.26";
        case Runtime :: STATIC_1: return "static-1";
        case Runtime :: FLUTTER_3_24: return "flutter-3.24";
        case Runtime :: FLUTTER_3_27: return "flutter-3.27";
        case Runtime :: FLUTTER_3_29: return "flutter-3.29";
        case Runtime :: FLUTTER_3_32: return "flutter-3.32";
        case Runtime :: FLUTTER_3_35: return "flutter-3.35";
        case Runtime :: FLUTTER_3_38: return "flutter-3.38";
        case Runtime :: FLUTTER_3_41: return "flutter-3.41";
        default: throw std::invalid_argument("Unknown Runtime enum value");
    }
}

inline Runtime runtimeFromString(const std::string& s) {
    if (s == "node-14.5") return Runtime::NODE_14_5;
    if (s == "node-16.0") return Runtime::NODE_16_0;
    if (s == "node-18.0") return Runtime::NODE_18_0;
    if (s == "node-19.0") return Runtime::NODE_19_0;
    if (s == "node-20.0") return Runtime::NODE_20_0;
    if (s == "node-21.0") return Runtime::NODE_21_0;
    if (s == "node-22") return Runtime::NODE_22;
    if (s == "node-23") return Runtime::NODE_23;
    if (s == "node-24") return Runtime::NODE_24;
    if (s == "node-25") return Runtime::NODE_25;
    if (s == "php-8.0") return Runtime::PHP_8_0;
    if (s == "php-8.1") return Runtime::PHP_8_1;
    if (s == "php-8.2") return Runtime::PHP_8_2;
    if (s == "php-8.3") return Runtime::PHP_8_3;
    if (s == "php-8.4") return Runtime::PHP_8_4;
    if (s == "ruby-3.0") return Runtime::RUBY_3_0;
    if (s == "ruby-3.1") return Runtime::RUBY_3_1;
    if (s == "ruby-3.2") return Runtime::RUBY_3_2;
    if (s == "ruby-3.3") return Runtime::RUBY_3_3;
    if (s == "ruby-3.4") return Runtime::RUBY_3_4;
    if (s == "ruby-4.0") return Runtime::RUBY_4_0;
    if (s == "python-3.8") return Runtime::PYTHON_3_8;
    if (s == "python-3.9") return Runtime::PYTHON_3_9;
    if (s == "python-3.10") return Runtime::PYTHON_3_10;
    if (s == "python-3.11") return Runtime::PYTHON_3_11;
    if (s == "python-3.12") return Runtime::PYTHON_3_12;
    if (s == "python-3.13") return Runtime::PYTHON_3_13;
    if (s == "python-3.14") return Runtime::PYTHON_3_14;
    if (s == "python-ml-3.11") return Runtime::PYTHON_ML_3_11;
    if (s == "python-ml-3.12") return Runtime::PYTHON_ML_3_12;
    if (s == "python-ml-3.13") return Runtime::PYTHON_ML_3_13;
    if (s == "deno-1.21") return Runtime::DENO_1_21;
    if (s == "deno-1.24") return Runtime::DENO_1_24;
    if (s == "deno-1.35") return Runtime::DENO_1_35;
    if (s == "deno-1.40") return Runtime::DENO_1_40;
    if (s == "deno-1.46") return Runtime::DENO_1_46;
    if (s == "deno-2.0") return Runtime::DENO_2_0;
    if (s == "deno-2.5") return Runtime::DENO_2_5;
    if (s == "deno-2.6") return Runtime::DENO_2_6;
    if (s == "dart-2.15") return Runtime::DART_2_15;
    if (s == "dart-2.16") return Runtime::DART_2_16;
    if (s == "dart-2.17") return Runtime::DART_2_17;
    if (s == "dart-2.18") return Runtime::DART_2_18;
    if (s == "dart-2.19") return Runtime::DART_2_19;
    if (s == "dart-3.0") return Runtime::DART_3_0;
    if (s == "dart-3.1") return Runtime::DART_3_1;
    if (s == "dart-3.3") return Runtime::DART_3_3;
    if (s == "dart-3.5") return Runtime::DART_3_5;
    if (s == "dart-3.8") return Runtime::DART_3_8;
    if (s == "dart-3.9") return Runtime::DART_3_9;
    if (s == "dart-3.10") return Runtime::DART_3_10;
    if (s == "dart-3.11") return Runtime::DART_3_11;
    if (s == "dotnet-6.0") return Runtime::DOTNET_6_0;
    if (s == "dotnet-7.0") return Runtime::DOTNET_7_0;
    if (s == "dotnet-8.0") return Runtime::DOTNET_8_0;
    if (s == "dotnet-10") return Runtime::DOTNET_10;
    if (s == "java-8.0") return Runtime::JAVA_8_0;
    if (s == "java-11.0") return Runtime::JAVA_11_0;
    if (s == "java-17.0") return Runtime::JAVA_17_0;
    if (s == "java-18.0") return Runtime::JAVA_18_0;
    if (s == "java-21.0") return Runtime::JAVA_21_0;
    if (s == "java-22") return Runtime::JAVA_22;
    if (s == "java-25") return Runtime::JAVA_25;
    if (s == "swift-5.5") return Runtime::SWIFT_5_5;
    if (s == "swift-5.8") return Runtime::SWIFT_5_8;
    if (s == "swift-5.9") return Runtime::SWIFT_5_9;
    if (s == "swift-5.10") return Runtime::SWIFT_5_10;
    if (s == "swift-6.2") return Runtime::SWIFT_6_2;
    if (s == "kotlin-1.6") return Runtime::KOTLIN_1_6;
    if (s == "kotlin-1.8") return Runtime::KOTLIN_1_8;
    if (s == "kotlin-1.9") return Runtime::KOTLIN_1_9;
    if (s == "kotlin-2.0") return Runtime::KOTLIN_2_0;
    if (s == "kotlin-2.3") return Runtime::KOTLIN_2_3;
    if (s == "cpp-17") return Runtime::CPP_17;
    if (s == "cpp-20") return Runtime::CPP_20;
    if (s == "bun-1.0") return Runtime::BUN_1_0;
    if (s == "bun-1.1") return Runtime::BUN_1_1;
    if (s == "bun-1.2") return Runtime::BUN_1_2;
    if (s == "bun-1.3") return Runtime::BUN_1_3;
    if (s == "go-1.23") return Runtime::GO_1_23;
    if (s == "go-1.24") return Runtime::GO_1_24;
    if (s == "go-1.25") return Runtime::GO_1_25;
    if (s == "go-1.26") return Runtime::GO_1_26;
    if (s == "static-1") return Runtime::STATIC_1;
    if (s == "flutter-3.24") return Runtime::FLUTTER_3_24;
    if (s == "flutter-3.27") return Runtime::FLUTTER_3_27;
    if (s == "flutter-3.29") return Runtime::FLUTTER_3_29;
    if (s == "flutter-3.32") return Runtime::FLUTTER_3_32;
    if (s == "flutter-3.35") return Runtime::FLUTTER_3_35;
    if (s == "flutter-3.38") return Runtime::FLUTTER_3_38;
    if (s == "flutter-3.41") return Runtime::FLUTTER_3_41;
    throw std::invalid_argument("Unknown Runtime value: " + s);
}

inline void from_json(const nlohmann::json& j, Runtime& v) {
    v = runtimeFromString(j.get<std::string>());
}

inline void to_json(nlohmann::json& j, const Runtime& v) {
    j = toString(v);
}

enum class Runtimes {
    NODE_14_5,
    NODE_16_0,
    NODE_18_0,
    NODE_19_0,
    NODE_20_0,
    NODE_21_0,
    NODE_22,
    NODE_23,
    NODE_24,
    NODE_25,
    PHP_8_0,
    PHP_8_1,
    PHP_8_2,
    PHP_8_3,
    PHP_8_4,
    RUBY_3_0,
    RUBY_3_1,
    RUBY_3_2,
    RUBY_3_3,
    RUBY_3_4,
    RUBY_4_0,
    PYTHON_3_8,
    PYTHON_3_9,
    PYTHON_3_10,
    PYTHON_3_11,
    PYTHON_3_12,
    PYTHON_3_13,
    PYTHON_3_14,
    PYTHON_ML_3_11,
    PYTHON_ML_3_12,
    PYTHON_ML_3_13,
    DENO_1_21,
    DENO_1_24,
    DENO_1_35,
    DENO_1_40,
    DENO_1_46,
    DENO_2_0,
    DENO_2_5,
    DENO_2_6,
    DART_2_15,
    DART_2_16,
    DART_2_17,
    DART_2_18,
    DART_2_19,
    DART_3_0,
    DART_3_1,
    DART_3_3,
    DART_3_5,
    DART_3_8,
    DART_3_9,
    DART_3_10,
    DART_3_11,
    DOTNET_6_0,
    DOTNET_7_0,
    DOTNET_8_0,
    DOTNET_10,
    JAVA_8_0,
    JAVA_11_0,
    JAVA_17_0,
    JAVA_18_0,
    JAVA_21_0,
    JAVA_22,
    JAVA_25,
    SWIFT_5_5,
    SWIFT_5_8,
    SWIFT_5_9,
    SWIFT_5_10,
    SWIFT_6_2,
    KOTLIN_1_6,
    KOTLIN_1_8,
    KOTLIN_1_9,
    KOTLIN_2_0,
    KOTLIN_2_3,
    CPP_17,
    CPP_20,
    BUN_1_0,
    BUN_1_1,
    BUN_1_2,
    BUN_1_3,
    GO_1_23,
    GO_1_24,
    GO_1_25,
    GO_1_26,
    STATIC_1,
    FLUTTER_3_24,
    FLUTTER_3_27,
    FLUTTER_3_29,
    FLUTTER_3_32,
    FLUTTER_3_35,
    FLUTTER_3_38,
    FLUTTER_3_41
};

inline std::string toString(Runtimes value) {
    switch (value) {
        case Runtimes :: NODE_14_5: return "node-14.5";
        case Runtimes :: NODE_16_0: return "node-16.0";
        case Runtimes :: NODE_18_0: return "node-18.0";
        case Runtimes :: NODE_19_0: return "node-19.0";
        case Runtimes :: NODE_20_0: return "node-20.0";
        case Runtimes :: NODE_21_0: return "node-21.0";
        case Runtimes :: NODE_22: return "node-22";
        case Runtimes :: NODE_23: return "node-23";
        case Runtimes :: NODE_24: return "node-24";
        case Runtimes :: NODE_25: return "node-25";
        case Runtimes :: PHP_8_0: return "php-8.0";
        case Runtimes :: PHP_8_1: return "php-8.1";
        case Runtimes :: PHP_8_2: return "php-8.2";
        case Runtimes :: PHP_8_3: return "php-8.3";
        case Runtimes :: PHP_8_4: return "php-8.4";
        case Runtimes :: RUBY_3_0: return "ruby-3.0";
        case Runtimes :: RUBY_3_1: return "ruby-3.1";
        case Runtimes :: RUBY_3_2: return "ruby-3.2";
        case Runtimes :: RUBY_3_3: return "ruby-3.3";
        case Runtimes :: RUBY_3_4: return "ruby-3.4";
        case Runtimes :: RUBY_4_0: return "ruby-4.0";
        case Runtimes :: PYTHON_3_8: return "python-3.8";
        case Runtimes :: PYTHON_3_9: return "python-3.9";
        case Runtimes :: PYTHON_3_10: return "python-3.10";
        case Runtimes :: PYTHON_3_11: return "python-3.11";
        case Runtimes :: PYTHON_3_12: return "python-3.12";
        case Runtimes :: PYTHON_3_13: return "python-3.13";
        case Runtimes :: PYTHON_3_14: return "python-3.14";
        case Runtimes :: PYTHON_ML_3_11: return "python-ml-3.11";
        case Runtimes :: PYTHON_ML_3_12: return "python-ml-3.12";
        case Runtimes :: PYTHON_ML_3_13: return "python-ml-3.13";
        case Runtimes :: DENO_1_21: return "deno-1.21";
        case Runtimes :: DENO_1_24: return "deno-1.24";
        case Runtimes :: DENO_1_35: return "deno-1.35";
        case Runtimes :: DENO_1_40: return "deno-1.40";
        case Runtimes :: DENO_1_46: return "deno-1.46";
        case Runtimes :: DENO_2_0: return "deno-2.0";
        case Runtimes :: DENO_2_5: return "deno-2.5";
        case Runtimes :: DENO_2_6: return "deno-2.6";
        case Runtimes :: DART_2_15: return "dart-2.15";
        case Runtimes :: DART_2_16: return "dart-2.16";
        case Runtimes :: DART_2_17: return "dart-2.17";
        case Runtimes :: DART_2_18: return "dart-2.18";
        case Runtimes :: DART_2_19: return "dart-2.19";
        case Runtimes :: DART_3_0: return "dart-3.0";
        case Runtimes :: DART_3_1: return "dart-3.1";
        case Runtimes :: DART_3_3: return "dart-3.3";
        case Runtimes :: DART_3_5: return "dart-3.5";
        case Runtimes :: DART_3_8: return "dart-3.8";
        case Runtimes :: DART_3_9: return "dart-3.9";
        case Runtimes :: DART_3_10: return "dart-3.10";
        case Runtimes :: DART_3_11: return "dart-3.11";
        case Runtimes :: DOTNET_6_0: return "dotnet-6.0";
        case Runtimes :: DOTNET_7_0: return "dotnet-7.0";
        case Runtimes :: DOTNET_8_0: return "dotnet-8.0";
        case Runtimes :: DOTNET_10: return "dotnet-10";
        case Runtimes :: JAVA_8_0: return "java-8.0";
        case Runtimes :: JAVA_11_0: return "java-11.0";
        case Runtimes :: JAVA_17_0: return "java-17.0";
        case Runtimes :: JAVA_18_0: return "java-18.0";
        case Runtimes :: JAVA_21_0: return "java-21.0";
        case Runtimes :: JAVA_22: return "java-22";
        case Runtimes :: JAVA_25: return "java-25";
        case Runtimes :: SWIFT_5_5: return "swift-5.5";
        case Runtimes :: SWIFT_5_8: return "swift-5.8";
        case Runtimes :: SWIFT_5_9: return "swift-5.9";
        case Runtimes :: SWIFT_5_10: return "swift-5.10";
        case Runtimes :: SWIFT_6_2: return "swift-6.2";
        case Runtimes :: KOTLIN_1_6: return "kotlin-1.6";
        case Runtimes :: KOTLIN_1_8: return "kotlin-1.8";
        case Runtimes :: KOTLIN_1_9: return "kotlin-1.9";
        case Runtimes :: KOTLIN_2_0: return "kotlin-2.0";
        case Runtimes :: KOTLIN_2_3: return "kotlin-2.3";
        case Runtimes :: CPP_17: return "cpp-17";
        case Runtimes :: CPP_20: return "cpp-20";
        case Runtimes :: BUN_1_0: return "bun-1.0";
        case Runtimes :: BUN_1_1: return "bun-1.1";
        case Runtimes :: BUN_1_2: return "bun-1.2";
        case Runtimes :: BUN_1_3: return "bun-1.3";
        case Runtimes :: GO_1_23: return "go-1.23";
        case Runtimes :: GO_1_24: return "go-1.24";
        case Runtimes :: GO_1_25: return "go-1.25";
        case Runtimes :: GO_1_26: return "go-1.26";
        case Runtimes :: STATIC_1: return "static-1";
        case Runtimes :: FLUTTER_3_24: return "flutter-3.24";
        case Runtimes :: FLUTTER_3_27: return "flutter-3.27";
        case Runtimes :: FLUTTER_3_29: return "flutter-3.29";
        case Runtimes :: FLUTTER_3_32: return "flutter-3.32";
        case Runtimes :: FLUTTER_3_35: return "flutter-3.35";
        case Runtimes :: FLUTTER_3_38: return "flutter-3.38";
        case Runtimes :: FLUTTER_3_41: return "flutter-3.41";
        default: throw std::invalid_argument("Unknown Runtimes enum value");
    }
}

inline Runtimes runtimesFromString(const std::string& s) {
    if (s == "node-14.5") return Runtimes::NODE_14_5;
    if (s == "node-16.0") return Runtimes::NODE_16_0;
    if (s == "node-18.0") return Runtimes::NODE_18_0;
    if (s == "node-19.0") return Runtimes::NODE_19_0;
    if (s == "node-20.0") return Runtimes::NODE_20_0;
    if (s == "node-21.0") return Runtimes::NODE_21_0;
    if (s == "node-22") return Runtimes::NODE_22;
    if (s == "node-23") return Runtimes::NODE_23;
    if (s == "node-24") return Runtimes::NODE_24;
    if (s == "node-25") return Runtimes::NODE_25;
    if (s == "php-8.0") return Runtimes::PHP_8_0;
    if (s == "php-8.1") return Runtimes::PHP_8_1;
    if (s == "php-8.2") return Runtimes::PHP_8_2;
    if (s == "php-8.3") return Runtimes::PHP_8_3;
    if (s == "php-8.4") return Runtimes::PHP_8_4;
    if (s == "ruby-3.0") return Runtimes::RUBY_3_0;
    if (s == "ruby-3.1") return Runtimes::RUBY_3_1;
    if (s == "ruby-3.2") return Runtimes::RUBY_3_2;
    if (s == "ruby-3.3") return Runtimes::RUBY_3_3;
    if (s == "ruby-3.4") return Runtimes::RUBY_3_4;
    if (s == "ruby-4.0") return Runtimes::RUBY_4_0;
    if (s == "python-3.8") return Runtimes::PYTHON_3_8;
    if (s == "python-3.9") return Runtimes::PYTHON_3_9;
    if (s == "python-3.10") return Runtimes::PYTHON_3_10;
    if (s == "python-3.11") return Runtimes::PYTHON_3_11;
    if (s == "python-3.12") return Runtimes::PYTHON_3_12;
    if (s == "python-3.13") return Runtimes::PYTHON_3_13;
    if (s == "python-3.14") return Runtimes::PYTHON_3_14;
    if (s == "python-ml-3.11") return Runtimes::PYTHON_ML_3_11;
    if (s == "python-ml-3.12") return Runtimes::PYTHON_ML_3_12;
    if (s == "python-ml-3.13") return Runtimes::PYTHON_ML_3_13;
    if (s == "deno-1.21") return Runtimes::DENO_1_21;
    if (s == "deno-1.24") return Runtimes::DENO_1_24;
    if (s == "deno-1.35") return Runtimes::DENO_1_35;
    if (s == "deno-1.40") return Runtimes::DENO_1_40;
    if (s == "deno-1.46") return Runtimes::DENO_1_46;
    if (s == "deno-2.0") return Runtimes::DENO_2_0;
    if (s == "deno-2.5") return Runtimes::DENO_2_5;
    if (s == "deno-2.6") return Runtimes::DENO_2_6;
    if (s == "dart-2.15") return Runtimes::DART_2_15;
    if (s == "dart-2.16") return Runtimes::DART_2_16;
    if (s == "dart-2.17") return Runtimes::DART_2_17;
    if (s == "dart-2.18") return Runtimes::DART_2_18;
    if (s == "dart-2.19") return Runtimes::DART_2_19;
    if (s == "dart-3.0") return Runtimes::DART_3_0;
    if (s == "dart-3.1") return Runtimes::DART_3_1;
    if (s == "dart-3.3") return Runtimes::DART_3_3;
    if (s == "dart-3.5") return Runtimes::DART_3_5;
    if (s == "dart-3.8") return Runtimes::DART_3_8;
    if (s == "dart-3.9") return Runtimes::DART_3_9;
    if (s == "dart-3.10") return Runtimes::DART_3_10;
    if (s == "dart-3.11") return Runtimes::DART_3_11;
    if (s == "dotnet-6.0") return Runtimes::DOTNET_6_0;
    if (s == "dotnet-7.0") return Runtimes::DOTNET_7_0;
    if (s == "dotnet-8.0") return Runtimes::DOTNET_8_0;
    if (s == "dotnet-10") return Runtimes::DOTNET_10;
    if (s == "java-8.0") return Runtimes::JAVA_8_0;
    if (s == "java-11.0") return Runtimes::JAVA_11_0;
    if (s == "java-17.0") return Runtimes::JAVA_17_0;
    if (s == "java-18.0") return Runtimes::JAVA_18_0;
    if (s == "java-21.0") return Runtimes::JAVA_21_0;
    if (s == "java-22") return Runtimes::JAVA_22;
    if (s == "java-25") return Runtimes::JAVA_25;
    if (s == "swift-5.5") return Runtimes::SWIFT_5_5;
    if (s == "swift-5.8") return Runtimes::SWIFT_5_8;
    if (s == "swift-5.9") return Runtimes::SWIFT_5_9;
    if (s == "swift-5.10") return Runtimes::SWIFT_5_10;
    if (s == "swift-6.2") return Runtimes::SWIFT_6_2;
    if (s == "kotlin-1.6") return Runtimes::KOTLIN_1_6;
    if (s == "kotlin-1.8") return Runtimes::KOTLIN_1_8;
    if (s == "kotlin-1.9") return Runtimes::KOTLIN_1_9;
    if (s == "kotlin-2.0") return Runtimes::KOTLIN_2_0;
    if (s == "kotlin-2.3") return Runtimes::KOTLIN_2_3;
    if (s == "cpp-17") return Runtimes::CPP_17;
    if (s == "cpp-20") return Runtimes::CPP_20;
    if (s == "bun-1.0") return Runtimes::BUN_1_0;
    if (s == "bun-1.1") return Runtimes::BUN_1_1;
    if (s == "bun-1.2") return Runtimes::BUN_1_2;
    if (s == "bun-1.3") return Runtimes::BUN_1_3;
    if (s == "go-1.23") return Runtimes::GO_1_23;
    if (s == "go-1.24") return Runtimes::GO_1_24;
    if (s == "go-1.25") return Runtimes::GO_1_25;
    if (s == "go-1.26") return Runtimes::GO_1_26;
    if (s == "static-1") return Runtimes::STATIC_1;
    if (s == "flutter-3.24") return Runtimes::FLUTTER_3_24;
    if (s == "flutter-3.27") return Runtimes::FLUTTER_3_27;
    if (s == "flutter-3.29") return Runtimes::FLUTTER_3_29;
    if (s == "flutter-3.32") return Runtimes::FLUTTER_3_32;
    if (s == "flutter-3.35") return Runtimes::FLUTTER_3_35;
    if (s == "flutter-3.38") return Runtimes::FLUTTER_3_38;
    if (s == "flutter-3.41") return Runtimes::FLUTTER_3_41;
    throw std::invalid_argument("Unknown Runtimes value: " + s);
}

inline void from_json(const nlohmann::json& j, Runtimes& v) {
    v = runtimesFromString(j.get<std::string>());
}

inline void to_json(nlohmann::json& j, const Runtimes& v) {
    j = toString(v);
}

enum class UseCases {
    STARTER,
    DATABASES,
    AI,
    MESSAGING,
    UTILITIES,
    DEV_TOOLS,
    AUTH
};

inline std::string toString(UseCases value) {
    switch (value) {
        case UseCases :: STARTER: return "starter";
        case UseCases :: DATABASES: return "databases";
        case UseCases :: AI: return "ai";
        case UseCases :: MESSAGING: return "messaging";
        case UseCases :: UTILITIES: return "utilities";
        case UseCases :: DEV_TOOLS: return "dev-tools";
        case UseCases :: AUTH: return "auth";
        default: throw std::invalid_argument("Unknown UseCases enum value");
    }
}

inline UseCases useCasesFromString(const std::string& s) {
    if (s == "starter") return UseCases::STARTER;
    if (s == "databases") return UseCases::DATABASES;
    if (s == "ai") return UseCases::AI;
    if (s == "messaging") return UseCases::MESSAGING;
    if (s == "utilities") return UseCases::UTILITIES;
    if (s == "dev-tools") return UseCases::DEV_TOOLS;
    if (s == "auth") return UseCases::AUTH;
    throw std::invalid_argument("Unknown UseCases value: " + s);
}

inline void from_json(const nlohmann::json& j, UseCases& v) {
    v = useCasesFromString(j.get<std::string>());
}

inline void to_json(nlohmann::json& j, const UseCases& v) {
    j = toString(v);
}

enum class TemplateReferenceType {
    COMMIT,
    BRANCH,
    TAG
};

inline std::string toString(TemplateReferenceType value) {
    switch (value) {
        case TemplateReferenceType :: COMMIT: return "commit";
        case TemplateReferenceType :: BRANCH: return "branch";
        case TemplateReferenceType :: TAG: return "tag";
        default: throw std::invalid_argument("Unknown TemplateReferenceType enum value");
    }
}

inline TemplateReferenceType templateReferenceTypeFromString(const std::string& s) {
    if (s == "commit") return TemplateReferenceType::COMMIT;
    if (s == "branch") return TemplateReferenceType::BRANCH;
    if (s == "tag") return TemplateReferenceType::TAG;
    throw std::invalid_argument("Unknown TemplateReferenceType value: " + s);
}

inline void from_json(const nlohmann::json& j, TemplateReferenceType& v) {
    v = templateReferenceTypeFromString(j.get<std::string>());
}

inline void to_json(nlohmann::json& j, const TemplateReferenceType& v) {
    j = toString(v);
}

enum class VCSReferenceType {
    BRANCH,
    COMMIT
};

inline std::string toString(VCSReferenceType value) {
    switch (value) {
        case VCSReferenceType :: BRANCH: return "branch";
        case VCSReferenceType :: COMMIT: return "commit";
        default: throw std::invalid_argument("Unknown VCSReferenceType enum value");
    }
}

inline VCSReferenceType vCSReferenceTypeFromString(const std::string& s) {
    if (s == "branch") return VCSReferenceType::BRANCH;
    if (s == "commit") return VCSReferenceType::COMMIT;
    throw std::invalid_argument("Unknown VCSReferenceType value: " + s);
}

inline void from_json(const nlohmann::json& j, VCSReferenceType& v) {
    v = vCSReferenceTypeFromString(j.get<std::string>());
}

inline void to_json(nlohmann::json& j, const VCSReferenceType& v) {
    j = toString(v);
}

enum class DeploymentDownloadType {
    SOURCE,
    OUTPUT
};

inline std::string toString(DeploymentDownloadType value) {
    switch (value) {
        case DeploymentDownloadType :: SOURCE: return "source";
        case DeploymentDownloadType :: OUTPUT: return "output";
        default: throw std::invalid_argument("Unknown DeploymentDownloadType enum value");
    }
}

inline DeploymentDownloadType deploymentDownloadTypeFromString(const std::string& s) {
    if (s == "source") return DeploymentDownloadType::SOURCE;
    if (s == "output") return DeploymentDownloadType::OUTPUT;
    throw std::invalid_argument("Unknown DeploymentDownloadType value: " + s);
}

inline void from_json(const nlohmann::json& j, DeploymentDownloadType& v) {
    v = deploymentDownloadTypeFromString(j.get<std::string>());
}

inline void to_json(nlohmann::json& j, const DeploymentDownloadType& v) {
    j = toString(v);
}

enum class ExecutionMethod {
    GET,
    POST,
    PUT,
    PATCH,
    DELETE,
    OPTIONS,
    HEAD
};

inline std::string toString(ExecutionMethod value) {
    switch (value) {
        case ExecutionMethod :: GET: return "GET";
        case ExecutionMethod :: POST: return "POST";
        case ExecutionMethod :: PUT: return "PUT";
        case ExecutionMethod :: PATCH: return "PATCH";
        case ExecutionMethod :: DELETE: return "DELETE";
        case ExecutionMethod :: OPTIONS: return "OPTIONS";
        case ExecutionMethod :: HEAD: return "HEAD";
        default: throw std::invalid_argument("Unknown ExecutionMethod enum value");
    }
}

inline ExecutionMethod executionMethodFromString(const std::string& s) {
    if (s == "GET") return ExecutionMethod::GET;
    if (s == "POST") return ExecutionMethod::POST;
    if (s == "PUT") return ExecutionMethod::PUT;
    if (s == "PATCH") return ExecutionMethod::PATCH;
    if (s == "DELETE") return ExecutionMethod::DELETE;
    if (s == "OPTIONS") return ExecutionMethod::OPTIONS;
    if (s == "HEAD") return ExecutionMethod::HEAD;
    throw std::invalid_argument("Unknown ExecutionMethod value: " + s);
}

inline void from_json(const nlohmann::json& j, ExecutionMethod& v) {
    v = executionMethodFromString(j.get<std::string>());
}

inline void to_json(nlohmann::json& j, const ExecutionMethod& v) {
    j = toString(v);
}

enum class Compression {
    NONE,
    GZIP,
    ZSTD
};

inline std::string toString(Compression value) {
    switch (value) {
        case Compression :: NONE: return "none";
        case Compression :: GZIP: return "gzip";
        case Compression :: ZSTD: return "zstd";
        default: throw std::invalid_argument("Unknown Compression enum value");
    }
}

inline Compression compressionFromString(const std::string& s) {
    if (s == "none") return Compression::NONE;
    if (s == "gzip") return Compression::GZIP;
    if (s == "zstd") return Compression::ZSTD;
    throw std::invalid_argument("Unknown Compression value: " + s);
}

inline void from_json(const nlohmann::json& j, Compression& v) {
    v = compressionFromString(j.get<std::string>());
}

inline void to_json(nlohmann::json& j, const Compression& v) {
    j = toString(v);
}

enum class ImageGravity {
    CENTER,
    TOP_LEFT,
    TOP,
    TOP_RIGHT,
    LEFT,
    RIGHT,
    BOTTOM_LEFT,
    BOTTOM,
    BOTTOM_RIGHT
};

inline std::string toString(ImageGravity value) {
    switch (value) {
        case ImageGravity :: CENTER: return "center";
        case ImageGravity :: TOP_LEFT: return "top-left";
        case ImageGravity :: TOP: return "top";
        case ImageGravity :: TOP_RIGHT: return "top-right";
        case ImageGravity :: LEFT: return "left";
        case ImageGravity :: RIGHT: return "right";
        case ImageGravity :: BOTTOM_LEFT: return "bottom-left";
        case ImageGravity :: BOTTOM: return "bottom";
        case ImageGravity :: BOTTOM_RIGHT: return "bottom-right";
        default: throw std::invalid_argument("Unknown ImageGravity enum value");
    }
}

inline ImageGravity imageGravityFromString(const std::string& s) {
    if (s == "center") return ImageGravity::CENTER;
    if (s == "top-left") return ImageGravity::TOP_LEFT;
    if (s == "top") return ImageGravity::TOP;
    if (s == "top-right") return ImageGravity::TOP_RIGHT;
    if (s == "left") return ImageGravity::LEFT;
    if (s == "right") return ImageGravity::RIGHT;
    if (s == "bottom-left") return ImageGravity::BOTTOM_LEFT;
    if (s == "bottom") return ImageGravity::BOTTOM;
    if (s == "bottom-right") return ImageGravity::BOTTOM_RIGHT;
    throw std::invalid_argument("Unknown ImageGravity value: " + s);
}

inline void from_json(const nlohmann::json& j, ImageGravity& v) {
    v = imageGravityFromString(j.get<std::string>());
}

inline void to_json(nlohmann::json& j, const ImageGravity& v) {
    j = toString(v);
}

enum class ImageFormat {
    JPG,
    JPEG,
    PNG,
    WEBP,
    HEIC,
    AVIF,
    GIF
};

inline std::string toString(ImageFormat value) {
    switch (value) {
        case ImageFormat :: JPG: return "jpg";
        case ImageFormat :: JPEG: return "jpeg";
        case ImageFormat :: PNG: return "png";
        case ImageFormat :: WEBP: return "webp";
        case ImageFormat :: HEIC: return "heic";
        case ImageFormat :: AVIF: return "avif";
        case ImageFormat :: GIF: return "gif";
        default: throw std::invalid_argument("Unknown ImageFormat enum value");
    }
}

inline ImageFormat imageFormatFromString(const std::string& s) {
    if (s == "jpg") return ImageFormat::JPG;
    if (s == "jpeg") return ImageFormat::JPEG;
    if (s == "png") return ImageFormat::PNG;
    if (s == "webp") return ImageFormat::WEBP;
    if (s == "heic") return ImageFormat::HEIC;
    if (s == "avif") return ImageFormat::AVIF;
    if (s == "gif") return ImageFormat::GIF;
    throw std::invalid_argument("Unknown ImageFormat value: " + s);
}

inline void from_json(const nlohmann::json& j, ImageFormat& v) {
    v = imageFormatFromString(j.get<std::string>());
}

inline void to_json(nlohmann::json& j, const ImageFormat& v) {
    j = toString(v);
}

enum class TablesDBIndexType {
    KEY,
    FULLTEXT,
    UNIQUE,
    SPATIAL
};

inline std::string toString(TablesDBIndexType value) {
    switch (value) {
        case TablesDBIndexType :: KEY: return "key";
        case TablesDBIndexType :: FULLTEXT: return "fulltext";
        case TablesDBIndexType :: UNIQUE: return "unique";
        case TablesDBIndexType :: SPATIAL: return "spatial";
        default: throw std::invalid_argument("Unknown TablesDBIndexType enum value");
    }
}

inline TablesDBIndexType tablesDBIndexTypeFromString(const std::string& s) {
    if (s == "key") return TablesDBIndexType::KEY;
    if (s == "fulltext") return TablesDBIndexType::FULLTEXT;
    if (s == "unique") return TablesDBIndexType::UNIQUE;
    if (s == "spatial") return TablesDBIndexType::SPATIAL;
    throw std::invalid_argument("Unknown TablesDBIndexType value: " + s);
}

inline void from_json(const nlohmann::json& j, TablesDBIndexType& v) {
    v = tablesDBIndexTypeFromString(j.get<std::string>());
}

inline void to_json(nlohmann::json& j, const TablesDBIndexType& v) {
    j = toString(v);
}

enum class PasswordHash {
    SHA1,
    SHA224,
    SHA256,
    SHA384,
    SHA512_224,
    SHA512_256,
    SHA512,
    SHA3_224,
    SHA3_256,
    SHA3_384,
    SHA3_512
};

inline std::string toString(PasswordHash value) {
    switch (value) {
        case PasswordHash :: SHA1: return "sha1";
        case PasswordHash :: SHA224: return "sha224";
        case PasswordHash :: SHA256: return "sha256";
        case PasswordHash :: SHA384: return "sha384";
        case PasswordHash :: SHA512_224: return "sha512/224";
        case PasswordHash :: SHA512_256: return "sha512/256";
        case PasswordHash :: SHA512: return "sha512";
        case PasswordHash :: SHA3_224: return "sha3-224";
        case PasswordHash :: SHA3_256: return "sha3-256";
        case PasswordHash :: SHA3_384: return "sha3-384";
        case PasswordHash :: SHA3_512: return "sha3-512";
        default: throw std::invalid_argument("Unknown PasswordHash enum value");
    }
}

inline PasswordHash passwordHashFromString(const std::string& s) {
    if (s == "sha1") return PasswordHash::SHA1;
    if (s == "sha224") return PasswordHash::SHA224;
    if (s == "sha256") return PasswordHash::SHA256;
    if (s == "sha384") return PasswordHash::SHA384;
    if (s == "sha512/224") return PasswordHash::SHA512_224;
    if (s == "sha512/256") return PasswordHash::SHA512_256;
    if (s == "sha512") return PasswordHash::SHA512;
    if (s == "sha3-224") return PasswordHash::SHA3_224;
    if (s == "sha3-256") return PasswordHash::SHA3_256;
    if (s == "sha3-384") return PasswordHash::SHA3_384;
    if (s == "sha3-512") return PasswordHash::SHA3_512;
    throw std::invalid_argument("Unknown PasswordHash value: " + s);
}

inline void from_json(const nlohmann::json& j, PasswordHash& v) {
    v = passwordHashFromString(j.get<std::string>());
}

inline void to_json(nlohmann::json& j, const PasswordHash& v) {
    j = toString(v);
}

enum class MessagingProviderType {
    EMAIL,
    SMS,
    PUSH
};

inline std::string toString(MessagingProviderType value) {
    switch (value) {
        case MessagingProviderType :: EMAIL: return "email";
        case MessagingProviderType :: SMS: return "sms";
        case MessagingProviderType :: PUSH: return "push";
        default: throw std::invalid_argument("Unknown MessagingProviderType enum value");
    }
}

inline MessagingProviderType messagingProviderTypeFromString(const std::string& s) {
    if (s == "email") return MessagingProviderType::EMAIL;
    if (s == "sms") return MessagingProviderType::SMS;
    if (s == "push") return MessagingProviderType::PUSH;
    throw std::invalid_argument("Unknown MessagingProviderType value: " + s);
}

inline void from_json(const nlohmann::json& j, MessagingProviderType& v) {
    v = messagingProviderTypeFromString(j.get<std::string>());
}

inline void to_json(nlohmann::json& j, const MessagingProviderType& v) {
    j = toString(v);
}

enum class DatabaseType {
    LEGACY,
    TABLESDB,
    DOCUMENTSDB,
    VECTORSDB
};

inline std::string toString(DatabaseType value) {
    switch (value) {
        case DatabaseType :: LEGACY: return "legacy";
        case DatabaseType :: TABLESDB: return "tablesdb";
        case DatabaseType :: DOCUMENTSDB: return "documentsdb";
        case DatabaseType :: VECTORSDB: return "vectorsdb";
        default: throw std::invalid_argument("Unknown DatabaseType enum value");
    }
}

inline DatabaseType databaseTypeFromString(const std::string& s) {
    if (s == "legacy") return DatabaseType::LEGACY;
    if (s == "tablesdb") return DatabaseType::TABLESDB;
    if (s == "documentsdb") return DatabaseType::DOCUMENTSDB;
    if (s == "vectorsdb") return DatabaseType::VECTORSDB;
    throw std::invalid_argument("Unknown DatabaseType value: " + s);
}

inline void from_json(const nlohmann::json& j, DatabaseType& v) {
    v = databaseTypeFromString(j.get<std::string>());
}

inline void to_json(nlohmann::json& j, const DatabaseType& v) {
    j = toString(v);
}

enum class AttributeStatus {
    AVAILABLE,
    PROCESSING,
    DELETING,
    STUCK,
    FAILED
};

inline std::string toString(AttributeStatus value) {
    switch (value) {
        case AttributeStatus :: AVAILABLE: return "available";
        case AttributeStatus :: PROCESSING: return "processing";
        case AttributeStatus :: DELETING: return "deleting";
        case AttributeStatus :: STUCK: return "stuck";
        case AttributeStatus :: FAILED: return "failed";
        default: throw std::invalid_argument("Unknown AttributeStatus enum value");
    }
}

inline AttributeStatus attributeStatusFromString(const std::string& s) {
    if (s == "available") return AttributeStatus::AVAILABLE;
    if (s == "processing") return AttributeStatus::PROCESSING;
    if (s == "deleting") return AttributeStatus::DELETING;
    if (s == "stuck") return AttributeStatus::STUCK;
    if (s == "failed") return AttributeStatus::FAILED;
    throw std::invalid_argument("Unknown AttributeStatus value: " + s);
}

inline void from_json(const nlohmann::json& j, AttributeStatus& v) {
    v = attributeStatusFromString(j.get<std::string>());
}

inline void to_json(nlohmann::json& j, const AttributeStatus& v) {
    j = toString(v);
}

enum class ColumnStatus {
    AVAILABLE,
    PROCESSING,
    DELETING,
    STUCK,
    FAILED
};

inline std::string toString(ColumnStatus value) {
    switch (value) {
        case ColumnStatus :: AVAILABLE: return "available";
        case ColumnStatus :: PROCESSING: return "processing";
        case ColumnStatus :: DELETING: return "deleting";
        case ColumnStatus :: STUCK: return "stuck";
        case ColumnStatus :: FAILED: return "failed";
        default: throw std::invalid_argument("Unknown ColumnStatus enum value");
    }
}

inline ColumnStatus columnStatusFromString(const std::string& s) {
    if (s == "available") return ColumnStatus::AVAILABLE;
    if (s == "processing") return ColumnStatus::PROCESSING;
    if (s == "deleting") return ColumnStatus::DELETING;
    if (s == "stuck") return ColumnStatus::STUCK;
    if (s == "failed") return ColumnStatus::FAILED;
    throw std::invalid_argument("Unknown ColumnStatus value: " + s);
}

inline void from_json(const nlohmann::json& j, ColumnStatus& v) {
    v = columnStatusFromString(j.get<std::string>());
}

inline void to_json(nlohmann::json& j, const ColumnStatus& v) {
    j = toString(v);
}

enum class IndexStatus {
    AVAILABLE,
    PROCESSING,
    DELETING,
    STUCK,
    FAILED
};

inline std::string toString(IndexStatus value) {
    switch (value) {
        case IndexStatus :: AVAILABLE: return "available";
        case IndexStatus :: PROCESSING: return "processing";
        case IndexStatus :: DELETING: return "deleting";
        case IndexStatus :: STUCK: return "stuck";
        case IndexStatus :: FAILED: return "failed";
        default: throw std::invalid_argument("Unknown IndexStatus enum value");
    }
}

inline IndexStatus indexStatusFromString(const std::string& s) {
    if (s == "available") return IndexStatus::AVAILABLE;
    if (s == "processing") return IndexStatus::PROCESSING;
    if (s == "deleting") return IndexStatus::DELETING;
    if (s == "stuck") return IndexStatus::STUCK;
    if (s == "failed") return IndexStatus::FAILED;
    throw std::invalid_argument("Unknown IndexStatus value: " + s);
}

inline void from_json(const nlohmann::json& j, IndexStatus& v) {
    v = indexStatusFromString(j.get<std::string>());
}

inline void to_json(nlohmann::json& j, const IndexStatus& v) {
    j = toString(v);
}

enum class DeploymentStatus {
    WAITING,
    PROCESSING,
    BUILDING,
    READY,
    CANCELED,
    FAILED
};

inline std::string toString(DeploymentStatus value) {
    switch (value) {
        case DeploymentStatus :: WAITING: return "waiting";
        case DeploymentStatus :: PROCESSING: return "processing";
        case DeploymentStatus :: BUILDING: return "building";
        case DeploymentStatus :: READY: return "ready";
        case DeploymentStatus :: CANCELED: return "canceled";
        case DeploymentStatus :: FAILED: return "failed";
        default: throw std::invalid_argument("Unknown DeploymentStatus enum value");
    }
}

inline DeploymentStatus deploymentStatusFromString(const std::string& s) {
    if (s == "waiting") return DeploymentStatus::WAITING;
    if (s == "processing") return DeploymentStatus::PROCESSING;
    if (s == "building") return DeploymentStatus::BUILDING;
    if (s == "ready") return DeploymentStatus::READY;
    if (s == "canceled") return DeploymentStatus::CANCELED;
    if (s == "failed") return DeploymentStatus::FAILED;
    throw std::invalid_argument("Unknown DeploymentStatus value: " + s);
}

inline void from_json(const nlohmann::json& j, DeploymentStatus& v) {
    v = deploymentStatusFromString(j.get<std::string>());
}

inline void to_json(nlohmann::json& j, const DeploymentStatus& v) {
    j = toString(v);
}

enum class ExecutionTrigger {
    HTTP,
    SCHEDULE,
    EVENT
};

inline std::string toString(ExecutionTrigger value) {
    switch (value) {
        case ExecutionTrigger :: HTTP: return "http";
        case ExecutionTrigger :: SCHEDULE: return "schedule";
        case ExecutionTrigger :: EVENT: return "event";
        default: throw std::invalid_argument("Unknown ExecutionTrigger enum value");
    }
}

inline ExecutionTrigger executionTriggerFromString(const std::string& s) {
    if (s == "http") return ExecutionTrigger::HTTP;
    if (s == "schedule") return ExecutionTrigger::SCHEDULE;
    if (s == "event") return ExecutionTrigger::EVENT;
    throw std::invalid_argument("Unknown ExecutionTrigger value: " + s);
}

inline void from_json(const nlohmann::json& j, ExecutionTrigger& v) {
    v = executionTriggerFromString(j.get<std::string>());
}

inline void to_json(nlohmann::json& j, const ExecutionTrigger& v) {
    j = toString(v);
}

enum class ExecutionStatus {
    WAITING,
    PROCESSING,
    COMPLETED,
    FAILED,
    SCHEDULED
};

inline std::string toString(ExecutionStatus value) {
    switch (value) {
        case ExecutionStatus :: WAITING: return "waiting";
        case ExecutionStatus :: PROCESSING: return "processing";
        case ExecutionStatus :: COMPLETED: return "completed";
        case ExecutionStatus :: FAILED: return "failed";
        case ExecutionStatus :: SCHEDULED: return "scheduled";
        default: throw std::invalid_argument("Unknown ExecutionStatus enum value");
    }
}

inline ExecutionStatus executionStatusFromString(const std::string& s) {
    if (s == "waiting") return ExecutionStatus::WAITING;
    if (s == "processing") return ExecutionStatus::PROCESSING;
    if (s == "completed") return ExecutionStatus::COMPLETED;
    if (s == "failed") return ExecutionStatus::FAILED;
    if (s == "scheduled") return ExecutionStatus::SCHEDULED;
    throw std::invalid_argument("Unknown ExecutionStatus value: " + s);
}

inline void from_json(const nlohmann::json& j, ExecutionStatus& v) {
    v = executionStatusFromString(j.get<std::string>());
}

inline void to_json(nlohmann::json& j, const ExecutionStatus& v) {
    j = toString(v);
}

} // namespace enums
} // namespace appwrite
