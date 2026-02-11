@if(auth()->user()->house_id)
    <x-layouts.display :title="__('Dashboard')">
        <livewire:dashboard.index />
    </x-layouts.display>
@else
    <x-layouts.app :title="__('Dashboard')">
        <livewire:dashboard.index />
    </x-layouts.app>
@endif
