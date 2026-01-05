import fs = require('fs');
import path = require('path');
import { parse as parseDotenv } from 'dotenv';
import chalk from 'chalk';
import inquirer from "inquirer";
import JSONbig from "json-bigint";
import { Command } from "commander";
import ID from "../id";
import { localConfig, globalConfig, KeysAttributes, KeysFunction, KeysSite, whitelistKeys, KeysTopics, KeysStorage, KeysTeams, KeysCollection, KeysTable } from "../config";
import { Spinner, SPINNER_ARC, SPINNER_DOTS } from '../spinner';
import { paginate } from '../paginate';
import { questionsPushBuckets, questionsPushTeams, questionsPushFunctions, questionsPushSites, questionsGetEntrypoint, questionsPushCollections, questionsPushTables, questionPushChanges, questionPushChangesConfirmation, questionsPushMessagingTopics, questionsPushResources } from "../questions";
import { cliConfig, actionRunner, success, warn, log, hint, error, commandDescriptions, drawTable } from "../parser";
import { proxyCreateFunctionRule, proxyCreateSiteRule, proxyListRules } from './proxy';
import { consoleVariables } from './console';
import { sdkForConsole } from '../sdks';
import { functionsGet, functionsCreate, functionsUpdate, functionsCreateDeployment, functionsGetDeployment, functionsListVariables, functionsDeleteVariable, functionsCreateVariable } from './functions';
import { sitesGet, sitesCreate, sitesUpdate, sitesCreateDeployment, sitesGetDeployment, sitesCreateVariable, sitesListVariables, sitesDeleteVariable } from './sites';
import {
    databasesGet,
    databasesCreate,
    databasesUpdate,
    databasesCreateBooleanAttribute,
    databasesGetCollection,
    databasesCreateCollection,
    databasesCreateStringAttribute,
    databasesCreateIntegerAttribute,
    databasesCreateFloatAttribute,
    databasesCreateEmailAttribute,
    databasesCreateDatetimeAttribute,
    databasesCreateIndex,
    databasesCreateUrlAttribute,
    databasesCreateIpAttribute,
    databasesCreateEnumAttribute,
    databasesUpdateBooleanAttribute,
    databasesUpdateStringAttribute,
    databasesUpdateIntegerAttribute,
    databasesUpdateFloatAttribute,
    databasesUpdateEmailAttribute,
    databasesUpdateDatetimeAttribute,
    databasesUpdateUrlAttribute,
    databasesUpdateIpAttribute,
    databasesUpdateEnumAttribute,
    databasesUpdateRelationshipAttribute,
    databasesCreateRelationshipAttribute,
    databasesCreatePointAttribute,
    databasesUpdatePointAttribute,
    databasesCreateLineAttribute,
    databasesUpdateLineAttribute,
    databasesCreatePolygonAttribute,
    databasesUpdatePolygonAttribute,
    databasesDeleteAttribute,
    databasesDeleteIndex,
    databasesListAttributes,
    databasesListIndexes,
    databasesUpdateCollection
} from "./databases";
import {
    tablesDBCreate,
    tablesDBGet,
    tablesDBUpdate,
    tablesDBCreateTable,
    tablesDBGetTable,
    tablesDBUpdateTable,
    tablesDBList,
    tablesDBDelete,
    tablesDBListTables,
    tablesDBDeleteTable
} from "./tables-db";
import {
    storageGetBucket, storageUpdateBucket, storageCreateBucket
} from "./storage";
import {
    messagingGetTopic, messagingUpdateTopic, messagingCreateTopic
} from "./messaging";
import {
    teamsGet,
    teamsUpdateName,
    teamsCreate
} from "./teams";
import {
    projectsGet,
    projectsUpdate,
    projectsUpdateServiceStatus,
    projectsUpdateAuthStatus,
    projectsUpdateAuthDuration,
    projectsUpdateAuthLimit,
    projectsUpdateAuthSessionsLimit,
    projectsUpdateAuthPasswordDictionary,
    projectsUpdateAuthPasswordHistory,
    projectsUpdatePersonalDataCheck,
    projectsUpdateSessionAlerts,
    projectsUpdateMockNumbers,
} from "./projects";
import { ApiService } from '../enums/api-service';
import { AuthMethod } from '../enums/auth-method';
import { checkDeployConditions } from '../utils';

const JSONbigNative = JSONbig({ storeAsString: false });

const STEP_SIZE = 100;      // Resources
const POLL_DEBOUNCE = 2000; // Milliseconds
const POLL_MAX_DEBOUNCE = 1800; // Times of POLL_DEBOUNCE (1 hour)
const POLL_DEFAULT_VALUE = 30;

let pollMaxDebounces = POLL_DEFAULT_VALUE;

const changeableKeys = ['status', 'required', 'xdefault', 'elements', 'min', 'max', 'default', 'error'];

interface AwaitPools {
    wipeAttributes: (databaseId: string, collectionId: string, iteration?: number) => Promise<boolean>;
    wipeIndexes: (databaseId: string, collectionId: string, iteration?: number) => Promise<boolean>;
    deleteAttributes: (databaseId: string, collectionId: string, attributeKeys: any[], iteration?: number) => Promise<boolean>;
    expectAttributes: (databaseId: string, collectionId: string, attributeKeys: string[], iteration?: number) => Promise<boolean>;
    deleteIndexes: (databaseId: string, collectionId: string, indexesKeys: any[], iteration?: number) => Promise<boolean>;
    expectIndexes: (databaseId: string, collectionId: string, indexKeys: string[], iteration?: number) => Promise<boolean>;
}

const awaitPools: AwaitPools = {
    wipeAttributes: async (databaseId: string, collectionId: string, iteration: number = 1): Promise<boolean> => {
        if (iteration > pollMaxDebounces) {
            return false;
        }

        const { total } = await databasesListAttributes({
            databaseId,
            collectionId,
            queries: [JSON.stringify({ method: 'limit', values: [1] })],
            parseOutput: false
        });

        if (total === 0) {
            return true;
        }

        if (pollMaxDebounces === POLL_DEFAULT_VALUE) {
            let steps = Math.max(1, Math.ceil(total / STEP_SIZE));
            if (steps > 1 && iteration === 1) {
                pollMaxDebounces *= steps;

                log('Found a large number of attributes, increasing timeout to ' + (pollMaxDebounces * POLL_DEBOUNCE / 1000 / 60) + ' minutes')
            }
        }

        await new Promise(resolve => setTimeout(resolve, POLL_DEBOUNCE));

        return await awaitPools.wipeAttributes(
            databaseId,
            collectionId,
            iteration + 1
        );
    },
    wipeIndexes: async (databaseId: string, collectionId: string, iteration: number = 1): Promise<boolean> => {
        if (iteration > pollMaxDebounces) {
            return false;
        }

        const { total } = await databasesListIndexes({
            databaseId,
            collectionId,
            queries: [JSON.stringify({ method: 'limit', values: [1] })],
            parseOutput: false
        });

        if (total === 0) {
            return true;
        }

        if (pollMaxDebounces === POLL_DEFAULT_VALUE) {
            let steps = Math.max(1, Math.ceil(total / STEP_SIZE));
            if (steps > 1 && iteration === 1) {
                pollMaxDebounces *= steps;

                log('Found a large number of indexes, increasing timeout to ' + (pollMaxDebounces * POLL_DEBOUNCE / 1000 / 60) + ' minutes')
            }
        }

        await new Promise(resolve => setTimeout(resolve, POLL_DEBOUNCE));

        return await awaitPools.wipeIndexes(
            databaseId,
            collectionId,
            iteration + 1
        );
    },
    deleteAttributes: async (databaseId: string, collectionId: string, attributeKeys: any[], iteration: number = 1): Promise<boolean> => {
        if (iteration > pollMaxDebounces) {
            return false;
        }

        if (pollMaxDebounces === POLL_DEFAULT_VALUE) {
            let steps = Math.max(1, Math.ceil(attributeKeys.length / STEP_SIZE));
            if (steps > 1 && iteration === 1) {
                pollMaxDebounces *= steps;

                log('Found a large number of attributes to be deleted. Increasing timeout to ' + (pollMaxDebounces * POLL_DEBOUNCE / 1000 / 60) + ' minutes')
            }
        }

        const { attributes } = await paginate(databasesListAttributes, {
            databaseId,
            collectionId,
            parseOutput: false
        }, 100, 'attributes');

        const ready = attributeKeys.filter((attribute: any) => attributes.includes(attribute.key));

        if (ready.length === 0) {
            return true;
        }

        await new Promise(resolve => setTimeout(resolve, POLL_DEBOUNCE));

        return await awaitPools.expectAttributes(
            databaseId,
            collectionId,
            attributeKeys,
            iteration + 1
        );
    },
    expectAttributes: async (databaseId: string, collectionId: string, attributeKeys: string[], iteration: number = 1): Promise<boolean> => {
        if (iteration > pollMaxDebounces) {
            return false;
        }

        if (pollMaxDebounces === POLL_DEFAULT_VALUE) {
            let steps = Math.max(1, Math.ceil(attributeKeys.length / STEP_SIZE));
            if (steps > 1 && iteration === 1) {
                pollMaxDebounces *= steps;

                log('Creating a large number of attributes, increasing timeout to ' + (pollMaxDebounces * POLL_DEBOUNCE / 1000 / 60) + ' minutes')
            }
        }

        const { attributes } = await paginate(databasesListAttributes, {
            databaseId,
            collectionId,
            parseOutput: false
        }, 100, 'attributes');

        const ready = attributes
            .filter((attribute: any) => {
                if (attributeKeys.includes(attribute.key)) {
                    if (['stuck', 'failed'].includes(attribute.status)) {
                        throw new Error(`Attribute '${attribute.key}' failed!`);
                    }

                    return attribute.status === 'available';
                }

                return false;
            })
            .map((attribute: any) => attribute.key);

        if (ready.length === attributeKeys.length) {
            return true;
        }

        await new Promise(resolve => setTimeout(resolve, POLL_DEBOUNCE));

        return await awaitPools.expectAttributes(
            databaseId,
            collectionId,
            attributeKeys,
            iteration + 1
        );
    },
    deleteIndexes: async (databaseId: string, collectionId: string, indexesKeys: any[], iteration: number = 1): Promise<boolean> => {
        if (iteration > pollMaxDebounces) {
            return false;
        }

        if (pollMaxDebounces === POLL_DEFAULT_VALUE) {
            let steps = Math.max(1, Math.ceil(indexesKeys.length / STEP_SIZE));
            if (steps > 1 && iteration === 1) {
                pollMaxDebounces *= steps;

                log('Found a large number of indexes to be deleted. Increasing timeout to ' + (pollMaxDebounces * POLL_DEBOUNCE / 1000 / 60) + ' minutes')
            }
        }

        const { indexes } = await paginate(databasesListIndexes, {
            databaseId,
            collectionId,
            parseOutput: false
        }, 100, 'indexes');

        const ready = indexesKeys.filter((index: any) => indexes.includes(index.key));

        if (ready.length === 0) {
            return true;
        }

        await new Promise(resolve => setTimeout(resolve, POLL_DEBOUNCE));

        return await awaitPools.expectIndexes(
            databaseId,
            collectionId,
            indexesKeys,
            iteration + 1
        );
    },
    expectIndexes: async (databaseId: string, collectionId: string, indexKeys: string[], iteration: number = 1): Promise<boolean> => {
        if (iteration > pollMaxDebounces) {
            return false;
        }

        if (pollMaxDebounces === POLL_DEFAULT_VALUE) {
            let steps = Math.max(1, Math.ceil(indexKeys.length / STEP_SIZE));
            if (steps > 1 && iteration === 1) {
                pollMaxDebounces *= steps;

                log('Creating a large number of indexes, increasing timeout to ' + (pollMaxDebounces * POLL_DEBOUNCE / 1000 / 60) + ' minutes')
            }
        }

        const { indexes } = await paginate(databasesListIndexes, {
            databaseId,
            collectionId,
            parseOutput: false
        }, 100, 'indexes');

        const ready = indexes
            .filter((index: any) => {
                if (indexKeys.includes(index.key)) {
                    if (['stuck', 'failed'].includes(index.status)) {
                        throw new Error(`Index '${index.key}' failed!`);
                    }

                    return index.status === 'available';
                }

                return false;
            })
            .map((index: any) => index.key);

        if (ready.length >= indexKeys.length) {
            return true;
        }

        await new Promise(resolve => setTimeout(resolve, POLL_DEBOUNCE));

        return await awaitPools.expectIndexes(
            databaseId,
            collectionId,
            indexKeys,
            iteration + 1
        );
    },
}

