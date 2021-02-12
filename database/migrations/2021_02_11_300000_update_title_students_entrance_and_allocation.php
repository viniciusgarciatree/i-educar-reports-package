<?php

use App\Menu;
use Illuminate\Database\Migrations\Migration;

class UpdateTitleStudentsEntranceAndAllocation extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Menu::query()->updateOrCreate(
            [
                'old' => 999871
            ],
            [
                'parent_id'   => Menu::query()->where('old', 999923)->firstOrFail()->getKey(),
                'title'       => 'Relação de alunos por data de matrícula e enturmação',
                'description' => null,
                'link'        => '/module/Reports/StudentsEntranceAndAllocation',
                'order'       => 0,
                'old'         => 999871,
                'process'     => 999871,
                'active'      => true,
            ]
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Menu::query()->where('process', 999871)->delete();
    }
}
