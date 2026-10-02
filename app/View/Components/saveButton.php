<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class SaveButton extends Component
{
    public array $permissions;

    public function __construct(string|array|null $can = null)
    {
        $this->permissions = $this->resolvePermissions($can);
    }

    private function resolvePermissions(string|array|null $can): array
    {
        if ($can) {
            return is_array($can) ? $can : explode('|', $can);
        }

        $currentRoute = request()->route()?->getName();

        if (!$currentRoute) {
            return [];
        }

        $segments = explode('.', $currentRoute);

        if (count($segments) >= 1) {
            return ["{$segments[0]}.add", "{$segments[0]}.update"];
        }

        return [];
    }

    public function render(): View|Closure|string
    {
        return view('components.save-button');
    }
}
