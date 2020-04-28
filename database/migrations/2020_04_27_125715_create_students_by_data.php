<?php

use App\Menu;
use Illuminate\Database\Migrations\Migration;

class CreateStudentsByData extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Menu::query()->create([
            'parent_id' => Menu::query()->where('old', 999300)->firstOrFail()->getKey(),
            'title' => 'Relatório Dados do Aluno',
            'description' => 'Relatório Dados do Aluno',
            'link' => '/module/Reports/StudentsByData',
            'order' => 0,
            'old' => 999401,
            'process' => 999401,
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Menu::query()->where('process', 999401)->delete();
    }
}
