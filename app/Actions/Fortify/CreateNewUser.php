<?php

namespace App\Actions\Fortify;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    public function create(array $input): User
    {
        Validator::make($input, [
            'name'                  => ['required','string','max:255'],
            'email'                 => ['required','string','email','max:255','unique:users'],
            'password'              => $this->passwordRules(),

            'location'              => ['nullable','string','max:255'],
            'lat'                   => ['required','numeric','between:-90,90'],    // make required if you want
            'lng'                   => ['required','numeric','between:-180,180'],  // make required if you want

            'skills'                => ['array'],
            'skills.*'              => ['integer','exists:skills,id'],
        ])->validate();

        $user = User::create([
            'name'     => $input['name'],
            'email'    => $input['email'],
            'password' => Hash::make($input['password']),
            'location' => $input['location'] ?? null,
            'lat'      => (float) $input['lat'],
            'lng'      => (float) $input['lng'],
        ]);

        $ids = collect($input['skills'] ?? [])
            ->map(fn ($v) => (int) $v)
            ->unique()
            ->values()
            ->all();

        if (!empty($ids)) {
            $user->skills()->sync($ids);
        }

        return $user;
    }
}
