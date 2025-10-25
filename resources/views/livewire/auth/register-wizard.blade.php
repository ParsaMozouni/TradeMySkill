<div class="flex flex-col gap-6">
    {{-- Stepper --}}
    <div class="flex items-center gap-2 text-sm">
        <span class="inline-flex h-6 w-6 items-center justify-center rounded-full {{ $step >= 1 ? 'bg-zinc-900 text-white' : 'border' }}">1</span>
        <span class="{{ $step === 1 ? 'font-medium' : '' }}">Account</span>
        <span class="h-px w-10 bg-zinc-300"></span>

        <span class="inline-flex h-6 w-6 items-center justify-center rounded-full {{ $step >= 2 ? 'bg-zinc-900 text-white' : 'border' }}">2</span>
        <span class="{{ $step === 2 ? 'font-medium' : '' }}">Location</span>
        <span class="h-px w-10 bg-zinc-300"></span>

        <span class="inline-flex h-6 w-6 items-center justify-center rounded-full {{ $step >= 3 ? 'bg-zinc-900 text-white' : 'border' }}">3</span>
        <span class="{{ $step === 3 ? 'font-medium' : '' }}">Skills</span>
    </div>

    {{-- STEP 1: Account --}}
    @if ($step === 1)
        <form wire:submit.prevent="next" class="flex flex-col gap-6">
            <flux:input name="name" :label="__('Name')" type="text" wire:model.defer="name" required autofocus autocomplete="name" :placeholder="__('Full name')" />

            <flux:input name="email" :label="__('Email address')" type="email" wire:model.defer="email" required autocomplete="email" placeholder="email@example.com" />

            <div class="grid gap-6 sm:grid-cols-2">
                <div>
                    <flux:input name="password" :label="__('Password')" type="password" wire:model.defer="password" required autocomplete="new-password" :placeholder="__('Password')" viewable />
                </div>
                <div>
                    <flux:input name="password_confirmation" :label="__('Confirm password')" type="password" wire:model.defer="password_confirmation" required autocomplete="new-password" :placeholder="__('Confirm password')" viewable />
                </div>
            </div>

            <flux:button variant="primary" type="submit" class="w-full">
                {{ __('Continue') }}
            </flux:button>
        </form>
    @endif

    {{-- STEP 2: Location --}}
    @if ($step === 2)
        <div class="flex flex-col gap-6">
            <div class="rounded-lg border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-900 p-4">
                <h3 class="text-sm font-semibold text-neutral-900 dark:text-neutral-100">
                    Why we ask for your location
                </h3>

                <p class="mt-1 text-sm text-neutral-600 dark:text-neutral-300">
                    We use an <strong>approximate</strong> location to match you with nearby people for quicker, more convenient meetups.
                    Your <strong>exact address is never shared</strong>.
                </p>

                <ul class="mt-3 space-y-1.5 text-sm text-neutral-600 dark:text-neutral-300">
                    <li class="flex gap-2">
                        <span class="mt-1 h-1.5 w-1.5 rounded-full bg-neutral-400 dark:bg-neutral-500"></span>
                        Better matches with people close to you
                    </li>
                    <li class="flex gap-2">
                        <span class="mt-1 h-1.5 w-1.5 rounded-full bg-neutral-400 dark:bg-neutral-500"></span>
                        Easier to coordinate time and place
                    </li>

                </ul>

                <p class="mt-3 text-xs text-neutral-500 dark:text-neutral-400">
                    You can move the map pin to a nearby landmark or choose a broad area for extra privacy.
                </p>
            </div>

            {{-- Optional label text field (neighborhood/city) --}}
            <div>
                <flux:input
                    name="location"
                    :label="__('Location label (optional)')"
                    type="text"
                    wire:model.defer="location"
                    :placeholder="__('e.g., Downtown Regina')"
                    autocomplete="address-level2"
                />
                @error('location') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            {{-- Map --}}
            <div
                id="map-picker"
                class="map-box border"
                data-map-picker
                data-lat="{{ $lat ?? '50.4452' }}"
                data-lng="{{ $lng ?? '-104.6189' }}"
                data-lat-target="[data-lat-input]"
                data-lng-target="[data-lng-input]"
                data-locate-btn="#use-my-location"
            ></div>

            <div class="flex items-center gap-3">
                <flux:button type="button" id="use-my-location" variant="outline">
                    {{ __('Use my location') }}
                </flux:button>

                <div class="text-sm text-zinc-600">
                    {{ __('Click the map or drag the pin to set your approximate location.') }}
                </div>
            </div>

            {{-- Hidden (Livewire-bound) fields updated by map JS --}}
            <input type="hidden" wire:model="lat" data-lat-input>
            <input type="hidden" wire:model="lng" data-lng-input>
            @error('lat') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            @error('lng') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror

            <div class="flex items-center justify-between gap-3">
                <flux:button type="button" variant="outline" class="w-32" wire:click="back">
                    {{ __('Back') }}
                </flux:button>
                <flux:button type="button" variant="primary" class="w-32" wire:click="next">
                    {{ __('Continue') }}
                </flux:button>
            </div>
        </div>
    @endif

    {{-- STEP 3: Skills + final POST to Fortify --}}
    @if ($step === 3)
        <form method="POST" action="{{ route('register.store') }}" class="flex flex-col gap-6">
            @csrf

            {{-- Search + category filters --}}
            <div class="grid gap-3">

                <p class="text-sm text-neutral-600 dark:text-neutral-400 mt-3 leading-relaxed">
                    We use your skills to match you with people who are looking to learn from someone like you —
                    or who can teach you what you want to learn.
                    <br><br>
                    You can update these skills anytime later, and they help improve the accuracy of your match suggestions.
                </p>
                <div>
                    <label class="text-sm font-medium">{{ __('Search skills or categories') }}</label>
                    <input
                        type="text"
                        class="mt-1 w-full rounded-lg border px-3 py-2"
                        placeholder="{{ __('e.g. Laravel, Guitar, Design…') }}"
                        wire:model.live="query"
                    />
                </div>

                {{-- Popular category chips (top-level skills) --}}
                <div class="flex flex-wrap gap-2">
                    @foreach ($popularCategories as $cat)
                        <button
                            type="button"
                            class="px-3 py-1 rounded-full border text-sm
                                   {{ $categoryId === $cat['id'] ? 'bg-zinc-900 text-white' : '' }}"
                            wire:click="toggleCategory({{ $cat['id'] }})"
                        >
                            {{ $cat['name'] }}
                        </button>
                    @endforeach

                    @if ($categoryId)
                        <button
                            type="button"
                            class="px-3 py-1 rounded-full border text-sm"
                            wire:click="$set('categoryId', null)"
                            title="{{ __('Clear category') }}"
                        >
                            ✕ {{ __('Clear') }}
                        </button>
                    @endif
                </div>
            </div>

            {{-- Skills grid (DB-backed, clickable chips, emoji) --}}
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                @forelse ($this->filteredSkills as $s)
                    @php $isSelected = in_array($s->id, $selectedSkills, true); @endphp

                    <button
                        type="button"
                        class="flex items-center gap-2 rounded-lg border px-3 py-2 text-left
                               {{ $isSelected ? 'bg-zinc-800 text-white' : '' }}"
                        wire:click="toggleSkill({{ $s->id }})"
                    >
                        <span class="text-base">{{ $s->emoji ?? '🧠' }}</span>
                        <span class="text-sm">{{ $s->name }}</span>
                    </button>
                @empty
                    <p class="col-span-full text-sm text-zinc-500">
                        {{ __('No skills match your search.') }}
                    </p>
                @endforelse
            </div>

            {{-- Hidden fields sent to Fortify --}}
            <input type="hidden" name="name" value="{{ $name }}">
            <input type="hidden" name="email" value="{{ $email }}">
            <input type="hidden" name="password" value="{{ $password }}">
            <input type="hidden" name="password_confirmation" value="{{ $password_confirmation }}">

            {{-- From Step 2 --}}
            <input type="hidden" name="location" value="{{ $location }}">
            <input type="hidden" name="lat" value="{{ $lat }}">
            <input type="hidden" name="lng" value="{{ $lng }}">

            {{-- Selected skills (IDs) --}}
            @foreach ($selectedSkills as $id)
                <input type="hidden" name="skills[]" value="{{ $id }}">
            @endforeach

            <div class="flex items-center justify-between gap-3">
                <flux:button type="button" variant="outline" class="w-32" wire:click="back">
                    {{ __('Back') }}
                </flux:button>

                <flux:button type="submit" variant="primary" class="w-40">
                    {{ __('Create account') }}
                </flux:button>
            </div>
        </form>
    @endif
</div>
