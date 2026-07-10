<?php

namespace Utopia\MockServer\Utopia\Model;

use Utopia\Model;

readonly class Player implements Model
{
    public function __construct(
        public string $id,
        public string $name,
        public int $score,
    ) {
    }

    public static function fromArray(array $value): static
    {
        return new static(
            id: $value['id'],
            name: $value['name'],
            score: $value['score'],
        );
    }
}
