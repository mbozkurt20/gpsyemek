<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\FooterMenuSection;
class FooterMenuSections extends Seeder
{
    public array $footerMenuSections = [
        [
            'name'       => "Hakkımızda",
        ],
        [
            'name'       => "Hizmetler",
        ]
    ];

    /**
     * Run the database seeds.
     *
     * @return void
     */



    public function run(){
        foreach ($this->footerMenuSections as $footerMenuSection) {
            FooterMenuSection::create([
                'name'       => $footerMenuSection['name'],
            ]);
        }
    }
}
