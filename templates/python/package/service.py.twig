from enum import Enum
from typing import Any, Optional, Tuple, Type, TypeVar

from pydantic import ValidationError

from .client import Client
from .exception import AppwriteException
from .models.base_model import AppwriteModel

ModelType = TypeVar('ModelType', bound=AppwriteModel)

class Service:
    def __init__(self, client: Client):
        self.client = client

    def _normalize_value(self, value: Any) -> Any:
        if isinstance(value, AppwriteModel):
            return value.to_dict()

        if isinstance(value, Enum):
            return value.value

        if isinstance(value, list):
            return [self._normalize_value(item) for item in value]

        if isinstance(value, dict):
            return {
                key: self._normalize_value(item)
                for key, item in value.items()
            }

        return value

    def _parse_response(
        self,
        response: Any,
        model: Optional[Type[ModelType]] = None,
        union_models: Optional[Tuple[Type[AppwriteModel], ...]] = None
    ) -> Any:
        if model is None and not union_models:
            return response

        if not isinstance(response, dict):
            return response

        if model is not None:
            try:
                return model.model_validate(response)
            except ValidationError as error:
                raise AppwriteException(
                    f'Unable to parse response into {model.__name__}: {error}'
                ) from error

        for union_model in union_models or ():
            try:
                return union_model.model_validate(response)
            except ValidationError:
                continue

        raise AppwriteException('Unable to parse response into any known model')
