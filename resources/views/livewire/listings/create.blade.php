<x-slot name="title">{{ __('Create Listing') }}</x-slot>

<div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
    @if (session('status'))
        <div class="rounded-lg border border-green-300 bg-green-50 px-3 py-2 text-sm text-green-800 dark:border-green-700 dark:bg-green-900/30 dark:text-green-200">
            {{ session('status') }}
        </div>
    @endif

    <div class="rounded-xl border border-neutral-200 bg-white p-4 dark:border-neutral-700 dark:bg-neutral-900">
        <form wire:submit.prevent="save" class="grid gap-6 md:grid-cols-2">
            {{-- Desired skill (dropdown tree) --}}
            <div class="md:col-span-2">
                <label class="text-sm font-medium">{{ __('Desired skill (what you want to learn)') }}</label>

                <div class="relative mt-1" wire:click.outside="closeTree">
                    <button type="button"
                            class="flex w-full items-center justify-between rounded-lg border px-3 py-2 text-left"
                            wire:click="toggleTree">
                        <span class="truncate">{{ $this->selectedLabel }}</span>
                        <span class="ml-2 text-base leading-none">{{ $treeOpen ? '▴' : '▾' }}</span>
                    </button>
                    @error('desiredSkillId')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror

                    @if ($treeOpen)
                        <div class="absolute z-10 mt-1 w-full rounded-lg border bg-white p-2 shadow-md
                                    dark:border-neutral-700 dark:bg-neutral-900">
                            <div class="max-h-64 space-y-1 overflow-auto">
                                @foreach ($this->tree as $parent)
                                    <div class="rounded-md">
                                        <div class="flex items-center gap-1">
                                            <button type="button"
                                                    class="inline-flex h-8 w-8 items-center justify-center text-xl"
                                                    aria-label="{{ ($expanded[$parent['id']] ?? false) ? 'Collapse' : 'Expand' }}"
                                                    aria-expanded="{{ ($expanded[$parent['id']] ?? false) ? 'true' : 'false' }}"
                                                    wire:click.stop="toggleExpand({{ $parent['id'] }})">
                                                {{ ($expanded[$parent['id']] ?? false) ? '▾' : '▸' }}
                                            </button>

                                            <button type="button"
                                                    class="flex-1 rounded-md px-2 py-1 text-left text-sm
                                                           {{ $desiredSkillId === $parent['id'] ? 'bg-zinc-900 text-white' : '' }}"
                                                    wire:click="selectCategory({{ $parent['id'] }})">
                                                {{ $parent['name'] }}
                                            </button>
                                        </div>

                                        @if ($expanded[$parent['id']] ?? false)
                                            <div class="ml-8 pl-2 mt-1 space-y-0.5 border-l border-neutral-200 dark:border-neutral-700">
                                                @forelse ($parent['children'] as $child)
                                                    <button type="button"
                                                            class="w-full rounded-md px-2 py-1 text-left text-sm
                                                                   {{ $desiredSkillId === $child['id'] ? 'bg-zinc-900 text-white' : '' }}"
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

                            @if ($desiredSkillId)
                                <div class="mt-2 flex justify-end">
                                    <button type="button" class="text-xs underline" wire:click="selectCategory(null)">
                                        {{ __('Clear selection') }}
                                    </button>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
            </div>

            {{-- Description --}}
            <div class="md:col-span-2">
                <label for="desc" class="text-sm font-medium">{{ __('Description (optional)') }}</label>
                <textarea id="desc" rows="4"
                          class="mt-1 w-full rounded-lg border px-3 py-2"
                          placeholder="{{ __('What specifically do you want to learn? (max 1000 chars)') }}"
                          wire:model.defer="description"></textarea>
                @error('description')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- My skills (for context) --}}
            <div class="md:col-span-2">
                <p class="text-xs text-neutral-500 mb-1">{{ __('Your skills (for reference)') }}</p>
                <div class="flex flex-wrap gap-1.5">
                    @forelse ($mySkills as $sk)
                        <span class="inline-flex items-center gap-1 rounded-full border px-2 py-0.5 text-xs dark:border-neutral-600">
                            <span>{{ $sk->emoji ?? '🧠' }}</span>
                            <span class="truncate max-w-[9rem]">{{ $sk->name }}</span>
                        </span>
                    @empty
                        <span class="text-xs text-neutral-500">{{ __('No skills yet.') }}</span>
                    @endforelse
                </div>
            </div>

            {{-- Actions --}}
            <div class="md:col-span-2 flex items-center justify-end gap-3">
                <a href="{{ route('dashboard') }}" class="rounded-lg border px-4 py-2 text-sm">
                    {{ __('Cancel') }}
                </a>
                <button type="submit" class="rounded-lg bg-zinc-900 px-4 py-2 text-sm font-medium text-white">
                    {{ __('Create Listing') }}
                </button>
            </div>
        </form>
    </div>
</div>
