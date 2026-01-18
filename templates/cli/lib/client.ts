import os from "os";
import { fetch, FormData, Agent } from "undici";
import JSONbig from "json-bigint";
import { AppwriteException } from "@appwrite.io/console";
import { globalConfig } from "./config.js";
import chalk from "chalk";
import type {
  Headers,
  RequestParams,
  ResponseType,
  FileUpload,
} from "./types.js";
import {
  DEFAULT_ENDPOINT,
  SDK_NAME,
  SDK_PLATFORM,
  SDK_LANGUAGE,
  SDK_VERSION,
  SDK_TITLE,
} from "./constants.js";

const JSONBigInt = JSONbig({ useNativeBigInt: true });

class Client {
  private endpoint: string;
  private headers: Headers;
  private selfSigned: boolean;

  constructor() {
    this.endpoint = DEFAULT_ENDPOINT;
    this.selfSigned = false;
    this.headers = {
      "content-type": "",
      "x-sdk-name": SDK_NAME,
      "x-sdk-platform": SDK_PLATFORM,
      "x-sdk-language": SDK_LANGUAGE,
      "x-sdk-version": SDK_VERSION,
      "user-agent": `${SDK_TITLE}CLI/${SDK_VERSION} (${os.type()} ${os.version()}; ${os.arch()})`,
      "X-Appwrite-Response-Format": "1.8.1",
    };
  }

  /**
   * Set Cookie
   *
   * Your cookie
   *
   * @param {string} cookie
   *
   * @return self
   */
  setCookie(cookie: string): this {
    this.addHeader("cookie", cookie);
    return this;
  }

  /**
   * Set Project
   *
   * Your project ID
   *
   * @param {string} project
   *
   * @return self
   */
  setProject(project: string): this {
    this.addHeader("X-Appwrite-Project", project);
    return this;
  }

  /**
   * Set Key
   *
   * Your secret API key
   *
   * @param {string} key
   *
   * @return self
   */
  setKey(key: string): this {
    this.addHeader("X-Appwrite-Key", key);
    return this;
  }

  /**
   * Set JWT
   *
   * Your secret JSON Web Token
   *
   * @param {string} jwt
   *
   * @return self
   */
  setJWT(jwt: string): this {
    this.addHeader("X-Appwrite-JWT", jwt);
    return this;
  }

  /**
   * Set Locale
   *
   * @param {string} locale
   *
   * @return self
   */
  setLocale(locale: string): this {
    this.addHeader("X-Appwrite-Locale", locale);
    return this;
  }

  /**
   * Set Mode
   *
   * @param {string} mode
   *
   * @return self
   */
  setMode(mode: string): this {
    this.addHeader("X-Appwrite-Mode", mode);
    return this;
  }

  /**
   * Set self signed.
   *
   * @param {bool} status
   *
   * @return this
   */
  setSelfSigned(status: boolean): this {
    this.selfSigned = status;
    return this;
  }

  /**
   * Set endpoint.
   *
   * @param {string} endpoint
   *
   * @return this
   */
  setEndpoint(endpoint: string): this {
    if (!endpoint.startsWith("http://") && !endpoint.startsWith("https://")) {
      throw new AppwriteException("Invalid endpoint URL: " + endpoint);
    }

    this.endpoint = endpoint;
    return this;
  }

  /**
   * @param {string} key
   * @param {string} value
   */
  addHeader(key: string, value: string): this {
    this.headers[key.toLowerCase()] = value;
    return this;
  }

  async call<T = unknown>(
    method: string,
    path: string = "",
    headers: Headers = {},
    params: RequestParams = {},
    responseType: ResponseType = "json",
  ): Promise<T> {
    const mergedHeaders: Headers = { ...this.headers, ...headers };
    const url = new URL(this.endpoint + path);

    let body: FormData | string | undefined = undefined;

    if (method.toUpperCase() === "GET") {
      url.search = new URLSearchParams(
        Client.flatten(params) as Record<string, string>,
      ).toString();
    } else if (
      mergedHeaders["content-type"]
        ?.toLowerCase()
        .startsWith("multipart/form-data")
    ) {
      delete mergedHeaders["content-type"];
      const formData = new FormData();

      const flatParams = Client.flatten(params);

      for (const [key, value] of Object.entries(flatParams)) {
        if (
          value &&
          typeof value === "object" &&
          "type" in value &&
          value.type === "file"
        ) {
          const fileUpload = value as FileUpload;
          formData.append(key, fileUpload.file as any, fileUpload.filename);
        } else {
          formData.append(key, value as string);
        }
      }

      body = formData;
    } else {
      body = JSONBigInt.stringify(params);
    }

    let response: Awaited<ReturnType<typeof fetch>> | undefined = undefined;
    try {
      response = await fetch(url.toString(), {
        method: method.toUpperCase(),
        headers: mergedHeaders,
        body,
        dispatcher: new Agent({
          connect: {
            rejectUnauthorized: !this.selfSigned,
          },
        }),
      });
    } catch (error) {
      throw new AppwriteException((error as Error).message);
    }

    if (response.status >= 400) {
      const text = await response.text();
      let json: { message?: string; code?: number; type?: string } | undefined =
        undefined;
      try {
        json = JSON.parse(text);
      } catch (error) {
        throw new AppwriteException(text, response.status, "", text);
      }

      if (
        path !== "/account" &&
        json.code === 401 &&
        json.type === "user_more_factors_required"
      ) {
        console.log(
          `${chalk.cyan.bold("ℹ Info")} ${chalk.cyan("Unusable account found, removing...")}`,
        );

        const current = globalConfig.getCurrentSession();
        globalConfig.setCurrentSession("");
        globalConfig.removeSession(current);
      }
      throw new AppwriteException(
        json.message || text,
        json.code,
        json.type,
        text,
      );
    }

    if (responseType === "arraybuffer") {
      const data = await response.arrayBuffer();
      return data as T;
    }

    const cookies = response.headers.getSetCookie();
    if (cookies && cookies.length > 0) {
      for (const cookie of cookies) {
        if (cookie.startsWith("a_session_console=")) {
          globalConfig.setCookie(cookie);
        }
      }
    }

    const text = await response.text();
    let json: T | undefined = undefined;
    try {
      json = JSONBigInt.parse(text);
    } catch (error) {
      return text as T;
    }
    return json as T;
  }

  static flatten(
    data: RequestParams,
    prefix: string = "",
  ): Record<string, unknown> {
    let output: Record<string, unknown> = {};

    for (const key in data) {
      const value = data[key];
      const finalKey = prefix ? prefix + "[" + key + "]" : key;

      if (Array.isArray(value)) {
        output = {
          ...output,
          ...Client.flatten(value as unknown as RequestParams, finalKey),
        };
      } else {
        output[finalKey] = value;
      }
    }

    return output;
  }
}

export default Client;
