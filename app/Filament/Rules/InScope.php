<?php

namespace App\Filament\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class InScope implements ValidationRule
{
    /**
     * @param  array<int, int>|null  $allowedIds
     */
    public function __construct(
        protected ?array $allowedIds,
        protected string $message,
    ) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($this->allowedIds === null) {
            return;
        }

        $submitted = array_map('intval', (array) $value);

        if (array_diff($submitted, $this->allowedIds) !== []) {
            $fail($this->message);
        }
    }
}
