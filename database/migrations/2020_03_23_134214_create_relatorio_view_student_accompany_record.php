<?php

use App\Menu;
use Illuminate\Database\Migrations\Migration;

class CreateRelatorioViewStudentAccompanyRecord extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Menu::query()->create([
            'parent_id' => Menu::query()->where('old', 999861)->firstOrFail()->getKey(),
            'title' => 'Ficha de Acompanhamento Individual do Aluno',
            'description' => null,
            'link' => '/module/Reports/StudentAccompanyRecord',
            'order' => 0,
            'old' => 999203,
            'process' => 999203,
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Menu::query()->where('process', 999203)->delete();
    }
}
