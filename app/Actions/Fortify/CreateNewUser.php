<?php

namespace App\Actions\Fortify;

use App\Models\User;
use App\Models\Skill;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    public function create(array $input): User
    {
        Validator::make($input, [
            'name' => ['required','string','max:255'],
            'email' => ['required','string','email','max:255','unique:users'],
            'password' => $this->passwordRules(),
            // extra fields from the wizard
            'location' => ['nullable','string','max:255'],
            'lat' => ['nullable','numeric','between:-90,90'],
            'lng' => ['nullable','numeric','between:-180,180'],
            'skills' => ['array'],
            'skills.*' => ['string','max:255'],
        ])->validate();

        $user = User::create([
            'name'     => $input['name'],
            'email'    => $input['email'],
            'password' => Hash::make($input['password']),
            'location' => $input['location'] ?? null,
            'lat'      => $input['lat'] ?? null,
            'lng'      => $input['lng'] ?? null,
        ]);

        // Attach skills by name (create if missing)
        $names = collect($input['skills'] ?? [])
            ->map(fn ($n) => trim($n))
            ->filter()
            ->unique();

        if ($names->isNotEmpty()) {
            $ids = Skill::whereIn('name', $names)->pluck('id', 'name');

            // create any missing skills on the fly (optional)
            $newIds = $names->diff($ids->keys())
                ->map(function ($name) {
                    return Skill::create(['name' => $name])->id;
                });

            $user->skills()->sync($ids->values()->merge($newIds)->all());
        }

        return $user;
    }
}
