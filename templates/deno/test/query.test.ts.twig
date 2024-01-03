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
                    Query.equal("attr", t.value),
                    `equal("attr", ${t.expectedValues})`,
                )
            )
        }
    })

    describe('basic filter notEqual', () => {
        for (const t of tests) {
            test(t.description, () =>
                assertEquals(
                    Query.notEqual("attr", t.value),
                    `notEqual("attr", ${t.expectedValues})`,
                )
            )
        }
    });

    describe('basic filter lessThan', () => {
        for (const t of tests) {
            test(t.description, () =>
                assertEquals(
                    Query.lessThan("attr", t.value),
                    `lessThan("attr", ${t.expectedValues})`,
                )
            )
        }
    });

    describe('basic filter lessThanEqual', () => {
        for (const t of tests) {
            test(t.description, () =>
                assertEquals(
                    Query.lessThanEqual("attr", t.value),
                    `lessThanEqual("attr", ${t.expectedValues})`,
                )
            )
        }
    });

    describe('basic filter greaterThan', () => {
        for (const t of tests) {
            test(t.description, () =>
                assertEquals(
                    Query.greaterThan("attr", t.value),
                    `greaterThan("attr", ${t.expectedValues})`,
                )
            )
        }
    });

    describe('basic filter greaterThanEqual', () => {
        for (const t of tests) {
            test(t.description, () =>
                assertEquals(
                    Query.greaterThanEqual("attr", t.value),
                    `greaterThanEqual("attr", ${t.expectedValues})`,
                )
            )
        }
    });

    test('search', () => assertEquals(
        Query.search('attr', 'keyword1 keyword2'),
        'search("attr", ["keyword1 keyword2"])',
    ));

    test('isNull', () => assertEquals(
        Query.isNull('attr'),
        'isNull("attr")',
    ));

    test('isNotNull', () => assertEquals(
        Query.isNotNull('attr'),
        'isNotNull("attr")',
    ));

    describe('between', () => {
        test('with integers', () => assertEquals(
            Query.between('attr', 1, 2),
            'between("attr", 1, 2)'
        ));
        test('with doubles', () => assertEquals(
            Query.between('attr', 1.2, 2.2),
            'between("attr", 1.2, 2.2)'
        ));
        test('with strings', () => assertEquals(
            Query.between('attr', "a", "z"),
            'between("attr", "a", "z")'
        ));
    });

    test('select', () => assertEquals(
        Query.select(['attr1', 'attr2']),
        'select(["attr1","attr2"])',
    ));

    test('orderAsc', () => assertEquals(
        Query.orderAsc('attr'),
        'orderAsc("attr")',
    ));

    test('orderDesc', () => assertEquals(
        Query.orderDesc('attr'),
        'orderDesc("attr")',
    ));

    test('cursorBefore', () => assertEquals(
        Query.cursorBefore('attr'),
        'cursorBefore("attr")',
    ));

    test('cursorAfter', () => assertEquals(
        Query.cursorAfter('attr'),
        'cursorAfter("attr")',
    ));

    test('limit', () => assertEquals(
        Query.limit(1),
        'limit(1)'
    ));

    test('offset', () => assertEquals(
        Query.offset(1),
        'offset(1)'
    ));
})
