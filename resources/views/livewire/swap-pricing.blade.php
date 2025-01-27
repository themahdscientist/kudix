<section>
    <x-filament::breadcrumbs :breadcrumbs="[
    route('billing.index') => 'Billing',
    'Subscriptions'
]" />
    @if (!empty($this->plans))
    <div class="lg:mt-20">
        <section class="flex flex-col items-center justify-center gap-16">
            <div>
                <div>
                    <h2 class="mb-4 text-lg font-medium">Choose a plan:</h2>
                    <div class="flex items-center justify-start divide-x-2 divide-dark dark:divide-light">
                        @foreach ($plans as $index => $plan)
                        <button wire:key="{{$plan['id']}}" type="button"
                            @class([ 'w-full transition duration-300 p-4 font-semibold uppercase first-of-type:rounded-tl-lg first-of-type:rounded-bl-lg last-of-type:rounded-br-lg last-of-type:rounded-tr-lg'
                            , 'bg-accent dark:bg-primary dark:text-light text-dark hover:text-secondary hover:bg-dark dark:hover:bg-light dark:hover:text-dark'=>
                            ($selectedPlan['id'] ?? null) === ($plan['id'] ??
                            null),
                            'bg-dark/15 dark:bg-light/15 hover:bg-dark hover:text-light dark:hover:bg-light
                            dark:hover:text-dark' =>
                            ($selectedPlan['id'] ?? null) !== ($plan['id']
                            ?? null),
                            ])
                            wire:click="selectPlan({{$index}})">
                            {{$plan['name'] ?? null}}
                        </button>
                        @endforeach
                    </div>
                </div>
                <div>
                    <h3 class="mt-8 mb-4 text-lg font-medium">Description:</h3>
                    <p class="text-sm">
                        {{$selectedPlan['description'] ?? 'Seems you are offline. Please connect to the internet and refresh the page to try again.'}}
                    </p>
                </div>
            </div>
            <div class="relative w-3/4 pb-8 mx-auto rounded-lg dark:bg-primary bg-accent">
                @if(($selectedPlan['name'] ?? null) === $currentPlan['name'])
                <div
                    class="absolute top-0 right-0 p-1 text-xs font-bold uppercase rounded-bl-lg dark:text-light text-dark bg-accent dark:bg-primary">
                    Current
                </div>
                @endif
                <h3
                    class="p-2 text-lg font-semibold text-center uppercase rounded-t-lg bg-dark dark:bg-light text-light dark:text-dark">
                    {{$selectedPlan['name'] ?? 'plan_name'}}
                </h3>
                <p class="my-8 text-sm font-medium text-center dark:text-light">
                    <span
                        class="text-xl font-bold lg:text-3xl dark:text-light">{{ $this->fmt($selectedPlan['amount'] ?? 0) }}</span>&sol;mo
                </p>
                @unless ($selectedPlan['name'] === $currentPlan['name'])
                <button wire:loading.attr="disabled" wire:target="switch('{{$selectedPlan['plan_code'] ?? null}}')"
                    wire:click="switch('{{$selectedPlan['plan_code'] ?? null}}')" type="button"
                    class="flex items-center justify-center gap-2 px-3 py-1 mx-auto font-medium transition duration-300 outline-none rounded-3xl bg-light dark:bg-dark hover:bg-dark dark:hover:bg-light dark:hover:text-dark hover:text-light group">
                    <x-filament::loading-indicator wire:loading wire:target="switch, selectPlan"
                        class="inline w-5 h-5 transition duration-300 text-dark group-hover:text-accent dark:group-hover:text-primary dark:text-light"
                        role="status" aria-hidden="true" />
                    <span wire:loading.remove>
                        @svg('heroicon-s-arrows-right-left', 'inline h-4 w-4 dark:text-light group-hover:text-accent
                        dark:group-hover:text-primary transition duration-300')
                    </span>
                    Switch
                </button>
                @else
                <button
                    class="flex items-center gap-2 px-3 py-1 mx-auto font-medium cursor-auto rounded-3xl bg-light/50 dark:bg-dark/50">
                    <span>
                        @svg('heroicon-s-fire', 'inline h-4 w-4 dark:text-light group-hover:text-accent
                        dark:group-hover:text-primary transition duration-300')
                    </span>
                    Subscribed
                </button>
                @endunless
            </div>
        </section>
        <section class="mt-16">
            <h4
                class="font-bold text-center text-lg uppercase text-light dark:text-dark before:content-normal relative before:absolute before:w-full before:top-1/2 before:-z-50 before:left-1/2 before:h-0.5 before:rounded-full before:bg-dark dark:before:bg-light after:content-normal after:absolute after:bg-dark dark:after:bg-light after:-z-30 after:px-16 after:py-4 after:rounded-full after:top-1/2 after:left-1/2 before:-translate-x-1/2 after:-translate-x-1/2 after:-translate-y-1/2">
                Features
            </h4>
            @if ($features)
            <ul role="list" class="grid grid-cols-2 mt-12 font-light bg-transparent">
                @foreach ($features as $index => $feature)
                <li wire:key="{{$index}}"
                    class="flex text-sm items-center gap-2 p-4 m-2 font-medium transition duration-300 rounded-full bg-dark text-light dark:bg-light dark:text-dark hover:-translate-y-0.5">
                    @svg('heroicon-s-check-circle', 'h-5 w-5 text-accent dark:text-primary')
                    {{$feature}}
                </li>
                @endforeach
            </ul>
            @else
            <div class="mt-12 text-lg font-medium text-center">Select a plan to preview features.</div>
            @endif
        </section>
    </div>
    @else
    <div class="w-full">
        <section role="heading" class="flex flex-col items-center">
            @svg('heroicon-s-signal-slash', 'w-8 h-8 text-dark dark:text-light')
            <h1 class="mt-4 text-xl font-bold tracking-tight ">
                Oops,
            </h1>
            <p class="mt-2 text-sm text-center text-dark/75 dark:text-light/75">
                Seems you are offline. Please connect to the internet and refresh the page to try again.
            </p>
            <a href=""
                class="px-4 py-2 mt-8 text-sm font-medium rounded-lg dark:bg-light bg-dark text-light dark:text-dark">Refresh</a>
        </section>
    </div>
    @endif
</section>