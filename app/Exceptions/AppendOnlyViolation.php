<?php

namespace App\Exceptions;

use Illuminate\Database\Eloquent\Model;
use LogicException;

class AppendOnlyViolation extends LogicException
{
    public static function forModel(Model $model, string $operation): self
    {
        return new self(sprintf(
            '%s records are append-only; %s is not allowed (id %s).',
            class_basename($model),
            $operation,
            (string) $model->getKey(),
        ));
    }
}
