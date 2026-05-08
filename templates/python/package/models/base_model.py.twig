from __future__ import annotations

from typing import Any, Dict, Optional, Type, TypeVar

from pydantic import BaseModel, ConfigDict

ModelType = TypeVar('ModelType', bound='AppwriteModel')

class AppwriteModel(BaseModel):
    model_config = ConfigDict(
        arbitrary_types_allowed=True,
        extra='allow',
        populate_by_name=True,
    )

    @classmethod
    def from_dict(cls: Type[ModelType], data: Optional[Dict[str, Any]]) -> Optional[ModelType]:
        if data is None:
            return None

        return cls.model_validate(data)

    def to_dict(self) -> Dict[str, Any]:
        return self.model_dump(by_alias=True, mode='json', exclude_unset=True)

    def to_json(self) -> str:
        return self.model_dump_json(by_alias=True)
