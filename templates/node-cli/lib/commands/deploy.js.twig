const inquirer = require("inquirer");
const { Command } = require("commander");
const { localConfig } = require("../config");
const { questionsDeployFunctions, questionsGetEntrypoint, questionsDeployCollections } = require("../questions");
const { actionRunner, success, log, error, commandDescriptions } = require("../parser");
const { functionsGet, functionsCreate, functionsCreateTag, functionsUpdateTag } = require('./functions');
const {
    databaseCreateBooleanAttribute,
    databaseGetCollection,
    databaseCreateCollection,
    databaseCreateStringAttribute,
    databaseCreateIntegerAttribute,
    databaseCreateFloatAttribute,
    databaseCreateEmailAttribute,
    databaseCreateIndex,
    databaseCreateUrlAttribute,
    databaseCreateIpAttribute,
    databaseCreateEnumAttribute,
    databaseDeleteAttribute
} = require("./database");

const deploy = new Command("deploy")
    .description(commandDescriptions['deploy'])
    .option("--all", "Flag to deploy collections and functions")
    .action(actionRunner(async ({ all }, command) => {
        if (all == undefined) {
            command.help()
        }

        try {
            await deployFunction();
        } catch (e) {
            error(e.message);
        }
        await deployCollection()
    }));

const deployFunction = async () => {
    let response = {};

    let answers = await inquirer.prompt(questionsDeployFunctions)
    let functions = answers.functions

    for (let func of functions) {
        log(`Deploying function ${func.name} ( ${func['$id']} )`)

        try {
            await functionsGet({
                functionId: func['$id'],
                parseOutput: false,
            })
        } catch (e) {
            if (e.code == 404) {
                log(`Function ${func.name} ( ${func['$id']} ) does not exist in the project. Creating ... `);
                response = await functionsCreate({
                    functionId: 'unique()',
                    name: func.name,
                    runtime: func.runtime,
                    parseOutput: false
                })

                localConfig.updateFunction(func['$id'], {
                    "$id": response['$id'],
                });

                func["$id"] = response['$id'];
                log(`Function ${func.name} created.`);
            } else {
                throw e;
            }
        }

        // Create tag
        if (!func.entrypoint) {
            answers = await inquirer.prompt(questionsGetEntrypoint)
            func.entrypoint = answers.entrypoint;
            localConfig.updateFunction(func['$id'], func);
        }

        try {
            response = await functionsCreateTag({
                functionId: func['$id'],
                entrypoint: func.entrypoint,
                code: func.path,
                automaticDeploy: true,
                parseOutput: false
            })

            let tag = response['$id'];

            await functionsUpdateTag({
                functionId: func['$id'],
                tag,
                parseOutput: false
            })

            success(`Deployed ${func.name} ( ${func['$id']} )`);

        } catch (e) {
            switch (e.code) {
                case 'ENOENT':
                    error(`Function ${func.name} ( ${func['$id']} ) not found in the current directory. Skipping ...`);
                    break;
                default:
                    throw e;
            }
        }
    }
}

