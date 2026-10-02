<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class CreateButton extends Component
{
    public string $event;
    public ?string $permission;

    public function __construct(
        string $event = 'open-modal',
        ?string $can = null
    ) {
        $this->event = $event;
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
            return "{$segments[0]}.add";
        }

        return null;
    }

    public function render(): View|Closure|string
    {
        return view('components.create-button');
    }
}
