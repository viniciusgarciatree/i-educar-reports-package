<?php

use App\Menu;
use Illuminate\Database\Migrations\Migration;

class AddStudentIndividualInfantVersa extends Migration
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
                'old' => 99999104
            ],
            [
                'parent_id'   => Menu::query()->where('old', 999861)->firstOrFail()->getKey(),
                'title'       => 'Ficha individual de avaliação formativa',
                'description' => null,
                'link'        => '/module/Reports/StudentIndividualInfantVersa',
                'order'       => 0,
                'old'         => 99999104,
                'process'     => 99999104,
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
        Menu::query()->where('process', 99999104)->delete();
    }
}
