@props([
'tableId' => 'custom-table',
'ajaxUrl' => '',
'minWidth' => '700px'
])

<div x-data="datatableComponent('{!! $ajaxUrl !!}', '{{ $tableId }}')">
    <div class="table-responsive w-100">
        <table class="table table-hover align-middle mb-0 w-100" id="{{ $tableId }}" @style(['min-width'=> $minWidth])>
            <thead class="table-light text-uppercase fs-7 text-secondary">
                <tr>
                    {{ $header }}
                </tr>
            </thead>
        </table>
    </div>
</div>