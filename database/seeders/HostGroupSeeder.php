<?php

namespace Database\Seeders;

use App\Models\HostGroup;
use Illuminate\Database\Seeder;

class HostGroupSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            [
                'group_id'    => 'default',
                'host'        => 'localhost',
                'name'        => 'Local Default',
                'is_default'  => true,
                'is_active'   => true,
                'description' => 'Base template group used for generating tenants.',
                'metadata'    => [
                    'aliases' => ['127.0.0.1', 'dev.local'],
                ],
            ],
        ];

        foreach ($defaults as $group) {
            HostGroup::updateOrCreate(
                ['group_id' => $group['group_id']],
                $group
            );
        }
    }
}
