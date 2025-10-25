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

    /** Details modal state */
    public bool $detailsOpen = false;
    public ?int $viewingId   = null;

    /** Data shown in the details modal */
    public array $detail = [
        'listing_id'   => null,
        'user_id'      => null,
        'user_name'    => null,
        'user_skills'  => [],   // [['name'=>..., 'emoji'=>...], ...]
        'desired'      => ['name' => null, 'emoji' => null, 'parent' => null],
        'description'  => null,
        'location'     => null,
        'lat'          => null, // approximate/fuzzed
        'lng'          => null, // approximate/fuzzed
        'radius_m'     => 1500, // circle radius (1.5km)
    ];

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

    /* ------------------- Details modal logic ------------------ */

    public function openDetails(int $listingId): void
    {
        $listing = Listing::query()
            ->with(['user.skills', 'desiredSkill.parent'])
            ->findOrFail($listingId);

        $this->detail = [
            'listing_id'  => $listing->id,
            'user_id'     => $listing->user->id,
            'user_name'   => $listing->user->name,
            'user_skills' => $listing->user->skills
                ->map(fn($s) => ['name' => $s->name, 'emoji' => $s->emoji])
                ->values()
                ->all(),
            'desired'     => [
                'name'   => $listing->desiredSkill?->name,
                'emoji'  => $listing->desiredSkill?->emoji,
                'parent' => $listing->desiredSkill?->parent?->name,
            ],
            'description' => $listing->description,
            'location'    => $listing->user->location ?: null,

            // Privacy: round coords and show a circle
            'lat'       => $this->approximateLat($listing->user->lat),
            'lng'       => $this->approximateLng($listing->user->lng, $listing->user->lat),
            'radius_m'  => 1500,
        ];

        $this->viewingId   = $listing->id;
        $this->detailsOpen = true;

        // Fire event for Leaflet readonly map boot
        $this->dispatch('detail-map:init');
    }

    public function closeDetails(): void
    {
        $this->reset(['detailsOpen', 'viewingId', 'detail']);
        $this->detail = [
            'listing_id' => null,'user_id'=>null,'user_name'=>null,'user_skills'=>[],
            'desired'=>['name'=>null,'emoji'=>null,'parent'=>null],
            'description'=>null,'location'=>null,'lat'=>null,'lng'=>null,'radius_m'=>1500,
        ];
    }

    /** Round latitude to ~0.01° (~1.1km at equator) */
    private function approximateLat(?float $lat): ?float
    {
        if ($lat === null) return null;
        return round($lat, 2);
    }

    /** Round longitude similarly (2 decimals is coarse) */
    private function approximateLng(?float $lng, ?float $lat): ?float
    {
        if ($lng === null) return null;
        return round($lng, 2);
    }

    /* ------------------------ Render -------------------------- */

    public function render()
    {
        $user = auth()->user();
        $me   = auth()->id();

        $mySkillIds = $user
            ? $user->skills()->pluck('skills.id')->map(fn ($v) => (int) $v)
            : collect();

        $q = trim(mb_strtolower($this->q));
        $ownerAllowedSkillIds = $this->allowedSkillIds();

        $listings = Listing::query()
            ->with(['user.skills','desiredSkill.parent'])

            // Exclude my own listings
            ->when($me, fn ($qr) => $qr->where('user_id', '!=', $me))

            // Filter by LISTING OWNER’S skills (tree-aware)
            ->when($this->categoryId && !empty($ownerAllowedSkillIds), function ($qr) use ($ownerAllowedSkillIds) {
                $qr->whereHas('user.skills', fn ($qs) => $qs->whereIn('skills.id', $ownerAllowedSkillIds));
            })

            // Search
            ->when($q !== '', function ($qr) use ($q) {
                $qr->where(function ($sub) use ($q) {
                    $sub->whereRaw('LOWER(description) LIKE ?', ['%'.$q.'%'])
                        ->orWhereHas('desiredSkill', fn ($ds) => $ds->whereRaw('LOWER(name) LIKE ?', ['%'.$q.'%']))
                        ->orWhereHas('user', fn ($u) => $u->whereRaw('LOWER(name) LIKE ?', ['%'.$q.'%']));
                });
            })

            // Match my skills
            ->when($this->matchMySkills && $mySkillIds->isNotEmpty(), fn ($qr) => $qr->whereIn('desired_skill_id', $mySkillIds))

            ->latest()
            ->paginate($this->perPage);

        return view('livewire.listings.index', compact('listings'));
    }
}
