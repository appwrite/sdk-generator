<?php

namespace Utopia\MockServer\Utopia\Validator;

use Utopia\MockServer\Utopia\Model\Player as PlayerModel;
use Utopia\Validator;

class Player extends Validator
{
    public function getDescription(): string
    {
        return 'Value must be a valid Player instance';
    }

    public function isValid($value): bool
    {
        return $value instanceof PlayerModel;
    }

    public function isArray(): bool
    {
        return false;
    }

    public function getType(): string
    {
        return self::TYPE_OBJECT;
    }
}
