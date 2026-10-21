const { Topic } = require('../dist/topic');

describe('Topic', () => {
    test('path', () => {
        expect(Topic.path(['user']).toString()).toEqual('user');
        expect(Topic.path(['user', 'notification']).toString()).toEqual(
            'user/notification',
        );
        expect(Topic.path(['user']).path(['notification']).toString()).toEqual(
            'user/notification',
        );
        expect(Topic.path(['org', '42']).path(['logs']).toString()).toEqual(
            'org/42/logs',
        );
    });
    test.each([
        ['an empty path', []],
        ['an empty level', ['user', '']],
        ['a slash', ['user/123']],
        ['a lone +', ['user', '+']],
        ['an embedded +', ['user', 'a+b']],
        ['a lone #', ['user', '#']],
        ['an embedded #', ['user', 'a#b']],
        ['a NUL character', ['user', 'a\u0000b']],
        ['a non-string level', ['user', null]],
        ['a non-array', 'user'],
    ])('rejects %s', (_, levels) => {
        expect(() => Topic.path(levels)).toThrow(Error);
        expect(() => Topic.path(['user']).path(levels)).toThrow(Error);
    });
});
