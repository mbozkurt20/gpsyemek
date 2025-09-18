<?php

namespace Database\Seeders;

use App\Models\Page;
use Illuminate\Database\Seeder;
use App\Models\User;

class PageTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Page::insert([
            [
                'title'                  => 'Kullanım Şartları',
                'slug'                   => 'kullanim-sartlari',
                'description'            => '',
                'footer_menu_section_id' => 1,
                'template_id'            => 1,
                'creator_type'           => User::class,
                'editor_type'            => User::class,
                'creator_id'             => 1,
                'editor_id'              => 1,
            ],
            [
                'title'                  => 'Hakkımızda',
                'slug'                   => 'hakkimizda',
                'description'            => "",
                'footer_menu_section_id' => 2,
                'template_id'            => 2,
                'creator_type'           => User::class,
                'editor_type'            => User::class,
                'creator_id'             => 1,
                'editor_id'              => 1,
            ],
            [
                'title'                  => 'Gizlilik Politikası',
                'slug'                   => 'gizlilik-politikasi',
                'description'            => '',
                'footer_menu_section_id' => 2,
                'template_id'            => 2,
                'creator_type'           => User::class,
                'editor_type'            => User::class,
                'creator_id'             => 1,
                'editor_id'              => 1,
            ],
            [
                'title'                  => 'İletişim',
                'slug'                   => 'iletisim',
                'description'            => '',
                'footer_menu_section_id' => 1,
                'template_id'            => 3,
                'creator_type'           => User::class,
                'editor_type'            => User::class,
                'creator_id'             => 1,
                'editor_id'              => 1,
            ],
        ]);
    }
}
