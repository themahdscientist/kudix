@php
$user = filament()->auth()->user();
$status = $user->subscription()->status;
$nbd = $user->subscription()->ends_at;
@endphp

<x-filament-widgets::widget class="fi-filament-info-widget">
    <x-filament::section>
        <div class="flex items-start justify-between">
            <div class="cursor-pointer">
                <div class="flex items-center gap-2">
                    <div x-tooltip="'{{ $status }}'">
                        @if($status === 'active')
                        <p class="w-2 h-2 bg-green-600 rounded-full"></p>
                        @else
                        <p class="w-2 h-2 bg-red-600 rounded-full"></p>
                        @endif
                    </div>
                    <p class="font-semibold uppercase">
                        {{ $user->subscription()->type ?? 'None' }}
                    </p>
                    <span
                        class="bg-black dark:bg-white dark:text-black text-white text-[8px] rounded-full px-1.5 py-[1px] font-bold uppercase">
                        Recurring
                    </span>
                </div>
                <p class="text-xs">
                    Subscription renews on&nbsp;<span class="font-semibold"
                        x-tooltip="'{{ $nbd?->diffForHumans() ?? 'null' }}'">{{ $nbd?->format('F j, Y') ?? 'N/A' }}</span>
                </p>
            </div>
            <x-filament::button href="{{ route('billing.index') }}" outlined tag="a" icon="heroicon-s-credit-card"
                color="success" icon-alias="Billing" labeled-from="sm">
                Billing
            </x-filament::button>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>