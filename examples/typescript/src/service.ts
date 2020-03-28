import { Client } from "./client";

export abstract class Service {
    
    client: Client;

    /**
     * @param client
     */
    constructor(client: Client) {
        this.client = client;
    }
}