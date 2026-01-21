<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class VoteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('votes')->insert([
            [
                'image_id' => '1',
                'user_id' => '2',
                'ronda' => 1,
                'created_at' => Carbon::createFromTimestampMs(1760755248688),
                'updated_at' => Carbon::createFromTimestampMs(1760755248688),
            ],
            [
                'image_id' => '3',
                'user_id' => '1',
                'ronda' => 1,
                'created_at' => Carbon::createFromTimestampMs(1760755271404),
                'updated_at' => Carbon::createFromTimestampMs(1760755271404),
            ],
            [
                'image_id' => '2',
                'user_id' => '3',
                'ronda' => 1,
                'created_at' => Carbon::createFromTimestampMs(1760755311795),
                'updated_at' => Carbon::createFromTimestampMs(1760755311795),
            ],            
        ]);
    }
}
