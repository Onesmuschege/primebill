<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Plan;

class PlanSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            [
                'name'          => 'Basic 2Mbps',
                'type'          => 'pppoe',
                'speed_up'      => 2048,
                'speed_down'    => 2048,
                'price'         => 1500,
                'validity_days' => 30,
                'is_active'     => true,
            ],
            [
                'name'          => 'Standard 5Mbps',
                'type'          => 'pppoe',
                'speed_up'      => 5120,
                'speed_down'    => 5120,
                'price'         => 2500,
                'validity_days' => 30,
                'is_active'     => true,
            ],
            [
                'name'          => 'Premium 10Mbps',
                'type'          => 'pppoe',
                'speed_up'      => 10240,
                'speed_down'    => 10240,
                'price'         => 4500,
                'validity_days' => 30,
                'is_active'     => true,
            ],
            [
                'name'          => 'Hotspot 1Hr',
                'type'          => 'hotspot',
                'speed_up'      => 2048,
                'speed_down'    => 2048,
                'price'         => 50,
                'validity_days' => 1,
                'is_active'     => true,
            ],
        ];

        foreach ($plans as $plan) {
            Plan::create($plan);
        }
    }
}
