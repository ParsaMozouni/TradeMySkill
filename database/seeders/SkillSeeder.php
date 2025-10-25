<?php

namespace Database\Seeders;

use App\Models\Skill;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SkillSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $design = Skill::firstOrCreate(['name' => 'Design'],      ['is_it_popular' => true, 'emoji' => '🎨']);
        $dev    = Skill::firstOrCreate(['name' => 'Development'], ['is_it_popular' => true, 'emoji' => '💻']);
        $tutor  = Skill::firstOrCreate(['name' => 'Tutoring'],    ['is_it_popular' => true, 'emoji' => '🗣️']);
        $music  = Skill::firstOrCreate(['name' => 'Music'],       ['is_it_popular' => true, 'emoji' => '🎵']);
        $cook   = Skill::firstOrCreate(['name' => 'Cooking'],     ['is_it_popular' => true, 'emoji' => '🍳']);
        $photo  = Skill::firstOrCreate(['name' => 'Photography'], ['is_it_popular' => true, 'emoji' => '📷']);

        Skill::firstOrCreate(['name' => 'Logo Design'],      ['skill_id' => $design->id, 'is_it_popular' => true, 'emoji' => '🎨']);
        Skill::firstOrCreate(['name' => 'UI/UX'],            ['skill_id' => $design->id, 'is_it_popular' => true, 'emoji' => '🧩']);
        Skill::firstOrCreate(['name' => 'Web Dev'],          ['skill_id' => $dev->id,    'is_it_popular' => true, 'emoji' => '💻']);
        Skill::firstOrCreate(['name' => 'Laravel'],          ['skill_id' => $dev->id,    'is_it_popular' => true, 'emoji' => '🅻']);
        Skill::firstOrCreate(['name' => 'React'],            ['skill_id' => $dev->id,    'is_it_popular' => true, 'emoji' => '⚛️']);
        Skill::firstOrCreate(['name' => 'English Tutoring'], ['skill_id' => $tutor->id,  'is_it_popular' => true, 'emoji' => '🗣️']);
        Skill::firstOrCreate(['name' => 'Math'],             ['skill_id' => $tutor->id,  'is_it_popular' => true, 'emoji' => '➗']);
        Skill::firstOrCreate(['name' => 'Guitar'],           ['skill_id' => $music->id,  'is_it_popular' => true, 'emoji' => '🎸']);
        Skill::firstOrCreate(['name' => 'Piano'],            ['skill_id' => $music->id,  'is_it_popular' => true, 'emoji' => '🎹']);
        Skill::firstOrCreate(['name' => 'Photography'],      ['skill_id' => $photo->id,  'is_it_popular' => true, 'emoji' => '📷']);
    }
}
