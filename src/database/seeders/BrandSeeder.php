<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BrandSeeder extends Seeder
{
    protected $tableName = 'brands';

    protected $values = [
        ['name' => 'VITACCI', 'slug' => 'vitacci', 'seo' => 'VITACCI'],
        ['name' => 'Barcelo Biagi', 'slug' => 'barcelo', 'seo' => 'Barcelo Biagi'],
        ['name' => 'Cover', 'slug' => 'cover', 'seo' => 'Cover'],
        ['name' => 'Franco Bellucci', 'slug' => 'bellucci', 'seo' => 'Franco Bellucci'],
        ['name' => 'Franco Osvaldo', 'slug' => 'osvaldo', 'seo' => 'Franco Osvaldo'],
        ['name' => 'Grand Gudini', 'slug' => 'gudini', 'seo' => 'Grand Gudini'],
        ['name' => 'Markos', 'slug' => 'markos', 'seo' => 'Markos'],
        ['name' => 'Renaissance', 'slug' => 'renaissance', 'seo' => 'Renaissance'],
        ['name' => 'Marsalitta', 'slug' => 'marsalitta', 'seo' => 'Marsalitta'],
        ['name' => 'Cavaletto', 'slug' => 'cavaletto', 'seo' => 'Cavaletto'],
        ['name' => 'Shuanguicheng', 'slug' => 'shuanguicheng', 'seo' => 'Shuanguicheng'],
        ['name' => 'Fermani', 'slug' => 'fermani', 'seo' => 'Fermani'],
        ['name' => 'Paola Conte', 'slug' => 'paola', 'seo' => 'Paola Conte'],
        ['name' => 'Ribellen', 'slug' => 'ribellen', 'seo' => 'Ribellen'],
        ['name' => 'Modelle', 'slug' => 'modelle', 'seo' => 'Modelle'],
        ['name' => 'La Pinta', 'slug' => 'lapinta', 'seo' => 'La Pinta'],
        ['name' => 'Gloria Shoes', 'slug' => 'gloria', 'seo' => 'Gloria Shoes'],
        ['name' => 'Pera Donna', 'slug' => 'pera', 'seo' => 'Pera Donna'],
        ['name' => 'Sherlock Soon', 'slug' => 'sherlock', 'seo' => 'Sherlock Soon'],
        ['name' => 'Mario Muzi', 'slug' => 'mario', 'seo' => 'Mario Muzi'],
        ['name' => 'Alpino', 'slug' => 'alpino', 'seo' => 'Alpino'],
        ['name' => 'Amy Michelle', 'slug' => 'amy', 'seo' => 'Amy Michelle'],
        ['name' => 'D/S', 'slug' => 'ds', 'seo' => 'D/S'],
        ['name' => 'Grand Donna', 'slug' => 'donna', 'seo' => 'Grand Donna'],
        ['name' => 'Magnolya', 'slug' => 'magnolya', 'seo' => 'Magnolya'],
        ['name' => 'Mainila', 'slug' => 'mainila', 'seo' => 'Mainila'],
        ['name' => 'Lifexpert', 'slug' => 'lifexpert', 'seo' => 'Lifexpert'],
        ['name' => 'Wit Mooni', 'slug' => 'wit', 'seo' => 'Wit Mooni'],
        ['name' => 'Mossani', 'slug' => 'mossani', 'seo' => 'Mossani'],
        ['name' => 'Vidorcci', 'slug' => 'vidorcci', 'seo' => 'Vidorcci'],
        ['name' => 'Evromoda', 'slug' => 'evromoda', 'seo' => 'Evromoda'],
        ['name' => 'Estomod', 'slug' => 'estomod', 'seo' => 'Estomod'],
        ['name' => 'Ripka', 'slug' => 'ripka', 'seo' => 'Ripka'],
        ['name' => 'Mumin dulun', 'slug' => 'mumin', 'seo' => 'Mumin dulun'],
        ['name' => 'Sasha Fabiani', 'slug' => 'sasha', 'seo' => 'Sasha Fabiani'],
        ['name' => 'Estro', 'slug' => 'estro', 'seo' => 'Estro'],
        ['name' => 'AIDINI', 'slug' => 'aidini', 'seo' => 'AIDINI'],
        ['name' => 'Derissi', 'slug' => 'derissi', 'seo' => 'Derissi'],
        ['name' => 'Maria Moro', 'slug' => 'maria', 'seo' => 'Maria Moro'],
        ['name' => 'Berkonty', 'slug' => 'berkonty', 'seo' => 'Berkonty'],
        ['name' => 'Deenoor', 'slug' => 'deenoor', 'seo' => 'Deenoor'],
        ['name' => 'Berisstini', 'slug' => 'berisstini', 'seo' => 'Berisstini'],
        ['name' => 'Tucino', 'slug' => 'tucino', 'seo' => 'Tucino'],
        ['name' => 'AQUAMARIN', 'slug' => 'aquamarin', 'seo' => 'AQUAMARIN'],
        ['name' => 'Chewhite', 'slug' => 'chewhite', 'seo' => 'Chewhite'],
        ['name' => 'VICTORIA SCARLETT', 'slug' => 'victoria', 'seo' => 'VICTORIA SCARLETT'],
        ['name' => 'VICES', 'slug' => 'vices', 'seo' => 'VICES'],
        ['name' => 'HEALTHSHOES ECONOM', 'slug' => 'healthshoes', 'seo' => 'HEALTHSHOES ECONOM'],
        ['name' => 'KADAR', 'slug' => 'kadar', 'seo' => 'KADAR'],
        ['name' => 'TOP LAND', 'slug' => 'top', 'seo' => 'TOP LAND'],
        ['name' => 'ALBERTO VIOLLI', 'slug' => 'alberto', 'seo' => 'ALBERTO VIOLLI'],
        ['name' => 'MAST-BUT', 'slug' => 'mastbut', 'seo' => 'MAST-BUT'],
        ['name' => 'MARKO-BUT', 'slug' => 'markobut', 'seo' => 'MARKO-BUT'],
        ['name' => 'LILY ROSE', 'slug' => 'lily', 'seo' => 'LILY ROSE'],
        ['name' => 'BETLER', 'slug' => 'betler', 'seo' => 'BETLER'],
        ['name' => 'BAROCCO style', 'slug' => 'barocco', 'seo' => 'BAROCCO style'],
    ];

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table($this->tableName)->truncate();

        foreach ($this->values as $value) {
            DB::table($this->tableName)->insert($value);
        }
    }
}
