<x-layouts.auth>
    <x-auth-header :title="__('Create an account')" :description="__('Enter your details below to create your account')" />

    {{-- VERY IMPORTANT — ONLY THIS. DO NOT PUT $step OR WIZARD UI HERE. --}}
    <livewire:auth.register-wizard />

    <div class="mt-6 text-center text-sm text-zinc-600 dark:text-zinc-400">
        <span>{{ __('Already have an account?') }}</span>
        <flux:link :href="route('login')" wire:navigate>{{ __('Log in') }}</flux:link>
    </div>
</x-layouts.auth>
