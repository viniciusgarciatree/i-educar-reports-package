<?php

use App\Menu;
use Illuminate\Database\Migrations\Migration;

class AddStudentIndividualRecordVersa extends Migration
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
                'old' => 99999100
            ],
            [
                'parent_id'   => Menu::query()->where('old', 999861)->firstOrFail()->getKey(),
                'title'       => 'Ficha Individual do Aluno',
                'description' => null,
                'link'        => '/module/Reports/StudentIndividualRecordVersa',
                'order'       => 0,
                'old'         => 99999100,
                'process'     => 99999100,
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
        Menu::query()->where('process', 99999100)->delete();
    }
}
