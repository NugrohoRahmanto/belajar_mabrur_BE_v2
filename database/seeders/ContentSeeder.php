<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use App\Models\Content;

class ContentSeeder extends Seeder
{
    public function run(): void
    {
        $files = [
            'database/data/ihram.json',
            'database/data/sai.json',
            'database/data/tahallul.json',
            'database/data/thawaf.json',
        ];

        foreach ($files as $file) {
            $json = File::get(base_path($file));
            $items = json_decode($json, true);

            foreach ($items as $item) {
                Content::create([
                    'name'         => $item['name'],
                    'category'     => $item['category'],
                    'arabic'       => $item['arabic'] ?? null,
                    'latin'        => $item['latin'] ?? null,
                    'translate_id' => $item['translate_id'] ?? null,
                    'description'  => $item['description'] ?? null,
                ]);
            }
        }
    }
}

