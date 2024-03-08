import {describe, it as test} from "https://deno.land/std@0.149.0/testing/bdd.ts";
import {assertEquals} from "https://deno.land/std@0.204.0/assert/assert_equals.ts";
import {Query, QueryTypes} from "../src/query.ts";

type BasicFilterQueryTest = {
    description: string;
    value: QueryTypes;
    expectedValues: string;
}

const tests: BasicFilterQueryTest[] = [
    {
        description: 'with a string',
        value: 's',
        expectedValues: '["s"]'
    },
    {
        description: 'with a integer',
        value: 1,
        expectedValues: '[1]'
    },
    {
        description: 'with a double',
        value: 1.2,
        expectedValues: '[1.2]'
    },
    {
        description: 'with a whole number double',
        value: 1.0,
        expectedValues: '[1]'
    },
    {
        description: 'with a bool',
        value: false,
        expectedValues: '[false]'
    },
    {
        description: 'with a list',
        value: ['a', 'b', 'c'],
        expectedValues: '["a","b","c"]'
    }
];

describe('Query', () => {
    describe('basic filter equal', () => {
        for (const t of tests) {
            test(t.description, () =>
                assertEquals(
                    Query.equal("attr", t.value).toString(),
                    `{"method":"equal","attribute":"attr","values":${t.expectedValues}}`,
                )
            )
        }
    })

    describe('basic filter notEqual', () => {
        for (const t of tests) {
            test(t.description, () =>
                assertEquals(
                    Query.notEqual("attr", t.value).toString(),
                    `{"method":"notEqual","attribute":"attr","values":${t.expectedValues}}`,
                )
            )
        }
    });

    describe('basic filter lessThan', () => {
        for (const t of tests) {
            test(t.description, () =>
                assertEquals(
                    Query.lessThan("attr", t.value).toString(),
                    `{"method":"lessThan","attribute":"attr","values":${t.expectedValues}}`,
                )
            )
        }
    });

    describe('basic filter lessThanEqual', () => {
        for (const t of tests) {
            test(t.description, () =>
                assertEquals(
                    Query.lessThanEqual("attr", t.value).toString(),
                    `{"method":"lessThanEqual","attribute":"attr","values":${t.expectedValues}}`,
                )
            )
        }
    });

    describe('basic filter greaterThan', () => {
        for (const t of tests) {
            test(t.description, () =>
                assertEquals(
                    Query.greaterThan("attr", t.value).toString(),
                    `{"method":"greaterThan","attribute":"attr","values":${t.expectedValues}}`,
                )
            )
        }
    });

    describe('basic filter greaterThanEqual', () => {
        for (const t of tests) {
            test(t.description, () =>
                assertEquals(
                    Query.greaterThanEqual("attr", t.value).toString(),
                    `{"method":"greaterThanEqual","attribute":"attr","values":${t.expectedValues}}`,
                )
            )
        }
    });

    test('search', () => assertEquals(
        Query.search('attr', 'keyword1 keyword2').toString(),
        '{"method":"search","attribute":"attr","values":["keyword1 keyword2"]}',
    ));

    test('isNull', () => assertEquals(
        Query.isNull('attr').toString(),
        `{"method":"isNull","attribute":"attr","values":[]}`,
    ));

    test('isNotNull', () => assertEquals(
        Query.isNotNull('attr').toString(),
        `{"method":"isNotNull","attribute":"attr","values":[]}`,
    ));

    describe('between', () => {
        test('with integers', () => assertEquals(
            Query.between('attr', 1, 2).toString(),
            `{"method":"between","attribute":"attr","values":[1,2]}`,
        ));
        test('with doubles', () => assertEquals(
            Query.between('attr', 1.2, 2.2).toString(),
            `{"method":"between","attribute":"attr","values":[1.2,2.2]}`,
        ));
        test('with strings', () => assertEquals(
            Query.between('attr', "a", "z").toString(),
            `{"method":"between","attribute":"attr","values":["a","z"]}`,
        ));
    });

    test('select', () => assertEquals(
        Query.select(['attr1', 'attr2']).toString(),
        `{"method":"select","attribute":["attr1","attr2"]}`,
    ));

    test('orderAsc', () => assertEquals(
        Query.orderAsc('attr').toString(),
        `{"method":"orderAsc","attribute":"attr"}`,
    ));

    test('orderDesc', () => assertEquals(
        Query.orderDesc('attr').toString(),
        `{"method":"orderDesc","attribute":"attr"}`,
    ));

    test('cursorBefore', () => assertEquals(
        Query.cursorBefore('attr').toString(),
        `{"method":"cursorBefore","attribute":"attr"}`,
    ));

    test('cursorAfter', () => assertEquals(
        Query.cursorAfter('attr').toString(),
        `{"method":"cursorAfter","attribute":"attr"}`,
    ));

    test('limit', () => assertEquals(
        Query.limit(1).toString(),
        `{"method":"limit","values":[1]}`,
    ));

    test('offset', () => assertEquals(
        Query.offset(1).toString(),
        `{"method":"offset","values":[1]}`,
    ));
})
