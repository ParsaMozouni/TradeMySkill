<x-slot name="title">{{ __('My Skills') }}</x-slot>

<div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
    @if (session('status'))
        <div class="rounded-lg border border-green-300 bg-green-50 px-3 py-2 text-sm text-green-800 dark:border-green-700 dark:bg-green-900/30 dark:text-green-200">
            {{ session('status') }}
        </div>
    @endif

    <div class="rounded-xl border border-neutral-200 bg-white p-4 dark:border-neutral-700 dark:bg-neutral-900">
        <div class="grid gap-4">
            {{-- Search --}}
            <div>
                <label class="text-sm font-medium">{{ __('Search skills or categories') }}</label>
                <input
                    type="text"
                    class="mt-1 w-full rounded-lg border px-3 py-2"
                    placeholder="{{ __('e.g. Laravel, Guitar, Design…') }}"
                    wire:model.live="query"
                />
                <span wire:loading.class.remove="hidden" class="hidden ml-2 text-xs text-zinc-500">{{ __('Searching…') }}</span>
            </div>

            {{-- Popular category chips (top-level) --}}
            <div class="flex flex-wrap gap-2">
                @foreach ($popularCategories as $cat)
                    <button
                        type="button"
                        class="px-3 py-1 rounded-full border text-sm
                               {{ $categoryId === $cat['id'] ? 'bg-zinc-700 text-white' : '' }}"
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

            {{-- Skills grid (emoji chips; click to toggle) --}}
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-2">
                @forelse ($this->filteredSkills as $s)
                    @php $isSelected = in_array($s->id, $selectedSkills, true); @endphp

                    <button
                        type="button"
                        class="flex items-center gap-2 rounded-lg border px-3 py-2 text-left
                               {{ $isSelected ? 'bg-zinc-700 text-white' : '' }}"
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

            {{-- Selected count --}}
            <div class="text-xs text-neutral-500">
                {{ trans_choice(':count skill selected|:count skills selected', count($selectedSkills), ['count' => count($selectedSkills)]) }}
            </div>

            {{-- Actions --}}
            <div class="flex items-center justify-end gap-3">
                <a href="{{ route('dashboard') }}" class="rounded-lg border px-4 py-2 text-sm">
                    {{ __('Cancel') }}
                </a>
                <button type="button"
                        class="rounded-lg bg-zinc-900 px-4 py-2 text-sm font-medium text-white"
                        wire:click="save">
                    {{ __('Save changes') }}
                </button>
            </div>
        </div>
    </div>
</div>
