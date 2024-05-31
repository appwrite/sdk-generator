import json


# Inherit from dict to allow for easy serialization
class Query():
    def __init__(self, method, attribute=None, values=None):
        self.method = method

        if attribute is not None:
            self.attribute = attribute

        if values is not None:
            self.values = values if isinstance(values, list) else [values]

    def __str__(self):
        return json.dumps(
            self.__dict__,
            separators=(",", ":"),
            default=lambda obj: obj.__dict__
        )

    @staticmethod
    def equal(attribute, value):
        return str(Query("equal", attribute, value))

    @staticmethod
    def not_equal(attribute, value):
        return str(Query("notEqual", attribute, value))

    @staticmethod
    def less_than(attribute, value):
        return str(Query("lessThan", attribute, value))

    @staticmethod
    def less_than_equal(attribute, value):
        return str(Query("lessThanEqual", attribute, value))

    @staticmethod
    def greater_than(attribute, value):
        return str(Query("greaterThan", attribute, value))

    @staticmethod
    def greater_than_equal(attribute, value):
        return str(Query("greaterThanEqual", attribute, value))

    @staticmethod
    def is_null(attribute):
        return str(Query("isNull", attribute, None))

    @staticmethod
    def is_not_null(attribute):
        return str(Query("isNotNull", attribute, None))

    @staticmethod
    def between(attribute, start, end):
        return str(Query("between", attribute, [start, end]))

    @staticmethod
    def starts_with(attribute, value):
        return str(Query("startsWith", attribute, value))

    @staticmethod
    def ends_with(attribute, value):
        return str(Query("endsWith", attribute, value))

    @staticmethod
    def select(attributes):
        return str(Query("select", None, attributes))

    @staticmethod
    def search(attribute, value):
        return str(Query("search", attribute, value))

    @staticmethod
    def order_asc(attribute):
        return str(Query("orderAsc", attribute, None))

    @staticmethod
    def order_desc(attribute):
        return str(Query("orderDesc", attribute, None))

    @staticmethod
    def cursor_before(id):
        return str(Query("cursorBefore", None, id))

    @staticmethod
    def cursor_after(id):
        return str(Query("cursorAfter", None, id))

    @staticmethod
    def limit(limit):
        return str(Query("limit", None, limit))

    @staticmethod
    def offset(offset):
        return str(Query("offset", None, offset))

    @staticmethod
    def contains(attribute, value):
        return str(Query("contains", attribute, value))

    @staticmethod
    def or_queries(queries):
        return str(Query("or", None, [json.loads(query) for query in queries]))

    @staticmethod
    def and_queries(queries):
        return str(Query("and", None, [json.loads(query) for query in queries]))
