<?php

namespace App\Filament\Infolists\Components;

use Filament\Infolists\Components\RepeatableEntry;

class PaginatedRepeatableEntry extends RepeatableEntry
{
    public function getItems(): array
    {
        $items = parent::getItems();

        $livewire = $this->getLivewire();
        $perPage = max(1, (int) ($livewire->questionsPerPage ?? 10));
        $currentPage = max(1, (int) ($livewire->questionsPage ?? 1));
        $lastPage = max(1, (int) ceil(count($items) / $perPage));
        $currentPage = min($currentPage, $lastPage);

        return array_slice(
            $items,
            ($currentPage - 1) * $perPage,
            $perPage,
            preserve_keys: true,
        );
    }
}
