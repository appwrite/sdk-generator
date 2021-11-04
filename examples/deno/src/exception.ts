export class AppwriteException {
    message: String;
    code: Number;
    response: any;

    constructor(message: String, code: Number = 0, response: any = "") {
        this.message = message;
        this.code = code;
        this.response = response;
    }

}