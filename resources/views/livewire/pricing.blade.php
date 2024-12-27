<section role="banner" class="flex items-center justify-center min-h-screen">
    <div class="w-full">
        <section role="heading" class="mt-16 text-center lg:mt-32">
            <h2 x-text="marshmellow"></h2>
            <h1 class="text-5xl font-bold tracking-tight">Subscription plans - <span
                    class="dark:text-primary text-accent">that don&apos;t
                    bite.</span></h1>
            <p class="mt-2 text-xl text-dark dark:text-secondary">Whatever&apos;s the size, we got just the right price
                for you.</p>
        </section>
        <section
            class="flex items-center h-full gap-20 mx-10 mt-16 border-l-2 lg:mx-20 rounded-bl-3xl rounded-tl-3xl dark:border-primary border-accent">
            <div class="w-[60%] p-8">
                <div>
                    <h2 class="mb-4 text-xl font-medium">Choose a pricing plan:</h2>
                    <div class="flex items-center justify-start divide-x-2 divide-dark">
                        @foreach ($plans as $index => $plan)
                        <button type="button"
                            @class([ 'w-full transition duration-300 p-4 font-semibold uppercase first-of-type:rounded-tl-lg first-of-type:rounded-bl-lg last-of-type:rounded-br-lg last-of-type:rounded-tr-lg'
                            , 'bg-accent dark:bg-primary dark:text-light text-dark hover:text-secondary hover:bg-dark dark:hover:bg-light dark:hover:text-dark'=>
                            ($selectedPlan['id'] ?? null) === ($plan['id'] ??
                            null),
                            'bg-secondary hover:bg-dark hover:text-secondary dark:hover:bg-light' =>
                            ($selectedPlan['id'] ?? null) !== ($plan['id']
                            ?? null),
                            ])
                            wire:click="selectPlan({{$index}})">
                            {{$plan['name'] ?? null}}
                        </button>
                        @endforeach
                    </div>
                </div>
                <div class="mt-8">
                    <h3 class="mb-4 text-lg font-medium">Description:</h3>
                    <p class="text-sm">
                        {{$selectedPlan['description'] ?? 'You are not online. Please connect to the internet and refresh the page to try again.'}}
                    </p>
                </div>
            </div>
            <div class="w-[40%] pb-8 rounded-tr-3xl rounded-br-3xl h-full dark:bg-primary bg-accent">
                <h3
                    class="p-2 text-xl font-semibold text-center uppercase rounded-tr-3xl bg-dark dark:bg-light text-secondary dark:text-dark">
                    {{$selectedPlan['name'] ?? 'plan_name'}}
                </h3>
                <p class="my-8 text-sm text-center dark:text-secondary">
                    <span class="text-3xl font-bold lg:text-5xl dark:text-light">{{ $this->fmt($selectedPlan['amount'] ?? 0) }}</span>&sol;mo
                </p>
                <button wire:loading.attr="disabled" wire:target="subscribe('{{$selectedPlan['plan_code'] ?? null}}')" wire:click="subscribe('{{$selectedPlan['plan_code'] ?? null}}')" type="button"
                    class="flex items-center justify-center gap-2 px-6 py-2 mx-auto text-lg font-medium transition duration-300 outline-none rounded-3xl bg-secondary dark:bg-dark hover:bg-dark hover:text-light group">
                    Subscribe
                    <span wire:loading.remove>
                        @svg('heroicon-s-fire', 'inline h-5 w-5 dark:text-light group-hover:text-accent
                        dark:group-hover:text-primary transition duration-300')
                    </span>
                    <x-filament::loading-indicator wire:loading
                        wire:target="subscribe, selectPlan"
                        class="inline w-5 h-5 text-dark group-hover:text-accent dark:group-hover:text-primary dark:text-light"
                        role="status" aria-hidden="true" />
                </button>
            </div>
        </section>
        <section class="mt-16">
            <div class="w-full h-[50vh]">
                <h4
                    class="font-bold text-center text-xl uppercase text-light dark:text-dark before:content-normal relative before:absolute before:w-full before:top-1/2 before:-z-50 before:left-1/2 before:h-0.5 before:rounded-full before:bg-dark dark:before:bg-light after:content-normal after:absolute after:bg-dark dark:after:bg-light after:-z-30 after:px-20 after:py-6 after:rounded-full after:top-1/2 after:left-1/2 before:-translate-x-1/2 after:-translate-x-1/2 after:-translate-y-1/2">
                    Features</h4>
                @if ($features)
                <ul role="list" class="grid w-2/3 grid-cols-2 mx-auto my-12 font-light bg-transparent">
                    @foreach ($features as $feature)
                    <li
                        class="flex items-center gap-2 p-4 m-2 font-medium transition duration-300 rounded-full bg-dark text-secondary hover:-translate-y-0.5">
                        @svg('heroicon-s-check-circle', 'h-5 w-5 text-accent dark:text-primary'){{$feature}}
                    </li>
                    @endforeach
                </ul>
                @else
                <div class="mt-12 text-lg font-medium text-center">Select a plan to preview features.</div>
                @endif
            </div>
        </section>
    </div>
</section>