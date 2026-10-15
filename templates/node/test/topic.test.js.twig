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
    });
    test('any', () => {
        expect(
            Topic.path(['user']).any().path(['notification']).toString(),
        ).toEqual('user/+/notification');
        expect(
            Topic.path(['chat']).any().any().path(['message']).toString(),
        ).toEqual('chat/+/+/message');
        expect(Topic.any().path(['notification']).toString()).toEqual(
            '+/notification',
        );
    });
    test('all', () => {
        expect(
            Topic.path(['org']).any().path(['logs']).all().toString(),
        ).toEqual('org/+/logs/#');
        expect(Topic.all().toString()).toEqual('#');
    });
    test.each([
        ['an empty path', []],
        ['an empty level', ['user', '']],
        ['a slash', ['user/123']],
        ['a lone +', ['user', '+']],
        ['an embedded +', ['user', 'a+b']],
        ['a lone #', ['user', '#']],
        ['an embedded #', ['user', 'a#b']],
        ['a non-string level', ['user', null]],
        ['a non-array', 'user'],
    ])('rejects %s', (_, levels) => {
        expect(() => Topic.path(levels)).toThrow(Error);
        expect(() => Topic.any().path(levels)).toThrow(Error);
    });
});
