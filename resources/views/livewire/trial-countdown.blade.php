@if (isset($trialEndsAt))
<div id="countdown-display" data-trial-ends-at="{{ $trialEndsAt->toIso8601String() }}"
    class="flex items-center justify-center py-2 bg-blue-500 dark:text-white dark:bg-blue-700">
    Loading your trial information...
</div>
@else
<div></div>
@endif

<script>
    function initializeCountdown() {
        // Clear any existing interval to avoid duplication
        if (window.countdownInterval) {
            clearInterval(window.countdownInterval);
        }

        let countdownElement = document.getElementById('countdown-display');
        if (countdownElement) {
            let trialEndsAt = countdownElement.getAttribute('data-trial-ends-at');
            let targetDate = new Date(trialEndsAt);

            window.countdownInterval = setInterval(() => {
                const now = new Date();
                const diff = Math.max(targetDate - now, 0);

                if (diff === 0) {
                    countdownElement.innerHTML = `
                <div>
                    <span class="font-mono font-bold !text-red-500">Your trial has expired. </span>
                    <a href="{{ route('pricing') }}" class="underline">
                        Subscribe Now
                    </a>
                </div>
                `;
                    clearInterval(window.countdownInterval);
                    return;
                }

                const days = Math.floor(diff / (1000 * 60 * 60 * 24));
                const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
                const seconds = Math.floor((diff % (1000 * 60)) / 1000);

                countdownElement.innerHTML = `
                <div>
                    Your trial ends in <span class="font-mono font-bold">${days}d:${hours}h:${minutes}m:${seconds}s. </span>
                    <a href="{{ route('pricing') }}" class="underline">
                        Subscribe Now
                    </a>
                </div>
                `;
            }, 1000);
        }
    }

    // Initialize countdown on DOMContentLoaded and Livewire events
    document.addEventListener("livewire:navigated", initializeCountdown);
</script>