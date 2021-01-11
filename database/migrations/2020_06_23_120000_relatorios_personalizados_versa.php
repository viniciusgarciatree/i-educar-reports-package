<?php

use App\Menu;
use Illuminate\Database\Migrations\Migration;

class RelatoriosPersonalizadosVersa extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Menu::query()->updateOrCreate([
            'old' => 999731
        ], [
            'parent_id' => Menu::query()->where('old', 999914)->firstOrFail()->getKey(),
            'title' => 'Relatório Geral de Servidores por Vinculo, Função e Cargo',
            'description' => null,
            'link' => '/module/Reports/ServantsGeneral',
            'order' => 0,
            'old' => 999731,
            'process' => 999731,
            'active' => true,
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Menu::query()->where('old', 999731)->delete();
    }
}
