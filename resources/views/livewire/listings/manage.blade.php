<x-slot name="title">{{ __('My Listings') }}</x-slot>

<div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">

    @if (session('status'))
        <div class="rounded-lg border border-green-300 bg-green-50 px-3 py-2 text-sm text-green-800 dark:border-green-700 dark:bg-green-900/30 dark:text-green-200">
            {{ session('status') }}
        </div>
    @endif

    {{-- Header + search --}}
    <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
        <a href="{{ route('listings.create') }}" class="inline-flex items-center justify-center rounded-lg bg-zinc-900 px-4 py-2 text-sm font-medium text-white">
            {{ __('Create Listing') }}
        </a>

        <div class="md:w-80">
            <label class="text-sm font-medium">{{ __('Search') }}</label>
            <input type="text"
                   class="mt-1 w-full rounded-lg border px-3 py-2"
                   placeholder="{{ __('Search by description or skill…') }}"
                   wire:model.live="q" />
        </div>
    </div>

    {{-- Table --}}
    <div class="overflow-hidden rounded-xl border border-neutral-200 bg-white dark:border-neutral-700 dark:bg-neutral-900">
        <table class="min-w-full text-sm">
            <thead class="bg-neutral-50 dark:bg-neutral-900/50">
            <tr>
                <th class="px-4 py-2 text-left font-medium">{{ __('Desired Skill') }}</th>
                <th class="px-4 py-2 text-left font-medium">{{ __('Category') }}</th>
                <th class="px-4 py-2 text-left font-medium">{{ __('Description') }}</th>
                <th class="px-4 py-2 text-right font-medium">{{ __('Actions') }}</th>
            </tr>
            </thead>
            <tbody>
            @forelse ($listings as $listing)
                <tr class="border-t border-neutral-200 dark:border-neutral-700">
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-2">
                            <span>{{ $listing->desiredSkill?->emoji ?? '🧠' }}</span>
                            <span>{{ $listing->desiredSkill?->name }}</span>
                        </div>
                    </td>
                    <td class="px-4 py-3">
                        {{ $listing->desiredSkill?->parent?->name ?? '—' }}
                    </td>
                    <td class="px-4 py-3">
                        <span class="text-neutral-800 dark:text-neutral-100">{{ $listing->description ?? '—' }}</span>
                    </td>
                    <td class="px-4 py-3 text-right">
                        <button type="button"
                                class="mr-2 rounded border px-3 py-1 text-xs"
                                wire:click="openEdit({{ $listing->id }})">
                            {{ __('Edit') }}
                        </button>
                        <button type="button"
                                class="rounded border border-red-300 px-3 py-1 text-xs text-red-700 dark:border-red-700 dark:text-red-300"
                                wire:click="confirmDelete({{ $listing->id }})">
                            {{ __('Delete') }}
                        </button>
                    </td>
                </tr>
            @empty
                <tr class="border-t border-neutral-200 dark:border-neutral-700">
                    <td colspan="4" class="px-4 py-6 text-center text-neutral-500">
                        {{ __('You have no listings yet.') }}
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <div>
        {{ $listings->links() }}
    </div>

    {{-- Edit Modal --}}
    @if ($editOpen)
        <div class="fixed inset-0 z-40 bg-black/30" wire:click="closeEdit"></div>
        <div class="fixed inset-x-0 z-50 mx-auto mt-24 w-full max-w-xl rounded-xl border border-neutral-200 bg-white p-4 shadow-xl dark:border-neutral-700 dark:bg-neutral-900" wire:click.stop>
            <div class="mb-2 flex items-center justify-between">
                <h3 class="text-base font-semibold">{{ __('Edit Listing') }}</h3>
                <button class="text-sm underline" wire:click="closeEdit">{{ __('Close') }}</button>
            </div>

            {{-- Desired Skill (dropdown tree) --}}
            <div class="mb-4" wire:click.outside="closeTree">
                <label class="text-sm font-medium">{{ __('Desired skill') }}</label>
                <button type="button"
                        class="mt-1 flex w-full items-center justify-between rounded-lg border px-3 py-2 text-left"
                        wire:click="toggleTree">
                    <span class="truncate">{{ $this->selectedLabel }}</span>
                    <span class="ml-2 text-base leading-none">{{ $treeOpen ? '▴' : '▾' }}</span>
                </button>
                @error('desiredSkillId') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror

                @if ($treeOpen)
                    <div class="absolute z-50 mt-1 w-[calc(100%-2rem)] rounded-lg border bg-white p-2 shadow-md dark:border-neutral-700 dark:bg-neutral-900">
                        <div class="max-h-64 space-y-1 overflow-auto">
                            @foreach ($this->tree as $parent)
                                <div class="rounded-md">
                                    <div class="flex items-center gap-1">
                                        <button type="button"
                                                class="inline-flex h-8 w-8 items-center justify-center text-xl"
                                                wire:click.stop="toggleExpand({{ $parent['id'] }})">
                                            {{ ($expanded[$parent['id']] ?? false) ? '▾' : '▸' }}
                                        </button>
                                        <button type="button"
                                                class="flex-1 rounded-md px-2 py-1 text-left text-sm {{ $desiredSkillId === $parent['id'] ? 'bg-zinc-900 text-white' : '' }}"
                                                wire:click="selectCategory({{ $parent['id'] }})">
                                            {{ $parent['name'] }}
                                        </button>
                                    </div>

                                    @if ($expanded[$parent['id']] ?? false)
                                        <div class="ml-8 pl-2 mt-1 space-y-0.5 border-l border-neutral-200 dark:border-neutral-700">
                                            @forelse ($parent['children'] as $child)
                                                <button type="button"
                                                        class="w-full rounded-md px-2 py-1 text-left text-sm {{ $desiredSkillId === $child['id'] ? 'bg-zinc-900 text-white' : '' }}"
                                                        wire:click="selectCategory({{ $child['id'] }})">
                                                    {{ $child['name'] }}
                                                </button>
                                            @empty
                                                <div class="px-2 py-1 text-xs text-zinc-500">{{ __('No sub-skills') }}</div>
                                            @endforelse
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            {{-- Description --}}
            <div class="mb-4">
                <label class="text-sm font-medium">{{ __('Description (optional)') }}</label>
                <textarea rows="4" class="mt-1 w-full rounded-lg border px-3 py-2"
                          placeholder="{{ __('What specifically do you want to learn?') }}"
                          wire:model.defer="description"></textarea>
                @error('description') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div class="flex items-center justify-end gap-2">
                <button type="button" class="rounded-lg border px-4 py-2 text-sm" wire:click="closeEdit">
                    {{ __('Cancel') }}
                </button>
                <button type="button" class="rounded-lg bg-zinc-900 px-4 py-2 text-sm font-medium text-white" wire:click="saveEdit">
                    {{ __('Save changes') }}
                </button>
            </div>
        </div>
    @endif

    {{-- Delete Confirm --}}
    @if ($confirmDeleteOpen)
        <div class="fixed inset-0 z-40 bg-black/30" wire:click="cancelDelete"></div>
        <div class="fixed inset-x-0 z-50 mx-auto mt-32 w-full max-w-md rounded-xl border border-neutral-200 bg-white p-4 shadow-xl dark:border-neutral-700 dark:bg-neutral-900" wire:click.stop>
            <h3 class="mb-2 text-base font-semibold">{{ __('Delete Listing') }}</h3>
            <p class="mb-4 text-sm text-neutral-600 dark:text-neutral-300">
                {{ __('Are you sure you want to delete this listing? This action cannot be undone.') }}
            </p>
            <div class="flex items-center justify-end gap-2">
                <button class="rounded-lg border px-4 py-2 text-sm" wire:click="cancelDelete">{{ __('Cancel') }}</button>
                <button class="rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white" wire:click="deleteConfirmed">{{ __('Delete') }}</button>
            </div>
        </div>
    @endif
</div>
