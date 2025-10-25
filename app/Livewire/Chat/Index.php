<?php

namespace App\Livewire\Chat;

use App\Models\Message;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $q = '';
    public int $perPage = 15;

    public function updatingQ() { $this->resetPage(); }

    public function render()
    {
        $me = auth()->id();

        // 1) Get the latest message id for each counterpart (thread)
        $lastIds = Message::query()
            ->selectRaw('MAX(messages.id) as last_id')
            ->selectRaw('CASE WHEN sender_id = ? THEN receiver_id ELSE sender_id END as user_id', [$me])
            ->where(fn($q)=>$q->where('sender_id',$me)->orWhere('receiver_id',$me))
            ->groupBy('user_id')
            ->pluck('last_id');

        // 2) Load those last messages as the thread rows (newest first)
        $threads = Message::with(['sender','receiver'])
            ->whereIn('id', $lastIds)
            ->when(trim($this->q) !== '', function ($qr) use ($me) {
                $q = mb_strtolower(trim($this->q));
                $qr->where(function ($sub) use ($q, $me) {
                    // match counterpart name or message body
                    $sub->whereRaw('LOWER(body) LIKE ?', ['%'.$q.'%'])
                        ->orWhereHas('sender', fn($u)=>$u->where('id','!=',$me)->whereRaw('LOWER(name) LIKE ?', ['%'.$q.'%']))
                        ->orWhereHas('receiver',fn($u)=>$u->where('id','!=',$me)->whereRaw('LOWER(name) LIKE ?', ['%'.$q.'%']));
                });
            })
            ->orderByDesc('created_at')
            ->paginate($this->perPage);

        // 3) Compute unread counts per counterpart
        $otherIds = $threads->map(fn($m)=> $m->sender_id === $me ? $m->receiver_id : $m->sender_id)->unique()->all();

        $unread = Message::query()
            ->select('sender_id', DB::raw('COUNT(*) as c'))
            ->whereIn('sender_id', $otherIds)
            ->where('receiver_id', $me)
            ->whereNull('read_at')
            ->groupBy('sender_id')
            ->pluck('c','sender_id'); // [other_id => count]

        return view('livewire.chat.index', [
            'threads' => $threads,
            'unread'  => $unread,
            'me'      => $me,
        ])->layout('components.layouts.app', ['title' => __('Chats')]);
    }
}
