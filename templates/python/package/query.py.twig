class Query:
    @staticmethod
    def equal(attribute, value):
        return Query.addQuery(attribute, "equal", value)

    @staticmethod
    def notEqual(attribute, value):
        return Query.addQuery(attribute, "notEqual", value)
    
    @staticmethod
    def lessThan(attribute, value):
        return Query.addQuery(attribute, "lessThan", value)
    
    @staticmethod
    def lessThanEqual(attribute, value):
        return Query.addQuery(attribute, "lessThanEqual", value)
    
    @staticmethod
    def greaterThan(attribute, value):
        return Query.addQuery(attribute, "greaterThan", value)
    
    @staticmethod
    def greaterThanEqual(attribute, value):
        return Query.addQuery(attribute, "greaterThanEqual", value)

    @staticmethod
    def search(attribute, value):
        return Query.addQuery(attribute, "search", value)

    @staticmethod
    def orderAsc(attribute):
        return f'orderAsc("{attribute}")'

    @staticmethod
    def orderDesc(attribute):
        return f'orderDesc("{attribute}")'

    @staticmethod
    def cursorBefore(id):
        return f'cursorBefore("{id}")'

    @staticmethod
    def cursorAfter(id):
        return f'cursorAfter("{id}")'

    @staticmethod
    def limit(limit):
        return f'limit({limit})'

    @staticmethod
    def offset(offset):
        return f'offset({offset})'

    @staticmethod
    def addQuery(attribute, method, value):
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