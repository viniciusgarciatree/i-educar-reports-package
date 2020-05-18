<?php

use App\Menu;
use Illuminate\Database\Migrations\Migration;

class CreateTransportationStudentsUse extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Menu::query()->create([
            'parent_id' => Menu::query()->where('old', 9998847)->firstOrFail()->getKey(),
            'title' => 'Relação de alunos que usam o transporte escolar',
            'description' => 'Relação de alunos que usam o transporte escolar',
            'link' => '/module/Reports/TransportationStudentsUse',
            'order' => 0,
            'old' => 999730,
            'process' => 999730,
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Menu::query()->where('process', 999730)->delete();
    }
}
