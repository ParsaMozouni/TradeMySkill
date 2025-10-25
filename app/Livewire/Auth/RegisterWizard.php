<?php

namespace App\Livewire\Auth;

use Livewire\Component;
use App\Models\Skill;

class RegisterWizard extends Component
{
    public int $step = 1;

    // Step 1
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';

    // Step 2 (map + optional label)
    public string $location = '';
    public ?float $lat = null;
    public ?float $lng = null;

    // Step 3 (skills UI state)
    public string $query = '';
    public ?int $categoryId = null;       // parent skill (category) id
    public array $selectedSkills = [];     // array of skill IDs

    // Optional: cache popular categories (top-level)
    public array $popularCategories = [];  // [ ['id'=>..., 'name'=>...], ... ]

    public function mount(): void
    {
        // Prefill name/email if came back from error
        $this->name  = (string) old('name', $this->name);
        $this->email = (string) old('email', $this->email);

        // Preload popular top-level categories
        $this->popularCategories = Skill::query()
            ->whereNull('skill_id')               // top-level
            ->where('is_it_popular', true)
            ->orderBy('name')
            ->get(['id','name'])
            ->map(fn ($s) => ['id' => (int) $s->id, 'name' => $s->name])
            ->all();
    }

    public function next(): void
    {
        if ($this->step === 1) {
            $this->validate([
                'name'                  => ['required','string','max:100'],
                'email'                 => ['required','email','max:255'],
                'password'              => ['required','min:8','confirmed'],
                'password_confirmation' => ['required'],
            ]);
            $this->step = 2;
            $this->dispatch('map:init'); // ensure the map boots after render
            return;
        }

        if ($this->step === 2) {
            $this->validate([
                'lat' => ['required','numeric','between:-90,90'],
                'lng' => ['required','numeric','between:-180,180'],
                'location' => ['nullable','string','max:100'],
            ]);
            $this->step = 3;
            return;
        }
    }

    public function back(): void
    {
        if ($this->step > 1) {
            $this->step--;
            if ($this->step === 2) {
                $this->dispatch('map:init'); // re-init map when returning to step 2
            }
        }
    }

    /** Toggle a skill by its ID */
    public function toggleSkill(int $skillId): void
    {
        $idx = array_search($skillId, $this->selectedSkills, true);
        if ($idx !== false) {
            unset($this->selectedSkills[$idx]);
            $this->selectedSkills = array_values($this->selectedSkills);
        } else {
            $this->selectedSkills[] = $skillId;
        }
    }

    /** Toggle category (parent skill) filter by ID */
    public function toggleCategory(int $categoryId): void
    {
        $this->categoryId = ($this->categoryId === $categoryId) ? null : $categoryId;
    }

    /** Computed: filtered sub-skills from DB (children = have a parent) */
    public function getFilteredSkillsProperty()
    {
        $q = trim(mb_strtolower($this->query));

        return Skill::query()
            ->whereNotNull('skill_id') // sub-skills only
            ->when($this->categoryId, fn ($qr) => $qr->where('skill_id', $this->categoryId))
            ->when($q !== '', function ($qr) use ($q) {
                $qr->where(function ($sub) use ($q) {
                    $sub->whereRaw('LOWER(name) LIKE ?', ['%'.$q.'%'])
                        ->orWhereHas('parent', function ($p) use ($q) {
                            $p->whereRaw('LOWER(name) LIKE ?', ['%'.$q.'%']);
                        });
                });
            })
            ->orderBy('name')
            ->get(['id','name','icon_path','skill_id','emoji']);
    }

    /** Tiny fallback icon if icon_path is null */


    public function render()
    {
        // Pass filtered skills collection to the view (use $this->filteredSkills in Blade too)
        $filtered = $this->filteredSkills;

        return view('livewire.auth.register-wizard', [
            'filteredSkills' => $filtered,
        ]);
    }
}
