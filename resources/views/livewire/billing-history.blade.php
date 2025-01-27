<section class="py-4 space-y-4">
    @forelse($history as $invoice)
    <a href="{{ $invoice['invoice_url'] }}" class="flex items-center justify-between">
        <span>{{ now()->parse($invoice['date'])->format('M j, Y') }}</span>
        <span>{{ $this->fmt($invoice['amount']) }}</span>
        <span>{{ $invoice['status'] }}</span>
    </a>
    @empty
    <span class="flex items-center gap-2 dark:text-light/75 text-dark/75">
        @svg('heroicon-s-no-symbol', 'h-4 w-4')
        No invoices at the moment.
    </span>
    @endforelse
</section>