const createAttribute = async (collectionId, attribute) => {
    switch (attribute.type) {
        case 'string':
            switch (attribute.format) {
                case 'email':
                    return await databaseCreateEmailAttribute({
                        collectionId,
                        key: attribute.key,
                        required: attribute.required,
                        xdefault: attribute.default,
                        array: attribute.array,
                        parseOutput: false
                    })
                case 'url':
                    return await databaseCreateUrlAttribute({
                        collectionId,
                        key: attribute.key,
                        required: attribute.required,
                        xdefault: attribute.default,
                        array: attribute.array,
                        parseOutput: false
                    })
                case 'ip':
                    return await databaseCreateIpAttribute({
                        collectionId,
                        key: attribute.key,
                        required: attribute.required,
                        xdefault: attribute.default,
                        array: attribute.array,
                        parseOutput: false
                    })
                case 'enum':
                    return await databaseCreateEnumAttribute({
                        collectionId,
                        key: attribute.key,
                        elements: attribute.elements,
                        required: attribute.required,
                        xdefault: attribute.default,
                        array: attribute.array,
                        parseOutput: false
                    })
                default:
                    return await databaseCreateStringAttribute({
                        collectionId,
                        key: attribute.key,
                        size: attribute.size,
                        required: attribute.required,
                        xdefault: attribute.default,
                        array: attribute.array,
                        parseOutput: false
                    })

            }
        case 'integer':
            return await databaseCreateIntegerAttribute({
                collectionId,
                key: attribute.key,
                required: attribute.required,
                min: attribute.min,
                max: attribute.max,
                xdefault: attribute.default,
                array: attribute.array,
                parseOutput: false
            })
        case 'double':
            return databaseCreateFloatAttribute({
                collectionId,
                key: attribute.key,
                required: attribute.required,
                min: attribute.min,
                max: attribute.max,
                xdefault: attribute.default,
                array: attribute.array,
                parseOutput: false
            })
        case 'boolean':
            return databaseCreateBooleanAttribute({
                collectionId,
                key: attribute.key,
                required: attribute.required,
                xdefault: attribute.default,
                array: attribute.array,
                parseOutput: false
            })
    }
}

const deployCollection = async () => {
    let response = {};
    let answers = await inquirer.prompt(questionsDeployCollections[0])
    let collections = answers.collections

    for (let collection of collections) {
        log(`Deploying collection ${collection.name} ( ${collection['$id']} )`)
        try {
            response = await databaseGetCollection({
                collectionId: collection['$id'],
                parseOutput: false,
            })
            log(`Collection ${collection.name} ( ${collection['$id']} ) already exists.`);

            answers = await inquirer.prompt(questionsDeployCollections[1])
            if (answers.override !== "YES") {
                log(`Received "${answers.override}". Skipping ${collection.name} ( ${collection['$id']} )`);
                continue;
            }

            log(`Updating attributes ... `);
            collection.attributes.forEach(async attribute => {
                await databaseDeleteAttribute({
                    collectionId: collection['$id'],
                    key: attribute.key,
                    parseOutput: false
                })
            });

            await new Promise(resolve => setTimeout(resolve, 3000));

            collection.attributes.forEach(async attribute => {
                await createAttribute(collection['$id'], attribute);
            });

            await new Promise(resolve => setTimeout(resolve, 3000));

            log(`Creating indexes ...`)
            collection.indexes.forEach(async index => {
                await databaseCreateIndex({
                    collectionId: collection['$id'],
                    indexId: index.key,
                    type: index.type,
                    attributes: index.attributes,
                    orders: index.orders,
                    parseOutput: false
                })
            })

        } catch (e) {
            if (e.code == 404) {
                log(`Collection ${collection.name} does not exist in the project. Creating ... `);
                response = await databaseCreateCollection({
                    collectionId: collection['$id'],
                    name: collection.name,
                    permission: collection.permission,
                    read: collection['$read'],
                    write: collection['$write'],
                    parseOutput: false
                })

                log(`Creating attributes ... `);
                collection.attributes.forEach(async attribute => {
                    await createAttribute(collection['$id'], attribute);
                });

                await new Promise(resolve => setTimeout(resolve, 3000));

                log(`Creating indexes ...`);
                collection.indexes.forEach(async index => {
                    await databaseCreateIndex({
                        collectionId: collection['$id'],
                        indexId: index.key,
                        type: index.type,
                        attributes: index.attributes,
                        orders: index.orders,
                        parseOutput: false
                    })
                })

                await new Promise(resolve => setTimeout(resolve, 3000));

                success(`Deployed ${collection.name} ( ${collection['$id']} )`);
            } else {
                throw e;
            }
        }
    }
}

deploy
    .command("function")
    .description("Deploy functions in the current directory.")
    .action(actionRunner(deployFunction));

deploy
    .command("collection")
    .description("Deploy collections in the current project.")
    .action(actionRunner(deployCollection));

module.exports = {
    deploy
}