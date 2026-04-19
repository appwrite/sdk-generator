#pragma once

#include <string>
#include <vector>
#include <unordered_map>
#include <span>
#include <format>
#include <nlohmann/json.hpp>
#include "client.hpp"
#include "models.hpp"
#include "enums/enums.hpp"

namespace appwrite {

/**
 * @brief Base class for all Appwrite services.
 */
class Service {
public:
    explicit Service(Client& client) : client_(client) {}
protected:
    Client& client_;
};

namespace services {

/**
 * @brief The Account service allows you to authenticate and manage a user account.
 */
class Account : public Service {
public:
    explicit Account(Client& client) : Service(client) {}

    /**
     * Get the currently logged in user.
     *
     * @return appwrite::Result<appwrite::models::User>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::User> get(    ) {
                std::string path_ = std::format("/account");
        
        nlohmann::json params = nlohmann::json::object();
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::User>("GET", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Get the currently logged in user.
     *
     * @return appwrite::Result<appwrite::models::User>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::User>> getAsync(    ) {
                std::string path_ = std::format("/account");
        
        nlohmann::json params = nlohmann::json::object();
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::User>("GET", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Use this endpoint to allow a new user to register a new account in your
     * project. After the user registration completes successfully, you can use
     * the
     * [/account/verfication](https://appwrite.io/docs/references/cloud/client-web/account#createVerification)
     * route to start verifying the user email address. To allow the new user to
     * login to their new account, you need to create a new [account
     * session](https://appwrite.io/docs/references/cloud/client-web/account#createEmailSession).
     *
     * @param userId User ID. Choose a custom ID or generate a random ID with `ID.unique()`. Valid chars are a-z, A-Z, 0-9, period, hyphen, and underscore. Can't start with a special char. Max length is 36 chars.
     * @param email User email.
     * @param password New user password. Must be between 8 and 256 chars.
     * @param name User name. Max length: 128 chars.
     * @return appwrite::Result<appwrite::models::User>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::User> create(        std::string_view userId,         std::string_view email,         std::string_view password,         std::optional<std::string_view> name = std::nullopt    ) {
                std::string path_ = std::format("/account");
        
        nlohmann::json params = nlohmann::json::object();
        params["userId"] = std::string(userId);
        params["email"] = std::string(email);
        params["password"] = std::string(password);
        if (name.has_value()) {
            params["name"] = std::string(name.value());
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::User>("POST", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Use this endpoint to allow a new user to register a new account in your
     * project. After the user registration completes successfully, you can use
     * the
     * [/account/verfication](https://appwrite.io/docs/references/cloud/client-web/account#createVerification)
     * route to start verifying the user email address. To allow the new user to
     * login to their new account, you need to create a new [account
     * session](https://appwrite.io/docs/references/cloud/client-web/account#createEmailSession).
     *
     * @param userId User ID. Choose a custom ID or generate a random ID with `ID.unique()`. Valid chars are a-z, A-Z, 0-9, period, hyphen, and underscore. Can't start with a special char. Max length is 36 chars.
     * @param email User email.
     * @param password New user password. Must be between 8 and 256 chars.
     * @param name User name. Max length: 128 chars.
     * @return appwrite::Result<appwrite::models::User>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::User>> createAsync(        std::string_view userId,         std::string_view email,         std::string_view password,         std::optional<std::string_view> name = std::nullopt    ) {
                std::string path_ = std::format("/account");
        
        nlohmann::json params = nlohmann::json::object();
        params["userId"] = std::string(userId);
        params["email"] = std::string(email);
        params["password"] = std::string(password);
        if (name.has_value()) {
            params["name"] = std::string(name.value());
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::User>("POST", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Update currently logged in user account email address. After changing user
     * address, the user confirmation status will get reset. A new confirmation
     * email is not sent automatically however you can use the send confirmation
     * email endpoint again to send the confirmation email. For security measures,
     * user password is required to complete this request.
This endpoint can also
     * be used to convert an anonymous account to a normal one, by passing an
     * email address and a new password.
     *
     * @param email User email.
     * @param password User password. Must be at least 8 chars.
     * @return appwrite::Result<appwrite::models::User>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::User> updateEmail(        std::string_view email,         std::string_view password    ) {
                std::string path_ = std::format("/account/email");
        
        nlohmann::json params = nlohmann::json::object();
        params["email"] = std::string(email);
        params["password"] = std::string(password);
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::User>("PATCH", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Update currently logged in user account email address. After changing user
     * address, the user confirmation status will get reset. A new confirmation
     * email is not sent automatically however you can use the send confirmation
     * email endpoint again to send the confirmation email. For security measures,
     * user password is required to complete this request.
This endpoint can also
     * be used to convert an anonymous account to a normal one, by passing an
     * email address and a new password.
     *
     * @param email User email.
     * @param password User password. Must be at least 8 chars.
     * @return appwrite::Result<appwrite::models::User>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::User>> updateEmailAsync(        std::string_view email,         std::string_view password    ) {
                std::string path_ = std::format("/account/email");
        
        nlohmann::json params = nlohmann::json::object();
        params["email"] = std::string(email);
        params["password"] = std::string(password);
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::User>("PATCH", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Get the list of identities for the currently logged in user.
     *
     * @param queries Array of query strings generated using the Query class provided by the SDK. [Learn more about queries](https://appwrite.io/docs/queries). Maximum of 100 queries are allowed, each 4096 characters long. You may filter on the following attributes: userId, provider, providerUid, providerEmail, providerAccessTokenExpiry
     * @param total When set to false, the total count returned will be 0 and will not be calculated.
     * @return appwrite::Result<appwrite::models::IdentityList>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::IdentityList> listIdentities(        std::optional<std::vector<std::string>> queries = std::vector<std::string>{},         std::optional<bool> total = true    ) {
                std::string path_ = std::format("/account/identities");
        
        nlohmann::json params = nlohmann::json::object();
        if (queries.has_value()) {
            params["queries"] = *queries;
        }
        if (total.has_value()) {
            params["total"] = *total;
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::IdentityList>("GET", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Get the list of identities for the currently logged in user.
     *
     * @param queries Array of query strings generated using the Query class provided by the SDK. [Learn more about queries](https://appwrite.io/docs/queries). Maximum of 100 queries are allowed, each 4096 characters long. You may filter on the following attributes: userId, provider, providerUid, providerEmail, providerAccessTokenExpiry
     * @param total When set to false, the total count returned will be 0 and will not be calculated.
     * @return appwrite::Result<appwrite::models::IdentityList>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::IdentityList>> listIdentitiesAsync(        std::optional<std::vector<std::string>> queries = std::vector<std::string>{},         std::optional<bool> total = true    ) {
                std::string path_ = std::format("/account/identities");
        
        nlohmann::json params = nlohmann::json::object();
        if (queries.has_value()) {
            params["queries"] = *queries;
        }
        if (total.has_value()) {
            params["total"] = *total;
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::IdentityList>("GET", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Delete an identity by its unique ID.
     *
     * @param identityId Identity ID.
     * @return appwrite::Result<void>
     */
    [[nodiscard]] appwrite::Result<void> deleteIdentity(        std::string_view identityId    ) {
                std::string path_ = std::format("/account/identities/{}", identityId);
        
        nlohmann::json params = nlohmann::json::object();
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callVoid("DELETE", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Delete an identity by its unique ID.
     *
     * @param identityId Identity ID.
     * @return appwrite::Result<void>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<void>> deleteIdentityAsync(        std::string_view identityId    ) {
                std::string path_ = std::format("/account/identities/{}", identityId);
        
        nlohmann::json params = nlohmann::json::object();
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callVoidAsync("DELETE", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Use this endpoint to create a JSON Web Token. You can use the resulting JWT
     * to authenticate on behalf of the current user when working with the
     * Appwrite server-side API and SDKs. The JWT secret is valid for 15 minutes
     * from its creation and will be invalid if the user will logout in that time
     * frame.
     *
     * @param duration Time in seconds before JWT expires. Default duration is 900 seconds, and maximum is 3600 seconds.
     * @return appwrite::Result<appwrite::models::Jwt>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::Jwt> createJWT(        std::optional<int64_t> duration = 900    ) {
                std::string path_ = std::format("/account/jwts");
        
        nlohmann::json params = nlohmann::json::object();
        if (duration.has_value()) {
            params["duration"] = *duration;
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::Jwt>("POST", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Use this endpoint to create a JSON Web Token. You can use the resulting JWT
     * to authenticate on behalf of the current user when working with the
     * Appwrite server-side API and SDKs. The JWT secret is valid for 15 minutes
     * from its creation and will be invalid if the user will logout in that time
     * frame.
     *
     * @param duration Time in seconds before JWT expires. Default duration is 900 seconds, and maximum is 3600 seconds.
     * @return appwrite::Result<appwrite::models::Jwt>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::Jwt>> createJWTAsync(        std::optional<int64_t> duration = 900    ) {
                std::string path_ = std::format("/account/jwts");
        
        nlohmann::json params = nlohmann::json::object();
        if (duration.has_value()) {
            params["duration"] = *duration;
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::Jwt>("POST", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Get the list of latest security activity logs for the currently logged in
     * user. Each log returns user IP address, location and date and time of log.
     *
     * @param queries Array of query strings generated using the Query class provided by the SDK. [Learn more about queries](https://appwrite.io/docs/queries). Only supported methods are limit and offset
     * @param total When set to false, the total count returned will be 0 and will not be calculated.
     * @return appwrite::Result<appwrite::models::LogList>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::LogList> listLogs(        std::optional<std::vector<std::string>> queries = std::vector<std::string>{},         std::optional<bool> total = true    ) {
                std::string path_ = std::format("/account/logs");
        
        nlohmann::json params = nlohmann::json::object();
        if (queries.has_value()) {
            params["queries"] = *queries;
        }
        if (total.has_value()) {
            params["total"] = *total;
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::LogList>("GET", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Get the list of latest security activity logs for the currently logged in
     * user. Each log returns user IP address, location and date and time of log.
     *
     * @param queries Array of query strings generated using the Query class provided by the SDK. [Learn more about queries](https://appwrite.io/docs/queries). Only supported methods are limit and offset
     * @param total When set to false, the total count returned will be 0 and will not be calculated.
     * @return appwrite::Result<appwrite::models::LogList>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::LogList>> listLogsAsync(        std::optional<std::vector<std::string>> queries = std::vector<std::string>{},         std::optional<bool> total = true    ) {
                std::string path_ = std::format("/account/logs");
        
        nlohmann::json params = nlohmann::json::object();
        if (queries.has_value()) {
            params["queries"] = *queries;
        }
        if (total.has_value()) {
            params["total"] = *total;
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::LogList>("GET", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Enable or disable MFA on an account.
     *
     * @param mfa Enable or disable MFA.
     * @return appwrite::Result<appwrite::models::User>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::User> updateMFA(        bool mfa    ) {
                std::string path_ = std::format("/account/mfa");
        
        nlohmann::json params = nlohmann::json::object();
        params["mfa"] = mfa;
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::User>("PATCH", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Enable or disable MFA on an account.
     *
     * @param mfa Enable or disable MFA.
     * @return appwrite::Result<appwrite::models::User>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::User>> updateMFAAsync(        bool mfa    ) {
                std::string path_ = std::format("/account/mfa");
        
        nlohmann::json params = nlohmann::json::object();
        params["mfa"] = mfa;
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::User>("PATCH", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Add an authenticator app to be used as an MFA factor. Verify the
     * authenticator using the [verify
     * authenticator](/docs/references/cloud/client-web/account#updateMfaAuthenticator)
     * method.
     *
     * @param type Type of authenticator. Must be `totp`
     * @return appwrite::Result<appwrite::models::MfaType>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::MfaType> createMfaAuthenticator(        appwrite::enums::AuthenticatorType type    ) {
                std::string path_ = std::format("/account/mfa/authenticators/{}", appwrite::enums::toString(type));
        
        nlohmann::json params = nlohmann::json::object();
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::MfaType>("POST", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Add an authenticator app to be used as an MFA factor. Verify the
     * authenticator using the [verify
     * authenticator](/docs/references/cloud/client-web/account#updateMfaAuthenticator)
     * method.
     *
     * @param type Type of authenticator. Must be `totp`
     * @return appwrite::Result<appwrite::models::MfaType>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::MfaType>> createMfaAuthenticatorAsync(        appwrite::enums::AuthenticatorType type    ) {
                std::string path_ = std::format("/account/mfa/authenticators/{}", appwrite::enums::toString(type));
        
        nlohmann::json params = nlohmann::json::object();
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::MfaType>("POST", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Add an authenticator app to be used as an MFA factor. Verify the
     * authenticator using the [verify
     * authenticator](/docs/references/cloud/client-web/account#updateMfaAuthenticator)
     * method.
     *
     * @param type Type of authenticator. Must be `totp`
     * @return appwrite::Result<appwrite::models::MfaType>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::MfaType> createMFAAuthenticator(        appwrite::enums::AuthenticatorType type    ) {
                std::string path_ = std::format("/account/mfa/authenticators/{}", appwrite::enums::toString(type));
        
        nlohmann::json params = nlohmann::json::object();
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::MfaType>("POST", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Add an authenticator app to be used as an MFA factor. Verify the
     * authenticator using the [verify
     * authenticator](/docs/references/cloud/client-web/account#updateMfaAuthenticator)
     * method.
     *
     * @param type Type of authenticator. Must be `totp`
     * @return appwrite::Result<appwrite::models::MfaType>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::MfaType>> createMFAAuthenticatorAsync(        appwrite::enums::AuthenticatorType type    ) {
                std::string path_ = std::format("/account/mfa/authenticators/{}", appwrite::enums::toString(type));
        
        nlohmann::json params = nlohmann::json::object();
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::MfaType>("POST", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Verify an authenticator app after adding it using the [add
     * authenticator](/docs/references/cloud/client-web/account#createMfaAuthenticator)
     * method.
     *
     * @param type Type of authenticator.
     * @param otp Valid verification token.
     * @return appwrite::Result<appwrite::models::User>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::User> updateMfaAuthenticator(        appwrite::enums::AuthenticatorType type,         std::string_view otp    ) {
                std::string path_ = std::format("/account/mfa/authenticators/{}", appwrite::enums::toString(type));
        
        nlohmann::json params = nlohmann::json::object();
        params["otp"] = std::string(otp);
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::User>("PUT", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Verify an authenticator app after adding it using the [add
     * authenticator](/docs/references/cloud/client-web/account#createMfaAuthenticator)
     * method.
     *
     * @param type Type of authenticator.
     * @param otp Valid verification token.
     * @return appwrite::Result<appwrite::models::User>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::User>> updateMfaAuthenticatorAsync(        appwrite::enums::AuthenticatorType type,         std::string_view otp    ) {
                std::string path_ = std::format("/account/mfa/authenticators/{}", appwrite::enums::toString(type));
        
        nlohmann::json params = nlohmann::json::object();
        params["otp"] = std::string(otp);
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::User>("PUT", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Verify an authenticator app after adding it using the [add
     * authenticator](/docs/references/cloud/client-web/account#createMfaAuthenticator)
     * method.
     *
     * @param type Type of authenticator.
     * @param otp Valid verification token.
     * @return appwrite::Result<appwrite::models::User>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::User> updateMFAAuthenticator(        appwrite::enums::AuthenticatorType type,         std::string_view otp    ) {
                std::string path_ = std::format("/account/mfa/authenticators/{}", appwrite::enums::toString(type));
        
        nlohmann::json params = nlohmann::json::object();
        params["otp"] = std::string(otp);
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::User>("PUT", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Verify an authenticator app after adding it using the [add
     * authenticator](/docs/references/cloud/client-web/account#createMfaAuthenticator)
     * method.
     *
     * @param type Type of authenticator.
     * @param otp Valid verification token.
     * @return appwrite::Result<appwrite::models::User>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::User>> updateMFAAuthenticatorAsync(        appwrite::enums::AuthenticatorType type,         std::string_view otp    ) {
                std::string path_ = std::format("/account/mfa/authenticators/{}", appwrite::enums::toString(type));
        
        nlohmann::json params = nlohmann::json::object();
        params["otp"] = std::string(otp);
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::User>("PUT", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Delete an authenticator for a user by ID.
     *
     * @param type Type of authenticator.
     * @return appwrite::Result<void>
     */
    [[nodiscard]] appwrite::Result<void> deleteMfaAuthenticator(        appwrite::enums::AuthenticatorType type    ) {
                std::string path_ = std::format("/account/mfa/authenticators/{}", appwrite::enums::toString(type));
        
        nlohmann::json params = nlohmann::json::object();
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callVoid("DELETE", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Delete an authenticator for a user by ID.
     *
     * @param type Type of authenticator.
     * @return appwrite::Result<void>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<void>> deleteMfaAuthenticatorAsync(        appwrite::enums::AuthenticatorType type    ) {
                std::string path_ = std::format("/account/mfa/authenticators/{}", appwrite::enums::toString(type));
        
        nlohmann::json params = nlohmann::json::object();
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callVoidAsync("DELETE", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Delete an authenticator for a user by ID.
     *
     * @param type Type of authenticator.
     * @return appwrite::Result<void>
     */
    [[nodiscard]] appwrite::Result<void> deleteMFAAuthenticator(        appwrite::enums::AuthenticatorType type    ) {
                std::string path_ = std::format("/account/mfa/authenticators/{}", appwrite::enums::toString(type));
        
        nlohmann::json params = nlohmann::json::object();
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callVoid("DELETE", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Delete an authenticator for a user by ID.
     *
     * @param type Type of authenticator.
     * @return appwrite::Result<void>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<void>> deleteMFAAuthenticatorAsync(        appwrite::enums::AuthenticatorType type    ) {
                std::string path_ = std::format("/account/mfa/authenticators/{}", appwrite::enums::toString(type));
        
        nlohmann::json params = nlohmann::json::object();
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callVoidAsync("DELETE", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Begin the process of MFA verification after sign-in. Finish the flow with
     * [updateMfaChallenge](/docs/references/cloud/client-web/account#updateMfaChallenge)
     * method.
     *
     * @param factor Factor used for verification. Must be one of following: `email`, `phone`, `totp`, `recoveryCode`.
     * @return appwrite::Result<appwrite::models::MfaChallenge>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::MfaChallenge> createMfaChallenge(        appwrite::enums::AuthenticationFactor factor    ) {
                std::string path_ = std::format("/account/mfa/challenges");
        
        nlohmann::json params = nlohmann::json::object();
        params["factor"] = factor;
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::MfaChallenge>("POST", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Begin the process of MFA verification after sign-in. Finish the flow with
     * [updateMfaChallenge](/docs/references/cloud/client-web/account#updateMfaChallenge)
     * method.
     *
     * @param factor Factor used for verification. Must be one of following: `email`, `phone`, `totp`, `recoveryCode`.
     * @return appwrite::Result<appwrite::models::MfaChallenge>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::MfaChallenge>> createMfaChallengeAsync(        appwrite::enums::AuthenticationFactor factor    ) {
                std::string path_ = std::format("/account/mfa/challenges");
        
        nlohmann::json params = nlohmann::json::object();
        params["factor"] = factor;
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::MfaChallenge>("POST", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Begin the process of MFA verification after sign-in. Finish the flow with
     * [updateMfaChallenge](/docs/references/cloud/client-web/account#updateMfaChallenge)
     * method.
     *
     * @param factor Factor used for verification. Must be one of following: `email`, `phone`, `totp`, `recoveryCode`.
     * @return appwrite::Result<appwrite::models::MfaChallenge>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::MfaChallenge> createMFAChallenge(        appwrite::enums::AuthenticationFactor factor    ) {
                std::string path_ = std::format("/account/mfa/challenges");
        
        nlohmann::json params = nlohmann::json::object();
        params["factor"] = factor;
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::MfaChallenge>("POST", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Begin the process of MFA verification after sign-in. Finish the flow with
     * [updateMfaChallenge](/docs/references/cloud/client-web/account#updateMfaChallenge)
     * method.
     *
     * @param factor Factor used for verification. Must be one of following: `email`, `phone`, `totp`, `recoveryCode`.
     * @return appwrite::Result<appwrite::models::MfaChallenge>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::MfaChallenge>> createMFAChallengeAsync(        appwrite::enums::AuthenticationFactor factor    ) {
                std::string path_ = std::format("/account/mfa/challenges");
        
        nlohmann::json params = nlohmann::json::object();
        params["factor"] = factor;
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::MfaChallenge>("POST", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Complete the MFA challenge by providing the one-time password. Finish the
     * process of MFA verification by providing the one-time password. To begin
     * the flow, use
     * [createMfaChallenge](/docs/references/cloud/client-web/account#createMfaChallenge)
     * method.
     *
     * @param challengeId ID of the challenge.
     * @param otp Valid verification token.
     * @return appwrite::Result<appwrite::models::Session>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::Session> updateMfaChallenge(        std::string_view challengeId,         std::string_view otp    ) {
                std::string path_ = std::format("/account/mfa/challenges");
        
        nlohmann::json params = nlohmann::json::object();
        params["challengeId"] = std::string(challengeId);
        params["otp"] = std::string(otp);
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::Session>("PUT", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Complete the MFA challenge by providing the one-time password. Finish the
     * process of MFA verification by providing the one-time password. To begin
     * the flow, use
     * [createMfaChallenge](/docs/references/cloud/client-web/account#createMfaChallenge)
     * method.
     *
     * @param challengeId ID of the challenge.
     * @param otp Valid verification token.
     * @return appwrite::Result<appwrite::models::Session>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::Session>> updateMfaChallengeAsync(        std::string_view challengeId,         std::string_view otp    ) {
                std::string path_ = std::format("/account/mfa/challenges");
        
        nlohmann::json params = nlohmann::json::object();
        params["challengeId"] = std::string(challengeId);
        params["otp"] = std::string(otp);
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::Session>("PUT", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Complete the MFA challenge by providing the one-time password. Finish the
     * process of MFA verification by providing the one-time password. To begin
     * the flow, use
     * [createMfaChallenge](/docs/references/cloud/client-web/account#createMfaChallenge)
     * method.
     *
     * @param challengeId ID of the challenge.
     * @param otp Valid verification token.
     * @return appwrite::Result<appwrite::models::Session>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::Session> updateMFAChallenge(        std::string_view challengeId,         std::string_view otp    ) {
                std::string path_ = std::format("/account/mfa/challenges");
        
        nlohmann::json params = nlohmann::json::object();
        params["challengeId"] = std::string(challengeId);
        params["otp"] = std::string(otp);
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::Session>("PUT", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Complete the MFA challenge by providing the one-time password. Finish the
     * process of MFA verification by providing the one-time password. To begin
     * the flow, use
     * [createMfaChallenge](/docs/references/cloud/client-web/account#createMfaChallenge)
     * method.
     *
     * @param challengeId ID of the challenge.
     * @param otp Valid verification token.
     * @return appwrite::Result<appwrite::models::Session>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::Session>> updateMFAChallengeAsync(        std::string_view challengeId,         std::string_view otp    ) {
                std::string path_ = std::format("/account/mfa/challenges");
        
        nlohmann::json params = nlohmann::json::object();
        params["challengeId"] = std::string(challengeId);
        params["otp"] = std::string(otp);
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::Session>("PUT", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * List the factors available on the account to be used as a MFA challange.
     *
     * @return appwrite::Result<appwrite::models::MfaFactors>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::MfaFactors> listMfaFactors(    ) {
                std::string path_ = std::format("/account/mfa/factors");
        
        nlohmann::json params = nlohmann::json::object();
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::MfaFactors>("GET", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * List the factors available on the account to be used as a MFA challange.
     *
     * @return appwrite::Result<appwrite::models::MfaFactors>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::MfaFactors>> listMfaFactorsAsync(    ) {
                std::string path_ = std::format("/account/mfa/factors");
        
        nlohmann::json params = nlohmann::json::object();
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::MfaFactors>("GET", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * List the factors available on the account to be used as a MFA challange.
     *
     * @return appwrite::Result<appwrite::models::MfaFactors>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::MfaFactors> listMFAFactors(    ) {
                std::string path_ = std::format("/account/mfa/factors");
        
        nlohmann::json params = nlohmann::json::object();
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::MfaFactors>("GET", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * List the factors available on the account to be used as a MFA challange.
     *
     * @return appwrite::Result<appwrite::models::MfaFactors>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::MfaFactors>> listMFAFactorsAsync(    ) {
                std::string path_ = std::format("/account/mfa/factors");
        
        nlohmann::json params = nlohmann::json::object();
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::MfaFactors>("GET", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Get recovery codes that can be used as backup for MFA flow. Before getting
     * codes, they must be generated using
     * [createMfaRecoveryCodes](/docs/references/cloud/client-web/account#createMfaRecoveryCodes)
     * method. An OTP challenge is required to read recovery codes.
     *
     * @return appwrite::Result<appwrite::models::MfaRecoveryCodes>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::MfaRecoveryCodes> getMfaRecoveryCodes(    ) {
                std::string path_ = std::format("/account/mfa/recovery-codes");
        
        nlohmann::json params = nlohmann::json::object();
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::MfaRecoveryCodes>("GET", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Get recovery codes that can be used as backup for MFA flow. Before getting
     * codes, they must be generated using
     * [createMfaRecoveryCodes](/docs/references/cloud/client-web/account#createMfaRecoveryCodes)
     * method. An OTP challenge is required to read recovery codes.
     *
     * @return appwrite::Result<appwrite::models::MfaRecoveryCodes>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::MfaRecoveryCodes>> getMfaRecoveryCodesAsync(    ) {
                std::string path_ = std::format("/account/mfa/recovery-codes");
        
        nlohmann::json params = nlohmann::json::object();
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::MfaRecoveryCodes>("GET", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Get recovery codes that can be used as backup for MFA flow. Before getting
     * codes, they must be generated using
     * [createMfaRecoveryCodes](/docs/references/cloud/client-web/account#createMfaRecoveryCodes)
     * method. An OTP challenge is required to read recovery codes.
     *
     * @return appwrite::Result<appwrite::models::MfaRecoveryCodes>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::MfaRecoveryCodes> getMFARecoveryCodes(    ) {
                std::string path_ = std::format("/account/mfa/recovery-codes");
        
        nlohmann::json params = nlohmann::json::object();
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::MfaRecoveryCodes>("GET", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Get recovery codes that can be used as backup for MFA flow. Before getting
     * codes, they must be generated using
     * [createMfaRecoveryCodes](/docs/references/cloud/client-web/account#createMfaRecoveryCodes)
     * method. An OTP challenge is required to read recovery codes.
     *
     * @return appwrite::Result<appwrite::models::MfaRecoveryCodes>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::MfaRecoveryCodes>> getMFARecoveryCodesAsync(    ) {
                std::string path_ = std::format("/account/mfa/recovery-codes");
        
        nlohmann::json params = nlohmann::json::object();
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::MfaRecoveryCodes>("GET", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Generate recovery codes as backup for MFA flow. It's recommended to
     * generate and show then immediately after user successfully adds their
     * authehticator. Recovery codes can be used as a MFA verification type in
     * [createMfaChallenge](/docs/references/cloud/client-web/account#createMfaChallenge)
     * method.
     *
     * @return appwrite::Result<appwrite::models::MfaRecoveryCodes>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::MfaRecoveryCodes> createMfaRecoveryCodes(    ) {
                std::string path_ = std::format("/account/mfa/recovery-codes");
        
        nlohmann::json params = nlohmann::json::object();
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::MfaRecoveryCodes>("POST", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Generate recovery codes as backup for MFA flow. It's recommended to
     * generate and show then immediately after user successfully adds their
     * authehticator. Recovery codes can be used as a MFA verification type in
     * [createMfaChallenge](/docs/references/cloud/client-web/account#createMfaChallenge)
     * method.
     *
     * @return appwrite::Result<appwrite::models::MfaRecoveryCodes>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::MfaRecoveryCodes>> createMfaRecoveryCodesAsync(    ) {
                std::string path_ = std::format("/account/mfa/recovery-codes");
        
        nlohmann::json params = nlohmann::json::object();
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::MfaRecoveryCodes>("POST", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Generate recovery codes as backup for MFA flow. It's recommended to
     * generate and show then immediately after user successfully adds their
     * authehticator. Recovery codes can be used as a MFA verification type in
     * [createMfaChallenge](/docs/references/cloud/client-web/account#createMfaChallenge)
     * method.
     *
     * @return appwrite::Result<appwrite::models::MfaRecoveryCodes>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::MfaRecoveryCodes> createMFARecoveryCodes(    ) {
                std::string path_ = std::format("/account/mfa/recovery-codes");
        
        nlohmann::json params = nlohmann::json::object();
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::MfaRecoveryCodes>("POST", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Generate recovery codes as backup for MFA flow. It's recommended to
     * generate and show then immediately after user successfully adds their
     * authehticator. Recovery codes can be used as a MFA verification type in
     * [createMfaChallenge](/docs/references/cloud/client-web/account#createMfaChallenge)
     * method.
     *
     * @return appwrite::Result<appwrite::models::MfaRecoveryCodes>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::MfaRecoveryCodes>> createMFARecoveryCodesAsync(    ) {
                std::string path_ = std::format("/account/mfa/recovery-codes");
        
        nlohmann::json params = nlohmann::json::object();
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::MfaRecoveryCodes>("POST", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Regenerate recovery codes that can be used as backup for MFA flow. Before
     * regenerating codes, they must be first generated using
     * [createMfaRecoveryCodes](/docs/references/cloud/client-web/account#createMfaRecoveryCodes)
     * method. An OTP challenge is required to regenreate recovery codes.
     *
     * @return appwrite::Result<appwrite::models::MfaRecoveryCodes>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::MfaRecoveryCodes> updateMfaRecoveryCodes(    ) {
                std::string path_ = std::format("/account/mfa/recovery-codes");
        
        nlohmann::json params = nlohmann::json::object();
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::MfaRecoveryCodes>("PATCH", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Regenerate recovery codes that can be used as backup for MFA flow. Before
     * regenerating codes, they must be first generated using
     * [createMfaRecoveryCodes](/docs/references/cloud/client-web/account#createMfaRecoveryCodes)
     * method. An OTP challenge is required to regenreate recovery codes.
     *
     * @return appwrite::Result<appwrite::models::MfaRecoveryCodes>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::MfaRecoveryCodes>> updateMfaRecoveryCodesAsync(    ) {
                std::string path_ = std::format("/account/mfa/recovery-codes");
        
        nlohmann::json params = nlohmann::json::object();
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::MfaRecoveryCodes>("PATCH", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Regenerate recovery codes that can be used as backup for MFA flow. Before
     * regenerating codes, they must be first generated using
     * [createMfaRecoveryCodes](/docs/references/cloud/client-web/account#createMfaRecoveryCodes)
     * method. An OTP challenge is required to regenreate recovery codes.
     *
     * @return appwrite::Result<appwrite::models::MfaRecoveryCodes>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::MfaRecoveryCodes> updateMFARecoveryCodes(    ) {
                std::string path_ = std::format("/account/mfa/recovery-codes");
        
        nlohmann::json params = nlohmann::json::object();
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::MfaRecoveryCodes>("PATCH", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Regenerate recovery codes that can be used as backup for MFA flow. Before
     * regenerating codes, they must be first generated using
     * [createMfaRecoveryCodes](/docs/references/cloud/client-web/account#createMfaRecoveryCodes)
     * method. An OTP challenge is required to regenreate recovery codes.
     *
     * @return appwrite::Result<appwrite::models::MfaRecoveryCodes>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::MfaRecoveryCodes>> updateMFARecoveryCodesAsync(    ) {
                std::string path_ = std::format("/account/mfa/recovery-codes");
        
        nlohmann::json params = nlohmann::json::object();
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::MfaRecoveryCodes>("PATCH", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Update currently logged in user account name.
     *
     * @param name User name. Max length: 128 chars.
     * @return appwrite::Result<appwrite::models::User>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::User> updateName(        std::string_view name    ) {
                std::string path_ = std::format("/account/name");
        
        nlohmann::json params = nlohmann::json::object();
        params["name"] = std::string(name);
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::User>("PATCH", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Update currently logged in user account name.
     *
     * @param name User name. Max length: 128 chars.
     * @return appwrite::Result<appwrite::models::User>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::User>> updateNameAsync(        std::string_view name    ) {
                std::string path_ = std::format("/account/name");
        
        nlohmann::json params = nlohmann::json::object();
        params["name"] = std::string(name);
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::User>("PATCH", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Update currently logged in user password. For validation, user is required
     * to pass in the new password, and the old password. For users created with
     * OAuth, Team Invites and Magic URL, oldPassword is optional.
     *
     * @param password New user password. Must be at least 8 chars.
     * @param oldPassword Current user password. Must be at least 8 chars.
     * @return appwrite::Result<appwrite::models::User>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::User> updatePassword(        std::string_view password,         std::optional<std::string_view> oldPassword = std::nullopt    ) {
                std::string path_ = std::format("/account/password");
        
        nlohmann::json params = nlohmann::json::object();
        params["password"] = std::string(password);
        if (oldPassword.has_value()) {
            params["oldPassword"] = std::string(oldPassword.value());
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::User>("PATCH", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Update currently logged in user password. For validation, user is required
     * to pass in the new password, and the old password. For users created with
     * OAuth, Team Invites and Magic URL, oldPassword is optional.
     *
     * @param password New user password. Must be at least 8 chars.
     * @param oldPassword Current user password. Must be at least 8 chars.
     * @return appwrite::Result<appwrite::models::User>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::User>> updatePasswordAsync(        std::string_view password,         std::optional<std::string_view> oldPassword = std::nullopt    ) {
                std::string path_ = std::format("/account/password");
        
        nlohmann::json params = nlohmann::json::object();
        params["password"] = std::string(password);
        if (oldPassword.has_value()) {
            params["oldPassword"] = std::string(oldPassword.value());
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::User>("PATCH", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Update the currently logged in user's phone number. After updating the
     * phone number, the phone verification status will be reset. A confirmation
     * SMS is not sent automatically, however you can use the [POST
     * /account/verification/phone](https://appwrite.io/docs/references/cloud/client-web/account#createPhoneVerification)
     * endpoint to send a confirmation SMS.
     *
     * @param phone Phone number. Format this number with a leading '+' and a country code, e.g., +16175551212.
     * @param password User password. Must be at least 8 chars.
     * @return appwrite::Result<appwrite::models::User>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::User> updatePhone(        std::string_view phone,         std::string_view password    ) {
                std::string path_ = std::format("/account/phone");
        
        nlohmann::json params = nlohmann::json::object();
        params["phone"] = std::string(phone);
        params["password"] = std::string(password);
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::User>("PATCH", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Update the currently logged in user's phone number. After updating the
     * phone number, the phone verification status will be reset. A confirmation
     * SMS is not sent automatically, however you can use the [POST
     * /account/verification/phone](https://appwrite.io/docs/references/cloud/client-web/account#createPhoneVerification)
     * endpoint to send a confirmation SMS.
     *
     * @param phone Phone number. Format this number with a leading '+' and a country code, e.g., +16175551212.
     * @param password User password. Must be at least 8 chars.
     * @return appwrite::Result<appwrite::models::User>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::User>> updatePhoneAsync(        std::string_view phone,         std::string_view password    ) {
                std::string path_ = std::format("/account/phone");
        
        nlohmann::json params = nlohmann::json::object();
        params["phone"] = std::string(phone);
        params["password"] = std::string(password);
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::User>("PATCH", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Get the preferences as a key-value object for the currently logged in user.
     *
     * @return appwrite::Result<appwrite::models::Preferences>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::Preferences> getPrefs(    ) {
                std::string path_ = std::format("/account/prefs");
        
        nlohmann::json params = nlohmann::json::object();
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::Preferences>("GET", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Get the preferences as a key-value object for the currently logged in user.
     *
     * @return appwrite::Result<appwrite::models::Preferences>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::Preferences>> getPrefsAsync(    ) {
                std::string path_ = std::format("/account/prefs");
        
        nlohmann::json params = nlohmann::json::object();
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::Preferences>("GET", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Update currently logged in user account preferences. The object you pass is
     * stored as is, and replaces any previous value. The maximum allowed prefs
     * size is 64kB and throws error if exceeded.
     *
     * @param prefs Prefs key-value JSON object.
     * @return appwrite::Result<appwrite::models::User>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::User> updatePrefs(        const nlohmann::json& prefs = nlohmann::json::object()    ) {
                std::string path_ = std::format("/account/prefs");
        
        nlohmann::json params = nlohmann::json::object();
        params["prefs"] = prefs;
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::User>("PATCH", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Update currently logged in user account preferences. The object you pass is
     * stored as is, and replaces any previous value. The maximum allowed prefs
     * size is 64kB and throws error if exceeded.
     *
     * @param prefs Prefs key-value JSON object.
     * @return appwrite::Result<appwrite::models::User>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::User>> updatePrefsAsync(        const nlohmann::json& prefs = nlohmann::json::object()    ) {
                std::string path_ = std::format("/account/prefs");
        
        nlohmann::json params = nlohmann::json::object();
        params["prefs"] = prefs;
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::User>("PATCH", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Sends the user an email with a temporary secret key for password reset.
     * When the user clicks the confirmation link he is redirected back to your
     * app password reset URL with the secret key and email address values
     * attached to the URL query string. Use the query string params to submit a
     * request to the [PUT
     * /account/recovery](https://appwrite.io/docs/references/cloud/client-web/account#updateRecovery)
     * endpoint to complete the process. The verification link sent to the user's
     * email address is valid for 1 hour.
     *
     * @param email User email.
     * @param url URL to redirect the user back to your app from the recovery email. Only URLs from hostnames in your project platform list are allowed. This requirement helps to prevent an [open redirect](https://cheatsheetseries.owasp.org/cheatsheets/Unvalidated_Redirects_and_Forwards_Cheat_Sheet.html) attack against your project API.
     * @return appwrite::Result<appwrite::models::Token>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::Token> createRecovery(        std::string_view email,         std::string_view url    ) {
                std::string path_ = std::format("/account/recovery");
        
        nlohmann::json params = nlohmann::json::object();
        params["email"] = std::string(email);
        params["url"] = std::string(url);
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::Token>("POST", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Sends the user an email with a temporary secret key for password reset.
     * When the user clicks the confirmation link he is redirected back to your
     * app password reset URL with the secret key and email address values
     * attached to the URL query string. Use the query string params to submit a
     * request to the [PUT
     * /account/recovery](https://appwrite.io/docs/references/cloud/client-web/account#updateRecovery)
     * endpoint to complete the process. The verification link sent to the user's
     * email address is valid for 1 hour.
     *
     * @param email User email.
     * @param url URL to redirect the user back to your app from the recovery email. Only URLs from hostnames in your project platform list are allowed. This requirement helps to prevent an [open redirect](https://cheatsheetseries.owasp.org/cheatsheets/Unvalidated_Redirects_and_Forwards_Cheat_Sheet.html) attack against your project API.
     * @return appwrite::Result<appwrite::models::Token>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::Token>> createRecoveryAsync(        std::string_view email,         std::string_view url    ) {
                std::string path_ = std::format("/account/recovery");
        
        nlohmann::json params = nlohmann::json::object();
        params["email"] = std::string(email);
        params["url"] = std::string(url);
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::Token>("POST", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Use this endpoint to complete the user account password reset. Both the
     * **userId** and **secret** arguments will be passed as query parameters to
     * the redirect URL you have provided when sending your request to the [POST
     * /account/recovery](https://appwrite.io/docs/references/cloud/client-web/account#createRecovery)
     * endpoint.

Please note that in order to avoid a [Redirect
     * Attack](https://github.com/OWASP/CheatSheetSeries/blob/master/cheatsheets/Unvalidated_Redirects_and_Forwards_Cheat_Sheet.md)
     * the only valid redirect URLs are the ones from domains you have set when
     * adding your platforms in the console interface.
     *
     * @param userId User ID.
     * @param secret Valid reset token.
     * @param password New user password. Must be between 8 and 256 chars.
     * @return appwrite::Result<appwrite::models::Token>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::Token> updateRecovery(        std::string_view userId,         std::string_view secret,         std::string_view password    ) {
                std::string path_ = std::format("/account/recovery");
        
        nlohmann::json params = nlohmann::json::object();
        params["userId"] = std::string(userId);
        params["secret"] = std::string(secret);
        params["password"] = std::string(password);
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::Token>("PUT", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Use this endpoint to complete the user account password reset. Both the
     * **userId** and **secret** arguments will be passed as query parameters to
     * the redirect URL you have provided when sending your request to the [POST
     * /account/recovery](https://appwrite.io/docs/references/cloud/client-web/account#createRecovery)
     * endpoint.

Please note that in order to avoid a [Redirect
     * Attack](https://github.com/OWASP/CheatSheetSeries/blob/master/cheatsheets/Unvalidated_Redirects_and_Forwards_Cheat_Sheet.md)
     * the only valid redirect URLs are the ones from domains you have set when
     * adding your platforms in the console interface.
     *
     * @param userId User ID.
     * @param secret Valid reset token.
     * @param password New user password. Must be between 8 and 256 chars.
     * @return appwrite::Result<appwrite::models::Token>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::Token>> updateRecoveryAsync(        std::string_view userId,         std::string_view secret,         std::string_view password    ) {
                std::string path_ = std::format("/account/recovery");
        
        nlohmann::json params = nlohmann::json::object();
        params["userId"] = std::string(userId);
        params["secret"] = std::string(secret);
        params["password"] = std::string(password);
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::Token>("PUT", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Get the list of active sessions across different devices for the currently
     * logged in user.
     *
     * @return appwrite::Result<appwrite::models::SessionList>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::SessionList> listSessions(    ) {
                std::string path_ = std::format("/account/sessions");
        
        nlohmann::json params = nlohmann::json::object();
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::SessionList>("GET", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Get the list of active sessions across different devices for the currently
     * logged in user.
     *
     * @return appwrite::Result<appwrite::models::SessionList>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::SessionList>> listSessionsAsync(    ) {
                std::string path_ = std::format("/account/sessions");
        
        nlohmann::json params = nlohmann::json::object();
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::SessionList>("GET", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Delete all sessions from the user account and remove any sessions cookies
     * from the end client.
     *
     * @return appwrite::Result<void>
     */
    [[nodiscard]] appwrite::Result<void> deleteSessions(    ) {
                std::string path_ = std::format("/account/sessions");
        
        nlohmann::json params = nlohmann::json::object();
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callVoid("DELETE", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Delete all sessions from the user account and remove any sessions cookies
     * from the end client.
     *
     * @return appwrite::Result<void>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<void>> deleteSessionsAsync(    ) {
                std::string path_ = std::format("/account/sessions");
        
        nlohmann::json params = nlohmann::json::object();
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callVoidAsync("DELETE", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Use this endpoint to allow a new user to register an anonymous account in
     * your project. This route will also create a new session for the user. To
     * allow the new user to convert an anonymous account to a normal account, you
     * need to update its [email and
     * password](https://appwrite.io/docs/references/cloud/client-web/account#updateEmail)
     * or create an [OAuth2
     * session](https://appwrite.io/docs/references/cloud/client-web/account#CreateOAuth2Session).
     *
     * @return appwrite::Result<appwrite::models::Session>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::Session> createAnonymousSession(    ) {
                std::string path_ = std::format("/account/sessions/anonymous");
        
        nlohmann::json params = nlohmann::json::object();
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::Session>("POST", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Use this endpoint to allow a new user to register an anonymous account in
     * your project. This route will also create a new session for the user. To
     * allow the new user to convert an anonymous account to a normal account, you
     * need to update its [email and
     * password](https://appwrite.io/docs/references/cloud/client-web/account#updateEmail)
     * or create an [OAuth2
     * session](https://appwrite.io/docs/references/cloud/client-web/account#CreateOAuth2Session).
     *
     * @return appwrite::Result<appwrite::models::Session>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::Session>> createAnonymousSessionAsync(    ) {
                std::string path_ = std::format("/account/sessions/anonymous");
        
        nlohmann::json params = nlohmann::json::object();
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::Session>("POST", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Allow the user to login into their account by providing a valid email and
     * password combination. This route will create a new session for the user.

A
     * user is limited to 10 active sessions at a time by default. [Learn more
     * about session
     * limits](https://appwrite.io/docs/authentication-security#limits).
     *
     * @param email User email.
     * @param password User password. Must be at least 8 chars.
     * @return appwrite::Result<appwrite::models::Session>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::Session> createEmailPasswordSession(        std::string_view email,         std::string_view password    ) {
                std::string path_ = std::format("/account/sessions/email");
        
        nlohmann::json params = nlohmann::json::object();
        params["email"] = std::string(email);
        params["password"] = std::string(password);
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::Session>("POST", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Allow the user to login into their account by providing a valid email and
     * password combination. This route will create a new session for the user.

A
     * user is limited to 10 active sessions at a time by default. [Learn more
     * about session
     * limits](https://appwrite.io/docs/authentication-security#limits).
     *
     * @param email User email.
     * @param password User password. Must be at least 8 chars.
     * @return appwrite::Result<appwrite::models::Session>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::Session>> createEmailPasswordSessionAsync(        std::string_view email,         std::string_view password    ) {
                std::string path_ = std::format("/account/sessions/email");
        
        nlohmann::json params = nlohmann::json::object();
        params["email"] = std::string(email);
        params["password"] = std::string(password);
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::Session>("POST", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Use this endpoint to create a session from token. Provide the **userId**
     * and **secret** parameters from the successful response of authentication
     * flows initiated by token creation. For example, magic URL and phone login.
     *
     * @param userId User ID. Choose a custom ID or generate a random ID with `ID.unique()`. Valid chars are a-z, A-Z, 0-9, period, hyphen, and underscore. Can't start with a special char. Max length is 36 chars.
     * @param secret Valid verification token.
     * @return appwrite::Result<appwrite::models::Session>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::Session> updateMagicURLSession(        std::string_view userId,         std::string_view secret    ) {
                std::string path_ = std::format("/account/sessions/magic-url");
        
        nlohmann::json params = nlohmann::json::object();
        params["userId"] = std::string(userId);
        params["secret"] = std::string(secret);
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::Session>("PUT", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Use this endpoint to create a session from token. Provide the **userId**
     * and **secret** parameters from the successful response of authentication
     * flows initiated by token creation. For example, magic URL and phone login.
     *
     * @param userId User ID. Choose a custom ID or generate a random ID with `ID.unique()`. Valid chars are a-z, A-Z, 0-9, period, hyphen, and underscore. Can't start with a special char. Max length is 36 chars.
     * @param secret Valid verification token.
     * @return appwrite::Result<appwrite::models::Session>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::Session>> updateMagicURLSessionAsync(        std::string_view userId,         std::string_view secret    ) {
                std::string path_ = std::format("/account/sessions/magic-url");
        
        nlohmann::json params = nlohmann::json::object();
        params["userId"] = std::string(userId);
        params["secret"] = std::string(secret);
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::Session>("PUT", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Use this endpoint to create a session from token. Provide the **userId**
     * and **secret** parameters from the successful response of authentication
     * flows initiated by token creation. For example, magic URL and phone login.
     *
     * @param userId User ID. Choose a custom ID or generate a random ID with `ID.unique()`. Valid chars are a-z, A-Z, 0-9, period, hyphen, and underscore. Can't start with a special char. Max length is 36 chars.
     * @param secret Valid verification token.
     * @return appwrite::Result<appwrite::models::Session>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::Session> updatePhoneSession(        std::string_view userId,         std::string_view secret    ) {
                std::string path_ = std::format("/account/sessions/phone");
        
        nlohmann::json params = nlohmann::json::object();
        params["userId"] = std::string(userId);
        params["secret"] = std::string(secret);
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::Session>("PUT", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Use this endpoint to create a session from token. Provide the **userId**
     * and **secret** parameters from the successful response of authentication
     * flows initiated by token creation. For example, magic URL and phone login.
     *
     * @param userId User ID. Choose a custom ID or generate a random ID with `ID.unique()`. Valid chars are a-z, A-Z, 0-9, period, hyphen, and underscore. Can't start with a special char. Max length is 36 chars.
     * @param secret Valid verification token.
     * @return appwrite::Result<appwrite::models::Session>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::Session>> updatePhoneSessionAsync(        std::string_view userId,         std::string_view secret    ) {
                std::string path_ = std::format("/account/sessions/phone");
        
        nlohmann::json params = nlohmann::json::object();
        params["userId"] = std::string(userId);
        params["secret"] = std::string(secret);
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::Session>("PUT", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Use this endpoint to create a session from token. Provide the **userId**
     * and **secret** parameters from the successful response of authentication
     * flows initiated by token creation. For example, magic URL and phone login.
     *
     * @param userId User ID. Choose a custom ID or generate a random ID with `ID.unique()`. Valid chars are a-z, A-Z, 0-9, period, hyphen, and underscore. Can't start with a special char. Max length is 36 chars.
     * @param secret Secret of a token generated by login methods. For example, the `createMagicURLToken` or `createPhoneToken` methods.
     * @return appwrite::Result<appwrite::models::Session>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::Session> createSession(        std::string_view userId,         std::string_view secret    ) {
                std::string path_ = std::format("/account/sessions/token");
        
        nlohmann::json params = nlohmann::json::object();
        params["userId"] = std::string(userId);
        params["secret"] = std::string(secret);
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::Session>("POST", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Use this endpoint to create a session from token. Provide the **userId**
     * and **secret** parameters from the successful response of authentication
     * flows initiated by token creation. For example, magic URL and phone login.
     *
     * @param userId User ID. Choose a custom ID or generate a random ID with `ID.unique()`. Valid chars are a-z, A-Z, 0-9, period, hyphen, and underscore. Can't start with a special char. Max length is 36 chars.
     * @param secret Secret of a token generated by login methods. For example, the `createMagicURLToken` or `createPhoneToken` methods.
     * @return appwrite::Result<appwrite::models::Session>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::Session>> createSessionAsync(        std::string_view userId,         std::string_view secret    ) {
                std::string path_ = std::format("/account/sessions/token");
        
        nlohmann::json params = nlohmann::json::object();
        params["userId"] = std::string(userId);
        params["secret"] = std::string(secret);
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::Session>("POST", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Use this endpoint to get a logged in user's session using a Session ID.
     * Inputting 'current' will return the current session being used.
     *
     * @param sessionId Session ID. Use the string 'current' to get the current device session.
     * @return appwrite::Result<appwrite::models::Session>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::Session> getSession(        std::string_view sessionId    ) {
                std::string path_ = std::format("/account/sessions/{}", sessionId);
        
        nlohmann::json params = nlohmann::json::object();
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::Session>("GET", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Use this endpoint to get a logged in user's session using a Session ID.
     * Inputting 'current' will return the current session being used.
     *
     * @param sessionId Session ID. Use the string 'current' to get the current device session.
     * @return appwrite::Result<appwrite::models::Session>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::Session>> getSessionAsync(        std::string_view sessionId    ) {
                std::string path_ = std::format("/account/sessions/{}", sessionId);
        
        nlohmann::json params = nlohmann::json::object();
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::Session>("GET", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Use this endpoint to extend a session's length. Extending a session is
     * useful when session expiry is short. If the session was created using an
     * OAuth provider, this endpoint refreshes the access token from the provider.
     *
     * @param sessionId Session ID. Use the string 'current' to update the current device session.
     * @return appwrite::Result<appwrite::models::Session>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::Session> updateSession(        std::string_view sessionId    ) {
                std::string path_ = std::format("/account/sessions/{}", sessionId);
        
        nlohmann::json params = nlohmann::json::object();
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::Session>("PATCH", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Use this endpoint to extend a session's length. Extending a session is
     * useful when session expiry is short. If the session was created using an
     * OAuth provider, this endpoint refreshes the access token from the provider.
     *
     * @param sessionId Session ID. Use the string 'current' to update the current device session.
     * @return appwrite::Result<appwrite::models::Session>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::Session>> updateSessionAsync(        std::string_view sessionId    ) {
                std::string path_ = std::format("/account/sessions/{}", sessionId);
        
        nlohmann::json params = nlohmann::json::object();
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::Session>("PATCH", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Logout the user. Use 'current' as the session ID to logout on this device,
     * use a session ID to logout on another device. If you're looking to logout
     * the user on all devices, use [Delete
     * Sessions](https://appwrite.io/docs/references/cloud/client-web/account#deleteSessions)
     * instead.
     *
     * @param sessionId Session ID. Use the string 'current' to delete the current device session.
     * @return appwrite::Result<void>
     */
    [[nodiscard]] appwrite::Result<void> deleteSession(        std::string_view sessionId    ) {
                std::string path_ = std::format("/account/sessions/{}", sessionId);
        
        nlohmann::json params = nlohmann::json::object();
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callVoid("DELETE", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Logout the user. Use 'current' as the session ID to logout on this device,
     * use a session ID to logout on another device. If you're looking to logout
     * the user on all devices, use [Delete
     * Sessions](https://appwrite.io/docs/references/cloud/client-web/account#deleteSessions)
     * instead.
     *
     * @param sessionId Session ID. Use the string 'current' to delete the current device session.
     * @return appwrite::Result<void>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<void>> deleteSessionAsync(        std::string_view sessionId    ) {
                std::string path_ = std::format("/account/sessions/{}", sessionId);
        
        nlohmann::json params = nlohmann::json::object();
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callVoidAsync("DELETE", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Block the currently logged in user account. Behind the scene, the user
     * record is not deleted but permanently blocked from any access. To
     * completely delete a user, use the Users API instead.
     *
     * @return appwrite::Result<appwrite::models::User>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::User> updateStatus(    ) {
                std::string path_ = std::format("/account/status");
        
        nlohmann::json params = nlohmann::json::object();
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::User>("PATCH", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Block the currently logged in user account. Behind the scene, the user
     * record is not deleted but permanently blocked from any access. To
     * completely delete a user, use the Users API instead.
     *
     * @return appwrite::Result<appwrite::models::User>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::User>> updateStatusAsync(    ) {
                std::string path_ = std::format("/account/status");
        
        nlohmann::json params = nlohmann::json::object();
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::User>("PATCH", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Sends the user an email with a secret key for creating a session. If the
     * email address has never been used, a **new account is created** using the
     * provided `userId`. Otherwise, if the email address is already attached to
     * an account, the **user ID is ignored**. Then, the user will receive an
     * email with the one-time password. Use the returned user ID and secret and
     * submit a request to the [POST
     * /v1/account/sessions/token](https://appwrite.io/docs/references/cloud/client-web/account#createSession)
     * endpoint to complete the login process. The secret sent to the user's email
     * is valid for 15 minutes.

A user is limited to 10 active sessions at a time
     * by default. [Learn more about session
     * limits](https://appwrite.io/docs/authentication-security#limits).
     *
     * @param userId User ID. Choose a custom ID or generate a random ID with `ID.unique()`. Valid chars are a-z, A-Z, 0-9, period, hyphen, and underscore. Can't start with a special char. Max length is 36 chars. If the email address has never been used, a new account is created using the provided userId. Otherwise, if the email address is already attached to an account, the user ID is ignored.
     * @param email User email.
     * @param phrase Toggle for security phrase. If enabled, email will be send with a randomly generated phrase and the phrase will also be included in the response. Confirming phrases match increases the security of your authentication flow.
     * @return appwrite::Result<appwrite::models::Token>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::Token> createEmailToken(        std::string_view userId,         std::string_view email,         std::optional<bool> phrase = false    ) {
                std::string path_ = std::format("/account/tokens/email");
        
        nlohmann::json params = nlohmann::json::object();
        params["userId"] = std::string(userId);
        params["email"] = std::string(email);
        if (phrase.has_value()) {
            params["phrase"] = *phrase;
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::Token>("POST", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Sends the user an email with a secret key for creating a session. If the
     * email address has never been used, a **new account is created** using the
     * provided `userId`. Otherwise, if the email address is already attached to
     * an account, the **user ID is ignored**. Then, the user will receive an
     * email with the one-time password. Use the returned user ID and secret and
     * submit a request to the [POST
     * /v1/account/sessions/token](https://appwrite.io/docs/references/cloud/client-web/account#createSession)
     * endpoint to complete the login process. The secret sent to the user's email
     * is valid for 15 minutes.

A user is limited to 10 active sessions at a time
     * by default. [Learn more about session
     * limits](https://appwrite.io/docs/authentication-security#limits).
     *
     * @param userId User ID. Choose a custom ID or generate a random ID with `ID.unique()`. Valid chars are a-z, A-Z, 0-9, period, hyphen, and underscore. Can't start with a special char. Max length is 36 chars. If the email address has never been used, a new account is created using the provided userId. Otherwise, if the email address is already attached to an account, the user ID is ignored.
     * @param email User email.
     * @param phrase Toggle for security phrase. If enabled, email will be send with a randomly generated phrase and the phrase will also be included in the response. Confirming phrases match increases the security of your authentication flow.
     * @return appwrite::Result<appwrite::models::Token>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::Token>> createEmailTokenAsync(        std::string_view userId,         std::string_view email,         std::optional<bool> phrase = false    ) {
                std::string path_ = std::format("/account/tokens/email");
        
        nlohmann::json params = nlohmann::json::object();
        params["userId"] = std::string(userId);
        params["email"] = std::string(email);
        if (phrase.has_value()) {
            params["phrase"] = *phrase;
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::Token>("POST", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Sends the user an email with a secret key for creating a session. If the
     * provided user ID has not been registered, a new user will be created. When
     * the user clicks the link in the email, the user is redirected back to the
     * URL you provided with the secret key and userId values attached to the URL
     * query string. Use the query string parameters to submit a request to the
     * [POST
     * /v1/account/sessions/token](https://appwrite.io/docs/references/cloud/client-web/account#createSession)
     * endpoint to complete the login process. The link sent to the user's email
     * address is valid for 1 hour.

A user is limited to 10 active sessions at a
     * time by default. [Learn more about session
     * limits](https://appwrite.io/docs/authentication-security#limits).
     *
     * @param userId Unique Id. Choose a custom ID or generate a random ID with `ID.unique()`. Valid chars are a-z, A-Z, 0-9, period, hyphen, and underscore. Can't start with a special char. Max length is 36 chars. If the email address has never been used, a new account is created using the provided userId. Otherwise, if the email address is already attached to an account, the user ID is ignored.
     * @param email User email.
     * @param url URL to redirect the user back to your app from the magic URL login. Only URLs from hostnames in your project platform list are allowed. This requirement helps to prevent an [open redirect](https://cheatsheetseries.owasp.org/cheatsheets/Unvalidated_Redirects_and_Forwards_Cheat_Sheet.html) attack against your project API.
     * @param phrase Toggle for security phrase. If enabled, email will be send with a randomly generated phrase and the phrase will also be included in the response. Confirming phrases match increases the security of your authentication flow.
     * @return appwrite::Result<appwrite::models::Token>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::Token> createMagicURLToken(        std::string_view userId,         std::string_view email,         std::optional<std::string_view> url = std::nullopt,         std::optional<bool> phrase = false    ) {
                std::string path_ = std::format("/account/tokens/magic-url");
        
        nlohmann::json params = nlohmann::json::object();
        params["userId"] = std::string(userId);
        params["email"] = std::string(email);
        if (url.has_value()) {
            params["url"] = std::string(url.value());
        }
        if (phrase.has_value()) {
            params["phrase"] = *phrase;
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::Token>("POST", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Sends the user an email with a secret key for creating a session. If the
     * provided user ID has not been registered, a new user will be created. When
     * the user clicks the link in the email, the user is redirected back to the
     * URL you provided with the secret key and userId values attached to the URL
     * query string. Use the query string parameters to submit a request to the
     * [POST
     * /v1/account/sessions/token](https://appwrite.io/docs/references/cloud/client-web/account#createSession)
     * endpoint to complete the login process. The link sent to the user's email
     * address is valid for 1 hour.

A user is limited to 10 active sessions at a
     * time by default. [Learn more about session
     * limits](https://appwrite.io/docs/authentication-security#limits).
     *
     * @param userId Unique Id. Choose a custom ID or generate a random ID with `ID.unique()`. Valid chars are a-z, A-Z, 0-9, period, hyphen, and underscore. Can't start with a special char. Max length is 36 chars. If the email address has never been used, a new account is created using the provided userId. Otherwise, if the email address is already attached to an account, the user ID is ignored.
     * @param email User email.
     * @param url URL to redirect the user back to your app from the magic URL login. Only URLs from hostnames in your project platform list are allowed. This requirement helps to prevent an [open redirect](https://cheatsheetseries.owasp.org/cheatsheets/Unvalidated_Redirects_and_Forwards_Cheat_Sheet.html) attack against your project API.
     * @param phrase Toggle for security phrase. If enabled, email will be send with a randomly generated phrase and the phrase will also be included in the response. Confirming phrases match increases the security of your authentication flow.
     * @return appwrite::Result<appwrite::models::Token>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::Token>> createMagicURLTokenAsync(        std::string_view userId,         std::string_view email,         std::optional<std::string_view> url = std::nullopt,         std::optional<bool> phrase = false    ) {
                std::string path_ = std::format("/account/tokens/magic-url");
        
        nlohmann::json params = nlohmann::json::object();
        params["userId"] = std::string(userId);
        params["email"] = std::string(email);
        if (url.has_value()) {
            params["url"] = std::string(url.value());
        }
        if (phrase.has_value()) {
            params["phrase"] = *phrase;
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::Token>("POST", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Allow the user to login to their account using the OAuth2 provider of their
     * choice. Each OAuth2 provider should be enabled from the Appwrite console
     * first. Use the success and failure arguments to provide a redirect URL's
     * back to your app when login is completed. 

If authentication succeeds,
     * `userId` and `secret` of a token will be appended to the success URL as
     * query parameters. These can be used to create a new session using the
     * [Create
     * session](https://appwrite.io/docs/references/cloud/client-web/account#createSession)
     * endpoint.

A user is limited to 10 active sessions at a time by default.
     * [Learn more about session
     * limits](https://appwrite.io/docs/authentication-security#limits).
     *
     * @param provider OAuth2 Provider. Currently, supported providers are: amazon, apple, auth0, authentik, autodesk, bitbucket, bitly, box, dailymotion, discord, disqus, dropbox, etsy, facebook, figma, github, gitlab, google, linkedin, microsoft, notion, oidc, okta, paypal, paypalSandbox, podio, salesforce, slack, spotify, stripe, tradeshift, tradeshiftBox, twitch, wordpress, x, yahoo, yammer, yandex, zoho, zoom.
     * @param success URL to redirect back to your app after a successful login attempt.  Only URLs from hostnames in your project's platform list are allowed. This requirement helps to prevent an [open redirect](https://cheatsheetseries.owasp.org/cheatsheets/Unvalidated_Redirects_and_Forwards_Cheat_Sheet.html) attack against your project API.
     * @param failure URL to redirect back to your app after a failed login attempt.  Only URLs from hostnames in your project's platform list are allowed. This requirement helps to prevent an [open redirect](https://cheatsheetseries.owasp.org/cheatsheets/Unvalidated_Redirects_and_Forwards_Cheat_Sheet.html) attack against your project API.
     * @param scopes A list of custom OAuth2 scopes. Check each provider internal docs for a list of supported scopes. Maximum of 100 scopes are allowed, each 4096 characters long.
     * @return appwrite::Result<std::string>
     */
    [[nodiscard]] appwrite::Result<std::string> createOAuth2Token(        appwrite::enums::OAuthProvider provider,         std::optional<std::string_view> success = std::nullopt,         std::optional<std::string_view> failure = std::nullopt,         std::optional<std::vector<std::string>> scopes = std::vector<std::string>{}    ) {
                std::string path_ = std::format("/account/tokens/oauth2/{}", appwrite::enums::toString(provider));
        
        nlohmann::json params = nlohmann::json::object();
        if (success.has_value()) {
            params["success"] = std::string(success.value());
        }
        if (failure.has_value()) {
            params["failure"] = std::string(failure.value());
        }
        if (scopes.has_value()) {
            params["scopes"] = *scopes;
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callLocation("GET", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Allow the user to login to their account using the OAuth2 provider of their
     * choice. Each OAuth2 provider should be enabled from the Appwrite console
     * first. Use the success and failure arguments to provide a redirect URL's
     * back to your app when login is completed. 

If authentication succeeds,
     * `userId` and `secret` of a token will be appended to the success URL as
     * query parameters. These can be used to create a new session using the
     * [Create
     * session](https://appwrite.io/docs/references/cloud/client-web/account#createSession)
     * endpoint.

A user is limited to 10 active sessions at a time by default.
     * [Learn more about session
     * limits](https://appwrite.io/docs/authentication-security#limits).
     *
     * @param provider OAuth2 Provider. Currently, supported providers are: amazon, apple, auth0, authentik, autodesk, bitbucket, bitly, box, dailymotion, discord, disqus, dropbox, etsy, facebook, figma, github, gitlab, google, linkedin, microsoft, notion, oidc, okta, paypal, paypalSandbox, podio, salesforce, slack, spotify, stripe, tradeshift, tradeshiftBox, twitch, wordpress, x, yahoo, yammer, yandex, zoho, zoom.
     * @param success URL to redirect back to your app after a successful login attempt.  Only URLs from hostnames in your project's platform list are allowed. This requirement helps to prevent an [open redirect](https://cheatsheetseries.owasp.org/cheatsheets/Unvalidated_Redirects_and_Forwards_Cheat_Sheet.html) attack against your project API.
     * @param failure URL to redirect back to your app after a failed login attempt.  Only URLs from hostnames in your project's platform list are allowed. This requirement helps to prevent an [open redirect](https://cheatsheetseries.owasp.org/cheatsheets/Unvalidated_Redirects_and_Forwards_Cheat_Sheet.html) attack against your project API.
     * @param scopes A list of custom OAuth2 scopes. Check each provider internal docs for a list of supported scopes. Maximum of 100 scopes are allowed, each 4096 characters long.
     * @return appwrite::Result<std::string>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<std::string>> createOAuth2TokenAsync(        appwrite::enums::OAuthProvider provider,         std::optional<std::string_view> success = std::nullopt,         std::optional<std::string_view> failure = std::nullopt,         std::optional<std::vector<std::string>> scopes = std::vector<std::string>{}    ) {
                std::string path_ = std::format("/account/tokens/oauth2/{}", appwrite::enums::toString(provider));
        
        nlohmann::json params = nlohmann::json::object();
        if (success.has_value()) {
            params["success"] = std::string(success.value());
        }
        if (failure.has_value()) {
            params["failure"] = std::string(failure.value());
        }
        if (scopes.has_value()) {
            params["scopes"] = *scopes;
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callLocationAsync("GET", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Sends the user an SMS with a secret key for creating a session. If the
     * provided user ID has not be registered, a new user will be created. Use the
     * returned user ID and secret and submit a request to the [POST
     * /v1/account/sessions/token](https://appwrite.io/docs/references/cloud/client-web/account#createSession)
     * endpoint to complete the login process. The secret sent to the user's phone
     * is valid for 15 minutes.

A user is limited to 10 active sessions at a time
     * by default. [Learn more about session
     * limits](https://appwrite.io/docs/authentication-security#limits).
     *
     * @param userId Unique Id. Choose a custom ID or generate a random ID with `ID.unique()`. Valid chars are a-z, A-Z, 0-9, period, hyphen, and underscore. Can't start with a special char. Max length is 36 chars. If the phone number has never been used, a new account is created using the provided userId. Otherwise, if the phone number is already attached to an account, the user ID is ignored.
     * @param phone Phone number. Format this number with a leading '+' and a country code, e.g., +16175551212.
     * @return appwrite::Result<appwrite::models::Token>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::Token> createPhoneToken(        std::string_view userId,         std::string_view phone    ) {
                std::string path_ = std::format("/account/tokens/phone");
        
        nlohmann::json params = nlohmann::json::object();
        params["userId"] = std::string(userId);
        params["phone"] = std::string(phone);
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::Token>("POST", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Sends the user an SMS with a secret key for creating a session. If the
     * provided user ID has not be registered, a new user will be created. Use the
     * returned user ID and secret and submit a request to the [POST
     * /v1/account/sessions/token](https://appwrite.io/docs/references/cloud/client-web/account#createSession)
     * endpoint to complete the login process. The secret sent to the user's phone
     * is valid for 15 minutes.

A user is limited to 10 active sessions at a time
     * by default. [Learn more about session
     * limits](https://appwrite.io/docs/authentication-security#limits).
     *
     * @param userId Unique Id. Choose a custom ID or generate a random ID with `ID.unique()`. Valid chars are a-z, A-Z, 0-9, period, hyphen, and underscore. Can't start with a special char. Max length is 36 chars. If the phone number has never been used, a new account is created using the provided userId. Otherwise, if the phone number is already attached to an account, the user ID is ignored.
     * @param phone Phone number. Format this number with a leading '+' and a country code, e.g., +16175551212.
     * @return appwrite::Result<appwrite::models::Token>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::Token>> createPhoneTokenAsync(        std::string_view userId,         std::string_view phone    ) {
                std::string path_ = std::format("/account/tokens/phone");
        
        nlohmann::json params = nlohmann::json::object();
        params["userId"] = std::string(userId);
        params["phone"] = std::string(phone);
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::Token>("POST", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Use this endpoint to send a verification message to your user email address
     * to confirm they are the valid owners of that address. Both the **userId**
     * and **secret** arguments will be passed as query parameters to the URL you
     * have provided to be attached to the verification email. The provided URL
     * should redirect the user back to your app and allow you to complete the
     * verification process by verifying both the **userId** and **secret**
     * parameters. Learn more about how to [complete the verification
     * process](https://appwrite.io/docs/references/cloud/client-web/account#updateVerification).
     * The verification link sent to the user's email address is valid for 7
     * days.

Please note that in order to avoid a [Redirect
     * Attack](https://github.com/OWASP/CheatSheetSeries/blob/master/cheatsheets/Unvalidated_Redirects_and_Forwards_Cheat_Sheet.md),
     * the only valid redirect URLs are the ones from domains you have set when
     * adding your platforms in the console interface.
     *
     * @param url URL to redirect the user back to your app from the verification email. Only URLs from hostnames in your project platform list are allowed. This requirement helps to prevent an [open redirect](https://cheatsheetseries.owasp.org/cheatsheets/Unvalidated_Redirects_and_Forwards_Cheat_Sheet.html) attack against your project API.
     * @return appwrite::Result<appwrite::models::Token>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::Token> createEmailVerification(        std::string_view url    ) {
                std::string path_ = std::format("/account/verifications/email");
        
        nlohmann::json params = nlohmann::json::object();
        params["url"] = std::string(url);
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::Token>("POST", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Use this endpoint to send a verification message to your user email address
     * to confirm they are the valid owners of that address. Both the **userId**
     * and **secret** arguments will be passed as query parameters to the URL you
     * have provided to be attached to the verification email. The provided URL
     * should redirect the user back to your app and allow you to complete the
     * verification process by verifying both the **userId** and **secret**
     * parameters. Learn more about how to [complete the verification
     * process](https://appwrite.io/docs/references/cloud/client-web/account#updateVerification).
     * The verification link sent to the user's email address is valid for 7
     * days.

Please note that in order to avoid a [Redirect
     * Attack](https://github.com/OWASP/CheatSheetSeries/blob/master/cheatsheets/Unvalidated_Redirects_and_Forwards_Cheat_Sheet.md),
     * the only valid redirect URLs are the ones from domains you have set when
     * adding your platforms in the console interface.
     *
     * @param url URL to redirect the user back to your app from the verification email. Only URLs from hostnames in your project platform list are allowed. This requirement helps to prevent an [open redirect](https://cheatsheetseries.owasp.org/cheatsheets/Unvalidated_Redirects_and_Forwards_Cheat_Sheet.html) attack against your project API.
     * @return appwrite::Result<appwrite::models::Token>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::Token>> createEmailVerificationAsync(        std::string_view url    ) {
                std::string path_ = std::format("/account/verifications/email");
        
        nlohmann::json params = nlohmann::json::object();
        params["url"] = std::string(url);
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::Token>("POST", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Use this endpoint to send a verification message to your user email address
     * to confirm they are the valid owners of that address. Both the **userId**
     * and **secret** arguments will be passed as query parameters to the URL you
     * have provided to be attached to the verification email. The provided URL
     * should redirect the user back to your app and allow you to complete the
     * verification process by verifying both the **userId** and **secret**
     * parameters. Learn more about how to [complete the verification
     * process](https://appwrite.io/docs/references/cloud/client-web/account#updateVerification).
     * The verification link sent to the user's email address is valid for 7
     * days.

Please note that in order to avoid a [Redirect
     * Attack](https://github.com/OWASP/CheatSheetSeries/blob/master/cheatsheets/Unvalidated_Redirects_and_Forwards_Cheat_Sheet.md),
     * the only valid redirect URLs are the ones from domains you have set when
     * adding your platforms in the console interface.
     *
     * @param url URL to redirect the user back to your app from the verification email. Only URLs from hostnames in your project platform list are allowed. This requirement helps to prevent an [open redirect](https://cheatsheetseries.owasp.org/cheatsheets/Unvalidated_Redirects_and_Forwards_Cheat_Sheet.html) attack against your project API.
     * @return appwrite::Result<appwrite::models::Token>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::Token> createVerification(        std::string_view url    ) {
                std::string path_ = std::format("/account/verifications/email");
        
        nlohmann::json params = nlohmann::json::object();
        params["url"] = std::string(url);
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::Token>("POST", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Use this endpoint to send a verification message to your user email address
     * to confirm they are the valid owners of that address. Both the **userId**
     * and **secret** arguments will be passed as query parameters to the URL you
     * have provided to be attached to the verification email. The provided URL
     * should redirect the user back to your app and allow you to complete the
     * verification process by verifying both the **userId** and **secret**
     * parameters. Learn more about how to [complete the verification
     * process](https://appwrite.io/docs/references/cloud/client-web/account#updateVerification).
     * The verification link sent to the user's email address is valid for 7
     * days.

Please note that in order to avoid a [Redirect
     * Attack](https://github.com/OWASP/CheatSheetSeries/blob/master/cheatsheets/Unvalidated_Redirects_and_Forwards_Cheat_Sheet.md),
     * the only valid redirect URLs are the ones from domains you have set when
     * adding your platforms in the console interface.
     *
     * @param url URL to redirect the user back to your app from the verification email. Only URLs from hostnames in your project platform list are allowed. This requirement helps to prevent an [open redirect](https://cheatsheetseries.owasp.org/cheatsheets/Unvalidated_Redirects_and_Forwards_Cheat_Sheet.html) attack against your project API.
     * @return appwrite::Result<appwrite::models::Token>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::Token>> createVerificationAsync(        std::string_view url    ) {
                std::string path_ = std::format("/account/verifications/email");
        
        nlohmann::json params = nlohmann::json::object();
        params["url"] = std::string(url);
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::Token>("POST", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Use this endpoint to complete the user email verification process. Use both
     * the **userId** and **secret** parameters that were attached to your app URL
     * to verify the user email ownership. If confirmed this route will return a
     * 200 status code.
     *
     * @param userId User ID.
     * @param secret Valid verification token.
     * @return appwrite::Result<appwrite::models::Token>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::Token> updateEmailVerification(        std::string_view userId,         std::string_view secret    ) {
                std::string path_ = std::format("/account/verifications/email");
        
        nlohmann::json params = nlohmann::json::object();
        params["userId"] = std::string(userId);
        params["secret"] = std::string(secret);
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::Token>("PUT", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Use this endpoint to complete the user email verification process. Use both
     * the **userId** and **secret** parameters that were attached to your app URL
     * to verify the user email ownership. If confirmed this route will return a
     * 200 status code.
     *
     * @param userId User ID.
     * @param secret Valid verification token.
     * @return appwrite::Result<appwrite::models::Token>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::Token>> updateEmailVerificationAsync(        std::string_view userId,         std::string_view secret    ) {
                std::string path_ = std::format("/account/verifications/email");
        
        nlohmann::json params = nlohmann::json::object();
        params["userId"] = std::string(userId);
        params["secret"] = std::string(secret);
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::Token>("PUT", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Use this endpoint to complete the user email verification process. Use both
     * the **userId** and **secret** parameters that were attached to your app URL
     * to verify the user email ownership. If confirmed this route will return a
     * 200 status code.
     *
     * @param userId User ID.
     * @param secret Valid verification token.
     * @return appwrite::Result<appwrite::models::Token>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::Token> updateVerification(        std::string_view userId,         std::string_view secret    ) {
                std::string path_ = std::format("/account/verifications/email");
        
        nlohmann::json params = nlohmann::json::object();
        params["userId"] = std::string(userId);
        params["secret"] = std::string(secret);
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::Token>("PUT", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Use this endpoint to complete the user email verification process. Use both
     * the **userId** and **secret** parameters that were attached to your app URL
     * to verify the user email ownership. If confirmed this route will return a
     * 200 status code.
     *
     * @param userId User ID.
     * @param secret Valid verification token.
     * @return appwrite::Result<appwrite::models::Token>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::Token>> updateVerificationAsync(        std::string_view userId,         std::string_view secret    ) {
                std::string path_ = std::format("/account/verifications/email");
        
        nlohmann::json params = nlohmann::json::object();
        params["userId"] = std::string(userId);
        params["secret"] = std::string(secret);
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::Token>("PUT", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Use this endpoint to send a verification SMS to the currently logged in
     * user. This endpoint is meant for use after updating a user's phone number
     * using the
     * [accountUpdatePhone](https://appwrite.io/docs/references/cloud/client-web/account#updatePhone)
     * endpoint. Learn more about how to [complete the verification
     * process](https://appwrite.io/docs/references/cloud/client-web/account#updatePhoneVerification).
     * The verification code sent to the user's phone number is valid for 15
     * minutes.
     *
     * @return appwrite::Result<appwrite::models::Token>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::Token> createPhoneVerification(    ) {
                std::string path_ = std::format("/account/verifications/phone");
        
        nlohmann::json params = nlohmann::json::object();
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::Token>("POST", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Use this endpoint to send a verification SMS to the currently logged in
     * user. This endpoint is meant for use after updating a user's phone number
     * using the
     * [accountUpdatePhone](https://appwrite.io/docs/references/cloud/client-web/account#updatePhone)
     * endpoint. Learn more about how to [complete the verification
     * process](https://appwrite.io/docs/references/cloud/client-web/account#updatePhoneVerification).
     * The verification code sent to the user's phone number is valid for 15
     * minutes.
     *
     * @return appwrite::Result<appwrite::models::Token>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::Token>> createPhoneVerificationAsync(    ) {
                std::string path_ = std::format("/account/verifications/phone");
        
        nlohmann::json params = nlohmann::json::object();
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::Token>("POST", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Use this endpoint to complete the user phone verification process. Use the
     * **userId** and **secret** that were sent to your user's phone number to
     * verify the user email ownership. If confirmed this route will return a 200
     * status code.
     *
     * @param userId User ID.
     * @param secret Valid verification token.
     * @return appwrite::Result<appwrite::models::Token>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::Token> updatePhoneVerification(        std::string_view userId,         std::string_view secret    ) {
                std::string path_ = std::format("/account/verifications/phone");
        
        nlohmann::json params = nlohmann::json::object();
        params["userId"] = std::string(userId);
        params["secret"] = std::string(secret);
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::Token>("PUT", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Use this endpoint to complete the user phone verification process. Use the
     * **userId** and **secret** that were sent to your user's phone number to
     * verify the user email ownership. If confirmed this route will return a 200
     * status code.
     *
     * @param userId User ID.
     * @param secret Valid verification token.
     * @return appwrite::Result<appwrite::models::Token>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::Token>> updatePhoneVerificationAsync(        std::string_view userId,         std::string_view secret    ) {
                std::string path_ = std::format("/account/verifications/phone");
        
        nlohmann::json params = nlohmann::json::object();
        params["userId"] = std::string(userId);
        params["secret"] = std::string(secret);
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::Token>("PUT", path_, std::move(local_headers_), std::move(params));
    }
};

/**
 * @brief The Databases service allows you to create structured collections of documents, query and filter lists of documents
 */
class Databases : public Service {
public:
    explicit Databases(Client& client) : Service(client) {}

    /**
     * Get a list of all databases from the current Appwrite project. You can use
     * the search parameter to filter your results.
     *
     * @param queries Array of query strings generated using the Query class provided by the SDK. [Learn more about queries](https://appwrite.io/docs/queries). Maximum of 100 queries are allowed, each 4096 characters long. You may filter on the following attributes: name
     * @param search Search term to filter your list results. Max length: 256 chars.
     * @param total When set to false, the total count returned will be 0 and will not be calculated.
     * @return appwrite::Result<appwrite::models::DatabaseList>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::DatabaseList> list(        std::optional<std::vector<std::string>> queries = std::vector<std::string>{},         std::optional<std::string_view> search = std::nullopt,         std::optional<bool> total = true    ) {
                std::string path_ = std::format("/databases");
        
        nlohmann::json params = nlohmann::json::object();
        if (queries.has_value()) {
            params["queries"] = *queries;
        }
        if (search.has_value()) {
            params["search"] = std::string(search.value());
        }
        if (total.has_value()) {
            params["total"] = *total;
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::DatabaseList>("GET", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Get a list of all databases from the current Appwrite project. You can use
     * the search parameter to filter your results.
     *
     * @param queries Array of query strings generated using the Query class provided by the SDK. [Learn more about queries](https://appwrite.io/docs/queries). Maximum of 100 queries are allowed, each 4096 characters long. You may filter on the following attributes: name
     * @param search Search term to filter your list results. Max length: 256 chars.
     * @param total When set to false, the total count returned will be 0 and will not be calculated.
     * @return appwrite::Result<appwrite::models::DatabaseList>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::DatabaseList>> listAsync(        std::optional<std::vector<std::string>> queries = std::vector<std::string>{},         std::optional<std::string_view> search = std::nullopt,         std::optional<bool> total = true    ) {
                std::string path_ = std::format("/databases");
        
        nlohmann::json params = nlohmann::json::object();
        if (queries.has_value()) {
            params["queries"] = *queries;
        }
        if (search.has_value()) {
            params["search"] = std::string(search.value());
        }
        if (total.has_value()) {
            params["total"] = *total;
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::DatabaseList>("GET", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Create a new Database.
     *
     * @param databaseId Unique Id. Choose a custom ID or generate a random ID with `ID.unique()`. Valid chars are a-z, A-Z, 0-9, period, hyphen, and underscore. Can't start with a special char. Max length is 36 chars.
     * @param name Database name. Max length: 128 chars.
     * @param enabled Is the database enabled? When set to 'disabled', users cannot access the database but Server SDKs with an API key can still read and write to the database. No data is lost when this is toggled.
     * @return appwrite::Result<appwrite::models::Database>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::Database> create(        std::string_view databaseId,         std::string_view name,         std::optional<bool> enabled = true    ) {
                std::string path_ = std::format("/databases");
        
        nlohmann::json params = nlohmann::json::object();
        params["databaseId"] = std::string(databaseId);
        params["name"] = std::string(name);
        if (enabled.has_value()) {
            params["enabled"] = *enabled;
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::Database>("POST", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Create a new Database.
     *
     * @param databaseId Unique Id. Choose a custom ID or generate a random ID with `ID.unique()`. Valid chars are a-z, A-Z, 0-9, period, hyphen, and underscore. Can't start with a special char. Max length is 36 chars.
     * @param name Database name. Max length: 128 chars.
     * @param enabled Is the database enabled? When set to 'disabled', users cannot access the database but Server SDKs with an API key can still read and write to the database. No data is lost when this is toggled.
     * @return appwrite::Result<appwrite::models::Database>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::Database>> createAsync(        std::string_view databaseId,         std::string_view name,         std::optional<bool> enabled = true    ) {
                std::string path_ = std::format("/databases");
        
        nlohmann::json params = nlohmann::json::object();
        params["databaseId"] = std::string(databaseId);
        params["name"] = std::string(name);
        if (enabled.has_value()) {
            params["enabled"] = *enabled;
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::Database>("POST", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * List transactions across all databases.
     *
     * @param queries Array of query strings generated using the Query class provided by the SDK. [Learn more about queries](https://appwrite.io/docs/queries).
     * @return appwrite::Result<appwrite::models::TransactionList>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::TransactionList> listTransactions(        std::optional<std::vector<std::string>> queries = std::vector<std::string>{}    ) {
                std::string path_ = std::format("/databases/transactions");
        
        nlohmann::json params = nlohmann::json::object();
        if (queries.has_value()) {
            params["queries"] = *queries;
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::TransactionList>("GET", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * List transactions across all databases.
     *
     * @param queries Array of query strings generated using the Query class provided by the SDK. [Learn more about queries](https://appwrite.io/docs/queries).
     * @return appwrite::Result<appwrite::models::TransactionList>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::TransactionList>> listTransactionsAsync(        std::optional<std::vector<std::string>> queries = std::vector<std::string>{}    ) {
                std::string path_ = std::format("/databases/transactions");
        
        nlohmann::json params = nlohmann::json::object();
        if (queries.has_value()) {
            params["queries"] = *queries;
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::TransactionList>("GET", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Create a new transaction.
     *
     * @param ttl Seconds before the transaction expires.
     * @return appwrite::Result<appwrite::models::Transaction>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::Transaction> createTransaction(        std::optional<int64_t> ttl = 300    ) {
                std::string path_ = std::format("/databases/transactions");
        
        nlohmann::json params = nlohmann::json::object();
        if (ttl.has_value()) {
            params["ttl"] = *ttl;
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::Transaction>("POST", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Create a new transaction.
     *
     * @param ttl Seconds before the transaction expires.
     * @return appwrite::Result<appwrite::models::Transaction>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::Transaction>> createTransactionAsync(        std::optional<int64_t> ttl = 300    ) {
                std::string path_ = std::format("/databases/transactions");
        
        nlohmann::json params = nlohmann::json::object();
        if (ttl.has_value()) {
            params["ttl"] = *ttl;
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::Transaction>("POST", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Get a transaction by its unique ID.
     *
     * @param transactionId Transaction ID.
     * @return appwrite::Result<appwrite::models::Transaction>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::Transaction> getTransaction(        std::string_view transactionId    ) {
                std::string path_ = std::format("/databases/transactions/{}", transactionId);
        
        nlohmann::json params = nlohmann::json::object();
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::Transaction>("GET", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Get a transaction by its unique ID.
     *
     * @param transactionId Transaction ID.
     * @return appwrite::Result<appwrite::models::Transaction>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::Transaction>> getTransactionAsync(        std::string_view transactionId    ) {
                std::string path_ = std::format("/databases/transactions/{}", transactionId);
        
        nlohmann::json params = nlohmann::json::object();
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::Transaction>("GET", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Update a transaction, to either commit or roll back its operations.
     *
     * @param transactionId Transaction ID.
     * @param commit Commit transaction?
     * @param rollback Rollback transaction?
     * @return appwrite::Result<appwrite::models::Transaction>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::Transaction> updateTransaction(        std::string_view transactionId,         std::optional<bool> commit = false,         std::optional<bool> rollback = false    ) {
                std::string path_ = std::format("/databases/transactions/{}", transactionId);
        
        nlohmann::json params = nlohmann::json::object();
        if (commit.has_value()) {
            params["commit"] = *commit;
        }
        if (rollback.has_value()) {
            params["rollback"] = *rollback;
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::Transaction>("PATCH", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Update a transaction, to either commit or roll back its operations.
     *
     * @param transactionId Transaction ID.
     * @param commit Commit transaction?
     * @param rollback Rollback transaction?
     * @return appwrite::Result<appwrite::models::Transaction>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::Transaction>> updateTransactionAsync(        std::string_view transactionId,         std::optional<bool> commit = false,         std::optional<bool> rollback = false    ) {
                std::string path_ = std::format("/databases/transactions/{}", transactionId);
        
        nlohmann::json params = nlohmann::json::object();
        if (commit.has_value()) {
            params["commit"] = *commit;
        }
        if (rollback.has_value()) {
            params["rollback"] = *rollback;
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::Transaction>("PATCH", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Delete a transaction by its unique ID.
     *
     * @param transactionId Transaction ID.
     * @return appwrite::Result<void>
     */
    [[nodiscard]] appwrite::Result<void> deleteTransaction(        std::string_view transactionId    ) {
                std::string path_ = std::format("/databases/transactions/{}", transactionId);
        
        nlohmann::json params = nlohmann::json::object();
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callVoid("DELETE", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Delete a transaction by its unique ID.
     *
     * @param transactionId Transaction ID.
     * @return appwrite::Result<void>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<void>> deleteTransactionAsync(        std::string_view transactionId    ) {
                std::string path_ = std::format("/databases/transactions/{}", transactionId);
        
        nlohmann::json params = nlohmann::json::object();
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callVoidAsync("DELETE", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Create multiple operations in a single transaction.
     *
     * @param transactionId Transaction ID.
     * @param operations Array of staged operations.
     * @return appwrite::Result<appwrite::models::Transaction>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::Transaction> createOperations(        std::string_view transactionId,         std::optional<std::vector<nlohmann::json>> operations = std::vector<nlohmann::json>{}    ) {
                std::string path_ = std::format("/databases/transactions/{}/operations", transactionId);
        
        nlohmann::json params = nlohmann::json::object();
        if (operations.has_value()) {
            params["operations"] = *operations;
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::Transaction>("POST", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Create multiple operations in a single transaction.
     *
     * @param transactionId Transaction ID.
     * @param operations Array of staged operations.
     * @return appwrite::Result<appwrite::models::Transaction>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::Transaction>> createOperationsAsync(        std::string_view transactionId,         std::optional<std::vector<nlohmann::json>> operations = std::vector<nlohmann::json>{}    ) {
                std::string path_ = std::format("/databases/transactions/{}/operations", transactionId);
        
        nlohmann::json params = nlohmann::json::object();
        if (operations.has_value()) {
            params["operations"] = *operations;
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::Transaction>("POST", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Get a database by its unique ID. This endpoint response returns a JSON
     * object with the database metadata.
     *
     * @param databaseId Database ID.
     * @return appwrite::Result<appwrite::models::Database>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::Database> get(        std::string_view databaseId    ) {
                std::string path_ = std::format("/databases/{}", databaseId);
        
        nlohmann::json params = nlohmann::json::object();
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::Database>("GET", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Get a database by its unique ID. This endpoint response returns a JSON
     * object with the database metadata.
     *
     * @param databaseId Database ID.
     * @return appwrite::Result<appwrite::models::Database>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::Database>> getAsync(        std::string_view databaseId    ) {
                std::string path_ = std::format("/databases/{}", databaseId);
        
        nlohmann::json params = nlohmann::json::object();
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::Database>("GET", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Update a database by its unique ID.
     *
     * @param databaseId Database ID.
     * @param name Database name. Max length: 128 chars.
     * @param enabled Is database enabled? When set to 'disabled', users cannot access the database but Server SDKs with an API key can still read and write to the database. No data is lost when this is toggled.
     * @return appwrite::Result<appwrite::models::Database>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::Database> update(        std::string_view databaseId,         std::optional<std::string_view> name = std::nullopt,         std::optional<bool> enabled = true    ) {
                std::string path_ = std::format("/databases/{}", databaseId);
        
        nlohmann::json params = nlohmann::json::object();
        if (name.has_value()) {
            params["name"] = std::string(name.value());
        }
        if (enabled.has_value()) {
            params["enabled"] = *enabled;
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::Database>("PUT", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Update a database by its unique ID.
     *
     * @param databaseId Database ID.
     * @param name Database name. Max length: 128 chars.
     * @param enabled Is database enabled? When set to 'disabled', users cannot access the database but Server SDKs with an API key can still read and write to the database. No data is lost when this is toggled.
     * @return appwrite::Result<appwrite::models::Database>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::Database>> updateAsync(        std::string_view databaseId,         std::optional<std::string_view> name = std::nullopt,         std::optional<bool> enabled = true    ) {
                std::string path_ = std::format("/databases/{}", databaseId);
        
        nlohmann::json params = nlohmann::json::object();
        if (name.has_value()) {
            params["name"] = std::string(name.value());
        }
        if (enabled.has_value()) {
            params["enabled"] = *enabled;
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::Database>("PUT", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Delete a database by its unique ID. Only API keys with with databases.write
     * scope can delete a database.
     *
     * @param databaseId Database ID.
     * @return appwrite::Result<void>
     */
    [[nodiscard]] appwrite::Result<void> delete_(        std::string_view databaseId    ) {
                std::string path_ = std::format("/databases/{}", databaseId);
        
        nlohmann::json params = nlohmann::json::object();
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callVoid("DELETE", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Delete a database by its unique ID. Only API keys with with databases.write
     * scope can delete a database.
     *
     * @param databaseId Database ID.
     * @return appwrite::Result<void>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<void>> delete_Async(        std::string_view databaseId    ) {
                std::string path_ = std::format("/databases/{}", databaseId);
        
        nlohmann::json params = nlohmann::json::object();
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callVoidAsync("DELETE", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Get a list of all collections that belong to the provided databaseId. You
     * can use the search parameter to filter your results.
     *
     * @param databaseId Database ID.
     * @param queries Array of query strings generated using the Query class provided by the SDK. [Learn more about queries](https://appwrite.io/docs/queries). Maximum of 100 queries are allowed, each 4096 characters long. You may filter on the following attributes: name, enabled, documentSecurity
     * @param search Search term to filter your list results. Max length: 256 chars.
     * @param total When set to false, the total count returned will be 0 and will not be calculated.
     * @return appwrite::Result<appwrite::models::CollectionList>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::CollectionList> listCollections(        std::string_view databaseId,         std::optional<std::vector<std::string>> queries = std::vector<std::string>{},         std::optional<std::string_view> search = std::nullopt,         std::optional<bool> total = true    ) {
                std::string path_ = std::format("/databases/{}/collections", databaseId);
        
        nlohmann::json params = nlohmann::json::object();
        if (queries.has_value()) {
            params["queries"] = *queries;
        }
        if (search.has_value()) {
            params["search"] = std::string(search.value());
        }
        if (total.has_value()) {
            params["total"] = *total;
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::CollectionList>("GET", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Get a list of all collections that belong to the provided databaseId. You
     * can use the search parameter to filter your results.
     *
     * @param databaseId Database ID.
     * @param queries Array of query strings generated using the Query class provided by the SDK. [Learn more about queries](https://appwrite.io/docs/queries). Maximum of 100 queries are allowed, each 4096 characters long. You may filter on the following attributes: name, enabled, documentSecurity
     * @param search Search term to filter your list results. Max length: 256 chars.
     * @param total When set to false, the total count returned will be 0 and will not be calculated.
     * @return appwrite::Result<appwrite::models::CollectionList>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::CollectionList>> listCollectionsAsync(        std::string_view databaseId,         std::optional<std::vector<std::string>> queries = std::vector<std::string>{},         std::optional<std::string_view> search = std::nullopt,         std::optional<bool> total = true    ) {
                std::string path_ = std::format("/databases/{}/collections", databaseId);
        
        nlohmann::json params = nlohmann::json::object();
        if (queries.has_value()) {
            params["queries"] = *queries;
        }
        if (search.has_value()) {
            params["search"] = std::string(search.value());
        }
        if (total.has_value()) {
            params["total"] = *total;
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::CollectionList>("GET", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Create a new Collection. Before using this route, you should create a new
     * database resource using either a [server
     * integration](https://appwrite.io/docs/server/databases#databasesCreateCollection)
     * API or directly from your database console.
     *
     * @param databaseId Database ID.
     * @param collectionId Unique Id. Choose a custom ID or generate a random ID with `ID.unique()`. Valid chars are a-z, A-Z, 0-9, period, hyphen, and underscore. Can't start with a special char. Max length is 36 chars.
     * @param name Collection name. Max length: 128 chars.
     * @param permissions An array of permissions strings. By default, no user is granted with any permissions. [Learn more about permissions](https://appwrite.io/docs/permissions).
     * @param documentSecurity Enables configuring permissions for individual documents. A user needs one of document or collection level permissions to access a document. [Learn more about permissions](https://appwrite.io/docs/permissions).
     * @param enabled Is collection enabled? When set to 'disabled', users cannot access the collection but Server SDKs with and API key can still read and write to the collection. No data is lost when this is toggled.
     * @param attributes Array of attribute definitions to create. Each attribute should contain: key (string), type (string: string, integer, float, boolean, datetime), size (integer, required for string type), required (boolean, optional), default (mixed, optional), array (boolean, optional), and type-specific options.
     * @param indexes Array of index definitions to create. Each index should contain: key (string), type (string: key, fulltext, unique, spatial), attributes (array of attribute keys), orders (array of ASC/DESC, optional), and lengths (array of integers, optional).
     * @return appwrite::Result<appwrite::models::Collection>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::Collection> createCollection(        std::string_view databaseId,         std::string_view collectionId,         std::string_view name,         std::optional<std::vector<std::string>> permissions = std::nullopt,         std::optional<bool> documentSecurity = false,         std::optional<bool> enabled = true,         std::optional<std::vector<nlohmann::json>> attributes = std::vector<nlohmann::json>{},         std::optional<std::vector<nlohmann::json>> indexes = std::vector<nlohmann::json>{}    ) {
                std::string path_ = std::format("/databases/{}/collections", databaseId);
        
        nlohmann::json params = nlohmann::json::object();
        params["collectionId"] = std::string(collectionId);
        params["name"] = std::string(name);
        if (permissions.has_value()) {
            params["permissions"] = *permissions;
        }
        if (documentSecurity.has_value()) {
            params["documentSecurity"] = *documentSecurity;
        }
        if (enabled.has_value()) {
            params["enabled"] = *enabled;
        }
        if (attributes.has_value()) {
            params["attributes"] = *attributes;
        }
        if (indexes.has_value()) {
            params["indexes"] = *indexes;
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::Collection>("POST", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Create a new Collection. Before using this route, you should create a new
     * database resource using either a [server
     * integration](https://appwrite.io/docs/server/databases#databasesCreateCollection)
     * API or directly from your database console.
     *
     * @param databaseId Database ID.
     * @param collectionId Unique Id. Choose a custom ID or generate a random ID with `ID.unique()`. Valid chars are a-z, A-Z, 0-9, period, hyphen, and underscore. Can't start with a special char. Max length is 36 chars.
     * @param name Collection name. Max length: 128 chars.
     * @param permissions An array of permissions strings. By default, no user is granted with any permissions. [Learn more about permissions](https://appwrite.io/docs/permissions).
     * @param documentSecurity Enables configuring permissions for individual documents. A user needs one of document or collection level permissions to access a document. [Learn more about permissions](https://appwrite.io/docs/permissions).
     * @param enabled Is collection enabled? When set to 'disabled', users cannot access the collection but Server SDKs with and API key can still read and write to the collection. No data is lost when this is toggled.
     * @param attributes Array of attribute definitions to create. Each attribute should contain: key (string), type (string: string, integer, float, boolean, datetime), size (integer, required for string type), required (boolean, optional), default (mixed, optional), array (boolean, optional), and type-specific options.
     * @param indexes Array of index definitions to create. Each index should contain: key (string), type (string: key, fulltext, unique, spatial), attributes (array of attribute keys), orders (array of ASC/DESC, optional), and lengths (array of integers, optional).
     * @return appwrite::Result<appwrite::models::Collection>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::Collection>> createCollectionAsync(        std::string_view databaseId,         std::string_view collectionId,         std::string_view name,         std::optional<std::vector<std::string>> permissions = std::nullopt,         std::optional<bool> documentSecurity = false,         std::optional<bool> enabled = true,         std::optional<std::vector<nlohmann::json>> attributes = std::vector<nlohmann::json>{},         std::optional<std::vector<nlohmann::json>> indexes = std::vector<nlohmann::json>{}    ) {
                std::string path_ = std::format("/databases/{}/collections", databaseId);
        
        nlohmann::json params = nlohmann::json::object();
        params["collectionId"] = std::string(collectionId);
        params["name"] = std::string(name);
        if (permissions.has_value()) {
            params["permissions"] = *permissions;
        }
        if (documentSecurity.has_value()) {
            params["documentSecurity"] = *documentSecurity;
        }
        if (enabled.has_value()) {
            params["enabled"] = *enabled;
        }
        if (attributes.has_value()) {
            params["attributes"] = *attributes;
        }
        if (indexes.has_value()) {
            params["indexes"] = *indexes;
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::Collection>("POST", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Get a collection by its unique ID. This endpoint response returns a JSON
     * object with the collection metadata.
     *
     * @param databaseId Database ID.
     * @param collectionId Collection ID.
     * @return appwrite::Result<appwrite::models::Collection>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::Collection> getCollection(        std::string_view databaseId,         std::string_view collectionId    ) {
                std::string path_ = std::format("/databases/{}/collections/{}", databaseId, collectionId);
        
        nlohmann::json params = nlohmann::json::object();
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::Collection>("GET", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Get a collection by its unique ID. This endpoint response returns a JSON
     * object with the collection metadata.
     *
     * @param databaseId Database ID.
     * @param collectionId Collection ID.
     * @return appwrite::Result<appwrite::models::Collection>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::Collection>> getCollectionAsync(        std::string_view databaseId,         std::string_view collectionId    ) {
                std::string path_ = std::format("/databases/{}/collections/{}", databaseId, collectionId);
        
        nlohmann::json params = nlohmann::json::object();
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::Collection>("GET", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Update a collection by its unique ID.
     *
     * @param databaseId Database ID.
     * @param collectionId Collection ID.
     * @param name Collection name. Max length: 128 chars.
     * @param permissions An array of permission strings. By default, the current permissions are inherited. [Learn more about permissions](https://appwrite.io/docs/permissions).
     * @param documentSecurity Enables configuring permissions for individual documents. A user needs one of document or collection level permissions to access a document. [Learn more about permissions](https://appwrite.io/docs/permissions).
     * @param enabled Is collection enabled? When set to 'disabled', users cannot access the collection but Server SDKs with and API key can still read and write to the collection. No data is lost when this is toggled.
     * @param purge When true, purge all cached list responses for this collection as part of the update. Use this to force readers to see fresh data immediately instead of waiting for the cache TTL to expire.
     * @return appwrite::Result<appwrite::models::Collection>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::Collection> updateCollection(        std::string_view databaseId,         std::string_view collectionId,         std::optional<std::string_view> name = std::nullopt,         std::optional<std::vector<std::string>> permissions = std::nullopt,         std::optional<bool> documentSecurity = false,         std::optional<bool> enabled = true,         std::optional<bool> purge = false    ) {
                std::string path_ = std::format("/databases/{}/collections/{}", databaseId, collectionId);
        
        nlohmann::json params = nlohmann::json::object();
        if (name.has_value()) {
            params["name"] = std::string(name.value());
        }
        if (permissions.has_value()) {
            params["permissions"] = *permissions;
        }
        if (documentSecurity.has_value()) {
            params["documentSecurity"] = *documentSecurity;
        }
        if (enabled.has_value()) {
            params["enabled"] = *enabled;
        }
        if (purge.has_value()) {
            params["purge"] = *purge;
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::Collection>("PUT", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Update a collection by its unique ID.
     *
     * @param databaseId Database ID.
     * @param collectionId Collection ID.
     * @param name Collection name. Max length: 128 chars.
     * @param permissions An array of permission strings. By default, the current permissions are inherited. [Learn more about permissions](https://appwrite.io/docs/permissions).
     * @param documentSecurity Enables configuring permissions for individual documents. A user needs one of document or collection level permissions to access a document. [Learn more about permissions](https://appwrite.io/docs/permissions).
     * @param enabled Is collection enabled? When set to 'disabled', users cannot access the collection but Server SDKs with and API key can still read and write to the collection. No data is lost when this is toggled.
     * @param purge When true, purge all cached list responses for this collection as part of the update. Use this to force readers to see fresh data immediately instead of waiting for the cache TTL to expire.
     * @return appwrite::Result<appwrite::models::Collection>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::Collection>> updateCollectionAsync(        std::string_view databaseId,         std::string_view collectionId,         std::optional<std::string_view> name = std::nullopt,         std::optional<std::vector<std::string>> permissions = std::nullopt,         std::optional<bool> documentSecurity = false,         std::optional<bool> enabled = true,         std::optional<bool> purge = false    ) {
                std::string path_ = std::format("/databases/{}/collections/{}", databaseId, collectionId);
        
        nlohmann::json params = nlohmann::json::object();
        if (name.has_value()) {
            params["name"] = std::string(name.value());
        }
        if (permissions.has_value()) {
            params["permissions"] = *permissions;
        }
        if (documentSecurity.has_value()) {
            params["documentSecurity"] = *documentSecurity;
        }
        if (enabled.has_value()) {
            params["enabled"] = *enabled;
        }
        if (purge.has_value()) {
            params["purge"] = *purge;
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::Collection>("PUT", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Delete a collection by its unique ID. Only users with write permissions
     * have access to delete this resource.
     *
     * @param databaseId Database ID.
     * @param collectionId Collection ID.
     * @return appwrite::Result<void>
     */
    [[nodiscard]] appwrite::Result<void> deleteCollection(        std::string_view databaseId,         std::string_view collectionId    ) {
                std::string path_ = std::format("/databases/{}/collections/{}", databaseId, collectionId);
        
        nlohmann::json params = nlohmann::json::object();
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callVoid("DELETE", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Delete a collection by its unique ID. Only users with write permissions
     * have access to delete this resource.
     *
     * @param databaseId Database ID.
     * @param collectionId Collection ID.
     * @return appwrite::Result<void>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<void>> deleteCollectionAsync(        std::string_view databaseId,         std::string_view collectionId    ) {
                std::string path_ = std::format("/databases/{}/collections/{}", databaseId, collectionId);
        
        nlohmann::json params = nlohmann::json::object();
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callVoidAsync("DELETE", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * List attributes in the collection.
     *
     * @param databaseId Database ID.
     * @param collectionId Collection ID.
     * @param queries Array of query strings generated using the Query class provided by the SDK. [Learn more about queries](https://appwrite.io/docs/queries). Maximum of 100 queries are allowed, each 4096 characters long. You may filter on the following attributes: key, type, size, required, array, status, error
     * @param total When set to false, the total count returned will be 0 and will not be calculated.
     * @return appwrite::Result<appwrite::models::AttributeList>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::AttributeList> listAttributes(        std::string_view databaseId,         std::string_view collectionId,         std::optional<std::vector<std::string>> queries = std::vector<std::string>{},         std::optional<bool> total = true    ) {
                std::string path_ = std::format("/databases/{}/collections/{}/attributes", databaseId, collectionId);
        
        nlohmann::json params = nlohmann::json::object();
        if (queries.has_value()) {
            params["queries"] = *queries;
        }
        if (total.has_value()) {
            params["total"] = *total;
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::AttributeList>("GET", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * List attributes in the collection.
     *
     * @param databaseId Database ID.
     * @param collectionId Collection ID.
     * @param queries Array of query strings generated using the Query class provided by the SDK. [Learn more about queries](https://appwrite.io/docs/queries). Maximum of 100 queries are allowed, each 4096 characters long. You may filter on the following attributes: key, type, size, required, array, status, error
     * @param total When set to false, the total count returned will be 0 and will not be calculated.
     * @return appwrite::Result<appwrite::models::AttributeList>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::AttributeList>> listAttributesAsync(        std::string_view databaseId,         std::string_view collectionId,         std::optional<std::vector<std::string>> queries = std::vector<std::string>{},         std::optional<bool> total = true    ) {
                std::string path_ = std::format("/databases/{}/collections/{}/attributes", databaseId, collectionId);
        
        nlohmann::json params = nlohmann::json::object();
        if (queries.has_value()) {
            params["queries"] = *queries;
        }
        if (total.has_value()) {
            params["total"] = *total;
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::AttributeList>("GET", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Create a boolean attribute.
     *
     * @param databaseId Database ID.
     * @param collectionId Collection ID. You can create a new collection using the Database service [server integration](https://appwrite.io/docs/server/databases#databasesCreateCollection).
     * @param key Attribute Key.
     * @param required Is attribute required?
     * @param default_ Default value for attribute when not provided. Cannot be set when attribute is required.
     * @param array Is attribute an array?
     * @return appwrite::Result<appwrite::models::AttributeBoolean>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::AttributeBoolean> createBooleanAttribute(        std::string_view databaseId,         std::string_view collectionId,         std::string_view key,         bool required,         std::optional<bool> default_ = std::nullopt,         std::optional<bool> array = false    ) {
                std::string path_ = std::format("/databases/{}/collections/{}/attributes/boolean", databaseId, collectionId);
        
        nlohmann::json params = nlohmann::json::object();
        params["key"] = std::string(key);
        params["required"] = required;
        if (default_.has_value()) {
            params["default"] = *default_;
        }
        if (array.has_value()) {
            params["array"] = *array;
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::AttributeBoolean>("POST", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Create a boolean attribute.
     *
     * @param databaseId Database ID.
     * @param collectionId Collection ID. You can create a new collection using the Database service [server integration](https://appwrite.io/docs/server/databases#databasesCreateCollection).
     * @param key Attribute Key.
     * @param required Is attribute required?
     * @param default_ Default value for attribute when not provided. Cannot be set when attribute is required.
     * @param array Is attribute an array?
     * @return appwrite::Result<appwrite::models::AttributeBoolean>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::AttributeBoolean>> createBooleanAttributeAsync(        std::string_view databaseId,         std::string_view collectionId,         std::string_view key,         bool required,         std::optional<bool> default_ = std::nullopt,         std::optional<bool> array = false    ) {
                std::string path_ = std::format("/databases/{}/collections/{}/attributes/boolean", databaseId, collectionId);
        
        nlohmann::json params = nlohmann::json::object();
        params["key"] = std::string(key);
        params["required"] = required;
        if (default_.has_value()) {
            params["default"] = *default_;
        }
        if (array.has_value()) {
            params["array"] = *array;
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::AttributeBoolean>("POST", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Update a boolean attribute. Changing the `default` value will not update
     * already existing documents.
     *
     * @param databaseId Database ID.
     * @param collectionId Collection ID. You can create a new collection using the Database service [server integration](https://appwrite.io/docs/server/databases#createCollection).
     * @param key Attribute Key.
     * @param required Is attribute required?
     * @param default_ Default value for attribute when not provided. Cannot be set when attribute is required.
     * @param newKey New attribute key.
     * @return appwrite::Result<appwrite::models::AttributeBoolean>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::AttributeBoolean> updateBooleanAttribute(        std::string_view databaseId,         std::string_view collectionId,         std::string_view key,         bool required,         bool default_,         std::optional<std::string_view> newKey = std::nullopt    ) {
                std::string path_ = std::format("/databases/{}/collections/{}/attributes/boolean/{}", databaseId, collectionId, key);
        
        nlohmann::json params = nlohmann::json::object();
        params["required"] = required;
        params["default"] = default_;
        if (newKey.has_value()) {
            params["newKey"] = std::string(newKey.value());
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::AttributeBoolean>("PATCH", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Update a boolean attribute. Changing the `default` value will not update
     * already existing documents.
     *
     * @param databaseId Database ID.
     * @param collectionId Collection ID. You can create a new collection using the Database service [server integration](https://appwrite.io/docs/server/databases#createCollection).
     * @param key Attribute Key.
     * @param required Is attribute required?
     * @param default_ Default value for attribute when not provided. Cannot be set when attribute is required.
     * @param newKey New attribute key.
     * @return appwrite::Result<appwrite::models::AttributeBoolean>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::AttributeBoolean>> updateBooleanAttributeAsync(        std::string_view databaseId,         std::string_view collectionId,         std::string_view key,         bool required,         bool default_,         std::optional<std::string_view> newKey = std::nullopt    ) {
                std::string path_ = std::format("/databases/{}/collections/{}/attributes/boolean/{}", databaseId, collectionId, key);
        
        nlohmann::json params = nlohmann::json::object();
        params["required"] = required;
        params["default"] = default_;
        if (newKey.has_value()) {
            params["newKey"] = std::string(newKey.value());
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::AttributeBoolean>("PATCH", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Create a date time attribute according to the ISO 8601 standard.
     *
     * @param databaseId Database ID.
     * @param collectionId Collection ID. You can create a new collection using the Database service [server integration](https://appwrite.io/docs/server/databases#createCollection).
     * @param key Attribute Key.
     * @param required Is attribute required?
     * @param default_ Default value for the attribute in [ISO 8601](https://www.iso.org/iso-8601-date-and-time-format.html) format. Cannot be set when attribute is required.
     * @param array Is attribute an array?
     * @return appwrite::Result<appwrite::models::AttributeDatetime>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::AttributeDatetime> createDatetimeAttribute(        std::string_view databaseId,         std::string_view collectionId,         std::string_view key,         bool required,         std::optional<std::string_view> default_ = std::nullopt,         std::optional<bool> array = false    ) {
                std::string path_ = std::format("/databases/{}/collections/{}/attributes/datetime", databaseId, collectionId);
        
        nlohmann::json params = nlohmann::json::object();
        params["key"] = std::string(key);
        params["required"] = required;
        if (default_.has_value()) {
            params["default"] = std::string(default_.value());
        }
        if (array.has_value()) {
            params["array"] = *array;
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::AttributeDatetime>("POST", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Create a date time attribute according to the ISO 8601 standard.
     *
     * @param databaseId Database ID.
     * @param collectionId Collection ID. You can create a new collection using the Database service [server integration](https://appwrite.io/docs/server/databases#createCollection).
     * @param key Attribute Key.
     * @param required Is attribute required?
     * @param default_ Default value for the attribute in [ISO 8601](https://www.iso.org/iso-8601-date-and-time-format.html) format. Cannot be set when attribute is required.
     * @param array Is attribute an array?
     * @return appwrite::Result<appwrite::models::AttributeDatetime>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::AttributeDatetime>> createDatetimeAttributeAsync(        std::string_view databaseId,         std::string_view collectionId,         std::string_view key,         bool required,         std::optional<std::string_view> default_ = std::nullopt,         std::optional<bool> array = false    ) {
                std::string path_ = std::format("/databases/{}/collections/{}/attributes/datetime", databaseId, collectionId);
        
        nlohmann::json params = nlohmann::json::object();
        params["key"] = std::string(key);
        params["required"] = required;
        if (default_.has_value()) {
            params["default"] = std::string(default_.value());
        }
        if (array.has_value()) {
            params["array"] = *array;
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::AttributeDatetime>("POST", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Update a date time attribute. Changing the `default` value will not update
     * already existing documents.
     *
     * @param databaseId Database ID.
     * @param collectionId Collection ID.
     * @param key Attribute Key.
     * @param required Is attribute required?
     * @param default_ Default value for attribute when not provided. Cannot be set when attribute is required.
     * @param newKey New attribute key.
     * @return appwrite::Result<appwrite::models::AttributeDatetime>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::AttributeDatetime> updateDatetimeAttribute(        std::string_view databaseId,         std::string_view collectionId,         std::string_view key,         bool required,         std::string_view default_,         std::optional<std::string_view> newKey = std::nullopt    ) {
                std::string path_ = std::format("/databases/{}/collections/{}/attributes/datetime/{}", databaseId, collectionId, key);
        
        nlohmann::json params = nlohmann::json::object();
        params["required"] = required;
        params["default"] = std::string(default_);
        if (newKey.has_value()) {
            params["newKey"] = std::string(newKey.value());
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::AttributeDatetime>("PATCH", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Update a date time attribute. Changing the `default` value will not update
     * already existing documents.
     *
     * @param databaseId Database ID.
     * @param collectionId Collection ID.
     * @param key Attribute Key.
     * @param required Is attribute required?
     * @param default_ Default value for attribute when not provided. Cannot be set when attribute is required.
     * @param newKey New attribute key.
     * @return appwrite::Result<appwrite::models::AttributeDatetime>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::AttributeDatetime>> updateDatetimeAttributeAsync(        std::string_view databaseId,         std::string_view collectionId,         std::string_view key,         bool required,         std::string_view default_,         std::optional<std::string_view> newKey = std::nullopt    ) {
                std::string path_ = std::format("/databases/{}/collections/{}/attributes/datetime/{}", databaseId, collectionId, key);
        
        nlohmann::json params = nlohmann::json::object();
        params["required"] = required;
        params["default"] = std::string(default_);
        if (newKey.has_value()) {
            params["newKey"] = std::string(newKey.value());
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::AttributeDatetime>("PATCH", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Create an email attribute.
     *
     * @param databaseId Database ID.
     * @param collectionId Collection ID.
     * @param key Attribute Key.
     * @param required Is attribute required?
     * @param default_ Default value for attribute when not provided. Cannot be set when attribute is required.
     * @param array Is attribute an array?
     * @return appwrite::Result<appwrite::models::AttributeEmail>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::AttributeEmail> createEmailAttribute(        std::string_view databaseId,         std::string_view collectionId,         std::string_view key,         bool required,         std::optional<std::string_view> default_ = std::nullopt,         std::optional<bool> array = false    ) {
                std::string path_ = std::format("/databases/{}/collections/{}/attributes/email", databaseId, collectionId);
        
        nlohmann::json params = nlohmann::json::object();
        params["key"] = std::string(key);
        params["required"] = required;
        if (default_.has_value()) {
            params["default"] = std::string(default_.value());
        }
        if (array.has_value()) {
            params["array"] = *array;
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::AttributeEmail>("POST", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Create an email attribute.
     *
     * @param databaseId Database ID.
     * @param collectionId Collection ID.
     * @param key Attribute Key.
     * @param required Is attribute required?
     * @param default_ Default value for attribute when not provided. Cannot be set when attribute is required.
     * @param array Is attribute an array?
     * @return appwrite::Result<appwrite::models::AttributeEmail>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::AttributeEmail>> createEmailAttributeAsync(        std::string_view databaseId,         std::string_view collectionId,         std::string_view key,         bool required,         std::optional<std::string_view> default_ = std::nullopt,         std::optional<bool> array = false    ) {
                std::string path_ = std::format("/databases/{}/collections/{}/attributes/email", databaseId, collectionId);
        
        nlohmann::json params = nlohmann::json::object();
        params["key"] = std::string(key);
        params["required"] = required;
        if (default_.has_value()) {
            params["default"] = std::string(default_.value());
        }
        if (array.has_value()) {
            params["array"] = *array;
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::AttributeEmail>("POST", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Update an email attribute. Changing the `default` value will not update
     * already existing documents.
     *
     * @param databaseId Database ID.
     * @param collectionId Collection ID.
     * @param key Attribute Key.
     * @param required Is attribute required?
     * @param default_ Default value for attribute when not provided. Cannot be set when attribute is required.
     * @param newKey New Attribute Key.
     * @return appwrite::Result<appwrite::models::AttributeEmail>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::AttributeEmail> updateEmailAttribute(        std::string_view databaseId,         std::string_view collectionId,         std::string_view key,         bool required,         std::string_view default_,         std::optional<std::string_view> newKey = std::nullopt    ) {
                std::string path_ = std::format("/databases/{}/collections/{}/attributes/email/{}", databaseId, collectionId, key);
        
        nlohmann::json params = nlohmann::json::object();
        params["required"] = required;
        params["default"] = std::string(default_);
        if (newKey.has_value()) {
            params["newKey"] = std::string(newKey.value());
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::AttributeEmail>("PATCH", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Update an email attribute. Changing the `default` value will not update
     * already existing documents.
     *
     * @param databaseId Database ID.
     * @param collectionId Collection ID.
     * @param key Attribute Key.
     * @param required Is attribute required?
     * @param default_ Default value for attribute when not provided. Cannot be set when attribute is required.
     * @param newKey New Attribute Key.
     * @return appwrite::Result<appwrite::models::AttributeEmail>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::AttributeEmail>> updateEmailAttributeAsync(        std::string_view databaseId,         std::string_view collectionId,         std::string_view key,         bool required,         std::string_view default_,         std::optional<std::string_view> newKey = std::nullopt    ) {
                std::string path_ = std::format("/databases/{}/collections/{}/attributes/email/{}", databaseId, collectionId, key);
        
        nlohmann::json params = nlohmann::json::object();
        params["required"] = required;
        params["default"] = std::string(default_);
        if (newKey.has_value()) {
            params["newKey"] = std::string(newKey.value());
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::AttributeEmail>("PATCH", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Create an enum attribute. The `elements` param acts as a white-list of
     * accepted values for this attribute.
     *
     * @param databaseId Database ID.
     * @param collectionId Collection ID.
     * @param key Attribute Key.
     * @param elements Array of enum values.
     * @param required Is attribute required?
     * @param default_ Default value for attribute when not provided. Cannot be set when attribute is required.
     * @param array Is attribute an array?
     * @return appwrite::Result<appwrite::models::AttributeEnum>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::AttributeEnum> createEnumAttribute(        std::string_view databaseId,         std::string_view collectionId,         std::string_view key,         std::vector<std::string> elements,         bool required,         std::optional<std::string_view> default_ = std::nullopt,         std::optional<bool> array = false    ) {
                std::string path_ = std::format("/databases/{}/collections/{}/attributes/enum", databaseId, collectionId);
        
        nlohmann::json params = nlohmann::json::object();
        params["key"] = std::string(key);
        params["elements"] = elements;
        params["required"] = required;
        if (default_.has_value()) {
            params["default"] = std::string(default_.value());
        }
        if (array.has_value()) {
            params["array"] = *array;
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::AttributeEnum>("POST", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Create an enum attribute. The `elements` param acts as a white-list of
     * accepted values for this attribute.
     *
     * @param databaseId Database ID.
     * @param collectionId Collection ID.
     * @param key Attribute Key.
     * @param elements Array of enum values.
     * @param required Is attribute required?
     * @param default_ Default value for attribute when not provided. Cannot be set when attribute is required.
     * @param array Is attribute an array?
     * @return appwrite::Result<appwrite::models::AttributeEnum>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::AttributeEnum>> createEnumAttributeAsync(        std::string_view databaseId,         std::string_view collectionId,         std::string_view key,         std::vector<std::string> elements,         bool required,         std::optional<std::string_view> default_ = std::nullopt,         std::optional<bool> array = false    ) {
                std::string path_ = std::format("/databases/{}/collections/{}/attributes/enum", databaseId, collectionId);
        
        nlohmann::json params = nlohmann::json::object();
        params["key"] = std::string(key);
        params["elements"] = elements;
        params["required"] = required;
        if (default_.has_value()) {
            params["default"] = std::string(default_.value());
        }
        if (array.has_value()) {
            params["array"] = *array;
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::AttributeEnum>("POST", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Update an enum attribute. Changing the `default` value will not update
     * already existing documents.
     *
     * @param databaseId Database ID.
     * @param collectionId Collection ID.
     * @param key Attribute Key.
     * @param elements Updated list of enum values.
     * @param required Is attribute required?
     * @param default_ Default value for attribute when not provided. Cannot be set when attribute is required.
     * @param newKey New Attribute Key.
     * @return appwrite::Result<appwrite::models::AttributeEnum>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::AttributeEnum> updateEnumAttribute(        std::string_view databaseId,         std::string_view collectionId,         std::string_view key,         std::vector<std::string> elements,         bool required,         std::string_view default_,         std::optional<std::string_view> newKey = std::nullopt    ) {
                std::string path_ = std::format("/databases/{}/collections/{}/attributes/enum/{}", databaseId, collectionId, key);
        
        nlohmann::json params = nlohmann::json::object();
        params["elements"] = elements;
        params["required"] = required;
        params["default"] = std::string(default_);
        if (newKey.has_value()) {
            params["newKey"] = std::string(newKey.value());
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::AttributeEnum>("PATCH", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Update an enum attribute. Changing the `default` value will not update
     * already existing documents.
     *
     * @param databaseId Database ID.
     * @param collectionId Collection ID.
     * @param key Attribute Key.
     * @param elements Updated list of enum values.
     * @param required Is attribute required?
     * @param default_ Default value for attribute when not provided. Cannot be set when attribute is required.
     * @param newKey New Attribute Key.
     * @return appwrite::Result<appwrite::models::AttributeEnum>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::AttributeEnum>> updateEnumAttributeAsync(        std::string_view databaseId,         std::string_view collectionId,         std::string_view key,         std::vector<std::string> elements,         bool required,         std::string_view default_,         std::optional<std::string_view> newKey = std::nullopt    ) {
                std::string path_ = std::format("/databases/{}/collections/{}/attributes/enum/{}", databaseId, collectionId, key);
        
        nlohmann::json params = nlohmann::json::object();
        params["elements"] = elements;
        params["required"] = required;
        params["default"] = std::string(default_);
        if (newKey.has_value()) {
            params["newKey"] = std::string(newKey.value());
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::AttributeEnum>("PATCH", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Create a float attribute. Optionally, minimum and maximum values can be
     * provided.
     *
     * @param databaseId Database ID.
     * @param collectionId Collection ID.
     * @param key Attribute Key.
     * @param required Is attribute required?
     * @param min Minimum value.
     * @param max Maximum value.
     * @param default_ Default value. Cannot be set when required.
     * @param array Is attribute an array?
     * @return appwrite::Result<appwrite::models::AttributeFloat>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::AttributeFloat> createFloatAttribute(        std::string_view databaseId,         std::string_view collectionId,         std::string_view key,         bool required,         std::optional<double> min = std::nullopt,         std::optional<double> max = std::nullopt,         std::optional<double> default_ = std::nullopt,         std::optional<bool> array = false    ) {
                std::string path_ = std::format("/databases/{}/collections/{}/attributes/float", databaseId, collectionId);
        
        nlohmann::json params = nlohmann::json::object();
        params["key"] = std::string(key);
        params["required"] = required;
        if (min.has_value()) {
            params["min"] = *min;
        }
        if (max.has_value()) {
            params["max"] = *max;
        }
        if (default_.has_value()) {
            params["default"] = *default_;
        }
        if (array.has_value()) {
            params["array"] = *array;
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::AttributeFloat>("POST", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Create a float attribute. Optionally, minimum and maximum values can be
     * provided.
     *
     * @param databaseId Database ID.
     * @param collectionId Collection ID.
     * @param key Attribute Key.
     * @param required Is attribute required?
     * @param min Minimum value.
     * @param max Maximum value.
     * @param default_ Default value. Cannot be set when required.
     * @param array Is attribute an array?
     * @return appwrite::Result<appwrite::models::AttributeFloat>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::AttributeFloat>> createFloatAttributeAsync(        std::string_view databaseId,         std::string_view collectionId,         std::string_view key,         bool required,         std::optional<double> min = std::nullopt,         std::optional<double> max = std::nullopt,         std::optional<double> default_ = std::nullopt,         std::optional<bool> array = false    ) {
                std::string path_ = std::format("/databases/{}/collections/{}/attributes/float", databaseId, collectionId);
        
        nlohmann::json params = nlohmann::json::object();
        params["key"] = std::string(key);
        params["required"] = required;
        if (min.has_value()) {
            params["min"] = *min;
        }
        if (max.has_value()) {
            params["max"] = *max;
        }
        if (default_.has_value()) {
            params["default"] = *default_;
        }
        if (array.has_value()) {
            params["array"] = *array;
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::AttributeFloat>("POST", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Update a float attribute. Changing the `default` value will not update
     * already existing documents.
     *
     * @param databaseId Database ID.
     * @param collectionId Collection ID.
     * @param key Attribute Key.
     * @param required Is attribute required?
     * @param default_ Default value. Cannot be set when required.
     * @param min Minimum value.
     * @param max Maximum value.
     * @param newKey New Attribute Key.
     * @return appwrite::Result<appwrite::models::AttributeFloat>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::AttributeFloat> updateFloatAttribute(        std::string_view databaseId,         std::string_view collectionId,         std::string_view key,         bool required,         double default_,         std::optional<double> min = std::nullopt,         std::optional<double> max = std::nullopt,         std::optional<std::string_view> newKey = std::nullopt    ) {
                std::string path_ = std::format("/databases/{}/collections/{}/attributes/float/{}", databaseId, collectionId, key);
        
        nlohmann::json params = nlohmann::json::object();
        params["required"] = required;
        if (min.has_value()) {
            params["min"] = *min;
        }
        if (max.has_value()) {
            params["max"] = *max;
        }
        params["default"] = default_;
        if (newKey.has_value()) {
            params["newKey"] = std::string(newKey.value());
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::AttributeFloat>("PATCH", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Update a float attribute. Changing the `default` value will not update
     * already existing documents.
     *
     * @param databaseId Database ID.
     * @param collectionId Collection ID.
     * @param key Attribute Key.
     * @param required Is attribute required?
     * @param default_ Default value. Cannot be set when required.
     * @param min Minimum value.
     * @param max Maximum value.
     * @param newKey New Attribute Key.
     * @return appwrite::Result<appwrite::models::AttributeFloat>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::AttributeFloat>> updateFloatAttributeAsync(        std::string_view databaseId,         std::string_view collectionId,         std::string_view key,         bool required,         double default_,         std::optional<double> min = std::nullopt,         std::optional<double> max = std::nullopt,         std::optional<std::string_view> newKey = std::nullopt    ) {
                std::string path_ = std::format("/databases/{}/collections/{}/attributes/float/{}", databaseId, collectionId, key);
        
        nlohmann::json params = nlohmann::json::object();
        params["required"] = required;
        if (min.has_value()) {
            params["min"] = *min;
        }
        if (max.has_value()) {
            params["max"] = *max;
        }
        params["default"] = default_;
        if (newKey.has_value()) {
            params["newKey"] = std::string(newKey.value());
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::AttributeFloat>("PATCH", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Create an integer attribute. Optionally, minimum and maximum values can be
     * provided.
     *
     * @param databaseId Database ID.
     * @param collectionId Collection ID.
     * @param key Attribute Key.
     * @param required Is attribute required?
     * @param min Minimum value
     * @param max Maximum value
     * @param default_ Default value. Cannot be set when attribute is required.
     * @param array Is attribute an array?
     * @return appwrite::Result<appwrite::models::AttributeInteger>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::AttributeInteger> createIntegerAttribute(        std::string_view databaseId,         std::string_view collectionId,         std::string_view key,         bool required,         std::optional<int64_t> min = std::nullopt,         std::optional<int64_t> max = std::nullopt,         std::optional<int64_t> default_ = std::nullopt,         std::optional<bool> array = false    ) {
                std::string path_ = std::format("/databases/{}/collections/{}/attributes/integer", databaseId, collectionId);
        
        nlohmann::json params = nlohmann::json::object();
        params["key"] = std::string(key);
        params["required"] = required;
        if (min.has_value()) {
            params["min"] = *min;
        }
        if (max.has_value()) {
            params["max"] = *max;
        }
        if (default_.has_value()) {
            params["default"] = *default_;
        }
        if (array.has_value()) {
            params["array"] = *array;
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::AttributeInteger>("POST", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Create an integer attribute. Optionally, minimum and maximum values can be
     * provided.
     *
     * @param databaseId Database ID.
     * @param collectionId Collection ID.
     * @param key Attribute Key.
     * @param required Is attribute required?
     * @param min Minimum value
     * @param max Maximum value
     * @param default_ Default value. Cannot be set when attribute is required.
     * @param array Is attribute an array?
     * @return appwrite::Result<appwrite::models::AttributeInteger>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::AttributeInteger>> createIntegerAttributeAsync(        std::string_view databaseId,         std::string_view collectionId,         std::string_view key,         bool required,         std::optional<int64_t> min = std::nullopt,         std::optional<int64_t> max = std::nullopt,         std::optional<int64_t> default_ = std::nullopt,         std::optional<bool> array = false    ) {
                std::string path_ = std::format("/databases/{}/collections/{}/attributes/integer", databaseId, collectionId);
        
        nlohmann::json params = nlohmann::json::object();
        params["key"] = std::string(key);
        params["required"] = required;
        if (min.has_value()) {
            params["min"] = *min;
        }
        if (max.has_value()) {
            params["max"] = *max;
        }
        if (default_.has_value()) {
            params["default"] = *default_;
        }
        if (array.has_value()) {
            params["array"] = *array;
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::AttributeInteger>("POST", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Update an integer attribute. Changing the `default` value will not update
     * already existing documents.
     *
     * @param databaseId Database ID.
     * @param collectionId Collection ID.
     * @param key Attribute Key.
     * @param required Is attribute required?
     * @param default_ Default value. Cannot be set when attribute is required.
     * @param min Minimum value
     * @param max Maximum value
     * @param newKey New Attribute Key.
     * @return appwrite::Result<appwrite::models::AttributeInteger>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::AttributeInteger> updateIntegerAttribute(        std::string_view databaseId,         std::string_view collectionId,         std::string_view key,         bool required,         int64_t default_,         std::optional<int64_t> min = std::nullopt,         std::optional<int64_t> max = std::nullopt,         std::optional<std::string_view> newKey = std::nullopt    ) {
                std::string path_ = std::format("/databases/{}/collections/{}/attributes/integer/{}", databaseId, collectionId, key);
        
        nlohmann::json params = nlohmann::json::object();
        params["required"] = required;
        if (min.has_value()) {
            params["min"] = *min;
        }
        if (max.has_value()) {
            params["max"] = *max;
        }
        params["default"] = default_;
        if (newKey.has_value()) {
            params["newKey"] = std::string(newKey.value());
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::AttributeInteger>("PATCH", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Update an integer attribute. Changing the `default` value will not update
     * already existing documents.
     *
     * @param databaseId Database ID.
     * @param collectionId Collection ID.
     * @param key Attribute Key.
     * @param required Is attribute required?
     * @param default_ Default value. Cannot be set when attribute is required.
     * @param min Minimum value
     * @param max Maximum value
     * @param newKey New Attribute Key.
     * @return appwrite::Result<appwrite::models::AttributeInteger>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::AttributeInteger>> updateIntegerAttributeAsync(        std::string_view databaseId,         std::string_view collectionId,         std::string_view key,         bool required,         int64_t default_,         std::optional<int64_t> min = std::nullopt,         std::optional<int64_t> max = std::nullopt,         std::optional<std::string_view> newKey = std::nullopt    ) {
                std::string path_ = std::format("/databases/{}/collections/{}/attributes/integer/{}", databaseId, collectionId, key);
        
        nlohmann::json params = nlohmann::json::object();
        params["required"] = required;
        if (min.has_value()) {
            params["min"] = *min;
        }
        if (max.has_value()) {
            params["max"] = *max;
        }
        params["default"] = default_;
        if (newKey.has_value()) {
            params["newKey"] = std::string(newKey.value());
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::AttributeInteger>("PATCH", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Create IP address attribute.
     *
     * @param databaseId Database ID.
     * @param collectionId Collection ID.
     * @param key Attribute Key.
     * @param required Is attribute required?
     * @param default_ Default value. Cannot be set when attribute is required.
     * @param array Is attribute an array?
     * @return appwrite::Result<appwrite::models::AttributeIp>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::AttributeIp> createIpAttribute(        std::string_view databaseId,         std::string_view collectionId,         std::string_view key,         bool required,         std::optional<std::string_view> default_ = std::nullopt,         std::optional<bool> array = false    ) {
                std::string path_ = std::format("/databases/{}/collections/{}/attributes/ip", databaseId, collectionId);
        
        nlohmann::json params = nlohmann::json::object();
        params["key"] = std::string(key);
        params["required"] = required;
        if (default_.has_value()) {
            params["default"] = std::string(default_.value());
        }
        if (array.has_value()) {
            params["array"] = *array;
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::AttributeIp>("POST", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Create IP address attribute.
     *
     * @param databaseId Database ID.
     * @param collectionId Collection ID.
     * @param key Attribute Key.
     * @param required Is attribute required?
     * @param default_ Default value. Cannot be set when attribute is required.
     * @param array Is attribute an array?
     * @return appwrite::Result<appwrite::models::AttributeIp>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::AttributeIp>> createIpAttributeAsync(        std::string_view databaseId,         std::string_view collectionId,         std::string_view key,         bool required,         std::optional<std::string_view> default_ = std::nullopt,         std::optional<bool> array = false    ) {
                std::string path_ = std::format("/databases/{}/collections/{}/attributes/ip", databaseId, collectionId);
        
        nlohmann::json params = nlohmann::json::object();
        params["key"] = std::string(key);
        params["required"] = required;
        if (default_.has_value()) {
            params["default"] = std::string(default_.value());
        }
        if (array.has_value()) {
            params["array"] = *array;
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::AttributeIp>("POST", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Update an ip attribute. Changing the `default` value will not update
     * already existing documents.
     *
     * @param databaseId Database ID.
     * @param collectionId Collection ID.
     * @param key Attribute Key.
     * @param required Is attribute required?
     * @param default_ Default value. Cannot be set when attribute is required.
     * @param newKey New Attribute Key.
     * @return appwrite::Result<appwrite::models::AttributeIp>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::AttributeIp> updateIpAttribute(        std::string_view databaseId,         std::string_view collectionId,         std::string_view key,         bool required,         std::string_view default_,         std::optional<std::string_view> newKey = std::nullopt    ) {
                std::string path_ = std::format("/databases/{}/collections/{}/attributes/ip/{}", databaseId, collectionId, key);
        
        nlohmann::json params = nlohmann::json::object();
        params["required"] = required;
        params["default"] = std::string(default_);
        if (newKey.has_value()) {
            params["newKey"] = std::string(newKey.value());
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::AttributeIp>("PATCH", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Update an ip attribute. Changing the `default` value will not update
     * already existing documents.
     *
     * @param databaseId Database ID.
     * @param collectionId Collection ID.
     * @param key Attribute Key.
     * @param required Is attribute required?
     * @param default_ Default value. Cannot be set when attribute is required.
     * @param newKey New Attribute Key.
     * @return appwrite::Result<appwrite::models::AttributeIp>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::AttributeIp>> updateIpAttributeAsync(        std::string_view databaseId,         std::string_view collectionId,         std::string_view key,         bool required,         std::string_view default_,         std::optional<std::string_view> newKey = std::nullopt    ) {
                std::string path_ = std::format("/databases/{}/collections/{}/attributes/ip/{}", databaseId, collectionId, key);
        
        nlohmann::json params = nlohmann::json::object();
        params["required"] = required;
        params["default"] = std::string(default_);
        if (newKey.has_value()) {
            params["newKey"] = std::string(newKey.value());
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::AttributeIp>("PATCH", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Create a geometric line attribute.
     *
     * @param databaseId Database ID.
     * @param collectionId Collection ID. You can create a new collection using the Database service [server integration](https://appwrite.io/docs/server/databases#databasesCreateCollection).
     * @param key Attribute Key.
     * @param required Is attribute required?
     * @param default_ Default value for attribute when not provided, two-dimensional array of coordinate pairs, [[longitude, latitude], [longitude, latitude], …], listing the vertices of the line in order. Cannot be set when attribute is required.
     * @return appwrite::Result<appwrite::models::AttributeLine>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::AttributeLine> createLineAttribute(        std::string_view databaseId,         std::string_view collectionId,         std::string_view key,         bool required,         std::optional<std::vector<std::string>> default_ = std::nullopt    ) {
                std::string path_ = std::format("/databases/{}/collections/{}/attributes/line", databaseId, collectionId);
        
        nlohmann::json params = nlohmann::json::object();
        params["key"] = std::string(key);
        params["required"] = required;
        if (default_.has_value()) {
            params["default"] = *default_;
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::AttributeLine>("POST", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Create a geometric line attribute.
     *
     * @param databaseId Database ID.
     * @param collectionId Collection ID. You can create a new collection using the Database service [server integration](https://appwrite.io/docs/server/databases#databasesCreateCollection).
     * @param key Attribute Key.
     * @param required Is attribute required?
     * @param default_ Default value for attribute when not provided, two-dimensional array of coordinate pairs, [[longitude, latitude], [longitude, latitude], …], listing the vertices of the line in order. Cannot be set when attribute is required.
     * @return appwrite::Result<appwrite::models::AttributeLine>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::AttributeLine>> createLineAttributeAsync(        std::string_view databaseId,         std::string_view collectionId,         std::string_view key,         bool required,         std::optional<std::vector<std::string>> default_ = std::nullopt    ) {
                std::string path_ = std::format("/databases/{}/collections/{}/attributes/line", databaseId, collectionId);
        
        nlohmann::json params = nlohmann::json::object();
        params["key"] = std::string(key);
        params["required"] = required;
        if (default_.has_value()) {
            params["default"] = *default_;
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::AttributeLine>("POST", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Update a line attribute. Changing the `default` value will not update
     * already existing documents.
     *
     * @param databaseId Database ID.
     * @param collectionId Collection ID. You can create a new collection using the Database service [server integration](https://appwrite.io/docs/server/databases#createCollection).
     * @param key Attribute Key.
     * @param required Is attribute required?
     * @param default_ Default value for attribute when not provided, two-dimensional array of coordinate pairs, [[longitude, latitude], [longitude, latitude], …], listing the vertices of the line in order. Cannot be set when attribute is required.
     * @param newKey New attribute key.
     * @return appwrite::Result<appwrite::models::AttributeLine>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::AttributeLine> updateLineAttribute(        std::string_view databaseId,         std::string_view collectionId,         std::string_view key,         bool required,         std::optional<std::vector<std::string>> default_ = std::nullopt,         std::optional<std::string_view> newKey = std::nullopt    ) {
                std::string path_ = std::format("/databases/{}/collections/{}/attributes/line/{}", databaseId, collectionId, key);
        
        nlohmann::json params = nlohmann::json::object();
        params["required"] = required;
        if (default_.has_value()) {
            params["default"] = *default_;
        }
        if (newKey.has_value()) {
            params["newKey"] = std::string(newKey.value());
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::AttributeLine>("PATCH", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Update a line attribute. Changing the `default` value will not update
     * already existing documents.
     *
     * @param databaseId Database ID.
     * @param collectionId Collection ID. You can create a new collection using the Database service [server integration](https://appwrite.io/docs/server/databases#createCollection).
     * @param key Attribute Key.
     * @param required Is attribute required?
     * @param default_ Default value for attribute when not provided, two-dimensional array of coordinate pairs, [[longitude, latitude], [longitude, latitude], …], listing the vertices of the line in order. Cannot be set when attribute is required.
     * @param newKey New attribute key.
     * @return appwrite::Result<appwrite::models::AttributeLine>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::AttributeLine>> updateLineAttributeAsync(        std::string_view databaseId,         std::string_view collectionId,         std::string_view key,         bool required,         std::optional<std::vector<std::string>> default_ = std::nullopt,         std::optional<std::string_view> newKey = std::nullopt    ) {
                std::string path_ = std::format("/databases/{}/collections/{}/attributes/line/{}", databaseId, collectionId, key);
        
        nlohmann::json params = nlohmann::json::object();
        params["required"] = required;
        if (default_.has_value()) {
            params["default"] = *default_;
        }
        if (newKey.has_value()) {
            params["newKey"] = std::string(newKey.value());
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::AttributeLine>("PATCH", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Create a longtext attribute.
     *
     * @param databaseId Database ID.
     * @param collectionId Collection ID. You can create a new collection using the Database service [server integration](https://appwrite.io/docs/server/databases#databasesCreateCollection).
     * @param key Attribute Key.
     * @param required Is attribute required?
     * @param default_ Default value for attribute when not provided. Cannot be set when attribute is required.
     * @param array Is attribute an array?
     * @param encrypt Toggle encryption for the attribute. Encryption enhances security by not storing any plain text values in the database. However, encrypted attributes cannot be queried.
     * @return appwrite::Result<appwrite::models::AttributeLongtext>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::AttributeLongtext> createLongtextAttribute(        std::string_view databaseId,         std::string_view collectionId,         std::string_view key,         bool required,         std::optional<std::string_view> default_ = std::nullopt,         std::optional<bool> array = false,         std::optional<bool> encrypt = false    ) {
                std::string path_ = std::format("/databases/{}/collections/{}/attributes/longtext", databaseId, collectionId);
        
        nlohmann::json params = nlohmann::json::object();
        params["key"] = std::string(key);
        params["required"] = required;
        if (default_.has_value()) {
            params["default"] = std::string(default_.value());
        }
        if (array.has_value()) {
            params["array"] = *array;
        }
        if (encrypt.has_value()) {
            params["encrypt"] = *encrypt;
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::AttributeLongtext>("POST", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Create a longtext attribute.
     *
     * @param databaseId Database ID.
     * @param collectionId Collection ID. You can create a new collection using the Database service [server integration](https://appwrite.io/docs/server/databases#databasesCreateCollection).
     * @param key Attribute Key.
     * @param required Is attribute required?
     * @param default_ Default value for attribute when not provided. Cannot be set when attribute is required.
     * @param array Is attribute an array?
     * @param encrypt Toggle encryption for the attribute. Encryption enhances security by not storing any plain text values in the database. However, encrypted attributes cannot be queried.
     * @return appwrite::Result<appwrite::models::AttributeLongtext>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::AttributeLongtext>> createLongtextAttributeAsync(        std::string_view databaseId,         std::string_view collectionId,         std::string_view key,         bool required,         std::optional<std::string_view> default_ = std::nullopt,         std::optional<bool> array = false,         std::optional<bool> encrypt = false    ) {
                std::string path_ = std::format("/databases/{}/collections/{}/attributes/longtext", databaseId, collectionId);
        
        nlohmann::json params = nlohmann::json::object();
        params["key"] = std::string(key);
        params["required"] = required;
        if (default_.has_value()) {
            params["default"] = std::string(default_.value());
        }
        if (array.has_value()) {
            params["array"] = *array;
        }
        if (encrypt.has_value()) {
            params["encrypt"] = *encrypt;
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::AttributeLongtext>("POST", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Update a longtext attribute. Changing the `default` value will not update
     * already existing documents.
     *
     * @param databaseId Database ID.
     * @param collectionId Collection ID. You can create a new collection using the Database service [server integration](https://appwrite.io/docs/server/databases#databasesCreateCollection).
     * @param key Attribute Key.
     * @param required Is attribute required?
     * @param default_ Default value for attribute when not provided. Cannot be set when attribute is required.
     * @param newKey New Attribute Key.
     * @return appwrite::Result<appwrite::models::AttributeLongtext>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::AttributeLongtext> updateLongtextAttribute(        std::string_view databaseId,         std::string_view collectionId,         std::string_view key,         bool required,         std::string_view default_,         std::optional<std::string_view> newKey = std::nullopt    ) {
                std::string path_ = std::format("/databases/{}/collections/{}/attributes/longtext/{}", databaseId, collectionId, key);
        
        nlohmann::json params = nlohmann::json::object();
        params["required"] = required;
        params["default"] = std::string(default_);
        if (newKey.has_value()) {
            params["newKey"] = std::string(newKey.value());
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::AttributeLongtext>("PATCH", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Update a longtext attribute. Changing the `default` value will not update
     * already existing documents.
     *
     * @param databaseId Database ID.
     * @param collectionId Collection ID. You can create a new collection using the Database service [server integration](https://appwrite.io/docs/server/databases#databasesCreateCollection).
     * @param key Attribute Key.
     * @param required Is attribute required?
     * @param default_ Default value for attribute when not provided. Cannot be set when attribute is required.
     * @param newKey New Attribute Key.
     * @return appwrite::Result<appwrite::models::AttributeLongtext>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::AttributeLongtext>> updateLongtextAttributeAsync(        std::string_view databaseId,         std::string_view collectionId,         std::string_view key,         bool required,         std::string_view default_,         std::optional<std::string_view> newKey = std::nullopt    ) {
                std::string path_ = std::format("/databases/{}/collections/{}/attributes/longtext/{}", databaseId, collectionId, key);
        
        nlohmann::json params = nlohmann::json::object();
        params["required"] = required;
        params["default"] = std::string(default_);
        if (newKey.has_value()) {
            params["newKey"] = std::string(newKey.value());
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::AttributeLongtext>("PATCH", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Create a mediumtext attribute.
     *
     * @param databaseId Database ID.
     * @param collectionId Collection ID. You can create a new collection using the Database service [server integration](https://appwrite.io/docs/server/databases#databasesCreateCollection).
     * @param key Attribute Key.
     * @param required Is attribute required?
     * @param default_ Default value for attribute when not provided. Cannot be set when attribute is required.
     * @param array Is attribute an array?
     * @param encrypt Toggle encryption for the attribute. Encryption enhances security by not storing any plain text values in the database. However, encrypted attributes cannot be queried.
     * @return appwrite::Result<appwrite::models::AttributeMediumtext>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::AttributeMediumtext> createMediumtextAttribute(        std::string_view databaseId,         std::string_view collectionId,         std::string_view key,         bool required,         std::optional<std::string_view> default_ = std::nullopt,         std::optional<bool> array = false,         std::optional<bool> encrypt = false    ) {
                std::string path_ = std::format("/databases/{}/collections/{}/attributes/mediumtext", databaseId, collectionId);
        
        nlohmann::json params = nlohmann::json::object();
        params["key"] = std::string(key);
        params["required"] = required;
        if (default_.has_value()) {
            params["default"] = std::string(default_.value());
        }
        if (array.has_value()) {
            params["array"] = *array;
        }
        if (encrypt.has_value()) {
            params["encrypt"] = *encrypt;
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::AttributeMediumtext>("POST", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Create a mediumtext attribute.
     *
     * @param databaseId Database ID.
     * @param collectionId Collection ID. You can create a new collection using the Database service [server integration](https://appwrite.io/docs/server/databases#databasesCreateCollection).
     * @param key Attribute Key.
     * @param required Is attribute required?
     * @param default_ Default value for attribute when not provided. Cannot be set when attribute is required.
     * @param array Is attribute an array?
     * @param encrypt Toggle encryption for the attribute. Encryption enhances security by not storing any plain text values in the database. However, encrypted attributes cannot be queried.
     * @return appwrite::Result<appwrite::models::AttributeMediumtext>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::AttributeMediumtext>> createMediumtextAttributeAsync(        std::string_view databaseId,         std::string_view collectionId,         std::string_view key,         bool required,         std::optional<std::string_view> default_ = std::nullopt,         std::optional<bool> array = false,         std::optional<bool> encrypt = false    ) {
                std::string path_ = std::format("/databases/{}/collections/{}/attributes/mediumtext", databaseId, collectionId);
        
        nlohmann::json params = nlohmann::json::object();
        params["key"] = std::string(key);
        params["required"] = required;
        if (default_.has_value()) {
            params["default"] = std::string(default_.value());
        }
        if (array.has_value()) {
            params["array"] = *array;
        }
        if (encrypt.has_value()) {
            params["encrypt"] = *encrypt;
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::AttributeMediumtext>("POST", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Update a mediumtext attribute. Changing the `default` value will not update
     * already existing documents.
     *
     * @param databaseId Database ID.
     * @param collectionId Collection ID. You can create a new collection using the Database service [server integration](https://appwrite.io/docs/server/databases#databasesCreateCollection).
     * @param key Attribute Key.
     * @param required Is attribute required?
     * @param default_ Default value for attribute when not provided. Cannot be set when attribute is required.
     * @param newKey New Attribute Key.
     * @return appwrite::Result<appwrite::models::AttributeMediumtext>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::AttributeMediumtext> updateMediumtextAttribute(        std::string_view databaseId,         std::string_view collectionId,         std::string_view key,         bool required,         std::string_view default_,         std::optional<std::string_view> newKey = std::nullopt    ) {
                std::string path_ = std::format("/databases/{}/collections/{}/attributes/mediumtext/{}", databaseId, collectionId, key);
        
        nlohmann::json params = nlohmann::json::object();
        params["required"] = required;
        params["default"] = std::string(default_);
        if (newKey.has_value()) {
            params["newKey"] = std::string(newKey.value());
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::AttributeMediumtext>("PATCH", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Update a mediumtext attribute. Changing the `default` value will not update
     * already existing documents.
     *
     * @param databaseId Database ID.
     * @param collectionId Collection ID. You can create a new collection using the Database service [server integration](https://appwrite.io/docs/server/databases#databasesCreateCollection).
     * @param key Attribute Key.
     * @param required Is attribute required?
     * @param default_ Default value for attribute when not provided. Cannot be set when attribute is required.
     * @param newKey New Attribute Key.
     * @return appwrite::Result<appwrite::models::AttributeMediumtext>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::AttributeMediumtext>> updateMediumtextAttributeAsync(        std::string_view databaseId,         std::string_view collectionId,         std::string_view key,         bool required,         std::string_view default_,         std::optional<std::string_view> newKey = std::nullopt    ) {
                std::string path_ = std::format("/databases/{}/collections/{}/attributes/mediumtext/{}", databaseId, collectionId, key);
        
        nlohmann::json params = nlohmann::json::object();
        params["required"] = required;
        params["default"] = std::string(default_);
        if (newKey.has_value()) {
            params["newKey"] = std::string(newKey.value());
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::AttributeMediumtext>("PATCH", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Create a geometric point attribute.
     *
     * @param databaseId Database ID.
     * @param collectionId Collection ID. You can create a new collection using the Database service [server integration](https://appwrite.io/docs/server/databases#databasesCreateCollection).
     * @param key Attribute Key.
     * @param required Is attribute required?
     * @param default_ Default value for attribute when not provided, array of two numbers [longitude, latitude], representing a single coordinate. Cannot be set when attribute is required.
     * @return appwrite::Result<appwrite::models::AttributePoint>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::AttributePoint> createPointAttribute(        std::string_view databaseId,         std::string_view collectionId,         std::string_view key,         bool required,         std::optional<std::vector<std::string>> default_ = std::nullopt    ) {
                std::string path_ = std::format("/databases/{}/collections/{}/attributes/point", databaseId, collectionId);
        
        nlohmann::json params = nlohmann::json::object();
        params["key"] = std::string(key);
        params["required"] = required;
        if (default_.has_value()) {
            params["default"] = *default_;
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::AttributePoint>("POST", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Create a geometric point attribute.
     *
     * @param databaseId Database ID.
     * @param collectionId Collection ID. You can create a new collection using the Database service [server integration](https://appwrite.io/docs/server/databases#databasesCreateCollection).
     * @param key Attribute Key.
     * @param required Is attribute required?
     * @param default_ Default value for attribute when not provided, array of two numbers [longitude, latitude], representing a single coordinate. Cannot be set when attribute is required.
     * @return appwrite::Result<appwrite::models::AttributePoint>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::AttributePoint>> createPointAttributeAsync(        std::string_view databaseId,         std::string_view collectionId,         std::string_view key,         bool required,         std::optional<std::vector<std::string>> default_ = std::nullopt    ) {
                std::string path_ = std::format("/databases/{}/collections/{}/attributes/point", databaseId, collectionId);
        
        nlohmann::json params = nlohmann::json::object();
        params["key"] = std::string(key);
        params["required"] = required;
        if (default_.has_value()) {
            params["default"] = *default_;
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::AttributePoint>("POST", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Update a point attribute. Changing the `default` value will not update
     * already existing documents.
     *
     * @param databaseId Database ID.
     * @param collectionId Collection ID. You can create a new collection using the Database service [server integration](https://appwrite.io/docs/server/databases#createCollection).
     * @param key Attribute Key.
     * @param required Is attribute required?
     * @param default_ Default value for attribute when not provided, array of two numbers [longitude, latitude], representing a single coordinate. Cannot be set when attribute is required.
     * @param newKey New attribute key.
     * @return appwrite::Result<appwrite::models::AttributePoint>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::AttributePoint> updatePointAttribute(        std::string_view databaseId,         std::string_view collectionId,         std::string_view key,         bool required,         std::optional<std::vector<std::string>> default_ = std::nullopt,         std::optional<std::string_view> newKey = std::nullopt    ) {
                std::string path_ = std::format("/databases/{}/collections/{}/attributes/point/{}", databaseId, collectionId, key);
        
        nlohmann::json params = nlohmann::json::object();
        params["required"] = required;
        if (default_.has_value()) {
            params["default"] = *default_;
        }
        if (newKey.has_value()) {
            params["newKey"] = std::string(newKey.value());
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::AttributePoint>("PATCH", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Update a point attribute. Changing the `default` value will not update
     * already existing documents.
     *
     * @param databaseId Database ID.
     * @param collectionId Collection ID. You can create a new collection using the Database service [server integration](https://appwrite.io/docs/server/databases#createCollection).
     * @param key Attribute Key.
     * @param required Is attribute required?
     * @param default_ Default value for attribute when not provided, array of two numbers [longitude, latitude], representing a single coordinate. Cannot be set when attribute is required.
     * @param newKey New attribute key.
     * @return appwrite::Result<appwrite::models::AttributePoint>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::AttributePoint>> updatePointAttributeAsync(        std::string_view databaseId,         std::string_view collectionId,         std::string_view key,         bool required,         std::optional<std::vector<std::string>> default_ = std::nullopt,         std::optional<std::string_view> newKey = std::nullopt    ) {
                std::string path_ = std::format("/databases/{}/collections/{}/attributes/point/{}", databaseId, collectionId, key);
        
        nlohmann::json params = nlohmann::json::object();
        params["required"] = required;
        if (default_.has_value()) {
            params["default"] = *default_;
        }
        if (newKey.has_value()) {
            params["newKey"] = std::string(newKey.value());
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::AttributePoint>("PATCH", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Create a geometric polygon attribute.
     *
     * @param databaseId Database ID.
     * @param collectionId Collection ID. You can create a new collection using the Database service [server integration](https://appwrite.io/docs/server/databases#databasesCreateCollection).
     * @param key Attribute Key.
     * @param required Is attribute required?
     * @param default_ Default value for attribute when not provided, three-dimensional array where the outer array holds one or more linear rings, [[[longitude, latitude], …], …], the first ring is the exterior boundary, any additional rings are interior holes, and each ring must start and end with the same coordinate pair. Cannot be set when attribute is required.
     * @return appwrite::Result<appwrite::models::AttributePolygon>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::AttributePolygon> createPolygonAttribute(        std::string_view databaseId,         std::string_view collectionId,         std::string_view key,         bool required,         std::optional<std::vector<std::string>> default_ = std::nullopt    ) {
                std::string path_ = std::format("/databases/{}/collections/{}/attributes/polygon", databaseId, collectionId);
        
        nlohmann::json params = nlohmann::json::object();
        params["key"] = std::string(key);
        params["required"] = required;
        if (default_.has_value()) {
            params["default"] = *default_;
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::AttributePolygon>("POST", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Create a geometric polygon attribute.
     *
     * @param databaseId Database ID.
     * @param collectionId Collection ID. You can create a new collection using the Database service [server integration](https://appwrite.io/docs/server/databases#databasesCreateCollection).
     * @param key Attribute Key.
     * @param required Is attribute required?
     * @param default_ Default value for attribute when not provided, three-dimensional array where the outer array holds one or more linear rings, [[[longitude, latitude], …], …], the first ring is the exterior boundary, any additional rings are interior holes, and each ring must start and end with the same coordinate pair. Cannot be set when attribute is required.
     * @return appwrite::Result<appwrite::models::AttributePolygon>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::AttributePolygon>> createPolygonAttributeAsync(        std::string_view databaseId,         std::string_view collectionId,         std::string_view key,         bool required,         std::optional<std::vector<std::string>> default_ = std::nullopt    ) {
                std::string path_ = std::format("/databases/{}/collections/{}/attributes/polygon", databaseId, collectionId);
        
        nlohmann::json params = nlohmann::json::object();
        params["key"] = std::string(key);
        params["required"] = required;
        if (default_.has_value()) {
            params["default"] = *default_;
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::AttributePolygon>("POST", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Update a polygon attribute. Changing the `default` value will not update
     * already existing documents.
     *
     * @param databaseId Database ID.
     * @param collectionId Collection ID. You can create a new collection using the Database service [server integration](https://appwrite.io/docs/server/databases#createCollection).
     * @param key Attribute Key.
     * @param required Is attribute required?
     * @param default_ Default value for attribute when not provided, three-dimensional array where the outer array holds one or more linear rings, [[[longitude, latitude], …], …], the first ring is the exterior boundary, any additional rings are interior holes, and each ring must start and end with the same coordinate pair. Cannot be set when attribute is required.
     * @param newKey New attribute key.
     * @return appwrite::Result<appwrite::models::AttributePolygon>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::AttributePolygon> updatePolygonAttribute(        std::string_view databaseId,         std::string_view collectionId,         std::string_view key,         bool required,         std::optional<std::vector<std::string>> default_ = std::nullopt,         std::optional<std::string_view> newKey = std::nullopt    ) {
                std::string path_ = std::format("/databases/{}/collections/{}/attributes/polygon/{}", databaseId, collectionId, key);
        
        nlohmann::json params = nlohmann::json::object();
        params["required"] = required;
        if (default_.has_value()) {
            params["default"] = *default_;
        }
        if (newKey.has_value()) {
            params["newKey"] = std::string(newKey.value());
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::AttributePolygon>("PATCH", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Update a polygon attribute. Changing the `default` value will not update
     * already existing documents.
     *
     * @param databaseId Database ID.
     * @param collectionId Collection ID. You can create a new collection using the Database service [server integration](https://appwrite.io/docs/server/databases#createCollection).
     * @param key Attribute Key.
     * @param required Is attribute required?
     * @param default_ Default value for attribute when not provided, three-dimensional array where the outer array holds one or more linear rings, [[[longitude, latitude], …], …], the first ring is the exterior boundary, any additional rings are interior holes, and each ring must start and end with the same coordinate pair. Cannot be set when attribute is required.
     * @param newKey New attribute key.
     * @return appwrite::Result<appwrite::models::AttributePolygon>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::AttributePolygon>> updatePolygonAttributeAsync(        std::string_view databaseId,         std::string_view collectionId,         std::string_view key,         bool required,         std::optional<std::vector<std::string>> default_ = std::nullopt,         std::optional<std::string_view> newKey = std::nullopt    ) {
                std::string path_ = std::format("/databases/{}/collections/{}/attributes/polygon/{}", databaseId, collectionId, key);
        
        nlohmann::json params = nlohmann::json::object();
        params["required"] = required;
        if (default_.has_value()) {
            params["default"] = *default_;
        }
        if (newKey.has_value()) {
            params["newKey"] = std::string(newKey.value());
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::AttributePolygon>("PATCH", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Create relationship attribute. [Learn more about relationship
     * attributes](https://appwrite.io/docs/databases-relationships#relationship-attributes).
     *
     * @param databaseId Database ID.
     * @param collectionId Collection ID.
     * @param relatedCollectionId Related Collection ID.
     * @param type Relation type
     * @param twoWay Is Two Way?
     * @param key Attribute Key.
     * @param twoWayKey Two Way Attribute Key.
     * @param onDelete Constraints option
     * @return appwrite::Result<appwrite::models::AttributeRelationship>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::AttributeRelationship> createRelationshipAttribute(        std::string_view databaseId,         std::string_view collectionId,         std::string_view relatedCollectionId,         appwrite::enums::RelationshipType type,         std::optional<bool> twoWay = false,         std::optional<std::string_view> key = std::nullopt,         std::optional<std::string_view> twoWayKey = std::nullopt,         std::optional<appwrite::enums::RelationMutate> onDelete = appwrite::enums::RelationMutate::RESTRICT    ) {
                std::string path_ = std::format("/databases/{}/collections/{}/attributes/relationship", databaseId, collectionId);
        
        nlohmann::json params = nlohmann::json::object();
        params["relatedCollectionId"] = std::string(relatedCollectionId);
        params["type"] = type;
        if (twoWay.has_value()) {
            params["twoWay"] = *twoWay;
        }
        if (key.has_value()) {
            params["key"] = std::string(key.value());
        }
        if (twoWayKey.has_value()) {
            params["twoWayKey"] = std::string(twoWayKey.value());
        }
        if (onDelete.has_value()) {
            params["onDelete"] = *onDelete;
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::AttributeRelationship>("POST", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Create relationship attribute. [Learn more about relationship
     * attributes](https://appwrite.io/docs/databases-relationships#relationship-attributes).
     *
     * @param databaseId Database ID.
     * @param collectionId Collection ID.
     * @param relatedCollectionId Related Collection ID.
     * @param type Relation type
     * @param twoWay Is Two Way?
     * @param key Attribute Key.
     * @param twoWayKey Two Way Attribute Key.
     * @param onDelete Constraints option
     * @return appwrite::Result<appwrite::models::AttributeRelationship>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::AttributeRelationship>> createRelationshipAttributeAsync(        std::string_view databaseId,         std::string_view collectionId,         std::string_view relatedCollectionId,         appwrite::enums::RelationshipType type,         std::optional<bool> twoWay = false,         std::optional<std::string_view> key = std::nullopt,         std::optional<std::string_view> twoWayKey = std::nullopt,         std::optional<appwrite::enums::RelationMutate> onDelete = appwrite::enums::RelationMutate::RESTRICT    ) {
                std::string path_ = std::format("/databases/{}/collections/{}/attributes/relationship", databaseId, collectionId);
        
        nlohmann::json params = nlohmann::json::object();
        params["relatedCollectionId"] = std::string(relatedCollectionId);
        params["type"] = type;
        if (twoWay.has_value()) {
            params["twoWay"] = *twoWay;
        }
        if (key.has_value()) {
            params["key"] = std::string(key.value());
        }
        if (twoWayKey.has_value()) {
            params["twoWayKey"] = std::string(twoWayKey.value());
        }
        if (onDelete.has_value()) {
            params["onDelete"] = *onDelete;
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::AttributeRelationship>("POST", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Update relationship attribute. [Learn more about relationship
     * attributes](https://appwrite.io/docs/databases-relationships#relationship-attributes).
     *
     * @param databaseId Database ID.
     * @param collectionId Collection ID.
     * @param key Attribute Key.
     * @param onDelete Constraints option
     * @param newKey New Attribute Key.
     * @return appwrite::Result<appwrite::models::AttributeRelationship>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::AttributeRelationship> updateRelationshipAttribute(        std::string_view databaseId,         std::string_view collectionId,         std::string_view key,         std::optional<appwrite::enums::RelationMutate> onDelete = std::nullopt,         std::optional<std::string_view> newKey = std::nullopt    ) {
                std::string path_ = std::format("/databases/{}/collections/{}/attributes/relationship/{}", databaseId, collectionId, key);
        
        nlohmann::json params = nlohmann::json::object();
        if (onDelete.has_value()) {
            params["onDelete"] = *onDelete;
        }
        if (newKey.has_value()) {
            params["newKey"] = std::string(newKey.value());
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::AttributeRelationship>("PATCH", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Update relationship attribute. [Learn more about relationship
     * attributes](https://appwrite.io/docs/databases-relationships#relationship-attributes).
     *
     * @param databaseId Database ID.
     * @param collectionId Collection ID.
     * @param key Attribute Key.
     * @param onDelete Constraints option
     * @param newKey New Attribute Key.
     * @return appwrite::Result<appwrite::models::AttributeRelationship>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::AttributeRelationship>> updateRelationshipAttributeAsync(        std::string_view databaseId,         std::string_view collectionId,         std::string_view key,         std::optional<appwrite::enums::RelationMutate> onDelete = std::nullopt,         std::optional<std::string_view> newKey = std::nullopt    ) {
                std::string path_ = std::format("/databases/{}/collections/{}/attributes/relationship/{}", databaseId, collectionId, key);
        
        nlohmann::json params = nlohmann::json::object();
        if (onDelete.has_value()) {
            params["onDelete"] = *onDelete;
        }
        if (newKey.has_value()) {
            params["newKey"] = std::string(newKey.value());
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::AttributeRelationship>("PATCH", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Create a string attribute.
     *
     * @param databaseId Database ID.
     * @param collectionId Collection ID. You can create a new table using the Database service [server integration](https://appwrite.io/docs/server/databases#databasesCreateCollection).
     * @param key Attribute Key.
     * @param size Attribute size for text attributes, in number of characters.
     * @param required Is attribute required?
     * @param default_ Default value for attribute when not provided. Cannot be set when attribute is required.
     * @param array Is attribute an array?
     * @param encrypt Toggle encryption for the attribute. Encryption enhances security by not storing any plain text values in the database. However, encrypted attributes cannot be queried.
     * @return appwrite::Result<appwrite::models::AttributeString>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::AttributeString> createStringAttribute(        std::string_view databaseId,         std::string_view collectionId,         std::string_view key,         int64_t size,         bool required,         std::optional<std::string_view> default_ = std::nullopt,         std::optional<bool> array = false,         std::optional<bool> encrypt = false    ) {
                std::string path_ = std::format("/databases/{}/collections/{}/attributes/string", databaseId, collectionId);
        
        nlohmann::json params = nlohmann::json::object();
        params["key"] = std::string(key);
        params["size"] = size;
        params["required"] = required;
        if (default_.has_value()) {
            params["default"] = std::string(default_.value());
        }
        if (array.has_value()) {
            params["array"] = *array;
        }
        if (encrypt.has_value()) {
            params["encrypt"] = *encrypt;
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::AttributeString>("POST", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Create a string attribute.
     *
     * @param databaseId Database ID.
     * @param collectionId Collection ID. You can create a new table using the Database service [server integration](https://appwrite.io/docs/server/databases#databasesCreateCollection).
     * @param key Attribute Key.
     * @param size Attribute size for text attributes, in number of characters.
     * @param required Is attribute required?
     * @param default_ Default value for attribute when not provided. Cannot be set when attribute is required.
     * @param array Is attribute an array?
     * @param encrypt Toggle encryption for the attribute. Encryption enhances security by not storing any plain text values in the database. However, encrypted attributes cannot be queried.
     * @return appwrite::Result<appwrite::models::AttributeString>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::AttributeString>> createStringAttributeAsync(        std::string_view databaseId,         std::string_view collectionId,         std::string_view key,         int64_t size,         bool required,         std::optional<std::string_view> default_ = std::nullopt,         std::optional<bool> array = false,         std::optional<bool> encrypt = false    ) {
                std::string path_ = std::format("/databases/{}/collections/{}/attributes/string", databaseId, collectionId);
        
        nlohmann::json params = nlohmann::json::object();
        params["key"] = std::string(key);
        params["size"] = size;
        params["required"] = required;
        if (default_.has_value()) {
            params["default"] = std::string(default_.value());
        }
        if (array.has_value()) {
            params["array"] = *array;
        }
        if (encrypt.has_value()) {
            params["encrypt"] = *encrypt;
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::AttributeString>("POST", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Update a string attribute. Changing the `default` value will not update
     * already existing documents.
     *
     * @param databaseId Database ID.
     * @param collectionId Collection ID. You can create a new table using the Database service [server integration](https://appwrite.io/docs/server/databases#databasesCreateCollection).
     * @param key Attribute Key.
     * @param required Is attribute required?
     * @param default_ Default value for attribute when not provided. Cannot be set when attribute is required.
     * @param size Maximum size of the string attribute.
     * @param newKey New Attribute Key.
     * @return appwrite::Result<appwrite::models::AttributeString>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::AttributeString> updateStringAttribute(        std::string_view databaseId,         std::string_view collectionId,         std::string_view key,         bool required,         std::string_view default_,         std::optional<int64_t> size = std::nullopt,         std::optional<std::string_view> newKey = std::nullopt    ) {
                std::string path_ = std::format("/databases/{}/collections/{}/attributes/string/{}", databaseId, collectionId, key);
        
        nlohmann::json params = nlohmann::json::object();
        params["required"] = required;
        params["default"] = std::string(default_);
        if (size.has_value()) {
            params["size"] = *size;
        }
        if (newKey.has_value()) {
            params["newKey"] = std::string(newKey.value());
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::AttributeString>("PATCH", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Update a string attribute. Changing the `default` value will not update
     * already existing documents.
     *
     * @param databaseId Database ID.
     * @param collectionId Collection ID. You can create a new table using the Database service [server integration](https://appwrite.io/docs/server/databases#databasesCreateCollection).
     * @param key Attribute Key.
     * @param required Is attribute required?
     * @param default_ Default value for attribute when not provided. Cannot be set when attribute is required.
     * @param size Maximum size of the string attribute.
     * @param newKey New Attribute Key.
     * @return appwrite::Result<appwrite::models::AttributeString>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::AttributeString>> updateStringAttributeAsync(        std::string_view databaseId,         std::string_view collectionId,         std::string_view key,         bool required,         std::string_view default_,         std::optional<int64_t> size = std::nullopt,         std::optional<std::string_view> newKey = std::nullopt    ) {
                std::string path_ = std::format("/databases/{}/collections/{}/attributes/string/{}", databaseId, collectionId, key);
        
        nlohmann::json params = nlohmann::json::object();
        params["required"] = required;
        params["default"] = std::string(default_);
        if (size.has_value()) {
            params["size"] = *size;
        }
        if (newKey.has_value()) {
            params["newKey"] = std::string(newKey.value());
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::AttributeString>("PATCH", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Create a text attribute.
     *
     * @param databaseId Database ID.
     * @param collectionId Collection ID. You can create a new collection using the Database service [server integration](https://appwrite.io/docs/server/databases#databasesCreateCollection).
     * @param key Attribute Key.
     * @param required Is attribute required?
     * @param default_ Default value for attribute when not provided. Cannot be set when attribute is required.
     * @param array Is attribute an array?
     * @param encrypt Toggle encryption for the attribute. Encryption enhances security by not storing any plain text values in the database. However, encrypted attributes cannot be queried.
     * @return appwrite::Result<appwrite::models::AttributeText>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::AttributeText> createTextAttribute(        std::string_view databaseId,         std::string_view collectionId,         std::string_view key,         bool required,         std::optional<std::string_view> default_ = std::nullopt,         std::optional<bool> array = false,         std::optional<bool> encrypt = false    ) {
                std::string path_ = std::format("/databases/{}/collections/{}/attributes/text", databaseId, collectionId);
        
        nlohmann::json params = nlohmann::json::object();
        params["key"] = std::string(key);
        params["required"] = required;
        if (default_.has_value()) {
            params["default"] = std::string(default_.value());
        }
        if (array.has_value()) {
            params["array"] = *array;
        }
        if (encrypt.has_value()) {
            params["encrypt"] = *encrypt;
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::AttributeText>("POST", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Create a text attribute.
     *
     * @param databaseId Database ID.
     * @param collectionId Collection ID. You can create a new collection using the Database service [server integration](https://appwrite.io/docs/server/databases#databasesCreateCollection).
     * @param key Attribute Key.
     * @param required Is attribute required?
     * @param default_ Default value for attribute when not provided. Cannot be set when attribute is required.
     * @param array Is attribute an array?
     * @param encrypt Toggle encryption for the attribute. Encryption enhances security by not storing any plain text values in the database. However, encrypted attributes cannot be queried.
     * @return appwrite::Result<appwrite::models::AttributeText>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::AttributeText>> createTextAttributeAsync(        std::string_view databaseId,         std::string_view collectionId,         std::string_view key,         bool required,         std::optional<std::string_view> default_ = std::nullopt,         std::optional<bool> array = false,         std::optional<bool> encrypt = false    ) {
                std::string path_ = std::format("/databases/{}/collections/{}/attributes/text", databaseId, collectionId);
        
        nlohmann::json params = nlohmann::json::object();
        params["key"] = std::string(key);
        params["required"] = required;
        if (default_.has_value()) {
            params["default"] = std::string(default_.value());
        }
        if (array.has_value()) {
            params["array"] = *array;
        }
        if (encrypt.has_value()) {
            params["encrypt"] = *encrypt;
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::AttributeText>("POST", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Update a text attribute. Changing the `default` value will not update
     * already existing documents.
     *
     * @param databaseId Database ID.
     * @param collectionId Collection ID. You can create a new collection using the Database service [server integration](https://appwrite.io/docs/server/databases#databasesCreateCollection).
     * @param key Attribute Key.
     * @param required Is attribute required?
     * @param default_ Default value for attribute when not provided. Cannot be set when attribute is required.
     * @param newKey New Attribute Key.
     * @return appwrite::Result<appwrite::models::AttributeText>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::AttributeText> updateTextAttribute(        std::string_view databaseId,         std::string_view collectionId,         std::string_view key,         bool required,         std::string_view default_,         std::optional<std::string_view> newKey = std::nullopt    ) {
                std::string path_ = std::format("/databases/{}/collections/{}/attributes/text/{}", databaseId, collectionId, key);
        
        nlohmann::json params = nlohmann::json::object();
        params["required"] = required;
        params["default"] = std::string(default_);
        if (newKey.has_value()) {
            params["newKey"] = std::string(newKey.value());
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::AttributeText>("PATCH", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Update a text attribute. Changing the `default` value will not update
     * already existing documents.
     *
     * @param databaseId Database ID.
     * @param collectionId Collection ID. You can create a new collection using the Database service [server integration](https://appwrite.io/docs/server/databases#databasesCreateCollection).
     * @param key Attribute Key.
     * @param required Is attribute required?
     * @param default_ Default value for attribute when not provided. Cannot be set when attribute is required.
     * @param newKey New Attribute Key.
     * @return appwrite::Result<appwrite::models::AttributeText>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::AttributeText>> updateTextAttributeAsync(        std::string_view databaseId,         std::string_view collectionId,         std::string_view key,         bool required,         std::string_view default_,         std::optional<std::string_view> newKey = std::nullopt    ) {
                std::string path_ = std::format("/databases/{}/collections/{}/attributes/text/{}", databaseId, collectionId, key);
        
        nlohmann::json params = nlohmann::json::object();
        params["required"] = required;
        params["default"] = std::string(default_);
        if (newKey.has_value()) {
            params["newKey"] = std::string(newKey.value());
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::AttributeText>("PATCH", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Create a URL attribute.
     *
     * @param databaseId Database ID.
     * @param collectionId Collection ID.
     * @param key Attribute Key.
     * @param required Is attribute required?
     * @param default_ Default value for attribute when not provided. Cannot be set when attribute is required.
     * @param array Is attribute an array?
     * @return appwrite::Result<appwrite::models::AttributeUrl>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::AttributeUrl> createUrlAttribute(        std::string_view databaseId,         std::string_view collectionId,         std::string_view key,         bool required,         std::optional<std::string_view> default_ = std::nullopt,         std::optional<bool> array = false    ) {
                std::string path_ = std::format("/databases/{}/collections/{}/attributes/url", databaseId, collectionId);
        
        nlohmann::json params = nlohmann::json::object();
        params["key"] = std::string(key);
        params["required"] = required;
        if (default_.has_value()) {
            params["default"] = std::string(default_.value());
        }
        if (array.has_value()) {
            params["array"] = *array;
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::AttributeUrl>("POST", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Create a URL attribute.
     *
     * @param databaseId Database ID.
     * @param collectionId Collection ID.
     * @param key Attribute Key.
     * @param required Is attribute required?
     * @param default_ Default value for attribute when not provided. Cannot be set when attribute is required.
     * @param array Is attribute an array?
     * @return appwrite::Result<appwrite::models::AttributeUrl>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::AttributeUrl>> createUrlAttributeAsync(        std::string_view databaseId,         std::string_view collectionId,         std::string_view key,         bool required,         std::optional<std::string_view> default_ = std::nullopt,         std::optional<bool> array = false    ) {
                std::string path_ = std::format("/databases/{}/collections/{}/attributes/url", databaseId, collectionId);
        
        nlohmann::json params = nlohmann::json::object();
        params["key"] = std::string(key);
        params["required"] = required;
        if (default_.has_value()) {
            params["default"] = std::string(default_.value());
        }
        if (array.has_value()) {
            params["array"] = *array;
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::AttributeUrl>("POST", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Update an url attribute. Changing the `default` value will not update
     * already existing documents.
     *
     * @param databaseId Database ID.
     * @param collectionId Collection ID.
     * @param key Attribute Key.
     * @param required Is attribute required?
     * @param default_ Default value for attribute when not provided. Cannot be set when attribute is required.
     * @param newKey New Attribute Key.
     * @return appwrite::Result<appwrite::models::AttributeUrl>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::AttributeUrl> updateUrlAttribute(        std::string_view databaseId,         std::string_view collectionId,         std::string_view key,         bool required,         std::string_view default_,         std::optional<std::string_view> newKey = std::nullopt    ) {
                std::string path_ = std::format("/databases/{}/collections/{}/attributes/url/{}", databaseId, collectionId, key);
        
        nlohmann::json params = nlohmann::json::object();
        params["required"] = required;
        params["default"] = std::string(default_);
        if (newKey.has_value()) {
            params["newKey"] = std::string(newKey.value());
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::AttributeUrl>("PATCH", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Update an url attribute. Changing the `default` value will not update
     * already existing documents.
     *
     * @param databaseId Database ID.
     * @param collectionId Collection ID.
     * @param key Attribute Key.
     * @param required Is attribute required?
     * @param default_ Default value for attribute when not provided. Cannot be set when attribute is required.
     * @param newKey New Attribute Key.
     * @return appwrite::Result<appwrite::models::AttributeUrl>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::AttributeUrl>> updateUrlAttributeAsync(        std::string_view databaseId,         std::string_view collectionId,         std::string_view key,         bool required,         std::string_view default_,         std::optional<std::string_view> newKey = std::nullopt    ) {
                std::string path_ = std::format("/databases/{}/collections/{}/attributes/url/{}", databaseId, collectionId, key);
        
        nlohmann::json params = nlohmann::json::object();
        params["required"] = required;
        params["default"] = std::string(default_);
        if (newKey.has_value()) {
            params["newKey"] = std::string(newKey.value());
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::AttributeUrl>("PATCH", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Create a varchar attribute.
     *
     * @param databaseId Database ID.
     * @param collectionId Collection ID. You can create a new collection using the Database service [server integration](https://appwrite.io/docs/server/databases#databasesCreateCollection).
     * @param key Attribute Key.
     * @param size Attribute size for varchar attributes, in number of characters. Maximum size is 16381.
     * @param required Is attribute required?
     * @param default_ Default value for attribute when not provided. Cannot be set when attribute is required.
     * @param array Is attribute an array?
     * @param encrypt Toggle encryption for the attribute. Encryption enhances security by not storing any plain text values in the database. However, encrypted attributes cannot be queried.
     * @return appwrite::Result<appwrite::models::AttributeVarchar>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::AttributeVarchar> createVarcharAttribute(        std::string_view databaseId,         std::string_view collectionId,         std::string_view key,         int64_t size,         bool required,         std::optional<std::string_view> default_ = std::nullopt,         std::optional<bool> array = false,         std::optional<bool> encrypt = false    ) {
                std::string path_ = std::format("/databases/{}/collections/{}/attributes/varchar", databaseId, collectionId);
        
        nlohmann::json params = nlohmann::json::object();
        params["key"] = std::string(key);
        params["size"] = size;
        params["required"] = required;
        if (default_.has_value()) {
            params["default"] = std::string(default_.value());
        }
        if (array.has_value()) {
            params["array"] = *array;
        }
        if (encrypt.has_value()) {
            params["encrypt"] = *encrypt;
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::AttributeVarchar>("POST", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Create a varchar attribute.
     *
     * @param databaseId Database ID.
     * @param collectionId Collection ID. You can create a new collection using the Database service [server integration](https://appwrite.io/docs/server/databases#databasesCreateCollection).
     * @param key Attribute Key.
     * @param size Attribute size for varchar attributes, in number of characters. Maximum size is 16381.
     * @param required Is attribute required?
     * @param default_ Default value for attribute when not provided. Cannot be set when attribute is required.
     * @param array Is attribute an array?
     * @param encrypt Toggle encryption for the attribute. Encryption enhances security by not storing any plain text values in the database. However, encrypted attributes cannot be queried.
     * @return appwrite::Result<appwrite::models::AttributeVarchar>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::AttributeVarchar>> createVarcharAttributeAsync(        std::string_view databaseId,         std::string_view collectionId,         std::string_view key,         int64_t size,         bool required,         std::optional<std::string_view> default_ = std::nullopt,         std::optional<bool> array = false,         std::optional<bool> encrypt = false    ) {
                std::string path_ = std::format("/databases/{}/collections/{}/attributes/varchar", databaseId, collectionId);
        
        nlohmann::json params = nlohmann::json::object();
        params["key"] = std::string(key);
        params["size"] = size;
        params["required"] = required;
        if (default_.has_value()) {
            params["default"] = std::string(default_.value());
        }
        if (array.has_value()) {
            params["array"] = *array;
        }
        if (encrypt.has_value()) {
            params["encrypt"] = *encrypt;
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::AttributeVarchar>("POST", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Update a varchar attribute. Changing the `default` value will not update
     * already existing documents.
     *
     * @param databaseId Database ID.
     * @param collectionId Collection ID. You can create a new collection using the Database service [server integration](https://appwrite.io/docs/server/databases#databasesCreateCollection).
     * @param key Attribute Key.
     * @param required Is attribute required?
     * @param default_ Default value for attribute when not provided. Cannot be set when attribute is required.
     * @param size Maximum size of the varchar attribute.
     * @param newKey New Attribute Key.
     * @return appwrite::Result<appwrite::models::AttributeVarchar>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::AttributeVarchar> updateVarcharAttribute(        std::string_view databaseId,         std::string_view collectionId,         std::string_view key,         bool required,         std::string_view default_,         std::optional<int64_t> size = std::nullopt,         std::optional<std::string_view> newKey = std::nullopt    ) {
                std::string path_ = std::format("/databases/{}/collections/{}/attributes/varchar/{}", databaseId, collectionId, key);
        
        nlohmann::json params = nlohmann::json::object();
        params["required"] = required;
        params["default"] = std::string(default_);
        if (size.has_value()) {
            params["size"] = *size;
        }
        if (newKey.has_value()) {
            params["newKey"] = std::string(newKey.value());
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::AttributeVarchar>("PATCH", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Update a varchar attribute. Changing the `default` value will not update
     * already existing documents.
     *
     * @param databaseId Database ID.
     * @param collectionId Collection ID. You can create a new collection using the Database service [server integration](https://appwrite.io/docs/server/databases#databasesCreateCollection).
     * @param key Attribute Key.
     * @param required Is attribute required?
     * @param default_ Default value for attribute when not provided. Cannot be set when attribute is required.
     * @param size Maximum size of the varchar attribute.
     * @param newKey New Attribute Key.
     * @return appwrite::Result<appwrite::models::AttributeVarchar>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::AttributeVarchar>> updateVarcharAttributeAsync(        std::string_view databaseId,         std::string_view collectionId,         std::string_view key,         bool required,         std::string_view default_,         std::optional<int64_t> size = std::nullopt,         std::optional<std::string_view> newKey = std::nullopt    ) {
                std::string path_ = std::format("/databases/{}/collections/{}/attributes/varchar/{}", databaseId, collectionId, key);
        
        nlohmann::json params = nlohmann::json::object();
        params["required"] = required;
        params["default"] = std::string(default_);
        if (size.has_value()) {
            params["size"] = *size;
        }
        if (newKey.has_value()) {
            params["newKey"] = std::string(newKey.value());
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::AttributeVarchar>("PATCH", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Get attribute by ID.
     *
     * @param databaseId Database ID.
     * @param collectionId Collection ID.
     * @param key Attribute Key.
     * @return appwrite::Result<nlohmann::json>
     */
    [[nodiscard]] appwrite::Result<nlohmann::json> getAttribute(        std::string_view databaseId,         std::string_view collectionId,         std::string_view key    ) {
                std::string path_ = std::format("/databases/{}/collections/{}/attributes/{}", databaseId, collectionId, key);
        
        nlohmann::json params = nlohmann::json::object();
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<nlohmann::json>("GET", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Get attribute by ID.
     *
     * @param databaseId Database ID.
     * @param collectionId Collection ID.
     * @param key Attribute Key.
     * @return appwrite::Result<nlohmann::json>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<nlohmann::json>> getAttributeAsync(        std::string_view databaseId,         std::string_view collectionId,         std::string_view key    ) {
                std::string path_ = std::format("/databases/{}/collections/{}/attributes/{}", databaseId, collectionId, key);
        
        nlohmann::json params = nlohmann::json::object();
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<nlohmann::json>("GET", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Deletes an attribute.
     *
     * @param databaseId Database ID.
     * @param collectionId Collection ID.
     * @param key Attribute Key.
     * @return appwrite::Result<void>
     */
    [[nodiscard]] appwrite::Result<void> deleteAttribute(        std::string_view databaseId,         std::string_view collectionId,         std::string_view key    ) {
                std::string path_ = std::format("/databases/{}/collections/{}/attributes/{}", databaseId, collectionId, key);
        
        nlohmann::json params = nlohmann::json::object();
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callVoid("DELETE", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Deletes an attribute.
     *
     * @param databaseId Database ID.
     * @param collectionId Collection ID.
     * @param key Attribute Key.
     * @return appwrite::Result<void>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<void>> deleteAttributeAsync(        std::string_view databaseId,         std::string_view collectionId,         std::string_view key    ) {
                std::string path_ = std::format("/databases/{}/collections/{}/attributes/{}", databaseId, collectionId, key);
        
        nlohmann::json params = nlohmann::json::object();
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callVoidAsync("DELETE", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Get a list of all the user's documents in a given collection. You can use
     * the query params to filter your results.
     *
     * @param databaseId Database ID.
     * @param collectionId Collection ID. You can create a new collection using the Database service [server integration](https://appwrite.io/docs/server/databases#databasesCreateCollection).
     * @param queries Array of query strings generated using the Query class provided by the SDK. [Learn more about queries](https://appwrite.io/docs/queries). Maximum of 100 queries are allowed, each 4096 characters long.
     * @param transactionId Transaction ID to read uncommitted changes within the transaction.
     * @param total When set to false, the total count returned will be 0 and will not be calculated.
     * @param ttl TTL (seconds) for caching list responses. Responses are stored in an in-memory key-value cache, keyed per project, collection, schema version (attributes and indexes), caller authorization roles, and the exact query — so users with different permissions never share cached entries. Schema changes invalidate cached entries automatically; document writes do not, so choose a TTL you are comfortable serving as stale data. Set to 0 to disable caching. Must be between 0 and 86400 (24 hours).
     * @return appwrite::Result<appwrite::models::DocumentList>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::DocumentList> listDocuments(        std::string_view databaseId,         std::string_view collectionId,         std::optional<std::vector<std::string>> queries = std::vector<std::string>{},         std::optional<std::string_view> transactionId = std::nullopt,         std::optional<bool> total = true,         std::optional<int64_t> ttl = 0    ) {
                std::string path_ = std::format("/databases/{}/collections/{}/documents", databaseId, collectionId);
        
        nlohmann::json params = nlohmann::json::object();
        if (queries.has_value()) {
            params["queries"] = *queries;
        }
        if (transactionId.has_value()) {
            params["transactionId"] = std::string(transactionId.value());
        }
        if (total.has_value()) {
            params["total"] = *total;
        }
        if (ttl.has_value()) {
            params["ttl"] = *ttl;
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::DocumentList>("GET", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Get a list of all the user's documents in a given collection. You can use
     * the query params to filter your results.
     *
     * @param databaseId Database ID.
     * @param collectionId Collection ID. You can create a new collection using the Database service [server integration](https://appwrite.io/docs/server/databases#databasesCreateCollection).
     * @param queries Array of query strings generated using the Query class provided by the SDK. [Learn more about queries](https://appwrite.io/docs/queries). Maximum of 100 queries are allowed, each 4096 characters long.
     * @param transactionId Transaction ID to read uncommitted changes within the transaction.
     * @param total When set to false, the total count returned will be 0 and will not be calculated.
     * @param ttl TTL (seconds) for caching list responses. Responses are stored in an in-memory key-value cache, keyed per project, collection, schema version (attributes and indexes), caller authorization roles, and the exact query — so users with different permissions never share cached entries. Schema changes invalidate cached entries automatically; document writes do not, so choose a TTL you are comfortable serving as stale data. Set to 0 to disable caching. Must be between 0 and 86400 (24 hours).
     * @return appwrite::Result<appwrite::models::DocumentList>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::DocumentList>> listDocumentsAsync(        std::string_view databaseId,         std::string_view collectionId,         std::optional<std::vector<std::string>> queries = std::vector<std::string>{},         std::optional<std::string_view> transactionId = std::nullopt,         std::optional<bool> total = true,         std::optional<int64_t> ttl = 0    ) {
                std::string path_ = std::format("/databases/{}/collections/{}/documents", databaseId, collectionId);
        
        nlohmann::json params = nlohmann::json::object();
        if (queries.has_value()) {
            params["queries"] = *queries;
        }
        if (transactionId.has_value()) {
            params["transactionId"] = std::string(transactionId.value());
        }
        if (total.has_value()) {
            params["total"] = *total;
        }
        if (ttl.has_value()) {
            params["ttl"] = *ttl;
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::DocumentList>("GET", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Create a new Document. Before using this route, you should create a new
     * collection resource using either a [server
     * integration](https://appwrite.io/docs/server/databases#databasesCreateCollection)
     * API or directly from your database console.
     *
     * @param databaseId Database ID.
     * @param collectionId Collection ID. You can create a new collection using the Database service [server integration](https://appwrite.io/docs/server/databases#databasesCreateCollection). Make sure to define attributes before creating documents.
     * @param documentId Document ID. Choose a custom ID or generate a random ID with `ID.unique()`. Valid chars are a-z, A-Z, 0-9, period, hyphen, and underscore. Can't start with a special char. Max length is 36 chars.
     * @param data Document data as JSON object.
     * @param permissions An array of permissions strings. By default, only the current user is granted all permissions. [Learn more about permissions](https://appwrite.io/docs/permissions).
     * @param transactionId Transaction ID for staging the operation.
     * @return appwrite::Result<appwrite::models::Document>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::Document> createDocument(        std::string_view databaseId,         std::string_view collectionId,         std::string_view documentId = "",         const nlohmann::json& data = nlohmann::json::object(),         std::optional<std::vector<std::string>> permissions = std::nullopt,         std::optional<std::string_view> transactionId = std::nullopt    ) {
                std::string path_ = std::format("/databases/{}/collections/{}/documents", databaseId, collectionId);
        
        nlohmann::json params = nlohmann::json::object();
        params["documentId"] = std::string(documentId);
        params["data"] = data;
        if (permissions.has_value()) {
            params["permissions"] = *permissions;
        }
        if (transactionId.has_value()) {
            params["transactionId"] = std::string(transactionId.value());
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::Document>("POST", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Create a new Document. Before using this route, you should create a new
     * collection resource using either a [server
     * integration](https://appwrite.io/docs/server/databases#databasesCreateCollection)
     * API or directly from your database console.
     *
     * @param databaseId Database ID.
     * @param collectionId Collection ID. You can create a new collection using the Database service [server integration](https://appwrite.io/docs/server/databases#databasesCreateCollection). Make sure to define attributes before creating documents.
     * @param documentId Document ID. Choose a custom ID or generate a random ID with `ID.unique()`. Valid chars are a-z, A-Z, 0-9, period, hyphen, and underscore. Can't start with a special char. Max length is 36 chars.
     * @param data Document data as JSON object.
     * @param permissions An array of permissions strings. By default, only the current user is granted all permissions. [Learn more about permissions](https://appwrite.io/docs/permissions).
     * @param transactionId Transaction ID for staging the operation.
     * @return appwrite::Result<appwrite::models::Document>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::Document>> createDocumentAsync(        std::string_view databaseId,         std::string_view collectionId,         std::string_view documentId = "",         const nlohmann::json& data = nlohmann::json::object(),         std::optional<std::vector<std::string>> permissions = std::nullopt,         std::optional<std::string_view> transactionId = std::nullopt    ) {
                std::string path_ = std::format("/databases/{}/collections/{}/documents", databaseId, collectionId);
        
        nlohmann::json params = nlohmann::json::object();
        params["documentId"] = std::string(documentId);
        params["data"] = data;
        if (permissions.has_value()) {
            params["permissions"] = *permissions;
        }
        if (transactionId.has_value()) {
            params["transactionId"] = std::string(transactionId.value());
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::Document>("POST", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Create new Documents. Before using this route, you should create a new
     * collection resource using either a [server
     * integration](https://appwrite.io/docs/server/databases#databasesCreateCollection)
     * API or directly from your database console.
     *
     * @param databaseId Database ID.
     * @param collectionId Collection ID. You can create a new collection using the Database service [server integration](https://appwrite.io/docs/server/databases#databasesCreateCollection). Make sure to define attributes before creating documents.
     * @param documents Array of documents data as JSON objects.
     * @param transactionId Transaction ID for staging the operation.
     * @return appwrite::Result<appwrite::models::DocumentList>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::DocumentList> createDocuments(        std::string_view databaseId,         std::string_view collectionId,         std::vector<nlohmann::json> documents = std::vector<nlohmann::json>{},         std::optional<std::string_view> transactionId = std::nullopt    ) {
                std::string path_ = std::format("/databases/{}/collections/{}/documents", databaseId, collectionId);
        
        nlohmann::json params = nlohmann::json::object();
        params["documents"] = documents;
        if (transactionId.has_value()) {
            params["transactionId"] = std::string(transactionId.value());
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::DocumentList>("POST", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Create new Documents. Before using this route, you should create a new
     * collection resource using either a [server
     * integration](https://appwrite.io/docs/server/databases#databasesCreateCollection)
     * API or directly from your database console.
     *
     * @param databaseId Database ID.
     * @param collectionId Collection ID. You can create a new collection using the Database service [server integration](https://appwrite.io/docs/server/databases#databasesCreateCollection). Make sure to define attributes before creating documents.
     * @param documents Array of documents data as JSON objects.
     * @param transactionId Transaction ID for staging the operation.
     * @return appwrite::Result<appwrite::models::DocumentList>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::DocumentList>> createDocumentsAsync(        std::string_view databaseId,         std::string_view collectionId,         std::vector<nlohmann::json> documents = std::vector<nlohmann::json>{},         std::optional<std::string_view> transactionId = std::nullopt    ) {
                std::string path_ = std::format("/databases/{}/collections/{}/documents", databaseId, collectionId);
        
        nlohmann::json params = nlohmann::json::object();
        params["documents"] = documents;
        if (transactionId.has_value()) {
            params["transactionId"] = std::string(transactionId.value());
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::DocumentList>("POST", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Create or update Documents. Before using this route, you should create a
     * new collection resource using either a [server
     * integration](https://appwrite.io/docs/server/databases#databasesCreateCollection)
     * API or directly from your database console.
     *
     * @param databaseId Database ID.
     * @param collectionId Collection ID.
     * @param documents Array of document data as JSON objects. May contain partial documents.
     * @param transactionId Transaction ID for staging the operation.
     * @return appwrite::Result<appwrite::models::DocumentList>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::DocumentList> upsertDocuments(        std::string_view databaseId,         std::string_view collectionId,         std::vector<nlohmann::json> documents,         std::optional<std::string_view> transactionId = std::nullopt    ) {
                std::string path_ = std::format("/databases/{}/collections/{}/documents", databaseId, collectionId);
        
        nlohmann::json params = nlohmann::json::object();
        params["documents"] = documents;
        if (transactionId.has_value()) {
            params["transactionId"] = std::string(transactionId.value());
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::DocumentList>("PUT", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Create or update Documents. Before using this route, you should create a
     * new collection resource using either a [server
     * integration](https://appwrite.io/docs/server/databases#databasesCreateCollection)
     * API or directly from your database console.
     *
     * @param databaseId Database ID.
     * @param collectionId Collection ID.
     * @param documents Array of document data as JSON objects. May contain partial documents.
     * @param transactionId Transaction ID for staging the operation.
     * @return appwrite::Result<appwrite::models::DocumentList>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::DocumentList>> upsertDocumentsAsync(        std::string_view databaseId,         std::string_view collectionId,         std::vector<nlohmann::json> documents,         std::optional<std::string_view> transactionId = std::nullopt    ) {
                std::string path_ = std::format("/databases/{}/collections/{}/documents", databaseId, collectionId);
        
        nlohmann::json params = nlohmann::json::object();
        params["documents"] = documents;
        if (transactionId.has_value()) {
            params["transactionId"] = std::string(transactionId.value());
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::DocumentList>("PUT", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Update all documents that match your queries, if no queries are submitted
     * then all documents are updated. You can pass only specific fields to be
     * updated.
     *
     * @param databaseId Database ID.
     * @param collectionId Collection ID.
     * @param data Document data as JSON object. Include only attribute and value pairs to be updated.
     * @param queries Array of query strings generated using the Query class provided by the SDK. [Learn more about queries](https://appwrite.io/docs/queries). Maximum of 100 queries are allowed, each 4096 characters long.
     * @param transactionId Transaction ID for staging the operation.
     * @return appwrite::Result<appwrite::models::DocumentList>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::DocumentList> updateDocuments(        std::string_view databaseId,         std::string_view collectionId,         std::optional<nlohmann::json> data = nlohmann::json::object(),         std::optional<std::vector<std::string>> queries = std::vector<std::string>{},         std::optional<std::string_view> transactionId = std::nullopt    ) {
                std::string path_ = std::format("/databases/{}/collections/{}/documents", databaseId, collectionId);
        
        nlohmann::json params = nlohmann::json::object();
        if (data.has_value()) {
            params["data"] = *data;
        }
        if (queries.has_value()) {
            params["queries"] = *queries;
        }
        if (transactionId.has_value()) {
            params["transactionId"] = std::string(transactionId.value());
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::DocumentList>("PATCH", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Update all documents that match your queries, if no queries are submitted
     * then all documents are updated. You can pass only specific fields to be
     * updated.
     *
     * @param databaseId Database ID.
     * @param collectionId Collection ID.
     * @param data Document data as JSON object. Include only attribute and value pairs to be updated.
     * @param queries Array of query strings generated using the Query class provided by the SDK. [Learn more about queries](https://appwrite.io/docs/queries). Maximum of 100 queries are allowed, each 4096 characters long.
     * @param transactionId Transaction ID for staging the operation.
     * @return appwrite::Result<appwrite::models::DocumentList>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::DocumentList>> updateDocumentsAsync(        std::string_view databaseId,         std::string_view collectionId,         std::optional<nlohmann::json> data = nlohmann::json::object(),         std::optional<std::vector<std::string>> queries = std::vector<std::string>{},         std::optional<std::string_view> transactionId = std::nullopt    ) {
                std::string path_ = std::format("/databases/{}/collections/{}/documents", databaseId, collectionId);
        
        nlohmann::json params = nlohmann::json::object();
        if (data.has_value()) {
            params["data"] = *data;
        }
        if (queries.has_value()) {
            params["queries"] = *queries;
        }
        if (transactionId.has_value()) {
            params["transactionId"] = std::string(transactionId.value());
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::DocumentList>("PATCH", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Bulk delete documents using queries, if no queries are passed then all
     * documents are deleted.
     *
     * @param databaseId Database ID.
     * @param collectionId Collection ID. You can create a new collection using the Database service [server integration](https://appwrite.io/docs/server/databases#databasesCreateCollection).
     * @param queries Array of query strings generated using the Query class provided by the SDK. [Learn more about queries](https://appwrite.io/docs/queries). Maximum of 100 queries are allowed, each 4096 characters long.
     * @param transactionId Transaction ID for staging the operation.
     * @return appwrite::Result<appwrite::models::DocumentList>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::DocumentList> deleteDocuments(        std::string_view databaseId,         std::string_view collectionId,         std::optional<std::vector<std::string>> queries = std::vector<std::string>{},         std::optional<std::string_view> transactionId = std::nullopt    ) {
                std::string path_ = std::format("/databases/{}/collections/{}/documents", databaseId, collectionId);
        
        nlohmann::json params = nlohmann::json::object();
        if (queries.has_value()) {
            params["queries"] = *queries;
        }
        if (transactionId.has_value()) {
            params["transactionId"] = std::string(transactionId.value());
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::DocumentList>("DELETE", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Bulk delete documents using queries, if no queries are passed then all
     * documents are deleted.
     *
     * @param databaseId Database ID.
     * @param collectionId Collection ID. You can create a new collection using the Database service [server integration](https://appwrite.io/docs/server/databases#databasesCreateCollection).
     * @param queries Array of query strings generated using the Query class provided by the SDK. [Learn more about queries](https://appwrite.io/docs/queries). Maximum of 100 queries are allowed, each 4096 characters long.
     * @param transactionId Transaction ID for staging the operation.
     * @return appwrite::Result<appwrite::models::DocumentList>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::DocumentList>> deleteDocumentsAsync(        std::string_view databaseId,         std::string_view collectionId,         std::optional<std::vector<std::string>> queries = std::vector<std::string>{},         std::optional<std::string_view> transactionId = std::nullopt    ) {
                std::string path_ = std::format("/databases/{}/collections/{}/documents", databaseId, collectionId);
        
        nlohmann::json params = nlohmann::json::object();
        if (queries.has_value()) {
            params["queries"] = *queries;
        }
        if (transactionId.has_value()) {
            params["transactionId"] = std::string(transactionId.value());
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::DocumentList>("DELETE", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Get a document by its unique ID. This endpoint response returns a JSON
     * object with the document data.
     *
     * @param databaseId Database ID.
     * @param collectionId Collection ID. You can create a new collection using the Database service [server integration](https://appwrite.io/docs/server/databases#databasesCreateCollection).
     * @param documentId Document ID.
     * @param queries Array of query strings generated using the Query class provided by the SDK. [Learn more about queries](https://appwrite.io/docs/queries). Maximum of 100 queries are allowed, each 4096 characters long.
     * @param transactionId Transaction ID to read uncommitted changes within the transaction.
     * @return appwrite::Result<appwrite::models::Document>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::Document> getDocument(        std::string_view databaseId,         std::string_view collectionId,         std::string_view documentId,         std::optional<std::vector<std::string>> queries = std::vector<std::string>{},         std::optional<std::string_view> transactionId = std::nullopt    ) {
                std::string path_ = std::format("/databases/{}/collections/{}/documents/{}", databaseId, collectionId, documentId);
        
        nlohmann::json params = nlohmann::json::object();
        if (queries.has_value()) {
            params["queries"] = *queries;
        }
        if (transactionId.has_value()) {
            params["transactionId"] = std::string(transactionId.value());
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::Document>("GET", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Get a document by its unique ID. This endpoint response returns a JSON
     * object with the document data.
     *
     * @param databaseId Database ID.
     * @param collectionId Collection ID. You can create a new collection using the Database service [server integration](https://appwrite.io/docs/server/databases#databasesCreateCollection).
     * @param documentId Document ID.
     * @param queries Array of query strings generated using the Query class provided by the SDK. [Learn more about queries](https://appwrite.io/docs/queries). Maximum of 100 queries are allowed, each 4096 characters long.
     * @param transactionId Transaction ID to read uncommitted changes within the transaction.
     * @return appwrite::Result<appwrite::models::Document>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::Document>> getDocumentAsync(        std::string_view databaseId,         std::string_view collectionId,         std::string_view documentId,         std::optional<std::vector<std::string>> queries = std::vector<std::string>{},         std::optional<std::string_view> transactionId = std::nullopt    ) {
                std::string path_ = std::format("/databases/{}/collections/{}/documents/{}", databaseId, collectionId, documentId);
        
        nlohmann::json params = nlohmann::json::object();
        if (queries.has_value()) {
            params["queries"] = *queries;
        }
        if (transactionId.has_value()) {
            params["transactionId"] = std::string(transactionId.value());
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::Document>("GET", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Create or update a Document. Before using this route, you should create a
     * new collection resource using either a [server
     * integration](https://appwrite.io/docs/server/databases#databasesCreateCollection)
     * API or directly from your database console.
     *
     * @param databaseId Database ID.
     * @param collectionId Collection ID.
     * @param documentId Document ID.
     * @param data Document data as JSON object. Include all required attributes of the document to be created or updated.
     * @param permissions An array of permissions strings. By default, the current permissions are inherited. [Learn more about permissions](https://appwrite.io/docs/permissions).
     * @param transactionId Transaction ID for staging the operation.
     * @return appwrite::Result<appwrite::models::Document>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::Document> upsertDocument(        std::string_view databaseId,         std::string_view collectionId,         std::string_view documentId,         std::optional<nlohmann::json> data = nlohmann::json::object(),         std::optional<std::vector<std::string>> permissions = std::nullopt,         std::optional<std::string_view> transactionId = std::nullopt    ) {
                std::string path_ = std::format("/databases/{}/collections/{}/documents/{}", databaseId, collectionId, documentId);
        
        nlohmann::json params = nlohmann::json::object();
        if (data.has_value()) {
            params["data"] = *data;
        }
        if (permissions.has_value()) {
            params["permissions"] = *permissions;
        }
        if (transactionId.has_value()) {
            params["transactionId"] = std::string(transactionId.value());
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::Document>("PUT", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Create or update a Document. Before using this route, you should create a
     * new collection resource using either a [server
     * integration](https://appwrite.io/docs/server/databases#databasesCreateCollection)
     * API or directly from your database console.
     *
     * @param databaseId Database ID.
     * @param collectionId Collection ID.
     * @param documentId Document ID.
     * @param data Document data as JSON object. Include all required attributes of the document to be created or updated.
     * @param permissions An array of permissions strings. By default, the current permissions are inherited. [Learn more about permissions](https://appwrite.io/docs/permissions).
     * @param transactionId Transaction ID for staging the operation.
     * @return appwrite::Result<appwrite::models::Document>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::Document>> upsertDocumentAsync(        std::string_view databaseId,         std::string_view collectionId,         std::string_view documentId,         std::optional<nlohmann::json> data = nlohmann::json::object(),         std::optional<std::vector<std::string>> permissions = std::nullopt,         std::optional<std::string_view> transactionId = std::nullopt    ) {
                std::string path_ = std::format("/databases/{}/collections/{}/documents/{}", databaseId, collectionId, documentId);
        
        nlohmann::json params = nlohmann::json::object();
        if (data.has_value()) {
            params["data"] = *data;
        }
        if (permissions.has_value()) {
            params["permissions"] = *permissions;
        }
        if (transactionId.has_value()) {
            params["transactionId"] = std::string(transactionId.value());
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::Document>("PUT", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Update a document by its unique ID. Using the patch method you can pass
     * only specific fields that will get updated.
     *
     * @param databaseId Database ID.
     * @param collectionId Collection ID.
     * @param documentId Document ID.
     * @param data Document data as JSON object. Include only attribute and value pairs to be updated.
     * @param permissions An array of permissions strings. By default, the current permissions are inherited. [Learn more about permissions](https://appwrite.io/docs/permissions).
     * @param transactionId Transaction ID for staging the operation.
     * @return appwrite::Result<appwrite::models::Document>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::Document> updateDocument(        std::string_view databaseId,         std::string_view collectionId,         std::string_view documentId,         std::optional<nlohmann::json> data = nlohmann::json::object(),         std::optional<std::vector<std::string>> permissions = std::nullopt,         std::optional<std::string_view> transactionId = std::nullopt    ) {
                std::string path_ = std::format("/databases/{}/collections/{}/documents/{}", databaseId, collectionId, documentId);
        
        nlohmann::json params = nlohmann::json::object();
        if (data.has_value()) {
            params["data"] = *data;
        }
        if (permissions.has_value()) {
            params["permissions"] = *permissions;
        }
        if (transactionId.has_value()) {
            params["transactionId"] = std::string(transactionId.value());
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::Document>("PATCH", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Update a document by its unique ID. Using the patch method you can pass
     * only specific fields that will get updated.
     *
     * @param databaseId Database ID.
     * @param collectionId Collection ID.
     * @param documentId Document ID.
     * @param data Document data as JSON object. Include only attribute and value pairs to be updated.
     * @param permissions An array of permissions strings. By default, the current permissions are inherited. [Learn more about permissions](https://appwrite.io/docs/permissions).
     * @param transactionId Transaction ID for staging the operation.
     * @return appwrite::Result<appwrite::models::Document>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::Document>> updateDocumentAsync(        std::string_view databaseId,         std::string_view collectionId,         std::string_view documentId,         std::optional<nlohmann::json> data = nlohmann::json::object(),         std::optional<std::vector<std::string>> permissions = std::nullopt,         std::optional<std::string_view> transactionId = std::nullopt    ) {
                std::string path_ = std::format("/databases/{}/collections/{}/documents/{}", databaseId, collectionId, documentId);
        
        nlohmann::json params = nlohmann::json::object();
        if (data.has_value()) {
            params["data"] = *data;
        }
        if (permissions.has_value()) {
            params["permissions"] = *permissions;
        }
        if (transactionId.has_value()) {
            params["transactionId"] = std::string(transactionId.value());
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::Document>("PATCH", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Delete a document by its unique ID.
     *
     * @param databaseId Database ID.
     * @param collectionId Collection ID. You can create a new collection using the Database service [server integration](https://appwrite.io/docs/server/databases#databasesCreateCollection).
     * @param documentId Document ID.
     * @param transactionId Transaction ID for staging the operation.
     * @return appwrite::Result<void>
     */
    [[nodiscard]] appwrite::Result<void> deleteDocument(        std::string_view databaseId,         std::string_view collectionId,         std::string_view documentId,         std::optional<std::string_view> transactionId = std::nullopt    ) {
                std::string path_ = std::format("/databases/{}/collections/{}/documents/{}", databaseId, collectionId, documentId);
        
        nlohmann::json params = nlohmann::json::object();
        if (transactionId.has_value()) {
            params["transactionId"] = std::string(transactionId.value());
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callVoid("DELETE", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Delete a document by its unique ID.
     *
     * @param databaseId Database ID.
     * @param collectionId Collection ID. You can create a new collection using the Database service [server integration](https://appwrite.io/docs/server/databases#databasesCreateCollection).
     * @param documentId Document ID.
     * @param transactionId Transaction ID for staging the operation.
     * @return appwrite::Result<void>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<void>> deleteDocumentAsync(        std::string_view databaseId,         std::string_view collectionId,         std::string_view documentId,         std::optional<std::string_view> transactionId = std::nullopt    ) {
                std::string path_ = std::format("/databases/{}/collections/{}/documents/{}", databaseId, collectionId, documentId);
        
        nlohmann::json params = nlohmann::json::object();
        if (transactionId.has_value()) {
            params["transactionId"] = std::string(transactionId.value());
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callVoidAsync("DELETE", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Decrement a specific attribute of a document by a given value.
     *
     * @param databaseId Database ID.
     * @param collectionId Collection ID.
     * @param documentId Document ID.
     * @param attribute Attribute key.
     * @param value Value to increment the attribute by. The value must be a number.
     * @param min Minimum value for the attribute. If the current value is lesser than this value, an exception will be thrown.
     * @param transactionId Transaction ID for staging the operation.
     * @return appwrite::Result<appwrite::models::Document>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::Document> decrementDocumentAttribute(        std::string_view databaseId,         std::string_view collectionId,         std::string_view documentId,         std::string_view attribute,         std::optional<double> value = 1,         std::optional<double> min = std::nullopt,         std::optional<std::string_view> transactionId = std::nullopt    ) {
                std::string path_ = std::format("/databases/{}/collections/{}/documents/{}/{}/decrement", databaseId, collectionId, documentId, attribute);
        
        nlohmann::json params = nlohmann::json::object();
        if (value.has_value()) {
            params["value"] = *value;
        }
        if (min.has_value()) {
            params["min"] = *min;
        }
        if (transactionId.has_value()) {
            params["transactionId"] = std::string(transactionId.value());
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::Document>("PATCH", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Decrement a specific attribute of a document by a given value.
     *
     * @param databaseId Database ID.
     * @param collectionId Collection ID.
     * @param documentId Document ID.
     * @param attribute Attribute key.
     * @param value Value to increment the attribute by. The value must be a number.
     * @param min Minimum value for the attribute. If the current value is lesser than this value, an exception will be thrown.
     * @param transactionId Transaction ID for staging the operation.
     * @return appwrite::Result<appwrite::models::Document>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::Document>> decrementDocumentAttributeAsync(        std::string_view databaseId,         std::string_view collectionId,         std::string_view documentId,         std::string_view attribute,         std::optional<double> value = 1,         std::optional<double> min = std::nullopt,         std::optional<std::string_view> transactionId = std::nullopt    ) {
                std::string path_ = std::format("/databases/{}/collections/{}/documents/{}/{}/decrement", databaseId, collectionId, documentId, attribute);
        
        nlohmann::json params = nlohmann::json::object();
        if (value.has_value()) {
            params["value"] = *value;
        }
        if (min.has_value()) {
            params["min"] = *min;
        }
        if (transactionId.has_value()) {
            params["transactionId"] = std::string(transactionId.value());
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::Document>("PATCH", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Increment a specific attribute of a document by a given value.
     *
     * @param databaseId Database ID.
     * @param collectionId Collection ID.
     * @param documentId Document ID.
     * @param attribute Attribute key.
     * @param value Value to increment the attribute by. The value must be a number.
     * @param max Maximum value for the attribute. If the current value is greater than this value, an error will be thrown.
     * @param transactionId Transaction ID for staging the operation.
     * @return appwrite::Result<appwrite::models::Document>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::Document> incrementDocumentAttribute(        std::string_view databaseId,         std::string_view collectionId,         std::string_view documentId,         std::string_view attribute,         std::optional<double> value = 1,         std::optional<double> max = std::nullopt,         std::optional<std::string_view> transactionId = std::nullopt    ) {
                std::string path_ = std::format("/databases/{}/collections/{}/documents/{}/{}/increment", databaseId, collectionId, documentId, attribute);
        
        nlohmann::json params = nlohmann::json::object();
        if (value.has_value()) {
            params["value"] = *value;
        }
        if (max.has_value()) {
            params["max"] = *max;
        }
        if (transactionId.has_value()) {
            params["transactionId"] = std::string(transactionId.value());
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::Document>("PATCH", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Increment a specific attribute of a document by a given value.
     *
     * @param databaseId Database ID.
     * @param collectionId Collection ID.
     * @param documentId Document ID.
     * @param attribute Attribute key.
     * @param value Value to increment the attribute by. The value must be a number.
     * @param max Maximum value for the attribute. If the current value is greater than this value, an error will be thrown.
     * @param transactionId Transaction ID for staging the operation.
     * @return appwrite::Result<appwrite::models::Document>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::Document>> incrementDocumentAttributeAsync(        std::string_view databaseId,         std::string_view collectionId,         std::string_view documentId,         std::string_view attribute,         std::optional<double> value = 1,         std::optional<double> max = std::nullopt,         std::optional<std::string_view> transactionId = std::nullopt    ) {
                std::string path_ = std::format("/databases/{}/collections/{}/documents/{}/{}/increment", databaseId, collectionId, documentId, attribute);
        
        nlohmann::json params = nlohmann::json::object();
        if (value.has_value()) {
            params["value"] = *value;
        }
        if (max.has_value()) {
            params["max"] = *max;
        }
        if (transactionId.has_value()) {
            params["transactionId"] = std::string(transactionId.value());
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::Document>("PATCH", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * List indexes in the collection.
     *
     * @param databaseId Database ID.
     * @param collectionId Collection ID. You can create a new collection using the Database service [server integration](https://appwrite.io/docs/server/databases#databasesCreateCollection).
     * @param queries Array of query strings generated using the Query class provided by the SDK. [Learn more about queries](https://appwrite.io/docs/queries). Maximum of 100 queries are allowed, each 4096 characters long. You may filter on the following attributes: key, type, status, attributes, error
     * @param total When set to false, the total count returned will be 0 and will not be calculated.
     * @return appwrite::Result<appwrite::models::IndexList>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::IndexList> listIndexes(        std::string_view databaseId,         std::string_view collectionId,         std::optional<std::vector<std::string>> queries = std::vector<std::string>{},         std::optional<bool> total = true    ) {
                std::string path_ = std::format("/databases/{}/collections/{}/indexes", databaseId, collectionId);
        
        nlohmann::json params = nlohmann::json::object();
        if (queries.has_value()) {
            params["queries"] = *queries;
        }
        if (total.has_value()) {
            params["total"] = *total;
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::IndexList>("GET", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * List indexes in the collection.
     *
     * @param databaseId Database ID.
     * @param collectionId Collection ID. You can create a new collection using the Database service [server integration](https://appwrite.io/docs/server/databases#databasesCreateCollection).
     * @param queries Array of query strings generated using the Query class provided by the SDK. [Learn more about queries](https://appwrite.io/docs/queries). Maximum of 100 queries are allowed, each 4096 characters long. You may filter on the following attributes: key, type, status, attributes, error
     * @param total When set to false, the total count returned will be 0 and will not be calculated.
     * @return appwrite::Result<appwrite::models::IndexList>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::IndexList>> listIndexesAsync(        std::string_view databaseId,         std::string_view collectionId,         std::optional<std::vector<std::string>> queries = std::vector<std::string>{},         std::optional<bool> total = true    ) {
                std::string path_ = std::format("/databases/{}/collections/{}/indexes", databaseId, collectionId);
        
        nlohmann::json params = nlohmann::json::object();
        if (queries.has_value()) {
            params["queries"] = *queries;
        }
        if (total.has_value()) {
            params["total"] = *total;
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::IndexList>("GET", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Creates an index on the attributes listed. Your index should include all
     * the attributes you will query in a single request.
Attributes can be `key`,
     * `fulltext`, and `unique`.
     *
     * @param databaseId Database ID.
     * @param collectionId Collection ID. You can create a new collection using the Database service [server integration](https://appwrite.io/docs/server/databases#databasesCreateCollection).
     * @param key Index Key.
     * @param type Index type.
     * @param attributes Array of attributes to index. Maximum of 100 attributes are allowed, each 32 characters long.
     * @param orders Array of index orders. Maximum of 100 orders are allowed.
     * @param lengths Length of index. Maximum of 100
     * @return appwrite::Result<appwrite::models::Index>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::Index> createIndex(        std::string_view databaseId,         std::string_view collectionId,         std::string_view key,         appwrite::enums::DatabasesIndexType type,         std::vector<std::string> attributes,         std::optional<std::vector<appwrite::enums::OrderBy>> orders = {},         std::optional<std::vector<int64_t>> lengths = std::vector<int64_t>{}    ) {
                std::string path_ = std::format("/databases/{}/collections/{}/indexes", databaseId, collectionId);
        
        nlohmann::json params = nlohmann::json::object();
        params["key"] = std::string(key);
        params["type"] = type;
        params["attributes"] = attributes;
        if (orders.has_value()) {
            params["orders"] = *orders;
        }
        if (lengths.has_value()) {
            params["lengths"] = *lengths;
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::Index>("POST", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Creates an index on the attributes listed. Your index should include all
     * the attributes you will query in a single request.
Attributes can be `key`,
     * `fulltext`, and `unique`.
     *
     * @param databaseId Database ID.
     * @param collectionId Collection ID. You can create a new collection using the Database service [server integration](https://appwrite.io/docs/server/databases#databasesCreateCollection).
     * @param key Index Key.
     * @param type Index type.
     * @param attributes Array of attributes to index. Maximum of 100 attributes are allowed, each 32 characters long.
     * @param orders Array of index orders. Maximum of 100 orders are allowed.
     * @param lengths Length of index. Maximum of 100
     * @return appwrite::Result<appwrite::models::Index>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::Index>> createIndexAsync(        std::string_view databaseId,         std::string_view collectionId,         std::string_view key,         appwrite::enums::DatabasesIndexType type,         std::vector<std::string> attributes,         std::optional<std::vector<appwrite::enums::OrderBy>> orders = {},         std::optional<std::vector<int64_t>> lengths = std::vector<int64_t>{}    ) {
                std::string path_ = std::format("/databases/{}/collections/{}/indexes", databaseId, collectionId);
        
        nlohmann::json params = nlohmann::json::object();
        params["key"] = std::string(key);
        params["type"] = type;
        params["attributes"] = attributes;
        if (orders.has_value()) {
            params["orders"] = *orders;
        }
        if (lengths.has_value()) {
            params["lengths"] = *lengths;
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::Index>("POST", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Get an index by its unique ID.
     *
     * @param databaseId Database ID.
     * @param collectionId Collection ID. You can create a new collection using the Database service [server integration](https://appwrite.io/docs/server/databases#databasesCreateCollection).
     * @param key Index Key.
     * @return appwrite::Result<appwrite::models::Index>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::Index> getIndex(        std::string_view databaseId,         std::string_view collectionId,         std::string_view key    ) {
                std::string path_ = std::format("/databases/{}/collections/{}/indexes/{}", databaseId, collectionId, key);
        
        nlohmann::json params = nlohmann::json::object();
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::Index>("GET", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Get an index by its unique ID.
     *
     * @param databaseId Database ID.
     * @param collectionId Collection ID. You can create a new collection using the Database service [server integration](https://appwrite.io/docs/server/databases#databasesCreateCollection).
     * @param key Index Key.
     * @return appwrite::Result<appwrite::models::Index>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::Index>> getIndexAsync(        std::string_view databaseId,         std::string_view collectionId,         std::string_view key    ) {
                std::string path_ = std::format("/databases/{}/collections/{}/indexes/{}", databaseId, collectionId, key);
        
        nlohmann::json params = nlohmann::json::object();
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::Index>("GET", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Delete an index.
     *
     * @param databaseId Database ID.
     * @param collectionId Collection ID. You can create a new collection using the Database service [server integration](https://appwrite.io/docs/server/databases#databasesCreateCollection).
     * @param key Index Key.
     * @return appwrite::Result<void>
     */
    [[nodiscard]] appwrite::Result<void> deleteIndex(        std::string_view databaseId,         std::string_view collectionId,         std::string_view key    ) {
                std::string path_ = std::format("/databases/{}/collections/{}/indexes/{}", databaseId, collectionId, key);
        
        nlohmann::json params = nlohmann::json::object();
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callVoid("DELETE", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Delete an index.
     *
     * @param databaseId Database ID.
     * @param collectionId Collection ID. You can create a new collection using the Database service [server integration](https://appwrite.io/docs/server/databases#databasesCreateCollection).
     * @param key Index Key.
     * @return appwrite::Result<void>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<void>> deleteIndexAsync(        std::string_view databaseId,         std::string_view collectionId,         std::string_view key    ) {
                std::string path_ = std::format("/databases/{}/collections/{}/indexes/{}", databaseId, collectionId, key);
        
        nlohmann::json params = nlohmann::json::object();
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callVoidAsync("DELETE", path_, std::move(local_headers_), std::move(params));
    }
};

/**
 * @brief The Functions Service allows you view, create and manage your Cloud Functions.
 */
class Functions : public Service {
public:
    explicit Functions(Client& client) : Service(client) {}

    /**
     * Get a list of all the project's functions. You can use the query params to
     * filter your results.
     *
     * @param queries Array of query strings generated using the Query class provided by the SDK. [Learn more about queries](https://appwrite.io/docs/queries). Maximum of 100 queries are allowed, each 4096 characters long. You may filter on the following attributes: name, enabled, runtime, deploymentId, schedule, scheduleNext, schedulePrevious, timeout, entrypoint, commands, installationId
     * @param search Search term to filter your list results. Max length: 256 chars.
     * @param total When set to false, the total count returned will be 0 and will not be calculated.
     * @return appwrite::Result<appwrite::models::FunctionList>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::FunctionList> list(        std::optional<std::vector<std::string>> queries = std::vector<std::string>{},         std::optional<std::string_view> search = std::nullopt,         std::optional<bool> total = true    ) {
                std::string path_ = std::format("/functions");
        
        nlohmann::json params = nlohmann::json::object();
        if (queries.has_value()) {
            params["queries"] = *queries;
        }
        if (search.has_value()) {
            params["search"] = std::string(search.value());
        }
        if (total.has_value()) {
            params["total"] = *total;
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::FunctionList>("GET", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Get a list of all the project's functions. You can use the query params to
     * filter your results.
     *
     * @param queries Array of query strings generated using the Query class provided by the SDK. [Learn more about queries](https://appwrite.io/docs/queries). Maximum of 100 queries are allowed, each 4096 characters long. You may filter on the following attributes: name, enabled, runtime, deploymentId, schedule, scheduleNext, schedulePrevious, timeout, entrypoint, commands, installationId
     * @param search Search term to filter your list results. Max length: 256 chars.
     * @param total When set to false, the total count returned will be 0 and will not be calculated.
     * @return appwrite::Result<appwrite::models::FunctionList>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::FunctionList>> listAsync(        std::optional<std::vector<std::string>> queries = std::vector<std::string>{},         std::optional<std::string_view> search = std::nullopt,         std::optional<bool> total = true    ) {
                std::string path_ = std::format("/functions");
        
        nlohmann::json params = nlohmann::json::object();
        if (queries.has_value()) {
            params["queries"] = *queries;
        }
        if (search.has_value()) {
            params["search"] = std::string(search.value());
        }
        if (total.has_value()) {
            params["total"] = *total;
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::FunctionList>("GET", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Create a new function. You can pass a list of
     * [permissions](https://appwrite.io/docs/permissions) to allow different
     * project users or team with access to execute the function using the client
     * API.
     *
     * @param functionId Function ID. Choose a custom ID or generate a random ID with `ID.unique()`. Valid chars are a-z, A-Z, 0-9, period, hyphen, and underscore. Can't start with a special char. Max length is 36 chars.
     * @param name Function name. Max length: 128 chars.
     * @param runtime Execution runtime.
     * @param execute An array of role strings with execution permissions. By default no user is granted with any execute permissions. [learn more about roles](https://appwrite.io/docs/permissions#permission-roles). Maximum of 100 roles are allowed, each 64 characters long.
     * @param events Events list. Maximum of 100 events are allowed.
     * @param schedule Schedule CRON syntax.
     * @param timeout Function maximum execution time in seconds.
     * @param enabled Is function enabled? When set to 'disabled', users cannot access the function but Server SDKs with and API key can still access the function. No data is lost when this is toggled.
     * @param logging When disabled, executions will exclude logs and errors, and will be slightly faster.
     * @param entrypoint Entrypoint File. This path is relative to the "providerRootDirectory".
     * @param commands Build Commands.
     * @param scopes List of scopes allowed for API key auto-generated for every execution. Maximum of 100 scopes are allowed.
     * @param installationId Appwrite Installation ID for VCS (Version Control System) deployment.
     * @param providerRepositoryId Repository ID of the repo linked to the function.
     * @param providerBranch Production branch for the repo linked to the function.
     * @param providerSilentMode Is the VCS (Version Control System) connection in silent mode for the repo linked to the function? In silent mode, comments will not be made on commits and pull requests.
     * @param providerRootDirectory Path to function code in the linked repo.
     * @param buildSpecification Build specification for the function deployments.
     * @param runtimeSpecification Runtime specification for the function executions.
     * @param deploymentRetention Days to keep non-active deployments before deletion. Value 0 means all deployments will be kept.
     * @return appwrite::Result<appwrite::models::Function>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::Function> create(        std::string_view functionId,         std::string_view name,         appwrite::enums::Runtime runtime,         std::optional<std::vector<std::string>> execute = std::vector<std::string>{},         std::optional<std::vector<std::string>> events = std::vector<std::string>{},         std::optional<std::string_view> schedule = std::nullopt,         std::optional<int64_t> timeout = 15,         std::optional<bool> enabled = true,         std::optional<bool> logging = true,         std::optional<std::string_view> entrypoint = std::nullopt,         std::optional<std::string_view> commands = std::nullopt,         std::optional<std::vector<appwrite::enums::Scopes>> scopes = {},         std::optional<std::string_view> installationId = std::nullopt,         std::optional<std::string_view> providerRepositoryId = std::nullopt,         std::optional<std::string_view> providerBranch = std::nullopt,         std::optional<bool> providerSilentMode = false,         std::optional<std::string_view> providerRootDirectory = std::nullopt,         std::optional<std::string_view> buildSpecification = "[]",         std::optional<std::string_view> runtimeSpecification = "[]",         std::optional<int64_t> deploymentRetention = 0    ) {
                std::string path_ = std::format("/functions");
        
        nlohmann::json params = nlohmann::json::object();
        params["functionId"] = std::string(functionId);
        params["name"] = std::string(name);
        params["runtime"] = runtime;
        if (execute.has_value()) {
            params["execute"] = *execute;
        }
        if (events.has_value()) {
            params["events"] = *events;
        }
        if (schedule.has_value()) {
            params["schedule"] = std::string(schedule.value());
        }
        if (timeout.has_value()) {
            params["timeout"] = *timeout;
        }
        if (enabled.has_value()) {
            params["enabled"] = *enabled;
        }
        if (logging.has_value()) {
            params["logging"] = *logging;
        }
        if (entrypoint.has_value()) {
            params["entrypoint"] = std::string(entrypoint.value());
        }
        if (commands.has_value()) {
            params["commands"] = std::string(commands.value());
        }
        if (scopes.has_value()) {
            params["scopes"] = *scopes;
        }
        if (installationId.has_value()) {
            params["installationId"] = std::string(installationId.value());
        }
        if (providerRepositoryId.has_value()) {
            params["providerRepositoryId"] = std::string(providerRepositoryId.value());
        }
        if (providerBranch.has_value()) {
            params["providerBranch"] = std::string(providerBranch.value());
        }
        if (providerSilentMode.has_value()) {
            params["providerSilentMode"] = *providerSilentMode;
        }
        if (providerRootDirectory.has_value()) {
            params["providerRootDirectory"] = std::string(providerRootDirectory.value());
        }
        if (buildSpecification.has_value()) {
            params["buildSpecification"] = std::string(buildSpecification.value());
        }
        if (runtimeSpecification.has_value()) {
            params["runtimeSpecification"] = std::string(runtimeSpecification.value());
        }
        if (deploymentRetention.has_value()) {
            params["deploymentRetention"] = *deploymentRetention;
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::Function>("POST", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Create a new function. You can pass a list of
     * [permissions](https://appwrite.io/docs/permissions) to allow different
     * project users or team with access to execute the function using the client
     * API.
     *
     * @param functionId Function ID. Choose a custom ID or generate a random ID with `ID.unique()`. Valid chars are a-z, A-Z, 0-9, period, hyphen, and underscore. Can't start with a special char. Max length is 36 chars.
     * @param name Function name. Max length: 128 chars.
     * @param runtime Execution runtime.
     * @param execute An array of role strings with execution permissions. By default no user is granted with any execute permissions. [learn more about roles](https://appwrite.io/docs/permissions#permission-roles). Maximum of 100 roles are allowed, each 64 characters long.
     * @param events Events list. Maximum of 100 events are allowed.
     * @param schedule Schedule CRON syntax.
     * @param timeout Function maximum execution time in seconds.
     * @param enabled Is function enabled? When set to 'disabled', users cannot access the function but Server SDKs with and API key can still access the function. No data is lost when this is toggled.
     * @param logging When disabled, executions will exclude logs and errors, and will be slightly faster.
     * @param entrypoint Entrypoint File. This path is relative to the "providerRootDirectory".
     * @param commands Build Commands.
     * @param scopes List of scopes allowed for API key auto-generated for every execution. Maximum of 100 scopes are allowed.
     * @param installationId Appwrite Installation ID for VCS (Version Control System) deployment.
     * @param providerRepositoryId Repository ID of the repo linked to the function.
     * @param providerBranch Production branch for the repo linked to the function.
     * @param providerSilentMode Is the VCS (Version Control System) connection in silent mode for the repo linked to the function? In silent mode, comments will not be made on commits and pull requests.
     * @param providerRootDirectory Path to function code in the linked repo.
     * @param buildSpecification Build specification for the function deployments.
     * @param runtimeSpecification Runtime specification for the function executions.
     * @param deploymentRetention Days to keep non-active deployments before deletion. Value 0 means all deployments will be kept.
     * @return appwrite::Result<appwrite::models::Function>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::Function>> createAsync(        std::string_view functionId,         std::string_view name,         appwrite::enums::Runtime runtime,         std::optional<std::vector<std::string>> execute = std::vector<std::string>{},         std::optional<std::vector<std::string>> events = std::vector<std::string>{},         std::optional<std::string_view> schedule = std::nullopt,         std::optional<int64_t> timeout = 15,         std::optional<bool> enabled = true,         std::optional<bool> logging = true,         std::optional<std::string_view> entrypoint = std::nullopt,         std::optional<std::string_view> commands = std::nullopt,         std::optional<std::vector<appwrite::enums::Scopes>> scopes = {},         std::optional<std::string_view> installationId = std::nullopt,         std::optional<std::string_view> providerRepositoryId = std::nullopt,         std::optional<std::string_view> providerBranch = std::nullopt,         std::optional<bool> providerSilentMode = false,         std::optional<std::string_view> providerRootDirectory = std::nullopt,         std::optional<std::string_view> buildSpecification = "[]",         std::optional<std::string_view> runtimeSpecification = "[]",         std::optional<int64_t> deploymentRetention = 0    ) {
                std::string path_ = std::format("/functions");
        
        nlohmann::json params = nlohmann::json::object();
        params["functionId"] = std::string(functionId);
        params["name"] = std::string(name);
        params["runtime"] = runtime;
        if (execute.has_value()) {
            params["execute"] = *execute;
        }
        if (events.has_value()) {
            params["events"] = *events;
        }
        if (schedule.has_value()) {
            params["schedule"] = std::string(schedule.value());
        }
        if (timeout.has_value()) {
            params["timeout"] = *timeout;
        }
        if (enabled.has_value()) {
            params["enabled"] = *enabled;
        }
        if (logging.has_value()) {
            params["logging"] = *logging;
        }
        if (entrypoint.has_value()) {
            params["entrypoint"] = std::string(entrypoint.value());
        }
        if (commands.has_value()) {
            params["commands"] = std::string(commands.value());
        }
        if (scopes.has_value()) {
            params["scopes"] = *scopes;
        }
        if (installationId.has_value()) {
            params["installationId"] = std::string(installationId.value());
        }
        if (providerRepositoryId.has_value()) {
            params["providerRepositoryId"] = std::string(providerRepositoryId.value());
        }
        if (providerBranch.has_value()) {
            params["providerBranch"] = std::string(providerBranch.value());
        }
        if (providerSilentMode.has_value()) {
            params["providerSilentMode"] = *providerSilentMode;
        }
        if (providerRootDirectory.has_value()) {
            params["providerRootDirectory"] = std::string(providerRootDirectory.value());
        }
        if (buildSpecification.has_value()) {
            params["buildSpecification"] = std::string(buildSpecification.value());
        }
        if (runtimeSpecification.has_value()) {
            params["runtimeSpecification"] = std::string(runtimeSpecification.value());
        }
        if (deploymentRetention.has_value()) {
            params["deploymentRetention"] = *deploymentRetention;
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::Function>("POST", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Get a list of all runtimes that are currently active on your instance.
     *
     * @return appwrite::Result<appwrite::models::RuntimeList>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::RuntimeList> listRuntimes(    ) {
                std::string path_ = std::format("/functions/runtimes");
        
        nlohmann::json params = nlohmann::json::object();
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::RuntimeList>("GET", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Get a list of all runtimes that are currently active on your instance.
     *
     * @return appwrite::Result<appwrite::models::RuntimeList>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::RuntimeList>> listRuntimesAsync(    ) {
                std::string path_ = std::format("/functions/runtimes");
        
        nlohmann::json params = nlohmann::json::object();
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::RuntimeList>("GET", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * List allowed function specifications for this instance.
     *
     * @return appwrite::Result<appwrite::models::SpecificationList>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::SpecificationList> listSpecifications(    ) {
                std::string path_ = std::format("/functions/specifications");
        
        nlohmann::json params = nlohmann::json::object();
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::SpecificationList>("GET", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * List allowed function specifications for this instance.
     *
     * @return appwrite::Result<appwrite::models::SpecificationList>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::SpecificationList>> listSpecificationsAsync(    ) {
                std::string path_ = std::format("/functions/specifications");
        
        nlohmann::json params = nlohmann::json::object();
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::SpecificationList>("GET", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Get a function by its unique ID.
     *
     * @param functionId Function ID.
     * @return appwrite::Result<appwrite::models::Function>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::Function> get(        std::string_view functionId    ) {
                std::string path_ = std::format("/functions/{}", functionId);
        
        nlohmann::json params = nlohmann::json::object();
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::Function>("GET", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Get a function by its unique ID.
     *
     * @param functionId Function ID.
     * @return appwrite::Result<appwrite::models::Function>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::Function>> getAsync(        std::string_view functionId    ) {
                std::string path_ = std::format("/functions/{}", functionId);
        
        nlohmann::json params = nlohmann::json::object();
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::Function>("GET", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Update function by its unique ID.
     *
     * @param functionId Function ID.
     * @param name Function name. Max length: 128 chars.
     * @param runtime Execution runtime.
     * @param execute An array of role strings with execution permissions. By default no user is granted with any execute permissions. [learn more about roles](https://appwrite.io/docs/permissions#permission-roles). Maximum of 100 roles are allowed, each 64 characters long.
     * @param events Events list. Maximum of 100 events are allowed.
     * @param schedule Schedule CRON syntax.
     * @param timeout Maximum execution time in seconds.
     * @param enabled Is function enabled? When set to 'disabled', users cannot access the function but Server SDKs with and API key can still access the function. No data is lost when this is toggled.
     * @param logging When disabled, executions will exclude logs and errors, and will be slightly faster.
     * @param entrypoint Entrypoint File. This path is relative to the "providerRootDirectory".
     * @param commands Build Commands.
     * @param scopes List of scopes allowed for API Key auto-generated for every execution. Maximum of 100 scopes are allowed.
     * @param installationId Appwrite Installation ID for VCS (Version Controle System) deployment.
     * @param providerRepositoryId Repository ID of the repo linked to the function
     * @param providerBranch Production branch for the repo linked to the function
     * @param providerSilentMode Is the VCS (Version Control System) connection in silent mode for the repo linked to the function? In silent mode, comments will not be made on commits and pull requests.
     * @param providerRootDirectory Path to function code in the linked repo.
     * @param buildSpecification Build specification for the function deployments.
     * @param runtimeSpecification Runtime specification for the function executions.
     * @param deploymentRetention Days to keep non-active deployments before deletion. Value 0 means all deployments will be kept.
     * @return appwrite::Result<appwrite::models::Function>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::Function> update(        std::string_view functionId,         std::string_view name,         std::optional<appwrite::enums::Runtime> runtime = std::nullopt,         std::optional<std::vector<std::string>> execute = std::vector<std::string>{},         std::optional<std::vector<std::string>> events = std::vector<std::string>{},         std::optional<std::string_view> schedule = std::nullopt,         std::optional<int64_t> timeout = 15,         std::optional<bool> enabled = true,         std::optional<bool> logging = true,         std::optional<std::string_view> entrypoint = std::nullopt,         std::optional<std::string_view> commands = std::nullopt,         std::optional<std::vector<appwrite::enums::Scopes>> scopes = {},         std::optional<std::string_view> installationId = std::nullopt,         std::optional<std::string_view> providerRepositoryId = std::nullopt,         std::optional<std::string_view> providerBranch = std::nullopt,         std::optional<bool> providerSilentMode = false,         std::optional<std::string_view> providerRootDirectory = std::nullopt,         std::optional<std::string_view> buildSpecification = "[]",         std::optional<std::string_view> runtimeSpecification = "[]",         std::optional<int64_t> deploymentRetention = 0    ) {
                std::string path_ = std::format("/functions/{}", functionId);
        
        nlohmann::json params = nlohmann::json::object();
        params["name"] = std::string(name);
        if (runtime.has_value()) {
            params["runtime"] = *runtime;
        }
        if (execute.has_value()) {
            params["execute"] = *execute;
        }
        if (events.has_value()) {
            params["events"] = *events;
        }
        if (schedule.has_value()) {
            params["schedule"] = std::string(schedule.value());
        }
        if (timeout.has_value()) {
            params["timeout"] = *timeout;
        }
        if (enabled.has_value()) {
            params["enabled"] = *enabled;
        }
        if (logging.has_value()) {
            params["logging"] = *logging;
        }
        if (entrypoint.has_value()) {
            params["entrypoint"] = std::string(entrypoint.value());
        }
        if (commands.has_value()) {
            params["commands"] = std::string(commands.value());
        }
        if (scopes.has_value()) {
            params["scopes"] = *scopes;
        }
        if (installationId.has_value()) {
            params["installationId"] = std::string(installationId.value());
        }
        if (providerRepositoryId.has_value()) {
            params["providerRepositoryId"] = std::string(providerRepositoryId.value());
        }
        if (providerBranch.has_value()) {
            params["providerBranch"] = std::string(providerBranch.value());
        }
        if (providerSilentMode.has_value()) {
            params["providerSilentMode"] = *providerSilentMode;
        }
        if (providerRootDirectory.has_value()) {
            params["providerRootDirectory"] = std::string(providerRootDirectory.value());
        }
        if (buildSpecification.has_value()) {
            params["buildSpecification"] = std::string(buildSpecification.value());
        }
        if (runtimeSpecification.has_value()) {
            params["runtimeSpecification"] = std::string(runtimeSpecification.value());
        }
        if (deploymentRetention.has_value()) {
            params["deploymentRetention"] = *deploymentRetention;
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::Function>("PUT", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Update function by its unique ID.
     *
     * @param functionId Function ID.
     * @param name Function name. Max length: 128 chars.
     * @param runtime Execution runtime.
     * @param execute An array of role strings with execution permissions. By default no user is granted with any execute permissions. [learn more about roles](https://appwrite.io/docs/permissions#permission-roles). Maximum of 100 roles are allowed, each 64 characters long.
     * @param events Events list. Maximum of 100 events are allowed.
     * @param schedule Schedule CRON syntax.
     * @param timeout Maximum execution time in seconds.
     * @param enabled Is function enabled? When set to 'disabled', users cannot access the function but Server SDKs with and API key can still access the function. No data is lost when this is toggled.
     * @param logging When disabled, executions will exclude logs and errors, and will be slightly faster.
     * @param entrypoint Entrypoint File. This path is relative to the "providerRootDirectory".
     * @param commands Build Commands.
     * @param scopes List of scopes allowed for API Key auto-generated for every execution. Maximum of 100 scopes are allowed.
     * @param installationId Appwrite Installation ID for VCS (Version Controle System) deployment.
     * @param providerRepositoryId Repository ID of the repo linked to the function
     * @param providerBranch Production branch for the repo linked to the function
     * @param providerSilentMode Is the VCS (Version Control System) connection in silent mode for the repo linked to the function? In silent mode, comments will not be made on commits and pull requests.
     * @param providerRootDirectory Path to function code in the linked repo.
     * @param buildSpecification Build specification for the function deployments.
     * @param runtimeSpecification Runtime specification for the function executions.
     * @param deploymentRetention Days to keep non-active deployments before deletion. Value 0 means all deployments will be kept.
     * @return appwrite::Result<appwrite::models::Function>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::Function>> updateAsync(        std::string_view functionId,         std::string_view name,         std::optional<appwrite::enums::Runtime> runtime = std::nullopt,         std::optional<std::vector<std::string>> execute = std::vector<std::string>{},         std::optional<std::vector<std::string>> events = std::vector<std::string>{},         std::optional<std::string_view> schedule = std::nullopt,         std::optional<int64_t> timeout = 15,         std::optional<bool> enabled = true,         std::optional<bool> logging = true,         std::optional<std::string_view> entrypoint = std::nullopt,         std::optional<std::string_view> commands = std::nullopt,         std::optional<std::vector<appwrite::enums::Scopes>> scopes = {},         std::optional<std::string_view> installationId = std::nullopt,         std::optional<std::string_view> providerRepositoryId = std::nullopt,         std::optional<std::string_view> providerBranch = std::nullopt,         std::optional<bool> providerSilentMode = false,         std::optional<std::string_view> providerRootDirectory = std::nullopt,         std::optional<std::string_view> buildSpecification = "[]",         std::optional<std::string_view> runtimeSpecification = "[]",         std::optional<int64_t> deploymentRetention = 0    ) {
                std::string path_ = std::format("/functions/{}", functionId);
        
        nlohmann::json params = nlohmann::json::object();
        params["name"] = std::string(name);
        if (runtime.has_value()) {
            params["runtime"] = *runtime;
        }
        if (execute.has_value()) {
            params["execute"] = *execute;
        }
        if (events.has_value()) {
            params["events"] = *events;
        }
        if (schedule.has_value()) {
            params["schedule"] = std::string(schedule.value());
        }
        if (timeout.has_value()) {
            params["timeout"] = *timeout;
        }
        if (enabled.has_value()) {
            params["enabled"] = *enabled;
        }
        if (logging.has_value()) {
            params["logging"] = *logging;
        }
        if (entrypoint.has_value()) {
            params["entrypoint"] = std::string(entrypoint.value());
        }
        if (commands.has_value()) {
            params["commands"] = std::string(commands.value());
        }
        if (scopes.has_value()) {
            params["scopes"] = *scopes;
        }
        if (installationId.has_value()) {
            params["installationId"] = std::string(installationId.value());
        }
        if (providerRepositoryId.has_value()) {
            params["providerRepositoryId"] = std::string(providerRepositoryId.value());
        }
        if (providerBranch.has_value()) {
            params["providerBranch"] = std::string(providerBranch.value());
        }
        if (providerSilentMode.has_value()) {
            params["providerSilentMode"] = *providerSilentMode;
        }
        if (providerRootDirectory.has_value()) {
            params["providerRootDirectory"] = std::string(providerRootDirectory.value());
        }
        if (buildSpecification.has_value()) {
            params["buildSpecification"] = std::string(buildSpecification.value());
        }
        if (runtimeSpecification.has_value()) {
            params["runtimeSpecification"] = std::string(runtimeSpecification.value());
        }
        if (deploymentRetention.has_value()) {
            params["deploymentRetention"] = *deploymentRetention;
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::Function>("PUT", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Delete a function by its unique ID.
     *
     * @param functionId Function ID.
     * @return appwrite::Result<void>
     */
    [[nodiscard]] appwrite::Result<void> delete_(        std::string_view functionId    ) {
                std::string path_ = std::format("/functions/{}", functionId);
        
        nlohmann::json params = nlohmann::json::object();
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callVoid("DELETE", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Delete a function by its unique ID.
     *
     * @param functionId Function ID.
     * @return appwrite::Result<void>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<void>> delete_Async(        std::string_view functionId    ) {
                std::string path_ = std::format("/functions/{}", functionId);
        
        nlohmann::json params = nlohmann::json::object();
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callVoidAsync("DELETE", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Update the function active deployment. Use this endpoint to switch the code
     * deployment that should be used when visitor opens your function.
     *
     * @param functionId Function ID.
     * @param deploymentId Deployment ID.
     * @return appwrite::Result<appwrite::models::Function>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::Function> updateFunctionDeployment(        std::string_view functionId,         std::string_view deploymentId    ) {
                std::string path_ = std::format("/functions/{}/deployment", functionId);
        
        nlohmann::json params = nlohmann::json::object();
        params["deploymentId"] = std::string(deploymentId);
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::Function>("PATCH", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Update the function active deployment. Use this endpoint to switch the code
     * deployment that should be used when visitor opens your function.
     *
     * @param functionId Function ID.
     * @param deploymentId Deployment ID.
     * @return appwrite::Result<appwrite::models::Function>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::Function>> updateFunctionDeploymentAsync(        std::string_view functionId,         std::string_view deploymentId    ) {
                std::string path_ = std::format("/functions/{}/deployment", functionId);
        
        nlohmann::json params = nlohmann::json::object();
        params["deploymentId"] = std::string(deploymentId);
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::Function>("PATCH", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Get a list of all the function's code deployments. You can use the query
     * params to filter your results.
     *
     * @param functionId Function ID.
     * @param queries Array of query strings generated using the Query class provided by the SDK. [Learn more about queries](https://appwrite.io/docs/queries). Maximum of 100 queries are allowed, each 4096 characters long. You may filter on the following attributes: buildSize, sourceSize, totalSize, buildDuration, status, activate, type
     * @param search Search term to filter your list results. Max length: 256 chars.
     * @param total When set to false, the total count returned will be 0 and will not be calculated.
     * @return appwrite::Result<appwrite::models::DeploymentList>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::DeploymentList> listDeployments(        std::string_view functionId,         std::optional<std::vector<std::string>> queries = std::vector<std::string>{},         std::optional<std::string_view> search = std::nullopt,         std::optional<bool> total = true    ) {
                std::string path_ = std::format("/functions/{}/deployments", functionId);
        
        nlohmann::json params = nlohmann::json::object();
        if (queries.has_value()) {
            params["queries"] = *queries;
        }
        if (search.has_value()) {
            params["search"] = std::string(search.value());
        }
        if (total.has_value()) {
            params["total"] = *total;
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::DeploymentList>("GET", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Get a list of all the function's code deployments. You can use the query
     * params to filter your results.
     *
     * @param functionId Function ID.
     * @param queries Array of query strings generated using the Query class provided by the SDK. [Learn more about queries](https://appwrite.io/docs/queries). Maximum of 100 queries are allowed, each 4096 characters long. You may filter on the following attributes: buildSize, sourceSize, totalSize, buildDuration, status, activate, type
     * @param search Search term to filter your list results. Max length: 256 chars.
     * @param total When set to false, the total count returned will be 0 and will not be calculated.
     * @return appwrite::Result<appwrite::models::DeploymentList>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::DeploymentList>> listDeploymentsAsync(        std::string_view functionId,         std::optional<std::vector<std::string>> queries = std::vector<std::string>{},         std::optional<std::string_view> search = std::nullopt,         std::optional<bool> total = true    ) {
                std::string path_ = std::format("/functions/{}/deployments", functionId);
        
        nlohmann::json params = nlohmann::json::object();
        if (queries.has_value()) {
            params["queries"] = *queries;
        }
        if (search.has_value()) {
            params["search"] = std::string(search.value());
        }
        if (total.has_value()) {
            params["total"] = *total;
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::DeploymentList>("GET", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Create a new function code deployment. Use this endpoint to upload a new
     * version of your code function. To execute your newly uploaded code, you'll
     * need to update the function's deployment to use your new deployment
     * UID.

This endpoint accepts a tar.gz file compressed with your code. Make
     * sure to include any dependencies your code has within the compressed file.
     * You can learn more about code packaging in the [Appwrite Cloud Functions
     * tutorial](https://appwrite.io/docs/functions).

Use the "command" param to
     * set the entrypoint used to execute your code.
     *
     * @param functionId Function ID.
     * @param code Gzip file with your code package. When used with the Appwrite CLI, pass the path to your code directory, and the CLI will automatically package your code. Use a path that is within the current directory.
     * @param activate Automatically activate the deployment when it is finished building.
     * @param entrypoint Entrypoint File.
     * @param commands Build Commands.
     * @return appwrite::Result<appwrite::models::Deployment>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::Deployment> createDeployment(        std::string_view functionId,         appwrite::InputFile code,         bool activate,         std::optional<std::string_view> entrypoint = std::nullopt,         std::optional<std::string_view> commands = std::nullopt        , Client::ProgressCallback onProgress = nullptr    ) {
                std::string path_ = std::format("/functions/{}/deployments", functionId);
        
        nlohmann::json params = nlohmann::json::object();
        if (entrypoint.has_value()) {
            params["entrypoint"] = std::string(entrypoint.value());
        }
        if (commands.has_value()) {
            params["commands"] = std::string(commands.value());
        }
        params["activate"] = activate;
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.fileUpload<appwrite::models::Deployment>("POST", path_, "code", std::move(code), std::move(local_headers_), std::move(params), std::move(onProgress));
    }

    /**
     * Create a new function code deployment. Use this endpoint to upload a new
     * version of your code function. To execute your newly uploaded code, you'll
     * need to update the function's deployment to use your new deployment
     * UID.

This endpoint accepts a tar.gz file compressed with your code. Make
     * sure to include any dependencies your code has within the compressed file.
     * You can learn more about code packaging in the [Appwrite Cloud Functions
     * tutorial](https://appwrite.io/docs/functions).

Use the "command" param to
     * set the entrypoint used to execute your code.
     *
     * @param functionId Function ID.
     * @param code Gzip file with your code package. When used with the Appwrite CLI, pass the path to your code directory, and the CLI will automatically package your code. Use a path that is within the current directory.
     * @param activate Automatically activate the deployment when it is finished building.
     * @param entrypoint Entrypoint File.
     * @param commands Build Commands.
     * @return appwrite::Result<appwrite::models::Deployment>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::Deployment>> createDeploymentAsync(        std::string_view functionId,         appwrite::InputFile code,         bool activate,         std::optional<std::string_view> entrypoint = std::nullopt,         std::optional<std::string_view> commands = std::nullopt        , Client::ProgressCallback onProgress = nullptr    ) {
                std::string path_ = std::format("/functions/{}/deployments", functionId);
        
        nlohmann::json params = nlohmann::json::object();
        if (entrypoint.has_value()) {
            params["entrypoint"] = std::string(entrypoint.value());
        }
        if (commands.has_value()) {
            params["commands"] = std::string(commands.value());
        }
        params["activate"] = activate;
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.fileUploadAsync<appwrite::models::Deployment>("POST", path_, "code", std::move(code), std::move(local_headers_), std::move(params), std::move(onProgress));
    }
    /**
     * Create a new build for an existing function deployment. This endpoint
     * allows you to rebuild a deployment with the updated function configuration,
     * including its entrypoint and build commands if they have been modified. The
     * build process will be queued and executed asynchronously. The original
     * deployment's code will be preserved and used for the new build.
     *
     * @param functionId Function ID.
     * @param deploymentId Deployment ID.
     * @param buildId Build unique ID.
     * @return appwrite::Result<appwrite::models::Deployment>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::Deployment> createDuplicateDeployment(        std::string_view functionId,         std::string_view deploymentId,         std::optional<std::string_view> buildId = std::nullopt    ) {
                std::string path_ = std::format("/functions/{}/deployments/duplicate", functionId);
        
        nlohmann::json params = nlohmann::json::object();
        params["deploymentId"] = std::string(deploymentId);
        if (buildId.has_value()) {
            params["buildId"] = std::string(buildId.value());
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::Deployment>("POST", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Create a new build for an existing function deployment. This endpoint
     * allows you to rebuild a deployment with the updated function configuration,
     * including its entrypoint and build commands if they have been modified. The
     * build process will be queued and executed asynchronously. The original
     * deployment's code will be preserved and used for the new build.
     *
     * @param functionId Function ID.
     * @param deploymentId Deployment ID.
     * @param buildId Build unique ID.
     * @return appwrite::Result<appwrite::models::Deployment>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::Deployment>> createDuplicateDeploymentAsync(        std::string_view functionId,         std::string_view deploymentId,         std::optional<std::string_view> buildId = std::nullopt    ) {
                std::string path_ = std::format("/functions/{}/deployments/duplicate", functionId);
        
        nlohmann::json params = nlohmann::json::object();
        params["deploymentId"] = std::string(deploymentId);
        if (buildId.has_value()) {
            params["buildId"] = std::string(buildId.value());
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::Deployment>("POST", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Create a deployment based on a template.

Use this endpoint with
     * combination of
     * [listTemplates](https://appwrite.io/docs/products/functions/templates) to
     * find the template details.
     *
     * @param functionId Function ID.
     * @param repository Repository name of the template.
     * @param owner The name of the owner of the template.
     * @param rootDirectory Path to function code in the template repo.
     * @param type Type for the reference provided. Can be commit, branch, or tag
     * @param reference Reference value, can be a commit hash, branch name, or release tag
     * @param activate Automatically activate the deployment when it is finished building.
     * @return appwrite::Result<appwrite::models::Deployment>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::Deployment> createTemplateDeployment(        std::string_view functionId,         std::string_view repository,         std::string_view owner,         std::string_view rootDirectory,         appwrite::enums::TemplateReferenceType type,         std::string_view reference,         std::optional<bool> activate = false    ) {
                std::string path_ = std::format("/functions/{}/deployments/template", functionId);
        
        nlohmann::json params = nlohmann::json::object();
        params["repository"] = std::string(repository);
        params["owner"] = std::string(owner);
        params["rootDirectory"] = std::string(rootDirectory);
        params["type"] = type;
        params["reference"] = std::string(reference);
        if (activate.has_value()) {
            params["activate"] = *activate;
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::Deployment>("POST", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Create a deployment based on a template.

Use this endpoint with
     * combination of
     * [listTemplates](https://appwrite.io/docs/products/functions/templates) to
     * find the template details.
     *
     * @param functionId Function ID.
     * @param repository Repository name of the template.
     * @param owner The name of the owner of the template.
     * @param rootDirectory Path to function code in the template repo.
     * @param type Type for the reference provided. Can be commit, branch, or tag
     * @param reference Reference value, can be a commit hash, branch name, or release tag
     * @param activate Automatically activate the deployment when it is finished building.
     * @return appwrite::Result<appwrite::models::Deployment>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::Deployment>> createTemplateDeploymentAsync(        std::string_view functionId,         std::string_view repository,         std::string_view owner,         std::string_view rootDirectory,         appwrite::enums::TemplateReferenceType type,         std::string_view reference,         std::optional<bool> activate = false    ) {
                std::string path_ = std::format("/functions/{}/deployments/template", functionId);
        
        nlohmann::json params = nlohmann::json::object();
        params["repository"] = std::string(repository);
        params["owner"] = std::string(owner);
        params["rootDirectory"] = std::string(rootDirectory);
        params["type"] = type;
        params["reference"] = std::string(reference);
        if (activate.has_value()) {
            params["activate"] = *activate;
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::Deployment>("POST", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Create a deployment when a function is connected to VCS.

This endpoint
     * lets you create deployment from a branch, commit, or a tag.
     *
     * @param functionId Function ID.
     * @param type Type of reference passed. Allowed values are: branch, commit
     * @param reference VCS reference to create deployment from. Depending on type this can be: branch name, commit hash
     * @param activate Automatically activate the deployment when it is finished building.
     * @return appwrite::Result<appwrite::models::Deployment>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::Deployment> createVcsDeployment(        std::string_view functionId,         appwrite::enums::VCSReferenceType type,         std::string_view reference,         std::optional<bool> activate = false    ) {
                std::string path_ = std::format("/functions/{}/deployments/vcs", functionId);
        
        nlohmann::json params = nlohmann::json::object();
        params["type"] = type;
        params["reference"] = std::string(reference);
        if (activate.has_value()) {
            params["activate"] = *activate;
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::Deployment>("POST", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Create a deployment when a function is connected to VCS.

This endpoint
     * lets you create deployment from a branch, commit, or a tag.
     *
     * @param functionId Function ID.
     * @param type Type of reference passed. Allowed values are: branch, commit
     * @param reference VCS reference to create deployment from. Depending on type this can be: branch name, commit hash
     * @param activate Automatically activate the deployment when it is finished building.
     * @return appwrite::Result<appwrite::models::Deployment>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::Deployment>> createVcsDeploymentAsync(        std::string_view functionId,         appwrite::enums::VCSReferenceType type,         std::string_view reference,         std::optional<bool> activate = false    ) {
                std::string path_ = std::format("/functions/{}/deployments/vcs", functionId);
        
        nlohmann::json params = nlohmann::json::object();
        params["type"] = type;
        params["reference"] = std::string(reference);
        if (activate.has_value()) {
            params["activate"] = *activate;
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::Deployment>("POST", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Get a function deployment by its unique ID.
     *
     * @param functionId Function ID.
     * @param deploymentId Deployment ID.
     * @return appwrite::Result<appwrite::models::Deployment>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::Deployment> getDeployment(        std::string_view functionId,         std::string_view deploymentId    ) {
                std::string path_ = std::format("/functions/{}/deployments/{}", functionId, deploymentId);
        
        nlohmann::json params = nlohmann::json::object();
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::Deployment>("GET", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Get a function deployment by its unique ID.
     *
     * @param functionId Function ID.
     * @param deploymentId Deployment ID.
     * @return appwrite::Result<appwrite::models::Deployment>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::Deployment>> getDeploymentAsync(        std::string_view functionId,         std::string_view deploymentId    ) {
                std::string path_ = std::format("/functions/{}/deployments/{}", functionId, deploymentId);
        
        nlohmann::json params = nlohmann::json::object();
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::Deployment>("GET", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Delete a code deployment by its unique ID.
     *
     * @param functionId Function ID.
     * @param deploymentId Deployment ID.
     * @return appwrite::Result<void>
     */
    [[nodiscard]] appwrite::Result<void> deleteDeployment(        std::string_view functionId,         std::string_view deploymentId    ) {
                std::string path_ = std::format("/functions/{}/deployments/{}", functionId, deploymentId);
        
        nlohmann::json params = nlohmann::json::object();
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callVoid("DELETE", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Delete a code deployment by its unique ID.
     *
     * @param functionId Function ID.
     * @param deploymentId Deployment ID.
     * @return appwrite::Result<void>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<void>> deleteDeploymentAsync(        std::string_view functionId,         std::string_view deploymentId    ) {
                std::string path_ = std::format("/functions/{}/deployments/{}", functionId, deploymentId);
        
        nlohmann::json params = nlohmann::json::object();
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callVoidAsync("DELETE", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Get a function deployment content by its unique ID. The endpoint response
     * return with a 'Content-Disposition: attachment' header that tells the
     * browser to start downloading the file to user downloads directory.
     *
     * @param functionId Function ID.
     * @param deploymentId Deployment ID.
     * @param type Deployment file to download. Can be: "source", "output".
     * @return appwrite::Result<BinaryResponse>
     */
    [[nodiscard]] appwrite::Result<BinaryResponse> getDeploymentDownload(        std::string_view functionId,         std::string_view deploymentId,         std::optional<appwrite::enums::DeploymentDownloadType> type = appwrite::enums::DeploymentDownloadType::SOURCE    ) {
                std::string path_ = std::format("/functions/{}/deployments/{}/download", functionId, deploymentId);
        
        nlohmann::json params = nlohmann::json::object();
        if (type.has_value()) {
            params["type"] = *type;
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callBytes("GET", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Get a function deployment content by its unique ID. The endpoint response
     * return with a 'Content-Disposition: attachment' header that tells the
     * browser to start downloading the file to user downloads directory.
     *
     * @param functionId Function ID.
     * @param deploymentId Deployment ID.
     * @param type Deployment file to download. Can be: "source", "output".
     * @return appwrite::Result<BinaryResponse>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<BinaryResponse>> getDeploymentDownloadAsync(        std::string_view functionId,         std::string_view deploymentId,         std::optional<appwrite::enums::DeploymentDownloadType> type = appwrite::enums::DeploymentDownloadType::SOURCE    ) {
                std::string path_ = std::format("/functions/{}/deployments/{}/download", functionId, deploymentId);
        
        nlohmann::json params = nlohmann::json::object();
        if (type.has_value()) {
            params["type"] = *type;
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callBytesAsync("GET", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Cancel an ongoing function deployment build. If the build is already in
     * progress, it will be stopped and marked as canceled. If the build hasn't
     * started yet, it will be marked as canceled without executing. You cannot
     * cancel builds that have already completed (status 'ready') or failed. The
     * response includes the final build status and details.
     *
     * @param functionId Function ID.
     * @param deploymentId Deployment ID.
     * @return appwrite::Result<appwrite::models::Deployment>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::Deployment> updateDeploymentStatus(        std::string_view functionId,         std::string_view deploymentId    ) {
                std::string path_ = std::format("/functions/{}/deployments/{}/status", functionId, deploymentId);
        
        nlohmann::json params = nlohmann::json::object();
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::Deployment>("PATCH", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Cancel an ongoing function deployment build. If the build is already in
     * progress, it will be stopped and marked as canceled. If the build hasn't
     * started yet, it will be marked as canceled without executing. You cannot
     * cancel builds that have already completed (status 'ready') or failed. The
     * response includes the final build status and details.
     *
     * @param functionId Function ID.
     * @param deploymentId Deployment ID.
     * @return appwrite::Result<appwrite::models::Deployment>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::Deployment>> updateDeploymentStatusAsync(        std::string_view functionId,         std::string_view deploymentId    ) {
                std::string path_ = std::format("/functions/{}/deployments/{}/status", functionId, deploymentId);
        
        nlohmann::json params = nlohmann::json::object();
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::Deployment>("PATCH", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Get a list of all the current user function execution logs. You can use the
     * query params to filter your results.
     *
     * @param functionId Function ID.
     * @param queries Array of query strings generated using the Query class provided by the SDK. [Learn more about queries](https://appwrite.io/docs/queries). Maximum of 100 queries are allowed, each 4096 characters long. You may filter on the following attributes: trigger, status, responseStatusCode, duration, requestMethod, requestPath, deploymentId
     * @param total When set to false, the total count returned will be 0 and will not be calculated.
     * @return appwrite::Result<appwrite::models::ExecutionList>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::ExecutionList> listExecutions(        std::string_view functionId,         std::optional<std::vector<std::string>> queries = std::vector<std::string>{},         std::optional<bool> total = true    ) {
                std::string path_ = std::format("/functions/{}/executions", functionId);
        
        nlohmann::json params = nlohmann::json::object();
        if (queries.has_value()) {
            params["queries"] = *queries;
        }
        if (total.has_value()) {
            params["total"] = *total;
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::ExecutionList>("GET", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Get a list of all the current user function execution logs. You can use the
     * query params to filter your results.
     *
     * @param functionId Function ID.
     * @param queries Array of query strings generated using the Query class provided by the SDK. [Learn more about queries](https://appwrite.io/docs/queries). Maximum of 100 queries are allowed, each 4096 characters long. You may filter on the following attributes: trigger, status, responseStatusCode, duration, requestMethod, requestPath, deploymentId
     * @param total When set to false, the total count returned will be 0 and will not be calculated.
     * @return appwrite::Result<appwrite::models::ExecutionList>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::ExecutionList>> listExecutionsAsync(        std::string_view functionId,         std::optional<std::vector<std::string>> queries = std::vector<std::string>{},         std::optional<bool> total = true    ) {
                std::string path_ = std::format("/functions/{}/executions", functionId);
        
        nlohmann::json params = nlohmann::json::object();
        if (queries.has_value()) {
            params["queries"] = *queries;
        }
        if (total.has_value()) {
            params["total"] = *total;
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::ExecutionList>("GET", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Trigger a function execution. The returned object will return you the
     * current execution status. You can ping the `Get Execution` endpoint to get
     * updates on the current execution status. Once this endpoint is called, your
     * function execution process will start asynchronously.
     *
     * @param functionId Function ID.
     * @param body HTTP body of execution. Default value is empty string.
     * @param async Execute code in the background. Default value is false.
     * @param path HTTP path of execution. Path can include query params. Default value is /
     * @param method HTTP method of execution. Default value is POST.
     * @param headers HTTP headers of execution. Defaults to empty.
     * @param scheduledAt Scheduled execution time in [ISO 8601](https://www.iso.org/iso-8601-date-and-time-format.html) format. DateTime value must be in future with precision in minutes.
     * @return appwrite::Result<appwrite::models::Execution>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::Execution> createExecution(        std::string_view functionId,         std::optional<std::string_view> body = std::nullopt,         std::optional<bool> async = false,         std::optional<std::string_view> path = "/",         std::optional<appwrite::enums::ExecutionMethod> method = appwrite::enums::ExecutionMethod::POST,         std::optional<nlohmann::json> headers = nlohmann::json::object(),         std::optional<std::string_view> scheduledAt = std::nullopt    ) {
                std::string path_ = std::format("/functions/{}/executions", functionId);
        
        nlohmann::json params = nlohmann::json::object();
        if (body.has_value()) {
            params["body"] = std::string(body.value());
        }
        if (async.has_value()) {
            params["async"] = *async;
        }
        if (path.has_value()) {
            params["path"] = std::string(path.value());
        }
        if (method.has_value()) {
            params["method"] = *method;
        }
        if (headers.has_value()) {
            params["headers"] = *headers;
        }
        if (scheduledAt.has_value()) {
            params["scheduledAt"] = std::string(scheduledAt.value());
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::Execution>("POST", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Trigger a function execution. The returned object will return you the
     * current execution status. You can ping the `Get Execution` endpoint to get
     * updates on the current execution status. Once this endpoint is called, your
     * function execution process will start asynchronously.
     *
     * @param functionId Function ID.
     * @param body HTTP body of execution. Default value is empty string.
     * @param async Execute code in the background. Default value is false.
     * @param path HTTP path of execution. Path can include query params. Default value is /
     * @param method HTTP method of execution. Default value is POST.
     * @param headers HTTP headers of execution. Defaults to empty.
     * @param scheduledAt Scheduled execution time in [ISO 8601](https://www.iso.org/iso-8601-date-and-time-format.html) format. DateTime value must be in future with precision in minutes.
     * @return appwrite::Result<appwrite::models::Execution>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::Execution>> createExecutionAsync(        std::string_view functionId,         std::optional<std::string_view> body = std::nullopt,         std::optional<bool> async = false,         std::optional<std::string_view> path = "/",         std::optional<appwrite::enums::ExecutionMethod> method = appwrite::enums::ExecutionMethod::POST,         std::optional<nlohmann::json> headers = nlohmann::json::object(),         std::optional<std::string_view> scheduledAt = std::nullopt    ) {
                std::string path_ = std::format("/functions/{}/executions", functionId);
        
        nlohmann::json params = nlohmann::json::object();
        if (body.has_value()) {
            params["body"] = std::string(body.value());
        }
        if (async.has_value()) {
            params["async"] = *async;
        }
        if (path.has_value()) {
            params["path"] = std::string(path.value());
        }
        if (method.has_value()) {
            params["method"] = *method;
        }
        if (headers.has_value()) {
            params["headers"] = *headers;
        }
        if (scheduledAt.has_value()) {
            params["scheduledAt"] = std::string(scheduledAt.value());
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::Execution>("POST", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Get a function execution log by its unique ID.
     *
     * @param functionId Function ID.
     * @param executionId Execution ID.
     * @return appwrite::Result<appwrite::models::Execution>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::Execution> getExecution(        std::string_view functionId,         std::string_view executionId    ) {
                std::string path_ = std::format("/functions/{}/executions/{}", functionId, executionId);
        
        nlohmann::json params = nlohmann::json::object();
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::Execution>("GET", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Get a function execution log by its unique ID.
     *
     * @param functionId Function ID.
     * @param executionId Execution ID.
     * @return appwrite::Result<appwrite::models::Execution>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::Execution>> getExecutionAsync(        std::string_view functionId,         std::string_view executionId    ) {
                std::string path_ = std::format("/functions/{}/executions/{}", functionId, executionId);
        
        nlohmann::json params = nlohmann::json::object();
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::Execution>("GET", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Delete a function execution by its unique ID.
     *
     * @param functionId Function ID.
     * @param executionId Execution ID.
     * @return appwrite::Result<void>
     */
    [[nodiscard]] appwrite::Result<void> deleteExecution(        std::string_view functionId,         std::string_view executionId    ) {
                std::string path_ = std::format("/functions/{}/executions/{}", functionId, executionId);
        
        nlohmann::json params = nlohmann::json::object();
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callVoid("DELETE", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Delete a function execution by its unique ID.
     *
     * @param functionId Function ID.
     * @param executionId Execution ID.
     * @return appwrite::Result<void>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<void>> deleteExecutionAsync(        std::string_view functionId,         std::string_view executionId    ) {
                std::string path_ = std::format("/functions/{}/executions/{}", functionId, executionId);
        
        nlohmann::json params = nlohmann::json::object();
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callVoidAsync("DELETE", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Get a list of all variables of a specific function.
     *
     * @param functionId Function unique ID.
     * @return appwrite::Result<appwrite::models::VariableList>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::VariableList> listVariables(        std::string_view functionId    ) {
                std::string path_ = std::format("/functions/{}/variables", functionId);
        
        nlohmann::json params = nlohmann::json::object();
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::VariableList>("GET", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Get a list of all variables of a specific function.
     *
     * @param functionId Function unique ID.
     * @return appwrite::Result<appwrite::models::VariableList>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::VariableList>> listVariablesAsync(        std::string_view functionId    ) {
                std::string path_ = std::format("/functions/{}/variables", functionId);
        
        nlohmann::json params = nlohmann::json::object();
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::VariableList>("GET", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Create a new function environment variable. These variables can be accessed
     * in the function at runtime as environment variables.
     *
     * @param functionId Function unique ID.
     * @param key Variable key. Max length: 255 chars.
     * @param value Variable value. Max length: 8192 chars.
     * @param secret Secret variables can be updated or deleted, but only functions can read them during build and runtime.
     * @return appwrite::Result<appwrite::models::Variable>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::Variable> createVariable(        std::string_view functionId,         std::string_view key,         std::string_view value,         std::optional<bool> secret = true    ) {
                std::string path_ = std::format("/functions/{}/variables", functionId);
        
        nlohmann::json params = nlohmann::json::object();
        params["key"] = std::string(key);
        params["value"] = std::string(value);
        if (secret.has_value()) {
            params["secret"] = *secret;
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::Variable>("POST", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Create a new function environment variable. These variables can be accessed
     * in the function at runtime as environment variables.
     *
     * @param functionId Function unique ID.
     * @param key Variable key. Max length: 255 chars.
     * @param value Variable value. Max length: 8192 chars.
     * @param secret Secret variables can be updated or deleted, but only functions can read them during build and runtime.
     * @return appwrite::Result<appwrite::models::Variable>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::Variable>> createVariableAsync(        std::string_view functionId,         std::string_view key,         std::string_view value,         std::optional<bool> secret = true    ) {
                std::string path_ = std::format("/functions/{}/variables", functionId);
        
        nlohmann::json params = nlohmann::json::object();
        params["key"] = std::string(key);
        params["value"] = std::string(value);
        if (secret.has_value()) {
            params["secret"] = *secret;
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::Variable>("POST", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Get a variable by its unique ID.
     *
     * @param functionId Function unique ID.
     * @param variableId Variable unique ID.
     * @return appwrite::Result<appwrite::models::Variable>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::Variable> getVariable(        std::string_view functionId,         std::string_view variableId    ) {
                std::string path_ = std::format("/functions/{}/variables/{}", functionId, variableId);
        
        nlohmann::json params = nlohmann::json::object();
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::Variable>("GET", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Get a variable by its unique ID.
     *
     * @param functionId Function unique ID.
     * @param variableId Variable unique ID.
     * @return appwrite::Result<appwrite::models::Variable>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::Variable>> getVariableAsync(        std::string_view functionId,         std::string_view variableId    ) {
                std::string path_ = std::format("/functions/{}/variables/{}", functionId, variableId);
        
        nlohmann::json params = nlohmann::json::object();
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::Variable>("GET", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Update variable by its unique ID.
     *
     * @param functionId Function unique ID.
     * @param variableId Variable unique ID.
     * @param key Variable key. Max length: 255 chars.
     * @param value Variable value. Max length: 8192 chars.
     * @param secret Secret variables can be updated or deleted, but only functions can read them during build and runtime.
     * @return appwrite::Result<appwrite::models::Variable>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::Variable> updateVariable(        std::string_view functionId,         std::string_view variableId,         std::string_view key,         std::optional<std::string_view> value = std::nullopt,         std::optional<bool> secret = std::nullopt    ) {
                std::string path_ = std::format("/functions/{}/variables/{}", functionId, variableId);
        
        nlohmann::json params = nlohmann::json::object();
        params["key"] = std::string(key);
        if (value.has_value()) {
            params["value"] = std::string(value.value());
        }
        if (secret.has_value()) {
            params["secret"] = *secret;
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::Variable>("PUT", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Update variable by its unique ID.
     *
     * @param functionId Function unique ID.
     * @param variableId Variable unique ID.
     * @param key Variable key. Max length: 255 chars.
     * @param value Variable value. Max length: 8192 chars.
     * @param secret Secret variables can be updated or deleted, but only functions can read them during build and runtime.
     * @return appwrite::Result<appwrite::models::Variable>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::Variable>> updateVariableAsync(        std::string_view functionId,         std::string_view variableId,         std::string_view key,         std::optional<std::string_view> value = std::nullopt,         std::optional<bool> secret = std::nullopt    ) {
                std::string path_ = std::format("/functions/{}/variables/{}", functionId, variableId);
        
        nlohmann::json params = nlohmann::json::object();
        params["key"] = std::string(key);
        if (value.has_value()) {
            params["value"] = std::string(value.value());
        }
        if (secret.has_value()) {
            params["secret"] = *secret;
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::Variable>("PUT", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Delete a variable by its unique ID.
     *
     * @param functionId Function unique ID.
     * @param variableId Variable unique ID.
     * @return appwrite::Result<void>
     */
    [[nodiscard]] appwrite::Result<void> deleteVariable(        std::string_view functionId,         std::string_view variableId    ) {
                std::string path_ = std::format("/functions/{}/variables/{}", functionId, variableId);
        
        nlohmann::json params = nlohmann::json::object();
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callVoid("DELETE", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Delete a variable by its unique ID.
     *
     * @param functionId Function unique ID.
     * @param variableId Variable unique ID.
     * @return appwrite::Result<void>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<void>> deleteVariableAsync(        std::string_view functionId,         std::string_view variableId    ) {
                std::string path_ = std::format("/functions/{}/variables/{}", functionId, variableId);
        
        nlohmann::json params = nlohmann::json::object();
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callVoidAsync("DELETE", path_, std::move(local_headers_), std::move(params));
    }
};

/**
 * @brief The Storage service allows you to manage your project files.
 */
class Storage : public Service {
public:
    explicit Storage(Client& client) : Service(client) {}

    /**
     * Get a list of all the storage buckets. You can use the query params to
     * filter your results.
     *
     * @param queries Array of query strings generated using the Query class provided by the SDK. [Learn more about queries](https://appwrite.io/docs/queries). Maximum of 100 queries are allowed, each 4096 characters long. You may filter on the following attributes: enabled, name, fileSecurity, maximumFileSize, encryption, antivirus, transformations
     * @param search Search term to filter your list results. Max length: 256 chars.
     * @param total When set to false, the total count returned will be 0 and will not be calculated.
     * @return appwrite::Result<appwrite::models::BucketList>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::BucketList> listBuckets(        std::optional<std::vector<std::string>> queries = std::vector<std::string>{},         std::optional<std::string_view> search = std::nullopt,         std::optional<bool> total = true    ) {
                std::string path_ = std::format("/storage/buckets");
        
        nlohmann::json params = nlohmann::json::object();
        if (queries.has_value()) {
            params["queries"] = *queries;
        }
        if (search.has_value()) {
            params["search"] = std::string(search.value());
        }
        if (total.has_value()) {
            params["total"] = *total;
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::BucketList>("GET", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Get a list of all the storage buckets. You can use the query params to
     * filter your results.
     *
     * @param queries Array of query strings generated using the Query class provided by the SDK. [Learn more about queries](https://appwrite.io/docs/queries). Maximum of 100 queries are allowed, each 4096 characters long. You may filter on the following attributes: enabled, name, fileSecurity, maximumFileSize, encryption, antivirus, transformations
     * @param search Search term to filter your list results. Max length: 256 chars.
     * @param total When set to false, the total count returned will be 0 and will not be calculated.
     * @return appwrite::Result<appwrite::models::BucketList>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::BucketList>> listBucketsAsync(        std::optional<std::vector<std::string>> queries = std::vector<std::string>{},         std::optional<std::string_view> search = std::nullopt,         std::optional<bool> total = true    ) {
                std::string path_ = std::format("/storage/buckets");
        
        nlohmann::json params = nlohmann::json::object();
        if (queries.has_value()) {
            params["queries"] = *queries;
        }
        if (search.has_value()) {
            params["search"] = std::string(search.value());
        }
        if (total.has_value()) {
            params["total"] = *total;
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::BucketList>("GET", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Create a new storage bucket.
     *
     * @param bucketId Unique Id. Choose a custom ID or generate a random ID with `ID.unique()`. Valid chars are a-z, A-Z, 0-9, period, hyphen, and underscore. Can't start with a special char. Max length is 36 chars.
     * @param name Bucket name
     * @param permissions An array of permission strings. By default, no user is granted with any permissions. [Learn more about permissions](https://appwrite.io/docs/permissions).
     * @param fileSecurity Enables configuring permissions for individual file. A user needs one of file or bucket level permissions to access a file. [Learn more about permissions](https://appwrite.io/docs/permissions).
     * @param enabled Is bucket enabled? When set to 'disabled', users cannot access the files in this bucket but Server SDKs with and API key can still access the bucket. No files are lost when this is toggled.
     * @param maximumFileSize Maximum file size allowed in bytes. Maximum allowed value is 5GB.
     * @param allowedFileExtensions Allowed file extensions. Maximum of 100 extensions are allowed, each 64 characters long.
     * @param compression Compression algorithm chosen for compression. Can be one of none,  [gzip](https://en.wikipedia.org/wiki/Gzip), or [zstd](https://en.wikipedia.org/wiki/Zstd), For file size above 20MB compression is skipped even if it's enabled
     * @param encryption Is encryption enabled? For file size above 20MB encryption is skipped even if it's enabled
     * @param antivirus Is virus scanning enabled? For file size above 20MB AntiVirus scanning is skipped even if it's enabled
     * @param transformations Are image transformations enabled?
     * @return appwrite::Result<appwrite::models::Bucket>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::Bucket> createBucket(        std::string_view bucketId,         std::string_view name,         std::optional<std::vector<std::string>> permissions = std::nullopt,         std::optional<bool> fileSecurity = false,         std::optional<bool> enabled = true,         std::optional<int64_t> maximumFileSize = 0,         std::optional<std::vector<std::string>> allowedFileExtensions = std::vector<std::string>{},         std::optional<appwrite::enums::Compression> compression = appwrite::enums::Compression::NONE,         std::optional<bool> encryption = true,         std::optional<bool> antivirus = true,         std::optional<bool> transformations = true    ) {
                std::string path_ = std::format("/storage/buckets");
        
        nlohmann::json params = nlohmann::json::object();
        params["bucketId"] = std::string(bucketId);
        params["name"] = std::string(name);
        if (permissions.has_value()) {
            params["permissions"] = *permissions;
        }
        if (fileSecurity.has_value()) {
            params["fileSecurity"] = *fileSecurity;
        }
        if (enabled.has_value()) {
            params["enabled"] = *enabled;
        }
        if (maximumFileSize.has_value()) {
            params["maximumFileSize"] = *maximumFileSize;
        }
        if (allowedFileExtensions.has_value()) {
            params["allowedFileExtensions"] = *allowedFileExtensions;
        }
        if (compression.has_value()) {
            params["compression"] = *compression;
        }
        if (encryption.has_value()) {
            params["encryption"] = *encryption;
        }
        if (antivirus.has_value()) {
            params["antivirus"] = *antivirus;
        }
        if (transformations.has_value()) {
            params["transformations"] = *transformations;
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::Bucket>("POST", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Create a new storage bucket.
     *
     * @param bucketId Unique Id. Choose a custom ID or generate a random ID with `ID.unique()`. Valid chars are a-z, A-Z, 0-9, period, hyphen, and underscore. Can't start with a special char. Max length is 36 chars.
     * @param name Bucket name
     * @param permissions An array of permission strings. By default, no user is granted with any permissions. [Learn more about permissions](https://appwrite.io/docs/permissions).
     * @param fileSecurity Enables configuring permissions for individual file. A user needs one of file or bucket level permissions to access a file. [Learn more about permissions](https://appwrite.io/docs/permissions).
     * @param enabled Is bucket enabled? When set to 'disabled', users cannot access the files in this bucket but Server SDKs with and API key can still access the bucket. No files are lost when this is toggled.
     * @param maximumFileSize Maximum file size allowed in bytes. Maximum allowed value is 5GB.
     * @param allowedFileExtensions Allowed file extensions. Maximum of 100 extensions are allowed, each 64 characters long.
     * @param compression Compression algorithm chosen for compression. Can be one of none,  [gzip](https://en.wikipedia.org/wiki/Gzip), or [zstd](https://en.wikipedia.org/wiki/Zstd), For file size above 20MB compression is skipped even if it's enabled
     * @param encryption Is encryption enabled? For file size above 20MB encryption is skipped even if it's enabled
     * @param antivirus Is virus scanning enabled? For file size above 20MB AntiVirus scanning is skipped even if it's enabled
     * @param transformations Are image transformations enabled?
     * @return appwrite::Result<appwrite::models::Bucket>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::Bucket>> createBucketAsync(        std::string_view bucketId,         std::string_view name,         std::optional<std::vector<std::string>> permissions = std::nullopt,         std::optional<bool> fileSecurity = false,         std::optional<bool> enabled = true,         std::optional<int64_t> maximumFileSize = 0,         std::optional<std::vector<std::string>> allowedFileExtensions = std::vector<std::string>{},         std::optional<appwrite::enums::Compression> compression = appwrite::enums::Compression::NONE,         std::optional<bool> encryption = true,         std::optional<bool> antivirus = true,         std::optional<bool> transformations = true    ) {
                std::string path_ = std::format("/storage/buckets");
        
        nlohmann::json params = nlohmann::json::object();
        params["bucketId"] = std::string(bucketId);
        params["name"] = std::string(name);
        if (permissions.has_value()) {
            params["permissions"] = *permissions;
        }
        if (fileSecurity.has_value()) {
            params["fileSecurity"] = *fileSecurity;
        }
        if (enabled.has_value()) {
            params["enabled"] = *enabled;
        }
        if (maximumFileSize.has_value()) {
            params["maximumFileSize"] = *maximumFileSize;
        }
        if (allowedFileExtensions.has_value()) {
            params["allowedFileExtensions"] = *allowedFileExtensions;
        }
        if (compression.has_value()) {
            params["compression"] = *compression;
        }
        if (encryption.has_value()) {
            params["encryption"] = *encryption;
        }
        if (antivirus.has_value()) {
            params["antivirus"] = *antivirus;
        }
        if (transformations.has_value()) {
            params["transformations"] = *transformations;
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::Bucket>("POST", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Get a storage bucket by its unique ID. This endpoint response returns a
     * JSON object with the storage bucket metadata.
     *
     * @param bucketId Bucket unique ID.
     * @return appwrite::Result<appwrite::models::Bucket>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::Bucket> getBucket(        std::string_view bucketId    ) {
                std::string path_ = std::format("/storage/buckets/{}", bucketId);
        
        nlohmann::json params = nlohmann::json::object();
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::Bucket>("GET", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Get a storage bucket by its unique ID. This endpoint response returns a
     * JSON object with the storage bucket metadata.
     *
     * @param bucketId Bucket unique ID.
     * @return appwrite::Result<appwrite::models::Bucket>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::Bucket>> getBucketAsync(        std::string_view bucketId    ) {
                std::string path_ = std::format("/storage/buckets/{}", bucketId);
        
        nlohmann::json params = nlohmann::json::object();
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::Bucket>("GET", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Update a storage bucket by its unique ID.
     *
     * @param bucketId Bucket unique ID.
     * @param name Bucket name
     * @param permissions An array of permission strings. By default, the current permissions are inherited. [Learn more about permissions](https://appwrite.io/docs/permissions).
     * @param fileSecurity Enables configuring permissions for individual file. A user needs one of file or bucket level permissions to access a file. [Learn more about permissions](https://appwrite.io/docs/permissions).
     * @param enabled Is bucket enabled? When set to 'disabled', users cannot access the files in this bucket but Server SDKs with and API key can still access the bucket. No files are lost when this is toggled.
     * @param maximumFileSize Maximum file size allowed in bytes. Maximum allowed value is 5GB.
     * @param allowedFileExtensions Allowed file extensions. Maximum of 100 extensions are allowed, each 64 characters long.
     * @param compression Compression algorithm chosen for compression. Can be one of none, [gzip](https://en.wikipedia.org/wiki/Gzip), or [zstd](https://en.wikipedia.org/wiki/Zstd), For file size above 20MB compression is skipped even if it's enabled
     * @param encryption Is encryption enabled? For file size above 20MB encryption is skipped even if it's enabled
     * @param antivirus Is virus scanning enabled? For file size above 20MB AntiVirus scanning is skipped even if it's enabled
     * @param transformations Are image transformations enabled?
     * @return appwrite::Result<appwrite::models::Bucket>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::Bucket> updateBucket(        std::string_view bucketId,         std::string_view name,         std::optional<std::vector<std::string>> permissions = std::nullopt,         std::optional<bool> fileSecurity = false,         std::optional<bool> enabled = true,         std::optional<int64_t> maximumFileSize = 0,         std::optional<std::vector<std::string>> allowedFileExtensions = std::vector<std::string>{},         std::optional<appwrite::enums::Compression> compression = appwrite::enums::Compression::NONE,         std::optional<bool> encryption = true,         std::optional<bool> antivirus = true,         std::optional<bool> transformations = true    ) {
                std::string path_ = std::format("/storage/buckets/{}", bucketId);
        
        nlohmann::json params = nlohmann::json::object();
        params["name"] = std::string(name);
        if (permissions.has_value()) {
            params["permissions"] = *permissions;
        }
        if (fileSecurity.has_value()) {
            params["fileSecurity"] = *fileSecurity;
        }
        if (enabled.has_value()) {
            params["enabled"] = *enabled;
        }
        if (maximumFileSize.has_value()) {
            params["maximumFileSize"] = *maximumFileSize;
        }
        if (allowedFileExtensions.has_value()) {
            params["allowedFileExtensions"] = *allowedFileExtensions;
        }
        if (compression.has_value()) {
            params["compression"] = *compression;
        }
        if (encryption.has_value()) {
            params["encryption"] = *encryption;
        }
        if (antivirus.has_value()) {
            params["antivirus"] = *antivirus;
        }
        if (transformations.has_value()) {
            params["transformations"] = *transformations;
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::Bucket>("PUT", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Update a storage bucket by its unique ID.
     *
     * @param bucketId Bucket unique ID.
     * @param name Bucket name
     * @param permissions An array of permission strings. By default, the current permissions are inherited. [Learn more about permissions](https://appwrite.io/docs/permissions).
     * @param fileSecurity Enables configuring permissions for individual file. A user needs one of file or bucket level permissions to access a file. [Learn more about permissions](https://appwrite.io/docs/permissions).
     * @param enabled Is bucket enabled? When set to 'disabled', users cannot access the files in this bucket but Server SDKs with and API key can still access the bucket. No files are lost when this is toggled.
     * @param maximumFileSize Maximum file size allowed in bytes. Maximum allowed value is 5GB.
     * @param allowedFileExtensions Allowed file extensions. Maximum of 100 extensions are allowed, each 64 characters long.
     * @param compression Compression algorithm chosen for compression. Can be one of none, [gzip](https://en.wikipedia.org/wiki/Gzip), or [zstd](https://en.wikipedia.org/wiki/Zstd), For file size above 20MB compression is skipped even if it's enabled
     * @param encryption Is encryption enabled? For file size above 20MB encryption is skipped even if it's enabled
     * @param antivirus Is virus scanning enabled? For file size above 20MB AntiVirus scanning is skipped even if it's enabled
     * @param transformations Are image transformations enabled?
     * @return appwrite::Result<appwrite::models::Bucket>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::Bucket>> updateBucketAsync(        std::string_view bucketId,         std::string_view name,         std::optional<std::vector<std::string>> permissions = std::nullopt,         std::optional<bool> fileSecurity = false,         std::optional<bool> enabled = true,         std::optional<int64_t> maximumFileSize = 0,         std::optional<std::vector<std::string>> allowedFileExtensions = std::vector<std::string>{},         std::optional<appwrite::enums::Compression> compression = appwrite::enums::Compression::NONE,         std::optional<bool> encryption = true,         std::optional<bool> antivirus = true,         std::optional<bool> transformations = true    ) {
                std::string path_ = std::format("/storage/buckets/{}", bucketId);
        
        nlohmann::json params = nlohmann::json::object();
        params["name"] = std::string(name);
        if (permissions.has_value()) {
            params["permissions"] = *permissions;
        }
        if (fileSecurity.has_value()) {
            params["fileSecurity"] = *fileSecurity;
        }
        if (enabled.has_value()) {
            params["enabled"] = *enabled;
        }
        if (maximumFileSize.has_value()) {
            params["maximumFileSize"] = *maximumFileSize;
        }
        if (allowedFileExtensions.has_value()) {
            params["allowedFileExtensions"] = *allowedFileExtensions;
        }
        if (compression.has_value()) {
            params["compression"] = *compression;
        }
        if (encryption.has_value()) {
            params["encryption"] = *encryption;
        }
        if (antivirus.has_value()) {
            params["antivirus"] = *antivirus;
        }
        if (transformations.has_value()) {
            params["transformations"] = *transformations;
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::Bucket>("PUT", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Delete a storage bucket by its unique ID.
     *
     * @param bucketId Bucket unique ID.
     * @return appwrite::Result<void>
     */
    [[nodiscard]] appwrite::Result<void> deleteBucket(        std::string_view bucketId    ) {
                std::string path_ = std::format("/storage/buckets/{}", bucketId);
        
        nlohmann::json params = nlohmann::json::object();
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callVoid("DELETE", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Delete a storage bucket by its unique ID.
     *
     * @param bucketId Bucket unique ID.
     * @return appwrite::Result<void>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<void>> deleteBucketAsync(        std::string_view bucketId    ) {
                std::string path_ = std::format("/storage/buckets/{}", bucketId);
        
        nlohmann::json params = nlohmann::json::object();
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callVoidAsync("DELETE", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Get a list of all the user files. You can use the query params to filter
     * your results.
     *
     * @param bucketId Storage bucket unique ID. You can create a new storage bucket using the Storage service [server integration](https://appwrite.io/docs/server/storage#createBucket).
     * @param queries Array of query strings generated using the Query class provided by the SDK. [Learn more about queries](https://appwrite.io/docs/queries). Maximum of 100 queries are allowed, each 4096 characters long. You may filter on the following attributes: name, signature, mimeType, sizeOriginal, chunksTotal, chunksUploaded
     * @param search Search term to filter your list results. Max length: 256 chars.
     * @param total When set to false, the total count returned will be 0 and will not be calculated.
     * @return appwrite::Result<appwrite::models::FileList>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::FileList> listFiles(        std::string_view bucketId,         std::optional<std::vector<std::string>> queries = std::vector<std::string>{},         std::optional<std::string_view> search = std::nullopt,         std::optional<bool> total = true    ) {
                std::string path_ = std::format("/storage/buckets/{}/files", bucketId);
        
        nlohmann::json params = nlohmann::json::object();
        if (queries.has_value()) {
            params["queries"] = *queries;
        }
        if (search.has_value()) {
            params["search"] = std::string(search.value());
        }
        if (total.has_value()) {
            params["total"] = *total;
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::FileList>("GET", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Get a list of all the user files. You can use the query params to filter
     * your results.
     *
     * @param bucketId Storage bucket unique ID. You can create a new storage bucket using the Storage service [server integration](https://appwrite.io/docs/server/storage#createBucket).
     * @param queries Array of query strings generated using the Query class provided by the SDK. [Learn more about queries](https://appwrite.io/docs/queries). Maximum of 100 queries are allowed, each 4096 characters long. You may filter on the following attributes: name, signature, mimeType, sizeOriginal, chunksTotal, chunksUploaded
     * @param search Search term to filter your list results. Max length: 256 chars.
     * @param total When set to false, the total count returned will be 0 and will not be calculated.
     * @return appwrite::Result<appwrite::models::FileList>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::FileList>> listFilesAsync(        std::string_view bucketId,         std::optional<std::vector<std::string>> queries = std::vector<std::string>{},         std::optional<std::string_view> search = std::nullopt,         std::optional<bool> total = true    ) {
                std::string path_ = std::format("/storage/buckets/{}/files", bucketId);
        
        nlohmann::json params = nlohmann::json::object();
        if (queries.has_value()) {
            params["queries"] = *queries;
        }
        if (search.has_value()) {
            params["search"] = std::string(search.value());
        }
        if (total.has_value()) {
            params["total"] = *total;
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::FileList>("GET", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Create a new file. Before using this route, you should create a new bucket
     * resource using either a [server
     * integration](https://appwrite.io/docs/server/storage#storageCreateBucket)
     * API or directly from your Appwrite console.

Larger files should be
     * uploaded using multiple requests with the
     * [content-range](https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Content-Range)
     * header to send a partial request with a maximum supported chunk of `5MB`.
     * The `content-range` header values should always be in bytes.

When the
     * first request is sent, the server will return the **File** object, and the
     * subsequent part request must include the file's **id** in `x-appwrite-id`
     * header to allow the server to know that the partial upload is for the
     * existing file and not for a new one.

If you're creating a new file using
     * one of the Appwrite SDKs, all the chunking logic will be managed by the SDK
     * internally.
     *
     * @param bucketId Storage bucket unique ID. You can create a new storage bucket using the Storage service [server integration](https://appwrite.io/docs/server/storage#createBucket).
     * @param fileId File ID. Choose a custom ID or generate a random ID with `ID.unique()`. Valid chars are a-z, A-Z, 0-9, period, hyphen, and underscore. Can't start with a special char. Max length is 36 chars.
     * @param file Binary file. Appwrite SDKs provide helpers to handle file input. [Learn about file input](https://appwrite.io/docs/products/storage/upload-download#input-file).
     * @param permissions An array of permission strings. By default, only the current user is granted all permissions. [Learn more about permissions](https://appwrite.io/docs/permissions).
     * @return appwrite::Result<appwrite::models::File>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::File> createFile(        std::string_view bucketId,         std::string_view fileId,         appwrite::InputFile file,         std::optional<std::vector<std::string>> permissions = std::nullopt        , Client::ProgressCallback onProgress = nullptr    ) {
                std::string path_ = std::format("/storage/buckets/{}/files", bucketId);
        
        nlohmann::json params = nlohmann::json::object();
        params["fileId"] = std::string(fileId);
        if (permissions.has_value()) {
            params["permissions"] = *permissions;
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.fileUpload<appwrite::models::File>("POST", path_, "file", std::move(file), std::move(local_headers_), std::move(params), std::move(onProgress));
    }

    /**
     * Create a new file. Before using this route, you should create a new bucket
     * resource using either a [server
     * integration](https://appwrite.io/docs/server/storage#storageCreateBucket)
     * API or directly from your Appwrite console.

Larger files should be
     * uploaded using multiple requests with the
     * [content-range](https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Content-Range)
     * header to send a partial request with a maximum supported chunk of `5MB`.
     * The `content-range` header values should always be in bytes.

When the
     * first request is sent, the server will return the **File** object, and the
     * subsequent part request must include the file's **id** in `x-appwrite-id`
     * header to allow the server to know that the partial upload is for the
     * existing file and not for a new one.

If you're creating a new file using
     * one of the Appwrite SDKs, all the chunking logic will be managed by the SDK
     * internally.
     *
     * @param bucketId Storage bucket unique ID. You can create a new storage bucket using the Storage service [server integration](https://appwrite.io/docs/server/storage#createBucket).
     * @param fileId File ID. Choose a custom ID or generate a random ID with `ID.unique()`. Valid chars are a-z, A-Z, 0-9, period, hyphen, and underscore. Can't start with a special char. Max length is 36 chars.
     * @param file Binary file. Appwrite SDKs provide helpers to handle file input. [Learn about file input](https://appwrite.io/docs/products/storage/upload-download#input-file).
     * @param permissions An array of permission strings. By default, only the current user is granted all permissions. [Learn more about permissions](https://appwrite.io/docs/permissions).
     * @return appwrite::Result<appwrite::models::File>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::File>> createFileAsync(        std::string_view bucketId,         std::string_view fileId,         appwrite::InputFile file,         std::optional<std::vector<std::string>> permissions = std::nullopt        , Client::ProgressCallback onProgress = nullptr    ) {
                std::string path_ = std::format("/storage/buckets/{}/files", bucketId);
        
        nlohmann::json params = nlohmann::json::object();
        params["fileId"] = std::string(fileId);
        if (permissions.has_value()) {
            params["permissions"] = *permissions;
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.fileUploadAsync<appwrite::models::File>("POST", path_, "file", std::move(file), std::move(local_headers_), std::move(params), std::move(onProgress));
    }
    /**
     * Get a file by its unique ID. This endpoint response returns a JSON object
     * with the file metadata.
     *
     * @param bucketId Storage bucket unique ID. You can create a new storage bucket using the Storage service [server integration](https://appwrite.io/docs/server/storage#createBucket).
     * @param fileId File ID.
     * @return appwrite::Result<appwrite::models::File>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::File> getFile(        std::string_view bucketId,         std::string_view fileId    ) {
                std::string path_ = std::format("/storage/buckets/{}/files/{}", bucketId, fileId);
        
        nlohmann::json params = nlohmann::json::object();
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::File>("GET", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Get a file by its unique ID. This endpoint response returns a JSON object
     * with the file metadata.
     *
     * @param bucketId Storage bucket unique ID. You can create a new storage bucket using the Storage service [server integration](https://appwrite.io/docs/server/storage#createBucket).
     * @param fileId File ID.
     * @return appwrite::Result<appwrite::models::File>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::File>> getFileAsync(        std::string_view bucketId,         std::string_view fileId    ) {
                std::string path_ = std::format("/storage/buckets/{}/files/{}", bucketId, fileId);
        
        nlohmann::json params = nlohmann::json::object();
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::File>("GET", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Update a file by its unique ID. Only users with write permissions have
     * access to update this resource.
     *
     * @param bucketId Bucket unique ID.
     * @param fileId File ID.
     * @param name File name.
     * @param permissions An array of permission strings. By default, the current permissions are inherited. [Learn more about permissions](https://appwrite.io/docs/permissions).
     * @return appwrite::Result<appwrite::models::File>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::File> updateFile(        std::string_view bucketId,         std::string_view fileId,         std::optional<std::string_view> name = std::nullopt,         std::optional<std::vector<std::string>> permissions = std::nullopt    ) {
                std::string path_ = std::format("/storage/buckets/{}/files/{}", bucketId, fileId);
        
        nlohmann::json params = nlohmann::json::object();
        if (name.has_value()) {
            params["name"] = std::string(name.value());
        }
        if (permissions.has_value()) {
            params["permissions"] = *permissions;
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::File>("PUT", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Update a file by its unique ID. Only users with write permissions have
     * access to update this resource.
     *
     * @param bucketId Bucket unique ID.
     * @param fileId File ID.
     * @param name File name.
     * @param permissions An array of permission strings. By default, the current permissions are inherited. [Learn more about permissions](https://appwrite.io/docs/permissions).
     * @return appwrite::Result<appwrite::models::File>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::File>> updateFileAsync(        std::string_view bucketId,         std::string_view fileId,         std::optional<std::string_view> name = std::nullopt,         std::optional<std::vector<std::string>> permissions = std::nullopt    ) {
                std::string path_ = std::format("/storage/buckets/{}/files/{}", bucketId, fileId);
        
        nlohmann::json params = nlohmann::json::object();
        if (name.has_value()) {
            params["name"] = std::string(name.value());
        }
        if (permissions.has_value()) {
            params["permissions"] = *permissions;
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::File>("PUT", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Delete a file by its unique ID. Only users with write permissions have
     * access to delete this resource.
     *
     * @param bucketId Storage bucket unique ID. You can create a new storage bucket using the Storage service [server integration](https://appwrite.io/docs/server/storage#createBucket).
     * @param fileId File ID.
     * @return appwrite::Result<void>
     */
    [[nodiscard]] appwrite::Result<void> deleteFile(        std::string_view bucketId,         std::string_view fileId    ) {
                std::string path_ = std::format("/storage/buckets/{}/files/{}", bucketId, fileId);
        
        nlohmann::json params = nlohmann::json::object();
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callVoid("DELETE", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Delete a file by its unique ID. Only users with write permissions have
     * access to delete this resource.
     *
     * @param bucketId Storage bucket unique ID. You can create a new storage bucket using the Storage service [server integration](https://appwrite.io/docs/server/storage#createBucket).
     * @param fileId File ID.
     * @return appwrite::Result<void>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<void>> deleteFileAsync(        std::string_view bucketId,         std::string_view fileId    ) {
                std::string path_ = std::format("/storage/buckets/{}/files/{}", bucketId, fileId);
        
        nlohmann::json params = nlohmann::json::object();
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callVoidAsync("DELETE", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Get a file content by its unique ID. The endpoint response return with a
     * 'Content-Disposition: attachment' header that tells the browser to start
     * downloading the file to user downloads directory.
     *
     * @param bucketId Storage bucket ID. You can create a new storage bucket using the Storage service [server integration](https://appwrite.io/docs/server/storage#createBucket).
     * @param fileId File ID.
     * @param token File token for accessing this file.
     * @return appwrite::Result<BinaryResponse>
     */
    [[nodiscard]] appwrite::Result<BinaryResponse> getFileDownload(        std::string_view bucketId,         std::string_view fileId,         std::optional<std::string_view> token = std::nullopt    ) {
                std::string path_ = std::format("/storage/buckets/{}/files/{}/download", bucketId, fileId);
        
        nlohmann::json params = nlohmann::json::object();
        if (token.has_value()) {
            params["token"] = std::string(token.value());
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callBytes("GET", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Get a file content by its unique ID. The endpoint response return with a
     * 'Content-Disposition: attachment' header that tells the browser to start
     * downloading the file to user downloads directory.
     *
     * @param bucketId Storage bucket ID. You can create a new storage bucket using the Storage service [server integration](https://appwrite.io/docs/server/storage#createBucket).
     * @param fileId File ID.
     * @param token File token for accessing this file.
     * @return appwrite::Result<BinaryResponse>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<BinaryResponse>> getFileDownloadAsync(        std::string_view bucketId,         std::string_view fileId,         std::optional<std::string_view> token = std::nullopt    ) {
                std::string path_ = std::format("/storage/buckets/{}/files/{}/download", bucketId, fileId);
        
        nlohmann::json params = nlohmann::json::object();
        if (token.has_value()) {
            params["token"] = std::string(token.value());
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callBytesAsync("GET", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Get a file preview image. Currently, this method supports preview for image
     * files (jpg, png, and gif), other supported formats, like pdf, docs, slides,
     * and spreadsheets, will return the file icon image. You can also pass query
     * string arguments for cutting and resizing your preview image. Preview is
     * supported only for image files smaller than 10MB.
     *
     * @param bucketId Storage bucket unique ID. You can create a new storage bucket using the Storage service [server integration](https://appwrite.io/docs/server/storage#createBucket).
     * @param fileId File ID
     * @param width Resize preview image width, Pass an integer between 0 to 4000.
     * @param height Resize preview image height, Pass an integer between 0 to 4000.
     * @param gravity Image crop gravity. Can be one of center,top-left,top,top-right,left,right,bottom-left,bottom,bottom-right
     * @param quality Preview image quality. Pass an integer between 0 to 100. Defaults to keep existing image quality.
     * @param borderWidth Preview image border in pixels. Pass an integer between 0 to 100. Defaults to 0.
     * @param borderColor Preview image border color. Use a valid HEX color, no # is needed for prefix.
     * @param borderRadius Preview image border radius in pixels. Pass an integer between 0 to 4000.
     * @param opacity Preview image opacity. Only works with images having an alpha channel (like png). Pass a number between 0 to 1.
     * @param rotation Preview image rotation in degrees. Pass an integer between -360 and 360.
     * @param background Preview image background color. Only works with transparent images (png). Use a valid HEX color, no # is needed for prefix.
     * @param output Output format type (jpeg, jpg, png, gif and webp).
     * @param token File token for accessing this file.
     * @return appwrite::Result<BinaryResponse>
     */
    [[nodiscard]] appwrite::Result<BinaryResponse> getFilePreview(        std::string_view bucketId,         std::string_view fileId,         std::optional<int64_t> width = 0,         std::optional<int64_t> height = 0,         std::optional<appwrite::enums::ImageGravity> gravity = appwrite::enums::ImageGravity::CENTER,         std::optional<int64_t> quality = -1,         std::optional<int64_t> borderWidth = 0,         std::optional<std::string_view> borderColor = std::nullopt,         std::optional<int64_t> borderRadius = 0,         std::optional<double> opacity = 1,         std::optional<int64_t> rotation = 0,         std::optional<std::string_view> background = std::nullopt,         std::optional<appwrite::enums::ImageFormat> output = std::nullopt,         std::optional<std::string_view> token = std::nullopt    ) {
                std::string path_ = std::format("/storage/buckets/{}/files/{}/preview", bucketId, fileId);
        
        nlohmann::json params = nlohmann::json::object();
        if (width.has_value()) {
            params["width"] = *width;
        }
        if (height.has_value()) {
            params["height"] = *height;
        }
        if (gravity.has_value()) {
            params["gravity"] = *gravity;
        }
        if (quality.has_value()) {
            params["quality"] = *quality;
        }
        if (borderWidth.has_value()) {
            params["borderWidth"] = *borderWidth;
        }
        if (borderColor.has_value()) {
            params["borderColor"] = std::string(borderColor.value());
        }
        if (borderRadius.has_value()) {
            params["borderRadius"] = *borderRadius;
        }
        if (opacity.has_value()) {
            params["opacity"] = *opacity;
        }
        if (rotation.has_value()) {
            params["rotation"] = *rotation;
        }
        if (background.has_value()) {
            params["background"] = std::string(background.value());
        }
        if (output.has_value()) {
            params["output"] = *output;
        }
        if (token.has_value()) {
            params["token"] = std::string(token.value());
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callBytes("GET", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Get a file preview image. Currently, this method supports preview for image
     * files (jpg, png, and gif), other supported formats, like pdf, docs, slides,
     * and spreadsheets, will return the file icon image. You can also pass query
     * string arguments for cutting and resizing your preview image. Preview is
     * supported only for image files smaller than 10MB.
     *
     * @param bucketId Storage bucket unique ID. You can create a new storage bucket using the Storage service [server integration](https://appwrite.io/docs/server/storage#createBucket).
     * @param fileId File ID
     * @param width Resize preview image width, Pass an integer between 0 to 4000.
     * @param height Resize preview image height, Pass an integer between 0 to 4000.
     * @param gravity Image crop gravity. Can be one of center,top-left,top,top-right,left,right,bottom-left,bottom,bottom-right
     * @param quality Preview image quality. Pass an integer between 0 to 100. Defaults to keep existing image quality.
     * @param borderWidth Preview image border in pixels. Pass an integer between 0 to 100. Defaults to 0.
     * @param borderColor Preview image border color. Use a valid HEX color, no # is needed for prefix.
     * @param borderRadius Preview image border radius in pixels. Pass an integer between 0 to 4000.
     * @param opacity Preview image opacity. Only works with images having an alpha channel (like png). Pass a number between 0 to 1.
     * @param rotation Preview image rotation in degrees. Pass an integer between -360 and 360.
     * @param background Preview image background color. Only works with transparent images (png). Use a valid HEX color, no # is needed for prefix.
     * @param output Output format type (jpeg, jpg, png, gif and webp).
     * @param token File token for accessing this file.
     * @return appwrite::Result<BinaryResponse>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<BinaryResponse>> getFilePreviewAsync(        std::string_view bucketId,         std::string_view fileId,         std::optional<int64_t> width = 0,         std::optional<int64_t> height = 0,         std::optional<appwrite::enums::ImageGravity> gravity = appwrite::enums::ImageGravity::CENTER,         std::optional<int64_t> quality = -1,         std::optional<int64_t> borderWidth = 0,         std::optional<std::string_view> borderColor = std::nullopt,         std::optional<int64_t> borderRadius = 0,         std::optional<double> opacity = 1,         std::optional<int64_t> rotation = 0,         std::optional<std::string_view> background = std::nullopt,         std::optional<appwrite::enums::ImageFormat> output = std::nullopt,         std::optional<std::string_view> token = std::nullopt    ) {
                std::string path_ = std::format("/storage/buckets/{}/files/{}/preview", bucketId, fileId);
        
        nlohmann::json params = nlohmann::json::object();
        if (width.has_value()) {
            params["width"] = *width;
        }
        if (height.has_value()) {
            params["height"] = *height;
        }
        if (gravity.has_value()) {
            params["gravity"] = *gravity;
        }
        if (quality.has_value()) {
            params["quality"] = *quality;
        }
        if (borderWidth.has_value()) {
            params["borderWidth"] = *borderWidth;
        }
        if (borderColor.has_value()) {
            params["borderColor"] = std::string(borderColor.value());
        }
        if (borderRadius.has_value()) {
            params["borderRadius"] = *borderRadius;
        }
        if (opacity.has_value()) {
            params["opacity"] = *opacity;
        }
        if (rotation.has_value()) {
            params["rotation"] = *rotation;
        }
        if (background.has_value()) {
            params["background"] = std::string(background.value());
        }
        if (output.has_value()) {
            params["output"] = *output;
        }
        if (token.has_value()) {
            params["token"] = std::string(token.value());
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callBytesAsync("GET", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Get a file content by its unique ID. This endpoint is similar to the
     * download method but returns with no  'Content-Disposition: attachment'
     * header.
     *
     * @param bucketId Storage bucket unique ID. You can create a new storage bucket using the Storage service [server integration](https://appwrite.io/docs/server/storage#createBucket).
     * @param fileId File ID.
     * @param token File token for accessing this file.
     * @return appwrite::Result<BinaryResponse>
     */
    [[nodiscard]] appwrite::Result<BinaryResponse> getFileView(        std::string_view bucketId,         std::string_view fileId,         std::optional<std::string_view> token = std::nullopt    ) {
                std::string path_ = std::format("/storage/buckets/{}/files/{}/view", bucketId, fileId);
        
        nlohmann::json params = nlohmann::json::object();
        if (token.has_value()) {
            params["token"] = std::string(token.value());
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callBytes("GET", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Get a file content by its unique ID. This endpoint is similar to the
     * download method but returns with no  'Content-Disposition: attachment'
     * header.
     *
     * @param bucketId Storage bucket unique ID. You can create a new storage bucket using the Storage service [server integration](https://appwrite.io/docs/server/storage#createBucket).
     * @param fileId File ID.
     * @param token File token for accessing this file.
     * @return appwrite::Result<BinaryResponse>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<BinaryResponse>> getFileViewAsync(        std::string_view bucketId,         std::string_view fileId,         std::optional<std::string_view> token = std::nullopt    ) {
                std::string path_ = std::format("/storage/buckets/{}/files/{}/view", bucketId, fileId);
        
        nlohmann::json params = nlohmann::json::object();
        if (token.has_value()) {
            params["token"] = std::string(token.value());
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callBytesAsync("GET", path_, std::move(local_headers_), std::move(params));
    }
};

/**
 * @brief 
 */
class TablesDB : public Service {
public:
    explicit TablesDB(Client& client) : Service(client) {}

    /**
     * Get a list of all databases from the current Appwrite project. You can use
     * the search parameter to filter your results.
     *
     * @param queries Array of query strings generated using the Query class provided by the SDK. [Learn more about queries](https://appwrite.io/docs/queries). Maximum of 100 queries are allowed, each 4096 characters long. You may filter on the following columns: name
     * @param search Search term to filter your list results. Max length: 256 chars.
     * @param total When set to false, the total count returned will be 0 and will not be calculated.
     * @return appwrite::Result<appwrite::models::DatabaseList>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::DatabaseList> list(        std::optional<std::vector<std::string>> queries = std::vector<std::string>{},         std::optional<std::string_view> search = std::nullopt,         std::optional<bool> total = true    ) {
                std::string path_ = std::format("/tablesdb");
        
        nlohmann::json params = nlohmann::json::object();
        if (queries.has_value()) {
            params["queries"] = *queries;
        }
        if (search.has_value()) {
            params["search"] = std::string(search.value());
        }
        if (total.has_value()) {
            params["total"] = *total;
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::DatabaseList>("GET", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Get a list of all databases from the current Appwrite project. You can use
     * the search parameter to filter your results.
     *
     * @param queries Array of query strings generated using the Query class provided by the SDK. [Learn more about queries](https://appwrite.io/docs/queries). Maximum of 100 queries are allowed, each 4096 characters long. You may filter on the following columns: name
     * @param search Search term to filter your list results. Max length: 256 chars.
     * @param total When set to false, the total count returned will be 0 and will not be calculated.
     * @return appwrite::Result<appwrite::models::DatabaseList>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::DatabaseList>> listAsync(        std::optional<std::vector<std::string>> queries = std::vector<std::string>{},         std::optional<std::string_view> search = std::nullopt,         std::optional<bool> total = true    ) {
                std::string path_ = std::format("/tablesdb");
        
        nlohmann::json params = nlohmann::json::object();
        if (queries.has_value()) {
            params["queries"] = *queries;
        }
        if (search.has_value()) {
            params["search"] = std::string(search.value());
        }
        if (total.has_value()) {
            params["total"] = *total;
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::DatabaseList>("GET", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Create a new Database.
     *
     * @param databaseId Unique Id. Choose a custom ID or generate a random ID with `ID.unique()`. Valid chars are a-z, A-Z, 0-9, period, hyphen, and underscore. Can't start with a special char. Max length is 36 chars.
     * @param name Database name. Max length: 128 chars.
     * @param enabled Is the database enabled? When set to 'disabled', users cannot access the database but Server SDKs with an API key can still read and write to the database. No data is lost when this is toggled.
     * @return appwrite::Result<appwrite::models::Database>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::Database> create(        std::string_view databaseId,         std::string_view name,         std::optional<bool> enabled = true    ) {
                std::string path_ = std::format("/tablesdb");
        
        nlohmann::json params = nlohmann::json::object();
        params["databaseId"] = std::string(databaseId);
        params["name"] = std::string(name);
        if (enabled.has_value()) {
            params["enabled"] = *enabled;
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::Database>("POST", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Create a new Database.
     *
     * @param databaseId Unique Id. Choose a custom ID or generate a random ID with `ID.unique()`. Valid chars are a-z, A-Z, 0-9, period, hyphen, and underscore. Can't start with a special char. Max length is 36 chars.
     * @param name Database name. Max length: 128 chars.
     * @param enabled Is the database enabled? When set to 'disabled', users cannot access the database but Server SDKs with an API key can still read and write to the database. No data is lost when this is toggled.
     * @return appwrite::Result<appwrite::models::Database>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::Database>> createAsync(        std::string_view databaseId,         std::string_view name,         std::optional<bool> enabled = true    ) {
                std::string path_ = std::format("/tablesdb");
        
        nlohmann::json params = nlohmann::json::object();
        params["databaseId"] = std::string(databaseId);
        params["name"] = std::string(name);
        if (enabled.has_value()) {
            params["enabled"] = *enabled;
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::Database>("POST", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * List transactions across all databases.
     *
     * @param queries Array of query strings generated using the Query class provided by the SDK. [Learn more about queries](https://appwrite.io/docs/queries).
     * @return appwrite::Result<appwrite::models::TransactionList>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::TransactionList> listTransactions(        std::optional<std::vector<std::string>> queries = std::vector<std::string>{}    ) {
                std::string path_ = std::format("/tablesdb/transactions");
        
        nlohmann::json params = nlohmann::json::object();
        if (queries.has_value()) {
            params["queries"] = *queries;
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::TransactionList>("GET", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * List transactions across all databases.
     *
     * @param queries Array of query strings generated using the Query class provided by the SDK. [Learn more about queries](https://appwrite.io/docs/queries).
     * @return appwrite::Result<appwrite::models::TransactionList>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::TransactionList>> listTransactionsAsync(        std::optional<std::vector<std::string>> queries = std::vector<std::string>{}    ) {
                std::string path_ = std::format("/tablesdb/transactions");
        
        nlohmann::json params = nlohmann::json::object();
        if (queries.has_value()) {
            params["queries"] = *queries;
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::TransactionList>("GET", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Create a new transaction.
     *
     * @param ttl Seconds before the transaction expires.
     * @return appwrite::Result<appwrite::models::Transaction>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::Transaction> createTransaction(        std::optional<int64_t> ttl = 300    ) {
                std::string path_ = std::format("/tablesdb/transactions");
        
        nlohmann::json params = nlohmann::json::object();
        if (ttl.has_value()) {
            params["ttl"] = *ttl;
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::Transaction>("POST", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Create a new transaction.
     *
     * @param ttl Seconds before the transaction expires.
     * @return appwrite::Result<appwrite::models::Transaction>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::Transaction>> createTransactionAsync(        std::optional<int64_t> ttl = 300    ) {
                std::string path_ = std::format("/tablesdb/transactions");
        
        nlohmann::json params = nlohmann::json::object();
        if (ttl.has_value()) {
            params["ttl"] = *ttl;
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::Transaction>("POST", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Get a transaction by its unique ID.
     *
     * @param transactionId Transaction ID.
     * @return appwrite::Result<appwrite::models::Transaction>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::Transaction> getTransaction(        std::string_view transactionId    ) {
                std::string path_ = std::format("/tablesdb/transactions/{}", transactionId);
        
        nlohmann::json params = nlohmann::json::object();
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::Transaction>("GET", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Get a transaction by its unique ID.
     *
     * @param transactionId Transaction ID.
     * @return appwrite::Result<appwrite::models::Transaction>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::Transaction>> getTransactionAsync(        std::string_view transactionId    ) {
                std::string path_ = std::format("/tablesdb/transactions/{}", transactionId);
        
        nlohmann::json params = nlohmann::json::object();
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::Transaction>("GET", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Update a transaction, to either commit or roll back its operations.
     *
     * @param transactionId Transaction ID.
     * @param commit Commit transaction?
     * @param rollback Rollback transaction?
     * @return appwrite::Result<appwrite::models::Transaction>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::Transaction> updateTransaction(        std::string_view transactionId,         std::optional<bool> commit = false,         std::optional<bool> rollback = false    ) {
                std::string path_ = std::format("/tablesdb/transactions/{}", transactionId);
        
        nlohmann::json params = nlohmann::json::object();
        if (commit.has_value()) {
            params["commit"] = *commit;
        }
        if (rollback.has_value()) {
            params["rollback"] = *rollback;
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::Transaction>("PATCH", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Update a transaction, to either commit or roll back its operations.
     *
     * @param transactionId Transaction ID.
     * @param commit Commit transaction?
     * @param rollback Rollback transaction?
     * @return appwrite::Result<appwrite::models::Transaction>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::Transaction>> updateTransactionAsync(        std::string_view transactionId,         std::optional<bool> commit = false,         std::optional<bool> rollback = false    ) {
                std::string path_ = std::format("/tablesdb/transactions/{}", transactionId);
        
        nlohmann::json params = nlohmann::json::object();
        if (commit.has_value()) {
            params["commit"] = *commit;
        }
        if (rollback.has_value()) {
            params["rollback"] = *rollback;
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::Transaction>("PATCH", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Delete a transaction by its unique ID.
     *
     * @param transactionId Transaction ID.
     * @return appwrite::Result<void>
     */
    [[nodiscard]] appwrite::Result<void> deleteTransaction(        std::string_view transactionId    ) {
                std::string path_ = std::format("/tablesdb/transactions/{}", transactionId);
        
        nlohmann::json params = nlohmann::json::object();
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callVoid("DELETE", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Delete a transaction by its unique ID.
     *
     * @param transactionId Transaction ID.
     * @return appwrite::Result<void>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<void>> deleteTransactionAsync(        std::string_view transactionId    ) {
                std::string path_ = std::format("/tablesdb/transactions/{}", transactionId);
        
        nlohmann::json params = nlohmann::json::object();
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callVoidAsync("DELETE", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Create multiple operations in a single transaction.
     *
     * @param transactionId Transaction ID.
     * @param operations Array of staged operations.
     * @return appwrite::Result<appwrite::models::Transaction>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::Transaction> createOperations(        std::string_view transactionId,         std::optional<std::vector<nlohmann::json>> operations = std::vector<nlohmann::json>{}    ) {
                std::string path_ = std::format("/tablesdb/transactions/{}/operations", transactionId);
        
        nlohmann::json params = nlohmann::json::object();
        if (operations.has_value()) {
            params["operations"] = *operations;
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::Transaction>("POST", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Create multiple operations in a single transaction.
     *
     * @param transactionId Transaction ID.
     * @param operations Array of staged operations.
     * @return appwrite::Result<appwrite::models::Transaction>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::Transaction>> createOperationsAsync(        std::string_view transactionId,         std::optional<std::vector<nlohmann::json>> operations = std::vector<nlohmann::json>{}    ) {
                std::string path_ = std::format("/tablesdb/transactions/{}/operations", transactionId);
        
        nlohmann::json params = nlohmann::json::object();
        if (operations.has_value()) {
            params["operations"] = *operations;
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::Transaction>("POST", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Get a database by its unique ID. This endpoint response returns a JSON
     * object with the database metadata.
     *
     * @param databaseId Database ID.
     * @return appwrite::Result<appwrite::models::Database>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::Database> get(        std::string_view databaseId    ) {
                std::string path_ = std::format("/tablesdb/{}", databaseId);
        
        nlohmann::json params = nlohmann::json::object();
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::Database>("GET", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Get a database by its unique ID. This endpoint response returns a JSON
     * object with the database metadata.
     *
     * @param databaseId Database ID.
     * @return appwrite::Result<appwrite::models::Database>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::Database>> getAsync(        std::string_view databaseId    ) {
                std::string path_ = std::format("/tablesdb/{}", databaseId);
        
        nlohmann::json params = nlohmann::json::object();
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::Database>("GET", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Update a database by its unique ID.
     *
     * @param databaseId Database ID.
     * @param name Database name. Max length: 128 chars.
     * @param enabled Is database enabled? When set to 'disabled', users cannot access the database but Server SDKs with an API key can still read and write to the database. No data is lost when this is toggled.
     * @return appwrite::Result<appwrite::models::Database>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::Database> update(        std::string_view databaseId,         std::optional<std::string_view> name = std::nullopt,         std::optional<bool> enabled = true    ) {
                std::string path_ = std::format("/tablesdb/{}", databaseId);
        
        nlohmann::json params = nlohmann::json::object();
        if (name.has_value()) {
            params["name"] = std::string(name.value());
        }
        if (enabled.has_value()) {
            params["enabled"] = *enabled;
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::Database>("PUT", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Update a database by its unique ID.
     *
     * @param databaseId Database ID.
     * @param name Database name. Max length: 128 chars.
     * @param enabled Is database enabled? When set to 'disabled', users cannot access the database but Server SDKs with an API key can still read and write to the database. No data is lost when this is toggled.
     * @return appwrite::Result<appwrite::models::Database>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::Database>> updateAsync(        std::string_view databaseId,         std::optional<std::string_view> name = std::nullopt,         std::optional<bool> enabled = true    ) {
                std::string path_ = std::format("/tablesdb/{}", databaseId);
        
        nlohmann::json params = nlohmann::json::object();
        if (name.has_value()) {
            params["name"] = std::string(name.value());
        }
        if (enabled.has_value()) {
            params["enabled"] = *enabled;
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::Database>("PUT", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Delete a database by its unique ID. Only API keys with with databases.write
     * scope can delete a database.
     *
     * @param databaseId Database ID.
     * @return appwrite::Result<void>
     */
    [[nodiscard]] appwrite::Result<void> delete_(        std::string_view databaseId    ) {
                std::string path_ = std::format("/tablesdb/{}", databaseId);
        
        nlohmann::json params = nlohmann::json::object();
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callVoid("DELETE", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Delete a database by its unique ID. Only API keys with with databases.write
     * scope can delete a database.
     *
     * @param databaseId Database ID.
     * @return appwrite::Result<void>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<void>> delete_Async(        std::string_view databaseId    ) {
                std::string path_ = std::format("/tablesdb/{}", databaseId);
        
        nlohmann::json params = nlohmann::json::object();
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callVoidAsync("DELETE", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Get a list of all tables that belong to the provided databaseId. You can
     * use the search parameter to filter your results.
     *
     * @param databaseId Database ID.
     * @param queries Array of query strings generated using the Query class provided by the SDK. [Learn more about queries](https://appwrite.io/docs/queries). Maximum of 100 queries are allowed, each 4096 characters long. You may filter on the following columns: name, enabled, rowSecurity
     * @param search Search term to filter your list results. Max length: 256 chars.
     * @param total When set to false, the total count returned will be 0 and will not be calculated.
     * @return appwrite::Result<appwrite::models::TableList>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::TableList> listTables(        std::string_view databaseId,         std::optional<std::vector<std::string>> queries = std::vector<std::string>{},         std::optional<std::string_view> search = std::nullopt,         std::optional<bool> total = true    ) {
                std::string path_ = std::format("/tablesdb/{}/tables", databaseId);
        
        nlohmann::json params = nlohmann::json::object();
        if (queries.has_value()) {
            params["queries"] = *queries;
        }
        if (search.has_value()) {
            params["search"] = std::string(search.value());
        }
        if (total.has_value()) {
            params["total"] = *total;
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::TableList>("GET", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Get a list of all tables that belong to the provided databaseId. You can
     * use the search parameter to filter your results.
     *
     * @param databaseId Database ID.
     * @param queries Array of query strings generated using the Query class provided by the SDK. [Learn more about queries](https://appwrite.io/docs/queries). Maximum of 100 queries are allowed, each 4096 characters long. You may filter on the following columns: name, enabled, rowSecurity
     * @param search Search term to filter your list results. Max length: 256 chars.
     * @param total When set to false, the total count returned will be 0 and will not be calculated.
     * @return appwrite::Result<appwrite::models::TableList>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::TableList>> listTablesAsync(        std::string_view databaseId,         std::optional<std::vector<std::string>> queries = std::vector<std::string>{},         std::optional<std::string_view> search = std::nullopt,         std::optional<bool> total = true    ) {
                std::string path_ = std::format("/tablesdb/{}/tables", databaseId);
        
        nlohmann::json params = nlohmann::json::object();
        if (queries.has_value()) {
            params["queries"] = *queries;
        }
        if (search.has_value()) {
            params["search"] = std::string(search.value());
        }
        if (total.has_value()) {
            params["total"] = *total;
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::TableList>("GET", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Create a new Table. Before using this route, you should create a new
     * database resource using either a [server
     * integration](https://appwrite.io/docs/references/cloud/server-dart/tablesDB#createTable)
     * API or directly from your database console.
     *
     * @param databaseId Database ID.
     * @param tableId Unique Id. Choose a custom ID or generate a random ID with `ID.unique()`. Valid chars are a-z, A-Z, 0-9, period, hyphen, and underscore. Can't start with a special char. Max length is 36 chars.
     * @param name Table name. Max length: 128 chars.
     * @param permissions An array of permissions strings. By default, no user is granted with any permissions. [Learn more about permissions](https://appwrite.io/docs/permissions).
     * @param rowSecurity Enables configuring permissions for individual rows. A user needs one of row or table level permissions to access a row. [Learn more about permissions](https://appwrite.io/docs/permissions).
     * @param enabled Is table enabled? When set to 'disabled', users cannot access the table but Server SDKs with and API key can still read and write to the table. No data is lost when this is toggled.
     * @param columns Array of column definitions to create. Each column should contain: key (string), type (string: string, integer, float, boolean, datetime, relationship), size (integer, required for string type), required (boolean, optional), default (mixed, optional), array (boolean, optional), and type-specific options.
     * @param indexes Array of index definitions to create. Each index should contain: key (string), type (string: key, fulltext, unique, spatial), attributes (array of column keys), orders (array of ASC/DESC, optional), and lengths (array of integers, optional).
     * @return appwrite::Result<appwrite::models::Table>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::Table> createTable(        std::string_view databaseId,         std::string_view tableId,         std::string_view name,         std::optional<std::vector<std::string>> permissions = std::nullopt,         std::optional<bool> rowSecurity = false,         std::optional<bool> enabled = true,         std::optional<std::vector<nlohmann::json>> columns = std::vector<nlohmann::json>{},         std::optional<std::vector<nlohmann::json>> indexes = std::vector<nlohmann::json>{}    ) {
                std::string path_ = std::format("/tablesdb/{}/tables", databaseId);
        
        nlohmann::json params = nlohmann::json::object();
        params["tableId"] = std::string(tableId);
        params["name"] = std::string(name);
        if (permissions.has_value()) {
            params["permissions"] = *permissions;
        }
        if (rowSecurity.has_value()) {
            params["rowSecurity"] = *rowSecurity;
        }
        if (enabled.has_value()) {
            params["enabled"] = *enabled;
        }
        if (columns.has_value()) {
            params["columns"] = *columns;
        }
        if (indexes.has_value()) {
            params["indexes"] = *indexes;
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::Table>("POST", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Create a new Table. Before using this route, you should create a new
     * database resource using either a [server
     * integration](https://appwrite.io/docs/references/cloud/server-dart/tablesDB#createTable)
     * API or directly from your database console.
     *
     * @param databaseId Database ID.
     * @param tableId Unique Id. Choose a custom ID or generate a random ID with `ID.unique()`. Valid chars are a-z, A-Z, 0-9, period, hyphen, and underscore. Can't start with a special char. Max length is 36 chars.
     * @param name Table name. Max length: 128 chars.
     * @param permissions An array of permissions strings. By default, no user is granted with any permissions. [Learn more about permissions](https://appwrite.io/docs/permissions).
     * @param rowSecurity Enables configuring permissions for individual rows. A user needs one of row or table level permissions to access a row. [Learn more about permissions](https://appwrite.io/docs/permissions).
     * @param enabled Is table enabled? When set to 'disabled', users cannot access the table but Server SDKs with and API key can still read and write to the table. No data is lost when this is toggled.
     * @param columns Array of column definitions to create. Each column should contain: key (string), type (string: string, integer, float, boolean, datetime, relationship), size (integer, required for string type), required (boolean, optional), default (mixed, optional), array (boolean, optional), and type-specific options.
     * @param indexes Array of index definitions to create. Each index should contain: key (string), type (string: key, fulltext, unique, spatial), attributes (array of column keys), orders (array of ASC/DESC, optional), and lengths (array of integers, optional).
     * @return appwrite::Result<appwrite::models::Table>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::Table>> createTableAsync(        std::string_view databaseId,         std::string_view tableId,         std::string_view name,         std::optional<std::vector<std::string>> permissions = std::nullopt,         std::optional<bool> rowSecurity = false,         std::optional<bool> enabled = true,         std::optional<std::vector<nlohmann::json>> columns = std::vector<nlohmann::json>{},         std::optional<std::vector<nlohmann::json>> indexes = std::vector<nlohmann::json>{}    ) {
                std::string path_ = std::format("/tablesdb/{}/tables", databaseId);
        
        nlohmann::json params = nlohmann::json::object();
        params["tableId"] = std::string(tableId);
        params["name"] = std::string(name);
        if (permissions.has_value()) {
            params["permissions"] = *permissions;
        }
        if (rowSecurity.has_value()) {
            params["rowSecurity"] = *rowSecurity;
        }
        if (enabled.has_value()) {
            params["enabled"] = *enabled;
        }
        if (columns.has_value()) {
            params["columns"] = *columns;
        }
        if (indexes.has_value()) {
            params["indexes"] = *indexes;
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::Table>("POST", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Get a table by its unique ID. This endpoint response returns a JSON object
     * with the table metadata.
     *
     * @param databaseId Database ID.
     * @param tableId Table ID.
     * @return appwrite::Result<appwrite::models::Table>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::Table> getTable(        std::string_view databaseId,         std::string_view tableId    ) {
                std::string path_ = std::format("/tablesdb/{}/tables/{}", databaseId, tableId);
        
        nlohmann::json params = nlohmann::json::object();
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::Table>("GET", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Get a table by its unique ID. This endpoint response returns a JSON object
     * with the table metadata.
     *
     * @param databaseId Database ID.
     * @param tableId Table ID.
     * @return appwrite::Result<appwrite::models::Table>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::Table>> getTableAsync(        std::string_view databaseId,         std::string_view tableId    ) {
                std::string path_ = std::format("/tablesdb/{}/tables/{}", databaseId, tableId);
        
        nlohmann::json params = nlohmann::json::object();
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::Table>("GET", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Update a table by its unique ID.
     *
     * @param databaseId Database ID.
     * @param tableId Table ID.
     * @param name Table name. Max length: 128 chars.
     * @param permissions An array of permission strings. By default, the current permissions are inherited. [Learn more about permissions](https://appwrite.io/docs/permissions).
     * @param rowSecurity Enables configuring permissions for individual rows. A user needs one of row or table-level permissions to access a row. [Learn more about permissions](https://appwrite.io/docs/permissions).
     * @param enabled Is table enabled? When set to 'disabled', users cannot access the table but Server SDKs with and API key can still read and write to the table. No data is lost when this is toggled.
     * @param purge When true, purge all cached list responses for this table as part of the update. Use this to force readers to see fresh data immediately instead of waiting for the cache TTL to expire.
     * @return appwrite::Result<appwrite::models::Table>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::Table> updateTable(        std::string_view databaseId,         std::string_view tableId,         std::optional<std::string_view> name = std::nullopt,         std::optional<std::vector<std::string>> permissions = std::nullopt,         std::optional<bool> rowSecurity = false,         std::optional<bool> enabled = true,         std::optional<bool> purge = false    ) {
                std::string path_ = std::format("/tablesdb/{}/tables/{}", databaseId, tableId);
        
        nlohmann::json params = nlohmann::json::object();
        if (name.has_value()) {
            params["name"] = std::string(name.value());
        }
        if (permissions.has_value()) {
            params["permissions"] = *permissions;
        }
        if (rowSecurity.has_value()) {
            params["rowSecurity"] = *rowSecurity;
        }
        if (enabled.has_value()) {
            params["enabled"] = *enabled;
        }
        if (purge.has_value()) {
            params["purge"] = *purge;
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::Table>("PUT", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Update a table by its unique ID.
     *
     * @param databaseId Database ID.
     * @param tableId Table ID.
     * @param name Table name. Max length: 128 chars.
     * @param permissions An array of permission strings. By default, the current permissions are inherited. [Learn more about permissions](https://appwrite.io/docs/permissions).
     * @param rowSecurity Enables configuring permissions for individual rows. A user needs one of row or table-level permissions to access a row. [Learn more about permissions](https://appwrite.io/docs/permissions).
     * @param enabled Is table enabled? When set to 'disabled', users cannot access the table but Server SDKs with and API key can still read and write to the table. No data is lost when this is toggled.
     * @param purge When true, purge all cached list responses for this table as part of the update. Use this to force readers to see fresh data immediately instead of waiting for the cache TTL to expire.
     * @return appwrite::Result<appwrite::models::Table>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::Table>> updateTableAsync(        std::string_view databaseId,         std::string_view tableId,         std::optional<std::string_view> name = std::nullopt,         std::optional<std::vector<std::string>> permissions = std::nullopt,         std::optional<bool> rowSecurity = false,         std::optional<bool> enabled = true,         std::optional<bool> purge = false    ) {
                std::string path_ = std::format("/tablesdb/{}/tables/{}", databaseId, tableId);
        
        nlohmann::json params = nlohmann::json::object();
        if (name.has_value()) {
            params["name"] = std::string(name.value());
        }
        if (permissions.has_value()) {
            params["permissions"] = *permissions;
        }
        if (rowSecurity.has_value()) {
            params["rowSecurity"] = *rowSecurity;
        }
        if (enabled.has_value()) {
            params["enabled"] = *enabled;
        }
        if (purge.has_value()) {
            params["purge"] = *purge;
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::Table>("PUT", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Delete a table by its unique ID. Only users with write permissions have
     * access to delete this resource.
     *
     * @param databaseId Database ID.
     * @param tableId Table ID.
     * @return appwrite::Result<void>
     */
    [[nodiscard]] appwrite::Result<void> deleteTable(        std::string_view databaseId,         std::string_view tableId    ) {
                std::string path_ = std::format("/tablesdb/{}/tables/{}", databaseId, tableId);
        
        nlohmann::json params = nlohmann::json::object();
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callVoid("DELETE", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Delete a table by its unique ID. Only users with write permissions have
     * access to delete this resource.
     *
     * @param databaseId Database ID.
     * @param tableId Table ID.
     * @return appwrite::Result<void>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<void>> deleteTableAsync(        std::string_view databaseId,         std::string_view tableId    ) {
                std::string path_ = std::format("/tablesdb/{}/tables/{}", databaseId, tableId);
        
        nlohmann::json params = nlohmann::json::object();
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callVoidAsync("DELETE", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * List columns in the table.
     *
     * @param databaseId Database ID.
     * @param tableId Table ID.
     * @param queries Array of query strings generated using the Query class provided by the SDK. [Learn more about queries](https://appwrite.io/docs/queries). Maximum of 100 queries are allowed, each 4096 characters long. You may filter on the following columns: key, type, size, required, array, status, error
     * @param total When set to false, the total count returned will be 0 and will not be calculated.
     * @return appwrite::Result<appwrite::models::ColumnList>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::ColumnList> listColumns(        std::string_view databaseId,         std::string_view tableId,         std::optional<std::vector<std::string>> queries = std::vector<std::string>{},         std::optional<bool> total = true    ) {
                std::string path_ = std::format("/tablesdb/{}/tables/{}/columns", databaseId, tableId);
        
        nlohmann::json params = nlohmann::json::object();
        if (queries.has_value()) {
            params["queries"] = *queries;
        }
        if (total.has_value()) {
            params["total"] = *total;
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::ColumnList>("GET", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * List columns in the table.
     *
     * @param databaseId Database ID.
     * @param tableId Table ID.
     * @param queries Array of query strings generated using the Query class provided by the SDK. [Learn more about queries](https://appwrite.io/docs/queries). Maximum of 100 queries are allowed, each 4096 characters long. You may filter on the following columns: key, type, size, required, array, status, error
     * @param total When set to false, the total count returned will be 0 and will not be calculated.
     * @return appwrite::Result<appwrite::models::ColumnList>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::ColumnList>> listColumnsAsync(        std::string_view databaseId,         std::string_view tableId,         std::optional<std::vector<std::string>> queries = std::vector<std::string>{},         std::optional<bool> total = true    ) {
                std::string path_ = std::format("/tablesdb/{}/tables/{}/columns", databaseId, tableId);
        
        nlohmann::json params = nlohmann::json::object();
        if (queries.has_value()) {
            params["queries"] = *queries;
        }
        if (total.has_value()) {
            params["total"] = *total;
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::ColumnList>("GET", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Create a boolean column.
     *
     * @param databaseId Database ID.
     * @param tableId Table ID. You can create a new table using the Database service [server integration](https://appwrite.io/docs/references/cloud/server-dart/tablesDB#createTable).
     * @param key Column Key.
     * @param required Is column required?
     * @param default_ Default value for column when not provided. Cannot be set when column is required.
     * @param array Is column an array?
     * @return appwrite::Result<appwrite::models::ColumnBoolean>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::ColumnBoolean> createBooleanColumn(        std::string_view databaseId,         std::string_view tableId,         std::string_view key,         bool required,         std::optional<bool> default_ = std::nullopt,         std::optional<bool> array = false    ) {
                std::string path_ = std::format("/tablesdb/{}/tables/{}/columns/boolean", databaseId, tableId);
        
        nlohmann::json params = nlohmann::json::object();
        params["key"] = std::string(key);
        params["required"] = required;
        if (default_.has_value()) {
            params["default"] = *default_;
        }
        if (array.has_value()) {
            params["array"] = *array;
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::ColumnBoolean>("POST", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Create a boolean column.
     *
     * @param databaseId Database ID.
     * @param tableId Table ID. You can create a new table using the Database service [server integration](https://appwrite.io/docs/references/cloud/server-dart/tablesDB#createTable).
     * @param key Column Key.
     * @param required Is column required?
     * @param default_ Default value for column when not provided. Cannot be set when column is required.
     * @param array Is column an array?
     * @return appwrite::Result<appwrite::models::ColumnBoolean>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::ColumnBoolean>> createBooleanColumnAsync(        std::string_view databaseId,         std::string_view tableId,         std::string_view key,         bool required,         std::optional<bool> default_ = std::nullopt,         std::optional<bool> array = false    ) {
                std::string path_ = std::format("/tablesdb/{}/tables/{}/columns/boolean", databaseId, tableId);
        
        nlohmann::json params = nlohmann::json::object();
        params["key"] = std::string(key);
        params["required"] = required;
        if (default_.has_value()) {
            params["default"] = *default_;
        }
        if (array.has_value()) {
            params["array"] = *array;
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::ColumnBoolean>("POST", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Update a boolean column. Changing the `default` value will not update
     * already existing rows.
     *
     * @param databaseId Database ID.
     * @param tableId Table ID. You can create a new table using the Database service [server integration](https://appwrite.io/docs/references/cloud/server-dart/tablesDB#createTable).
     * @param key Column Key.
     * @param required Is column required?
     * @param default_ Default value for column when not provided. Cannot be set when column is required.
     * @param newKey New Column Key.
     * @return appwrite::Result<appwrite::models::ColumnBoolean>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::ColumnBoolean> updateBooleanColumn(        std::string_view databaseId,         std::string_view tableId,         std::string_view key,         bool required,         bool default_,         std::optional<std::string_view> newKey = std::nullopt    ) {
                std::string path_ = std::format("/tablesdb/{}/tables/{}/columns/boolean/{}", databaseId, tableId, key);
        
        nlohmann::json params = nlohmann::json::object();
        params["required"] = required;
        params["default"] = default_;
        if (newKey.has_value()) {
            params["newKey"] = std::string(newKey.value());
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::ColumnBoolean>("PATCH", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Update a boolean column. Changing the `default` value will not update
     * already existing rows.
     *
     * @param databaseId Database ID.
     * @param tableId Table ID. You can create a new table using the Database service [server integration](https://appwrite.io/docs/references/cloud/server-dart/tablesDB#createTable).
     * @param key Column Key.
     * @param required Is column required?
     * @param default_ Default value for column when not provided. Cannot be set when column is required.
     * @param newKey New Column Key.
     * @return appwrite::Result<appwrite::models::ColumnBoolean>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::ColumnBoolean>> updateBooleanColumnAsync(        std::string_view databaseId,         std::string_view tableId,         std::string_view key,         bool required,         bool default_,         std::optional<std::string_view> newKey = std::nullopt    ) {
                std::string path_ = std::format("/tablesdb/{}/tables/{}/columns/boolean/{}", databaseId, tableId, key);
        
        nlohmann::json params = nlohmann::json::object();
        params["required"] = required;
        params["default"] = default_;
        if (newKey.has_value()) {
            params["newKey"] = std::string(newKey.value());
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::ColumnBoolean>("PATCH", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Create a date time column according to the ISO 8601 standard.
     *
     * @param databaseId Database ID.
     * @param tableId Table ID.
     * @param key Column Key.
     * @param required Is column required?
     * @param default_ Default value for the column in [ISO 8601](https://www.iso.org/iso-8601-date-and-time-format.html) format. Cannot be set when column is required.
     * @param array Is column an array?
     * @return appwrite::Result<appwrite::models::ColumnDatetime>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::ColumnDatetime> createDatetimeColumn(        std::string_view databaseId,         std::string_view tableId,         std::string_view key,         bool required,         std::optional<std::string_view> default_ = std::nullopt,         std::optional<bool> array = false    ) {
                std::string path_ = std::format("/tablesdb/{}/tables/{}/columns/datetime", databaseId, tableId);
        
        nlohmann::json params = nlohmann::json::object();
        params["key"] = std::string(key);
        params["required"] = required;
        if (default_.has_value()) {
            params["default"] = std::string(default_.value());
        }
        if (array.has_value()) {
            params["array"] = *array;
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::ColumnDatetime>("POST", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Create a date time column according to the ISO 8601 standard.
     *
     * @param databaseId Database ID.
     * @param tableId Table ID.
     * @param key Column Key.
     * @param required Is column required?
     * @param default_ Default value for the column in [ISO 8601](https://www.iso.org/iso-8601-date-and-time-format.html) format. Cannot be set when column is required.
     * @param array Is column an array?
     * @return appwrite::Result<appwrite::models::ColumnDatetime>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::ColumnDatetime>> createDatetimeColumnAsync(        std::string_view databaseId,         std::string_view tableId,         std::string_view key,         bool required,         std::optional<std::string_view> default_ = std::nullopt,         std::optional<bool> array = false    ) {
                std::string path_ = std::format("/tablesdb/{}/tables/{}/columns/datetime", databaseId, tableId);
        
        nlohmann::json params = nlohmann::json::object();
        params["key"] = std::string(key);
        params["required"] = required;
        if (default_.has_value()) {
            params["default"] = std::string(default_.value());
        }
        if (array.has_value()) {
            params["array"] = *array;
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::ColumnDatetime>("POST", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Update a date time column. Changing the `default` value will not update
     * already existing rows.
     *
     * @param databaseId Database ID.
     * @param tableId Table ID.
     * @param key Column Key.
     * @param required Is column required?
     * @param default_ Default value for column when not provided. Cannot be set when column is required.
     * @param newKey New Column Key.
     * @return appwrite::Result<appwrite::models::ColumnDatetime>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::ColumnDatetime> updateDatetimeColumn(        std::string_view databaseId,         std::string_view tableId,         std::string_view key,         bool required,         std::string_view default_,         std::optional<std::string_view> newKey = std::nullopt    ) {
                std::string path_ = std::format("/tablesdb/{}/tables/{}/columns/datetime/{}", databaseId, tableId, key);
        
        nlohmann::json params = nlohmann::json::object();
        params["required"] = required;
        params["default"] = std::string(default_);
        if (newKey.has_value()) {
            params["newKey"] = std::string(newKey.value());
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::ColumnDatetime>("PATCH", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Update a date time column. Changing the `default` value will not update
     * already existing rows.
     *
     * @param databaseId Database ID.
     * @param tableId Table ID.
     * @param key Column Key.
     * @param required Is column required?
     * @param default_ Default value for column when not provided. Cannot be set when column is required.
     * @param newKey New Column Key.
     * @return appwrite::Result<appwrite::models::ColumnDatetime>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::ColumnDatetime>> updateDatetimeColumnAsync(        std::string_view databaseId,         std::string_view tableId,         std::string_view key,         bool required,         std::string_view default_,         std::optional<std::string_view> newKey = std::nullopt    ) {
                std::string path_ = std::format("/tablesdb/{}/tables/{}/columns/datetime/{}", databaseId, tableId, key);
        
        nlohmann::json params = nlohmann::json::object();
        params["required"] = required;
        params["default"] = std::string(default_);
        if (newKey.has_value()) {
            params["newKey"] = std::string(newKey.value());
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::ColumnDatetime>("PATCH", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Create an email column.
     *
     * @param databaseId Database ID.
     * @param tableId Table ID.
     * @param key Column Key.
     * @param required Is column required?
     * @param default_ Default value for column when not provided. Cannot be set when column is required.
     * @param array Is column an array?
     * @return appwrite::Result<appwrite::models::ColumnEmail>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::ColumnEmail> createEmailColumn(        std::string_view databaseId,         std::string_view tableId,         std::string_view key,         bool required,         std::optional<std::string_view> default_ = std::nullopt,         std::optional<bool> array = false    ) {
                std::string path_ = std::format("/tablesdb/{}/tables/{}/columns/email", databaseId, tableId);
        
        nlohmann::json params = nlohmann::json::object();
        params["key"] = std::string(key);
        params["required"] = required;
        if (default_.has_value()) {
            params["default"] = std::string(default_.value());
        }
        if (array.has_value()) {
            params["array"] = *array;
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::ColumnEmail>("POST", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Create an email column.
     *
     * @param databaseId Database ID.
     * @param tableId Table ID.
     * @param key Column Key.
     * @param required Is column required?
     * @param default_ Default value for column when not provided. Cannot be set when column is required.
     * @param array Is column an array?
     * @return appwrite::Result<appwrite::models::ColumnEmail>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::ColumnEmail>> createEmailColumnAsync(        std::string_view databaseId,         std::string_view tableId,         std::string_view key,         bool required,         std::optional<std::string_view> default_ = std::nullopt,         std::optional<bool> array = false    ) {
                std::string path_ = std::format("/tablesdb/{}/tables/{}/columns/email", databaseId, tableId);
        
        nlohmann::json params = nlohmann::json::object();
        params["key"] = std::string(key);
        params["required"] = required;
        if (default_.has_value()) {
            params["default"] = std::string(default_.value());
        }
        if (array.has_value()) {
            params["array"] = *array;
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::ColumnEmail>("POST", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Update an email column. Changing the `default` value will not update
     * already existing rows.
     *
     * @param databaseId Database ID.
     * @param tableId Table ID.
     * @param key Column Key.
     * @param required Is column required?
     * @param default_ Default value for column when not provided. Cannot be set when column is required.
     * @param newKey New Column Key.
     * @return appwrite::Result<appwrite::models::ColumnEmail>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::ColumnEmail> updateEmailColumn(        std::string_view databaseId,         std::string_view tableId,         std::string_view key,         bool required,         std::string_view default_,         std::optional<std::string_view> newKey = std::nullopt    ) {
                std::string path_ = std::format("/tablesdb/{}/tables/{}/columns/email/{}", databaseId, tableId, key);
        
        nlohmann::json params = nlohmann::json::object();
        params["required"] = required;
        params["default"] = std::string(default_);
        if (newKey.has_value()) {
            params["newKey"] = std::string(newKey.value());
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::ColumnEmail>("PATCH", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Update an email column. Changing the `default` value will not update
     * already existing rows.
     *
     * @param databaseId Database ID.
     * @param tableId Table ID.
     * @param key Column Key.
     * @param required Is column required?
     * @param default_ Default value for column when not provided. Cannot be set when column is required.
     * @param newKey New Column Key.
     * @return appwrite::Result<appwrite::models::ColumnEmail>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::ColumnEmail>> updateEmailColumnAsync(        std::string_view databaseId,         std::string_view tableId,         std::string_view key,         bool required,         std::string_view default_,         std::optional<std::string_view> newKey = std::nullopt    ) {
                std::string path_ = std::format("/tablesdb/{}/tables/{}/columns/email/{}", databaseId, tableId, key);
        
        nlohmann::json params = nlohmann::json::object();
        params["required"] = required;
        params["default"] = std::string(default_);
        if (newKey.has_value()) {
            params["newKey"] = std::string(newKey.value());
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::ColumnEmail>("PATCH", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Create an enumeration column. The `elements` param acts as a white-list of
     * accepted values for this column.
     *
     * @param databaseId Database ID.
     * @param tableId Table ID.
     * @param key Column Key.
     * @param elements Array of enum values.
     * @param required Is column required?
     * @param default_ Default value for column when not provided. Cannot be set when column is required.
     * @param array Is column an array?
     * @return appwrite::Result<appwrite::models::ColumnEnum>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::ColumnEnum> createEnumColumn(        std::string_view databaseId,         std::string_view tableId,         std::string_view key,         std::vector<std::string> elements,         bool required,         std::optional<std::string_view> default_ = std::nullopt,         std::optional<bool> array = false    ) {
                std::string path_ = std::format("/tablesdb/{}/tables/{}/columns/enum", databaseId, tableId);
        
        nlohmann::json params = nlohmann::json::object();
        params["key"] = std::string(key);
        params["elements"] = elements;
        params["required"] = required;
        if (default_.has_value()) {
            params["default"] = std::string(default_.value());
        }
        if (array.has_value()) {
            params["array"] = *array;
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::ColumnEnum>("POST", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Create an enumeration column. The `elements` param acts as a white-list of
     * accepted values for this column.
     *
     * @param databaseId Database ID.
     * @param tableId Table ID.
     * @param key Column Key.
     * @param elements Array of enum values.
     * @param required Is column required?
     * @param default_ Default value for column when not provided. Cannot be set when column is required.
     * @param array Is column an array?
     * @return appwrite::Result<appwrite::models::ColumnEnum>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::ColumnEnum>> createEnumColumnAsync(        std::string_view databaseId,         std::string_view tableId,         std::string_view key,         std::vector<std::string> elements,         bool required,         std::optional<std::string_view> default_ = std::nullopt,         std::optional<bool> array = false    ) {
                std::string path_ = std::format("/tablesdb/{}/tables/{}/columns/enum", databaseId, tableId);
        
        nlohmann::json params = nlohmann::json::object();
        params["key"] = std::string(key);
        params["elements"] = elements;
        params["required"] = required;
        if (default_.has_value()) {
            params["default"] = std::string(default_.value());
        }
        if (array.has_value()) {
            params["array"] = *array;
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::ColumnEnum>("POST", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Update an enum column. Changing the `default` value will not update already
     * existing rows.
     *
     * @param databaseId Database ID.
     * @param tableId Table ID.
     * @param key Column Key.
     * @param elements Updated list of enum values.
     * @param required Is column required?
     * @param default_ Default value for column when not provided. Cannot be set when column is required.
     * @param newKey New Column Key.
     * @return appwrite::Result<appwrite::models::ColumnEnum>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::ColumnEnum> updateEnumColumn(        std::string_view databaseId,         std::string_view tableId,         std::string_view key,         std::vector<std::string> elements,         bool required,         std::string_view default_,         std::optional<std::string_view> newKey = std::nullopt    ) {
                std::string path_ = std::format("/tablesdb/{}/tables/{}/columns/enum/{}", databaseId, tableId, key);
        
        nlohmann::json params = nlohmann::json::object();
        params["elements"] = elements;
        params["required"] = required;
        params["default"] = std::string(default_);
        if (newKey.has_value()) {
            params["newKey"] = std::string(newKey.value());
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::ColumnEnum>("PATCH", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Update an enum column. Changing the `default` value will not update already
     * existing rows.
     *
     * @param databaseId Database ID.
     * @param tableId Table ID.
     * @param key Column Key.
     * @param elements Updated list of enum values.
     * @param required Is column required?
     * @param default_ Default value for column when not provided. Cannot be set when column is required.
     * @param newKey New Column Key.
     * @return appwrite::Result<appwrite::models::ColumnEnum>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::ColumnEnum>> updateEnumColumnAsync(        std::string_view databaseId,         std::string_view tableId,         std::string_view key,         std::vector<std::string> elements,         bool required,         std::string_view default_,         std::optional<std::string_view> newKey = std::nullopt    ) {
                std::string path_ = std::format("/tablesdb/{}/tables/{}/columns/enum/{}", databaseId, tableId, key);
        
        nlohmann::json params = nlohmann::json::object();
        params["elements"] = elements;
        params["required"] = required;
        params["default"] = std::string(default_);
        if (newKey.has_value()) {
            params["newKey"] = std::string(newKey.value());
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::ColumnEnum>("PATCH", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Create a float column. Optionally, minimum and maximum values can be
     * provided.
     *
     * @param databaseId Database ID.
     * @param tableId Table ID.
     * @param key Column Key.
     * @param required Is column required?
     * @param min Minimum value
     * @param max Maximum value
     * @param default_ Default value. Cannot be set when required.
     * @param array Is column an array?
     * @return appwrite::Result<appwrite::models::ColumnFloat>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::ColumnFloat> createFloatColumn(        std::string_view databaseId,         std::string_view tableId,         std::string_view key,         bool required,         std::optional<double> min = std::nullopt,         std::optional<double> max = std::nullopt,         std::optional<double> default_ = std::nullopt,         std::optional<bool> array = false    ) {
                std::string path_ = std::format("/tablesdb/{}/tables/{}/columns/float", databaseId, tableId);
        
        nlohmann::json params = nlohmann::json::object();
        params["key"] = std::string(key);
        params["required"] = required;
        if (min.has_value()) {
            params["min"] = *min;
        }
        if (max.has_value()) {
            params["max"] = *max;
        }
        if (default_.has_value()) {
            params["default"] = *default_;
        }
        if (array.has_value()) {
            params["array"] = *array;
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::ColumnFloat>("POST", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Create a float column. Optionally, minimum and maximum values can be
     * provided.
     *
     * @param databaseId Database ID.
     * @param tableId Table ID.
     * @param key Column Key.
     * @param required Is column required?
     * @param min Minimum value
     * @param max Maximum value
     * @param default_ Default value. Cannot be set when required.
     * @param array Is column an array?
     * @return appwrite::Result<appwrite::models::ColumnFloat>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::ColumnFloat>> createFloatColumnAsync(        std::string_view databaseId,         std::string_view tableId,         std::string_view key,         bool required,         std::optional<double> min = std::nullopt,         std::optional<double> max = std::nullopt,         std::optional<double> default_ = std::nullopt,         std::optional<bool> array = false    ) {
                std::string path_ = std::format("/tablesdb/{}/tables/{}/columns/float", databaseId, tableId);
        
        nlohmann::json params = nlohmann::json::object();
        params["key"] = std::string(key);
        params["required"] = required;
        if (min.has_value()) {
            params["min"] = *min;
        }
        if (max.has_value()) {
            params["max"] = *max;
        }
        if (default_.has_value()) {
            params["default"] = *default_;
        }
        if (array.has_value()) {
            params["array"] = *array;
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::ColumnFloat>("POST", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Update a float column. Changing the `default` value will not update already
     * existing rows.
     *
     * @param databaseId Database ID.
     * @param tableId Table ID.
     * @param key Column Key.
     * @param required Is column required?
     * @param default_ Default value. Cannot be set when required.
     * @param min Minimum value
     * @param max Maximum value
     * @param newKey New Column Key.
     * @return appwrite::Result<appwrite::models::ColumnFloat>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::ColumnFloat> updateFloatColumn(        std::string_view databaseId,         std::string_view tableId,         std::string_view key,         bool required,         double default_,         std::optional<double> min = std::nullopt,         std::optional<double> max = std::nullopt,         std::optional<std::string_view> newKey = std::nullopt    ) {
                std::string path_ = std::format("/tablesdb/{}/tables/{}/columns/float/{}", databaseId, tableId, key);
        
        nlohmann::json params = nlohmann::json::object();
        params["required"] = required;
        if (min.has_value()) {
            params["min"] = *min;
        }
        if (max.has_value()) {
            params["max"] = *max;
        }
        params["default"] = default_;
        if (newKey.has_value()) {
            params["newKey"] = std::string(newKey.value());
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::ColumnFloat>("PATCH", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Update a float column. Changing the `default` value will not update already
     * existing rows.
     *
     * @param databaseId Database ID.
     * @param tableId Table ID.
     * @param key Column Key.
     * @param required Is column required?
     * @param default_ Default value. Cannot be set when required.
     * @param min Minimum value
     * @param max Maximum value
     * @param newKey New Column Key.
     * @return appwrite::Result<appwrite::models::ColumnFloat>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::ColumnFloat>> updateFloatColumnAsync(        std::string_view databaseId,         std::string_view tableId,         std::string_view key,         bool required,         double default_,         std::optional<double> min = std::nullopt,         std::optional<double> max = std::nullopt,         std::optional<std::string_view> newKey = std::nullopt    ) {
                std::string path_ = std::format("/tablesdb/{}/tables/{}/columns/float/{}", databaseId, tableId, key);
        
        nlohmann::json params = nlohmann::json::object();
        params["required"] = required;
        if (min.has_value()) {
            params["min"] = *min;
        }
        if (max.has_value()) {
            params["max"] = *max;
        }
        params["default"] = default_;
        if (newKey.has_value()) {
            params["newKey"] = std::string(newKey.value());
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::ColumnFloat>("PATCH", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Create an integer column. Optionally, minimum and maximum values can be
     * provided.
     *
     * @param databaseId Database ID.
     * @param tableId Table ID.
     * @param key Column Key.
     * @param required Is column required?
     * @param min Minimum value
     * @param max Maximum value
     * @param default_ Default value. Cannot be set when column is required.
     * @param array Is column an array?
     * @return appwrite::Result<appwrite::models::ColumnInteger>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::ColumnInteger> createIntegerColumn(        std::string_view databaseId,         std::string_view tableId,         std::string_view key,         bool required,         std::optional<int64_t> min = std::nullopt,         std::optional<int64_t> max = std::nullopt,         std::optional<int64_t> default_ = std::nullopt,         std::optional<bool> array = false    ) {
                std::string path_ = std::format("/tablesdb/{}/tables/{}/columns/integer", databaseId, tableId);
        
        nlohmann::json params = nlohmann::json::object();
        params["key"] = std::string(key);
        params["required"] = required;
        if (min.has_value()) {
            params["min"] = *min;
        }
        if (max.has_value()) {
            params["max"] = *max;
        }
        if (default_.has_value()) {
            params["default"] = *default_;
        }
        if (array.has_value()) {
            params["array"] = *array;
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::ColumnInteger>("POST", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Create an integer column. Optionally, minimum and maximum values can be
     * provided.
     *
     * @param databaseId Database ID.
     * @param tableId Table ID.
     * @param key Column Key.
     * @param required Is column required?
     * @param min Minimum value
     * @param max Maximum value
     * @param default_ Default value. Cannot be set when column is required.
     * @param array Is column an array?
     * @return appwrite::Result<appwrite::models::ColumnInteger>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::ColumnInteger>> createIntegerColumnAsync(        std::string_view databaseId,         std::string_view tableId,         std::string_view key,         bool required,         std::optional<int64_t> min = std::nullopt,         std::optional<int64_t> max = std::nullopt,         std::optional<int64_t> default_ = std::nullopt,         std::optional<bool> array = false    ) {
                std::string path_ = std::format("/tablesdb/{}/tables/{}/columns/integer", databaseId, tableId);
        
        nlohmann::json params = nlohmann::json::object();
        params["key"] = std::string(key);
        params["required"] = required;
        if (min.has_value()) {
            params["min"] = *min;
        }
        if (max.has_value()) {
            params["max"] = *max;
        }
        if (default_.has_value()) {
            params["default"] = *default_;
        }
        if (array.has_value()) {
            params["array"] = *array;
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::ColumnInteger>("POST", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Update an integer column. Changing the `default` value will not update
     * already existing rows.
     *
     * @param databaseId Database ID.
     * @param tableId Table ID.
     * @param key Column Key.
     * @param required Is column required?
     * @param default_ Default value. Cannot be set when column is required.
     * @param min Minimum value
     * @param max Maximum value
     * @param newKey New Column Key.
     * @return appwrite::Result<appwrite::models::ColumnInteger>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::ColumnInteger> updateIntegerColumn(        std::string_view databaseId,         std::string_view tableId,         std::string_view key,         bool required,         int64_t default_,         std::optional<int64_t> min = std::nullopt,         std::optional<int64_t> max = std::nullopt,         std::optional<std::string_view> newKey = std::nullopt    ) {
                std::string path_ = std::format("/tablesdb/{}/tables/{}/columns/integer/{}", databaseId, tableId, key);
        
        nlohmann::json params = nlohmann::json::object();
        params["required"] = required;
        if (min.has_value()) {
            params["min"] = *min;
        }
        if (max.has_value()) {
            params["max"] = *max;
        }
        params["default"] = default_;
        if (newKey.has_value()) {
            params["newKey"] = std::string(newKey.value());
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::ColumnInteger>("PATCH", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Update an integer column. Changing the `default` value will not update
     * already existing rows.
     *
     * @param databaseId Database ID.
     * @param tableId Table ID.
     * @param key Column Key.
     * @param required Is column required?
     * @param default_ Default value. Cannot be set when column is required.
     * @param min Minimum value
     * @param max Maximum value
     * @param newKey New Column Key.
     * @return appwrite::Result<appwrite::models::ColumnInteger>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::ColumnInteger>> updateIntegerColumnAsync(        std::string_view databaseId,         std::string_view tableId,         std::string_view key,         bool required,         int64_t default_,         std::optional<int64_t> min = std::nullopt,         std::optional<int64_t> max = std::nullopt,         std::optional<std::string_view> newKey = std::nullopt    ) {
                std::string path_ = std::format("/tablesdb/{}/tables/{}/columns/integer/{}", databaseId, tableId, key);
        
        nlohmann::json params = nlohmann::json::object();
        params["required"] = required;
        if (min.has_value()) {
            params["min"] = *min;
        }
        if (max.has_value()) {
            params["max"] = *max;
        }
        params["default"] = default_;
        if (newKey.has_value()) {
            params["newKey"] = std::string(newKey.value());
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::ColumnInteger>("PATCH", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Create IP address column.
     *
     * @param databaseId Database ID.
     * @param tableId Table ID.
     * @param key Column Key.
     * @param required Is column required?
     * @param default_ Default value. Cannot be set when column is required.
     * @param array Is column an array?
     * @return appwrite::Result<appwrite::models::ColumnIp>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::ColumnIp> createIpColumn(        std::string_view databaseId,         std::string_view tableId,         std::string_view key,         bool required,         std::optional<std::string_view> default_ = std::nullopt,         std::optional<bool> array = false    ) {
                std::string path_ = std::format("/tablesdb/{}/tables/{}/columns/ip", databaseId, tableId);
        
        nlohmann::json params = nlohmann::json::object();
        params["key"] = std::string(key);
        params["required"] = required;
        if (default_.has_value()) {
            params["default"] = std::string(default_.value());
        }
        if (array.has_value()) {
            params["array"] = *array;
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::ColumnIp>("POST", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Create IP address column.
     *
     * @param databaseId Database ID.
     * @param tableId Table ID.
     * @param key Column Key.
     * @param required Is column required?
     * @param default_ Default value. Cannot be set when column is required.
     * @param array Is column an array?
     * @return appwrite::Result<appwrite::models::ColumnIp>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::ColumnIp>> createIpColumnAsync(        std::string_view databaseId,         std::string_view tableId,         std::string_view key,         bool required,         std::optional<std::string_view> default_ = std::nullopt,         std::optional<bool> array = false    ) {
                std::string path_ = std::format("/tablesdb/{}/tables/{}/columns/ip", databaseId, tableId);
        
        nlohmann::json params = nlohmann::json::object();
        params["key"] = std::string(key);
        params["required"] = required;
        if (default_.has_value()) {
            params["default"] = std::string(default_.value());
        }
        if (array.has_value()) {
            params["array"] = *array;
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::ColumnIp>("POST", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Update an ip column. Changing the `default` value will not update already
     * existing rows.
     *
     * @param databaseId Database ID.
     * @param tableId Table ID.
     * @param key Column Key.
     * @param required Is column required?
     * @param default_ Default value. Cannot be set when column is required.
     * @param newKey New Column Key.
     * @return appwrite::Result<appwrite::models::ColumnIp>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::ColumnIp> updateIpColumn(        std::string_view databaseId,         std::string_view tableId,         std::string_view key,         bool required,         std::string_view default_,         std::optional<std::string_view> newKey = std::nullopt    ) {
                std::string path_ = std::format("/tablesdb/{}/tables/{}/columns/ip/{}", databaseId, tableId, key);
        
        nlohmann::json params = nlohmann::json::object();
        params["required"] = required;
        params["default"] = std::string(default_);
        if (newKey.has_value()) {
            params["newKey"] = std::string(newKey.value());
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::ColumnIp>("PATCH", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Update an ip column. Changing the `default` value will not update already
     * existing rows.
     *
     * @param databaseId Database ID.
     * @param tableId Table ID.
     * @param key Column Key.
     * @param required Is column required?
     * @param default_ Default value. Cannot be set when column is required.
     * @param newKey New Column Key.
     * @return appwrite::Result<appwrite::models::ColumnIp>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::ColumnIp>> updateIpColumnAsync(        std::string_view databaseId,         std::string_view tableId,         std::string_view key,         bool required,         std::string_view default_,         std::optional<std::string_view> newKey = std::nullopt    ) {
                std::string path_ = std::format("/tablesdb/{}/tables/{}/columns/ip/{}", databaseId, tableId, key);
        
        nlohmann::json params = nlohmann::json::object();
        params["required"] = required;
        params["default"] = std::string(default_);
        if (newKey.has_value()) {
            params["newKey"] = std::string(newKey.value());
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::ColumnIp>("PATCH", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Create a geometric line column.
     *
     * @param databaseId Database ID.
     * @param tableId Table ID. You can create a new table using the TablesDB service [server integration](https://appwrite.io/docs/references/cloud/server-dart/tablesDB#createTable).
     * @param key Column Key.
     * @param required Is column required?
     * @param default_ Default value for column when not provided, two-dimensional array of coordinate pairs, [[longitude, latitude], [longitude, latitude], …], listing the vertices of the line in order. Cannot be set when column is required.
     * @return appwrite::Result<appwrite::models::ColumnLine>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::ColumnLine> createLineColumn(        std::string_view databaseId,         std::string_view tableId,         std::string_view key,         bool required,         std::optional<std::vector<std::string>> default_ = std::nullopt    ) {
                std::string path_ = std::format("/tablesdb/{}/tables/{}/columns/line", databaseId, tableId);
        
        nlohmann::json params = nlohmann::json::object();
        params["key"] = std::string(key);
        params["required"] = required;
        if (default_.has_value()) {
            params["default"] = *default_;
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::ColumnLine>("POST", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Create a geometric line column.
     *
     * @param databaseId Database ID.
     * @param tableId Table ID. You can create a new table using the TablesDB service [server integration](https://appwrite.io/docs/references/cloud/server-dart/tablesDB#createTable).
     * @param key Column Key.
     * @param required Is column required?
     * @param default_ Default value for column when not provided, two-dimensional array of coordinate pairs, [[longitude, latitude], [longitude, latitude], …], listing the vertices of the line in order. Cannot be set when column is required.
     * @return appwrite::Result<appwrite::models::ColumnLine>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::ColumnLine>> createLineColumnAsync(        std::string_view databaseId,         std::string_view tableId,         std::string_view key,         bool required,         std::optional<std::vector<std::string>> default_ = std::nullopt    ) {
                std::string path_ = std::format("/tablesdb/{}/tables/{}/columns/line", databaseId, tableId);
        
        nlohmann::json params = nlohmann::json::object();
        params["key"] = std::string(key);
        params["required"] = required;
        if (default_.has_value()) {
            params["default"] = *default_;
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::ColumnLine>("POST", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Update a line column. Changing the `default` value will not update already
     * existing rows.
     *
     * @param databaseId Database ID.
     * @param tableId Table ID. You can create a new table using the TablesDB service [server integration](https://appwrite.io/docs/references/cloud/server-dart/tablesDB#createTable).
     * @param key Column Key.
     * @param required Is column required?
     * @param default_ Default value for column when not provided, two-dimensional array of coordinate pairs, [[longitude, latitude], [longitude, latitude], …], listing the vertices of the line in order. Cannot be set when column is required.
     * @param newKey New Column Key.
     * @return appwrite::Result<appwrite::models::ColumnLine>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::ColumnLine> updateLineColumn(        std::string_view databaseId,         std::string_view tableId,         std::string_view key,         bool required,         std::optional<std::vector<std::string>> default_ = std::nullopt,         std::optional<std::string_view> newKey = std::nullopt    ) {
                std::string path_ = std::format("/tablesdb/{}/tables/{}/columns/line/{}", databaseId, tableId, key);
        
        nlohmann::json params = nlohmann::json::object();
        params["required"] = required;
        if (default_.has_value()) {
            params["default"] = *default_;
        }
        if (newKey.has_value()) {
            params["newKey"] = std::string(newKey.value());
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::ColumnLine>("PATCH", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Update a line column. Changing the `default` value will not update already
     * existing rows.
     *
     * @param databaseId Database ID.
     * @param tableId Table ID. You can create a new table using the TablesDB service [server integration](https://appwrite.io/docs/references/cloud/server-dart/tablesDB#createTable).
     * @param key Column Key.
     * @param required Is column required?
     * @param default_ Default value for column when not provided, two-dimensional array of coordinate pairs, [[longitude, latitude], [longitude, latitude], …], listing the vertices of the line in order. Cannot be set when column is required.
     * @param newKey New Column Key.
     * @return appwrite::Result<appwrite::models::ColumnLine>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::ColumnLine>> updateLineColumnAsync(        std::string_view databaseId,         std::string_view tableId,         std::string_view key,         bool required,         std::optional<std::vector<std::string>> default_ = std::nullopt,         std::optional<std::string_view> newKey = std::nullopt    ) {
                std::string path_ = std::format("/tablesdb/{}/tables/{}/columns/line/{}", databaseId, tableId, key);
        
        nlohmann::json params = nlohmann::json::object();
        params["required"] = required;
        if (default_.has_value()) {
            params["default"] = *default_;
        }
        if (newKey.has_value()) {
            params["newKey"] = std::string(newKey.value());
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::ColumnLine>("PATCH", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Create a longtext column.
     *
     * @param databaseId Database ID.
     * @param tableId Table ID. You can create a new table using the Database service [server integration](https://appwrite.io/docs/references/cloud/server-dart/tablesDB#createTable).
     * @param key Column Key.
     * @param required Is column required?
     * @param default_ Default value for column when not provided. Cannot be set when column is required.
     * @param array Is column an array?
     * @param encrypt Toggle encryption for the column. Encryption enhances security by not storing any plain text values in the database. However, encrypted columns cannot be queried.
     * @return appwrite::Result<appwrite::models::ColumnLongtext>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::ColumnLongtext> createLongtextColumn(        std::string_view databaseId,         std::string_view tableId,         std::string_view key,         bool required,         std::optional<std::string_view> default_ = std::nullopt,         std::optional<bool> array = false,         std::optional<bool> encrypt = false    ) {
                std::string path_ = std::format("/tablesdb/{}/tables/{}/columns/longtext", databaseId, tableId);
        
        nlohmann::json params = nlohmann::json::object();
        params["key"] = std::string(key);
        params["required"] = required;
        if (default_.has_value()) {
            params["default"] = std::string(default_.value());
        }
        if (array.has_value()) {
            params["array"] = *array;
        }
        if (encrypt.has_value()) {
            params["encrypt"] = *encrypt;
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::ColumnLongtext>("POST", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Create a longtext column.
     *
     * @param databaseId Database ID.
     * @param tableId Table ID. You can create a new table using the Database service [server integration](https://appwrite.io/docs/references/cloud/server-dart/tablesDB#createTable).
     * @param key Column Key.
     * @param required Is column required?
     * @param default_ Default value for column when not provided. Cannot be set when column is required.
     * @param array Is column an array?
     * @param encrypt Toggle encryption for the column. Encryption enhances security by not storing any plain text values in the database. However, encrypted columns cannot be queried.
     * @return appwrite::Result<appwrite::models::ColumnLongtext>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::ColumnLongtext>> createLongtextColumnAsync(        std::string_view databaseId,         std::string_view tableId,         std::string_view key,         bool required,         std::optional<std::string_view> default_ = std::nullopt,         std::optional<bool> array = false,         std::optional<bool> encrypt = false    ) {
                std::string path_ = std::format("/tablesdb/{}/tables/{}/columns/longtext", databaseId, tableId);
        
        nlohmann::json params = nlohmann::json::object();
        params["key"] = std::string(key);
        params["required"] = required;
        if (default_.has_value()) {
            params["default"] = std::string(default_.value());
        }
        if (array.has_value()) {
            params["array"] = *array;
        }
        if (encrypt.has_value()) {
            params["encrypt"] = *encrypt;
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::ColumnLongtext>("POST", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Update a longtext column. Changing the `default` value will not update
     * already existing rows.
     *
     * @param databaseId Database ID.
     * @param tableId Table ID. You can create a new table using the Database service [server integration](https://appwrite.io/docs/references/cloud/server-dart/tablesDB#createTable).
     * @param key Column Key.
     * @param required Is column required?
     * @param default_ Default value for column when not provided. Cannot be set when column is required.
     * @param newKey New Column Key.
     * @return appwrite::Result<appwrite::models::ColumnLongtext>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::ColumnLongtext> updateLongtextColumn(        std::string_view databaseId,         std::string_view tableId,         std::string_view key,         bool required,         std::string_view default_,         std::optional<std::string_view> newKey = std::nullopt    ) {
                std::string path_ = std::format("/tablesdb/{}/tables/{}/columns/longtext/{}", databaseId, tableId, key);
        
        nlohmann::json params = nlohmann::json::object();
        params["required"] = required;
        params["default"] = std::string(default_);
        if (newKey.has_value()) {
            params["newKey"] = std::string(newKey.value());
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::ColumnLongtext>("PATCH", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Update a longtext column. Changing the `default` value will not update
     * already existing rows.
     *
     * @param databaseId Database ID.
     * @param tableId Table ID. You can create a new table using the Database service [server integration](https://appwrite.io/docs/references/cloud/server-dart/tablesDB#createTable).
     * @param key Column Key.
     * @param required Is column required?
     * @param default_ Default value for column when not provided. Cannot be set when column is required.
     * @param newKey New Column Key.
     * @return appwrite::Result<appwrite::models::ColumnLongtext>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::ColumnLongtext>> updateLongtextColumnAsync(        std::string_view databaseId,         std::string_view tableId,         std::string_view key,         bool required,         std::string_view default_,         std::optional<std::string_view> newKey = std::nullopt    ) {
                std::string path_ = std::format("/tablesdb/{}/tables/{}/columns/longtext/{}", databaseId, tableId, key);
        
        nlohmann::json params = nlohmann::json::object();
        params["required"] = required;
        params["default"] = std::string(default_);
        if (newKey.has_value()) {
            params["newKey"] = std::string(newKey.value());
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::ColumnLongtext>("PATCH", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Create a mediumtext column.
     *
     * @param databaseId Database ID.
     * @param tableId Table ID. You can create a new table using the Database service [server integration](https://appwrite.io/docs/references/cloud/server-dart/tablesDB#createTable).
     * @param key Column Key.
     * @param required Is column required?
     * @param default_ Default value for column when not provided. Cannot be set when column is required.
     * @param array Is column an array?
     * @param encrypt Toggle encryption for the column. Encryption enhances security by not storing any plain text values in the database. However, encrypted columns cannot be queried.
     * @return appwrite::Result<appwrite::models::ColumnMediumtext>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::ColumnMediumtext> createMediumtextColumn(        std::string_view databaseId,         std::string_view tableId,         std::string_view key,         bool required,         std::optional<std::string_view> default_ = std::nullopt,         std::optional<bool> array = false,         std::optional<bool> encrypt = false    ) {
                std::string path_ = std::format("/tablesdb/{}/tables/{}/columns/mediumtext", databaseId, tableId);
        
        nlohmann::json params = nlohmann::json::object();
        params["key"] = std::string(key);
        params["required"] = required;
        if (default_.has_value()) {
            params["default"] = std::string(default_.value());
        }
        if (array.has_value()) {
            params["array"] = *array;
        }
        if (encrypt.has_value()) {
            params["encrypt"] = *encrypt;
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::ColumnMediumtext>("POST", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Create a mediumtext column.
     *
     * @param databaseId Database ID.
     * @param tableId Table ID. You can create a new table using the Database service [server integration](https://appwrite.io/docs/references/cloud/server-dart/tablesDB#createTable).
     * @param key Column Key.
     * @param required Is column required?
     * @param default_ Default value for column when not provided. Cannot be set when column is required.
     * @param array Is column an array?
     * @param encrypt Toggle encryption for the column. Encryption enhances security by not storing any plain text values in the database. However, encrypted columns cannot be queried.
     * @return appwrite::Result<appwrite::models::ColumnMediumtext>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::ColumnMediumtext>> createMediumtextColumnAsync(        std::string_view databaseId,         std::string_view tableId,         std::string_view key,         bool required,         std::optional<std::string_view> default_ = std::nullopt,         std::optional<bool> array = false,         std::optional<bool> encrypt = false    ) {
                std::string path_ = std::format("/tablesdb/{}/tables/{}/columns/mediumtext", databaseId, tableId);
        
        nlohmann::json params = nlohmann::json::object();
        params["key"] = std::string(key);
        params["required"] = required;
        if (default_.has_value()) {
            params["default"] = std::string(default_.value());
        }
        if (array.has_value()) {
            params["array"] = *array;
        }
        if (encrypt.has_value()) {
            params["encrypt"] = *encrypt;
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::ColumnMediumtext>("POST", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Update a mediumtext column. Changing the `default` value will not update
     * already existing rows.
     *
     * @param databaseId Database ID.
     * @param tableId Table ID. You can create a new table using the Database service [server integration](https://appwrite.io/docs/references/cloud/server-dart/tablesDB#createTable).
     * @param key Column Key.
     * @param required Is column required?
     * @param default_ Default value for column when not provided. Cannot be set when column is required.
     * @param newKey New Column Key.
     * @return appwrite::Result<appwrite::models::ColumnMediumtext>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::ColumnMediumtext> updateMediumtextColumn(        std::string_view databaseId,         std::string_view tableId,         std::string_view key,         bool required,         std::string_view default_,         std::optional<std::string_view> newKey = std::nullopt    ) {
                std::string path_ = std::format("/tablesdb/{}/tables/{}/columns/mediumtext/{}", databaseId, tableId, key);
        
        nlohmann::json params = nlohmann::json::object();
        params["required"] = required;
        params["default"] = std::string(default_);
        if (newKey.has_value()) {
            params["newKey"] = std::string(newKey.value());
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::ColumnMediumtext>("PATCH", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Update a mediumtext column. Changing the `default` value will not update
     * already existing rows.
     *
     * @param databaseId Database ID.
     * @param tableId Table ID. You can create a new table using the Database service [server integration](https://appwrite.io/docs/references/cloud/server-dart/tablesDB#createTable).
     * @param key Column Key.
     * @param required Is column required?
     * @param default_ Default value for column when not provided. Cannot be set when column is required.
     * @param newKey New Column Key.
     * @return appwrite::Result<appwrite::models::ColumnMediumtext>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::ColumnMediumtext>> updateMediumtextColumnAsync(        std::string_view databaseId,         std::string_view tableId,         std::string_view key,         bool required,         std::string_view default_,         std::optional<std::string_view> newKey = std::nullopt    ) {
                std::string path_ = std::format("/tablesdb/{}/tables/{}/columns/mediumtext/{}", databaseId, tableId, key);
        
        nlohmann::json params = nlohmann::json::object();
        params["required"] = required;
        params["default"] = std::string(default_);
        if (newKey.has_value()) {
            params["newKey"] = std::string(newKey.value());
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::ColumnMediumtext>("PATCH", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Create a geometric point column.
     *
     * @param databaseId Database ID.
     * @param tableId Table ID. You can create a new table using the TablesDB service [server integration](https://appwrite.io/docs/references/cloud/server-dart/tablesDB#createTable).
     * @param key Column Key.
     * @param required Is column required?
     * @param default_ Default value for column when not provided, array of two numbers [longitude, latitude], representing a single coordinate. Cannot be set when column is required.
     * @return appwrite::Result<appwrite::models::ColumnPoint>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::ColumnPoint> createPointColumn(        std::string_view databaseId,         std::string_view tableId,         std::string_view key,         bool required,         std::optional<std::vector<std::string>> default_ = std::nullopt    ) {
                std::string path_ = std::format("/tablesdb/{}/tables/{}/columns/point", databaseId, tableId);
        
        nlohmann::json params = nlohmann::json::object();
        params["key"] = std::string(key);
        params["required"] = required;
        if (default_.has_value()) {
            params["default"] = *default_;
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::ColumnPoint>("POST", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Create a geometric point column.
     *
     * @param databaseId Database ID.
     * @param tableId Table ID. You can create a new table using the TablesDB service [server integration](https://appwrite.io/docs/references/cloud/server-dart/tablesDB#createTable).
     * @param key Column Key.
     * @param required Is column required?
     * @param default_ Default value for column when not provided, array of two numbers [longitude, latitude], representing a single coordinate. Cannot be set when column is required.
     * @return appwrite::Result<appwrite::models::ColumnPoint>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::ColumnPoint>> createPointColumnAsync(        std::string_view databaseId,         std::string_view tableId,         std::string_view key,         bool required,         std::optional<std::vector<std::string>> default_ = std::nullopt    ) {
                std::string path_ = std::format("/tablesdb/{}/tables/{}/columns/point", databaseId, tableId);
        
        nlohmann::json params = nlohmann::json::object();
        params["key"] = std::string(key);
        params["required"] = required;
        if (default_.has_value()) {
            params["default"] = *default_;
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::ColumnPoint>("POST", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Update a point column. Changing the `default` value will not update already
     * existing rows.
     *
     * @param databaseId Database ID.
     * @param tableId Table ID. You can create a new table using the TablesDB service [server integration](https://appwrite.io/docs/references/cloud/server-dart/tablesDB#createTable).
     * @param key Column Key.
     * @param required Is column required?
     * @param default_ Default value for column when not provided, array of two numbers [longitude, latitude], representing a single coordinate. Cannot be set when column is required.
     * @param newKey New Column Key.
     * @return appwrite::Result<appwrite::models::ColumnPoint>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::ColumnPoint> updatePointColumn(        std::string_view databaseId,         std::string_view tableId,         std::string_view key,         bool required,         std::optional<std::vector<std::string>> default_ = std::nullopt,         std::optional<std::string_view> newKey = std::nullopt    ) {
                std::string path_ = std::format("/tablesdb/{}/tables/{}/columns/point/{}", databaseId, tableId, key);
        
        nlohmann::json params = nlohmann::json::object();
        params["required"] = required;
        if (default_.has_value()) {
            params["default"] = *default_;
        }
        if (newKey.has_value()) {
            params["newKey"] = std::string(newKey.value());
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::ColumnPoint>("PATCH", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Update a point column. Changing the `default` value will not update already
     * existing rows.
     *
     * @param databaseId Database ID.
     * @param tableId Table ID. You can create a new table using the TablesDB service [server integration](https://appwrite.io/docs/references/cloud/server-dart/tablesDB#createTable).
     * @param key Column Key.
     * @param required Is column required?
     * @param default_ Default value for column when not provided, array of two numbers [longitude, latitude], representing a single coordinate. Cannot be set when column is required.
     * @param newKey New Column Key.
     * @return appwrite::Result<appwrite::models::ColumnPoint>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::ColumnPoint>> updatePointColumnAsync(        std::string_view databaseId,         std::string_view tableId,         std::string_view key,         bool required,         std::optional<std::vector<std::string>> default_ = std::nullopt,         std::optional<std::string_view> newKey = std::nullopt    ) {
                std::string path_ = std::format("/tablesdb/{}/tables/{}/columns/point/{}", databaseId, tableId, key);
        
        nlohmann::json params = nlohmann::json::object();
        params["required"] = required;
        if (default_.has_value()) {
            params["default"] = *default_;
        }
        if (newKey.has_value()) {
            params["newKey"] = std::string(newKey.value());
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::ColumnPoint>("PATCH", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Create a geometric polygon column.
     *
     * @param databaseId Database ID.
     * @param tableId Table ID. You can create a new table using the TablesDB service [server integration](https://appwrite.io/docs/references/cloud/server-dart/tablesDB#createTable).
     * @param key Column Key.
     * @param required Is column required?
     * @param default_ Default value for column when not provided, three-dimensional array where the outer array holds one or more linear rings, [[[longitude, latitude], …], …], the first ring is the exterior boundary, any additional rings are interior holes, and each ring must start and end with the same coordinate pair. Cannot be set when column is required.
     * @return appwrite::Result<appwrite::models::ColumnPolygon>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::ColumnPolygon> createPolygonColumn(        std::string_view databaseId,         std::string_view tableId,         std::string_view key,         bool required,         std::optional<std::vector<std::string>> default_ = std::nullopt    ) {
                std::string path_ = std::format("/tablesdb/{}/tables/{}/columns/polygon", databaseId, tableId);
        
        nlohmann::json params = nlohmann::json::object();
        params["key"] = std::string(key);
        params["required"] = required;
        if (default_.has_value()) {
            params["default"] = *default_;
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::ColumnPolygon>("POST", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Create a geometric polygon column.
     *
     * @param databaseId Database ID.
     * @param tableId Table ID. You can create a new table using the TablesDB service [server integration](https://appwrite.io/docs/references/cloud/server-dart/tablesDB#createTable).
     * @param key Column Key.
     * @param required Is column required?
     * @param default_ Default value for column when not provided, three-dimensional array where the outer array holds one or more linear rings, [[[longitude, latitude], …], …], the first ring is the exterior boundary, any additional rings are interior holes, and each ring must start and end with the same coordinate pair. Cannot be set when column is required.
     * @return appwrite::Result<appwrite::models::ColumnPolygon>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::ColumnPolygon>> createPolygonColumnAsync(        std::string_view databaseId,         std::string_view tableId,         std::string_view key,         bool required,         std::optional<std::vector<std::string>> default_ = std::nullopt    ) {
                std::string path_ = std::format("/tablesdb/{}/tables/{}/columns/polygon", databaseId, tableId);
        
        nlohmann::json params = nlohmann::json::object();
        params["key"] = std::string(key);
        params["required"] = required;
        if (default_.has_value()) {
            params["default"] = *default_;
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::ColumnPolygon>("POST", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Update a polygon column. Changing the `default` value will not update
     * already existing rows.
     *
     * @param databaseId Database ID.
     * @param tableId Table ID. You can create a new table using the TablesDB service [server integration](https://appwrite.io/docs/references/cloud/server-dart/tablesDB#createTable).
     * @param key Column Key.
     * @param required Is column required?
     * @param default_ Default value for column when not provided, three-dimensional array where the outer array holds one or more linear rings, [[[longitude, latitude], …], …], the first ring is the exterior boundary, any additional rings are interior holes, and each ring must start and end with the same coordinate pair. Cannot be set when column is required.
     * @param newKey New Column Key.
     * @return appwrite::Result<appwrite::models::ColumnPolygon>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::ColumnPolygon> updatePolygonColumn(        std::string_view databaseId,         std::string_view tableId,         std::string_view key,         bool required,         std::optional<std::vector<std::string>> default_ = std::nullopt,         std::optional<std::string_view> newKey = std::nullopt    ) {
                std::string path_ = std::format("/tablesdb/{}/tables/{}/columns/polygon/{}", databaseId, tableId, key);
        
        nlohmann::json params = nlohmann::json::object();
        params["required"] = required;
        if (default_.has_value()) {
            params["default"] = *default_;
        }
        if (newKey.has_value()) {
            params["newKey"] = std::string(newKey.value());
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::ColumnPolygon>("PATCH", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Update a polygon column. Changing the `default` value will not update
     * already existing rows.
     *
     * @param databaseId Database ID.
     * @param tableId Table ID. You can create a new table using the TablesDB service [server integration](https://appwrite.io/docs/references/cloud/server-dart/tablesDB#createTable).
     * @param key Column Key.
     * @param required Is column required?
     * @param default_ Default value for column when not provided, three-dimensional array where the outer array holds one or more linear rings, [[[longitude, latitude], …], …], the first ring is the exterior boundary, any additional rings are interior holes, and each ring must start and end with the same coordinate pair. Cannot be set when column is required.
     * @param newKey New Column Key.
     * @return appwrite::Result<appwrite::models::ColumnPolygon>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::ColumnPolygon>> updatePolygonColumnAsync(        std::string_view databaseId,         std::string_view tableId,         std::string_view key,         bool required,         std::optional<std::vector<std::string>> default_ = std::nullopt,         std::optional<std::string_view> newKey = std::nullopt    ) {
                std::string path_ = std::format("/tablesdb/{}/tables/{}/columns/polygon/{}", databaseId, tableId, key);
        
        nlohmann::json params = nlohmann::json::object();
        params["required"] = required;
        if (default_.has_value()) {
            params["default"] = *default_;
        }
        if (newKey.has_value()) {
            params["newKey"] = std::string(newKey.value());
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::ColumnPolygon>("PATCH", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Create relationship column. [Learn more about relationship
     * columns](https://appwrite.io/docs/databases-relationships#relationship-columns).
     *
     * @param databaseId Database ID.
     * @param tableId Table ID.
     * @param relatedTableId Related Table ID.
     * @param type Relation type
     * @param twoWay Is Two Way?
     * @param key Column Key.
     * @param twoWayKey Two Way Column Key.
     * @param onDelete Constraints option
     * @return appwrite::Result<appwrite::models::ColumnRelationship>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::ColumnRelationship> createRelationshipColumn(        std::string_view databaseId,         std::string_view tableId,         std::string_view relatedTableId,         appwrite::enums::RelationshipType type,         std::optional<bool> twoWay = false,         std::optional<std::string_view> key = std::nullopt,         std::optional<std::string_view> twoWayKey = std::nullopt,         std::optional<appwrite::enums::RelationMutate> onDelete = appwrite::enums::RelationMutate::RESTRICT    ) {
                std::string path_ = std::format("/tablesdb/{}/tables/{}/columns/relationship", databaseId, tableId);
        
        nlohmann::json params = nlohmann::json::object();
        params["relatedTableId"] = std::string(relatedTableId);
        params["type"] = type;
        if (twoWay.has_value()) {
            params["twoWay"] = *twoWay;
        }
        if (key.has_value()) {
            params["key"] = std::string(key.value());
        }
        if (twoWayKey.has_value()) {
            params["twoWayKey"] = std::string(twoWayKey.value());
        }
        if (onDelete.has_value()) {
            params["onDelete"] = *onDelete;
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::ColumnRelationship>("POST", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Create relationship column. [Learn more about relationship
     * columns](https://appwrite.io/docs/databases-relationships#relationship-columns).
     *
     * @param databaseId Database ID.
     * @param tableId Table ID.
     * @param relatedTableId Related Table ID.
     * @param type Relation type
     * @param twoWay Is Two Way?
     * @param key Column Key.
     * @param twoWayKey Two Way Column Key.
     * @param onDelete Constraints option
     * @return appwrite::Result<appwrite::models::ColumnRelationship>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::ColumnRelationship>> createRelationshipColumnAsync(        std::string_view databaseId,         std::string_view tableId,         std::string_view relatedTableId,         appwrite::enums::RelationshipType type,         std::optional<bool> twoWay = false,         std::optional<std::string_view> key = std::nullopt,         std::optional<std::string_view> twoWayKey = std::nullopt,         std::optional<appwrite::enums::RelationMutate> onDelete = appwrite::enums::RelationMutate::RESTRICT    ) {
                std::string path_ = std::format("/tablesdb/{}/tables/{}/columns/relationship", databaseId, tableId);
        
        nlohmann::json params = nlohmann::json::object();
        params["relatedTableId"] = std::string(relatedTableId);
        params["type"] = type;
        if (twoWay.has_value()) {
            params["twoWay"] = *twoWay;
        }
        if (key.has_value()) {
            params["key"] = std::string(key.value());
        }
        if (twoWayKey.has_value()) {
            params["twoWayKey"] = std::string(twoWayKey.value());
        }
        if (onDelete.has_value()) {
            params["onDelete"] = *onDelete;
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::ColumnRelationship>("POST", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Create a string column.
     *
     * @param databaseId Database ID.
     * @param tableId Table ID. You can create a new table using the Database service [server integration](https://appwrite.io/docs/references/cloud/server-dart/tablesDB#createTable).
     * @param key Column Key.
     * @param size Column size for text columns, in number of characters.
     * @param required Is column required?
     * @param default_ Default value for column when not provided. Cannot be set when column is required.
     * @param array Is column an array?
     * @param encrypt Toggle encryption for the column. Encryption enhances security by not storing any plain text values in the database. However, encrypted columns cannot be queried.
     * @return appwrite::Result<appwrite::models::ColumnString>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::ColumnString> createStringColumn(        std::string_view databaseId,         std::string_view tableId,         std::string_view key,         int64_t size,         bool required,         std::optional<std::string_view> default_ = std::nullopt,         std::optional<bool> array = false,         std::optional<bool> encrypt = false    ) {
                std::string path_ = std::format("/tablesdb/{}/tables/{}/columns/string", databaseId, tableId);
        
        nlohmann::json params = nlohmann::json::object();
        params["key"] = std::string(key);
        params["size"] = size;
        params["required"] = required;
        if (default_.has_value()) {
            params["default"] = std::string(default_.value());
        }
        if (array.has_value()) {
            params["array"] = *array;
        }
        if (encrypt.has_value()) {
            params["encrypt"] = *encrypt;
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::ColumnString>("POST", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Create a string column.
     *
     * @param databaseId Database ID.
     * @param tableId Table ID. You can create a new table using the Database service [server integration](https://appwrite.io/docs/references/cloud/server-dart/tablesDB#createTable).
     * @param key Column Key.
     * @param size Column size for text columns, in number of characters.
     * @param required Is column required?
     * @param default_ Default value for column when not provided. Cannot be set when column is required.
     * @param array Is column an array?
     * @param encrypt Toggle encryption for the column. Encryption enhances security by not storing any plain text values in the database. However, encrypted columns cannot be queried.
     * @return appwrite::Result<appwrite::models::ColumnString>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::ColumnString>> createStringColumnAsync(        std::string_view databaseId,         std::string_view tableId,         std::string_view key,         int64_t size,         bool required,         std::optional<std::string_view> default_ = std::nullopt,         std::optional<bool> array = false,         std::optional<bool> encrypt = false    ) {
                std::string path_ = std::format("/tablesdb/{}/tables/{}/columns/string", databaseId, tableId);
        
        nlohmann::json params = nlohmann::json::object();
        params["key"] = std::string(key);
        params["size"] = size;
        params["required"] = required;
        if (default_.has_value()) {
            params["default"] = std::string(default_.value());
        }
        if (array.has_value()) {
            params["array"] = *array;
        }
        if (encrypt.has_value()) {
            params["encrypt"] = *encrypt;
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::ColumnString>("POST", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Update a string column. Changing the `default` value will not update
     * already existing rows.
     *
     * @param databaseId Database ID.
     * @param tableId Table ID. You can create a new table using the Database service [server integration](https://appwrite.io/docs/references/cloud/server-dart/tablesDB#createTable).
     * @param key Column Key.
     * @param required Is column required?
     * @param default_ Default value for column when not provided. Cannot be set when column is required.
     * @param size Maximum size of the string column.
     * @param newKey New Column Key.
     * @return appwrite::Result<appwrite::models::ColumnString>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::ColumnString> updateStringColumn(        std::string_view databaseId,         std::string_view tableId,         std::string_view key,         bool required,         std::string_view default_,         std::optional<int64_t> size = std::nullopt,         std::optional<std::string_view> newKey = std::nullopt    ) {
                std::string path_ = std::format("/tablesdb/{}/tables/{}/columns/string/{}", databaseId, tableId, key);
        
        nlohmann::json params = nlohmann::json::object();
        params["required"] = required;
        params["default"] = std::string(default_);
        if (size.has_value()) {
            params["size"] = *size;
        }
        if (newKey.has_value()) {
            params["newKey"] = std::string(newKey.value());
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::ColumnString>("PATCH", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Update a string column. Changing the `default` value will not update
     * already existing rows.
     *
     * @param databaseId Database ID.
     * @param tableId Table ID. You can create a new table using the Database service [server integration](https://appwrite.io/docs/references/cloud/server-dart/tablesDB#createTable).
     * @param key Column Key.
     * @param required Is column required?
     * @param default_ Default value for column when not provided. Cannot be set when column is required.
     * @param size Maximum size of the string column.
     * @param newKey New Column Key.
     * @return appwrite::Result<appwrite::models::ColumnString>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::ColumnString>> updateStringColumnAsync(        std::string_view databaseId,         std::string_view tableId,         std::string_view key,         bool required,         std::string_view default_,         std::optional<int64_t> size = std::nullopt,         std::optional<std::string_view> newKey = std::nullopt    ) {
                std::string path_ = std::format("/tablesdb/{}/tables/{}/columns/string/{}", databaseId, tableId, key);
        
        nlohmann::json params = nlohmann::json::object();
        params["required"] = required;
        params["default"] = std::string(default_);
        if (size.has_value()) {
            params["size"] = *size;
        }
        if (newKey.has_value()) {
            params["newKey"] = std::string(newKey.value());
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::ColumnString>("PATCH", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Create a text column.
     *
     * @param databaseId Database ID.
     * @param tableId Table ID. You can create a new table using the Database service [server integration](https://appwrite.io/docs/references/cloud/server-dart/tablesDB#createTable).
     * @param key Column Key.
     * @param required Is column required?
     * @param default_ Default value for column when not provided. Cannot be set when column is required.
     * @param array Is column an array?
     * @param encrypt Toggle encryption for the column. Encryption enhances security by not storing any plain text values in the database. However, encrypted columns cannot be queried.
     * @return appwrite::Result<appwrite::models::ColumnText>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::ColumnText> createTextColumn(        std::string_view databaseId,         std::string_view tableId,         std::string_view key,         bool required,         std::optional<std::string_view> default_ = std::nullopt,         std::optional<bool> array = false,         std::optional<bool> encrypt = false    ) {
                std::string path_ = std::format("/tablesdb/{}/tables/{}/columns/text", databaseId, tableId);
        
        nlohmann::json params = nlohmann::json::object();
        params["key"] = std::string(key);
        params["required"] = required;
        if (default_.has_value()) {
            params["default"] = std::string(default_.value());
        }
        if (array.has_value()) {
            params["array"] = *array;
        }
        if (encrypt.has_value()) {
            params["encrypt"] = *encrypt;
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::ColumnText>("POST", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Create a text column.
     *
     * @param databaseId Database ID.
     * @param tableId Table ID. You can create a new table using the Database service [server integration](https://appwrite.io/docs/references/cloud/server-dart/tablesDB#createTable).
     * @param key Column Key.
     * @param required Is column required?
     * @param default_ Default value for column when not provided. Cannot be set when column is required.
     * @param array Is column an array?
     * @param encrypt Toggle encryption for the column. Encryption enhances security by not storing any plain text values in the database. However, encrypted columns cannot be queried.
     * @return appwrite::Result<appwrite::models::ColumnText>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::ColumnText>> createTextColumnAsync(        std::string_view databaseId,         std::string_view tableId,         std::string_view key,         bool required,         std::optional<std::string_view> default_ = std::nullopt,         std::optional<bool> array = false,         std::optional<bool> encrypt = false    ) {
                std::string path_ = std::format("/tablesdb/{}/tables/{}/columns/text", databaseId, tableId);
        
        nlohmann::json params = nlohmann::json::object();
        params["key"] = std::string(key);
        params["required"] = required;
        if (default_.has_value()) {
            params["default"] = std::string(default_.value());
        }
        if (array.has_value()) {
            params["array"] = *array;
        }
        if (encrypt.has_value()) {
            params["encrypt"] = *encrypt;
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::ColumnText>("POST", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Update a text column. Changing the `default` value will not update already
     * existing rows.
     *
     * @param databaseId Database ID.
     * @param tableId Table ID. You can create a new table using the Database service [server integration](https://appwrite.io/docs/references/cloud/server-dart/tablesDB#createTable).
     * @param key Column Key.
     * @param required Is column required?
     * @param default_ Default value for column when not provided. Cannot be set when column is required.
     * @param newKey New Column Key.
     * @return appwrite::Result<appwrite::models::ColumnText>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::ColumnText> updateTextColumn(        std::string_view databaseId,         std::string_view tableId,         std::string_view key,         bool required,         std::string_view default_,         std::optional<std::string_view> newKey = std::nullopt    ) {
                std::string path_ = std::format("/tablesdb/{}/tables/{}/columns/text/{}", databaseId, tableId, key);
        
        nlohmann::json params = nlohmann::json::object();
        params["required"] = required;
        params["default"] = std::string(default_);
        if (newKey.has_value()) {
            params["newKey"] = std::string(newKey.value());
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::ColumnText>("PATCH", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Update a text column. Changing the `default` value will not update already
     * existing rows.
     *
     * @param databaseId Database ID.
     * @param tableId Table ID. You can create a new table using the Database service [server integration](https://appwrite.io/docs/references/cloud/server-dart/tablesDB#createTable).
     * @param key Column Key.
     * @param required Is column required?
     * @param default_ Default value for column when not provided. Cannot be set when column is required.
     * @param newKey New Column Key.
     * @return appwrite::Result<appwrite::models::ColumnText>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::ColumnText>> updateTextColumnAsync(        std::string_view databaseId,         std::string_view tableId,         std::string_view key,         bool required,         std::string_view default_,         std::optional<std::string_view> newKey = std::nullopt    ) {
                std::string path_ = std::format("/tablesdb/{}/tables/{}/columns/text/{}", databaseId, tableId, key);
        
        nlohmann::json params = nlohmann::json::object();
        params["required"] = required;
        params["default"] = std::string(default_);
        if (newKey.has_value()) {
            params["newKey"] = std::string(newKey.value());
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::ColumnText>("PATCH", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Create a URL column.
     *
     * @param databaseId Database ID.
     * @param tableId Table ID.
     * @param key Column Key.
     * @param required Is column required?
     * @param default_ Default value for column when not provided. Cannot be set when column is required.
     * @param array Is column an array?
     * @return appwrite::Result<appwrite::models::ColumnUrl>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::ColumnUrl> createUrlColumn(        std::string_view databaseId,         std::string_view tableId,         std::string_view key,         bool required,         std::optional<std::string_view> default_ = std::nullopt,         std::optional<bool> array = false    ) {
                std::string path_ = std::format("/tablesdb/{}/tables/{}/columns/url", databaseId, tableId);
        
        nlohmann::json params = nlohmann::json::object();
        params["key"] = std::string(key);
        params["required"] = required;
        if (default_.has_value()) {
            params["default"] = std::string(default_.value());
        }
        if (array.has_value()) {
            params["array"] = *array;
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::ColumnUrl>("POST", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Create a URL column.
     *
     * @param databaseId Database ID.
     * @param tableId Table ID.
     * @param key Column Key.
     * @param required Is column required?
     * @param default_ Default value for column when not provided. Cannot be set when column is required.
     * @param array Is column an array?
     * @return appwrite::Result<appwrite::models::ColumnUrl>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::ColumnUrl>> createUrlColumnAsync(        std::string_view databaseId,         std::string_view tableId,         std::string_view key,         bool required,         std::optional<std::string_view> default_ = std::nullopt,         std::optional<bool> array = false    ) {
                std::string path_ = std::format("/tablesdb/{}/tables/{}/columns/url", databaseId, tableId);
        
        nlohmann::json params = nlohmann::json::object();
        params["key"] = std::string(key);
        params["required"] = required;
        if (default_.has_value()) {
            params["default"] = std::string(default_.value());
        }
        if (array.has_value()) {
            params["array"] = *array;
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::ColumnUrl>("POST", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Update an url column. Changing the `default` value will not update already
     * existing rows.
     *
     * @param databaseId Database ID.
     * @param tableId Table ID.
     * @param key Column Key.
     * @param required Is column required?
     * @param default_ Default value for column when not provided. Cannot be set when column is required.
     * @param newKey New Column Key.
     * @return appwrite::Result<appwrite::models::ColumnUrl>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::ColumnUrl> updateUrlColumn(        std::string_view databaseId,         std::string_view tableId,         std::string_view key,         bool required,         std::string_view default_,         std::optional<std::string_view> newKey = std::nullopt    ) {
                std::string path_ = std::format("/tablesdb/{}/tables/{}/columns/url/{}", databaseId, tableId, key);
        
        nlohmann::json params = nlohmann::json::object();
        params["required"] = required;
        params["default"] = std::string(default_);
        if (newKey.has_value()) {
            params["newKey"] = std::string(newKey.value());
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::ColumnUrl>("PATCH", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Update an url column. Changing the `default` value will not update already
     * existing rows.
     *
     * @param databaseId Database ID.
     * @param tableId Table ID.
     * @param key Column Key.
     * @param required Is column required?
     * @param default_ Default value for column when not provided. Cannot be set when column is required.
     * @param newKey New Column Key.
     * @return appwrite::Result<appwrite::models::ColumnUrl>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::ColumnUrl>> updateUrlColumnAsync(        std::string_view databaseId,         std::string_view tableId,         std::string_view key,         bool required,         std::string_view default_,         std::optional<std::string_view> newKey = std::nullopt    ) {
                std::string path_ = std::format("/tablesdb/{}/tables/{}/columns/url/{}", databaseId, tableId, key);
        
        nlohmann::json params = nlohmann::json::object();
        params["required"] = required;
        params["default"] = std::string(default_);
        if (newKey.has_value()) {
            params["newKey"] = std::string(newKey.value());
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::ColumnUrl>("PATCH", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Create a varchar column.
     *
     * @param databaseId Database ID.
     * @param tableId Table ID. You can create a new table using the Database service [server integration](https://appwrite.io/docs/references/cloud/server-dart/tablesDB#createTable).
     * @param key Column Key.
     * @param size Column size for varchar columns, in number of characters. Maximum size is 16381.
     * @param required Is column required?
     * @param default_ Default value for column when not provided. Cannot be set when column is required.
     * @param array Is column an array?
     * @param encrypt Toggle encryption for the column. Encryption enhances security by not storing any plain text values in the database. However, encrypted columns cannot be queried.
     * @return appwrite::Result<appwrite::models::ColumnVarchar>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::ColumnVarchar> createVarcharColumn(        std::string_view databaseId,         std::string_view tableId,         std::string_view key,         int64_t size,         bool required,         std::optional<std::string_view> default_ = std::nullopt,         std::optional<bool> array = false,         std::optional<bool> encrypt = false    ) {
                std::string path_ = std::format("/tablesdb/{}/tables/{}/columns/varchar", databaseId, tableId);
        
        nlohmann::json params = nlohmann::json::object();
        params["key"] = std::string(key);
        params["size"] = size;
        params["required"] = required;
        if (default_.has_value()) {
            params["default"] = std::string(default_.value());
        }
        if (array.has_value()) {
            params["array"] = *array;
        }
        if (encrypt.has_value()) {
            params["encrypt"] = *encrypt;
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::ColumnVarchar>("POST", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Create a varchar column.
     *
     * @param databaseId Database ID.
     * @param tableId Table ID. You can create a new table using the Database service [server integration](https://appwrite.io/docs/references/cloud/server-dart/tablesDB#createTable).
     * @param key Column Key.
     * @param size Column size for varchar columns, in number of characters. Maximum size is 16381.
     * @param required Is column required?
     * @param default_ Default value for column when not provided. Cannot be set when column is required.
     * @param array Is column an array?
     * @param encrypt Toggle encryption for the column. Encryption enhances security by not storing any plain text values in the database. However, encrypted columns cannot be queried.
     * @return appwrite::Result<appwrite::models::ColumnVarchar>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::ColumnVarchar>> createVarcharColumnAsync(        std::string_view databaseId,         std::string_view tableId,         std::string_view key,         int64_t size,         bool required,         std::optional<std::string_view> default_ = std::nullopt,         std::optional<bool> array = false,         std::optional<bool> encrypt = false    ) {
                std::string path_ = std::format("/tablesdb/{}/tables/{}/columns/varchar", databaseId, tableId);
        
        nlohmann::json params = nlohmann::json::object();
        params["key"] = std::string(key);
        params["size"] = size;
        params["required"] = required;
        if (default_.has_value()) {
            params["default"] = std::string(default_.value());
        }
        if (array.has_value()) {
            params["array"] = *array;
        }
        if (encrypt.has_value()) {
            params["encrypt"] = *encrypt;
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::ColumnVarchar>("POST", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Update a varchar column. Changing the `default` value will not update
     * already existing rows.
     *
     * @param databaseId Database ID.
     * @param tableId Table ID. You can create a new table using the Database service [server integration](https://appwrite.io/docs/references/cloud/server-dart/tablesDB#createTable).
     * @param key Column Key.
     * @param required Is column required?
     * @param default_ Default value for column when not provided. Cannot be set when column is required.
     * @param size Maximum size of the varchar column.
     * @param newKey New Column Key.
     * @return appwrite::Result<appwrite::models::ColumnVarchar>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::ColumnVarchar> updateVarcharColumn(        std::string_view databaseId,         std::string_view tableId,         std::string_view key,         bool required,         std::string_view default_,         std::optional<int64_t> size = std::nullopt,         std::optional<std::string_view> newKey = std::nullopt    ) {
                std::string path_ = std::format("/tablesdb/{}/tables/{}/columns/varchar/{}", databaseId, tableId, key);
        
        nlohmann::json params = nlohmann::json::object();
        params["required"] = required;
        params["default"] = std::string(default_);
        if (size.has_value()) {
            params["size"] = *size;
        }
        if (newKey.has_value()) {
            params["newKey"] = std::string(newKey.value());
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::ColumnVarchar>("PATCH", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Update a varchar column. Changing the `default` value will not update
     * already existing rows.
     *
     * @param databaseId Database ID.
     * @param tableId Table ID. You can create a new table using the Database service [server integration](https://appwrite.io/docs/references/cloud/server-dart/tablesDB#createTable).
     * @param key Column Key.
     * @param required Is column required?
     * @param default_ Default value for column when not provided. Cannot be set when column is required.
     * @param size Maximum size of the varchar column.
     * @param newKey New Column Key.
     * @return appwrite::Result<appwrite::models::ColumnVarchar>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::ColumnVarchar>> updateVarcharColumnAsync(        std::string_view databaseId,         std::string_view tableId,         std::string_view key,         bool required,         std::string_view default_,         std::optional<int64_t> size = std::nullopt,         std::optional<std::string_view> newKey = std::nullopt    ) {
                std::string path_ = std::format("/tablesdb/{}/tables/{}/columns/varchar/{}", databaseId, tableId, key);
        
        nlohmann::json params = nlohmann::json::object();
        params["required"] = required;
        params["default"] = std::string(default_);
        if (size.has_value()) {
            params["size"] = *size;
        }
        if (newKey.has_value()) {
            params["newKey"] = std::string(newKey.value());
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::ColumnVarchar>("PATCH", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Get column by ID.
     *
     * @param databaseId Database ID.
     * @param tableId Table ID.
     * @param key Column Key.
     * @return appwrite::Result<nlohmann::json>
     */
    [[nodiscard]] appwrite::Result<nlohmann::json> getColumn(        std::string_view databaseId,         std::string_view tableId,         std::string_view key    ) {
                std::string path_ = std::format("/tablesdb/{}/tables/{}/columns/{}", databaseId, tableId, key);
        
        nlohmann::json params = nlohmann::json::object();
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<nlohmann::json>("GET", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Get column by ID.
     *
     * @param databaseId Database ID.
     * @param tableId Table ID.
     * @param key Column Key.
     * @return appwrite::Result<nlohmann::json>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<nlohmann::json>> getColumnAsync(        std::string_view databaseId,         std::string_view tableId,         std::string_view key    ) {
                std::string path_ = std::format("/tablesdb/{}/tables/{}/columns/{}", databaseId, tableId, key);
        
        nlohmann::json params = nlohmann::json::object();
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<nlohmann::json>("GET", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Deletes a column.
     *
     * @param databaseId Database ID.
     * @param tableId Table ID.
     * @param key Column Key.
     * @return appwrite::Result<void>
     */
    [[nodiscard]] appwrite::Result<void> deleteColumn(        std::string_view databaseId,         std::string_view tableId,         std::string_view key    ) {
                std::string path_ = std::format("/tablesdb/{}/tables/{}/columns/{}", databaseId, tableId, key);
        
        nlohmann::json params = nlohmann::json::object();
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callVoid("DELETE", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Deletes a column.
     *
     * @param databaseId Database ID.
     * @param tableId Table ID.
     * @param key Column Key.
     * @return appwrite::Result<void>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<void>> deleteColumnAsync(        std::string_view databaseId,         std::string_view tableId,         std::string_view key    ) {
                std::string path_ = std::format("/tablesdb/{}/tables/{}/columns/{}", databaseId, tableId, key);
        
        nlohmann::json params = nlohmann::json::object();
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callVoidAsync("DELETE", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Update relationship column. [Learn more about relationship
     * columns](https://appwrite.io/docs/databases-relationships#relationship-columns).
     *
     * @param databaseId Database ID.
     * @param tableId Table ID.
     * @param key Column Key.
     * @param onDelete Constraints option
     * @param newKey New Column Key.
     * @return appwrite::Result<appwrite::models::ColumnRelationship>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::ColumnRelationship> updateRelationshipColumn(        std::string_view databaseId,         std::string_view tableId,         std::string_view key,         std::optional<appwrite::enums::RelationMutate> onDelete = std::nullopt,         std::optional<std::string_view> newKey = std::nullopt    ) {
                std::string path_ = std::format("/tablesdb/{}/tables/{}/columns/{}/relationship", databaseId, tableId, key);
        
        nlohmann::json params = nlohmann::json::object();
        if (onDelete.has_value()) {
            params["onDelete"] = *onDelete;
        }
        if (newKey.has_value()) {
            params["newKey"] = std::string(newKey.value());
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::ColumnRelationship>("PATCH", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Update relationship column. [Learn more about relationship
     * columns](https://appwrite.io/docs/databases-relationships#relationship-columns).
     *
     * @param databaseId Database ID.
     * @param tableId Table ID.
     * @param key Column Key.
     * @param onDelete Constraints option
     * @param newKey New Column Key.
     * @return appwrite::Result<appwrite::models::ColumnRelationship>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::ColumnRelationship>> updateRelationshipColumnAsync(        std::string_view databaseId,         std::string_view tableId,         std::string_view key,         std::optional<appwrite::enums::RelationMutate> onDelete = std::nullopt,         std::optional<std::string_view> newKey = std::nullopt    ) {
                std::string path_ = std::format("/tablesdb/{}/tables/{}/columns/{}/relationship", databaseId, tableId, key);
        
        nlohmann::json params = nlohmann::json::object();
        if (onDelete.has_value()) {
            params["onDelete"] = *onDelete;
        }
        if (newKey.has_value()) {
            params["newKey"] = std::string(newKey.value());
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::ColumnRelationship>("PATCH", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * List indexes on the table.
     *
     * @param databaseId Database ID.
     * @param tableId Table ID. You can create a new table using the Database service [server integration](https://appwrite.io/docs/references/cloud/server-dart/tablesDB#createTable).
     * @param queries Array of query strings generated using the Query class provided by the SDK. [Learn more about queries](https://appwrite.io/docs/queries). Maximum of 100 queries are allowed, each 4096 characters long. You may filter on the following columns: key, type, status, attributes, error
     * @param total When set to false, the total count returned will be 0 and will not be calculated.
     * @return appwrite::Result<appwrite::models::ColumnIndexList>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::ColumnIndexList> listIndexes(        std::string_view databaseId,         std::string_view tableId,         std::optional<std::vector<std::string>> queries = std::vector<std::string>{},         std::optional<bool> total = true    ) {
                std::string path_ = std::format("/tablesdb/{}/tables/{}/indexes", databaseId, tableId);
        
        nlohmann::json params = nlohmann::json::object();
        if (queries.has_value()) {
            params["queries"] = *queries;
        }
        if (total.has_value()) {
            params["total"] = *total;
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::ColumnIndexList>("GET", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * List indexes on the table.
     *
     * @param databaseId Database ID.
     * @param tableId Table ID. You can create a new table using the Database service [server integration](https://appwrite.io/docs/references/cloud/server-dart/tablesDB#createTable).
     * @param queries Array of query strings generated using the Query class provided by the SDK. [Learn more about queries](https://appwrite.io/docs/queries). Maximum of 100 queries are allowed, each 4096 characters long. You may filter on the following columns: key, type, status, attributes, error
     * @param total When set to false, the total count returned will be 0 and will not be calculated.
     * @return appwrite::Result<appwrite::models::ColumnIndexList>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::ColumnIndexList>> listIndexesAsync(        std::string_view databaseId,         std::string_view tableId,         std::optional<std::vector<std::string>> queries = std::vector<std::string>{},         std::optional<bool> total = true    ) {
                std::string path_ = std::format("/tablesdb/{}/tables/{}/indexes", databaseId, tableId);
        
        nlohmann::json params = nlohmann::json::object();
        if (queries.has_value()) {
            params["queries"] = *queries;
        }
        if (total.has_value()) {
            params["total"] = *total;
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::ColumnIndexList>("GET", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Creates an index on the columns listed. Your index should include all the
     * columns you will query in a single request.
Type can be `key`, `fulltext`,
     * or `unique`.
     *
     * @param databaseId Database ID.
     * @param tableId Table ID. You can create a new table using the Database service [server integration](https://appwrite.io/docs/references/cloud/server-dart/tablesDB#createTable).
     * @param key Index Key.
     * @param type Index type.
     * @param columns Array of columns to index. Maximum of 100 columns are allowed, each 32 characters long.
     * @param orders Array of index orders. Maximum of 100 orders are allowed.
     * @param lengths Length of index. Maximum of 100
     * @return appwrite::Result<appwrite::models::ColumnIndex>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::ColumnIndex> createIndex(        std::string_view databaseId,         std::string_view tableId,         std::string_view key,         appwrite::enums::TablesDBIndexType type,         std::vector<std::string> columns,         std::optional<std::vector<appwrite::enums::OrderBy>> orders = {},         std::optional<std::vector<int64_t>> lengths = std::vector<int64_t>{}    ) {
                std::string path_ = std::format("/tablesdb/{}/tables/{}/indexes", databaseId, tableId);
        
        nlohmann::json params = nlohmann::json::object();
        params["key"] = std::string(key);
        params["type"] = type;
        params["columns"] = columns;
        if (orders.has_value()) {
            params["orders"] = *orders;
        }
        if (lengths.has_value()) {
            params["lengths"] = *lengths;
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::ColumnIndex>("POST", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Creates an index on the columns listed. Your index should include all the
     * columns you will query in a single request.
Type can be `key`, `fulltext`,
     * or `unique`.
     *
     * @param databaseId Database ID.
     * @param tableId Table ID. You can create a new table using the Database service [server integration](https://appwrite.io/docs/references/cloud/server-dart/tablesDB#createTable).
     * @param key Index Key.
     * @param type Index type.
     * @param columns Array of columns to index. Maximum of 100 columns are allowed, each 32 characters long.
     * @param orders Array of index orders. Maximum of 100 orders are allowed.
     * @param lengths Length of index. Maximum of 100
     * @return appwrite::Result<appwrite::models::ColumnIndex>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::ColumnIndex>> createIndexAsync(        std::string_view databaseId,         std::string_view tableId,         std::string_view key,         appwrite::enums::TablesDBIndexType type,         std::vector<std::string> columns,         std::optional<std::vector<appwrite::enums::OrderBy>> orders = {},         std::optional<std::vector<int64_t>> lengths = std::vector<int64_t>{}    ) {
                std::string path_ = std::format("/tablesdb/{}/tables/{}/indexes", databaseId, tableId);
        
        nlohmann::json params = nlohmann::json::object();
        params["key"] = std::string(key);
        params["type"] = type;
        params["columns"] = columns;
        if (orders.has_value()) {
            params["orders"] = *orders;
        }
        if (lengths.has_value()) {
            params["lengths"] = *lengths;
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::ColumnIndex>("POST", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Get index by ID.
     *
     * @param databaseId Database ID.
     * @param tableId Table ID. You can create a new table using the Database service [server integration](https://appwrite.io/docs/references/cloud/server-dart/tablesDB#createTable).
     * @param key Index Key.
     * @return appwrite::Result<appwrite::models::ColumnIndex>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::ColumnIndex> getIndex(        std::string_view databaseId,         std::string_view tableId,         std::string_view key    ) {
                std::string path_ = std::format("/tablesdb/{}/tables/{}/indexes/{}", databaseId, tableId, key);
        
        nlohmann::json params = nlohmann::json::object();
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::ColumnIndex>("GET", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Get index by ID.
     *
     * @param databaseId Database ID.
     * @param tableId Table ID. You can create a new table using the Database service [server integration](https://appwrite.io/docs/references/cloud/server-dart/tablesDB#createTable).
     * @param key Index Key.
     * @return appwrite::Result<appwrite::models::ColumnIndex>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::ColumnIndex>> getIndexAsync(        std::string_view databaseId,         std::string_view tableId,         std::string_view key    ) {
                std::string path_ = std::format("/tablesdb/{}/tables/{}/indexes/{}", databaseId, tableId, key);
        
        nlohmann::json params = nlohmann::json::object();
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::ColumnIndex>("GET", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Delete an index.
     *
     * @param databaseId Database ID.
     * @param tableId Table ID. You can create a new table using the TablesDB service [server integration](https://appwrite.io/docs/references/cloud/server-dart/tablesDB#createTable).
     * @param key Index Key.
     * @return appwrite::Result<void>
     */
    [[nodiscard]] appwrite::Result<void> deleteIndex(        std::string_view databaseId,         std::string_view tableId,         std::string_view key    ) {
                std::string path_ = std::format("/tablesdb/{}/tables/{}/indexes/{}", databaseId, tableId, key);
        
        nlohmann::json params = nlohmann::json::object();
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callVoid("DELETE", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Delete an index.
     *
     * @param databaseId Database ID.
     * @param tableId Table ID. You can create a new table using the TablesDB service [server integration](https://appwrite.io/docs/references/cloud/server-dart/tablesDB#createTable).
     * @param key Index Key.
     * @return appwrite::Result<void>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<void>> deleteIndexAsync(        std::string_view databaseId,         std::string_view tableId,         std::string_view key    ) {
                std::string path_ = std::format("/tablesdb/{}/tables/{}/indexes/{}", databaseId, tableId, key);
        
        nlohmann::json params = nlohmann::json::object();
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callVoidAsync("DELETE", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Get a list of all the user's rows in a given table. You can use the query
     * params to filter your results.
     *
     * @param databaseId Database ID.
     * @param tableId Table ID. You can create a new table using the TablesDB service [server integration](https://appwrite.io/docs/products/databases/tables#create-table).
     * @param queries Array of query strings generated using the Query class provided by the SDK. [Learn more about queries](https://appwrite.io/docs/queries). Maximum of 100 queries are allowed, each 4096 characters long.
     * @param transactionId Transaction ID to read uncommitted changes within the transaction.
     * @param total When set to false, the total count returned will be 0 and will not be calculated.
     * @param ttl TTL (seconds) for caching list responses. Responses are stored in an in-memory key-value cache, keyed per project, table, schema version (columns and indexes), caller authorization roles, and the exact query — so users with different permissions never share cached entries. Schema changes invalidate cached entries automatically; row writes do not, so choose a TTL you are comfortable serving as stale data. Set to 0 to disable caching. Must be between 0 and 86400 (24 hours).
     * @return appwrite::Result<appwrite::models::RowList>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::RowList> listRows(        std::string_view databaseId,         std::string_view tableId,         std::optional<std::vector<std::string>> queries = std::vector<std::string>{},         std::optional<std::string_view> transactionId = std::nullopt,         std::optional<bool> total = true,         std::optional<int64_t> ttl = 0    ) {
                std::string path_ = std::format("/tablesdb/{}/tables/{}/rows", databaseId, tableId);
        
        nlohmann::json params = nlohmann::json::object();
        if (queries.has_value()) {
            params["queries"] = *queries;
        }
        if (transactionId.has_value()) {
            params["transactionId"] = std::string(transactionId.value());
        }
        if (total.has_value()) {
            params["total"] = *total;
        }
        if (ttl.has_value()) {
            params["ttl"] = *ttl;
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::RowList>("GET", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Get a list of all the user's rows in a given table. You can use the query
     * params to filter your results.
     *
     * @param databaseId Database ID.
     * @param tableId Table ID. You can create a new table using the TablesDB service [server integration](https://appwrite.io/docs/products/databases/tables#create-table).
     * @param queries Array of query strings generated using the Query class provided by the SDK. [Learn more about queries](https://appwrite.io/docs/queries). Maximum of 100 queries are allowed, each 4096 characters long.
     * @param transactionId Transaction ID to read uncommitted changes within the transaction.
     * @param total When set to false, the total count returned will be 0 and will not be calculated.
     * @param ttl TTL (seconds) for caching list responses. Responses are stored in an in-memory key-value cache, keyed per project, table, schema version (columns and indexes), caller authorization roles, and the exact query — so users with different permissions never share cached entries. Schema changes invalidate cached entries automatically; row writes do not, so choose a TTL you are comfortable serving as stale data. Set to 0 to disable caching. Must be between 0 and 86400 (24 hours).
     * @return appwrite::Result<appwrite::models::RowList>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::RowList>> listRowsAsync(        std::string_view databaseId,         std::string_view tableId,         std::optional<std::vector<std::string>> queries = std::vector<std::string>{},         std::optional<std::string_view> transactionId = std::nullopt,         std::optional<bool> total = true,         std::optional<int64_t> ttl = 0    ) {
                std::string path_ = std::format("/tablesdb/{}/tables/{}/rows", databaseId, tableId);
        
        nlohmann::json params = nlohmann::json::object();
        if (queries.has_value()) {
            params["queries"] = *queries;
        }
        if (transactionId.has_value()) {
            params["transactionId"] = std::string(transactionId.value());
        }
        if (total.has_value()) {
            params["total"] = *total;
        }
        if (ttl.has_value()) {
            params["ttl"] = *ttl;
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::RowList>("GET", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Create a new Row. Before using this route, you should create a new table
     * resource using either a [server
     * integration](https://appwrite.io/docs/references/cloud/server-dart/tablesDB#createTable)
     * API or directly from your database console.
     *
     * @param databaseId Database ID.
     * @param tableId Table ID. You can create a new table using the Database service [server integration](https://appwrite.io/docs/references/cloud/server-dart/tablesDB#createTable). Make sure to define columns before creating rows.
     * @param rowId Row ID. Choose a custom ID or generate a random ID with `ID.unique()`. Valid chars are a-z, A-Z, 0-9, period, hyphen, and underscore. Can't start with a special char. Max length is 36 chars.
     * @param data Row data as JSON object.
     * @param permissions An array of permissions strings. By default, only the current user is granted all permissions. [Learn more about permissions](https://appwrite.io/docs/permissions).
     * @param transactionId Transaction ID for staging the operation.
     * @return appwrite::Result<appwrite::models::Row>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::Row> createRow(        std::string_view databaseId,         std::string_view tableId,         std::string_view rowId = "",         const nlohmann::json& data = nlohmann::json::object(),         std::optional<std::vector<std::string>> permissions = std::nullopt,         std::optional<std::string_view> transactionId = std::nullopt    ) {
                std::string path_ = std::format("/tablesdb/{}/tables/{}/rows", databaseId, tableId);
        
        nlohmann::json params = nlohmann::json::object();
        params["rowId"] = std::string(rowId);
        params["data"] = data;
        if (permissions.has_value()) {
            params["permissions"] = *permissions;
        }
        if (transactionId.has_value()) {
            params["transactionId"] = std::string(transactionId.value());
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::Row>("POST", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Create a new Row. Before using this route, you should create a new table
     * resource using either a [server
     * integration](https://appwrite.io/docs/references/cloud/server-dart/tablesDB#createTable)
     * API or directly from your database console.
     *
     * @param databaseId Database ID.
     * @param tableId Table ID. You can create a new table using the Database service [server integration](https://appwrite.io/docs/references/cloud/server-dart/tablesDB#createTable). Make sure to define columns before creating rows.
     * @param rowId Row ID. Choose a custom ID or generate a random ID with `ID.unique()`. Valid chars are a-z, A-Z, 0-9, period, hyphen, and underscore. Can't start with a special char. Max length is 36 chars.
     * @param data Row data as JSON object.
     * @param permissions An array of permissions strings. By default, only the current user is granted all permissions. [Learn more about permissions](https://appwrite.io/docs/permissions).
     * @param transactionId Transaction ID for staging the operation.
     * @return appwrite::Result<appwrite::models::Row>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::Row>> createRowAsync(        std::string_view databaseId,         std::string_view tableId,         std::string_view rowId = "",         const nlohmann::json& data = nlohmann::json::object(),         std::optional<std::vector<std::string>> permissions = std::nullopt,         std::optional<std::string_view> transactionId = std::nullopt    ) {
                std::string path_ = std::format("/tablesdb/{}/tables/{}/rows", databaseId, tableId);
        
        nlohmann::json params = nlohmann::json::object();
        params["rowId"] = std::string(rowId);
        params["data"] = data;
        if (permissions.has_value()) {
            params["permissions"] = *permissions;
        }
        if (transactionId.has_value()) {
            params["transactionId"] = std::string(transactionId.value());
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::Row>("POST", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Create new Rows. Before using this route, you should create a new table
     * resource using either a [server
     * integration](https://appwrite.io/docs/references/cloud/server-dart/tablesDB#createTable)
     * API or directly from your database console.
     *
     * @param databaseId Database ID.
     * @param tableId Table ID. You can create a new table using the Database service [server integration](https://appwrite.io/docs/references/cloud/server-dart/tablesDB#createTable). Make sure to define columns before creating rows.
     * @param rows Array of rows data as JSON objects.
     * @param transactionId Transaction ID for staging the operation.
     * @return appwrite::Result<appwrite::models::RowList>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::RowList> createRows(        std::string_view databaseId,         std::string_view tableId,         std::vector<nlohmann::json> rows = std::vector<nlohmann::json>{},         std::optional<std::string_view> transactionId = std::nullopt    ) {
                std::string path_ = std::format("/tablesdb/{}/tables/{}/rows", databaseId, tableId);
        
        nlohmann::json params = nlohmann::json::object();
        params["rows"] = rows;
        if (transactionId.has_value()) {
            params["transactionId"] = std::string(transactionId.value());
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::RowList>("POST", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Create new Rows. Before using this route, you should create a new table
     * resource using either a [server
     * integration](https://appwrite.io/docs/references/cloud/server-dart/tablesDB#createTable)
     * API or directly from your database console.
     *
     * @param databaseId Database ID.
     * @param tableId Table ID. You can create a new table using the Database service [server integration](https://appwrite.io/docs/references/cloud/server-dart/tablesDB#createTable). Make sure to define columns before creating rows.
     * @param rows Array of rows data as JSON objects.
     * @param transactionId Transaction ID for staging the operation.
     * @return appwrite::Result<appwrite::models::RowList>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::RowList>> createRowsAsync(        std::string_view databaseId,         std::string_view tableId,         std::vector<nlohmann::json> rows = std::vector<nlohmann::json>{},         std::optional<std::string_view> transactionId = std::nullopt    ) {
                std::string path_ = std::format("/tablesdb/{}/tables/{}/rows", databaseId, tableId);
        
        nlohmann::json params = nlohmann::json::object();
        params["rows"] = rows;
        if (transactionId.has_value()) {
            params["transactionId"] = std::string(transactionId.value());
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::RowList>("POST", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Create or update Rows. Before using this route, you should create a new
     * table resource using either a [server
     * integration](https://appwrite.io/docs/references/cloud/server-dart/tablesDB#createTable)
     * API or directly from your database console.
     *
     * @param databaseId Database ID.
     * @param tableId Table ID.
     * @param rows Array of row data as JSON objects. May contain partial rows.
     * @param transactionId Transaction ID for staging the operation.
     * @return appwrite::Result<appwrite::models::RowList>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::RowList> upsertRows(        std::string_view databaseId,         std::string_view tableId,         std::vector<nlohmann::json> rows,         std::optional<std::string_view> transactionId = std::nullopt    ) {
                std::string path_ = std::format("/tablesdb/{}/tables/{}/rows", databaseId, tableId);
        
        nlohmann::json params = nlohmann::json::object();
        params["rows"] = rows;
        if (transactionId.has_value()) {
            params["transactionId"] = std::string(transactionId.value());
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::RowList>("PUT", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Create or update Rows. Before using this route, you should create a new
     * table resource using either a [server
     * integration](https://appwrite.io/docs/references/cloud/server-dart/tablesDB#createTable)
     * API or directly from your database console.
     *
     * @param databaseId Database ID.
     * @param tableId Table ID.
     * @param rows Array of row data as JSON objects. May contain partial rows.
     * @param transactionId Transaction ID for staging the operation.
     * @return appwrite::Result<appwrite::models::RowList>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::RowList>> upsertRowsAsync(        std::string_view databaseId,         std::string_view tableId,         std::vector<nlohmann::json> rows,         std::optional<std::string_view> transactionId = std::nullopt    ) {
                std::string path_ = std::format("/tablesdb/{}/tables/{}/rows", databaseId, tableId);
        
        nlohmann::json params = nlohmann::json::object();
        params["rows"] = rows;
        if (transactionId.has_value()) {
            params["transactionId"] = std::string(transactionId.value());
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::RowList>("PUT", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Update all rows that match your queries, if no queries are submitted then
     * all rows are updated. You can pass only specific fields to be updated.
     *
     * @param databaseId Database ID.
     * @param tableId Table ID.
     * @param data Row data as JSON object. Include only column and value pairs to be updated.
     * @param queries Array of query strings generated using the Query class provided by the SDK. [Learn more about queries](https://appwrite.io/docs/queries). Maximum of 100 queries are allowed, each 4096 characters long.
     * @param transactionId Transaction ID for staging the operation.
     * @return appwrite::Result<appwrite::models::RowList>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::RowList> updateRows(        std::string_view databaseId,         std::string_view tableId,         std::optional<nlohmann::json> data = nlohmann::json::object(),         std::optional<std::vector<std::string>> queries = std::vector<std::string>{},         std::optional<std::string_view> transactionId = std::nullopt    ) {
                std::string path_ = std::format("/tablesdb/{}/tables/{}/rows", databaseId, tableId);
        
        nlohmann::json params = nlohmann::json::object();
        if (data.has_value()) {
            params["data"] = *data;
        }
        if (queries.has_value()) {
            params["queries"] = *queries;
        }
        if (transactionId.has_value()) {
            params["transactionId"] = std::string(transactionId.value());
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::RowList>("PATCH", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Update all rows that match your queries, if no queries are submitted then
     * all rows are updated. You can pass only specific fields to be updated.
     *
     * @param databaseId Database ID.
     * @param tableId Table ID.
     * @param data Row data as JSON object. Include only column and value pairs to be updated.
     * @param queries Array of query strings generated using the Query class provided by the SDK. [Learn more about queries](https://appwrite.io/docs/queries). Maximum of 100 queries are allowed, each 4096 characters long.
     * @param transactionId Transaction ID for staging the operation.
     * @return appwrite::Result<appwrite::models::RowList>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::RowList>> updateRowsAsync(        std::string_view databaseId,         std::string_view tableId,         std::optional<nlohmann::json> data = nlohmann::json::object(),         std::optional<std::vector<std::string>> queries = std::vector<std::string>{},         std::optional<std::string_view> transactionId = std::nullopt    ) {
                std::string path_ = std::format("/tablesdb/{}/tables/{}/rows", databaseId, tableId);
        
        nlohmann::json params = nlohmann::json::object();
        if (data.has_value()) {
            params["data"] = *data;
        }
        if (queries.has_value()) {
            params["queries"] = *queries;
        }
        if (transactionId.has_value()) {
            params["transactionId"] = std::string(transactionId.value());
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::RowList>("PATCH", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Bulk delete rows using queries, if no queries are passed then all rows are
     * deleted.
     *
     * @param databaseId Database ID.
     * @param tableId Table ID. You can create a new table using the Database service [server integration](https://appwrite.io/docs/references/cloud/server-dart/tablesDB#createTable).
     * @param queries Array of query strings generated using the Query class provided by the SDK. [Learn more about queries](https://appwrite.io/docs/queries). Maximum of 100 queries are allowed, each 4096 characters long.
     * @param transactionId Transaction ID for staging the operation.
     * @return appwrite::Result<appwrite::models::RowList>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::RowList> deleteRows(        std::string_view databaseId,         std::string_view tableId,         std::optional<std::vector<std::string>> queries = std::vector<std::string>{},         std::optional<std::string_view> transactionId = std::nullopt    ) {
                std::string path_ = std::format("/tablesdb/{}/tables/{}/rows", databaseId, tableId);
        
        nlohmann::json params = nlohmann::json::object();
        if (queries.has_value()) {
            params["queries"] = *queries;
        }
        if (transactionId.has_value()) {
            params["transactionId"] = std::string(transactionId.value());
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::RowList>("DELETE", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Bulk delete rows using queries, if no queries are passed then all rows are
     * deleted.
     *
     * @param databaseId Database ID.
     * @param tableId Table ID. You can create a new table using the Database service [server integration](https://appwrite.io/docs/references/cloud/server-dart/tablesDB#createTable).
     * @param queries Array of query strings generated using the Query class provided by the SDK. [Learn more about queries](https://appwrite.io/docs/queries). Maximum of 100 queries are allowed, each 4096 characters long.
     * @param transactionId Transaction ID for staging the operation.
     * @return appwrite::Result<appwrite::models::RowList>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::RowList>> deleteRowsAsync(        std::string_view databaseId,         std::string_view tableId,         std::optional<std::vector<std::string>> queries = std::vector<std::string>{},         std::optional<std::string_view> transactionId = std::nullopt    ) {
                std::string path_ = std::format("/tablesdb/{}/tables/{}/rows", databaseId, tableId);
        
        nlohmann::json params = nlohmann::json::object();
        if (queries.has_value()) {
            params["queries"] = *queries;
        }
        if (transactionId.has_value()) {
            params["transactionId"] = std::string(transactionId.value());
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::RowList>("DELETE", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Get a row by its unique ID. This endpoint response returns a JSON object
     * with the row data.
     *
     * @param databaseId Database ID.
     * @param tableId Table ID. You can create a new table using the Database service [server integration](https://appwrite.io/docs/references/cloud/server-dart/tablesDB#createTable).
     * @param rowId Row ID.
     * @param queries Array of query strings generated using the Query class provided by the SDK. [Learn more about queries](https://appwrite.io/docs/queries). Maximum of 100 queries are allowed, each 4096 characters long.
     * @param transactionId Transaction ID to read uncommitted changes within the transaction.
     * @return appwrite::Result<appwrite::models::Row>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::Row> getRow(        std::string_view databaseId,         std::string_view tableId,         std::string_view rowId,         std::optional<std::vector<std::string>> queries = std::vector<std::string>{},         std::optional<std::string_view> transactionId = std::nullopt    ) {
                std::string path_ = std::format("/tablesdb/{}/tables/{}/rows/{}", databaseId, tableId, rowId);
        
        nlohmann::json params = nlohmann::json::object();
        if (queries.has_value()) {
            params["queries"] = *queries;
        }
        if (transactionId.has_value()) {
            params["transactionId"] = std::string(transactionId.value());
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::Row>("GET", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Get a row by its unique ID. This endpoint response returns a JSON object
     * with the row data.
     *
     * @param databaseId Database ID.
     * @param tableId Table ID. You can create a new table using the Database service [server integration](https://appwrite.io/docs/references/cloud/server-dart/tablesDB#createTable).
     * @param rowId Row ID.
     * @param queries Array of query strings generated using the Query class provided by the SDK. [Learn more about queries](https://appwrite.io/docs/queries). Maximum of 100 queries are allowed, each 4096 characters long.
     * @param transactionId Transaction ID to read uncommitted changes within the transaction.
     * @return appwrite::Result<appwrite::models::Row>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::Row>> getRowAsync(        std::string_view databaseId,         std::string_view tableId,         std::string_view rowId,         std::optional<std::vector<std::string>> queries = std::vector<std::string>{},         std::optional<std::string_view> transactionId = std::nullopt    ) {
                std::string path_ = std::format("/tablesdb/{}/tables/{}/rows/{}", databaseId, tableId, rowId);
        
        nlohmann::json params = nlohmann::json::object();
        if (queries.has_value()) {
            params["queries"] = *queries;
        }
        if (transactionId.has_value()) {
            params["transactionId"] = std::string(transactionId.value());
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::Row>("GET", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Create or update a Row. Before using this route, you should create a new
     * table resource using either a [server
     * integration](https://appwrite.io/docs/references/cloud/server-dart/tablesDB#createTable)
     * API or directly from your database console.
     *
     * @param databaseId Database ID.
     * @param tableId Table ID.
     * @param rowId Row ID.
     * @param data Row data as JSON object. Include all required columns of the row to be created or updated.
     * @param permissions An array of permissions strings. By default, the current permissions are inherited. [Learn more about permissions](https://appwrite.io/docs/permissions).
     * @param transactionId Transaction ID for staging the operation.
     * @return appwrite::Result<appwrite::models::Row>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::Row> upsertRow(        std::string_view databaseId,         std::string_view tableId,         std::string_view rowId,         std::optional<nlohmann::json> data = nlohmann::json::object(),         std::optional<std::vector<std::string>> permissions = std::nullopt,         std::optional<std::string_view> transactionId = std::nullopt    ) {
                std::string path_ = std::format("/tablesdb/{}/tables/{}/rows/{}", databaseId, tableId, rowId);
        
        nlohmann::json params = nlohmann::json::object();
        if (data.has_value()) {
            params["data"] = *data;
        }
        if (permissions.has_value()) {
            params["permissions"] = *permissions;
        }
        if (transactionId.has_value()) {
            params["transactionId"] = std::string(transactionId.value());
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::Row>("PUT", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Create or update a Row. Before using this route, you should create a new
     * table resource using either a [server
     * integration](https://appwrite.io/docs/references/cloud/server-dart/tablesDB#createTable)
     * API or directly from your database console.
     *
     * @param databaseId Database ID.
     * @param tableId Table ID.
     * @param rowId Row ID.
     * @param data Row data as JSON object. Include all required columns of the row to be created or updated.
     * @param permissions An array of permissions strings. By default, the current permissions are inherited. [Learn more about permissions](https://appwrite.io/docs/permissions).
     * @param transactionId Transaction ID for staging the operation.
     * @return appwrite::Result<appwrite::models::Row>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::Row>> upsertRowAsync(        std::string_view databaseId,         std::string_view tableId,         std::string_view rowId,         std::optional<nlohmann::json> data = nlohmann::json::object(),         std::optional<std::vector<std::string>> permissions = std::nullopt,         std::optional<std::string_view> transactionId = std::nullopt    ) {
                std::string path_ = std::format("/tablesdb/{}/tables/{}/rows/{}", databaseId, tableId, rowId);
        
        nlohmann::json params = nlohmann::json::object();
        if (data.has_value()) {
            params["data"] = *data;
        }
        if (permissions.has_value()) {
            params["permissions"] = *permissions;
        }
        if (transactionId.has_value()) {
            params["transactionId"] = std::string(transactionId.value());
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::Row>("PUT", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Update a row by its unique ID. Using the patch method you can pass only
     * specific fields that will get updated.
     *
     * @param databaseId Database ID.
     * @param tableId Table ID.
     * @param rowId Row ID.
     * @param data Row data as JSON object. Include only columns and value pairs to be updated.
     * @param permissions An array of permissions strings. By default, the current permissions are inherited. [Learn more about permissions](https://appwrite.io/docs/permissions).
     * @param transactionId Transaction ID for staging the operation.
     * @return appwrite::Result<appwrite::models::Row>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::Row> updateRow(        std::string_view databaseId,         std::string_view tableId,         std::string_view rowId,         std::optional<nlohmann::json> data = nlohmann::json::object(),         std::optional<std::vector<std::string>> permissions = std::nullopt,         std::optional<std::string_view> transactionId = std::nullopt    ) {
                std::string path_ = std::format("/tablesdb/{}/tables/{}/rows/{}", databaseId, tableId, rowId);
        
        nlohmann::json params = nlohmann::json::object();
        if (data.has_value()) {
            params["data"] = *data;
        }
        if (permissions.has_value()) {
            params["permissions"] = *permissions;
        }
        if (transactionId.has_value()) {
            params["transactionId"] = std::string(transactionId.value());
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::Row>("PATCH", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Update a row by its unique ID. Using the patch method you can pass only
     * specific fields that will get updated.
     *
     * @param databaseId Database ID.
     * @param tableId Table ID.
     * @param rowId Row ID.
     * @param data Row data as JSON object. Include only columns and value pairs to be updated.
     * @param permissions An array of permissions strings. By default, the current permissions are inherited. [Learn more about permissions](https://appwrite.io/docs/permissions).
     * @param transactionId Transaction ID for staging the operation.
     * @return appwrite::Result<appwrite::models::Row>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::Row>> updateRowAsync(        std::string_view databaseId,         std::string_view tableId,         std::string_view rowId,         std::optional<nlohmann::json> data = nlohmann::json::object(),         std::optional<std::vector<std::string>> permissions = std::nullopt,         std::optional<std::string_view> transactionId = std::nullopt    ) {
                std::string path_ = std::format("/tablesdb/{}/tables/{}/rows/{}", databaseId, tableId, rowId);
        
        nlohmann::json params = nlohmann::json::object();
        if (data.has_value()) {
            params["data"] = *data;
        }
        if (permissions.has_value()) {
            params["permissions"] = *permissions;
        }
        if (transactionId.has_value()) {
            params["transactionId"] = std::string(transactionId.value());
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::Row>("PATCH", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Delete a row by its unique ID.
     *
     * @param databaseId Database ID.
     * @param tableId Table ID. You can create a new table using the Database service [server integration](https://appwrite.io/docs/references/cloud/server-dart/tablesDB#createTable).
     * @param rowId Row ID.
     * @param transactionId Transaction ID for staging the operation.
     * @return appwrite::Result<void>
     */
    [[nodiscard]] appwrite::Result<void> deleteRow(        std::string_view databaseId,         std::string_view tableId,         std::string_view rowId,         std::optional<std::string_view> transactionId = std::nullopt    ) {
                std::string path_ = std::format("/tablesdb/{}/tables/{}/rows/{}", databaseId, tableId, rowId);
        
        nlohmann::json params = nlohmann::json::object();
        if (transactionId.has_value()) {
            params["transactionId"] = std::string(transactionId.value());
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callVoid("DELETE", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Delete a row by its unique ID.
     *
     * @param databaseId Database ID.
     * @param tableId Table ID. You can create a new table using the Database service [server integration](https://appwrite.io/docs/references/cloud/server-dart/tablesDB#createTable).
     * @param rowId Row ID.
     * @param transactionId Transaction ID for staging the operation.
     * @return appwrite::Result<void>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<void>> deleteRowAsync(        std::string_view databaseId,         std::string_view tableId,         std::string_view rowId,         std::optional<std::string_view> transactionId = std::nullopt    ) {
                std::string path_ = std::format("/tablesdb/{}/tables/{}/rows/{}", databaseId, tableId, rowId);
        
        nlohmann::json params = nlohmann::json::object();
        if (transactionId.has_value()) {
            params["transactionId"] = std::string(transactionId.value());
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callVoidAsync("DELETE", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Decrement a specific column of a row by a given value.
     *
     * @param databaseId Database ID.
     * @param tableId Table ID.
     * @param rowId Row ID.
     * @param column Column key.
     * @param value Value to increment the column by. The value must be a number.
     * @param min Minimum value for the column. If the current value is lesser than this value, an exception will be thrown.
     * @param transactionId Transaction ID for staging the operation.
     * @return appwrite::Result<appwrite::models::Row>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::Row> decrementRowColumn(        std::string_view databaseId,         std::string_view tableId,         std::string_view rowId,         std::string_view column,         std::optional<double> value = 1,         std::optional<double> min = std::nullopt,         std::optional<std::string_view> transactionId = std::nullopt    ) {
                std::string path_ = std::format("/tablesdb/{}/tables/{}/rows/{}/{}/decrement", databaseId, tableId, rowId, column);
        
        nlohmann::json params = nlohmann::json::object();
        if (value.has_value()) {
            params["value"] = *value;
        }
        if (min.has_value()) {
            params["min"] = *min;
        }
        if (transactionId.has_value()) {
            params["transactionId"] = std::string(transactionId.value());
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::Row>("PATCH", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Decrement a specific column of a row by a given value.
     *
     * @param databaseId Database ID.
     * @param tableId Table ID.
     * @param rowId Row ID.
     * @param column Column key.
     * @param value Value to increment the column by. The value must be a number.
     * @param min Minimum value for the column. If the current value is lesser than this value, an exception will be thrown.
     * @param transactionId Transaction ID for staging the operation.
     * @return appwrite::Result<appwrite::models::Row>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::Row>> decrementRowColumnAsync(        std::string_view databaseId,         std::string_view tableId,         std::string_view rowId,         std::string_view column,         std::optional<double> value = 1,         std::optional<double> min = std::nullopt,         std::optional<std::string_view> transactionId = std::nullopt    ) {
                std::string path_ = std::format("/tablesdb/{}/tables/{}/rows/{}/{}/decrement", databaseId, tableId, rowId, column);
        
        nlohmann::json params = nlohmann::json::object();
        if (value.has_value()) {
            params["value"] = *value;
        }
        if (min.has_value()) {
            params["min"] = *min;
        }
        if (transactionId.has_value()) {
            params["transactionId"] = std::string(transactionId.value());
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::Row>("PATCH", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Increment a specific column of a row by a given value.
     *
     * @param databaseId Database ID.
     * @param tableId Table ID.
     * @param rowId Row ID.
     * @param column Column key.
     * @param value Value to increment the column by. The value must be a number.
     * @param max Maximum value for the column. If the current value is greater than this value, an error will be thrown.
     * @param transactionId Transaction ID for staging the operation.
     * @return appwrite::Result<appwrite::models::Row>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::Row> incrementRowColumn(        std::string_view databaseId,         std::string_view tableId,         std::string_view rowId,         std::string_view column,         std::optional<double> value = 1,         std::optional<double> max = std::nullopt,         std::optional<std::string_view> transactionId = std::nullopt    ) {
                std::string path_ = std::format("/tablesdb/{}/tables/{}/rows/{}/{}/increment", databaseId, tableId, rowId, column);
        
        nlohmann::json params = nlohmann::json::object();
        if (value.has_value()) {
            params["value"] = *value;
        }
        if (max.has_value()) {
            params["max"] = *max;
        }
        if (transactionId.has_value()) {
            params["transactionId"] = std::string(transactionId.value());
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::Row>("PATCH", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Increment a specific column of a row by a given value.
     *
     * @param databaseId Database ID.
     * @param tableId Table ID.
     * @param rowId Row ID.
     * @param column Column key.
     * @param value Value to increment the column by. The value must be a number.
     * @param max Maximum value for the column. If the current value is greater than this value, an error will be thrown.
     * @param transactionId Transaction ID for staging the operation.
     * @return appwrite::Result<appwrite::models::Row>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::Row>> incrementRowColumnAsync(        std::string_view databaseId,         std::string_view tableId,         std::string_view rowId,         std::string_view column,         std::optional<double> value = 1,         std::optional<double> max = std::nullopt,         std::optional<std::string_view> transactionId = std::nullopt    ) {
                std::string path_ = std::format("/tablesdb/{}/tables/{}/rows/{}/{}/increment", databaseId, tableId, rowId, column);
        
        nlohmann::json params = nlohmann::json::object();
        if (value.has_value()) {
            params["value"] = *value;
        }
        if (max.has_value()) {
            params["max"] = *max;
        }
        if (transactionId.has_value()) {
            params["transactionId"] = std::string(transactionId.value());
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::Row>("PATCH", path_, std::move(local_headers_), std::move(params));
    }
};

/**
 * @brief The Teams service allows you to group users of your project and to enable them to share read and write access to your project resources
 */
class Teams : public Service {
public:
    explicit Teams(Client& client) : Service(client) {}

    /**
     * Get a list of all the teams in which the current user is a member. You can
     * use the parameters to filter your results.
     *
     * @param queries Array of query strings generated using the Query class provided by the SDK. [Learn more about queries](https://appwrite.io/docs/queries). Maximum of 100 queries are allowed, each 4096 characters long. You may filter on the following attributes: name, total, billingPlan
     * @param search Search term to filter your list results. Max length: 256 chars.
     * @param total When set to false, the total count returned will be 0 and will not be calculated.
     * @return appwrite::Result<appwrite::models::TeamList>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::TeamList> list(        std::optional<std::vector<std::string>> queries = std::vector<std::string>{},         std::optional<std::string_view> search = std::nullopt,         std::optional<bool> total = true    ) {
                std::string path_ = std::format("/teams");
        
        nlohmann::json params = nlohmann::json::object();
        if (queries.has_value()) {
            params["queries"] = *queries;
        }
        if (search.has_value()) {
            params["search"] = std::string(search.value());
        }
        if (total.has_value()) {
            params["total"] = *total;
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::TeamList>("GET", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Get a list of all the teams in which the current user is a member. You can
     * use the parameters to filter your results.
     *
     * @param queries Array of query strings generated using the Query class provided by the SDK. [Learn more about queries](https://appwrite.io/docs/queries). Maximum of 100 queries are allowed, each 4096 characters long. You may filter on the following attributes: name, total, billingPlan
     * @param search Search term to filter your list results. Max length: 256 chars.
     * @param total When set to false, the total count returned will be 0 and will not be calculated.
     * @return appwrite::Result<appwrite::models::TeamList>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::TeamList>> listAsync(        std::optional<std::vector<std::string>> queries = std::vector<std::string>{},         std::optional<std::string_view> search = std::nullopt,         std::optional<bool> total = true    ) {
                std::string path_ = std::format("/teams");
        
        nlohmann::json params = nlohmann::json::object();
        if (queries.has_value()) {
            params["queries"] = *queries;
        }
        if (search.has_value()) {
            params["search"] = std::string(search.value());
        }
        if (total.has_value()) {
            params["total"] = *total;
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::TeamList>("GET", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Create a new team. The user who creates the team will automatically be
     * assigned as the owner of the team. Only the users with the owner role can
     * invite new members, add new owners and delete or update the team.
     *
     * @param teamId Team ID. Choose a custom ID or generate a random ID with `ID.unique()`. Valid chars are a-z, A-Z, 0-9, period, hyphen, and underscore. Can't start with a special char. Max length is 36 chars.
     * @param name Team name. Max length: 128 chars.
     * @param roles Array of strings. Use this param to set the roles in the team for the user who created it. The default role is **owner**. A role can be any string. Learn more about [roles and permissions](https://appwrite.io/docs/permissions). Maximum of 100 roles are allowed, each 32 characters long.
     * @return appwrite::Result<appwrite::models::Team>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::Team> create(        std::string_view teamId,         std::string_view name,         std::optional<std::vector<std::string>> roles = std::vector<std::string>{"owner"}    ) {
                std::string path_ = std::format("/teams");
        
        nlohmann::json params = nlohmann::json::object();
        params["teamId"] = std::string(teamId);
        params["name"] = std::string(name);
        if (roles.has_value()) {
            params["roles"] = *roles;
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::Team>("POST", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Create a new team. The user who creates the team will automatically be
     * assigned as the owner of the team. Only the users with the owner role can
     * invite new members, add new owners and delete or update the team.
     *
     * @param teamId Team ID. Choose a custom ID or generate a random ID with `ID.unique()`. Valid chars are a-z, A-Z, 0-9, period, hyphen, and underscore. Can't start with a special char. Max length is 36 chars.
     * @param name Team name. Max length: 128 chars.
     * @param roles Array of strings. Use this param to set the roles in the team for the user who created it. The default role is **owner**. A role can be any string. Learn more about [roles and permissions](https://appwrite.io/docs/permissions). Maximum of 100 roles are allowed, each 32 characters long.
     * @return appwrite::Result<appwrite::models::Team>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::Team>> createAsync(        std::string_view teamId,         std::string_view name,         std::optional<std::vector<std::string>> roles = std::vector<std::string>{"owner"}    ) {
                std::string path_ = std::format("/teams");
        
        nlohmann::json params = nlohmann::json::object();
        params["teamId"] = std::string(teamId);
        params["name"] = std::string(name);
        if (roles.has_value()) {
            params["roles"] = *roles;
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::Team>("POST", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Get a team by its ID. All team members have read access for this resource.
     *
     * @param teamId Team ID.
     * @return appwrite::Result<appwrite::models::Team>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::Team> get(        std::string_view teamId    ) {
                std::string path_ = std::format("/teams/{}", teamId);
        
        nlohmann::json params = nlohmann::json::object();
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::Team>("GET", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Get a team by its ID. All team members have read access for this resource.
     *
     * @param teamId Team ID.
     * @return appwrite::Result<appwrite::models::Team>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::Team>> getAsync(        std::string_view teamId    ) {
                std::string path_ = std::format("/teams/{}", teamId);
        
        nlohmann::json params = nlohmann::json::object();
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::Team>("GET", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Update the team's name by its unique ID.
     *
     * @param teamId Team ID.
     * @param name New team name. Max length: 128 chars.
     * @return appwrite::Result<appwrite::models::Team>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::Team> updateName(        std::string_view teamId,         std::string_view name    ) {
                std::string path_ = std::format("/teams/{}", teamId);
        
        nlohmann::json params = nlohmann::json::object();
        params["name"] = std::string(name);
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::Team>("PUT", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Update the team's name by its unique ID.
     *
     * @param teamId Team ID.
     * @param name New team name. Max length: 128 chars.
     * @return appwrite::Result<appwrite::models::Team>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::Team>> updateNameAsync(        std::string_view teamId,         std::string_view name    ) {
                std::string path_ = std::format("/teams/{}", teamId);
        
        nlohmann::json params = nlohmann::json::object();
        params["name"] = std::string(name);
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::Team>("PUT", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Delete a team using its ID. Only team members with the owner role can
     * delete the team.
     *
     * @param teamId Team ID.
     * @return appwrite::Result<void>
     */
    [[nodiscard]] appwrite::Result<void> delete_(        std::string_view teamId    ) {
                std::string path_ = std::format("/teams/{}", teamId);
        
        nlohmann::json params = nlohmann::json::object();
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callVoid("DELETE", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Delete a team using its ID. Only team members with the owner role can
     * delete the team.
     *
     * @param teamId Team ID.
     * @return appwrite::Result<void>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<void>> delete_Async(        std::string_view teamId    ) {
                std::string path_ = std::format("/teams/{}", teamId);
        
        nlohmann::json params = nlohmann::json::object();
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callVoidAsync("DELETE", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Use this endpoint to list a team's members using the team's ID. All team
     * members have read access to this endpoint. Hide sensitive attributes from
     * the response by toggling membership privacy in the Console.
     *
     * @param teamId Team ID.
     * @param queries Array of query strings generated using the Query class provided by the SDK. [Learn more about queries](https://appwrite.io/docs/queries). Maximum of 100 queries are allowed, each 4096 characters long. You may filter on the following attributes: userId, teamId, invited, joined, confirm, roles
     * @param search Search term to filter your list results. Max length: 256 chars.
     * @param total When set to false, the total count returned will be 0 and will not be calculated.
     * @return appwrite::Result<appwrite::models::MembershipList>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::MembershipList> listMemberships(        std::string_view teamId,         std::optional<std::vector<std::string>> queries = std::vector<std::string>{},         std::optional<std::string_view> search = std::nullopt,         std::optional<bool> total = true    ) {
                std::string path_ = std::format("/teams/{}/memberships", teamId);
        
        nlohmann::json params = nlohmann::json::object();
        if (queries.has_value()) {
            params["queries"] = *queries;
        }
        if (search.has_value()) {
            params["search"] = std::string(search.value());
        }
        if (total.has_value()) {
            params["total"] = *total;
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::MembershipList>("GET", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Use this endpoint to list a team's members using the team's ID. All team
     * members have read access to this endpoint. Hide sensitive attributes from
     * the response by toggling membership privacy in the Console.
     *
     * @param teamId Team ID.
     * @param queries Array of query strings generated using the Query class provided by the SDK. [Learn more about queries](https://appwrite.io/docs/queries). Maximum of 100 queries are allowed, each 4096 characters long. You may filter on the following attributes: userId, teamId, invited, joined, confirm, roles
     * @param search Search term to filter your list results. Max length: 256 chars.
     * @param total When set to false, the total count returned will be 0 and will not be calculated.
     * @return appwrite::Result<appwrite::models::MembershipList>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::MembershipList>> listMembershipsAsync(        std::string_view teamId,         std::optional<std::vector<std::string>> queries = std::vector<std::string>{},         std::optional<std::string_view> search = std::nullopt,         std::optional<bool> total = true    ) {
                std::string path_ = std::format("/teams/{}/memberships", teamId);
        
        nlohmann::json params = nlohmann::json::object();
        if (queries.has_value()) {
            params["queries"] = *queries;
        }
        if (search.has_value()) {
            params["search"] = std::string(search.value());
        }
        if (total.has_value()) {
            params["total"] = *total;
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::MembershipList>("GET", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Invite a new member to join your team. Provide an ID for existing users, or
     * invite unregistered users using an email or phone number. If initiated from
     * a Client SDK, Appwrite will send an email or sms with a link to join the
     * team to the invited user, and an account will be created for them if one
     * doesn't exist. If initiated from a Server SDK, the new member will be added
     * automatically to the team.

You only need to provide one of a user ID,
     * email, or phone number. Appwrite will prioritize accepting the user ID >
     * email > phone number if you provide more than one of these parameters.

Use
     * the `url` parameter to redirect the user from the invitation email to your
     * app. After the user is redirected, use the [Update Team Membership
     * Status](https://appwrite.io/docs/references/cloud/client-web/teams#updateMembershipStatus)
     * endpoint to allow the user to accept the invitation to the team. 

Please
     * note that to avoid a [Redirect
     * Attack](https://github.com/OWASP/CheatSheetSeries/blob/master/cheatsheets/Unvalidated_Redirects_and_Forwards_Cheat_Sheet.md)
     * Appwrite will accept the only redirect URLs under the domains you have
     * added as a platform on the Appwrite Console.
     *
     * @param teamId Team ID.
     * @param roles Array of strings. Use this param to set the user roles in the team. A role can be any string. Learn more about [roles and permissions](https://appwrite.io/docs/permissions). Maximum of 100 roles are allowed, each 81 characters long.
     * @param email Email of the new team member.
     * @param userId ID of the user to be added to a team.
     * @param phone Phone number. Format this number with a leading '+' and a country code, e.g., +16175551212.
     * @param url URL to redirect the user back to your app from the invitation email. This parameter is not required when an API key is supplied. Only URLs from hostnames in your project platform list are allowed. This requirement helps to prevent an [open redirect](https://cheatsheetseries.owasp.org/cheatsheets/Unvalidated_Redirects_and_Forwards_Cheat_Sheet.html) attack against your project API.
     * @param name Name of the new team member. Max length: 128 chars.
     * @return appwrite::Result<appwrite::models::Membership>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::Membership> createMembership(        std::string_view teamId,         std::vector<std::string> roles,         std::optional<std::string_view> email = std::nullopt,         std::optional<std::string_view> userId = std::nullopt,         std::optional<std::string_view> phone = std::nullopt,         std::optional<std::string_view> url = std::nullopt,         std::optional<std::string_view> name = std::nullopt    ) {
                std::string path_ = std::format("/teams/{}/memberships", teamId);
        
        nlohmann::json params = nlohmann::json::object();
        if (email.has_value()) {
            params["email"] = std::string(email.value());
        }
        if (userId.has_value()) {
            params["userId"] = std::string(userId.value());
        }
        if (phone.has_value()) {
            params["phone"] = std::string(phone.value());
        }
        params["roles"] = roles;
        if (url.has_value()) {
            params["url"] = std::string(url.value());
        }
        if (name.has_value()) {
            params["name"] = std::string(name.value());
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::Membership>("POST", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Invite a new member to join your team. Provide an ID for existing users, or
     * invite unregistered users using an email or phone number. If initiated from
     * a Client SDK, Appwrite will send an email or sms with a link to join the
     * team to the invited user, and an account will be created for them if one
     * doesn't exist. If initiated from a Server SDK, the new member will be added
     * automatically to the team.

You only need to provide one of a user ID,
     * email, or phone number. Appwrite will prioritize accepting the user ID >
     * email > phone number if you provide more than one of these parameters.

Use
     * the `url` parameter to redirect the user from the invitation email to your
     * app. After the user is redirected, use the [Update Team Membership
     * Status](https://appwrite.io/docs/references/cloud/client-web/teams#updateMembershipStatus)
     * endpoint to allow the user to accept the invitation to the team. 

Please
     * note that to avoid a [Redirect
     * Attack](https://github.com/OWASP/CheatSheetSeries/blob/master/cheatsheets/Unvalidated_Redirects_and_Forwards_Cheat_Sheet.md)
     * Appwrite will accept the only redirect URLs under the domains you have
     * added as a platform on the Appwrite Console.
     *
     * @param teamId Team ID.
     * @param roles Array of strings. Use this param to set the user roles in the team. A role can be any string. Learn more about [roles and permissions](https://appwrite.io/docs/permissions). Maximum of 100 roles are allowed, each 81 characters long.
     * @param email Email of the new team member.
     * @param userId ID of the user to be added to a team.
     * @param phone Phone number. Format this number with a leading '+' and a country code, e.g., +16175551212.
     * @param url URL to redirect the user back to your app from the invitation email. This parameter is not required when an API key is supplied. Only URLs from hostnames in your project platform list are allowed. This requirement helps to prevent an [open redirect](https://cheatsheetseries.owasp.org/cheatsheets/Unvalidated_Redirects_and_Forwards_Cheat_Sheet.html) attack against your project API.
     * @param name Name of the new team member. Max length: 128 chars.
     * @return appwrite::Result<appwrite::models::Membership>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::Membership>> createMembershipAsync(        std::string_view teamId,         std::vector<std::string> roles,         std::optional<std::string_view> email = std::nullopt,         std::optional<std::string_view> userId = std::nullopt,         std::optional<std::string_view> phone = std::nullopt,         std::optional<std::string_view> url = std::nullopt,         std::optional<std::string_view> name = std::nullopt    ) {
                std::string path_ = std::format("/teams/{}/memberships", teamId);
        
        nlohmann::json params = nlohmann::json::object();
        if (email.has_value()) {
            params["email"] = std::string(email.value());
        }
        if (userId.has_value()) {
            params["userId"] = std::string(userId.value());
        }
        if (phone.has_value()) {
            params["phone"] = std::string(phone.value());
        }
        params["roles"] = roles;
        if (url.has_value()) {
            params["url"] = std::string(url.value());
        }
        if (name.has_value()) {
            params["name"] = std::string(name.value());
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::Membership>("POST", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Get a team member by the membership unique id. All team members have read
     * access for this resource. Hide sensitive attributes from the response by
     * toggling membership privacy in the Console.
     *
     * @param teamId Team ID.
     * @param membershipId Membership ID.
     * @return appwrite::Result<appwrite::models::Membership>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::Membership> getMembership(        std::string_view teamId,         std::string_view membershipId    ) {
                std::string path_ = std::format("/teams/{}/memberships/{}", teamId, membershipId);
        
        nlohmann::json params = nlohmann::json::object();
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::Membership>("GET", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Get a team member by the membership unique id. All team members have read
     * access for this resource. Hide sensitive attributes from the response by
     * toggling membership privacy in the Console.
     *
     * @param teamId Team ID.
     * @param membershipId Membership ID.
     * @return appwrite::Result<appwrite::models::Membership>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::Membership>> getMembershipAsync(        std::string_view teamId,         std::string_view membershipId    ) {
                std::string path_ = std::format("/teams/{}/memberships/{}", teamId, membershipId);
        
        nlohmann::json params = nlohmann::json::object();
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::Membership>("GET", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Modify the roles of a team member. Only team members with the owner role
     * have access to this endpoint. Learn more about [roles and
     * permissions](https://appwrite.io/docs/permissions).
     *
     * @param teamId Team ID.
     * @param membershipId Membership ID.
     * @param roles An array of strings. Use this param to set the user's roles in the team. A role can be any string. Learn more about [roles and permissions](https://appwrite.io/docs/permissions). Maximum of 100 roles are allowed, each 81 characters long.
     * @return appwrite::Result<appwrite::models::Membership>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::Membership> updateMembership(        std::string_view teamId,         std::string_view membershipId,         std::vector<std::string> roles    ) {
                std::string path_ = std::format("/teams/{}/memberships/{}", teamId, membershipId);
        
        nlohmann::json params = nlohmann::json::object();
        params["roles"] = roles;
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::Membership>("PATCH", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Modify the roles of a team member. Only team members with the owner role
     * have access to this endpoint. Learn more about [roles and
     * permissions](https://appwrite.io/docs/permissions).
     *
     * @param teamId Team ID.
     * @param membershipId Membership ID.
     * @param roles An array of strings. Use this param to set the user's roles in the team. A role can be any string. Learn more about [roles and permissions](https://appwrite.io/docs/permissions). Maximum of 100 roles are allowed, each 81 characters long.
     * @return appwrite::Result<appwrite::models::Membership>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::Membership>> updateMembershipAsync(        std::string_view teamId,         std::string_view membershipId,         std::vector<std::string> roles    ) {
                std::string path_ = std::format("/teams/{}/memberships/{}", teamId, membershipId);
        
        nlohmann::json params = nlohmann::json::object();
        params["roles"] = roles;
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::Membership>("PATCH", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * This endpoint allows a user to leave a team or for a team owner to delete
     * the membership of any other team member. You can also use this endpoint to
     * delete a user membership even if it is not accepted.
     *
     * @param teamId Team ID.
     * @param membershipId Membership ID.
     * @return appwrite::Result<void>
     */
    [[nodiscard]] appwrite::Result<void> deleteMembership(        std::string_view teamId,         std::string_view membershipId    ) {
                std::string path_ = std::format("/teams/{}/memberships/{}", teamId, membershipId);
        
        nlohmann::json params = nlohmann::json::object();
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callVoid("DELETE", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * This endpoint allows a user to leave a team or for a team owner to delete
     * the membership of any other team member. You can also use this endpoint to
     * delete a user membership even if it is not accepted.
     *
     * @param teamId Team ID.
     * @param membershipId Membership ID.
     * @return appwrite::Result<void>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<void>> deleteMembershipAsync(        std::string_view teamId,         std::string_view membershipId    ) {
                std::string path_ = std::format("/teams/{}/memberships/{}", teamId, membershipId);
        
        nlohmann::json params = nlohmann::json::object();
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callVoidAsync("DELETE", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Use this endpoint to allow a user to accept an invitation to join a team
     * after being redirected back to your app from the invitation email received
     * by the user.

If the request is successful, a session for the user is
     * automatically created.
     *
     * @param teamId Team ID.
     * @param membershipId Membership ID.
     * @param userId User ID.
     * @param secret Secret key.
     * @return appwrite::Result<appwrite::models::Membership>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::Membership> updateMembershipStatus(        std::string_view teamId,         std::string_view membershipId,         std::string_view userId,         std::string_view secret    ) {
                std::string path_ = std::format("/teams/{}/memberships/{}/status", teamId, membershipId);
        
        nlohmann::json params = nlohmann::json::object();
        params["userId"] = std::string(userId);
        params["secret"] = std::string(secret);
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::Membership>("PATCH", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Use this endpoint to allow a user to accept an invitation to join a team
     * after being redirected back to your app from the invitation email received
     * by the user.

If the request is successful, a session for the user is
     * automatically created.
     *
     * @param teamId Team ID.
     * @param membershipId Membership ID.
     * @param userId User ID.
     * @param secret Secret key.
     * @return appwrite::Result<appwrite::models::Membership>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::Membership>> updateMembershipStatusAsync(        std::string_view teamId,         std::string_view membershipId,         std::string_view userId,         std::string_view secret    ) {
                std::string path_ = std::format("/teams/{}/memberships/{}/status", teamId, membershipId);
        
        nlohmann::json params = nlohmann::json::object();
        params["userId"] = std::string(userId);
        params["secret"] = std::string(secret);
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::Membership>("PATCH", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Get the team's shared preferences by its unique ID. If a preference doesn't
     * need to be shared by all team members, prefer storing them in [user
     * preferences](https://appwrite.io/docs/references/cloud/client-web/account#getPrefs).
     *
     * @param teamId Team ID.
     * @return appwrite::Result<appwrite::models::Preferences>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::Preferences> getPrefs(        std::string_view teamId    ) {
                std::string path_ = std::format("/teams/{}/prefs", teamId);
        
        nlohmann::json params = nlohmann::json::object();
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::Preferences>("GET", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Get the team's shared preferences by its unique ID. If a preference doesn't
     * need to be shared by all team members, prefer storing them in [user
     * preferences](https://appwrite.io/docs/references/cloud/client-web/account#getPrefs).
     *
     * @param teamId Team ID.
     * @return appwrite::Result<appwrite::models::Preferences>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::Preferences>> getPrefsAsync(        std::string_view teamId    ) {
                std::string path_ = std::format("/teams/{}/prefs", teamId);
        
        nlohmann::json params = nlohmann::json::object();
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::Preferences>("GET", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Update the team's preferences by its unique ID. The object you pass is
     * stored as is and replaces any previous value. The maximum allowed prefs
     * size is 64kB and throws an error if exceeded.
     *
     * @param teamId Team ID.
     * @param prefs Prefs key-value JSON object.
     * @return appwrite::Result<appwrite::models::Preferences>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::Preferences> updatePrefs(        std::string_view teamId,         const nlohmann::json& prefs = nlohmann::json::object()    ) {
                std::string path_ = std::format("/teams/{}/prefs", teamId);
        
        nlohmann::json params = nlohmann::json::object();
        params["prefs"] = prefs;
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::Preferences>("PUT", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Update the team's preferences by its unique ID. The object you pass is
     * stored as is and replaces any previous value. The maximum allowed prefs
     * size is 64kB and throws an error if exceeded.
     *
     * @param teamId Team ID.
     * @param prefs Prefs key-value JSON object.
     * @return appwrite::Result<appwrite::models::Preferences>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::Preferences>> updatePrefsAsync(        std::string_view teamId,         const nlohmann::json& prefs = nlohmann::json::object()    ) {
                std::string path_ = std::format("/teams/{}/prefs", teamId);
        
        nlohmann::json params = nlohmann::json::object();
        params["prefs"] = prefs;
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::Preferences>("PUT", path_, std::move(local_headers_), std::move(params));
    }
};

/**
 * @brief The Users service allows you to manage your project users.
 */
class Users : public Service {
public:
    explicit Users(Client& client) : Service(client) {}

    /**
     * Get a list of all the project's users. You can use the query params to
     * filter your results.
     *
     * @param queries Array of query strings generated using the Query class provided by the SDK. [Learn more about queries](https://appwrite.io/docs/queries). Maximum of 100 queries are allowed, each 4096 characters long. You may filter on the following attributes: name, email, phone, status, passwordUpdate, registration, emailVerification, phoneVerification, labels, impersonator
     * @param search Search term to filter your list results. Max length: 256 chars.
     * @param total When set to false, the total count returned will be 0 and will not be calculated.
     * @return appwrite::Result<appwrite::models::UserList>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::UserList> list(        std::optional<std::vector<std::string>> queries = std::vector<std::string>{},         std::optional<std::string_view> search = std::nullopt,         std::optional<bool> total = true    ) {
                std::string path_ = std::format("/users");
        
        nlohmann::json params = nlohmann::json::object();
        if (queries.has_value()) {
            params["queries"] = *queries;
        }
        if (search.has_value()) {
            params["search"] = std::string(search.value());
        }
        if (total.has_value()) {
            params["total"] = *total;
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::UserList>("GET", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Get a list of all the project's users. You can use the query params to
     * filter your results.
     *
     * @param queries Array of query strings generated using the Query class provided by the SDK. [Learn more about queries](https://appwrite.io/docs/queries). Maximum of 100 queries are allowed, each 4096 characters long. You may filter on the following attributes: name, email, phone, status, passwordUpdate, registration, emailVerification, phoneVerification, labels, impersonator
     * @param search Search term to filter your list results. Max length: 256 chars.
     * @param total When set to false, the total count returned will be 0 and will not be calculated.
     * @return appwrite::Result<appwrite::models::UserList>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::UserList>> listAsync(        std::optional<std::vector<std::string>> queries = std::vector<std::string>{},         std::optional<std::string_view> search = std::nullopt,         std::optional<bool> total = true    ) {
                std::string path_ = std::format("/users");
        
        nlohmann::json params = nlohmann::json::object();
        if (queries.has_value()) {
            params["queries"] = *queries;
        }
        if (search.has_value()) {
            params["search"] = std::string(search.value());
        }
        if (total.has_value()) {
            params["total"] = *total;
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::UserList>("GET", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Create a new user.
     *
     * @param userId User ID. Choose a custom ID or generate a random ID with `ID.unique()`. Valid chars are a-z, A-Z, 0-9, period, hyphen, and underscore. Can't start with a special char. Max length is 36 chars.
     * @param email User email.
     * @param phone Phone number. Format this number with a leading '+' and a country code, e.g., +16175551212.
     * @param password Plain text user password. Must be at least 8 chars.
     * @param name User name. Max length: 128 chars.
     * @return appwrite::Result<appwrite::models::User>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::User> create(        std::string_view userId,         std::optional<std::string_view> email = std::nullopt,         std::optional<std::string_view> phone = std::nullopt,         std::optional<std::string_view> password = std::nullopt,         std::optional<std::string_view> name = std::nullopt    ) {
                std::string path_ = std::format("/users");
        
        nlohmann::json params = nlohmann::json::object();
        params["userId"] = std::string(userId);
        if (email.has_value()) {
            params["email"] = std::string(email.value());
        }
        if (phone.has_value()) {
            params["phone"] = std::string(phone.value());
        }
        if (password.has_value()) {
            params["password"] = std::string(password.value());
        }
        if (name.has_value()) {
            params["name"] = std::string(name.value());
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::User>("POST", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Create a new user.
     *
     * @param userId User ID. Choose a custom ID or generate a random ID with `ID.unique()`. Valid chars are a-z, A-Z, 0-9, period, hyphen, and underscore. Can't start with a special char. Max length is 36 chars.
     * @param email User email.
     * @param phone Phone number. Format this number with a leading '+' and a country code, e.g., +16175551212.
     * @param password Plain text user password. Must be at least 8 chars.
     * @param name User name. Max length: 128 chars.
     * @return appwrite::Result<appwrite::models::User>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::User>> createAsync(        std::string_view userId,         std::optional<std::string_view> email = std::nullopt,         std::optional<std::string_view> phone = std::nullopt,         std::optional<std::string_view> password = std::nullopt,         std::optional<std::string_view> name = std::nullopt    ) {
                std::string path_ = std::format("/users");
        
        nlohmann::json params = nlohmann::json::object();
        params["userId"] = std::string(userId);
        if (email.has_value()) {
            params["email"] = std::string(email.value());
        }
        if (phone.has_value()) {
            params["phone"] = std::string(phone.value());
        }
        if (password.has_value()) {
            params["password"] = std::string(password.value());
        }
        if (name.has_value()) {
            params["name"] = std::string(name.value());
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::User>("POST", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Create a new user. Password provided must be hashed with the
     * [Argon2](https://en.wikipedia.org/wiki/Argon2) algorithm. Use the [POST
     * /users](https://appwrite.io/docs/server/users#usersCreate) endpoint to
     * create users with a plain text password.
     *
     * @param userId User ID. Choose a custom ID or generate a random ID with `ID.unique()`. Valid chars are a-z, A-Z, 0-9, period, hyphen, and underscore. Can't start with a special char. Max length is 36 chars.
     * @param email User email.
     * @param password User password hashed using Argon2.
     * @param name User name. Max length: 128 chars.
     * @return appwrite::Result<appwrite::models::User>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::User> createArgon2User(        std::string_view userId,         std::string_view email,         std::string_view password,         std::optional<std::string_view> name = std::nullopt    ) {
                std::string path_ = std::format("/users/argon2");
        
        nlohmann::json params = nlohmann::json::object();
        params["userId"] = std::string(userId);
        params["email"] = std::string(email);
        params["password"] = std::string(password);
        if (name.has_value()) {
            params["name"] = std::string(name.value());
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::User>("POST", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Create a new user. Password provided must be hashed with the
     * [Argon2](https://en.wikipedia.org/wiki/Argon2) algorithm. Use the [POST
     * /users](https://appwrite.io/docs/server/users#usersCreate) endpoint to
     * create users with a plain text password.
     *
     * @param userId User ID. Choose a custom ID or generate a random ID with `ID.unique()`. Valid chars are a-z, A-Z, 0-9, period, hyphen, and underscore. Can't start with a special char. Max length is 36 chars.
     * @param email User email.
     * @param password User password hashed using Argon2.
     * @param name User name. Max length: 128 chars.
     * @return appwrite::Result<appwrite::models::User>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::User>> createArgon2UserAsync(        std::string_view userId,         std::string_view email,         std::string_view password,         std::optional<std::string_view> name = std::nullopt    ) {
                std::string path_ = std::format("/users/argon2");
        
        nlohmann::json params = nlohmann::json::object();
        params["userId"] = std::string(userId);
        params["email"] = std::string(email);
        params["password"] = std::string(password);
        if (name.has_value()) {
            params["name"] = std::string(name.value());
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::User>("POST", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Create a new user. Password provided must be hashed with the
     * [Bcrypt](https://en.wikipedia.org/wiki/Bcrypt) algorithm. Use the [POST
     * /users](https://appwrite.io/docs/server/users#usersCreate) endpoint to
     * create users with a plain text password.
     *
     * @param userId User ID. Choose a custom ID or generate a random ID with `ID.unique()`. Valid chars are a-z, A-Z, 0-9, period, hyphen, and underscore. Can't start with a special char. Max length is 36 chars.
     * @param email User email.
     * @param password User password hashed using Bcrypt.
     * @param name User name. Max length: 128 chars.
     * @return appwrite::Result<appwrite::models::User>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::User> createBcryptUser(        std::string_view userId,         std::string_view email,         std::string_view password,         std::optional<std::string_view> name = std::nullopt    ) {
                std::string path_ = std::format("/users/bcrypt");
        
        nlohmann::json params = nlohmann::json::object();
        params["userId"] = std::string(userId);
        params["email"] = std::string(email);
        params["password"] = std::string(password);
        if (name.has_value()) {
            params["name"] = std::string(name.value());
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::User>("POST", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Create a new user. Password provided must be hashed with the
     * [Bcrypt](https://en.wikipedia.org/wiki/Bcrypt) algorithm. Use the [POST
     * /users](https://appwrite.io/docs/server/users#usersCreate) endpoint to
     * create users with a plain text password.
     *
     * @param userId User ID. Choose a custom ID or generate a random ID with `ID.unique()`. Valid chars are a-z, A-Z, 0-9, period, hyphen, and underscore. Can't start with a special char. Max length is 36 chars.
     * @param email User email.
     * @param password User password hashed using Bcrypt.
     * @param name User name. Max length: 128 chars.
     * @return appwrite::Result<appwrite::models::User>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::User>> createBcryptUserAsync(        std::string_view userId,         std::string_view email,         std::string_view password,         std::optional<std::string_view> name = std::nullopt    ) {
                std::string path_ = std::format("/users/bcrypt");
        
        nlohmann::json params = nlohmann::json::object();
        params["userId"] = std::string(userId);
        params["email"] = std::string(email);
        params["password"] = std::string(password);
        if (name.has_value()) {
            params["name"] = std::string(name.value());
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::User>("POST", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Get identities for all users.
     *
     * @param queries Array of query strings generated using the Query class provided by the SDK. [Learn more about queries](https://appwrite.io/docs/queries). Maximum of 100 queries are allowed, each 4096 characters long. You may filter on the following attributes: userId, provider, providerUid, providerEmail, providerAccessTokenExpiry
     * @param search Search term to filter your list results. Max length: 256 chars.
     * @param total When set to false, the total count returned will be 0 and will not be calculated.
     * @return appwrite::Result<appwrite::models::IdentityList>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::IdentityList> listIdentities(        std::optional<std::vector<std::string>> queries = std::vector<std::string>{},         std::optional<std::string_view> search = std::nullopt,         std::optional<bool> total = true    ) {
                std::string path_ = std::format("/users/identities");
        
        nlohmann::json params = nlohmann::json::object();
        if (queries.has_value()) {
            params["queries"] = *queries;
        }
        if (search.has_value()) {
            params["search"] = std::string(search.value());
        }
        if (total.has_value()) {
            params["total"] = *total;
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::IdentityList>("GET", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Get identities for all users.
     *
     * @param queries Array of query strings generated using the Query class provided by the SDK. [Learn more about queries](https://appwrite.io/docs/queries). Maximum of 100 queries are allowed, each 4096 characters long. You may filter on the following attributes: userId, provider, providerUid, providerEmail, providerAccessTokenExpiry
     * @param search Search term to filter your list results. Max length: 256 chars.
     * @param total When set to false, the total count returned will be 0 and will not be calculated.
     * @return appwrite::Result<appwrite::models::IdentityList>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::IdentityList>> listIdentitiesAsync(        std::optional<std::vector<std::string>> queries = std::vector<std::string>{},         std::optional<std::string_view> search = std::nullopt,         std::optional<bool> total = true    ) {
                std::string path_ = std::format("/users/identities");
        
        nlohmann::json params = nlohmann::json::object();
        if (queries.has_value()) {
            params["queries"] = *queries;
        }
        if (search.has_value()) {
            params["search"] = std::string(search.value());
        }
        if (total.has_value()) {
            params["total"] = *total;
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::IdentityList>("GET", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Delete an identity by its unique ID.
     *
     * @param identityId Identity ID.
     * @return appwrite::Result<void>
     */
    [[nodiscard]] appwrite::Result<void> deleteIdentity(        std::string_view identityId    ) {
                std::string path_ = std::format("/users/identities/{}", identityId);
        
        nlohmann::json params = nlohmann::json::object();
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callVoid("DELETE", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Delete an identity by its unique ID.
     *
     * @param identityId Identity ID.
     * @return appwrite::Result<void>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<void>> deleteIdentityAsync(        std::string_view identityId    ) {
                std::string path_ = std::format("/users/identities/{}", identityId);
        
        nlohmann::json params = nlohmann::json::object();
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callVoidAsync("DELETE", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Create a new user. Password provided must be hashed with the
     * [MD5](https://en.wikipedia.org/wiki/MD5) algorithm. Use the [POST
     * /users](https://appwrite.io/docs/server/users#usersCreate) endpoint to
     * create users with a plain text password.
     *
     * @param userId User ID. Choose a custom ID or generate a random ID with `ID.unique()`. Valid chars are a-z, A-Z, 0-9, period, hyphen, and underscore. Can't start with a special char. Max length is 36 chars.
     * @param email User email.
     * @param password User password hashed using MD5.
     * @param name User name. Max length: 128 chars.
     * @return appwrite::Result<appwrite::models::User>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::User> createMD5User(        std::string_view userId,         std::string_view email,         std::string_view password,         std::optional<std::string_view> name = std::nullopt    ) {
                std::string path_ = std::format("/users/md5");
        
        nlohmann::json params = nlohmann::json::object();
        params["userId"] = std::string(userId);
        params["email"] = std::string(email);
        params["password"] = std::string(password);
        if (name.has_value()) {
            params["name"] = std::string(name.value());
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::User>("POST", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Create a new user. Password provided must be hashed with the
     * [MD5](https://en.wikipedia.org/wiki/MD5) algorithm. Use the [POST
     * /users](https://appwrite.io/docs/server/users#usersCreate) endpoint to
     * create users with a plain text password.
     *
     * @param userId User ID. Choose a custom ID or generate a random ID with `ID.unique()`. Valid chars are a-z, A-Z, 0-9, period, hyphen, and underscore. Can't start with a special char. Max length is 36 chars.
     * @param email User email.
     * @param password User password hashed using MD5.
     * @param name User name. Max length: 128 chars.
     * @return appwrite::Result<appwrite::models::User>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::User>> createMD5UserAsync(        std::string_view userId,         std::string_view email,         std::string_view password,         std::optional<std::string_view> name = std::nullopt    ) {
                std::string path_ = std::format("/users/md5");
        
        nlohmann::json params = nlohmann::json::object();
        params["userId"] = std::string(userId);
        params["email"] = std::string(email);
        params["password"] = std::string(password);
        if (name.has_value()) {
            params["name"] = std::string(name.value());
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::User>("POST", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Create a new user. Password provided must be hashed with the
     * [PHPass](https://www.openwall.com/phpass/) algorithm. Use the [POST
     * /users](https://appwrite.io/docs/server/users#usersCreate) endpoint to
     * create users with a plain text password.
     *
     * @param userId User ID. Choose a custom ID or pass the string `ID.unique()`to auto generate it. Valid chars are a-z, A-Z, 0-9, period, hyphen, and underscore. Can't start with a special char. Max length is 36 chars.
     * @param email User email.
     * @param password User password hashed using PHPass.
     * @param name User name. Max length: 128 chars.
     * @return appwrite::Result<appwrite::models::User>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::User> createPHPassUser(        std::string_view userId,         std::string_view email,         std::string_view password,         std::optional<std::string_view> name = std::nullopt    ) {
                std::string path_ = std::format("/users/phpass");
        
        nlohmann::json params = nlohmann::json::object();
        params["userId"] = std::string(userId);
        params["email"] = std::string(email);
        params["password"] = std::string(password);
        if (name.has_value()) {
            params["name"] = std::string(name.value());
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::User>("POST", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Create a new user. Password provided must be hashed with the
     * [PHPass](https://www.openwall.com/phpass/) algorithm. Use the [POST
     * /users](https://appwrite.io/docs/server/users#usersCreate) endpoint to
     * create users with a plain text password.
     *
     * @param userId User ID. Choose a custom ID or pass the string `ID.unique()`to auto generate it. Valid chars are a-z, A-Z, 0-9, period, hyphen, and underscore. Can't start with a special char. Max length is 36 chars.
     * @param email User email.
     * @param password User password hashed using PHPass.
     * @param name User name. Max length: 128 chars.
     * @return appwrite::Result<appwrite::models::User>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::User>> createPHPassUserAsync(        std::string_view userId,         std::string_view email,         std::string_view password,         std::optional<std::string_view> name = std::nullopt    ) {
                std::string path_ = std::format("/users/phpass");
        
        nlohmann::json params = nlohmann::json::object();
        params["userId"] = std::string(userId);
        params["email"] = std::string(email);
        params["password"] = std::string(password);
        if (name.has_value()) {
            params["name"] = std::string(name.value());
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::User>("POST", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Create a new user. Password provided must be hashed with the
     * [Scrypt](https://github.com/Tarsnap/scrypt) algorithm. Use the [POST
     * /users](https://appwrite.io/docs/server/users#usersCreate) endpoint to
     * create users with a plain text password.
     *
     * @param userId User ID. Choose a custom ID or generate a random ID with `ID.unique()`. Valid chars are a-z, A-Z, 0-9, period, hyphen, and underscore. Can't start with a special char. Max length is 36 chars.
     * @param email User email.
     * @param password User password hashed using Scrypt.
     * @param passwordSalt Optional salt used to hash password.
     * @param passwordCpu Optional CPU cost used to hash password.
     * @param passwordMemory Optional memory cost used to hash password.
     * @param passwordParallel Optional parallelization cost used to hash password.
     * @param passwordLength Optional hash length used to hash password.
     * @param name User name. Max length: 128 chars.
     * @return appwrite::Result<appwrite::models::User>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::User> createScryptUser(        std::string_view userId,         std::string_view email,         std::string_view password,         std::string_view passwordSalt,         int64_t passwordCpu,         int64_t passwordMemory,         int64_t passwordParallel,         int64_t passwordLength,         std::optional<std::string_view> name = std::nullopt    ) {
                std::string path_ = std::format("/users/scrypt");
        
        nlohmann::json params = nlohmann::json::object();
        params["userId"] = std::string(userId);
        params["email"] = std::string(email);
        params["password"] = std::string(password);
        params["passwordSalt"] = std::string(passwordSalt);
        params["passwordCpu"] = passwordCpu;
        params["passwordMemory"] = passwordMemory;
        params["passwordParallel"] = passwordParallel;
        params["passwordLength"] = passwordLength;
        if (name.has_value()) {
            params["name"] = std::string(name.value());
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::User>("POST", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Create a new user. Password provided must be hashed with the
     * [Scrypt](https://github.com/Tarsnap/scrypt) algorithm. Use the [POST
     * /users](https://appwrite.io/docs/server/users#usersCreate) endpoint to
     * create users with a plain text password.
     *
     * @param userId User ID. Choose a custom ID or generate a random ID with `ID.unique()`. Valid chars are a-z, A-Z, 0-9, period, hyphen, and underscore. Can't start with a special char. Max length is 36 chars.
     * @param email User email.
     * @param password User password hashed using Scrypt.
     * @param passwordSalt Optional salt used to hash password.
     * @param passwordCpu Optional CPU cost used to hash password.
     * @param passwordMemory Optional memory cost used to hash password.
     * @param passwordParallel Optional parallelization cost used to hash password.
     * @param passwordLength Optional hash length used to hash password.
     * @param name User name. Max length: 128 chars.
     * @return appwrite::Result<appwrite::models::User>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::User>> createScryptUserAsync(        std::string_view userId,         std::string_view email,         std::string_view password,         std::string_view passwordSalt,         int64_t passwordCpu,         int64_t passwordMemory,         int64_t passwordParallel,         int64_t passwordLength,         std::optional<std::string_view> name = std::nullopt    ) {
                std::string path_ = std::format("/users/scrypt");
        
        nlohmann::json params = nlohmann::json::object();
        params["userId"] = std::string(userId);
        params["email"] = std::string(email);
        params["password"] = std::string(password);
        params["passwordSalt"] = std::string(passwordSalt);
        params["passwordCpu"] = passwordCpu;
        params["passwordMemory"] = passwordMemory;
        params["passwordParallel"] = passwordParallel;
        params["passwordLength"] = passwordLength;
        if (name.has_value()) {
            params["name"] = std::string(name.value());
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::User>("POST", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Create a new user. Password provided must be hashed with the [Scrypt
     * Modified](https://gist.github.com/Meldiron/eecf84a0225eccb5a378d45bb27462cc)
     * algorithm. Use the [POST
     * /users](https://appwrite.io/docs/server/users#usersCreate) endpoint to
     * create users with a plain text password.
     *
     * @param userId User ID. Choose a custom ID or generate a random ID with `ID.unique()`. Valid chars are a-z, A-Z, 0-9, period, hyphen, and underscore. Can't start with a special char. Max length is 36 chars.
     * @param email User email.
     * @param password User password hashed using Scrypt Modified.
     * @param passwordSalt Salt used to hash password.
     * @param passwordSaltSeparator Salt separator used to hash password.
     * @param passwordSignerKey Signer key used to hash password.
     * @param name User name. Max length: 128 chars.
     * @return appwrite::Result<appwrite::models::User>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::User> createScryptModifiedUser(        std::string_view userId,         std::string_view email,         std::string_view password,         std::string_view passwordSalt,         std::string_view passwordSaltSeparator,         std::string_view passwordSignerKey,         std::optional<std::string_view> name = std::nullopt    ) {
                std::string path_ = std::format("/users/scrypt-modified");
        
        nlohmann::json params = nlohmann::json::object();
        params["userId"] = std::string(userId);
        params["email"] = std::string(email);
        params["password"] = std::string(password);
        params["passwordSalt"] = std::string(passwordSalt);
        params["passwordSaltSeparator"] = std::string(passwordSaltSeparator);
        params["passwordSignerKey"] = std::string(passwordSignerKey);
        if (name.has_value()) {
            params["name"] = std::string(name.value());
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::User>("POST", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Create a new user. Password provided must be hashed with the [Scrypt
     * Modified](https://gist.github.com/Meldiron/eecf84a0225eccb5a378d45bb27462cc)
     * algorithm. Use the [POST
     * /users](https://appwrite.io/docs/server/users#usersCreate) endpoint to
     * create users with a plain text password.
     *
     * @param userId User ID. Choose a custom ID or generate a random ID with `ID.unique()`. Valid chars are a-z, A-Z, 0-9, period, hyphen, and underscore. Can't start with a special char. Max length is 36 chars.
     * @param email User email.
     * @param password User password hashed using Scrypt Modified.
     * @param passwordSalt Salt used to hash password.
     * @param passwordSaltSeparator Salt separator used to hash password.
     * @param passwordSignerKey Signer key used to hash password.
     * @param name User name. Max length: 128 chars.
     * @return appwrite::Result<appwrite::models::User>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::User>> createScryptModifiedUserAsync(        std::string_view userId,         std::string_view email,         std::string_view password,         std::string_view passwordSalt,         std::string_view passwordSaltSeparator,         std::string_view passwordSignerKey,         std::optional<std::string_view> name = std::nullopt    ) {
                std::string path_ = std::format("/users/scrypt-modified");
        
        nlohmann::json params = nlohmann::json::object();
        params["userId"] = std::string(userId);
        params["email"] = std::string(email);
        params["password"] = std::string(password);
        params["passwordSalt"] = std::string(passwordSalt);
        params["passwordSaltSeparator"] = std::string(passwordSaltSeparator);
        params["passwordSignerKey"] = std::string(passwordSignerKey);
        if (name.has_value()) {
            params["name"] = std::string(name.value());
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::User>("POST", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Create a new user. Password provided must be hashed with the
     * [SHA](https://en.wikipedia.org/wiki/Secure_Hash_Algorithm) algorithm. Use
     * the [POST /users](https://appwrite.io/docs/server/users#usersCreate)
     * endpoint to create users with a plain text password.
     *
     * @param userId User ID. Choose a custom ID or generate a random ID with `ID.unique()`. Valid chars are a-z, A-Z, 0-9, period, hyphen, and underscore. Can't start with a special char. Max length is 36 chars.
     * @param email User email.
     * @param password User password hashed using SHA.
     * @param passwordVersion Optional SHA version used to hash password. Allowed values are: 'sha1', 'sha224', 'sha256', 'sha384', 'sha512/224', 'sha512/256', 'sha512', 'sha3-224', 'sha3-256', 'sha3-384', 'sha3-512'
     * @param name User name. Max length: 128 chars.
     * @return appwrite::Result<appwrite::models::User>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::User> createSHAUser(        std::string_view userId,         std::string_view email,         std::string_view password,         std::optional<appwrite::enums::PasswordHash> passwordVersion = std::nullopt,         std::optional<std::string_view> name = std::nullopt    ) {
                std::string path_ = std::format("/users/sha");
        
        nlohmann::json params = nlohmann::json::object();
        params["userId"] = std::string(userId);
        params["email"] = std::string(email);
        params["password"] = std::string(password);
        if (passwordVersion.has_value()) {
            params["passwordVersion"] = *passwordVersion;
        }
        if (name.has_value()) {
            params["name"] = std::string(name.value());
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::User>("POST", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Create a new user. Password provided must be hashed with the
     * [SHA](https://en.wikipedia.org/wiki/Secure_Hash_Algorithm) algorithm. Use
     * the [POST /users](https://appwrite.io/docs/server/users#usersCreate)
     * endpoint to create users with a plain text password.
     *
     * @param userId User ID. Choose a custom ID or generate a random ID with `ID.unique()`. Valid chars are a-z, A-Z, 0-9, period, hyphen, and underscore. Can't start with a special char. Max length is 36 chars.
     * @param email User email.
     * @param password User password hashed using SHA.
     * @param passwordVersion Optional SHA version used to hash password. Allowed values are: 'sha1', 'sha224', 'sha256', 'sha384', 'sha512/224', 'sha512/256', 'sha512', 'sha3-224', 'sha3-256', 'sha3-384', 'sha3-512'
     * @param name User name. Max length: 128 chars.
     * @return appwrite::Result<appwrite::models::User>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::User>> createSHAUserAsync(        std::string_view userId,         std::string_view email,         std::string_view password,         std::optional<appwrite::enums::PasswordHash> passwordVersion = std::nullopt,         std::optional<std::string_view> name = std::nullopt    ) {
                std::string path_ = std::format("/users/sha");
        
        nlohmann::json params = nlohmann::json::object();
        params["userId"] = std::string(userId);
        params["email"] = std::string(email);
        params["password"] = std::string(password);
        if (passwordVersion.has_value()) {
            params["passwordVersion"] = *passwordVersion;
        }
        if (name.has_value()) {
            params["name"] = std::string(name.value());
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::User>("POST", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Get a user by its unique ID.
     *
     * @param userId User ID.
     * @return appwrite::Result<appwrite::models::User>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::User> get(        std::string_view userId    ) {
                std::string path_ = std::format("/users/{}", userId);
        
        nlohmann::json params = nlohmann::json::object();
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::User>("GET", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Get a user by its unique ID.
     *
     * @param userId User ID.
     * @return appwrite::Result<appwrite::models::User>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::User>> getAsync(        std::string_view userId    ) {
                std::string path_ = std::format("/users/{}", userId);
        
        nlohmann::json params = nlohmann::json::object();
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::User>("GET", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Delete a user by its unique ID, thereby releasing it's ID. Since ID is
     * released and can be reused, all user-related resources like documents or
     * storage files should be deleted before user deletion. If you want to keep
     * ID reserved, use the
     * [updateStatus](https://appwrite.io/docs/server/users#usersUpdateStatus)
     * endpoint instead.
     *
     * @param userId User ID.
     * @return appwrite::Result<void>
     */
    [[nodiscard]] appwrite::Result<void> delete_(        std::string_view userId    ) {
                std::string path_ = std::format("/users/{}", userId);
        
        nlohmann::json params = nlohmann::json::object();
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callVoid("DELETE", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Delete a user by its unique ID, thereby releasing it's ID. Since ID is
     * released and can be reused, all user-related resources like documents or
     * storage files should be deleted before user deletion. If you want to keep
     * ID reserved, use the
     * [updateStatus](https://appwrite.io/docs/server/users#usersUpdateStatus)
     * endpoint instead.
     *
     * @param userId User ID.
     * @return appwrite::Result<void>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<void>> delete_Async(        std::string_view userId    ) {
                std::string path_ = std::format("/users/{}", userId);
        
        nlohmann::json params = nlohmann::json::object();
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callVoidAsync("DELETE", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Update the user email by its unique ID.
     *
     * @param userId User ID.
     * @param email User email.
     * @return appwrite::Result<appwrite::models::User>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::User> updateEmail(        std::string_view userId,         std::string_view email    ) {
                std::string path_ = std::format("/users/{}/email", userId);
        
        nlohmann::json params = nlohmann::json::object();
        params["email"] = std::string(email);
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::User>("PATCH", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Update the user email by its unique ID.
     *
     * @param userId User ID.
     * @param email User email.
     * @return appwrite::Result<appwrite::models::User>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::User>> updateEmailAsync(        std::string_view userId,         std::string_view email    ) {
                std::string path_ = std::format("/users/{}/email", userId);
        
        nlohmann::json params = nlohmann::json::object();
        params["email"] = std::string(email);
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::User>("PATCH", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Enable or disable whether a user can impersonate other users. When
     * impersonation headers are used, the request runs as the target user for API
     * behavior, while internal audit logs still attribute the action to the
     * original impersonator and store the impersonated target details only in
     * internal audit payload data.
     *
     * @param userId User ID.
     * @param impersonator Whether the user can impersonate other users. When true, the user can browse project users to choose a target and can pass impersonation headers to act as that user. Internal audit logs still attribute impersonated actions to the original impersonator and store the target user details only in internal audit payload data.
     * @return appwrite::Result<appwrite::models::User>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::User> updateImpersonator(        std::string_view userId,         bool impersonator    ) {
                std::string path_ = std::format("/users/{}/impersonator", userId);
        
        nlohmann::json params = nlohmann::json::object();
        params["impersonator"] = impersonator;
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::User>("PATCH", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Enable or disable whether a user can impersonate other users. When
     * impersonation headers are used, the request runs as the target user for API
     * behavior, while internal audit logs still attribute the action to the
     * original impersonator and store the impersonated target details only in
     * internal audit payload data.
     *
     * @param userId User ID.
     * @param impersonator Whether the user can impersonate other users. When true, the user can browse project users to choose a target and can pass impersonation headers to act as that user. Internal audit logs still attribute impersonated actions to the original impersonator and store the target user details only in internal audit payload data.
     * @return appwrite::Result<appwrite::models::User>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::User>> updateImpersonatorAsync(        std::string_view userId,         bool impersonator    ) {
                std::string path_ = std::format("/users/{}/impersonator", userId);
        
        nlohmann::json params = nlohmann::json::object();
        params["impersonator"] = impersonator;
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::User>("PATCH", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Use this endpoint to create a JSON Web Token for user by its unique ID. You
     * can use the resulting JWT to authenticate on behalf of the user. The JWT
     * secret will become invalid if the session it uses gets deleted.
     *
     * @param userId User ID.
     * @param sessionId Session ID. Use the string 'recent' to use the most recent session. Defaults to the most recent session.
     * @param duration Time in seconds before JWT expires. Default duration is 900 seconds, and maximum is 3600 seconds.
     * @return appwrite::Result<appwrite::models::Jwt>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::Jwt> createJWT(        std::string_view userId,         std::optional<std::string_view> sessionId = std::nullopt,         std::optional<int64_t> duration = 900    ) {
                std::string path_ = std::format("/users/{}/jwts", userId);
        
        nlohmann::json params = nlohmann::json::object();
        if (sessionId.has_value()) {
            params["sessionId"] = std::string(sessionId.value());
        }
        if (duration.has_value()) {
            params["duration"] = *duration;
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::Jwt>("POST", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Use this endpoint to create a JSON Web Token for user by its unique ID. You
     * can use the resulting JWT to authenticate on behalf of the user. The JWT
     * secret will become invalid if the session it uses gets deleted.
     *
     * @param userId User ID.
     * @param sessionId Session ID. Use the string 'recent' to use the most recent session. Defaults to the most recent session.
     * @param duration Time in seconds before JWT expires. Default duration is 900 seconds, and maximum is 3600 seconds.
     * @return appwrite::Result<appwrite::models::Jwt>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::Jwt>> createJWTAsync(        std::string_view userId,         std::optional<std::string_view> sessionId = std::nullopt,         std::optional<int64_t> duration = 900    ) {
                std::string path_ = std::format("/users/{}/jwts", userId);
        
        nlohmann::json params = nlohmann::json::object();
        if (sessionId.has_value()) {
            params["sessionId"] = std::string(sessionId.value());
        }
        if (duration.has_value()) {
            params["duration"] = *duration;
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::Jwt>("POST", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Update the user labels by its unique ID. 

Labels can be used to grant
     * access to resources. While teams are a way for user's to share access to a
     * resource, labels can be defined by the developer to grant access without an
     * invitation. See the [Permissions
     * docs](https://appwrite.io/docs/permissions) for more info.
     *
     * @param userId User ID.
     * @param labels Array of user labels. Replaces the previous labels. Maximum of 1000 labels are allowed, each up to 36 alphanumeric characters long.
     * @return appwrite::Result<appwrite::models::User>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::User> updateLabels(        std::string_view userId,         std::vector<std::string> labels    ) {
                std::string path_ = std::format("/users/{}/labels", userId);
        
        nlohmann::json params = nlohmann::json::object();
        params["labels"] = labels;
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::User>("PUT", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Update the user labels by its unique ID. 

Labels can be used to grant
     * access to resources. While teams are a way for user's to share access to a
     * resource, labels can be defined by the developer to grant access without an
     * invitation. See the [Permissions
     * docs](https://appwrite.io/docs/permissions) for more info.
     *
     * @param userId User ID.
     * @param labels Array of user labels. Replaces the previous labels. Maximum of 1000 labels are allowed, each up to 36 alphanumeric characters long.
     * @return appwrite::Result<appwrite::models::User>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::User>> updateLabelsAsync(        std::string_view userId,         std::vector<std::string> labels    ) {
                std::string path_ = std::format("/users/{}/labels", userId);
        
        nlohmann::json params = nlohmann::json::object();
        params["labels"] = labels;
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::User>("PUT", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Get the user activity logs list by its unique ID.
     *
     * @param userId User ID.
     * @param queries Array of query strings generated using the Query class provided by the SDK. [Learn more about queries](https://appwrite.io/docs/queries). Only supported methods are limit and offset
     * @param total When set to false, the total count returned will be 0 and will not be calculated.
     * @return appwrite::Result<appwrite::models::LogList>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::LogList> listLogs(        std::string_view userId,         std::optional<std::vector<std::string>> queries = std::vector<std::string>{},         std::optional<bool> total = true    ) {
                std::string path_ = std::format("/users/{}/logs", userId);
        
        nlohmann::json params = nlohmann::json::object();
        if (queries.has_value()) {
            params["queries"] = *queries;
        }
        if (total.has_value()) {
            params["total"] = *total;
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::LogList>("GET", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Get the user activity logs list by its unique ID.
     *
     * @param userId User ID.
     * @param queries Array of query strings generated using the Query class provided by the SDK. [Learn more about queries](https://appwrite.io/docs/queries). Only supported methods are limit and offset
     * @param total When set to false, the total count returned will be 0 and will not be calculated.
     * @return appwrite::Result<appwrite::models::LogList>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::LogList>> listLogsAsync(        std::string_view userId,         std::optional<std::vector<std::string>> queries = std::vector<std::string>{},         std::optional<bool> total = true    ) {
                std::string path_ = std::format("/users/{}/logs", userId);
        
        nlohmann::json params = nlohmann::json::object();
        if (queries.has_value()) {
            params["queries"] = *queries;
        }
        if (total.has_value()) {
            params["total"] = *total;
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::LogList>("GET", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Get the user membership list by its unique ID.
     *
     * @param userId User ID.
     * @param queries Array of query strings generated using the Query class provided by the SDK. [Learn more about queries](https://appwrite.io/docs/queries). Maximum of 100 queries are allowed, each 4096 characters long. You may filter on the following attributes: userId, teamId, invited, joined, confirm, roles
     * @param search Search term to filter your list results. Max length: 256 chars.
     * @param total When set to false, the total count returned will be 0 and will not be calculated.
     * @return appwrite::Result<appwrite::models::MembershipList>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::MembershipList> listMemberships(        std::string_view userId,         std::optional<std::vector<std::string>> queries = std::vector<std::string>{},         std::optional<std::string_view> search = std::nullopt,         std::optional<bool> total = true    ) {
                std::string path_ = std::format("/users/{}/memberships", userId);
        
        nlohmann::json params = nlohmann::json::object();
        if (queries.has_value()) {
            params["queries"] = *queries;
        }
        if (search.has_value()) {
            params["search"] = std::string(search.value());
        }
        if (total.has_value()) {
            params["total"] = *total;
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::MembershipList>("GET", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Get the user membership list by its unique ID.
     *
     * @param userId User ID.
     * @param queries Array of query strings generated using the Query class provided by the SDK. [Learn more about queries](https://appwrite.io/docs/queries). Maximum of 100 queries are allowed, each 4096 characters long. You may filter on the following attributes: userId, teamId, invited, joined, confirm, roles
     * @param search Search term to filter your list results. Max length: 256 chars.
     * @param total When set to false, the total count returned will be 0 and will not be calculated.
     * @return appwrite::Result<appwrite::models::MembershipList>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::MembershipList>> listMembershipsAsync(        std::string_view userId,         std::optional<std::vector<std::string>> queries = std::vector<std::string>{},         std::optional<std::string_view> search = std::nullopt,         std::optional<bool> total = true    ) {
                std::string path_ = std::format("/users/{}/memberships", userId);
        
        nlohmann::json params = nlohmann::json::object();
        if (queries.has_value()) {
            params["queries"] = *queries;
        }
        if (search.has_value()) {
            params["search"] = std::string(search.value());
        }
        if (total.has_value()) {
            params["total"] = *total;
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::MembershipList>("GET", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Enable or disable MFA on a user account.
     *
     * @param userId User ID.
     * @param mfa Enable or disable MFA.
     * @return appwrite::Result<appwrite::models::User>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::User> updateMfa(        std::string_view userId,         bool mfa    ) {
                std::string path_ = std::format("/users/{}/mfa", userId);
        
        nlohmann::json params = nlohmann::json::object();
        params["mfa"] = mfa;
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::User>("PATCH", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Enable or disable MFA on a user account.
     *
     * @param userId User ID.
     * @param mfa Enable or disable MFA.
     * @return appwrite::Result<appwrite::models::User>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::User>> updateMfaAsync(        std::string_view userId,         bool mfa    ) {
                std::string path_ = std::format("/users/{}/mfa", userId);
        
        nlohmann::json params = nlohmann::json::object();
        params["mfa"] = mfa;
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::User>("PATCH", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Enable or disable MFA on a user account.
     *
     * @param userId User ID.
     * @param mfa Enable or disable MFA.
     * @return appwrite::Result<appwrite::models::User>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::User> updateMFA(        std::string_view userId,         bool mfa    ) {
                std::string path_ = std::format("/users/{}/mfa", userId);
        
        nlohmann::json params = nlohmann::json::object();
        params["mfa"] = mfa;
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::User>("PATCH", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Enable or disable MFA on a user account.
     *
     * @param userId User ID.
     * @param mfa Enable or disable MFA.
     * @return appwrite::Result<appwrite::models::User>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::User>> updateMFAAsync(        std::string_view userId,         bool mfa    ) {
                std::string path_ = std::format("/users/{}/mfa", userId);
        
        nlohmann::json params = nlohmann::json::object();
        params["mfa"] = mfa;
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::User>("PATCH", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Delete an authenticator app.
     *
     * @param userId User ID.
     * @param type Type of authenticator.
     * @return appwrite::Result<void>
     */
    [[nodiscard]] appwrite::Result<void> deleteMfaAuthenticator(        std::string_view userId,         appwrite::enums::AuthenticatorType type    ) {
                std::string path_ = std::format("/users/{}/mfa/authenticators/{}", userId, appwrite::enums::toString(type));
        
        nlohmann::json params = nlohmann::json::object();
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callVoid("DELETE", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Delete an authenticator app.
     *
     * @param userId User ID.
     * @param type Type of authenticator.
     * @return appwrite::Result<void>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<void>> deleteMfaAuthenticatorAsync(        std::string_view userId,         appwrite::enums::AuthenticatorType type    ) {
                std::string path_ = std::format("/users/{}/mfa/authenticators/{}", userId, appwrite::enums::toString(type));
        
        nlohmann::json params = nlohmann::json::object();
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callVoidAsync("DELETE", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Delete an authenticator app.
     *
     * @param userId User ID.
     * @param type Type of authenticator.
     * @return appwrite::Result<void>
     */
    [[nodiscard]] appwrite::Result<void> deleteMFAAuthenticator(        std::string_view userId,         appwrite::enums::AuthenticatorType type    ) {
                std::string path_ = std::format("/users/{}/mfa/authenticators/{}", userId, appwrite::enums::toString(type));
        
        nlohmann::json params = nlohmann::json::object();
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callVoid("DELETE", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Delete an authenticator app.
     *
     * @param userId User ID.
     * @param type Type of authenticator.
     * @return appwrite::Result<void>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<void>> deleteMFAAuthenticatorAsync(        std::string_view userId,         appwrite::enums::AuthenticatorType type    ) {
                std::string path_ = std::format("/users/{}/mfa/authenticators/{}", userId, appwrite::enums::toString(type));
        
        nlohmann::json params = nlohmann::json::object();
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callVoidAsync("DELETE", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * List the factors available on the account to be used as a MFA challange.
     *
     * @param userId User ID.
     * @return appwrite::Result<appwrite::models::MfaFactors>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::MfaFactors> listMfaFactors(        std::string_view userId    ) {
                std::string path_ = std::format("/users/{}/mfa/factors", userId);
        
        nlohmann::json params = nlohmann::json::object();
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::MfaFactors>("GET", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * List the factors available on the account to be used as a MFA challange.
     *
     * @param userId User ID.
     * @return appwrite::Result<appwrite::models::MfaFactors>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::MfaFactors>> listMfaFactorsAsync(        std::string_view userId    ) {
                std::string path_ = std::format("/users/{}/mfa/factors", userId);
        
        nlohmann::json params = nlohmann::json::object();
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::MfaFactors>("GET", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * List the factors available on the account to be used as a MFA challange.
     *
     * @param userId User ID.
     * @return appwrite::Result<appwrite::models::MfaFactors>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::MfaFactors> listMFAFactors(        std::string_view userId    ) {
                std::string path_ = std::format("/users/{}/mfa/factors", userId);
        
        nlohmann::json params = nlohmann::json::object();
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::MfaFactors>("GET", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * List the factors available on the account to be used as a MFA challange.
     *
     * @param userId User ID.
     * @return appwrite::Result<appwrite::models::MfaFactors>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::MfaFactors>> listMFAFactorsAsync(        std::string_view userId    ) {
                std::string path_ = std::format("/users/{}/mfa/factors", userId);
        
        nlohmann::json params = nlohmann::json::object();
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::MfaFactors>("GET", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Get recovery codes that can be used as backup for MFA flow by User ID.
     * Before getting codes, they must be generated using
     * [createMfaRecoveryCodes](/docs/references/cloud/client-web/account#createMfaRecoveryCodes)
     * method.
     *
     * @param userId User ID.
     * @return appwrite::Result<appwrite::models::MfaRecoveryCodes>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::MfaRecoveryCodes> getMfaRecoveryCodes(        std::string_view userId    ) {
                std::string path_ = std::format("/users/{}/mfa/recovery-codes", userId);
        
        nlohmann::json params = nlohmann::json::object();
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::MfaRecoveryCodes>("GET", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Get recovery codes that can be used as backup for MFA flow by User ID.
     * Before getting codes, they must be generated using
     * [createMfaRecoveryCodes](/docs/references/cloud/client-web/account#createMfaRecoveryCodes)
     * method.
     *
     * @param userId User ID.
     * @return appwrite::Result<appwrite::models::MfaRecoveryCodes>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::MfaRecoveryCodes>> getMfaRecoveryCodesAsync(        std::string_view userId    ) {
                std::string path_ = std::format("/users/{}/mfa/recovery-codes", userId);
        
        nlohmann::json params = nlohmann::json::object();
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::MfaRecoveryCodes>("GET", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Get recovery codes that can be used as backup for MFA flow by User ID.
     * Before getting codes, they must be generated using
     * [createMfaRecoveryCodes](/docs/references/cloud/client-web/account#createMfaRecoveryCodes)
     * method.
     *
     * @param userId User ID.
     * @return appwrite::Result<appwrite::models::MfaRecoveryCodes>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::MfaRecoveryCodes> getMFARecoveryCodes(        std::string_view userId    ) {
                std::string path_ = std::format("/users/{}/mfa/recovery-codes", userId);
        
        nlohmann::json params = nlohmann::json::object();
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::MfaRecoveryCodes>("GET", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Get recovery codes that can be used as backup for MFA flow by User ID.
     * Before getting codes, they must be generated using
     * [createMfaRecoveryCodes](/docs/references/cloud/client-web/account#createMfaRecoveryCodes)
     * method.
     *
     * @param userId User ID.
     * @return appwrite::Result<appwrite::models::MfaRecoveryCodes>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::MfaRecoveryCodes>> getMFARecoveryCodesAsync(        std::string_view userId    ) {
                std::string path_ = std::format("/users/{}/mfa/recovery-codes", userId);
        
        nlohmann::json params = nlohmann::json::object();
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::MfaRecoveryCodes>("GET", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Regenerate recovery codes that can be used as backup for MFA flow by User
     * ID. Before regenerating codes, they must be first generated using
     * [createMfaRecoveryCodes](/docs/references/cloud/client-web/account#createMfaRecoveryCodes)
     * method.
     *
     * @param userId User ID.
     * @return appwrite::Result<appwrite::models::MfaRecoveryCodes>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::MfaRecoveryCodes> updateMfaRecoveryCodes(        std::string_view userId    ) {
                std::string path_ = std::format("/users/{}/mfa/recovery-codes", userId);
        
        nlohmann::json params = nlohmann::json::object();
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::MfaRecoveryCodes>("PUT", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Regenerate recovery codes that can be used as backup for MFA flow by User
     * ID. Before regenerating codes, they must be first generated using
     * [createMfaRecoveryCodes](/docs/references/cloud/client-web/account#createMfaRecoveryCodes)
     * method.
     *
     * @param userId User ID.
     * @return appwrite::Result<appwrite::models::MfaRecoveryCodes>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::MfaRecoveryCodes>> updateMfaRecoveryCodesAsync(        std::string_view userId    ) {
                std::string path_ = std::format("/users/{}/mfa/recovery-codes", userId);
        
        nlohmann::json params = nlohmann::json::object();
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::MfaRecoveryCodes>("PUT", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Regenerate recovery codes that can be used as backup for MFA flow by User
     * ID. Before regenerating codes, they must be first generated using
     * [createMfaRecoveryCodes](/docs/references/cloud/client-web/account#createMfaRecoveryCodes)
     * method.
     *
     * @param userId User ID.
     * @return appwrite::Result<appwrite::models::MfaRecoveryCodes>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::MfaRecoveryCodes> updateMFARecoveryCodes(        std::string_view userId    ) {
                std::string path_ = std::format("/users/{}/mfa/recovery-codes", userId);
        
        nlohmann::json params = nlohmann::json::object();
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::MfaRecoveryCodes>("PUT", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Regenerate recovery codes that can be used as backup for MFA flow by User
     * ID. Before regenerating codes, they must be first generated using
     * [createMfaRecoveryCodes](/docs/references/cloud/client-web/account#createMfaRecoveryCodes)
     * method.
     *
     * @param userId User ID.
     * @return appwrite::Result<appwrite::models::MfaRecoveryCodes>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::MfaRecoveryCodes>> updateMFARecoveryCodesAsync(        std::string_view userId    ) {
                std::string path_ = std::format("/users/{}/mfa/recovery-codes", userId);
        
        nlohmann::json params = nlohmann::json::object();
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::MfaRecoveryCodes>("PUT", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Generate recovery codes used as backup for MFA flow for User ID. Recovery
     * codes can be used as a MFA verification type in
     * [createMfaChallenge](/docs/references/cloud/client-web/account#createMfaChallenge)
     * method by client SDK.
     *
     * @param userId User ID.
     * @return appwrite::Result<appwrite::models::MfaRecoveryCodes>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::MfaRecoveryCodes> createMfaRecoveryCodes(        std::string_view userId    ) {
                std::string path_ = std::format("/users/{}/mfa/recovery-codes", userId);
        
        nlohmann::json params = nlohmann::json::object();
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::MfaRecoveryCodes>("PATCH", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Generate recovery codes used as backup for MFA flow for User ID. Recovery
     * codes can be used as a MFA verification type in
     * [createMfaChallenge](/docs/references/cloud/client-web/account#createMfaChallenge)
     * method by client SDK.
     *
     * @param userId User ID.
     * @return appwrite::Result<appwrite::models::MfaRecoveryCodes>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::MfaRecoveryCodes>> createMfaRecoveryCodesAsync(        std::string_view userId    ) {
                std::string path_ = std::format("/users/{}/mfa/recovery-codes", userId);
        
        nlohmann::json params = nlohmann::json::object();
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::MfaRecoveryCodes>("PATCH", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Generate recovery codes used as backup for MFA flow for User ID. Recovery
     * codes can be used as a MFA verification type in
     * [createMfaChallenge](/docs/references/cloud/client-web/account#createMfaChallenge)
     * method by client SDK.
     *
     * @param userId User ID.
     * @return appwrite::Result<appwrite::models::MfaRecoveryCodes>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::MfaRecoveryCodes> createMFARecoveryCodes(        std::string_view userId    ) {
                std::string path_ = std::format("/users/{}/mfa/recovery-codes", userId);
        
        nlohmann::json params = nlohmann::json::object();
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::MfaRecoveryCodes>("PATCH", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Generate recovery codes used as backup for MFA flow for User ID. Recovery
     * codes can be used as a MFA verification type in
     * [createMfaChallenge](/docs/references/cloud/client-web/account#createMfaChallenge)
     * method by client SDK.
     *
     * @param userId User ID.
     * @return appwrite::Result<appwrite::models::MfaRecoveryCodes>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::MfaRecoveryCodes>> createMFARecoveryCodesAsync(        std::string_view userId    ) {
                std::string path_ = std::format("/users/{}/mfa/recovery-codes", userId);
        
        nlohmann::json params = nlohmann::json::object();
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::MfaRecoveryCodes>("PATCH", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Update the user name by its unique ID.
     *
     * @param userId User ID.
     * @param name User name. Max length: 128 chars.
     * @return appwrite::Result<appwrite::models::User>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::User> updateName(        std::string_view userId,         std::string_view name    ) {
                std::string path_ = std::format("/users/{}/name", userId);
        
        nlohmann::json params = nlohmann::json::object();
        params["name"] = std::string(name);
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::User>("PATCH", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Update the user name by its unique ID.
     *
     * @param userId User ID.
     * @param name User name. Max length: 128 chars.
     * @return appwrite::Result<appwrite::models::User>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::User>> updateNameAsync(        std::string_view userId,         std::string_view name    ) {
                std::string path_ = std::format("/users/{}/name", userId);
        
        nlohmann::json params = nlohmann::json::object();
        params["name"] = std::string(name);
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::User>("PATCH", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Update the user password by its unique ID.
     *
     * @param userId User ID.
     * @param password New user password. Must be at least 8 chars.
     * @return appwrite::Result<appwrite::models::User>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::User> updatePassword(        std::string_view userId,         std::string_view password    ) {
                std::string path_ = std::format("/users/{}/password", userId);
        
        nlohmann::json params = nlohmann::json::object();
        params["password"] = std::string(password);
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::User>("PATCH", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Update the user password by its unique ID.
     *
     * @param userId User ID.
     * @param password New user password. Must be at least 8 chars.
     * @return appwrite::Result<appwrite::models::User>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::User>> updatePasswordAsync(        std::string_view userId,         std::string_view password    ) {
                std::string path_ = std::format("/users/{}/password", userId);
        
        nlohmann::json params = nlohmann::json::object();
        params["password"] = std::string(password);
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::User>("PATCH", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Update the user phone by its unique ID.
     *
     * @param userId User ID.
     * @param number User phone number.
     * @return appwrite::Result<appwrite::models::User>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::User> updatePhone(        std::string_view userId,         std::string_view number    ) {
                std::string path_ = std::format("/users/{}/phone", userId);
        
        nlohmann::json params = nlohmann::json::object();
        params["number"] = std::string(number);
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::User>("PATCH", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Update the user phone by its unique ID.
     *
     * @param userId User ID.
     * @param number User phone number.
     * @return appwrite::Result<appwrite::models::User>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::User>> updatePhoneAsync(        std::string_view userId,         std::string_view number    ) {
                std::string path_ = std::format("/users/{}/phone", userId);
        
        nlohmann::json params = nlohmann::json::object();
        params["number"] = std::string(number);
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::User>("PATCH", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Get the user preferences by its unique ID.
     *
     * @param userId User ID.
     * @return appwrite::Result<appwrite::models::Preferences>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::Preferences> getPrefs(        std::string_view userId    ) {
                std::string path_ = std::format("/users/{}/prefs", userId);
        
        nlohmann::json params = nlohmann::json::object();
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::Preferences>("GET", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Get the user preferences by its unique ID.
     *
     * @param userId User ID.
     * @return appwrite::Result<appwrite::models::Preferences>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::Preferences>> getPrefsAsync(        std::string_view userId    ) {
                std::string path_ = std::format("/users/{}/prefs", userId);
        
        nlohmann::json params = nlohmann::json::object();
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::Preferences>("GET", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Update the user preferences by its unique ID. The object you pass is stored
     * as is, and replaces any previous value. The maximum allowed prefs size is
     * 64kB and throws error if exceeded.
     *
     * @param userId User ID.
     * @param prefs Prefs key-value JSON object.
     * @return appwrite::Result<appwrite::models::Preferences>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::Preferences> updatePrefs(        std::string_view userId,         const nlohmann::json& prefs = nlohmann::json::object()    ) {
                std::string path_ = std::format("/users/{}/prefs", userId);
        
        nlohmann::json params = nlohmann::json::object();
        params["prefs"] = prefs;
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::Preferences>("PATCH", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Update the user preferences by its unique ID. The object you pass is stored
     * as is, and replaces any previous value. The maximum allowed prefs size is
     * 64kB and throws error if exceeded.
     *
     * @param userId User ID.
     * @param prefs Prefs key-value JSON object.
     * @return appwrite::Result<appwrite::models::Preferences>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::Preferences>> updatePrefsAsync(        std::string_view userId,         const nlohmann::json& prefs = nlohmann::json::object()    ) {
                std::string path_ = std::format("/users/{}/prefs", userId);
        
        nlohmann::json params = nlohmann::json::object();
        params["prefs"] = prefs;
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::Preferences>("PATCH", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Get the user sessions list by its unique ID.
     *
     * @param userId User ID.
     * @param total When set to false, the total count returned will be 0 and will not be calculated.
     * @return appwrite::Result<appwrite::models::SessionList>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::SessionList> listSessions(        std::string_view userId,         std::optional<bool> total = true    ) {
                std::string path_ = std::format("/users/{}/sessions", userId);
        
        nlohmann::json params = nlohmann::json::object();
        if (total.has_value()) {
            params["total"] = *total;
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::SessionList>("GET", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Get the user sessions list by its unique ID.
     *
     * @param userId User ID.
     * @param total When set to false, the total count returned will be 0 and will not be calculated.
     * @return appwrite::Result<appwrite::models::SessionList>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::SessionList>> listSessionsAsync(        std::string_view userId,         std::optional<bool> total = true    ) {
                std::string path_ = std::format("/users/{}/sessions", userId);
        
        nlohmann::json params = nlohmann::json::object();
        if (total.has_value()) {
            params["total"] = *total;
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::SessionList>("GET", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Creates a session for a user. Returns an immediately usable session
     * object.

If you want to generate a token for a custom authentication flow,
     * use the [POST
     * /users/{userId}/tokens](https://appwrite.io/docs/server/users#createToken)
     * endpoint.
     *
     * @param userId User ID. Choose a custom ID or generate a random ID with `ID.unique()`. Valid chars are a-z, A-Z, 0-9, period, hyphen, and underscore. Can't start with a special char. Max length is 36 chars.
     * @return appwrite::Result<appwrite::models::Session>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::Session> createSession(        std::string_view userId    ) {
                std::string path_ = std::format("/users/{}/sessions", userId);
        
        nlohmann::json params = nlohmann::json::object();
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::Session>("POST", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Creates a session for a user. Returns an immediately usable session
     * object.

If you want to generate a token for a custom authentication flow,
     * use the [POST
     * /users/{userId}/tokens](https://appwrite.io/docs/server/users#createToken)
     * endpoint.
     *
     * @param userId User ID. Choose a custom ID or generate a random ID with `ID.unique()`. Valid chars are a-z, A-Z, 0-9, period, hyphen, and underscore. Can't start with a special char. Max length is 36 chars.
     * @return appwrite::Result<appwrite::models::Session>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::Session>> createSessionAsync(        std::string_view userId    ) {
                std::string path_ = std::format("/users/{}/sessions", userId);
        
        nlohmann::json params = nlohmann::json::object();
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::Session>("POST", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Delete all user's sessions by using the user's unique ID.
     *
     * @param userId User ID.
     * @return appwrite::Result<void>
     */
    [[nodiscard]] appwrite::Result<void> deleteSessions(        std::string_view userId    ) {
                std::string path_ = std::format("/users/{}/sessions", userId);
        
        nlohmann::json params = nlohmann::json::object();
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callVoid("DELETE", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Delete all user's sessions by using the user's unique ID.
     *
     * @param userId User ID.
     * @return appwrite::Result<void>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<void>> deleteSessionsAsync(        std::string_view userId    ) {
                std::string path_ = std::format("/users/{}/sessions", userId);
        
        nlohmann::json params = nlohmann::json::object();
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callVoidAsync("DELETE", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Delete a user sessions by its unique ID.
     *
     * @param userId User ID.
     * @param sessionId Session ID.
     * @return appwrite::Result<void>
     */
    [[nodiscard]] appwrite::Result<void> deleteSession(        std::string_view userId,         std::string_view sessionId    ) {
                std::string path_ = std::format("/users/{}/sessions/{}", userId, sessionId);
        
        nlohmann::json params = nlohmann::json::object();
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callVoid("DELETE", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Delete a user sessions by its unique ID.
     *
     * @param userId User ID.
     * @param sessionId Session ID.
     * @return appwrite::Result<void>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<void>> deleteSessionAsync(        std::string_view userId,         std::string_view sessionId    ) {
                std::string path_ = std::format("/users/{}/sessions/{}", userId, sessionId);
        
        nlohmann::json params = nlohmann::json::object();
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callVoidAsync("DELETE", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Update the user status by its unique ID. Use this endpoint as an
     * alternative to deleting a user if you want to keep user's ID reserved.
     *
     * @param userId User ID.
     * @param status User Status. To activate the user pass `true` and to block the user pass `false`.
     * @return appwrite::Result<appwrite::models::User>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::User> updateStatus(        std::string_view userId,         bool status    ) {
                std::string path_ = std::format("/users/{}/status", userId);
        
        nlohmann::json params = nlohmann::json::object();
        params["status"] = status;
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::User>("PATCH", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Update the user status by its unique ID. Use this endpoint as an
     * alternative to deleting a user if you want to keep user's ID reserved.
     *
     * @param userId User ID.
     * @param status User Status. To activate the user pass `true` and to block the user pass `false`.
     * @return appwrite::Result<appwrite::models::User>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::User>> updateStatusAsync(        std::string_view userId,         bool status    ) {
                std::string path_ = std::format("/users/{}/status", userId);
        
        nlohmann::json params = nlohmann::json::object();
        params["status"] = status;
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::User>("PATCH", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * List the messaging targets that are associated with a user.
     *
     * @param userId User ID.
     * @param queries Array of query strings generated using the Query class provided by the SDK. [Learn more about queries](https://appwrite.io/docs/queries). Maximum of 100 queries are allowed, each 4096 characters long. You may filter on the following attributes: userId, providerId, identifier, providerType
     * @param total When set to false, the total count returned will be 0 and will not be calculated.
     * @return appwrite::Result<appwrite::models::TargetList>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::TargetList> listTargets(        std::string_view userId,         std::optional<std::vector<std::string>> queries = std::vector<std::string>{},         std::optional<bool> total = true    ) {
                std::string path_ = std::format("/users/{}/targets", userId);
        
        nlohmann::json params = nlohmann::json::object();
        if (queries.has_value()) {
            params["queries"] = *queries;
        }
        if (total.has_value()) {
            params["total"] = *total;
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::TargetList>("GET", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * List the messaging targets that are associated with a user.
     *
     * @param userId User ID.
     * @param queries Array of query strings generated using the Query class provided by the SDK. [Learn more about queries](https://appwrite.io/docs/queries). Maximum of 100 queries are allowed, each 4096 characters long. You may filter on the following attributes: userId, providerId, identifier, providerType
     * @param total When set to false, the total count returned will be 0 and will not be calculated.
     * @return appwrite::Result<appwrite::models::TargetList>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::TargetList>> listTargetsAsync(        std::string_view userId,         std::optional<std::vector<std::string>> queries = std::vector<std::string>{},         std::optional<bool> total = true    ) {
                std::string path_ = std::format("/users/{}/targets", userId);
        
        nlohmann::json params = nlohmann::json::object();
        if (queries.has_value()) {
            params["queries"] = *queries;
        }
        if (total.has_value()) {
            params["total"] = *total;
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::TargetList>("GET", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Create a messaging target.
     *
     * @param userId User ID.
     * @param targetId Target ID. Choose a custom ID or generate a random ID with `ID.unique()`. Valid chars are a-z, A-Z, 0-9, period, hyphen, and underscore. Can't start with a special char. Max length is 36 chars.
     * @param providerType The target provider type. Can be one of the following: `email`, `sms` or `push`.
     * @param identifier The target identifier (token, email, phone etc.)
     * @param providerId Provider ID. Message will be sent to this target from the specified provider ID. If no provider ID is set the first setup provider will be used.
     * @param name Target name. Max length: 128 chars. For example: My Awesome App Galaxy S23.
     * @return appwrite::Result<appwrite::models::Target>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::Target> createTarget(        std::string_view userId,         std::string_view targetId,         appwrite::enums::MessagingProviderType providerType,         std::string_view identifier,         std::optional<std::string_view> providerId = std::nullopt,         std::optional<std::string_view> name = std::nullopt    ) {
                std::string path_ = std::format("/users/{}/targets", userId);
        
        nlohmann::json params = nlohmann::json::object();
        params["targetId"] = std::string(targetId);
        params["providerType"] = providerType;
        params["identifier"] = std::string(identifier);
        if (providerId.has_value()) {
            params["providerId"] = std::string(providerId.value());
        }
        if (name.has_value()) {
            params["name"] = std::string(name.value());
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::Target>("POST", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Create a messaging target.
     *
     * @param userId User ID.
     * @param targetId Target ID. Choose a custom ID or generate a random ID with `ID.unique()`. Valid chars are a-z, A-Z, 0-9, period, hyphen, and underscore. Can't start with a special char. Max length is 36 chars.
     * @param providerType The target provider type. Can be one of the following: `email`, `sms` or `push`.
     * @param identifier The target identifier (token, email, phone etc.)
     * @param providerId Provider ID. Message will be sent to this target from the specified provider ID. If no provider ID is set the first setup provider will be used.
     * @param name Target name. Max length: 128 chars. For example: My Awesome App Galaxy S23.
     * @return appwrite::Result<appwrite::models::Target>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::Target>> createTargetAsync(        std::string_view userId,         std::string_view targetId,         appwrite::enums::MessagingProviderType providerType,         std::string_view identifier,         std::optional<std::string_view> providerId = std::nullopt,         std::optional<std::string_view> name = std::nullopt    ) {
                std::string path_ = std::format("/users/{}/targets", userId);
        
        nlohmann::json params = nlohmann::json::object();
        params["targetId"] = std::string(targetId);
        params["providerType"] = providerType;
        params["identifier"] = std::string(identifier);
        if (providerId.has_value()) {
            params["providerId"] = std::string(providerId.value());
        }
        if (name.has_value()) {
            params["name"] = std::string(name.value());
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::Target>("POST", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Get a user's push notification target by ID.
     *
     * @param userId User ID.
     * @param targetId Target ID.
     * @return appwrite::Result<appwrite::models::Target>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::Target> getTarget(        std::string_view userId,         std::string_view targetId    ) {
                std::string path_ = std::format("/users/{}/targets/{}", userId, targetId);
        
        nlohmann::json params = nlohmann::json::object();
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::Target>("GET", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Get a user's push notification target by ID.
     *
     * @param userId User ID.
     * @param targetId Target ID.
     * @return appwrite::Result<appwrite::models::Target>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::Target>> getTargetAsync(        std::string_view userId,         std::string_view targetId    ) {
                std::string path_ = std::format("/users/{}/targets/{}", userId, targetId);
        
        nlohmann::json params = nlohmann::json::object();
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::Target>("GET", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Update a messaging target.
     *
     * @param userId User ID.
     * @param targetId Target ID.
     * @param identifier The target identifier (token, email, phone etc.)
     * @param providerId Provider ID. Message will be sent to this target from the specified provider ID. If no provider ID is set the first setup provider will be used.
     * @param name Target name. Max length: 128 chars. For example: My Awesome App Galaxy S23.
     * @return appwrite::Result<appwrite::models::Target>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::Target> updateTarget(        std::string_view userId,         std::string_view targetId,         std::optional<std::string_view> identifier = std::nullopt,         std::optional<std::string_view> providerId = std::nullopt,         std::optional<std::string_view> name = std::nullopt    ) {
                std::string path_ = std::format("/users/{}/targets/{}", userId, targetId);
        
        nlohmann::json params = nlohmann::json::object();
        if (identifier.has_value()) {
            params["identifier"] = std::string(identifier.value());
        }
        if (providerId.has_value()) {
            params["providerId"] = std::string(providerId.value());
        }
        if (name.has_value()) {
            params["name"] = std::string(name.value());
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::Target>("PATCH", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Update a messaging target.
     *
     * @param userId User ID.
     * @param targetId Target ID.
     * @param identifier The target identifier (token, email, phone etc.)
     * @param providerId Provider ID. Message will be sent to this target from the specified provider ID. If no provider ID is set the first setup provider will be used.
     * @param name Target name. Max length: 128 chars. For example: My Awesome App Galaxy S23.
     * @return appwrite::Result<appwrite::models::Target>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::Target>> updateTargetAsync(        std::string_view userId,         std::string_view targetId,         std::optional<std::string_view> identifier = std::nullopt,         std::optional<std::string_view> providerId = std::nullopt,         std::optional<std::string_view> name = std::nullopt    ) {
                std::string path_ = std::format("/users/{}/targets/{}", userId, targetId);
        
        nlohmann::json params = nlohmann::json::object();
        if (identifier.has_value()) {
            params["identifier"] = std::string(identifier.value());
        }
        if (providerId.has_value()) {
            params["providerId"] = std::string(providerId.value());
        }
        if (name.has_value()) {
            params["name"] = std::string(name.value());
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::Target>("PATCH", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Delete a messaging target.
     *
     * @param userId User ID.
     * @param targetId Target ID.
     * @return appwrite::Result<void>
     */
    [[nodiscard]] appwrite::Result<void> deleteTarget(        std::string_view userId,         std::string_view targetId    ) {
                std::string path_ = std::format("/users/{}/targets/{}", userId, targetId);
        
        nlohmann::json params = nlohmann::json::object();
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callVoid("DELETE", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Delete a messaging target.
     *
     * @param userId User ID.
     * @param targetId Target ID.
     * @return appwrite::Result<void>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<void>> deleteTargetAsync(        std::string_view userId,         std::string_view targetId    ) {
                std::string path_ = std::format("/users/{}/targets/{}", userId, targetId);
        
        nlohmann::json params = nlohmann::json::object();
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callVoidAsync("DELETE", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Returns a token with a secret key for creating a session. Use the user ID
     * and secret and submit a request to the [PUT
     * /account/sessions/token](https://appwrite.io/docs/references/cloud/client-web/account#createSession)
     * endpoint to complete the login process.
     *
     * @param userId User ID.
     * @param length Token length in characters. The default length is 6 characters
     * @param expire Token expiration period in seconds. The default expiration is 15 minutes.
     * @return appwrite::Result<appwrite::models::Token>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::Token> createToken(        std::string_view userId,         std::optional<int64_t> length = 6,         std::optional<int64_t> expire = 900    ) {
                std::string path_ = std::format("/users/{}/tokens", userId);
        
        nlohmann::json params = nlohmann::json::object();
        if (length.has_value()) {
            params["length"] = *length;
        }
        if (expire.has_value()) {
            params["expire"] = *expire;
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::Token>("POST", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Returns a token with a secret key for creating a session. Use the user ID
     * and secret and submit a request to the [PUT
     * /account/sessions/token](https://appwrite.io/docs/references/cloud/client-web/account#createSession)
     * endpoint to complete the login process.
     *
     * @param userId User ID.
     * @param length Token length in characters. The default length is 6 characters
     * @param expire Token expiration period in seconds. The default expiration is 15 minutes.
     * @return appwrite::Result<appwrite::models::Token>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::Token>> createTokenAsync(        std::string_view userId,         std::optional<int64_t> length = 6,         std::optional<int64_t> expire = 900    ) {
                std::string path_ = std::format("/users/{}/tokens", userId);
        
        nlohmann::json params = nlohmann::json::object();
        if (length.has_value()) {
            params["length"] = *length;
        }
        if (expire.has_value()) {
            params["expire"] = *expire;
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::Token>("POST", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Update the user email verification status by its unique ID.
     *
     * @param userId User ID.
     * @param emailVerification User email verification status.
     * @return appwrite::Result<appwrite::models::User>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::User> updateEmailVerification(        std::string_view userId,         bool emailVerification    ) {
                std::string path_ = std::format("/users/{}/verification", userId);
        
        nlohmann::json params = nlohmann::json::object();
        params["emailVerification"] = emailVerification;
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::User>("PATCH", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Update the user email verification status by its unique ID.
     *
     * @param userId User ID.
     * @param emailVerification User email verification status.
     * @return appwrite::Result<appwrite::models::User>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::User>> updateEmailVerificationAsync(        std::string_view userId,         bool emailVerification    ) {
                std::string path_ = std::format("/users/{}/verification", userId);
        
        nlohmann::json params = nlohmann::json::object();
        params["emailVerification"] = emailVerification;
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::User>("PATCH", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Update the user phone verification status by its unique ID.
     *
     * @param userId User ID.
     * @param phoneVerification User phone verification status.
     * @return appwrite::Result<appwrite::models::User>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::User> updatePhoneVerification(        std::string_view userId,         bool phoneVerification    ) {
                std::string path_ = std::format("/users/{}/verification/phone", userId);
        
        nlohmann::json params = nlohmann::json::object();
        params["phoneVerification"] = phoneVerification;
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::User>("PATCH", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Update the user phone verification status by its unique ID.
     *
     * @param userId User ID.
     * @param phoneVerification User phone verification status.
     * @return appwrite::Result<appwrite::models::User>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::User>> updatePhoneVerificationAsync(        std::string_view userId,         bool phoneVerification    ) {
                std::string path_ = std::format("/users/{}/verification/phone", userId);
        
        nlohmann::json params = nlohmann::json::object();
        params["phoneVerification"] = phoneVerification;
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::User>("PATCH", path_, std::move(local_headers_), std::move(params));
    }
};

/**
 * @brief 
 */
class Webhooks : public Service {
public:
    explicit Webhooks(Client& client) : Service(client) {}

    /**
     * Get a list of all webhooks belonging to the project. You can use the query
     * params to filter your results.
     *
     * @param queries Array of query strings generated using the Query class provided by the SDK. [Learn more about queries](https://appwrite.io/docs/queries). Maximum of 100 queries are allowed, each 4096 characters long. You may filter on the following attributes: name, url, authUsername, tls, events, enabled, logs, attempts
     * @param total When set to false, the total count returned will be 0 and will not be calculated.
     * @return appwrite::Result<appwrite::models::WebhookList>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::WebhookList> list(        std::optional<std::vector<std::string>> queries = std::vector<std::string>{},         std::optional<bool> total = true    ) {
                std::string path_ = std::format("/webhooks");
        
        nlohmann::json params = nlohmann::json::object();
        if (queries.has_value()) {
            params["queries"] = *queries;
        }
        if (total.has_value()) {
            params["total"] = *total;
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::WebhookList>("GET", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Get a list of all webhooks belonging to the project. You can use the query
     * params to filter your results.
     *
     * @param queries Array of query strings generated using the Query class provided by the SDK. [Learn more about queries](https://appwrite.io/docs/queries). Maximum of 100 queries are allowed, each 4096 characters long. You may filter on the following attributes: name, url, authUsername, tls, events, enabled, logs, attempts
     * @param total When set to false, the total count returned will be 0 and will not be calculated.
     * @return appwrite::Result<appwrite::models::WebhookList>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::WebhookList>> listAsync(        std::optional<std::vector<std::string>> queries = std::vector<std::string>{},         std::optional<bool> total = true    ) {
                std::string path_ = std::format("/webhooks");
        
        nlohmann::json params = nlohmann::json::object();
        if (queries.has_value()) {
            params["queries"] = *queries;
        }
        if (total.has_value()) {
            params["total"] = *total;
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::WebhookList>("GET", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Create a new webhook. Use this endpoint to configure a URL that will
     * receive events from Appwrite when specific events occur.
     *
     * @param webhookId Webhook ID. Choose a custom ID or generate a random ID with `ID.unique()`. Valid chars are a-z, A-Z, 0-9, period, hyphen, and underscore. Can't start with a special char. Max length is 36 chars.
     * @param url Webhook URL.
     * @param name Webhook name. Max length: 128 chars.
     * @param events Events list. Maximum of 100 events are allowed.
     * @param enabled Enable or disable a webhook.
     * @param tls Certificate verification, false for disabled or true for enabled.
     * @param authUsername Webhook HTTP user. Max length: 256 chars.
     * @param authPassword Webhook HTTP password. Max length: 256 chars.
     * @param secret Webhook secret key. If not provided, a new key will be generated automatically. Key must be at least 8 characters long, and at max 256 characters.
     * @return appwrite::Result<appwrite::models::Webhook>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::Webhook> create(        std::string_view webhookId,         std::string_view url,         std::string_view name,         std::vector<std::string> events,         std::optional<bool> enabled = true,         std::optional<bool> tls = false,         std::optional<std::string_view> authUsername = std::nullopt,         std::optional<std::string_view> authPassword = std::nullopt,         std::optional<std::string_view> secret = std::nullopt    ) {
                std::string path_ = std::format("/webhooks");
        
        nlohmann::json params = nlohmann::json::object();
        params["webhookId"] = std::string(webhookId);
        params["url"] = std::string(url);
        params["name"] = std::string(name);
        params["events"] = events;
        if (enabled.has_value()) {
            params["enabled"] = *enabled;
        }
        if (tls.has_value()) {
            params["tls"] = *tls;
        }
        if (authUsername.has_value()) {
            params["authUsername"] = std::string(authUsername.value());
        }
        if (authPassword.has_value()) {
            params["authPassword"] = std::string(authPassword.value());
        }
        if (secret.has_value()) {
            params["secret"] = std::string(secret.value());
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::Webhook>("POST", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Create a new webhook. Use this endpoint to configure a URL that will
     * receive events from Appwrite when specific events occur.
     *
     * @param webhookId Webhook ID. Choose a custom ID or generate a random ID with `ID.unique()`. Valid chars are a-z, A-Z, 0-9, period, hyphen, and underscore. Can't start with a special char. Max length is 36 chars.
     * @param url Webhook URL.
     * @param name Webhook name. Max length: 128 chars.
     * @param events Events list. Maximum of 100 events are allowed.
     * @param enabled Enable or disable a webhook.
     * @param tls Certificate verification, false for disabled or true for enabled.
     * @param authUsername Webhook HTTP user. Max length: 256 chars.
     * @param authPassword Webhook HTTP password. Max length: 256 chars.
     * @param secret Webhook secret key. If not provided, a new key will be generated automatically. Key must be at least 8 characters long, and at max 256 characters.
     * @return appwrite::Result<appwrite::models::Webhook>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::Webhook>> createAsync(        std::string_view webhookId,         std::string_view url,         std::string_view name,         std::vector<std::string> events,         std::optional<bool> enabled = true,         std::optional<bool> tls = false,         std::optional<std::string_view> authUsername = std::nullopt,         std::optional<std::string_view> authPassword = std::nullopt,         std::optional<std::string_view> secret = std::nullopt    ) {
                std::string path_ = std::format("/webhooks");
        
        nlohmann::json params = nlohmann::json::object();
        params["webhookId"] = std::string(webhookId);
        params["url"] = std::string(url);
        params["name"] = std::string(name);
        params["events"] = events;
        if (enabled.has_value()) {
            params["enabled"] = *enabled;
        }
        if (tls.has_value()) {
            params["tls"] = *tls;
        }
        if (authUsername.has_value()) {
            params["authUsername"] = std::string(authUsername.value());
        }
        if (authPassword.has_value()) {
            params["authPassword"] = std::string(authPassword.value());
        }
        if (secret.has_value()) {
            params["secret"] = std::string(secret.value());
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::Webhook>("POST", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Get a webhook by its unique ID. This endpoint returns details about a
     * specific webhook configured for a project.
     *
     * @param webhookId Webhook ID.
     * @return appwrite::Result<appwrite::models::Webhook>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::Webhook> get(        std::string_view webhookId    ) {
                std::string path_ = std::format("/webhooks/{}", webhookId);
        
        nlohmann::json params = nlohmann::json::object();
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::Webhook>("GET", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Get a webhook by its unique ID. This endpoint returns details about a
     * specific webhook configured for a project.
     *
     * @param webhookId Webhook ID.
     * @return appwrite::Result<appwrite::models::Webhook>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::Webhook>> getAsync(        std::string_view webhookId    ) {
                std::string path_ = std::format("/webhooks/{}", webhookId);
        
        nlohmann::json params = nlohmann::json::object();
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::Webhook>("GET", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Update a webhook by its unique ID. Use this endpoint to update the URL,
     * events, or status of an existing webhook.
     *
     * @param webhookId Webhook ID.
     * @param name Webhook name. Max length: 128 chars.
     * @param url Webhook URL.
     * @param events Events list. Maximum of 100 events are allowed.
     * @param enabled Enable or disable a webhook.
     * @param tls Certificate verification, false for disabled or true for enabled.
     * @param authUsername Webhook HTTP user. Max length: 256 chars.
     * @param authPassword Webhook HTTP password. Max length: 256 chars.
     * @return appwrite::Result<appwrite::models::Webhook>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::Webhook> update(        std::string_view webhookId,         std::string_view name,         std::string_view url,         std::vector<std::string> events,         std::optional<bool> enabled = true,         std::optional<bool> tls = false,         std::optional<std::string_view> authUsername = std::nullopt,         std::optional<std::string_view> authPassword = std::nullopt    ) {
                std::string path_ = std::format("/webhooks/{}", webhookId);
        
        nlohmann::json params = nlohmann::json::object();
        params["name"] = std::string(name);
        params["url"] = std::string(url);
        params["events"] = events;
        if (enabled.has_value()) {
            params["enabled"] = *enabled;
        }
        if (tls.has_value()) {
            params["tls"] = *tls;
        }
        if (authUsername.has_value()) {
            params["authUsername"] = std::string(authUsername.value());
        }
        if (authPassword.has_value()) {
            params["authPassword"] = std::string(authPassword.value());
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::Webhook>("PUT", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Update a webhook by its unique ID. Use this endpoint to update the URL,
     * events, or status of an existing webhook.
     *
     * @param webhookId Webhook ID.
     * @param name Webhook name. Max length: 128 chars.
     * @param url Webhook URL.
     * @param events Events list. Maximum of 100 events are allowed.
     * @param enabled Enable or disable a webhook.
     * @param tls Certificate verification, false for disabled or true for enabled.
     * @param authUsername Webhook HTTP user. Max length: 256 chars.
     * @param authPassword Webhook HTTP password. Max length: 256 chars.
     * @return appwrite::Result<appwrite::models::Webhook>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::Webhook>> updateAsync(        std::string_view webhookId,         std::string_view name,         std::string_view url,         std::vector<std::string> events,         std::optional<bool> enabled = true,         std::optional<bool> tls = false,         std::optional<std::string_view> authUsername = std::nullopt,         std::optional<std::string_view> authPassword = std::nullopt    ) {
                std::string path_ = std::format("/webhooks/{}", webhookId);
        
        nlohmann::json params = nlohmann::json::object();
        params["name"] = std::string(name);
        params["url"] = std::string(url);
        params["events"] = events;
        if (enabled.has_value()) {
            params["enabled"] = *enabled;
        }
        if (tls.has_value()) {
            params["tls"] = *tls;
        }
        if (authUsername.has_value()) {
            params["authUsername"] = std::string(authUsername.value());
        }
        if (authPassword.has_value()) {
            params["authPassword"] = std::string(authPassword.value());
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::Webhook>("PUT", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Delete a webhook by its unique ID. Once deleted, the webhook will no longer
     * receive project events.
     *
     * @param webhookId Webhook ID.
     * @return appwrite::Result<void>
     */
    [[nodiscard]] appwrite::Result<void> delete_(        std::string_view webhookId    ) {
                std::string path_ = std::format("/webhooks/{}", webhookId);
        
        nlohmann::json params = nlohmann::json::object();
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callVoid("DELETE", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Delete a webhook by its unique ID. Once deleted, the webhook will no longer
     * receive project events.
     *
     * @param webhookId Webhook ID.
     * @return appwrite::Result<void>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<void>> delete_Async(        std::string_view webhookId    ) {
                std::string path_ = std::format("/webhooks/{}", webhookId);
        
        nlohmann::json params = nlohmann::json::object();
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callVoidAsync("DELETE", path_, std::move(local_headers_), std::move(params));
    }
    /**
     * Update the webhook signing key. This endpoint can be used to regenerate the
     * signing key used to sign and validate payload deliveries for a specific
     * webhook.
     *
     * @param webhookId Webhook ID.
     * @param secret Webhook secret key. If not provided, a new key will be generated automatically. Key must be at least 8 characters long, and at max 256 characters.
     * @return appwrite::Result<appwrite::models::Webhook>
     */
    [[nodiscard]] appwrite::Result<appwrite::models::Webhook> updateSecret(        std::string_view webhookId,         std::optional<std::string_view> secret = std::nullopt    ) {
                std::string path_ = std::format("/webhooks/{}/secret", webhookId);
        
        nlohmann::json params = nlohmann::json::object();
        if (secret.has_value()) {
            params["secret"] = std::string(secret.value());
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.call<appwrite::models::Webhook>("PATCH", path_, std::move(local_headers_), std::move(params));
    }

    /**
     * Update the webhook signing key. This endpoint can be used to regenerate the
     * signing key used to sign and validate payload deliveries for a specific
     * webhook.
     *
     * @param webhookId Webhook ID.
     * @param secret Webhook secret key. If not provided, a new key will be generated automatically. Key must be at least 8 characters long, and at max 256 characters.
     * @return appwrite::Result<appwrite::models::Webhook>
     */
    [[nodiscard]] appwrite::Task<appwrite::Result<appwrite::models::Webhook>> updateSecretAsync(        std::string_view webhookId,         std::optional<std::string_view> secret = std::nullopt    ) {
                std::string path_ = std::format("/webhooks/{}/secret", webhookId);
        
        nlohmann::json params = nlohmann::json::object();
        if (secret.has_value()) {
            params["secret"] = std::string(secret.value());
        }
        
        std::unordered_map<std::string, std::string> local_headers_;
        
                return client_.callAsync<appwrite::models::Webhook>("PATCH", path_, std::move(local_headers_), std::move(params));
    }
};


} // namespace services

/**
 * @brief Response payload received from the Appwrite Realtime service.
 */
struct RealtimeResponse {
    std::string event;
    std::string timestamp;
    std::vector<std::string> channels;
    nlohmann::json data;
};

namespace services {

/**
 * @brief Realtime Service for subscribing to Appwrite events.
 *
 * @note WebSocket support requires a SocketBackend implementation.
 *       Enable by defining APPWRITE_ENABLE_REALTIME and providing
 *       a SocketBackend implementation before including this header.
 */
#ifdef APPWRITE_ENABLE_REALTIME

class Realtime : public Service {
public:
    using MessageCallback = std::function<void(const RealtimeResponse&)>;

    struct Subscription {
        std::vector<std::string> channels;
        MessageCallback callback;
        std::string id;
        void unsubscribe() { if (onUnsubscribe) onUnsubscribe(id); }
    private:
        friend class Realtime;
        std::function<void(const std::string&)> onUnsubscribe;
    };

    explicit Realtime(Client& client) : Service(client) {}

    Subscription subscribe(std::vector<std::string> channels, MessageCallback callback) {
        std::lock_guard<std::mutex> lock(mutex_);
        static uint64_t subIdCounter = 0;
        std::string subId = std::to_string(++subIdCounter);

        Subscription sub;
        sub.id = subId;
        sub.channels = channels;
        sub.callback = std::move(callback);
        sub.onUnsubscribe = [this](const std::string& id) { this->unsubscribe(id); };

        subscriptions_[subId] = sub;
        refresh();
        return sub;
    }

private:
    std::mutex mutex_;
    std::unordered_map<std::string, Subscription> subscriptions_;
    std::shared_ptr<SocketBackend> socket_;

    void unsubscribe(const std::string& subId) {
        std::lock_guard<std::mutex> lock(mutex_);
        subscriptions_.erase(subId);
        refresh();
    }

    void refresh() {
        if (subscriptions_.empty()) {
            if (socket_) { socket_->close(); socket_.reset(); }
            return;
        }

        std::vector<std::string> allChannels;
        for (const auto& [id, sub] : subscriptions_) {
            for (const auto& chan : sub.channels) {
                if (std::find(allChannels.begin(), allChannels.end(), chan) == allChannels.end()) {
                    allChannels.push_back(chan);
                }
            }
        }

        if (!socket_) {
            socket_ = client_.createSocket();
            socket_->onMessage([this](const std::string& msg) { this->handleMessage(msg); });
            socket_->connect(client_.getRealtimeEndpoint(), client_.getProject());
        }
        socket_->subscribe(allChannels);
    }

    void handleMessage(const std::string& msg) {
        try {
            auto j = nlohmann::json::parse(msg);
            RealtimeResponse resp;
            resp.event = j.value("event", "");
            resp.timestamp = j.value("timestamp", "");
            resp.data = j.value("data", nlohmann::json::object());
            if (j.contains("channels")) {
                for (auto& c : j["channels"]) resp.channels.push_back(c.get<std::string>());
            }

            std::lock_guard<std::mutex> lock(mutex_);
            for (const auto& [id, sub] : subscriptions_) {
                for (const auto& subChan : sub.channels) {
                    if (std::find(resp.channels.begin(), resp.channels.end(), subChan) != resp.channels.end()) {
                        sub.callback(resp);
                        break;
                    }
                }
            }
        } catch (...) {}
    }
};

#else

class Realtime : public Service {
public:
    using MessageCallback = std::function<void(const RealtimeResponse&)>;

    struct Subscription {
        std::string id;
        std::vector<std::string> channels;
        void unsubscribe() {}
    };

    explicit Realtime(Client& client) : Service(client) {}

    template <typename T = void>
    Subscription subscribe(
        std::vector<std::string> /*channels*/,
        MessageCallback /*callback*/
    ) {
        static_assert(
            !std::is_same_v<T, void>,
            "Realtime requires APPWRITE_ENABLE_REALTIME. "
            "Define it and provide a SocketBackend before including services.hpp."
        );
        return {};
    }
};

#endif // APPWRITE_ENABLE_REALTIME

} // namespace services
} // namespace appwrite
