class AppwriteException extends Error {
  constructor(message, code, response) {
    super(message);
    this.code = code;
    this.response = response;
  }
}

module.exports = AppwriteException;