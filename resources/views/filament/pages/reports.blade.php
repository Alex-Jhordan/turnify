<x-filament-panels::page>
    <form wire:submit="exportExcel">
        {{ $this->form }}

        <div class="flex flex-wrap items-center gap-4 mt-6">
            @foreach ($this->getFormActions() as $action)
                {{ $action }}
            @endforeach
        </div>
    </form>
</x-filament-panels::page>
