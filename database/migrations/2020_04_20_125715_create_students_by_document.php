<?php

use App\Menu;
use Illuminate\Database\Migrations\Migration;

class CreateStudentsByDocument extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Menu::query()->create([
            'parent_id' => Menu::query()->where('old', 999923)->firstOrFail()->getKey(),
            'title' => 'Relatório Documentos de Alunos por Turma',
            'description' => 'Relatório Documentos de Alunos por Turma',
            'link' => '/module/Reports/StudentsByDocument',
            'order' => 0,
            'old' => 999729,
            'process' => 999729,
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Menu::query()->where('process', 999729)->delete();
    }
}
