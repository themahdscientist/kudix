@assets
<style type="text/css" media="print">
    .fi-main,
    .fi-main-ctn,
    .fi-section,
    .fi-section-content,
    .fi-section-content-ctn {
        border: none !important;
        box-shadow: none !important;
        padding: 0 !important;
        margin: 0 !important;
    }

    .fi-topbar,
    .fi-header,
    .fi-sidebar,
    .fi-sidebar-close-overlay,
    #countdown-display {
        display: none !important;
    }
</style>
@endassets
@php
$user = filament()->auth()->user()->load('setting');
$record = $this->getRecord();
$fmt = new NumberFormatter(app()->getLocale(), NumberFormatter::CURRENCY);
@endphp
<x-filament-panels::page>
    <x-filament::section>
        <div class="relative p-5 space-y-10 font-mono">
            <section class="flex justify-between">
                <div>
                    <h1>
                        Invoice: <span class="font-black">{{ Str::upper(Str::after($record->uuid, '-')) }}</span>
                    </h1>
                    <h2>
                        {{ Str::ucfirst($record->documentable_type) }}: <span
                            class="font-bold">{{ Str::upper(Str::after($record->documentable->uuid, '-')) }}</span>
                    </h2>
                </div>
                <div>
                    <div class="space-y-0.5">
                        <div class="text-xs font-bold text-end">{{ $user->setting->company_name }}</div>
                        <div class="text-end text-xs w-[35ch] leading-normal">
                            {{ $user->setting->company_address }}
                        </div>
                    </div>
                    <div class="mt-2 text-xs font-medium text-end text-neutral-600 dark:text-neutral-400">
                        <span>Issued date: </span>
                        <span>{{$record->created_at->format("l, F j, Y")}}</span>
                    </div>
                </div>
            </section>
            <section class="flex items-center justify-between">
                @if ($record->documentable_type == \App\DocumentableType::Sale->value)
                <div>
                    <h2 class="font-black uppercase">billed to</h2>
                    <div class="mt-2 text-sm font-medium text-neutral-600 dark:text-neutral-400">Name:
                        {{$record->documentable->client?->name ?? 'UNREGISTERED'}}
                    </div>
                    <div class="mt-2 text-sm font-medium text-neutral-600 dark:text-neutral-400">
                        Address: {{$record->documentable->client?->clientInfo->address ?? 'UNREGISTERED'}}
                    </div>
                    <div class="mt-2 text-sm font-medium text-neutral-600 dark:text-neutral-400">
                        Email: {{$record->documentable->client?->email ?? 'UNREGISTERED'}}
                    </div>
                    <div class="mt-2 text-sm font-medium text-neutral-600 dark:text-neutral-400">
                        Phone: {{$record->documentable->client?->phone ?? 'UNREGISTERED'}}
                    </div>
                </div>
                @else
                <div>
                    <h2 class="font-black uppercase">billed by</h2>
                    <div class="mt-2 text-sm font-medium text-neutral-600 dark:text-neutral-400">Name:
                        {{$record->documentable->supplier?->name ?? 'UNREGISTERED'}}
                    </div>
                    <div class="mt-2 w-[55ch] text-sm font-medium text-neutral-600 dark:text-neutral-400">
                        Address: {{$record->documentable->supplier?->address ?? 'UNREGISTERED'}}
                    </div>
                    <div class="mt-2 text-sm font-medium text-neutral-600 dark:text-neutral-400">
                        Email: {{$record->documentable->supplier?->email ?? 'UNREGISTERED'}}
                    </div>
                    <div class="mt-2 text-sm font-medium text-neutral-600 dark:text-neutral-400">
                        Phone: {{$record->documentable->supplier?->phone ?? 'UNREGISTERED'}}
                    </div>
                </div>
                @endif
                <img src="{{ $this->getQrCode() }}" alt="{{ $record->uuid }}">
                <div
                    class="absolute flex items-center gap-2 font-serif font-bold -rotate-45 translate-x-1/2 -translate-y-1/2 transform-gpu opacity-10 top-1/2 right-1/2 text-8xl text-amber-600">
                    {{Str::upper($record->payment_status)}} @svg('heroicon-s-minus-circle', 'w-16 h-16')
                </div>
            </section>
            <section class="p-8">
                <table class="min-w-full text-sm border-collapse table-fixed indent-0">
                    <caption class="table-caption mb-4">
                        @if ($record->type === \App\DocumentType::Invoice->value && $record->documentable_type ===
                        \App\DocumentableType::Sale->value)
                        Sale Invoice
                        @else
                        Purchase Invoice
                        @endif
                    </caption>
                    <thead class="table-header-group bg-neutral-300 dark:bg-neutral-700">
                        <tr>
                            <th scope="col" class="tracking-[2px] text-left p-4 uppercase w-1/3">Product</th>
                            <th scope="col" class="tracking-[2px] p-4 uppercase w-[23.33%]">Cost</th>
                            <th scope="col" class="tracking-[2px] p-4 uppercase w-[23.33%]">Quantity</th>
                            <th scope="col" class="tracking-[2px] p-4 uppercase w-[23.33%]">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($record->documentable->products as $product)
                        <tr
                            class="table-row odd:bg-neutral-500 even:bg-neutral-400 dark:odd:bg-neutral-900 dark:even:bg-neutral-800">
                            <th scope="row" class="tracking-[2px] table-cell p-4 text-left w-1/2">
                                <div>{{$product->name}}</div>
                                <div class="text-sm font-medium text-neutral-700 dark:text-neutral-500">
                                    {{$product->description}}
                                </div>
                            </th>
                            <td class="tracking-[1px] text-center table-cell p-4">
                                @if ($record->documentable_type === \App\DocumentableType::Sale->value)
                                {{$fmt->formatCurrency($product->pivot->unit_price, 'NGN')}}
                                @else
                                {{$fmt->formatCurrency($product->pivot->unit_cost, 'NGN')}}
                                @endif
                            </td>
                            <td class="tracking-[1px] text-center table-cell p-4">
                                {{$product->pivot->quantity.' '.Str::plural('unit', $product->pivot->quantity)}}
                            </td>
                            <td class="tracking-[1px] text-center table-cell p-4">
                                @if ($record->documentable_type === \App\DocumentableType::Sale->value)
                                {{$fmt->formatCurrency($product->pivot->quantity * $product->pivot->unit_price, 'NGN')}}
                                @else
                                {{$fmt->formatCurrency($product->pivot->quantity * $product->pivot->unit_cost, 'NGN')}}
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="table-row text-right">
                            <th scope="row" colspan="3"
                                class="pt-8 p-2 tracking-[2px] table-cell font-medium text-xs text-neutral-600 dark:text-neutral-400 uppercase">
                                Shipping</th>
                            <td
                                class="pt-8 p-2 tracking-[1px] table-cell font-medium text-xs text-neutral-600 dark:text-neutral-400">
                                {{$fmt->formatCurrency($record->documentable->shipping, 'NGN')}}
                            </td>
                        </tr>
                        <tr class="table-row text-right">
                            <th scope="row" colspan="3"
                                class="p-1 tracking-[2px] table-cell font-medium text-xs text-neutral-600 dark:text-neutral-400 uppercase">
                                Discount</th>
                            <td class="p-1 tracking-[1px] font-medium text-xs text-neutral-600 dark:text-neutral-400">
                                {{$record->documentable->discount}}%
                            </td>
                        </tr>
                        <tr class="table-row text-right">
                            <th scope="row" colspan="3"
                                class="p-1 tracking-[2px] table-cell font-medium text-xs text-neutral-600 dark:text-neutral-400 uppercase">
                                VAT</th>
                            <td
                                class="p-1 tracking-[1px] table-cell font-medium text-xs text-neutral-600 dark:text-neutral-400">
                                {{$record->documentable->vat}}%
                            </td>
                        </tr>
                        <tr class="table-row text-right">
                            <th scope="row" colspan="3"
                                class="p-1 tracking-[2px] table-cell font-medium text-sm uppercase">
                                Total</th>
                            <td class="p-1 tracking-[1px] table-cell font-medium text-sm">
                                @if ($record->documentable_type === \App\DocumentableType::Sale->value)
                                {{$fmt->formatCurrency($record->documentable->total_price, 'NGN')}}
                                @else
                                {{$fmt->formatCurrency($record->documentable->total_cost, 'NGN')}}
                                @endif
                            </td>
                        </tr>
                        <tr class="table-row text-right">
                            <th scope="row" colspan="3"
                                class="p-1 tracking-[2px] table-cell font-medium text-xs text-neutral-600 dark:text-neutral-400 uppercase">
                                Tendered</th>
                            <td
                                class="p-1 tracking-[1px] table-cell font-medium text-xs text-neutral-600 dark:text-neutral-400">
                                {{$fmt->formatCurrency($record->documentable->tendered, 'NGN')}}
                            </td>
                        </tr>
                        <tr class="table-row text-right">
                            <th scope="row" colspan="3"
                                class="p-1 tracking-[2px] table-cell font-medium text-sm uppercase">
                                Balance</th>
                            <td class="p-1 tracking-[1px] table-cell font-medium text-sm">
                                @if ($record->documentable_type === \App\DocumentableType::Sale->value)
                                {{$fmt->formatCurrency(abs($record->documentable->tendered - $record->documentable->total_price), 'NGN')}}
                                @else
                                {{$fmt->formatCurrency(abs($record->documentable->tendered - $record->documentable->total_cost), 'NGN')}}
                                @endif
                            </td>
                        </tr>
                    </tfoot>
                </table>
                <img class="h-6 hidden dark:block" src="{{ asset('images/logo-light.svg') }}"
                    alt="{{ config('app.name') }} logo">
                <img class="h-6 dark:hidden block" src="{{ asset('images/logo-dark.svg') }}"
                    alt="{{ config('app.name') }} logo">
            </section>
        </div>
    </x-filament::section>
    @if (count($relationManagers = $this->getRelationManagers()))
    <x-filament-panels::resources.relation-managers :active-manager="$this->activeRelationManager"
        :managers="$relationManagers" :owner-record="$record" :page-class="static::class" />
    @endif
</x-filament-panels::page>