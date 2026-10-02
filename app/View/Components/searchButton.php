<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class SearchButton extends Component
{
    public string $event;
    public string $label;
    public string $class;

    public function __construct(
        string $event = 'perform-search',
        string $label = 'Search',
        string $class = 'btn btn-outline-primary btn-sm d-flex align-items-center'
    ) {
        $this->event = $event;
        $this->label = $label;
        $this->class = $class;
    }

    public function render(): View
    {
        return view('components.search-button');
    }
}
