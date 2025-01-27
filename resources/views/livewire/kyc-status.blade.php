<section wire:poll.visible class="flex items-center gap-2 text-xs font-bold uppercase">
    @if($this->setting->kyc === 'verified')
    <p class="w-2 h-2 bg-green-600 rounded-full"></p>
    verified
    @else
    <p class="w-2 h-2 bg-red-600 rounded-full"></p>
    pending verification
    @endif
</section>