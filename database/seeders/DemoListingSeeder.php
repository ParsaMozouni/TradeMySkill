<?php


namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Skill;
use App\Models\Listing;

class DemoListingSeeder extends Seeder
{
    public function run(): void
    {
        /* -----------------------------
         * 1) Ensure parents (categories)
         * ----------------------------- */
        $parents = collect([
            ['name' => 'Development', 'emoji' => '💻'],
            ['name' => 'Music',       'emoji' => '🎵'],
            ['name' => 'Languages',   'emoji' => '🗣️'],
            ['name' => 'Arts',        'emoji' => '🎨'],
            ['name' => 'Fitness',     'emoji' => '🏃'],
            ['name' => 'Cooking',     'emoji' => '🍳'],
            ['name' => 'Business',    'emoji' => '📈'],
        ])->mapWithKeys(function ($p) {
            $parent = Skill::firstOrCreate(
                ['name' => $p['name'], 'skill_id' => null],
                ['emoji' => $p['emoji'], 'is_it_popular' => true]
            );
            return [$p['name'] => $parent->id];
        });

        /* -----------------------------
         * 2) Ensure child skills exist
         * ----------------------------- */
        $childSkillsByParent = [
            'Development' => [
                ['Laravel','🅻'], ['React','⚛️'], ['Vue','🟢'], ['PHP','🐘'], ['JavaScript','🟨'],
                ['Python','🐍'], ['Docker','🐳'], ['SQL','🗃️'], ['DevOps','⚙️'], ['Testing (Pest)','🧪'],
            ],
            'Music' => [
                ['Guitar','🎸'], ['Piano','🎹'], ['Singing','🎤'], ['Drums','🥁'], ['Violin','🎻'],
            ],
            'Languages' => [
                ['English Tutoring','🇬🇧'], ['French','🇫🇷'], ['Spanish','🇪🇸'], ['Mandarin','🇨🇳'], ['German','🇩🇪'],
            ],
            'Arts' => [
                ['Photography','📸'], ['Graphic Design','🖍️'], ['Painting','🖌️'], ['Video Editing','🎬'],
            ],
            'Fitness' => [
                ['Yoga','🧘'], ['Running','👟'], ['Strength Training','🏋️'], ['Cycling','🚴'],
            ],
            'Cooking' => [
                ['Baking','🥐'], ['BBQ','🔥'], ['Meal Prep','🥗'], ['Pastry','🍰'],
            ],
            'Business' => [
                ['Public Speaking','🎙️'], ['Excel/Sheets','📊'], ['Bookkeeping','🧾'], ['Marketing','📣'],
            ],
        ];

        $allChildSkills = collect();
        foreach ($childSkillsByParent as $parentName => $skills) {
            $parentId = $parents[$parentName] ?? null;
            foreach ($skills as [$name, $emoji]) {
                $s = Skill::firstOrCreate(
                    ['name' => $name],
                    ['emoji' => $emoji, 'is_it_popular' => true, 'skill_id' => $parentId]
                );
                $allChildSkills->push($s);
            }
        }

        // quick helpers to fetch by name
        $skillByName = fn (string $name) => Skill::where('name', $name)->first();

        /* ------------------------------------------
         * 3) Create your original two demo accounts
         * ------------------------------------------ */
        $laravel = $skillByName('Laravel');
        $react   = $skillByName('React');
        $guitar  = $skillByName('Guitar');

        $demo = User::firstOrCreate(
            ['email' => 'demo@example.com'],
            [
                'name'     => 'Demo Mentor',
                'password' => Hash::make('password'),
                'location' => 'Downtown Regina',
                'lat'      => 50.4452,
                'lng'      => -104.6189,
            ]
        );
        $demo->skills()->syncWithoutDetaching([$laravel->id, $react->id]);

        Listing::firstOrCreate(
            ['user_id' => $demo->id, 'desired_skill_id' => $guitar->id],
            ['description' => 'Looking to learn beginner to intermediate guitar chords and strumming patterns.']
        );

        $student = User::firstOrCreate(
            ['email' => 'student@example.com'],
            [
                'name'     => 'New Learner',
                'password' => Hash::make('password'),
                'location' => 'Cathedral Area',
                'lat'      => 50.4481,
                'lng'      => -104.6269,
            ]
        );
        $student->skills()->syncWithoutDetaching([$guitar->id]);

        Listing::firstOrCreate(
            ['user_id' => $student->id, 'desired_skill_id' => $laravel->id],
            ['description' => 'Want to learn Laravel queues, Eloquent tips, and testing with Pest.']
        );

        /* -------------------------------------------------
         * 4) Regina neighbourhoods/POIs with anchor coords
         * ------------------------------------------------- */
        $areas = [
            ['name' => 'Downtown',           'lat' => 50.4500, 'lng' => -104.6170],
            ['name' => 'Cathedral Area',     'lat' => 50.4459, 'lng' => -104.6345],
            ['name' => 'Wascana Centre',     'lat' => 50.4319, 'lng' => -104.6173],
            ['name' => 'Harbour Landing',    'lat' => 50.4031, 'lng' => -104.6485],
            ['name' => 'Whitmore Park',      'lat' => 50.4169, 'lng' => -104.6134],
            ['name' => 'Hillsdale',          'lat' => 50.4206, 'lng' => -104.6168],
            ['name' => 'Lakeview',           'lat' => 50.4326, 'lng' => -104.6402],
            ['name' => 'Gardiner Park',      'lat' => 50.4619, 'lng' => -104.5535],
            ['name' => 'Glencairn',          'lat' => 50.4583, 'lng' => -104.5548],
            ['name' => 'Normanview',         'lat' => 50.4737, 'lng' => -104.6547],
            ['name' => 'Rochdale',           'lat' => 50.4908, 'lng' => -104.6505],
            ['name' => 'Uplands',            'lat' => 50.4857, 'lng' => -104.5898],
            ['name' => 'Al Ritchie',         'lat' => 50.4450, 'lng' => -104.6010],
            ['name' => 'Broders Annex',      'lat' => 50.4524, 'lng' => -104.5936],
            ['name' => 'University of Regina','lat'=> 50.4150, 'lng' => -104.5883],
            ['name' => 'Eastview',           'lat' => 50.4540, 'lng' => -104.5842],
            ['name' => 'Walsh Acres',        'lat' => 50.4868, 'lng' => -104.6406],
            ['name' => 'Sherwood Estates',   'lat' => 50.4943, 'lng' => -104.6507],
        ];

        /* -------------------------------------------
         * 5) Generate a big set of users + listings
         * ------------------------------------------- */

        // Faker without factory to keep this file self-contained
        $faker = \Faker\Factory::create();

        // How many demo users to add
        $totalUsers = 80;

        // To avoid e-mail collisions when reseeding
        $emailDomain = 'example.test';

        for ($i = 1; $i <= $totalUsers; $i++) {
            $area     = Arr::random($areas);
            [$lat, $lng] = $this->jitter($area['lat'], $area['lng'], 800); // jitter within ~800m

            $name  = $faker->name();
            $email = sprintf('demo%03d@%s', $i, $emailDomain);

            $user = User::firstOrCreate(
                ['email' => $email],
                [
                    'name'     => $name,
                    'password' => Hash::make('password'),
                    'location' => $area['name'],
                    'lat'      => $lat,
                    'lng'      => $lng,
                ]
            );

            // Assign 2–4 random skills as "has skills"
            $owned = $allChildSkills->random(rand(2, 4))->pluck('id')->all();
            $user->skills()->syncWithoutDetaching($owned);

            // Create 1–2 listings wanting skills the user does NOT have
            $wantsCount = rand(1, 2);
            $notOwned   = $allChildSkills->whereNotIn('id', $owned)->values();

            if ($notOwned->isEmpty()) {
                // Edge case: give them at least something to want
                $notOwned = $allChildSkills;
            }

            $chosen = $notOwned->random($wantsCount);

            foreach ($chosen as $desired) {
                Listing::firstOrCreate(
                    ['user_id' => $user->id, 'desired_skill_id' => $desired->id],
                    [
                        'description' => $this->listingBlurb(
                            $name,
                            $desired->name,
                            $area['name'],
                            $faker
                        ),
                    ]
                );
            }
        }

        $this->command?->info('ReginaDemoSeeder: seeded users, skills and listings with Regina locations.');
    }

