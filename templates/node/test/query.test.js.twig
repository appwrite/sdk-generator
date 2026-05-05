const { Query } = require("../dist/query");

const tests = [
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
                expect(Query.equal("attr", t.value))
                    .toEqual(`{"method":"equal","attribute":"attr","values":${t.expectedValues}}`)
            )
        }
    })

    describe('basic filter notEqual', () => {
        for (const t of tests) {
            test(t.description, () =>
                expect(Query.notEqual("attr", t.value))
                    .toEqual(`{"method":"notEqual","attribute":"attr","values":${t.expectedValues}}`)
            )
        }
    });

    describe('basic filter lessThan', () => {
        for (const t of tests) {
            test(t.description, () =>
                expect(Query.lessThan("attr", t.value))
                    .toEqual(`{"method":"lessThan","attribute":"attr","values":${t.expectedValues}}`)
            )
        }
    });

    describe('basic filter lessThanEqual', () => {
        for (const t of tests) {
            test(t.description, () =>
                expect(Query.lessThanEqual("attr", t.value))
                    .toEqual(`{"method":"lessThanEqual","attribute":"attr","values":${t.expectedValues}}`)
            )
        }
    });

    describe('basic filter greaterThan', () => {
        for (const t of tests) {
            test(t.description, () =>
                expect(Query.greaterThan("attr", t.value))
                    .toEqual(`{"method":"greaterThan","attribute":"attr","values":${t.expectedValues}}`)
            )
        }
    });

    describe('basic filter greaterThanEqual', () => {
        for (const t of tests) {
            test(t.description, () =>
                expect(Query.greaterThanEqual("attr", t.value))
                    .toEqual(`{"method":"greaterThanEqual","attribute":"attr","values":${t.expectedValues}}`)
            )
        }
    });

    test('search', () =>
        expect(Query.search('attr', 'keyword1 keyword2'))
            .toEqual(`{"method":"search","attribute":"attr","values":["keyword1 keyword2"]}`)
    );

    test('isNull', () =>
        expect(Query.isNull('attr'))
            .toEqual(`{"method":"isNull","attribute":"attr"}`)
    );

    test('isNotNull', () =>
        expect(Query.isNotNull('attr'))
            .toEqual(`{"method":"isNotNull","attribute":"attr"}`)
    );

    describe('between', () => {
        test('with integers', () =>
            expect(Query.between('attr', 1, 2))
                .toEqual(`{"method":"between","attribute":"attr","values":[1,2]}`)
        );
        test('with doubles', () =>
            expect(Query.between('attr', 1.2, 2.2))
                .toEqual(`{"method":"between","attribute":"attr","values":[1.2,2.2]}`)
        );
        test('with strings', () =>
            expect(Query.between('attr',"a","z"))
                .toEqual(`{"method":"between","attribute":"attr","values":["a","z"]}`)
        );
    });

    test('select', () =>
        expect(Query.select(['attr1', 'attr2']))
            .toEqual(`{"method":"select","values":["attr1","attr2"]}`)
    );

    test('orderAsc', () =>
        expect(Query.orderAsc('attr'))
            .toEqual(`{"method":"orderAsc","attribute":"attr"}`)
    );

    test('orderDesc', () =>
        expect(Query.orderDesc('attr'))
            .toEqual(`{"method":"orderDesc","attribute":"attr"}`)
    );

    test('cursorBefore', () =>
        expect(Query.cursorBefore('attr'))
            .toEqual('{"method":"cursorBefore","values":["attr"]}')
    );

    test('cursorAfter', () =>
        expect(Query.cursorAfter('attr'))
            .toEqual('{"method":"cursorAfter","values":["attr"]}')
    );

    test('limit', () =>
        expect(Query.limit(1))
            .toEqual('{"method":"limit","values":[1]}')
    );

    test('offset', () =>
        expect(Query.offset(1))
            .toEqual('{"method":"offset","values":[1]}')
    );
})
