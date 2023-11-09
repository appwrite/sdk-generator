class Query:
    @staticmethod
    def equal(attribute, value):
        return Query.add_query(attribute, "equal", value)

    @staticmethod
    def not_equal(attribute, value):
        return Query.add_query(attribute, "notEqual", value)
    
    @staticmethod
    def less_than(attribute, value):
        return Query.add_query(attribute, "lessThan", value)
    
    @staticmethod
    def less_than_equal(attribute, value):
        return Query.add_query(attribute, "lessThanEqual", value)
    
    @staticmethod
    def greater_than(attribute, value):
        return Query.add_query(attribute, "greaterThan", value)
    
    @staticmethod
    def greater_than_equal(attribute, value):
        return Query.add_query(attribute, "greaterThanEqual", value)

    @staticmethod
    def is_null(attribute):
        return f'isNull("{attribute}")'

    @staticmethod
    def is_not_null(attribute):
        return f'isNotNull("{attribute}")'

    @staticmethod
    def between(attribute, start, end):
        return f'between("{attribute}", {Query.parseValues(start)}, {Query.parseValues(end)})'

    @staticmethod
    def starts_with(attribute, value):
        return Query.add_query(attribute, "startsWith", value)

    @staticmethod
    def ends_with(attribute, value):
        return Query.add_query(attribute, "endsWith", value)

    @staticmethod
    def select(attributes):
        return f'select([{",".join(map(Query.parseValues, attributes))}])'

    @staticmethod
    def search(attribute, value):
        return Query.add_query(attribute, "search", value)

    @staticmethod
    def order_asc(attribute):
        return f'orderAsc("{attribute}")'

    @staticmethod
    def order_desc(attribute):
        return f'orderDesc("{attribute}")'

    @staticmethod
    def cursor_before(id):
        return f'cursorBefore("{id}")'

    @staticmethod
    def cursor_after(id):
        return f'cursorAfter("{id}")'

    @staticmethod
    def limit(limit):
        return f'limit({limit})'

    @staticmethod
    def offset(offset):
        return f'offset({offset})'

    @staticmethod
    def add_query(attribute, method, value):
        if type(value) == list:
            return f'{method}("{attribute}", [{",".join(map(Query.parseValues, value))}])'
        else:
            return f'{method}("{attribute}", [{Query.parseValues(value)}])'

    @staticmethod
    def parseValues(value):
        if type(value) == str:
            return f'"{value}"'
        elif type(value) == bool:
            return str(value).lower()
        else:
            return str(value)