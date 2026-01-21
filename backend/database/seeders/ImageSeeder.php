<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class ImageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('images')->insert([
            [
                'user_id' => '1', 
                'image_url' => 'uploads/1760755153020-i3clvgl.jpg',
                'ronda' => 1,
                'votes' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'user_id' => '2',
                'image_url' => 'uploads/1760755214796-syjv0o0.jpg',
                'ronda' => 1,
                'votes' => 2,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'user_id' => '3',
                'image_url' => 'uploads/1760755852553-ag675ic.jpg',
                'ronda' => 1,
                'votes' => 0,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],           
        ]);
    }
}
