from types import MethodType


def _levels_of(levels):
    """
    Validate the levels passed to `path()`: a non-empty list of non-empty strings, none holding
    the level separator `/` or the wildcards `+` and `#` (use `any()` and `all()` for those).
    """
    if not isinstance(levels, (list, tuple)):
        raise TypeError("path() expects an array, e.g. path(['user'])")
    if len(levels) == 0:
        raise ValueError("path() needs at least one level")
    for index, level in enumerate(levels):
        if not isinstance(level, str):
            kind = "null" if level is None else type(level).__name__
            raise TypeError(f"level at index {index} must be a string, got {kind}")
        if level == "":
            raise ValueError(f"empty level at index {index}")
        if "/" in level:
            parts = ", ".join(f"'{part}'" for part in level.split("/"))
            raise ValueError(f'"{level}" contains "/", split it: path([{parts}])')
        if level == "+":
            raise ValueError('"+" is reserved, use any()')
        if "+" in level:
            raise ValueError(f'"{level}" contains "+", use any() for wildcards')
        if level == "#":
            raise ValueError('"#" is reserved, use all()')
        if "#" in level:
            raise ValueError(f'"{level}" contains "#", use all() for wildcards')
    return tuple(levels)


class _builder:
    """
    Bind a Topic method to the instance it is read from, or to an empty topic when it is read
    from the class, so `Topic.path([...])` starts a topic and `topic.path([...])` extends one.
    """

    def __init__(self, func):
        self.__func__ = func
        self.__doc__ = func.__doc__

    def __get__(self, instance, owner=None):
        target = instance if instance is not None else owner(())
        return MethodType(self.__func__, target)


class Topic:
    """
    Helper to build MQTT topics for `Push`, level by level.

        Topic.path(['user', user_id, 'notification'])  # "user/123/notification"
        Topic.path(['user']).any().path(['notification'])  # "user/+/notification"
        Topic.path(['org']).any().path(['logs']).all()  # "org/+/logs/#"

    `any()` matches exactly one level and `all()` everything below, at any depth (including the
    parent itself). `all()` ends the topic, so it returns a `ResolvedTopic`.
    """

    def __init__(self, levels):
        # Internal: build topics with `Topic.path()`, `Topic.any()` and `Topic.all()`.
        self._levels = tuple(levels)

    @_builder
    def path(self, levels):
        """Start a topic with the given levels, or append them, e.g. `Topic.path(['user', user_id])`."""
        return Topic(self._levels + _levels_of(levels))

    @_builder
    def any(self):
        """Start a topic with, or append, a single-level wildcard (`+`): matches exactly one level."""
        return Topic(self._levels + ("+",))

    @_builder
    def all(self):
        """The multi-level wildcard (`#`) alone, or appended to end the topic."""
        return ResolvedTopic(self._levels + ("#",))

    def __str__(self):
        return "/".join(self._levels)

    def __repr__(self):
        return f"Topic({str(self)!r})"


class ResolvedTopic:
    """A topic ended by `all()`: nothing can be appended after the `#` wildcard."""

    def __init__(self, levels):
        # Internal: created by `Topic.all()`.
        self._levels = tuple(levels)

    def path(self, levels):
        raise TypeError("nothing can come after all()")

    def any(self):
        raise TypeError("nothing can come after all()")

    def all(self):
        raise TypeError("nothing can come after all()")

    def __str__(self):
        return "/".join(self._levels)

    def __repr__(self):
        return f"ResolvedTopic({str(self)!r})"
