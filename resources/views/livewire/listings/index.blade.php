<div>
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">

        {{-- Filters --}}
        <div class="rounded-xl border border-neutral-200 bg-white p-4 dark:border-neutral-700 dark:bg-neutral-900">
            <div class="grid gap-3 md:grid-cols-3">
                {{-- Search --}}
                <div>
                    <label class="text-sm font-medium">{{ __('Search') }}</label>
                    <input type="text"
                           class="mt-1 w-full rounded-lg border px-3 py-2"
                           placeholder="{{ __('Search by text, desired skill, or user name…') }}"
                           wire:model.live="q" />
                </div>

                {{-- Category (Dropdown Tree Select) --}}
                <div class="relative" wire:click.outside="closeTree">
                    <label class="text-sm font-medium">{{ __('Category') }}</label>

                    {{-- The "select box" button --}}
                    <button type="button"
                            class="mt-1 flex w-full items-center justify-between rounded-lg border px-3 py-2 text-left"
                            wire:click="toggleTree">
                        <span class="truncate">
                            {{ $this->selectedCategoryLabel }}
                        </span>
                        <span class="ml-2 text-2xl">{{ $treeOpen ? '▴' : '▾' }}</span>
                    </button>

                    {{-- Dropdown panel --}}
                    @if ($treeOpen)
                        <div class="absolute z-10 mt-1 w-full rounded-lg border bg-white p-2 shadow-md dark:border-neutral-700 dark:bg-neutral-900">
                            {{-- “All categories” row --}}
                            <button type="button"
                                    class="flex w-full items-center rounded-md px-2 py-1 text-left text-sm {{ $categoryId ? '' : 'bg-zinc-900 text-white' }}"
                                    wire:click="selectCategory(null)">
                                {{ __('All categories') }}
                            </button>

                            <div class="mt-1 max-h-64 space-y-1 overflow-auto">
                                @foreach ($this->tree as $parent)
                                    <div class="rounded-md">
                                        {{-- Parent row --}}
                                        <div class="flex items-center gap-1">
                                            <button type="button"
                                                    class="inline-flex h-8 w-8 items-center justify-center text-2xl"
                                                    aria-label="{{ ($expanded[$parent['id']] ?? false) ? 'Collapse' : 'Expand' }}"
                                                    aria-expanded="{{ ($expanded[$parent['id']] ?? false) ? 'true' : 'false' }}"
                                                    wire:click.stop="toggleExpand({{ $parent['id'] }})">
                                                {{ ($expanded[$parent['id']] ?? false) ? '▾' : '▸' }}
                                            </button>

                                            <button type="button"
                                                    class="flex-1 rounded-md px-2 py-1 text-left text-sm {{ $categoryId === $parent['id'] ? 'bg-zinc-900 text-white' : '' }}"
                                                    wire:click="selectCategory({{ $parent['id'] }})">
                                                {{ $parent['name'] }}
                                            </button>
                                        </div>

                                        {{-- Children (shown only when expanded) --}}
                                        @if ($expanded[$parent['id']] ?? false)
                                            <div class="ml-8 pl-2 mt-1 space-y-0.5 border-l border-neutral-200 dark:border-neutral-700">
                                                @forelse ($parent['children'] as $child)
                                                    <button type="button"
                                                            class="w-full rounded-md px-2 py-1 text-left text-sm {{ $categoryId === $child['id'] ? 'bg-zinc-900 text-white' : '' }}"
                                                            wire:click="selectCategory({{ $child['id'] }})">
                                                        {{ $child['name'] }}
                                                    </button>
                                                @empty
                                                    <div class="px-2 py-1 text-xs text-zinc-500">
                                                        {{ __('No sub-skills') }}
                                                    </div>
                                                @endforelse
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>

                            @if ($categoryId)
                                <div class="mt-2 flex justify-end">
                                    <button type="button" class="text-xs underline" wire:click="selectCategory(null)">
                                        {{ __('Clear selection') }}
                                    </button>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>

                {{-- Match my skills --}}
                <div class="flex items-end">
                    <label class="inline-flex items-center gap-2 text-sm">
                        <input type="checkbox" class="rounded" wire:model.live="matchMySkills">
                        <span>{{ __('Show listings seeking skills I have') }}</span>
                    </label>
                </div>
            </div>
        </div>

        {{-- Cards --}}
        <div class="grid auto-rows-min gap-4 md:grid-cols-3">
            @forelse ($listings as $listing)
                <div class="relative rounded-xl border border-neutral-200 bg-white p-4 dark:border-neutral-700 dark:bg-neutral-900">
                    <div class="flex items-start gap-4">
                        {{-- Left: user skills (emoji pills) --}}
                        <div class="flex min-w-0 flex-1 flex-col gap-2">
                            <div class="flex flex-wrap items-center gap-1.5">
                                <p class="text-xs text-neutral-500 px-2 py-0.5">
                                    {{ __('I have skills in :') }}
                                </p>
                                @foreach ($listing->user->skills as $sk)
                                    <span class="inline-flex items-center gap-1 rounded-full border px-2 py-0.5 text-xs dark:border-neutral-600">
                                        <span>{{ $sk->emoji ?? '🧠' }}</span>
                                        <span class="truncate max-w-[9rem]">{{ $sk->name }}</span>
                                    </span>
                                @endforeach
                            </div>

                            {{-- Middle: description + desired skill --}}
                            <div class="space-y-1">
                                <p class="text-sm text-neutral-800 dark:text-neutral-100">
                                    {{ $listing->description ?? __('No description provided.') }}
                                </p>
                                <p class="text-xs text-neutral-500">
                                    {{ __('Wants to learn: ') }}
                                    <span class="font-medium">
                                        <span class="mr-1">{{ $listing->desiredSkill?->emoji ?? '🧠' }}</span>
                                        {{ $listing->desiredSkill?->name }}
                                    </span>
                                    @if($listing->desiredSkill?->parent)
                                        <span class="text-neutral-400"> · {{ $listing->desiredSkill->parent->name }}</span>
                                    @endif
                                </p>
                            </div>

                            {{-- Actions --}}
                            <div class="mt-3 flex items-center justify-end gap-2">
                                <button type="button"
                                        class="rounded-lg border px-3 py-1 text-xs"
                                        wire:click="openDetails({{ $listing->id }})">
                                    {{ __('Details') }}
                                </button>

                                {{-- Chat stub: route can be added later --}}
                                <a href="#"
                                   class="rounded-lg bg-zinc-900 px-3 py-1 text-xs font-medium text-white">
                                    {{ __('Chat') }}
                                </a>
                            </div>
                        </div>

                        {{-- Right: avatar --}}
                        <div class="shrink-0">
                            @php
                                $avatar = method_exists($listing->user, 'profile_photo_url')
                                    ? $listing->user->profile_photo_url
                                    : 'https://ui-avatars.com/api/?name='.urlencode($listing->user->name).'&background=111&color=fff';
                            @endphp
                            <img src="{{ $avatar }}" alt="{{ $listing->user->name }}"
                                 class="h-12 w-12 rounded-full ring-1 ring-neutral-200 dark:ring-neutral-700">
                        </div>
                    </div>
                </div>
            @empty
                <div class="md:col-span-3">
                    <div class="relative h-36 overflow-hidden rounded-xl border border-dashed border-neutral-300 dark:border-neutral-700">
                        <div class="flex h-full items-center justify-center text-neutral-500">
                            {{ __('No listings match your filters.') }}
                        </div>
                    </div>
                </div>
            @endforelse
        </div>

        <div>
            {{ $listings->links() }}
        </div>
    </div>

    {{-- DETAILS MODAL --}}

    {{-- DETAILS MODAL (centered) --}}
    @if ($detailsOpen ?? false)
        {{-- Backdrop --}}
        <div class="fixed inset-0 z-40 bg-black/30" wire:click="closeDetails"></div>

        {{-- Centered container --}}
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
            {{-- Panel (note: NOT fixed/inset) --}}
            <div class="w-full max-w-2xl rounded-xl border border-neutral-200 bg-white p-4 shadow-xl dark:border-neutral-700 dark:bg-neutral-900"
                 wire:click.stop>
                {{-- Header: avatar + name --}}
                <div class="mb-3 flex items-center justify-between gap-3">
                    <div class="flex items-center gap-3">
                        @php
                            $avatar = $detail['user_avatar']
                                ?? ('https://ui-avatars.com/api/?name='.urlencode($detail['user_name'] ?? '').'&background=111&color=fff');
                        @endphp
                        <img src="{{ $avatar }}"
                             alt="{{ $detail['user_name'] ?? '' }}"
                             class="h-10 w-10 rounded-full ring-1 ring-neutral-200 dark:ring-neutral-700">
                        <div>
                            <h3 class="text-base font-semibold">
                                {{ $detail['user_name'] ?? '' }}
                            </h3>
                            @if(!empty($detail['location']))
                                <p class="text-xs text-neutral-500">{{ $detail['location'] }}</p>
                            @endif
                        </div>
                    </div>

                    <button class="text-sm underline" wire:click="closeDetails">{{ __('Close') }}</button>
                </div>

                {{-- Desired + description --}}
                <div class="mb-3 space-y-1">
                    <p class="text-sm">
                        <span class="text-neutral-500">{{ __('Wants to learn:') }}</span>
                        <span class="ml-1 font-medium">
                        <span class="mr-1">{{ $detail['desired']['emoji'] ?? '🧠' }}</span>
                        {{ $detail['desired']['name'] ?? '' }}
                    </span>
                        @if(!empty($detail['desired']['parent']))
                            <span class="text-neutral-400">· {{ $detail['desired']['parent'] }}</span>
                        @endif
                    </p>
                    @if(!empty($detail['description']))
                        <p class="text-sm text-neutral-800 dark:text-neutral-100">{{ $detail['description'] }}</p>
                    @endif
                </div>

                {{-- User skills pills --}}
                <div class="mb-3">
                    <p class="mb-1 text-xs text-neutral-500">{{ __('Has skills:') }}</p>
                    <div class="flex flex-wrap gap-1.5">
                        @foreach ($detail['user_skills'] as $sk)
                            <span class="inline-flex items-center gap-1 rounded-full border px-2 py-0.5 text-xs dark:border-neutral-600">
                            <span>{{ $sk['emoji'] ?? '🧠' }}</span>
                            <span class="truncate max-w-[9rem]">{{ $sk['name'] }}</span>
                        </span>
                        @endforeach
                    </div>
                </div>

                {{-- Map (readonly, privacy circle) --}}
                <div class="mb-4">
                    @if(!empty($detail['lat']) && !empty($detail['lng']))
                        <div
                            id="listing-map"
                            class="h-64 w-full rounded-lg border"
                            data-map-view
                            data-lat="{{ $detail['lat'] }}"
                            data-lng="{{ $detail['lng'] }}"
                            data-radius="{{ $detail['radius_m'] ?? 1500 }}"
                            wire:ignore
                        ></div>
                        <p class="mt-1 text-[11px] text-neutral-500">
                            {{ __('Approximate area only (privacy protected).') }}
                        </p>
                    @else
                        <div class="flex h-32 items-center justify-center rounded-lg border border-dashed text-xs text-neutral-500">
                            {{ __('Location not available.') }}
                        </div>
                    @endif
                </div>

                {{-- Actions --}}
                <div class="flex items-center justify-end gap-2">
                    <button type="button" class="rounded-lg border px-3 py-2 text-sm" wire:click="closeDetails">
                        {{ __('Close') }}
                    </button>
                    <a href="#"
                       class="rounded-lg bg-zinc-900 px-4 py-2 text-sm font-medium text-white">
                        {{ __('Chat') }}
                    </a>
                </div>
            </div>
        </div>

        {{-- tell JS to boot the map once modal is in DOM --}}
        <script>document.dispatchEvent(new CustomEvent('detail-map:init'));</script>
    @endif
</div>