const getConfirmation = async (): Promise<boolean> => {
    if (cliConfig.force) {
        return true;
    }

    async function fixConfirmation(): Promise<string> {
        const answers = await inquirer.prompt(questionPushChangesConfirmation);
        if (answers.changes !== 'YES' && answers.changes !== 'NO') {
            return await fixConfirmation();
        }

        return answers.changes;
    }

    let answers = await inquirer.prompt(questionPushChanges);

    if (answers.changes !== 'YES' && answers.changes !== 'NO') {
        answers.changes = await fixConfirmation();
    }

    if (answers.changes === 'YES') {
        return true;
    }

    warn('Skipping push action. Changes were not applied.');
    return false;

};
const isEmpty = (value: any): boolean => (value === null || value === undefined || (typeof value === "string" && value.trim().length === 0) || (Array.isArray(value) && value.length === 0));

const approveChanges = async (resource: any[], resourceGetFunction: Function, keys: Set<string>, resourceName: string, resourcePlural: string, skipKeys: string[] = [], secondId: string = '', secondResourceName: string = ''): Promise<boolean> => {
    log('Checking for changes ...');
    const changes: any[] = [];

    await Promise.all(resource.map(async (localResource) => {
        try {
            const options: Record<string, any> = {
                [resourceName]: localResource['$id'],
                parseOutput: false,
            };

            if (secondId !== '' && secondResourceName !== '') {
                options[secondResourceName] = localResource[secondId]
            }

            const remoteResource = await resourceGetFunction(options);

            for (let [key, value] of Object.entries(whitelistKeys(remoteResource, keys))) {
                if (skipKeys.includes(key)) {
                    continue;
                }

                if (isEmpty(value) && isEmpty(localResource[key])) {
                    continue;
                }

                if (Array.isArray(value) && Array.isArray(localResource[key])) {
                    if (JSON.stringify(value) !== JSON.stringify(localResource[key])) {
                        changes.push({
                            id: localResource['$id'],
                            key,
                            remote: chalk.red((value as string[]).join('\n')),
                            local: chalk.green(localResource[key].join('\n'))
                        })
                    }
                } else if (value !== localResource[key]) {
                    changes.push({
                        id: localResource['$id'],
                        key,
                        remote: chalk.red(value),
                        local: chalk.green(localResource[key])
                    })
                }
            }
        } catch (e: any) {
            if (Number(e.code) !== 404) {
                throw e;
            }
        }
    }));

    if (changes.length === 0) {
        return true;
    }

    drawTable(changes);
    if ((await getConfirmation()) === true) {
        return true;
    }

    success(`Successfully pushed 0 ${resourcePlural}.`);
    return false;
}

const getObjectChanges = (remote: any, local: any, index: string, what: string): any[] => {
    const changes: any[] = [];

    if (remote[index] && local[index]) {
        for (let [service, status] of Object.entries(remote[index])) {
            const localValue = local[index][service];
            let valuesEqual = false;

            if (Array.isArray(status) && Array.isArray(localValue)) {
                valuesEqual = JSON.stringify(status) === JSON.stringify(localValue);
            } else {
                valuesEqual = status === localValue;
            }
            
            if (!valuesEqual) {
                changes.push({ group: what, setting: service, remote: chalk.red(status), local: chalk.green(localValue) })
            }
        }
    }

    return changes;
}

