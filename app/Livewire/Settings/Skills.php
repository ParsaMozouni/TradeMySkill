<?php

namespace App\Livewire\Settings;

use Livewire\Component;
use App\Models\Skill;

class Skills extends Component
{
    public string $query = '';
    public ?int $categoryId = null;        // parent category filter (top-level skill)
    public array $selectedSkills = [];     // array of skill IDs for the current user
    public array $popularCategories = [];  // top-level “popular” categories

    public function mount(): void
    {
        $user = auth()->user();

        // Preload user's current skills as IDs
        $this->selectedSkills = $user->skills()->pluck('skills.id')->map(fn ($v) => (int) $v)->all();

        // Popular top-level categories
        $this->popularCategories = Skill::query()
            ->whereNull('skill_id')
            ->where('is_it_popular', true)
            ->orderBy('name')
            ->get(['id','name'])
            ->map(fn ($s) => ['id' => (int) $s->id, 'name' => $s->name])
            ->all();
    }

    /** Toggle a sub-skill by its ID */
    public function toggleSkill(int $skillId): void
    {
        $i = array_search($skillId, $this->selectedSkills, true);
        if ($i !== false) {
            unset($this->selectedSkills[$i]);
            $this->selectedSkills = array_values($this->selectedSkills);
        } else {
            $this->selectedSkills[] = $skillId;
        }
    }

    /** Toggle top-level category filter */
    public function toggleCategory(int $categoryId): void
    {
        $this->categoryId = ($this->categoryId === $categoryId) ? null : $categoryId;
    }

    /** Persist changes to pivot */
    public function save(): void
    {
        // validate IDs exist
        $ids = collect($this->selectedSkills)->map(fn ($v) => (int) $v)->all();

        $this->validate([
            'selectedSkills'   => ['array'],
            'selectedSkills.*' => ['integer', 'exists:skills,id'],
        ]);

        auth()->user()->skills()->sync($ids);

        session()->flash('status', __('Skills updated.'));
    }

    /** Reactive, DB-backed filtered list of sub-skills */
    public function getFilteredSkillsProperty()
    {
        $q = trim(mb_strtolower($this->query));

        return Skill::query()
            ->whereNotNull('skill_id') // only children
            ->when($this->categoryId, fn ($qr) => $qr->where('skill_id', $this->categoryId))
            ->when($q !== '', function ($qr) use ($q) {
                $qr->where(function ($sub) use ($q) {
                    $sub->whereRaw('LOWER(name) LIKE ?', ['%'.$q.'%'])
                        ->orWhereHas('parent', fn ($p) => $p->whereRaw('LOWER(name) LIKE ?', ['%'.$q.'%']));
                });
            })
            ->orderBy('name')
            ->get(['id','name','emoji','skill_id']);
    }

    public function render()
    {
        return view('livewire.settings.skills');
    }
}
