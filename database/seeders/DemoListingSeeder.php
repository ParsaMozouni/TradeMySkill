<?php


namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Skill;
use App\Models\Listing;

class DemoListingSeeder extends Seeder
{
    public function run(): void
    {
        // Ensure some skills exist (assumes SkillSeeder already ran)
        $laravel = Skill::firstOrCreate(['name' => 'Laravel'], ['emoji' => '🅻', 'is_it_popular' => true, 'skill_id' => Skill::where('name','Development')->value('id')]);
        $react   = Skill::firstOrCreate(['name' => 'React'],   ['emoji' => '⚛️', 'is_it_popular' => true, 'skill_id' => Skill::where('name','Development')->value('id')]);
        $guitar  = Skill::firstOrCreate(['name' => 'Guitar'],  ['emoji' => '🎸', 'is_it_popular' => true, 'skill_id' => Skill::where('name','Music')->value('id')]);

        // Create a demo user
        $user = User::firstOrCreate(
            ['email' => 'demo@example.com'],
            [
                'name' => 'Demo Mentor',
                'password' => Hash::make('password'),
                'location' => 'Downtown Regina',
                'lat' => 50.4452,
                'lng' => -104.6189,
            ]
        );

        // Give the user some skills
        $user->skills()->syncWithoutDetaching([$laravel->id, $react->id]);

        // Create a listing where the user wants to learn Guitar
        Listing::firstOrCreate(
            ['user_id' => $user->id, 'desired_skill_id' => $guitar->id],
            ['description' => 'Looking to learn beginner to intermediate guitar chords and strumming patterns.']
        );

        // Add another user + listing (optional)
        $user2 = User::firstOrCreate(
            ['email' => 'student@example.com'],
            [
                'name' => 'New Learner',
                'password' => Hash::make('password'),
                'location' => 'Cathedral Area',
                'lat' => 50.4481,
                'lng' => -104.6269,
            ]
        );
        $user2->skills()->syncWithoutDetaching([$guitar->id]);
        Listing::firstOrCreate(
            ['user_id' => $user2->id, 'desired_skill_id' => $laravel->id],
            ['description' => 'Want to learn Laravel queues, Eloquent tips, and testing with Pest.']
        );
    }
}
