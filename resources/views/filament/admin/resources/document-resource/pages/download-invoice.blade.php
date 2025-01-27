<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <script src="https://cdn.tailwindcss.com/3.4.5"></script>
    <title>Receipt</title>
</head>

<body>
    <div class="relative p-5 space-y-10">
        <section class="flex justify-between">
            <h1 class="font-black">Invoice #{{ Str::upper(Str::after($record->document->uuid, '-')) }}
            </h1>
            <div>
                <div class="space-y-0.5">
                    <div class="text-xs font-bold text-end">Kudix Inc.</div>
                    <div class="text-end text-xs w-[35ch] leading-normal">
                        No. 1 Ekwema Crescent Ikenegbu Layout, Owerri Municipal, IM 420628, Nigeria.
                    </div>
                </div>
                <div class="mt-2 text-xs font-medium text-end text-neutral-600 dark:text-neutral-400">
                    <span>Issued date: </span>
                    <span>{{$record->document->created_at->format("l, F j, Y")}}</span>
                </div>
            </div>
        </section>
        <section class="flex items-center justify-between">
            <div>
                <h2 class="font-black uppercase">bill to</h2>
                <div class="mt-2 text-sm font-medium text-neutral-600 dark:text-neutral-400">Name:
                    {{$record->customer?->name ?? 'UNREGISTERED'}}
                </div>
                <div class="mt-2 text-sm font-medium text-neutral-600 dark:text-neutral-400">
                    Address: {{$record->customer?->customerInfo->address ?? 'UNREGISTERED'}}</div>
                <div class="mt-2 text-sm font-medium text-neutral-600 dark:text-neutral-400">
                    Email: {{$record->customer?->email ?? 'UNREGISTERED'}}</div>
                <div class="mt-2 text-sm font-medium text-neutral-600 dark:text-neutral-400">
                    Phone: {{$record->customer?->phone ?? 'UNREGISTERED'}}</div>
            </div>
            <div
                class="absolute flex items-center gap-2 font-serif font-bold -rotate-45 translate-x-1/2 -translate-y-1/2 transform-gpu opacity-10 top-1/2 right-1/2 text-8xl text-amber-600">
                {{Str::upper($record->payment_status)}} @svg('heroicon-s-minus-circle', 'w-16 h-16')
            </div>
        </section>
        <section class="p-8">
            <table class="min-w-full text-sm border-collapse table-fixed indent-0">
                <caption class="table-caption mb-4">Sales Invoice</caption>
                <thead class="table-header-group bg-neutral-300 dark:bg-neutral-700">
                    <tr>
                        <th scope="col" class="tracking-[2px] text-left p-4 uppercase w-1/3">Product</th>
                        <th scope="col" class="tracking-[2px] p-4 uppercase w-[23.33%]">Cost</th>
                        <th scope="col" class="tracking-[2px] p-4 uppercase w-[23.33%]">Quantity</th>
                        <th scope="col" class="tracking-[2px] p-4 uppercase w-[23.33%]">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($record->products as $product)
                    <tr
                        class="table-row odd:bg-neutral-500 even:bg-neutral-400 dark:odd:bg-neutral-900 dark:even:bg-neutral-800">
                        <th scope="row" class="tracking-[2px] table-cell p-4 text-left w-1/2">
                            <div>{{$product->name}}</div>
                            <div class="text-sm font-medium text-neutral-700 dark:text-neutral-500">
                                {{$product->description}}
                            </div>
                        </th>
                        <td class="tracking-[1px] text-center table-cell p-4">
                            {{$fmt->formatCurrency($product->pivot->unit_cost, 'NGN')}}
                        </td>
                        <td class="tracking-[1px] text-center table-cell p-4">
                            {{$product->pivot->quantity.' '.Str::plural('unit', $product->pivot->quantity)}}
                        </td>
                        <td class="tracking-[1px] text-center table-cell p-4">
                            {{$fmt->formatCurrency($product->pivot->quantity * $product->pivot->unit_cost, 'NGN')}}
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
                            {{$fmt->formatCurrency($record->shipping, 'NGN')}}
                        </td>
                    </tr>
                    <tr class="table-row text-right">
                        <th scope="row" colspan="3"
                            class="p-1 tracking-[2px] table-cell font-medium text-xs text-neutral-600 dark:text-neutral-400 uppercase">
                            Discount</th>
                        <td class="p-1 tracking-[1px] font-medium text-xs text-neutral-600 dark:text-neutral-400">
                            {{$record->discount}}%
                        </td>
                    </tr>
                    <tr class="table-row text-right">
                        <th scope="row" colspan="3"
                            class="p-1 tracking-[2px] table-cell font-medium text-xs text-neutral-600 dark:text-neutral-400 uppercase">
                            VAT</th>
                        <td
                            class="p-1 tracking-[1px] table-cell font-medium text-xs text-neutral-600 dark:text-neutral-400">
                            {{$record->vat}}%
                        </td>
                    </tr>
                    <tr class="table-row text-right">
                        <th scope="row" colspan="3" class="p-1 tracking-[2px] table-cell font-medium text-sm uppercase">
                            Total</th>
                        <td class="p-1 tracking-[1px] table-cell font-medium text-sm">
                            {{$fmt->formatCurrency($record->total_cost, 'NGN')}}
                        </td>
                    </tr>
                    <tr class="table-row text-right">
                        <th scope="row" colspan="3"
                            class="p-1 tracking-[2px] table-cell font-medium text-xs text-neutral-600 dark:text-neutral-400 uppercase">
                            Tendered</th>
                        <td
                            class="p-1 tracking-[1px] table-cell font-medium text-xs text-neutral-600 dark:text-neutral-400">
                            {{$fmt->formatCurrency($record->tendered, 'NGN')}}
                        </td>
                    </tr>
                    <tr class="table-row text-right">
                        <th scope="row" colspan="3" class="p-1 tracking-[2px] table-cell font-medium text-sm uppercase">
                            Balance</th>
                        <td class="p-1 tracking-[1px] table-cell font-medium text-sm">
                            {{$fmt->formatCurrency(abs($record->tendered - $record->total_cost), 'NGN')}}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </section>
    </div>
</body>

</html>