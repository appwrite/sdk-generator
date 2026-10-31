"""
A decorator to mark functions as deprecated.

When the function is called, a DeprecationWarning is emitted.
Compatible with Python 2.7+ and Python 3.x.
"""

import functools
import inspect
import threading
import warnings

# `warnings.filters` is process global, so the temporary filter used to show the
# deprecation warning on every call is guarded by a lock. Without it, two
# threads calling a deprecated function at the same time could restore each
# other's filters and leak the `always` filter into the process.
_filter_lock = threading.RLock()


def _warn_deprecated(message, stacklevel=3):
    # Emit a deprecation warning that is shown on every call. The `always` filter
    # is only installed while the warning is emitted and the filters configured
    # by the caller are restored right after, so concurrent tasks and threads are
    # never affected by it. `stacklevel` blames the frame that called the
    # deprecated function, which is two frames above this one.
    with _filter_lock:
        with warnings.catch_warnings():
            warnings.simplefilter('always', DeprecationWarning)  # show warning every time
            warnings.warn(message, category=DeprecationWarning, stacklevel=stacklevel)


def deprecated(reason=None):
    """
    Decorator to mark functions as deprecated.
    Emits a DeprecationWarning when the function is called.

    Args:
        reason (str, optional): Reason for deprecation. Defaults to None.

    Usage:
        @deprecated("Use another_function instead.")
        def old_function(...):
            ...
    """

    def decorator(func):
        message = "Call to deprecated function '{}'.{}".format(func.__name__, " " + reason if reason else "")

        if inspect.iscoroutinefunction(func):

            @functools.wraps(func)
            async def wrapped(*args, **kwargs):
                _warn_deprecated(message)
                return await func(*args, **kwargs)

            return wrapped

        @functools.wraps(func)
        def wrapped(*args, **kwargs):
            _warn_deprecated(message)
            return func(*args, **kwargs)

        return wrapped

    # Support both @deprecated and @deprecated("reason")
    if callable(reason):
        # Used as @deprecated without arguments
        func = reason
        reason = None
        return decorator(func)
    else:
        return decorator
