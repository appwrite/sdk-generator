class Query:
    @staticmethod
    def equal(attribute, value):
        return Query.addQuery(attribute, "equal", value)

    @staticmethod
    def notEqual(attribute, value):
        return Query.addQuery(attribute, "notEqual", value)
    
    @staticmethod
    def lesser(attribute, value):
        return Query.addQuery(attribute, "lesser", value)
    
    @staticmethod
    def lesserEqual(attribute, value):
        return Query.addQuery(attribute, "lesserEqual", value)
    
    @staticmethod
    def greater(attribute, value):
        return Query.addQuery(attribute, "greater", value)
    
    @staticmethod
    def greaterEqual(attribute, value):
        return Query.addQuery(attribute, "greaterEqual", value)

    @staticmethod
    def search(attribute, value):
        return Query.addQuery(attribute, "search", value)

    @staticmethod
    def addQuery(attribute, oper, value):
        if type(value) == list:
            return '{}.{}({})'.format(attribute,oper, ','.join(map(Query.parseValues, value)))
        else:
            return '{}.{}({})'.format(attribute,oper, Query.parseValues(value))

    @staticmethod
    def parseValues(value):
        if type(value) == str:
            return '"{}"'.format(value)
        else:
            return value