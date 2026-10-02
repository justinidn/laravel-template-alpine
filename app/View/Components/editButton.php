<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class EditButton extends Component
{
    public string $event;
    public string $id;
    public ?string $permission;

    public function __construct(
        string $event = 'open-edit-modal',
        string $id = '',
        ?string $can = null
    ) {
        $this->event = $event;
        $this->id = $id;
        $this->permission = $can ?? $this->resolvePermissionFromRoute();
    }

    private function resolvePermissionFromRoute(): ?string
    {
        $currentRoute = request()->route()?->getName();

        if (!$currentRoute) {
            return null;
        }

        $segments = explode('.', $currentRoute);

        if (count($segments) >= 1) {
            return "{$segments[0]}.update";
        }

        return null;
    }

    public function render(): View|Closure|string
    {
        return view('components.edit-button');
    }
}