    /**
     * Return a short, friendly description for a listing.
     */
    private function listingBlurb(string $name, string $skill, string $area, \Faker\Generator $faker): string
    {
        $templates = [
            "Hi, I'm {$name}. Based in {$area}, hoping to learn {$skill}. Open to weekend sessions.",
            "Looking for a friendly mentor in {$skill} around {$area}. Flexible schedule.",
            "Seeking {$skill} lessons near {$area}. Casual pace, practical focus preferred.",
            "Would love to learn {$skill} in {$area}. Coffee + learning vibe!",
            "New to {$skill}; around {$area}. Short sessions okay.",
        ];
        return Arr::random($templates) . ' ' . $faker->sentence(rand(6, 12));
    }

    /**
     * Jitter lat/lng by up to $meters (roughly), to distribute users inside an area.
     */
    private function jitter(float $lat, float $lng, int $meters = 600): array
    {
        // 1 deg latitude ~= 111,320 m
        $deltaLat = ($meters / 111320) * $this->randSigned();
        // 1 deg longitude depends on latitude
        $deltaLng = ($meters / (111320 * cos(deg2rad($lat)))) * $this->randSigned();

        return [round($lat + $deltaLat, 6), round($lng + $deltaLng, 6)];
    }

    private function randSigned(): float
    {
        // random value between -1 and 1, scaled down to ~0.5 average
        return (mt_rand() / mt_getrandmax() - 0.5) * 1.0;
    }
}
