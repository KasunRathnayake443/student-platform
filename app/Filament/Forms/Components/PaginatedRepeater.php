<?php

namespace App\Filament\Forms\Components;

use Filament\Actions\Action;
use Filament\Forms\Components\Repeater;

class PaginatedRepeater extends Repeater
{
    /**
     * Only the current page of items is rendered. The underlying form state
     * still contains every item, so validation, dehydration, file uploads and
     * saving continue to work exactly as they did before pagination.
     */
    public function getItems(): array
    {
        $items = parent::getItems();

        $perPage = $this->getQuestionsPerPage();
        $currentPage = $this->getEffectiveQuestionsPage(count($items));

        return array_slice(
            $items,
            ($currentPage - 1) * $perPage,
            $perPage,
            preserve_keys: true,
        );
    }

    public function getQuestionsPerPage(): int
    {
        $livewire = $this->getLivewire();

        return max(1, (int) ($livewire->questionsPerPage ?? 10));
    }

    public function getEffectiveQuestionsPage(?int $total = null): int
    {
        $total ??= $this->getItemsCount();

        $lastPage = max(1, (int) ceil($total / $this->getQuestionsPerPage()));
        $currentPage = max(1, (int) ($this->getLivewire()->questionsPage ?? 1));

        return min($currentPage, $lastPage);
    }

    public function getAddAction(): Action
    {
        $action = parent::getAddAction();

        if (! $originalAction = $action->getActionFunction()) {
            return $action;
        }

        return $action->action(function (Repeater $component) use ($originalAction): void {
            $this->jumpToLastQuestionsPageAfterAdding($component);

            $component->evaluate($originalAction, ['component' => $component]);
        });
    }

    public function getCloneAction(): Action
    {
        $action = parent::getCloneAction();

        if (! $originalAction = $action->getActionFunction()) {
            return $action;
        }

        return $action->action(function (array $arguments, Repeater $component) use ($originalAction): void {
            $this->jumpToLastQuestionsPageAfterAdding($component);

            $component->evaluate($originalAction, [
                'arguments' => $arguments,
                'component' => $component,
            ]);
        });
    }

    /**
     * Filament's default reorder action expects every item key to be present.
     * With pagination only the visible keys are dragged, so reorder those keys
     * within their existing global positions instead of moving them to the top.
     */
    public function getReorderAction(): Action
    {
        $action = parent::getReorderAction();

        if (! $action->getActionFunction()) {
            return $action;
        }

        return $action->action(function (array $arguments, Repeater $component): void {
            $visibleKeys = array_values($arguments['items'] ?? []);
            $rawState = $component->getRawState() ?? [];

            if ($visibleKeys === [] || $rawState === []) {
                return;
            }

            $visibleKeySet = array_fill_keys($visibleKeys, true);
            $orderedKeys = [];
            $visibleIndex = 0;

            foreach (array_keys($rawState) as $key) {
                if (isset($visibleKeySet[$key])) {
                    $orderedKeys[] = $visibleKeys[$visibleIndex] ?? $key;
                    $visibleIndex++;

                    continue;
                }

                $orderedKeys[] = $key;
            }

            $items = [];

            foreach ($orderedKeys as $key) {
                $items[$key] = $rawState[$key];
            }

            $component->rawState($items);

            $component->callAfterStateUpdated();

            $component->shouldPartiallyRenderAfterActionsCalled() ? $component->partiallyRender() : null;
        });
    }

    /**
     * New items are appended to the end of the repeater state. Jump to the
     * page they will be rendered on so the user can see what was added.
     */
    protected function jumpToLastQuestionsPageAfterAdding(Repeater $component): void
    {
        $livewire = $component->getLivewire();

        if (! is_object($livewire) || ! method_exists($livewire, 'gotoQuestionsPage')) {
            return;
        }

        $perPage = max(1, (int) ($livewire->questionsPerPage ?? 10));
        $totalAfterAdding = $component->getItemsCount() + 1;

        $livewire->gotoQuestionsPage((int) ceil($totalAfterAdding / $perPage));
    }
}
