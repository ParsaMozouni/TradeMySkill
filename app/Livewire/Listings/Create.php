<?php

namespace App\Livewire\Listings;

use Livewire\Component;
use App\Models\Skill;
use App\Models\Listing;

class Create extends Component
{
    public ?int $desiredSkillId = null;
    public string $description = '';

    /** Tree data (parents + children) for dropdown */
    public array $tree = [];

    /** Dropdown UI state */
    public bool $treeOpen = false;
    public array $expanded = []; // [parentId => true]

    public function mount(): void
    {
        // Build parent + children tree
        $parents = Skill::query()
            ->whereNull('skill_id')
            ->orderBy('name')
            ->get(['id','name']);

        $this->tree = $parents->map(function ($p) {
            $children = Skill::where('skill_id', $p->id)
                ->orderBy('name')
                ->get(['id','name'])
                ->map(fn($c) => ['id' => (int) $c->id, 'name' => $c->name])
                ->all();

            return [
                'id' => (int) $p->id,
                'name' => $p->name,
                'children' => $children,
            ];
        })->all();

        $this->expanded = []; // collapsed by default
    }

    /* ---------- Dropdown actions ---------- */
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

    /* ---------- Validation ---------- */
    protected function rules(): array
    {
        return [
            'desiredSkillId' => ['required', 'integer', 'exists:skills,id'],
            'description'    => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function save(): mixed
    {
        $this->validate();

        $user = auth()->user();

        Listing::create([
            'user_id'          => $user->id,
            'desired_skill_id' => $this->desiredSkillId,
            'description'      => $this->description ?: null,
        ]);

        session()->flash('status', __('Listing created'));
        return redirect()->route('listings.index');
    }

    public function render()
    {
        // Load my skills for display (emoji pills)
        $mySkills = auth()->user()
            ? auth()->user()->skills()->orderBy('name')->get(['name','emoji'])
            : collect();

        return view('livewire.listings.create', compact('mySkills'))
            ->layout('components.layouts.app', ['title' => __('Create Listing')]);
    }
}
