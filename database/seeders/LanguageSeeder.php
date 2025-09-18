<?php

namespace Database\Seeders;

use App\Enums\Status;
use App\Models\Language;
use Illuminate\Database\Seeder;

class LanguageSeeder extends Seeder
{
    public array $languageOptions = [
        [
            'name'      => 'English',
            'code'      => 'en',
            'flag_icon' => '🇬🇧',
            'status'    => Status::ACTIVE,
        ],
        [
            'name'      => 'German',
            'code'      => 'de',
            'flag_icon' => '🇩🇪',
            'status'    => Status::ACTIVE,
        ],
        [
            'name'      => 'Bangla',
            'code'      => 'bn',
            'flag_icon' => '🇧🇩',
            'status'    => Status::ACTIVE,
        ],
        [
            'name'      => 'Türkçe',
            'code'      => 'tr',
            'flag_icon' => '🇹🇷',
            'status'    => Status::ACTIVE,
        ],
    ];


    /**
     * Run the database seeds.
     *
     * @return void
     */

    public function run(){
        foreach ($this->languageOptions as $languageOption) {
            Language::create([
                'name'      => $languageOption['name'],
                'code'      => $languageOption['code'],
                'flag_icon' => $languageOption['flag_icon'],
                'status'    => $languageOption['status'],
            ]);
        }
    }
}
