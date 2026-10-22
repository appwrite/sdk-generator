<?php

namespace Utopia\MockServer\Utopia\Validator;

use Utopia\MockServer\Utopia\Model\Player as PlayerModel;
use Utopia\Validator;

class Player extends Validator
{
    public function getDescription(): string
    {
        return 'Value must be a valid Player object';
    }

    public function isValid($value): bool
    {
        if (!\is_array($value) || \array_diff(['id', 'name', 'score'], \array_keys($value)) !== []) {
            return false;
        }

        try {
            PlayerModel::fromArray($value);
        } catch (\TypeError) {
            return false;
        }

        return true;
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
