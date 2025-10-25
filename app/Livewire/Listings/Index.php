<?php

namespace App\Livewire\Listings;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Listing;
use App\Models\Skill;

class Index extends Component
{
    use WithPagination;

    /** Filters */
    public string $q = '';
    public ?int $categoryId = null;          // selected node (parent or child)
    public bool $matchMySkills = false;      // show listings whose desired skill I (viewer) have
    public int $perPage = 9;

    /** Category tree (parents + children) for the dropdown */
    public array $tree = [];

    /** Dropdown + expand/collapse UI state */
    public bool $treeOpen = false;           // dropdown open/close
    public array $expanded = [];             // [parentId => true]

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
                ->map(fn ($c) => ['id' => (int) $c->id, 'name' => $c->name])
                ->all();

            return [
                'id' => (int) $p->id,
                'name' => $p->name,
                'children' => $children,
            ];
        })->all();

        // start collapsed
        $this->expanded = [];
    }

    /* ----------------------- UI actions ----------------------- */

    public function toggleTree(): void   { $this->treeOpen = !$this->treeOpen; }
    public function closeTree(): void    { $this->treeOpen = false; }

    public function toggleExpand(int $parentId): void
    {
        $this->expanded[$parentId] = !($this->expanded[$parentId] ?? false);
    }

    public function expandAll(): void
    {
        $this->expanded = collect($this->tree)
            ->mapWithKeys(fn ($p) => [$p['id'] => true])
            ->all();
    }

    public function collapseAll(): void
    {
        $this->expanded = [];
    }

    /** Select a parent or child node (close dropdown after pick) */
    public function selectCategory(?int $id): void
    {
        $this->categoryId = $id;
        $this->resetPage();
        $this->treeOpen = false;
    }

    /** Label shown in the closed "select box" */
    public function getSelectedCategoryLabelProperty(): string
    {
        if (!$this->categoryId) return __('All categories');
        $node = Skill::query()->find($this->categoryId, ['id','name']);
        return $node?->name ?? __('All categories');
    }

    /* -------------------- Livewire hooks ---------------------- */

    public function updatingQ()             { $this->resetPage(); }
    public function updatingCategoryId()    { $this->resetPage(); }
    public function updatingMatchMySkills() { $this->resetPage(); }

    /* --------------------- Query helpers ---------------------- */

    /**
     * Allowed skill IDs to match against the LISTING OWNER'S skills.
     * - If a parent is selected → include parent + all its children
     * - If a child is selected  → include only that child
     */
    private function allowedSkillIds(): array
    {
        if (!$this->categoryId) return [];

        $node = Skill::query()->find($this->categoryId, ['id','skill_id']);
        if (!$node) return [];

        // Parent
        if ($node->skill_id === null) {
            $childIds = Skill::where('skill_id', $node->id)
                ->pluck('id')
                ->map(fn ($v) => (int) $v)
                ->all();

            return array_values(array_unique(array_merge([$node->id], $childIds)));
        }

        // Child
        return [(int) $node->id];
    }

    /* ------------------------ Render -------------------------- */

    public function render()
    {
        $user = auth()->user();

        // Viewer’s own skills for "match my skills"
        $mySkillIds = $user
            ? $user->skills()->pluck('skills.id')->map(fn ($v) => (int) $v)
            : collect();

        $q = trim(mb_strtolower($this->q));
        $ownerAllowedSkillIds = $this->allowedSkillIds(); // filter on listing owner's skills

        $listings = Listing::query()
            ->with(['user.skills', 'desiredSkill.parent'])

            // Filter by LISTING OWNER'S skills (tree-aware)
            ->when($this->categoryId && !empty($ownerAllowedSkillIds), function ($qr) use ($ownerAllowedSkillIds) {
                $qr->whereHas('user.skills', function ($qs) use ($ownerAllowedSkillIds) {
                    $qs->whereIn('skills.id', $ownerAllowedSkillIds);
                });
            })

            // Search in description, desired skill, or user name
            ->when($q !== '', function ($qr) use ($q) {
                $qr->where(function ($sub) use ($q) {
                    $sub->whereRaw('LOWER(description) LIKE ?', ['%'.$q.'%'])
                        ->orWhereHas('desiredSkill', fn ($ds) => $ds->whereRaw('LOWER(name) LIKE ?', ['%'.$q.'%']))
                        ->orWhereHas('user', fn ($u) => $u->whereRaw('LOWER(name) LIKE ?', ['%'.$q.'%']));
                });
            })

            // Show listings that seek skills I have
            ->when($this->matchMySkills && $mySkillIds->isNotEmpty(), function ($qr) use ($mySkillIds) {
                $qr->whereIn('desired_skill_id', $mySkillIds);
            })

            ->latest()
            ->paginate($this->perPage);

        return view('livewire.listings.index', compact('listings'));
    }
}