const createAttribute = (databaseId: string, collectionId: string, attribute: any): Promise<any> => {
    switch (attribute.type) {
        case 'string':
            switch (attribute.format) {
                case 'email':
                    return databasesCreateEmailAttribute({
                        databaseId,
                        collectionId,
                        key: attribute.key,
                        required: attribute.required,
                        xdefault: attribute.default,
                        array: attribute.array,
                        parseOutput: false
                    })
                case 'url':
                    return databasesCreateUrlAttribute({
                        databaseId,
                        collectionId,
                        key: attribute.key,
                        required: attribute.required,
                        xdefault: attribute.default,
                        array: attribute.array,
                        parseOutput: false
                    })
                case 'ip':
                    return databasesCreateIpAttribute({
                        databaseId,
                        collectionId,
                        key: attribute.key,
                        required: attribute.required,
                        xdefault: attribute.default,
                        array: attribute.array,
                        parseOutput: false
                    })
                case 'enum':
                    return databasesCreateEnumAttribute({
                        databaseId,
                        collectionId,
                        key: attribute.key,
                        elements: attribute.elements,
                        required: attribute.required,
                        xdefault: attribute.default,
                        array: attribute.array,
                        parseOutput: false
                    })
                default:
                    return databasesCreateStringAttribute({
                        databaseId,
                        collectionId,
                        key: attribute.key,
                        size: attribute.size,
                        required: attribute.required,
                        xdefault: attribute.default,
                        array: attribute.array,
                        encrypt: attribute.encrypt,
                        parseOutput: false
                    })

            }
        case 'integer':
            return databasesCreateIntegerAttribute({
                databaseId,
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
            return databasesCreateFloatAttribute({
                databaseId,
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
            return databasesCreateBooleanAttribute({
                databaseId,
                collectionId,
                key: attribute.key,
                required: attribute.required,
                xdefault: attribute.default,
                array: attribute.array,
                parseOutput: false
            })
        case 'datetime':
            return databasesCreateDatetimeAttribute({
                databaseId,
                collectionId,
                key: attribute.key,
                required: attribute.required,
                xdefault: attribute.default,
                array: attribute.array,
                parseOutput: false
            })
        case 'relationship':
            return databasesCreateRelationshipAttribute({
                databaseId,
                collectionId,
                relatedCollectionId: attribute.relatedTable ?? attribute.relatedCollection,
                type: attribute.relationType,
                twoWay: attribute.twoWay,
                key: attribute.key,
                twoWayKey: attribute.twoWayKey,
                onDelete: attribute.onDelete,
                parseOutput: false
            })
        case 'point':
            return databasesCreatePointAttribute({
                databaseId,
                collectionId,
                key:attribute.key,
                required:attribute.required,
                xdefault:attribute.default,
                parseOutput:false
            })
        case 'linestring':
            return databasesCreateLineAttribute({
                databaseId,
                collectionId,
                key:attribute.key,
                required:attribute.required,
                xdefault:attribute.default,
                parseOutput:false
            })
        case 'polygon':
            return databasesCreatePolygonAttribute({
                databaseId,
                collectionId,
                key:attribute.key,
                required:attribute.required,
                xdefault:attribute.default,
                parseOutput:false
            })
        default:
            throw new Error(`Unsupported attribute type: ${attribute.type}`);
    }
}

const updateAttribute = (databaseId: string, collectionId: string, attribute: any): Promise<any> => {
    switch (attribute.type) {
        case 'string':
            switch (attribute.format) {
                case 'email':
                    return databasesUpdateEmailAttribute({
                        databaseId,
                        collectionId,
                        key: attribute.key,
                        required: attribute.required,
                        xdefault: attribute.default,
                        parseOutput: false
                    })
                case 'url':
                    return databasesUpdateUrlAttribute({
                        databaseId,
                        collectionId,
                        key: attribute.key,
                        required: attribute.required,
                        xdefault: attribute.default,
                        parseOutput: false
                    })
                case 'ip':
                    return databasesUpdateIpAttribute({
                        databaseId,
                        collectionId,
                        key: attribute.key,
                        required: attribute.required,
                        xdefault: attribute.default,
                        parseOutput: false
                    })
                case 'enum':
                    return databasesUpdateEnumAttribute({
                        databaseId,
                        collectionId,
                        key: attribute.key,
                        elements: attribute.elements,
                        required: attribute.required,
                        xdefault: attribute.default,
                        parseOutput: false
                    })
                default:
                    return databasesUpdateStringAttribute({
                        databaseId,
                        collectionId,
                        key: attribute.key,
                        required: attribute.required,
                        xdefault: attribute.default,
                        parseOutput: false
                    })

            }
        case 'integer':
            return databasesUpdateIntegerAttribute({
                databaseId,
                collectionId,
                key: attribute.key,
                required: attribute.required,
                min: attribute.min,
                max: attribute.max,
                xdefault: attribute.default,
                parseOutput: false
            })
        case 'double':
            return databasesUpdateFloatAttribute({
                databaseId,
                collectionId,
                key: attribute.key,
                required: attribute.required,
                min: attribute.min,
                max: attribute.max,
                xdefault: attribute.default,
                parseOutput: false
            })
        case 'boolean':
            return databasesUpdateBooleanAttribute({
                databaseId,
                collectionId,
                key: attribute.key,
                required: attribute.required,
                xdefault: attribute.default,
                parseOutput: false
            })
        case 'datetime':
            return databasesUpdateDatetimeAttribute({
                databaseId,
                collectionId,
                key: attribute.key,
                required: attribute.required,
                xdefault: attribute.default,
                parseOutput: false
            })
        case 'relationship':
            return databasesUpdateRelationshipAttribute({
                databaseId,
                collectionId,
                key: attribute.key,
                onDelete: attribute.onDelete,
                parseOutput: false
            })
        case 'point':
            return databasesUpdatePointAttribute({
                databaseId,
                collectionId,
                key:attribute.key,
                required:attribute.required,
                xdefault:attribute.default,
                parseOutput:false
            })
        case 'linestring':
            return databasesUpdateLineAttribute({
                databaseId,
                collectionId,
                key:attribute.key,
                required:attribute.required,
                xdefault:attribute.default,
                parseOutput:false
            })
        case 'polygon':
            return databasesUpdatePolygonAttribute({
                databaseId,
                collectionId,
                key:attribute.key,
                required:attribute.required,
                xdefault:attribute.default,
                parseOutput:false
            })
        default:
            throw new Error(`Unsupported attribute type: ${attribute.type}`);
    }
}
const deleteAttribute = async (collection: any, attribute: any, isIndex: boolean = false): Promise<void> => {
    log(`Deleting ${isIndex ? 'index' : 'attribute'} ${attribute.key} of ${collection.name} ( ${collection['$id']} )`);

    if (isIndex) {
        await databasesDeleteIndex({
            databaseId: collection['databaseId'],
            collectionId: collection['$id'],
            key: attribute.key,
            parseOutput: false
        });
        return;
    }

    await databasesDeleteAttribute({
        databaseId: collection['databaseId'],
        collectionId: collection['$id'],
        key: attribute.key,
        parseOutput: false
    });
}

const isEqual = (a: any, b: any): boolean => {
    if (a === b) return true;
    
    if (a && b && typeof a === 'object' && typeof b === 'object') {
        if (a.constructor && a.constructor.name === 'BigNumber' && 
            b.constructor && b.constructor.name === 'BigNumber') {
            return a.eq(b);
        }
        
        if (typeof a.equals === 'function') {
            return a.equals(b);
        }
        
        if (typeof a.eq === 'function') {
            return a.eq(b);
        }
    }
    
    if (typeof a === 'number' && typeof b === 'number') {
        if (isNaN(a) && isNaN(b)) return true;
        if (!isFinite(a) && !isFinite(b)) return a === b;
        return Math.abs(a - b) < Number.EPSILON;
    }
    
    return false;
};

const compareAttribute = (remote: any, local: any, reason: string, key: string): string => {
    if (isEmpty(remote) && isEmpty(local)) {
        return reason;
    }

    if (Array.isArray(remote) && Array.isArray(local)) {
        if (JSON.stringify(remote) !== JSON.stringify(local)) {
            const bol = reason === '' ? '' : '\n';
            reason += `${bol}${key} changed from ${chalk.red(remote)} to ${chalk.green(local)}`;
        }
    } else if (!isEqual(remote, local)) {
        const bol = reason === '' ? '' : '\n';
        reason += `${bol}${key} changed from ${chalk.red(remote)} to ${chalk.green(local)}`;
    }

    return reason
}

interface AttributeChange {
    key: string;
    attribute: any;
    reason: string;
    action: string;
}

/**
 * Check if attribute non-changeable fields has been changed
 * If so return the differences as an object.
 */
const checkAttributeChanges = (remote: any, local: any, collection: any, recreating: boolean = true): AttributeChange | undefined => {
    if (local === undefined) {
        return undefined;
    }

    const keyName = `${chalk.yellow(local.key)} in ${collection.name} (${collection['$id']})`;
    const action = chalk.cyan(recreating ? 'recreating' : 'changing');
    let reason = '';
    let attribute = recreating ? remote : local;

    for (let key of Object.keys(remote)) {
        if (!KeysAttributes.has(key)) {
            continue;
        }

        if (changeableKeys.includes(key)) {
            if (!recreating) {
                reason = compareAttribute(remote[key], local[key], reason, key)
            }
            continue;
        }

        if (!recreating) {
            continue;
        }

        reason = compareAttribute(remote[key], local[key], reason, key)
    }

    return reason === '' ? undefined : { key: keyName, attribute, reason, action };
}

/**
 * Check if attributes contain the given attribute
 */
const attributesContains = (attribute: any, attributes: any[]): any => attributes.find((attr) => attr.key === attribute.key);

const generateChangesObject = (attribute: any, collection: any, isAdding: boolean): AttributeChange => {
    return {
        key: `${chalk.yellow(attribute.key)} in ${collection.name} (${collection['$id']})`,
        attribute: attribute,
        reason: isAdding ? 'Field isn\'t present on the remote server' : 'Field isn\'t present on the appwrite.config.json file',
        action: isAdding ? chalk.green('adding') : chalk.red('deleting')
    };
};

/**
 * Filter deleted and recreated attributes,
 * return list of attributes to create
 */
const attributesToCreate = async (remoteAttributes: any[], localAttributes: any[], collection: any, isIndex: boolean = false): Promise<any[]> => {

    const deleting = remoteAttributes.filter((attribute) => !attributesContains(attribute, localAttributes)).map((attr) => generateChangesObject(attr, collection, false));
    const adding = localAttributes.filter((attribute) => !attributesContains(attribute, remoteAttributes)).map((attr) => generateChangesObject(attr, collection, true));
    const conflicts = remoteAttributes.map((attribute) => checkAttributeChanges(attribute, attributesContains(attribute, localAttributes), collection)).filter(attribute => attribute !== undefined) as AttributeChange[];
    const changes = remoteAttributes.map((attribute) => checkAttributeChanges(attribute, attributesContains(attribute, localAttributes), collection, false))
        .filter(attribute => attribute !== undefined)
        .filter(attribute => conflicts.filter(attr => attribute!.key === attr.key).length !== 1) as AttributeChange[];

    let changedAttributes: any[] = [];
    const changing = [...deleting, ...adding, ...conflicts, ...changes]
    if (changing.length === 0) {
        return changedAttributes;
    }

    log(!cliConfig.force ? 'There are pending changes in your collection deployment' : 'List of applied changes');

    drawTable(changing.map((change) => {
        return { Key: change.key, Action: change.action, Reason: change.reason, };
    }));

    if (!cliConfig.force) {
        if (deleting.length > 0 && !isIndex) {
            console.log(`${chalk.red('------------------------------------------------------')}`);
            console.log(`${chalk.red('| WARNING: Attribute deletion may cause loss of data |')}`);
            console.log(`${chalk.red('------------------------------------------------------')}`);
            console.log();
        }
        if (conflicts.length > 0 && !isIndex) {
            console.log(`${chalk.red('--------------------------------------------------------')}`);
            console.log(`${chalk.red('| WARNING: Attribute recreation may cause loss of data |')}`);
            console.log(`${chalk.red('--------------------------------------------------------')}`);
            console.log();
        }

        if ((await getConfirmation()) !== true) {
            return changedAttributes;
        }
    }

    if (conflicts.length > 0) {
        changedAttributes = conflicts.map((change) => change.attribute);
        await Promise.all(changedAttributes.map((changed) => deleteAttribute(collection, changed, isIndex)));
        remoteAttributes = remoteAttributes.filter((attribute) => !attributesContains(attribute, changedAttributes))
    }

    if (changes.length > 0) {
        changedAttributes = changes.map((change) => change.attribute);
        await Promise.all(changedAttributes.map((changed) => updateAttribute(collection['databaseId'], collection['$id'], changed)));
    }

    const deletingAttributes = deleting.map((change) => change.attribute);
    await Promise.all(deletingAttributes.map((attribute) => deleteAttribute(collection, attribute, isIndex)));
    const attributeKeys = [...remoteAttributes.map((attribute: any) => attribute.key), ...deletingAttributes.map((attribute: any) => attribute.key)]

    if (attributeKeys.length) {
        const deleteAttributesPoolStatus = await awaitPools.deleteAttributes(collection['databaseId'], collection['$id'], attributeKeys);

        if (!deleteAttributesPoolStatus) {
            throw new Error("Attribute deletion timed out.");
        }
    }

    return localAttributes.filter((attribute) => !attributesContains(attribute, remoteAttributes));
}

const createIndexes = async (indexes: any[], collection: any): Promise<void> => {
    log(`Creating indexes ...`)

    for (let index of indexes) {
        await databasesCreateIndex({
            databaseId: collection['databaseId'],
            collectionId: collection['$id'],
            key: index.key,
            type: index.type,
            attributes: index.columns ?? index.attributes,
            orders: index.orders,
            parseOutput: false
        });
    }

    const result = await awaitPools.expectIndexes(
        collection['databaseId'],
        collection['$id'],
        indexes.map((index: any) => index.key)
    );

    if (!result) {
        throw new Error('Index creation timed out.');
    }

    success(`Created ${indexes.length} indexes`);
}

const createAttributes = async (attributes: any[], collection: any): Promise<void> => {
    for (let attribute of attributes) {
        if (attribute.side !== 'child') {
            await createAttribute(collection['databaseId'], collection['$id'], attribute);
        }
    }

    const result = await awaitPools.expectAttributes(
        collection['databaseId'],
        collection['$id'],
        collection.attributes.filter((attribute: any) => attribute.side !== 'child').map((attribute: any) => attribute.key)
    );

    if (!result) {
        throw new Error(`Attribute creation timed out.`);
    }

    success(`Created ${attributes.length} attributes`);
}

const createColumns = async (columns: any[], table: any): Promise<void> => {
    for (let column of columns) {
        if (column.side !== 'child') {
            await createAttribute(table['databaseId'], table['$id'], column);
        }
    }

    const result = await awaitPools.expectAttributes(
        table['databaseId'],
        table['$id'],
        table.columns.filter((column: any) => column.side !== 'child').map((column: any) => column.key)
    );

    if (!result) {
        throw new Error(`Column creation timed out.`);
    }

    success(`Created ${columns.length} columns`);
}

const pushResources = async (): Promise<void> => {
    const actions: Record<string, (options?: any) => Promise<void>> = {
        settings: pushSettings,
        functions: pushFunction,
        sites: pushSite,
        collections: pushCollection,
        tables: pushTable,
        buckets: pushBucket,
        teams: pushTeam,
        messages: pushMessagingTopic
    }

    if (cliConfig.all) {
        for (let action of Object.values(actions)) {
            await action({ returnOnZero: true });
        }
    } else {
        const answers = await inquirer.prompt(questionsPushResources);

        const action = actions[answers.resource];
        if (action !== undefined) {
            await action({ returnOnZero: true });
        }
    }
};

const pushSettings = async (): Promise<void> => {
    checkDeployConditions(localConfig);

    try {
        let response = await projectsGet({
            parseOutput: false,
            projectId: localConfig.getProject().projectId
        });

        const remoteSettings = localConfig.createSettingsObject(response ?? {});
        const localSettings = localConfig.getProject().projectSettings ?? {};

        log('Checking for changes ...');
        const changes: any[] = [];

        changes.push(...(getObjectChanges(remoteSettings, localSettings, 'services', 'Service')));
        changes.push(...(getObjectChanges(remoteSettings['auth'] ?? {}, localSettings['auth'] ?? {}, 'methods', 'Auth method')));
        changes.push(...(getObjectChanges(remoteSettings['auth'] ?? {}, localSettings['auth'] ?? {}, 'security', 'Auth security')));

        if (changes.length > 0) {
            drawTable(changes);
            if ((await getConfirmation()) !== true) {
                success(`Successfully pushed 0 project settings.`);
                return;
            }
        }
    } catch (e) {
    }

    try {
        log("Pushing project settings ...");

        const projectId = localConfig.getProject().projectId;
        const projectName = localConfig.getProject().projectName;
        const settings = localConfig.getProject().projectSettings ?? {};

        if (projectName) {
            log("Applying project name ...");
            await projectsUpdate({
                projectId,
                name: projectName,
                parseOutput: false
            });
        }

        if (settings.services) {
            log("Applying service statuses ...");
            for (let [service, status] of Object.entries(settings.services)) {
                await projectsUpdateServiceStatus({
                    projectId,
                    service: service as ApiService,
                    status,
                    parseOutput: false
                });
            }
        }

        if (settings.auth) {
            if (settings.auth.security) {
                log("Applying auth security settings ...");
                await projectsUpdateAuthDuration({ projectId, duration: settings.auth.security.duration, parseOutput: false });
                await projectsUpdateAuthLimit({ projectId, limit: settings.auth.security.limit, parseOutput: false });
                await projectsUpdateAuthSessionsLimit({ projectId, limit: settings.auth.security.sessionsLimit, parseOutput: false });
                await projectsUpdateAuthPasswordDictionary({ projectId, enabled: settings.auth.security.passwordDictionary, parseOutput: false });
                await projectsUpdateAuthPasswordHistory({ projectId, limit: settings.auth.security.passwordHistory, parseOutput: false });
                await projectsUpdatePersonalDataCheck({ projectId, enabled: settings.auth.security.personalDataCheck, parseOutput: false });
                await projectsUpdateSessionAlerts({ projectId, alerts: settings.auth.security.sessionAlerts, parseOutput: false });
                await projectsUpdateMockNumbers({ projectId, numbers: settings.auth.security.mockNumbers, parseOutput: false });
            }

            if (settings.auth.methods) {
                log("Applying auth methods statuses ...");

                for (let [method, status] of Object.entries(settings.auth.methods)) {
                    await projectsUpdateAuthStatus({
                        projectId,
                        method: method as AuthMethod,
                        status,
                        parseOutput: false
                    });
                }
            }
        }

        success(`Successfully pushed ${chalk.bold('all')} project settings.`);
    } catch (e) {
        throw e;
    }
}

interface PushSiteOptions {
    siteId?: string;
    async?: boolean;
    code?: boolean;
    withVariables?: boolean;
    returnOnZero?: boolean;
}

const pushSite = async({ siteId, async: asyncDeploy, code, withVariables }: PushSiteOptions = { returnOnZero: false }): Promise<void> => {
    process.chdir(localConfig.configDirectoryPath)

    const siteIds: string[] = [];

    if(siteId) {
        siteIds.push(siteId);
    } else if (cliConfig.all) {
        checkDeployConditions(localConfig);
        const sites = localConfig.getSites();
        siteIds.push(...sites.map((site: any) => {
            return site.$id;
        }));
    }

    if (siteIds.length <= 0) {
        const answers = await inquirer.prompt(questionsPushSites);
        if (answers.sites) {
            siteIds.push(...answers.sites);
        }
    }

    if (siteIds.length === 0) {
        log("No sites found.");
        hint("Use 'appwrite pull sites' to synchronize existing one, or use 'appwrite init site' to create a new one.");
        return;
    }

    let sites = siteIds.map((id: string) => {
        const sites = localConfig.getSites();
        const site = sites.find((s: any) => s.$id === id);

        if (!site) {
            throw new Error("Site '" + id + "' not found.")
        }

        return site;
    });

    log('Validating sites ...');
    // Validation is done BEFORE pushing so the deployment process can be run in async with progress update
    for (let site of sites) {

        if (!site.buildCommand) {
            log(`Site ${site.name} is missing build command.`);
            const answers = await inquirer.prompt(questionsGetEntrypoint)
            site.buildCommand = answers.entrypoint;
            localConfig.addSite(site);
        }
    }

    if (!(await approveChanges(sites, sitesGet, KeysSite, 'siteId', 'sites', ['vars']))) {
        return;
    }

    log('Pushing sites ...');

    Spinner.start(false);
    let successfullyPushed = 0;
    let successfullyDeployed = 0;
    const failedDeployments: any[] = [];
    const errors: any[] = [];

    await Promise.all(sites.map(async (site: any) => {
        let response: any = {};

        const ignore = site.ignore ? 'appwrite.config.json' : '.gitignore';
        let siteExists = false;
        let deploymentCreated = false;

        const updaterRow = new Spinner({ status: '', resource: site.name, id: site['$id'], end: `Ignoring using: ${ignore}` });

        updaterRow.update({ status: 'Getting' }).startSpinner(SPINNER_DOTS);

        try {
            response = await sitesGet({
                siteId: site['$id'],
                parseOutput: false,
            });
            siteExists = true;
            if (response.framework !== site.framework) {
                updaterRow.fail({ errorMessage: `Framework mismatch! (local=${site.framework},remote=${response.framework}) Please delete remote site or update your appwrite.config.json` })
                return;
            }

            updaterRow.update({ status: 'Updating' }).replaceSpinner(SPINNER_ARC);

            response = await sitesUpdate({
                siteId: site['$id'],
                name: site.name,
                framework: site.framework,
                buildRuntime: site.buildRuntime,
                specification: site.specification,
                timeout: site.timeout,
                enabled: site.enabled,
                logging: site.logging,
                adapter: site.adapter,
                buildCommand: site.buildCommand,
                installCommand: site.installCommand,
                outputDirectory: site.outputDirectory,
                parseOutput: false
            });
        } catch (e: any) {

            if (Number(e.code) === 404) {
                siteExists = false;
            } else {
                errors.push(e);
                updaterRow.fail({ errorMessage: e.message ?? 'General error occurs please try again' });
                return;
            }
        }

        if (!siteExists) {
            updaterRow.update({ status: 'Creating' }).replaceSpinner(SPINNER_DOTS);

            try {
                response = await sitesCreate({
                    siteId: site.$id,
                    name: site.name,
                    framework: site.framework,
                    specification: site.specification,
                    buildRuntime: site.buildRuntime,
                    buildCommand: site.buildCommand,
                    installCommand: site.installCommand,
                    outputDirectory: site.outputDirectory,
                    adapter: site.adapter,
                    timeout: site.timeout,
                    enabled: site.enabled,
                    logging: site.logging,
                    parseOutput: false
                });

                let domain = '';
                try {
                    const variables = await consoleVariables({ parseOutput: false, sdk: await sdkForConsole() });
                    domain = ID.unique() + '.' + variables['_APP_DOMAIN_SITES'];
                } catch (error) {
                    console.error('Error fetching console variables.');
                    throw error;
                }

                try {
                    const rule = await proxyCreateSiteRule(
                        {
                            domain: domain,
                            siteId: site.$id
                        }
                    );
                } catch (error) {
                    console.error('Error creating site rule.');
                    throw error;
                }

                updaterRow.update({ status: 'Created' });
            } catch (e: any) {
                errors.push(e)
                updaterRow.fail({ errorMessage: e.message ?? 'General error occurs please try again' });
                return;
            }
        }

        if (withVariables) {
            updaterRow.update({ status: 'Creating variables' }).replaceSpinner(SPINNER_ARC);

            const { variables } = await paginate(sitesListVariables, {
                siteId: site['$id'],
                parseOutput: false
            }, 100, 'variables');

            await Promise.all(variables.map(async (variable: any) => {
                await sitesDeleteVariable({
                    siteId: site['$id'],
                    variableId: variable['$id'],
                    parseOutput: false
                });
            }));

            const envFileLocation = `${site['path']}/.env`;
            let envVariables: Array<{key: string; value: string}> = [];
            try {
                if (fs.existsSync(envFileLocation)) {
                    const envObject = parseDotenv(fs.readFileSync(envFileLocation, 'utf8'));
                    envVariables = Object.entries(envObject || {}).map(([key, value]) => ({ key, value }));
                }
            } catch (error) {
                // Handle parsing errors gracefully
                envVariables = [];
            }
            await Promise.all(envVariables.map(async (variable) => {
                await sitesCreateVariable({
                    siteId: site['$id'],
                    key: variable.key,
                    value: variable.value,
                    parseOutput: false,
                    secret: false
                });
            }));
        }

        if (code === false) {
            successfullyPushed++;
            successfullyDeployed++;
            updaterRow.update({ status: 'Pushed' });
            updaterRow.stopSpinner();
            return;
        }

        try {
            updaterRow.update({ status: 'Pushing' }).replaceSpinner(SPINNER_ARC);
            response = await sitesCreateDeployment({
                siteId: site['$id'],
                code: site.path,
                activate: true,
                parseOutput: false
            })

            updaterRow.update({ status: 'Pushed' });
            deploymentCreated = true;
            successfullyPushed++;
        } catch (e: any) {
            errors.push(e);

            switch (e.code) {
                case 'ENOENT':
                    updaterRow.fail({ errorMessage: 'Not found in the current directory. Skipping...' })
                    break;
                default:
                    updaterRow.fail({ errorMessage: e.message ?? 'An unknown error occurred. Please try again.' })
            }
        }

        if (deploymentCreated && !asyncDeploy) {
            try {
                const deploymentId = response['$id'];
                updaterRow.update({ status: 'Deploying', end: 'Checking deployment status...' })
                let pollChecks = 0;

                while (true) {
                    response = await sitesGetDeployment({
                    siteId: site['$id'],
                    deploymentId: deploymentId,
                    parseOutput: false
                });

                const status = response['status'];
                    if (status === 'ready') {
                        successfullyDeployed++;

                        let url = '';
                        const res = await proxyListRules({
                            parseOutput: false,
                            queries: [
                                JSON.stringify({ method: 'limit', values: [1] }),
                                JSON.stringify({ method: 'equal', "attribute": "deploymentResourceType", "values": ["site"] }),
                                JSON.stringify({ method: 'equal', "attribute": "deploymentResourceId", "values": [site['$id']] }),
                                JSON.stringify({ method: 'equal', "attribute": "trigger", "values": ["manual"] }),
                            ],
                        });

                        if (Number(res.total) === 1) {
                            url = res.rules[0].domain;
                        }

                        updaterRow.update({ status: 'Deployed', end: url });

                        break;
                    } else if (status === 'failed') {
                        failedDeployments.push({ name: site['name'], $id: site['$id'], deployment: response['$id'] });
                        updaterRow.fail({ errorMessage: `Failed to deploy` });

                        break;
                    } else {
                        updaterRow.update({ status: 'Deploying', end: `Current status: ${status}` })
                    }

                    pollChecks++;
                    await new Promise(resolve => setTimeout(resolve, POLL_DEBOUNCE * 1.5));
                }
            } catch (e: any) {
                errors.push(e);
                updaterRow.fail({ errorMessage: e.message ?? 'Unknown error occurred. Please try again' })
            }
        }

        updaterRow.stopSpinner();
    }));

    Spinner.stop();

    failedDeployments.forEach((failed) => {
        const { name, deployment, $id } = failed;
        const failUrl = `${globalConfig.getEndpoint().slice(0, -3)}/console/project-${localConfig.getProject().projectId}/sites/site-${$id}/deployments/deployment-${deployment}`;

        error(`Deployment of ${name} has failed. Check at ${failUrl} for more details\n`);
    });

    if (!asyncDeploy) {
        if (successfullyPushed === 0) {
            error('No sites were pushed.');
        } else if (successfullyDeployed !== successfullyPushed) {
            warn(`Successfully pushed ${successfullyDeployed} of ${successfullyPushed} sites`)
        } else {
            success(`Successfully pushed ${successfullyPushed} sites.`);
        }
    } else {
        success(`Successfully pushed ${successfullyPushed} sites.`);
    }

    if (cliConfig.verbose) {
        errors.forEach(e => {
            console.error(e);
        })
    }
}

interface PushFunctionOptions {
    functionId?: string;
    async?: boolean;
    code?: boolean;
    withVariables?: boolean;
    returnOnZero?: boolean;
}

const pushFunction = async ({ functionId, async: asyncDeploy, code, withVariables }: PushFunctionOptions = { returnOnZero: false }): Promise<void> => {
    process.chdir(localConfig.configDirectoryPath)

    const functionIds: string[] = [];

    if (functionId) {
        functionIds.push(functionId);
    } else if (cliConfig.all) {
        checkDeployConditions(localConfig);
        const functions = localConfig.getFunctions();
        functionIds.push(...functions.map((func: any) => {
            return func.$id;
        }));
    }

    if (functionIds.length <= 0) {
        const answers = await inquirer.prompt(questionsPushFunctions);
        if (answers.functions) {
            functionIds.push(...answers.functions);
        }
    }

    if (functionIds.length === 0) {
        log("No functions found.");
        hint("Use 'appwrite pull functions' to synchronize existing one, or use 'appwrite init function' to create a new one.");
        return;
    }

    let functions = functionIds.map((id: string) => {
        const functions = localConfig.getFunctions();
        const func = functions.find((f: any) => f.$id === id);

        if (!func) {
            throw new Error("Function '" + id + "' not found.")
        }

        return func;
    });

    log('Validating functions ...');
    // Validation is done BEFORE pushing so the deployment process can be run in async with progress update
    for (let func of functions) {

        if (!func.entrypoint) {
            log(`Function ${func.name} is missing an entrypoint.`);
            const answers = await inquirer.prompt(questionsGetEntrypoint)
            func.entrypoint = answers.entrypoint;
            localConfig.addFunction(func);
        }
    }

    if (!(await approveChanges(functions, functionsGet, KeysFunction, 'functionId', 'functions', ['vars']))) {
        return;
    }

    log('Pushing functions ...');

    Spinner.start(false);
    let successfullyPushed = 0;
    let successfullyDeployed = 0;
    const failedDeployments: any[] = [];
    const errors: any[] = [];

    await Promise.all(functions.map(async (func: any) => {
        let response: any = {};

        const ignore = func.ignore ? 'appwrite.config.json' : '.gitignore';
        let functionExists = false;
        let deploymentCreated = false;

        const updaterRow = new Spinner({ status: '', resource: func.name, id: func['$id'], end: `Ignoring using: ${ignore}` });

        updaterRow.update({ status: 'Getting' }).startSpinner(SPINNER_DOTS);
        try {
            response = await functionsGet({
                functionId: func['$id'],
                parseOutput: false,
            });
            functionExists = true;
            if (response.runtime !== func.runtime) {
                updaterRow.fail({ errorMessage: `Runtime mismatch! (local=${func.runtime},remote=${response.runtime}) Please delete remote function or update your appwrite.config.json` })
                return;
            }

            updaterRow.update({ status: 'Updating' }).replaceSpinner(SPINNER_ARC);

            response = await functionsUpdate({
                functionId: func['$id'],
                name: func.name,
                specification: func.specification,
                execute: func.execute,
                events: func.events,
                schedule: func.schedule,
                timeout: func.timeout,
                enabled: func.enabled,
                logging: func.logging,
                entrypoint: func.entrypoint,
                commands: func.commands,
                scopes: func.scopes,
                parseOutput: false
            });
        } catch (e: any) {

            if (Number(e.code) === 404) {
                functionExists = false;
            } else {
                errors.push(e);
                updaterRow.fail({ errorMessage: e.message ?? 'General error occurs please try again' });
                return;
            }
        }

        if (!functionExists) {
            updaterRow.update({ status: 'Creating' }).replaceSpinner(SPINNER_DOTS);

            try {
                response = await functionsCreate({
                    functionId: func.$id,
                    name: func.name,
                    runtime: func.runtime,
                    specification: func.specification,
                    execute: func.execute,
                    events: func.events,
                    schedule: func.schedule,
                    timeout: func.timeout,
                    enabled: func.enabled,
                    logging: func.logging,
                    entrypoint: func.entrypoint,
                    commands: func.commands,
                    scopes: func.scopes,
                    parseOutput: false
                });

                let domain = '';
                try {
                    const variables = await consoleVariables({ parseOutput: false, sdk: await sdkForConsole() });
                    domain = ID.unique() + '.' + variables['_APP_DOMAIN_FUNCTIONS'];
                } catch (error) {
                    console.error('Error fetching console variables.');
                    throw error;
                }

                try {
                    const rule = await proxyCreateFunctionRule(
                        {
                            domain: domain,
                            functionId: func.$id
                        }
                    );
                } catch (error) {
                    console.error('Error creating function rule.');
                    throw error;
                }

                updaterRow.update({ status: 'Created' });
            } catch (e: any) {
                errors.push(e)
                updaterRow.fail({ errorMessage: e.message ?? 'General error occurs please try again' });
                return;
            }
        }

        if (withVariables) {
            updaterRow.update({ status: 'Updating variables' }).replaceSpinner(SPINNER_ARC);

            const { variables } = await paginate(functionsListVariables, {
                functionId: func['$id'],
                parseOutput: false
            }, 100, 'variables');

            await Promise.all(variables.map(async (variable: any) => {
                await functionsDeleteVariable({
                    functionId: func['$id'],
                    variableId: variable['$id'],
                    parseOutput: false
                });
            }));

            const envFileLocation = `${func['path']}/.env`;
            let envVariables: Array<{key: string; value: string}> = [];
            try {
                if (fs.existsSync(envFileLocation)) {
                    const envObject = parseDotenv(fs.readFileSync(envFileLocation, 'utf8'));
                    envVariables = Object.entries(envObject || {}).map(([key, value]) => ({ key, value }));
                }
            } catch (error) {
                // Handle parsing errors gracefully
                envVariables = [];
            }
            await Promise.all(envVariables.map(async (variable) => {
                await functionsCreateVariable({
                    functionId: func['$id'],
                    key: variable.key,
                    value: variable.value,
                    parseOutput: false,
                    secret: false
                });
            }));
        }

        if (code === false) {
            successfullyPushed++;
            successfullyDeployed++;
            updaterRow.update({ status: 'Pushed' });
            updaterRow.stopSpinner();
            return;
        }

        try {
            updaterRow.update({ status: 'Pushing' }).replaceSpinner(SPINNER_ARC);
            response = await functionsCreateDeployment({
                functionId: func['$id'],
                entrypoint: func.entrypoint,
                commands: func.commands,
                code: func.path,
                activate: true,
                parseOutput: false
            })

            updaterRow.update({ status: 'Pushed' });
            deploymentCreated = true;
            successfullyPushed++;
        } catch (e: any) {
            errors.push(e);

            switch (e.code) {
                case 'ENOENT':
                    updaterRow.fail({ errorMessage: 'Not found in the current directory. Skipping...' })
                    break;
                default:
                    updaterRow.fail({ errorMessage: e.message ?? 'An unknown error occurred. Please try again.' })
            }
        }

        if (deploymentCreated && !asyncDeploy) {
            try {
                const deploymentId = response['$id'];
                updaterRow.update({ status: 'Deploying', end: 'Checking deployment status...' })
                let pollChecks = 0;

                while (true) {
                    response = await functionsGetDeployment({
                        functionId: func['$id'],
                        deploymentId: deploymentId,
                        parseOutput: false
                    });

                    const status = response['status'];
                    if (status === 'ready') {
                        successfullyDeployed++;

                        let url = '';
                        const res = await proxyListRules({
                            parseOutput: false,
                            queries: [
                                JSON.stringify({ method: 'limit', values: [1] }),
                                JSON.stringify({ method: 'equal', "attribute": "deploymentResourceType", "values": ["function"] }),
                                JSON.stringify({ method: 'equal', "attribute": "deploymentResourceId", "values": [func['$id']] }),
                                JSON.stringify({ method: 'equal', "attribute": "trigger", "values": ["manual"] }),
                            ],
                        });

                        if (Number(res.total) === 1) {
                            url = res.rules[0].domain;
                        }

                        updaterRow.update({ status: 'Deployed', end: url });

                        break;
                    } else if (status === 'failed') {
                        failedDeployments.push({ name: func['name'], $id: func['$id'], deployment: response['$id'] });
                        updaterRow.fail({ errorMessage: `Failed to deploy` });

                        break;
                    } else {
                        updaterRow.update({ status: 'Deploying', end: `Current status: ${status}` })
                    }

                    pollChecks++;
                    await new Promise(resolve => setTimeout(resolve, POLL_DEBOUNCE * 1.5));
                }
            } catch (e: any) {
                errors.push(e);
                updaterRow.fail({ errorMessage: e.message ?? 'Unknown error occurred. Please try again' })
            }
        }

        updaterRow.stopSpinner();
    }));

    Spinner.stop();

    failedDeployments.forEach((failed) => {
        const { name, deployment, $id } = failed;
        const failUrl = `${globalConfig.getEndpoint().slice(0, -3)}/console/project-${localConfig.getProject().projectId}/functions/function-${$id}/deployment-${deployment}`;

        error(`Deployment of ${name} has failed. Check at ${failUrl} for more details\n`);
    });

    if (!asyncDeploy) {
        if (successfullyPushed === 0) {
            error('No functions were pushed.');
        } else if (successfullyDeployed !== successfullyPushed) {
            warn(`Successfully pushed ${successfullyDeployed} of ${successfullyPushed} functions`)
        } else {
            success(`Successfully pushed ${successfullyPushed} functions.`);
        }
    } else {
        success(`Successfully pushed ${successfullyPushed} functions.`);
    }

    if (cliConfig.verbose) {
        errors.forEach(e => {
            console.error(e);
        })
    }
}

interface TablesDBChangesResult {
    applied: boolean;
    resyncNeeded: boolean;
}

const checkAndApplyTablesDBChanges = async (): Promise<TablesDBChangesResult> => {
    log('Checking for tablesDB changes ...');
    
    const localTablesDBs = localConfig.getTablesDBs();
    const { databases: remoteTablesDBs } = await paginate(tablesDBList, { parseOutput: false }, 100, 'databases');
    
    if (localTablesDBs.length === 0 && remoteTablesDBs.length === 0) {
        return { applied: false, resyncNeeded: false };
    }
    
    const changes: any[] = [];
    const toCreate: any[] = [];
    const toUpdate: any[] = [];
    const toDelete: any[] = [];

    // Check for deletions - remote DBs that aren't in local config
    for (const remoteDB of remoteTablesDBs) {
        const localDB = localTablesDBs.find((db: any) => db.$id === remoteDB.$id);
        if (!localDB) {
            toDelete.push(remoteDB);
            changes.push({
                id: remoteDB.$id,
                action: chalk.red('deleting'),
                key: 'Database',
                remote: remoteDB.name,
                local: '(deleted locally)'
            });
        }
    }

    // Check for additions and updates
    for (const localDB of localTablesDBs) {
        const remoteDB = remoteTablesDBs.find((db: any) => db.$id === localDB.$id);
        
        if (!remoteDB) {
            toCreate.push(localDB);
            changes.push({
                id: localDB.$id,
                action: chalk.green('creating'),
                key: 'Database',
                remote: '(does not exist)',
                local: localDB.name
            });
        } else {
            let hasChanges = false;
            
            if (remoteDB.name !== localDB.name) {
                hasChanges = true;
                changes.push({
                    id: localDB.$id,
                    action: chalk.yellow('updating'),
                    key: 'Name',
                    remote: remoteDB.name,
                    local: localDB.name
                });
            }
            
            if (remoteDB.enabled !== localDB.enabled) {
                hasChanges = true;
                changes.push({
                    id: localDB.$id,
                    action: chalk.yellow('updating'),
                    key: 'Enabled',
                    remote: remoteDB.enabled,
                    local: localDB.enabled
                });
            }
            
            if (hasChanges) {
                toUpdate.push(localDB);
            }
        }
    }

    if (changes.length === 0) {
        console.log('No changes found in tablesDB resource');
        console.log();
        return { applied: false, resyncNeeded: false };
    }

    log('Found changes in tablesDB resource:');
    drawTable(changes);

    if (toDelete.length > 0) {
        console.log(`${chalk.red('------------------------------------------------------------------')}`);
        console.log(`${chalk.red('| WARNING: Database deletion will also delete all related tables |')}`);
        console.log(`${chalk.red('------------------------------------------------------------------')}`);
        console.log();
    }

    if ((await getConfirmation()) !== true) {
        return { applied: false, resyncNeeded: false };
    }

    // Apply deletions first
    let needsResync = false;
    for (const db of toDelete) {
        try {
            log(`Deleting database ${db.name} ( ${db.$id} ) ...`);
            await tablesDBDelete({
                databaseId: db.$id,
                parseOutput: false
            });
            success(`Deleted ${db.name} ( ${db.$id} )`);
            needsResync = true;
        } catch (e: any) {
            error(`Failed to delete database ${db.name} ( ${db.$id} ): ${e.message}`);
            throw new Error(`Database sync failed during deletion of ${db.$id}. Some changes may have been applied.`);
        }
    }

    // Apply creations
    for (const db of toCreate) {
        try {
            log(`Creating database ${db.name} ( ${db.$id} ) ...`);
            await tablesDBCreate({
                databaseId: db.$id,
                name: db.name,
                enabled: db.enabled,
                parseOutput: false
            });
            success(`Created ${db.name} ( ${db.$id} )`);
        } catch (e: any) {
            error(`Failed to create database ${db.name} ( ${db.$id} ): ${e.message}`);
            throw new Error(`Database sync failed during creation of ${db.$id}. Some changes may have been applied.`);
        }
    }

    // Apply updates
    for (const db of toUpdate) {
        try {
            log(`Updating database ${db.name} ( ${db.$id} ) ...`);
            await tablesDBUpdate({
                databaseId: db.$id,
                name: db.name,
                enabled: db.enabled,
                parseOutput: false
            });
            success(`Updated ${db.name} ( ${db.$id} )`);
        } catch (e: any) {
            error(`Failed to update database ${db.name} ( ${db.$id} ): ${e.message}`);
            throw new Error(`Database sync failed during update of ${db.$id}. Some changes may have been applied.`);
        }
    }

    if (toDelete.length === 0){
        console.log();
    }

    return { applied: true, resyncNeeded: needsResync };
};

interface PushTableOptions {
    returnOnZero?: boolean;
    attempts?: number;
}

const pushTable = async ({ returnOnZero, attempts }: PushTableOptions = { returnOnZero: false }): Promise<void> => {
    const tables: any[] = [];

    if (attempts) {
        pollMaxDebounces = attempts;
    }

    const { applied: tablesDBApplied, resyncNeeded } = await checkAndApplyTablesDBChanges();
    if (resyncNeeded) {
        log('Resyncing configuration due to tablesDB deletions ...');

        const remoteTablesDBs = (await paginate(tablesDBList, { parseOutput: false }, 100, 'databases')).databases;
        const localTablesDBs = localConfig.getTablesDBs();

        const remoteDatabaseIds = new Set(remoteTablesDBs.map((db: any) => db.$id));
        const localTables = localConfig.getTables();
        const validTables = localTables.filter((table: any) => remoteDatabaseIds.has(table.databaseId));

        localConfig.set('tables', validTables);

        const validTablesDBs = localTablesDBs.filter((db: any) => remoteDatabaseIds.has(db.$id));
        localConfig.set('tablesDB', validTablesDBs);

        success('Configuration resynced successfully.');
        console.log();
    }

    log('Checking for deleted tables ...');
    const localTablesDBs = localConfig.getTablesDBs();
    const localTables = localConfig.getTables();
    const tablesToDelete: any[] = [];

    for (const db of localTablesDBs) {
        try {
            const { tables: remoteTables } = await paginate(tablesDBListTables, {
                databaseId: db.$id,
                parseOutput: false
            }, 100, 'tables');

            for (const remoteTable of remoteTables) {
                const localTable = localTables.find((t: any) => t.$id === remoteTable.$id && t.databaseId === db.$id);
                if (!localTable) {
                    tablesToDelete.push({
                        ...remoteTable,
                        databaseId: db.$id,
                        databaseName: db.name
                    });
                }
            }
        } catch (e) {
            // Skip if database doesn't exist or other errors
        }
    }

    if (tablesToDelete.length > 0) {
        log('Found tables that exist remotely but not locally:');
        const deletionChanges = tablesToDelete.map((table: any) => ({
            id: table.$id,
            action: chalk.red('deleting'),
            key: 'Table',
            database: table.databaseName,
            remote: table.name,
            local: '(deleted locally)'
        }));
        drawTable(deletionChanges);

        if ((await getConfirmation()) === true) {
            for (const table of tablesToDelete) {
                try {
                    log(`Deleting table ${table.name} ( ${table.$id} ) from database ${table.databaseName} ...`);
                    await tablesDBDeleteTable({
                        databaseId: table.databaseId,
                        tableId: table.$id,
                        parseOutput: false
                    });
                    success(`Deleted ${table.name} ( ${table.$id} )`);
                } catch (e: any) {
                    error(`Failed to delete table ${table.name} ( ${table.$id} ): ${e.message}`);
                }
            }
        }
    } else {
        console.log('No tables found to delete');
    }
    console.log();

    if (cliConfig.all) {
        checkDeployConditions(localConfig);
        tables.push(...localConfig.getTables());
    } else {
        const answers = await inquirer.prompt(questionsPushTables)
        if (answers.tables) {
            const configTables = new Map();
            localConfig.getTables().forEach((c: any) => {
                configTables.set(`${c['databaseId']}|${c['$id']}`, c);
            });
            answers.tables.forEach((a: any) => {
                const table = configTables.get(a);
                tables.push(table);
            })
        }
    }

    if (tables.length === 0) {
        log("No tables found.");
        hint("Use 'appwrite pull tables' to synchronize existing one, or use 'appwrite init table' to create a new one.");
        return;
    }

    if (!(await approveChanges(tables, tablesDBGetTable, KeysTable, 'tableId', 'tables', ['columns', 'indexes'], 'databaseId', 'databaseId'))) {
        return;
    }
    let tablesChanged = new Set();

    // Parallel tables actions
    await Promise.all(tables.map(async (table: any) => {
        try {
            const remoteTable = await tablesDBGetTable({
                databaseId: table['databaseId'],
                tableId: table['$id'],
                parseOutput: false,
            });

            const changes: string[] = [];
            if (remoteTable.name !== table.name) changes.push('name');
            if (remoteTable.rowSecurity !== table.rowSecurity) changes.push('rowSecurity');
            if (remoteTable.enabled !== table.enabled) changes.push('enabled');
            if (JSON.stringify(remoteTable['$permissions']) !== JSON.stringify(table['$permissions'])) changes.push('permissions');

            if (changes.length > 0) {
                await tablesDBUpdateTable({
                    databaseId: table['databaseId'],
                    tableId: table['$id'],
                    name: table.name,
                    parseOutput: false,
                    rowSecurity: table.rowSecurity,
                    permissions: table['$permissions']
                })

                success(`Updated ${table.name} ( ${table['$id']} ) - ${changes.join(', ')}`);
                tablesChanged.add(table['$id']);
            }
            table.remoteVersion = remoteTable;

            table.isExisted = true;
        } catch (e: any) {
            if (Number(e.code) === 404) {
                log(`Table ${table.name} does not exist in the project. Creating ... `);
                await tablesDBCreateTable({
                    databaseId: table['databaseId'],
                    tableId: table['$id'],
                    name: table.name,
                    rowSecurity: table.rowSecurity,
                    permissions: table['$permissions'],
                    parseOutput: false
                })

                success(`Created ${table.name} ( ${table['$id']} )`);
                tablesChanged.add(table['$id']);
            } else {
                throw e;
            }
        }
    }))

    // Serialize attribute actions
    for (let table of tables) {
        let columns = table.columns;
        let indexes = table.indexes;

        if (table.isExisted) {
            columns = await attributesToCreate(table.remoteVersion.columns, table.columns, table);
            indexes = await attributesToCreate(table.remoteVersion.indexes, table.indexes, table, true);

            if ((Array.isArray(columns) && columns.length <= 0) && (Array.isArray(indexes) && indexes.length <= 0)) {
                continue;
            }
        }

        log(`Pushing table ${table.name} ( ${table['databaseId']} - ${table['$id']} ) attributes`)

        try {
            await createColumns(columns, table)
        } catch (e) {
            throw e;
        }

        try {
            await createIndexes(indexes, table);
        } catch (e) {
            throw e;
        }
        tablesChanged.add(table['$id']);
        success(`Successfully pushed ${table.name} ( ${table['$id']} )`);
    }

    success(`Successfully pushed ${tablesChanged.size} tables`);
}

interface PushCollectionOptions {
    returnOnZero?: boolean;
    attempts?: number;
}

const pushCollection = async ({ returnOnZero, attempts }: PushCollectionOptions = { returnOnZero: false }): Promise<void> => {
    warn("appwrite push collection has been deprecated. Please consider using 'appwrite push tables' instead");
    const collections: any[] = [];

    if (attempts) {
        pollMaxDebounces = attempts;
    }

    if (cliConfig.all) {
        checkDeployConditions(localConfig);
        collections.push(...localConfig.getCollections());
    } else {
        const answers = await inquirer.prompt(questionsPushCollections)
        if (answers.collections) {
            const configCollections = new Map();
            localConfig.getCollections().forEach((c: any) => {
                configCollections.set(`${c['databaseId']}|${c['$id']}`, c);
            });
            answers.collections.forEach((a: any) => {
                const collection = configCollections.get(a);
                collections.push(collection);
            })
        }
    }

    if (collections.length === 0) {
        log("No collections found.");
        hint("Use 'appwrite pull collections' to synchronize existing one, or use 'appwrite init collection' to create a new one.");
        return;
    }

    const databases = Array.from(new Set(collections.map((collection: any) => collection['databaseId'])));

    // Parallel db actions
    await Promise.all(databases.map(async (databaseId: any) => {
        const localDatabase = localConfig.getDatabase(databaseId);

        try {
            const database = await databasesGet({
                databaseId: databaseId,
                parseOutput: false,
            });

            if (database.name !== (localDatabase.name ?? databaseId)) {
                await databasesUpdate({
                    databaseId: databaseId,
                    name: localDatabase.name ?? databaseId,
                    parseOutput: false
                })

                success(`Updated ${localDatabase.name} ( ${databaseId} ) name`);
            }
        } catch (err) {
            log(`Database ${databaseId} not found. Creating it now ...`);

            await databasesCreate({
                databaseId: databaseId,
                name: localDatabase.name ?? databaseId,
                parseOutput: false,
            });
        }
    }));

    if (!(await approveChanges(collections, databasesGetCollection, KeysCollection, 'collectionId', 'collections', ['attributes', 'indexes'], 'databaseId', 'databaseId',))) {
        return;
    }
    // Parallel collection actions
    await Promise.all(collections.map(async (collection: any) => {
        try {
            const remoteCollection = await databasesGetCollection({
                databaseId: collection['databaseId'],
                collectionId: collection['$id'],
                parseOutput: false,
            });

            if (remoteCollection.name !== collection.name) {
                await databasesUpdateCollection({
                    databaseId: collection['databaseId'],
                    collectionId: collection['$id'],
                    name: collection.name,
                    parseOutput: false
                })

                success(`Updated ${collection.name} ( ${collection['$id']} ) name`);
            }
            collection.remoteVersion = remoteCollection;

            collection.isExisted = true;
        } catch (e: any) {
            if (Number(e.code) === 404) {
                log(`Collection ${collection.name} does not exist in the project. Creating ... `);
                await databasesCreateCollection({
                    databaseId: collection['databaseId'],
                    collectionId: collection['$id'],
                    name: collection.name,
                    documentSecurity: collection.documentSecurity,
                    permissions: collection['$permissions'],
                    parseOutput: false
                })
            } else {
                throw e;
            }
        }
    }))
    let numberOfCollections = 0;
    // Serialize attribute actions
    for (let collection of collections) {
        let attributes = collection.attributes;
        let indexes = collection.indexes;

        if (collection.isExisted) {
            attributes = await attributesToCreate(collection.remoteVersion.attributes, collection.attributes, collection);
            indexes = await attributesToCreate(collection.remoteVersion.indexes, collection.indexes, collection, true);

            if ((Array.isArray(attributes) && attributes.length <= 0) && (Array.isArray(indexes) && indexes.length <= 0)) {
                continue;
            }

        }

        log(`Pushing collection ${collection.name} ( ${collection['databaseId']} - ${collection['$id']} ) attributes`)

        try {
            await createAttributes(attributes, collection)
        } catch (e) {
            throw e;
        }

        try {
            await createIndexes(indexes, collection);
        } catch (e) {
            throw e;
        }
        numberOfCollections++;
        success(`Successfully pushed ${collection.name} ( ${collection['$id']} )`);
    }

    success(`Successfully pushed ${numberOfCollections} collections`);
}

interface PushBucketOptions {
    returnOnZero?: boolean;
}

const pushBucket = async ({ returnOnZero }: PushBucketOptions = { returnOnZero: false }): Promise<void> => {
    let response: any = {};

    let bucketIds: string[] = [];
    const configBuckets = localConfig.getBuckets();

    if (cliConfig.all) {
        checkDeployConditions(localConfig);
        bucketIds.push(...configBuckets.map((b: any) => b.$id));
    }

    if (bucketIds.length === 0) {
        const answers = await inquirer.prompt(questionsPushBuckets)
        if (answers.buckets) {
            bucketIds.push(...answers.buckets);
        }
    }

    if (bucketIds.length === 0) {
        log("No buckets found.");
        hint("Use 'appwrite pull buckets' to synchronize existing one, or use 'appwrite init bucket' to create a new one.");
        return;
    }

    let buckets: any[] = [];

    for (const bucketId of bucketIds) {
        const idBuckets = configBuckets.filter((b: any) => b.$id === bucketId);
        buckets.push(...idBuckets);
    }

    if (!(await approveChanges(buckets, storageGetBucket, KeysStorage, 'bucketId', 'buckets'))) {
        return;
    }

    log('Pushing buckets ...');

    for (let bucket of buckets) {
        log(`Pushing bucket ${chalk.bold(bucket['name'])} ...`);

        try {
            response = await storageGetBucket({
                bucketId: bucket['$id'],
                parseOutput: false,
            })

            await storageUpdateBucket({
                bucketId: bucket['$id'],
                name: bucket.name,
                permissions: bucket['$permissions'],
                fileSecurity: bucket.fileSecurity,
                enabled: bucket.enabled,
                maximumFileSize: bucket.maximumFileSize,
                allowedFileExtensions: bucket.allowedFileExtensions,
                encryption: bucket.encryption,
                antivirus: bucket.antivirus,
                compression: bucket.compression,
                parseOutput: false
            });
        } catch (e: any) {
            if (Number(e.code) === 404) {
                log(`Bucket ${bucket.name} does not exist in the project. Creating ... `);

                response = await storageCreateBucket({
                    bucketId: bucket['$id'],
                    name: bucket.name,
                    permissions: bucket['$permissions'],
                    fileSecurity: bucket.fileSecurity,
                    enabled: bucket.enabled,
                    maximumFileSize: bucket.maximumFileSize,
                    allowedFileExtensions: bucket.allowedFileExtensions,
                    compression: bucket.compression,
                    encryption: bucket.encryption,
                    antivirus: bucket.antivirus,
                    parseOutput: false
                })
            } else {
                throw e;
            }
        }
    }

    success(`Successfully pushed ${buckets.length} buckets.`);
}

interface PushTeamOptions {
    returnOnZero?: boolean;
}

const pushTeam = async ({ returnOnZero }: PushTeamOptions = { returnOnZero: false }): Promise<void> => {
    let response: any = {};

    let teamIds: string[] = [];
    const configTeams = localConfig.getTeams();

    if (cliConfig.all) {
        checkDeployConditions(localConfig);
        teamIds.push(...configTeams.map((t: any) => t.$id));
    }

    if (teamIds.length === 0) {
        const answers = await inquirer.prompt(questionsPushTeams)
        if (answers.teams) {
            teamIds.push(...answers.teams);
        }
    }

    if (teamIds.length === 0) {
        log("No teams found.");
        hint("Use 'appwrite pull teams' to synchronize existing one, or use 'appwrite init team' to create a new one.");
        return;
    }

    let teams: any[] = [];

    for (const teamId of teamIds) {
        const idTeams = configTeams.filter((t: any) => t.$id === teamId);
        teams.push(...idTeams);
    }

    if (!(await approveChanges(teams, teamsGet, KeysTeams, 'teamId', 'teams'))) {
        return;
    }

    log('Pushing teams ...');

    for (let team of teams) {
        log(`Pushing team ${chalk.bold(team['name'])} ...`);

        try {
            response = await teamsGet({
                teamId: team['$id'],
                parseOutput: false,
            })

            await teamsUpdateName({
                teamId: team['$id'],
                name: team.name,
                parseOutput: false
            });
        } catch (e: any) {
            if (Number(e.code) === 404) {
                log(`Team ${team.name} does not exist in the project. Creating ... `);

                response = await teamsCreate({
                    teamId: team['$id'],
                    name: team.name,
                    parseOutput: false
                })
            } else {
                throw e;
            }
        }
    }

    success(`Successfully pushed ${teams.length} teams.`);
}

interface PushMessagingTopicOptions {
    returnOnZero?: boolean;
}

const pushMessagingTopic = async ({ returnOnZero }: PushMessagingTopicOptions = { returnOnZero: false }): Promise<void> => {
    let response: any = {};

    let topicsIds: string[] = [];
    const configTopics = localConfig.getMessagingTopics();

    if (cliConfig.all) {
        checkDeployConditions(localConfig);
        topicsIds.push(...configTopics.map((b: any) => b.$id));
    }

    if (topicsIds.length === 0) {
        const answers = await inquirer.prompt(questionsPushMessagingTopics)
        if (answers.topics) {
            topicsIds.push(...answers.topics);
        }
    }

    if (topicsIds.length === 0) {
        log("No topics found.");
        hint("Use 'appwrite pull topics' to synchronize existing one, or use 'appwrite init topic' to create a new one.");
        return;
    }

    let topics: any[] = [];

    for (const topicId of topicsIds) {
        const idTopic = configTopics.filter((b: any) => b.$id === topicId);
        topics.push(...idTopic);
    }

    if (!(await approveChanges(topics, messagingGetTopic, KeysTopics, 'topicId', 'topics'))) {
        return;
    }

    log('Pushing topics ...');

    for (let topic of topics) {
        log(`Pushing topic ${chalk.bold(topic['name'])} ...`);

        try {
            response = await messagingGetTopic({
                topicId: topic['$id'],
                parseOutput: false
            })
            log(`Topic ${topic.name} ( ${topic['$id']} ) already exists.`);

            await messagingUpdateTopic({
                topicId: topic['$id'],
                name: topic.name,
                subscribe: topic.subscribe,
                parseOutput: false
            });
        } catch (e: any) {
            if (Number(e.code) === 404) {
                log(`Topic ${topic.name} does not exist in the project. Creating ... `);

                response = await messagingCreateTopic({
                    topicId: topic['$id'],
                    name: topic.name,
                    subscribe: topic.subscribe,
                    parseOutput: false
                })

                success(`Created ${topic.name} ( ${topic['$id']} )`);
            } else {
                throw e;
            }
        }
    }

    success(`Successfully pushed ${topics.length} topics.`);
}

export const push = new Command("push")
    .description(commandDescriptions['push'])
    .action(actionRunner(pushResources));

push
    .command("all")
    .description("Push all resource.")
    .action(actionRunner(() => {
        cliConfig.all = true;
        return pushResources();
    }));

push
    .command("settings")
    .description("Push project name, services and auth settings")
    .action(actionRunner(pushSettings));

push
    .command("function")
    .alias("functions")
    .description("Push functions in the current directory.")
    .option(`-f, --function-id <function-id>`, `ID of function to run`)
    .option(`-A, --async`, `Don't wait for functions deployments status`)
    .option("--no-code", "Don't push the function's code")
    .option("--with-variables", `Push function variables.`)
    .action(actionRunner(pushFunction));

push
    .command("site")
    .alias("sites")
    .description("Push sites in the current directory.")
    .option(`-f, --site-id <site-id>`, `ID of site to run`)
    .option(`-A, --async`, `Don't wait for sites deployments status`)
    .option("--no-code", "Don't push the site's code")
    .option("--with-variables", `Push site variables.`)
    .action(actionRunner(pushSite));

push
    .command("collection")
    .alias("collections")
    .description("Push collections in the current project. (deprecated, please use 'push tables' instead)")
    .option(`-a, --attempts <numberOfAttempts>`, `Max number of attempts before timing out. default: 30.`)
    .action(actionRunner(pushCollection));

push
    .command("table")
    .alias("tables")
    .description("Push tables in the current project.")
    .option(`-a, --attempts <numberOfAttempts>`, `Max number of attempts before timing out. default: 30.`)
    .action(actionRunner(pushTable));

push
    .command("bucket")
    .alias("buckets")
    .description("Push buckets in the current project.")
    .action(actionRunner(pushBucket));

push
    .command("team")
    .alias("teams")
    .description("Push teams in the current project.")
    .action(actionRunner(pushTeam));

push
    .command("topic")
    .alias("topics")
    .description("Push messaging topics in the current project.")
    .action(actionRunner(pushMessagingTopic));

export const deploy = new Command("deploy")
    .description('Removed. Use appwrite push instead')
    .action(actionRunner(async () => {
        warn("appwrite deploy has been removed. Please use 'appwrite push' instead");
    }));
