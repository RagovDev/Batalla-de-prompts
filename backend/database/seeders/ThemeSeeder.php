<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class ThemeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('themes')->insert([
            [
                'ronda' => 1,
                'title' => 'Mascota en el Paraíso',
                'image_url' => 'themes/r1-mascota.jpg',
                'votes' => 2,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'ronda' => 1,
                'title' => 'Paisaje de Fantasía',
                'image_url' => 'themes/r1-paisaje.jpg',
                'votes' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'ronda' => 1,
                'title' => 'Gigantes Cotidianos',
                'image_url' => 'themes/r1-gigante.jpg',
                'votes' => 0,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'ronda' => 2,
                'title' => 'Futuro al Atardecer',
                'image_url' => 'themes/r2-futuro.jpg',
                'votes' => 0,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'ronda' => 2,
                'title' => 'Misterio Lluvioso',
                'image_url' => 'themes/r2-misterio.jpg',
                'votes' => 0,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'ronda' => 2,
                'title' => 'Fiesta y Tradición',
                'image_url' => 'themes/r2-fiesta.jpg',
                'votes' => 0,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'ronda' => 3,
                'title' => 'Choque de Eras',
                'image_url' => 'themes/r3-choque.jpg',
                'votes' => 0,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'ronda' => 3,
                'title' => 'Sueños de Arte',
                'image_url' => 'themes/r3-sueno.jpg',
                'votes' => 0,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'ronda' => 3,
                'title' => 'Cena Cinematográfica',
                'image_url' => 'themes/r3-cena.jpg',
                'votes' => 0,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'ronda' => 4,
                'title' => 'Retrato',
                'image_url' => 'themes/r4-retrato.jpg',
                'votes' => 0,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'ronda' => 4,
                'title' => 'Humanidad 2100',
                'image_url' => 'themes/r4-humanidad.jpg',
                'votes' => 0,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'ronda' => 4,
                'title' => 'Guerra de Máquinas',
                'image_url' => 'themes/r4-guerra.jpg',
                'votes' => 0,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ]);
    }
}
