@php
    $tabs = menu()->getCurrentActiveMenu('admin')?->getChildren();
@endphp

@if (
    $tabs
    && $tabs->isNotEmpty()
)
    <div class="tabs">
        <div class="mb-4 flex gap-4 border-b-2 pt-2 dark:border-border-default max-sm:hidden">
            @foreach ($tabs as $tab)
                <a href="{{ $tab->getUrl() }}">
                    <div class="{{ $tab->isActive() ? "-mb-px active-tab" : '' }} pb-3.5 px-2.5 text-base font-medium text-text-secondary dark:text-text-secondary cursor-pointer">
                        {{ $tab->getName() }}
                    </div>
                </a>
            @endforeach
        </div>
    </div>
@endif
