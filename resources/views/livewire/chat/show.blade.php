<x-slot name="title">{{ __('Chat') }}</x-slot>

<div class="flex h-[calc(100vh-8rem)] flex-col rounded-xl border border-neutral-200 bg-white dark:border-neutral-700 dark:bg-neutral-900">

    {{-- Header --}}
    <div class="flex items-center gap-3 border-b border-neutral-200 p-3 dark:border-neutral-700">
        @php
            $avatar = method_exists($other, 'profile_photo_url')
                ? $other->profile_photo_url
                : 'https://ui-avatars.com/api/?name='.urlencode($other->name).'&background=111&color=fff';
        @endphp
        <img src="{{ $avatar }}" alt="{{ $other->name }}" class="h-10 w-10 rounded-full ring-1 ring-neutral-200 dark:ring-neutral-700">
        <div class="min-w-0">
            <p class="truncate text-sm font-semibold">{{ $other->name }}</p>
            <p class="text-xs text-neutral-500">{{ __('Direct message') }}</p>
        </div>
    </div>

    {{-- Messages (scoped polling so composer is not re-rendered) --}}
    <div id="chat-scroll"
         class="flex-1 space-y-2 overflow-y-auto p-4"
         wire:poll.5s
         wire:ignore.self
         x-data
         x-init="$nextTick(()=>{ const el=$el; el.scrollTop = el.scrollHeight; })"
         x-on:chat\:scroll-bottom.window="const el=$el; el.scrollTop = el.scrollHeight;">
        @forelse ($messages as $m)
            @php
                $mine = $m->sender_id === auth()->id();
                $isLastOutgoing = $lastOutgoing && $m->id === $lastOutgoing->id;
            @endphp

            <div class="flex {{ $mine ? 'justify-end' : 'justify-start' }}">
                <div class="{{ $mine ? 'bg-blue-600 text-white' : 'bg-neutral-100 text-neutral-900 dark:bg-neutral-800 dark:text-neutral-100' }}
                            max-w-[75%] rounded-2xl px-3 py-2 text-sm">
                    <p class="whitespace-pre-wrap break-words">{{ $m->body }}</p>
                    <p class="mt-1 text-[10px] opacity-70">
                        {{-- Use Carbon parse if needed: \Carbon\Carbon::parse($m->created_at)->format(...) --}}
                        {{ optional($m->created_at)->format('M j, H:i') }}
                    </p>
                </div>
            </div>

            {{-- Read receipt only for my last outgoing message --}}
            @if ($isLastOutgoing)
                <div class="mt-0.5 flex justify-end">
                    <span class="text-[11px] text-neutral-500">
                        {{ $m->read_at ? __('Seen ') . optional($m->read_at)->shortAbsoluteDiffForHumans() : __('Sent') }}
                    </span>
                </div>
            @endif
        @empty
            <div class="flex h-full items-center justify-center text-sm text-neutral-500">
                {{ __('Start the conversation!') }}
            </div>
        @endforelse
    </div>

    {{-- Composer (instant enable/disable with Alpine entangle) --}}
    <form wire:submit.prevent="send"
          x-data="{ t: @entangle('text').live }"
          class="flex items-center gap-2 border-t border-neutral-200 p-3 dark:border-neutral-700">
        <textarea
            x-model="t"
            wire:model.live="text"
            rows="1"
            placeholder="{{ __('Type a message…') }}"
            class="max-h-32 min-h-[42px] w-full resize-none rounded-lg border px-3 py-2 text-sm focus:outline-none"
            x-on:input="$nextTick(() => { $el.style.height='auto'; $el.style.height = Math.min($el.scrollHeight, 160) + 'px'; })"
        ></textarea>

        <button type="submit"
                class="shrink-0 rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white disabled:opacity-50"
                :disabled="!t.trim().length"
                wire:loading.attr="disabled">
            {{ __('Send') }}
        </button>
    </form>
</div>
