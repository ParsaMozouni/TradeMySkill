<x-slot name="title">{{ __('Chats') }}</x-slot>

{{-- wire:poll makes the list refresh itself --}}
<div class="flex h-[calc(100vh-8rem)] flex-col rounded-xl border border-neutral-200 bg-white dark:border-neutral-700 dark:bg-neutral-900"
     wire:poll.7s>
    {{-- Header / Search --}}
    <div class="flex items-center justify-between gap-3 border-b border-neutral-200 p-3 dark:border-neutral-700">
        <h2 class="text-base font-semibold">{{ __('Messages') }}</h2>
        <div class="flex items-center gap-2">
            <div class="w-72">
                <input type="text"
                       wire:model.live="q"
                       placeholder="{{ __('Search by name or message…') }}"
                       class="w-full rounded-lg border px-3 py-2 text-sm" />
            </div>
            <button type="button"
                    wire:click="$refresh"
                    class="rounded-lg border px-3 py-2 text-xs">
                {{ __('Refresh') }}
            </button>
        </div>
    </div>

    {{-- Threads list --}}
    <div class="flex-1 overflow-y-auto">
        @forelse ($threads as $m)
            @php
                $other = $m->sender_id === $me ? $m->receiver : $m->sender;
                $avatar = method_exists($other, 'profile_photo_url')
                    ? $other->profile_photo_url
                    : 'https://ui-avatars.com/api/?name='.urlencode($other->name).'&background=111&color=fff';
                $snippet = \Illuminate\Support\Str::limit($m->body, 80);
                $unreadCount = (int) ($unread[$other->id] ?? 0);
            @endphp

            <a href="{{ route('chat.show', $other) }}"
               class="flex items-center gap-3 border-b border-neutral-200 p-3 hover:bg-neutral-50 dark:border-neutral-700 dark:hover:bg-neutral-800/50">
                {{-- Avatar (left) --}}
                <img src="{{ $avatar }}" alt="{{ $other->name }}"
                     class="h-12 w-12 shrink-0 rounded-full ring-1 ring-neutral-200 dark:ring-neutral-700">

                {{-- Name + last message (right) --}}
                <div class="min-w-0 flex-1">
                    <div class="flex items-center justify-between gap-3">
                        <p class="truncate text-sm font-semibold">
                            {{ $other->name }}
                        </p>
                        <span class="shrink-0 text-[11px] text-neutral-500">
                            {{ $m->created_at->shortAbsoluteDiffForHumans() }}
                        </span>
                    </div>

                    <div class="mt-0.5 flex items-center gap-2">
                        {{-- Unread badge --}}
                        @if ($unreadCount > 0)
                            <span class="inline-flex h-5 min-w-5 items-center justify-center rounded-full bg-blue-600 px-1 text-[11px] font-semibold text-white">
                                {{ $unreadCount }}
                            </span>
                        @endif

                        <p class="truncate text-xs text-neutral-600 dark:text-neutral-300 {{ $unreadCount ? 'font-semibold text-neutral-900 dark:text-white' : '' }}">
                            {{ $snippet }}
                        </p>
                    </div>
                </div>
            </a>
        @empty
            <div class="flex h-full items-center justify-center text-neutral-500">
                {{ __('No conversations yet.') }}
            </div>
        @endforelse
    </div>

    <div class="border-t border-neutral-200 p-2 dark:border-neutral-700">
        {{ $threads->links() }}
    </div>
</div>
