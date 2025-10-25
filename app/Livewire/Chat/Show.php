<?php

namespace App\Livewire\Chat;

use App\Models\Message;
use App\Models\User;
use Livewire\Component;

class Show extends Component
{
    public User $other;
    public string $text = '';

    protected $rules = [
        'text' => ['required', 'string', 'max:2000'],
    ];

    public function getLastOutgoingProperty(): ?Message
    {
        $me = auth()->id();

        return Message::where('sender_id', $me)
            ->where('receiver_id', $this->other->id)
            ->latest('id')
            ->first();
    }

    public function mount(User $user): void
    {
        abort_unless(auth()->check() && $user->id !== auth()->id(), 403);
        $this->other = $user;
        $this->markAsRead();
    }
    private function markAsRead(): void
    {
        \App\Models\Message::where('sender_id', $this->other->id)
            ->where('receiver_id', auth()->id())
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }

    public function send(): void
    {
        $this->validate();

        Message::create([
            'sender_id'   => auth()->id(),
            'receiver_id' => $this->other->id,
            'body'        => $this->text,
        ]);

        $this->text = '';
        $this->dispatch('chat:scroll-bottom');
    }

    public function getMessagesProperty()
    {
        $me = auth()->id();

        return Message::query()
            ->where(function ($q) use ($me) {
                $q->where('sender_id', $me)
                    ->where('receiver_id', $this->other->id);
            })
            ->orWhere(function ($q) use ($me) {
                $q->where('sender_id', $this->other->id)
                    ->where('receiver_id', $me);
            })
            ->orderBy('created_at', 'asc')
            ->get();
    }

    public function render()
    {
        return view('livewire.chat.show', [
            'messages'        => $this->messages,
            'lastOutgoing'    => $this->lastOutgoing,
        ])->layout('components.layouts.app', ['title' => __('Chat')]);
    }
}
