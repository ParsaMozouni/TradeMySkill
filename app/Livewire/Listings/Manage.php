<?php

namespace App\Livewire\Listings;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Listing;
use App\Models\Skill;
use Illuminate\Validation\Rule;

class Manage extends Component
{
    use WithPagination;

    public string $q = '';
    public int $perPage = 10;

    /** Edit modal state */
    public bool $editOpen = false;
    public ?int $editingId = null;
    public ?int $desiredSkillId = null;
    public string $description = '';

    /** Delete confirm state */
    public bool $confirmDeleteOpen = false;
    public ?int $deletingId = null;

    /** Tree data + dropdown state for desiredSkill */
    public array $tree = [];
    public bool $treeOpen = false;
    public array $expanded = [];

    public function mount(): void
    {
        // build parent + children tree once
        $parents = Skill::query()->whereNull('skill_id')->orderBy('name')->get(['id','name']);
        $this->tree = $parents->map(function ($p) {
            $children = Skill::where('skill_id', $p->id)->orderBy('name')->get(['id','name'])
                ->map(fn($c)=>['id'=>(int)$c->id,'name'=>$c->name])->all();
            return ['id'=>(int)$p->id,'name'=>$p->name,'children'=>$children];
        })->all();

        $this->expanded = [];
    }

    public function updatingQ(){ $this->resetPage(); }

    /* ---------- Edit flow ---------- */

    public function openEdit(int $listingId): void
    {
        $listing = Listing::where('user_id', auth()->id())->findOrFail($listingId);

        $this->editingId     = $listing->id;
        $this->desiredSkillId = $listing->desired_skill_id;
        $this->description    = (string) ($listing->description ?? '');

        $this->editOpen = true;
        $this->treeOpen = false;
    }

    public function closeEdit(): void
    {
        $this->reset(['editOpen','editingId','desiredSkillId','description','treeOpen']);
    }

    public function saveEdit(): void
    {
        $this->validate([
            'editingId'      => ['required', Rule::exists('listings','id')->where('user_id', auth()->id())],
            'desiredSkillId' => ['required','integer','exists:skills,id'],
            'description'    => ['nullable','string','max:1000'],
        ]);

        $listing = Listing::where('user_id', auth()->id())->findOrFail($this->editingId);

        $listing->update([
            'desired_skill_id' => $this->desiredSkillId,
            'description'      => $this->description ?: null,
        ]);

        session()->flash('status', __('Listing updated.'));
        $this->closeEdit();
    }

    /* ---------- Delete flow ---------- */

    public function confirmDelete(int $listingId): void
    {
        // ensure ownership
        $exists = Listing::where('user_id', auth()->id())->where('id', $listingId)->exists();
        if (! $exists) return;

        $this->deletingId = $listingId;
        $this->confirmDeleteOpen = true;
    }

    public function cancelDelete(): void
    {
        $this->reset(['confirmDeleteOpen','deletingId']);
    }

    public function deleteConfirmed(): void
    {
        if (!$this->deletingId) return;

        $listing = Listing::where('user_id', auth()->id())->findOrFail($this->deletingId);
        $listing->delete();

        session()->flash('status', __('Listing deleted.'));
        $this->cancelDelete();
        $this->resetPage();
    }

    /* ---------- Tree dropdown helpers ---------- */

    public function toggleTree(): void   { $this->treeOpen = !$this->treeOpen; }
    public function closeTree(): void    { $this->treeOpen = false; }
    public function toggleExpand(int $parentId): void
    {
        $this->expanded[$parentId] = !($this->expanded[$parentId] ?? false);
    }
    public function selectCategory(?int $id): void
    {
        $this->desiredSkillId = $id;
        $this->treeOpen = false;
    }
    public function getSelectedLabelProperty(): string
    {
        if (!$this->desiredSkillId) return __('Select a skill');
        $node = Skill::query()->find($this->desiredSkillId, ['id','name']);
        return $node?->name ?? __('Select a skill');
    }

    public function render()
    {
        $userId = auth()->id();
        $q = trim(mb_strtolower($this->q));

        $listings = Listing::query()
            ->with(['desiredSkill.parent'])
            ->where('user_id', $userId)
            ->when($q !== '', function ($qr) use ($q) {
                $qr->where(function ($sub) use ($q) {
                    $sub->whereRaw('LOWER(description) LIKE ?', ['%'.$q.'%'])
                        ->orWhereHas('desiredSkill', fn($ds) => $ds->whereRaw('LOWER(name) LIKE ?', ['%'.$q.'%']));
                });
            })
            ->latest()
            ->paginate($this->perPage);

        return view('livewire.listings.manage', compact('listings'))
            ->layout('components.layouts.app', ['title' => __('My Listings')]);
    }
}
