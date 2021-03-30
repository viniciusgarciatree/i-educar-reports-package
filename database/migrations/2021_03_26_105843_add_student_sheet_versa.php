<?php

use App\Menu;
use Illuminate\Database\Migrations\Migration;

class AddStudentSheetVersa extends Migration
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
                'old' => 99999102
            ],
            [
                'parent_id'   => Menu::query()->where('old', 999861)->firstOrFail()->getKey(),
                'title'       => 'Ficha de acompanhamento individual do aluno',
                'description' => null,
                'link'        => '/module/Reports/StudentAccompanyRecordVersa',
                'order'       => 0,
                'old'         => 99999102,
                'process'     => 99999102,
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
        Menu::query()->where('process', 99999102)->delete();
    }
}
