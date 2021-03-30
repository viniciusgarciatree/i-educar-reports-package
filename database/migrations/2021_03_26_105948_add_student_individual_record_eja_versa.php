<?php

use App\Menu;
use Illuminate\Database\Migrations\Migration;

class AddStudentIndividualRecordEjaVersa extends Migration
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
                'old' => 99999103
            ],
            [
                'parent_id'   => Menu::query()->where('old', 999861)->firstOrFail()->getKey(),
                'title'       => 'Ficha Individual - EJA',
                'description' => null,
                'link'        => '/module/Reports/StudentIndividualRecordEjaVersa',
                'order'       => 0,
                'old'         => 99999103,
                'process'     => 99999103,
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
        Menu::query()->where('process', 99999103)->delete();
    }
}
