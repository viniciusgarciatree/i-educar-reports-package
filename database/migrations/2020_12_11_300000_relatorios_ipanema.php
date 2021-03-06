<?php

use App\Menu;
use Illuminate\Database\Migrations\Migration;

class RelatoriosIpanema extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $result = DB::select('select EXISTS (SELECT FROM pmieducar.instituicao WHERE cnpj = \'18334292000164\');');
        /* Para Ipanema verifica com cnpj */
        if (count($result) > 0 && $result[0] == true) {

            Menu::query()->updateOrCreate(
                [
                    'old' => 9999200
                ],
                [
                    'parent_id'   => Menu::query()->where('old', 999861)->firstOrFail()->getKey(),
                    'title'       => 'Ficha Individual do Aluno',
                    'description' => null,
                    'link'        => '/module/Reports/StudentIndividualRecord',
                    'order'       => 0,
                    'old'         => 9999200,
                    'process'     => 9999200,
                    'active'      => true,
                ]
            );

            Menu::query()->updateOrCreate(
                [
                    'old' => 9999207
                ],
                [
                    'parent_id'   => Menu::query()->where('old', 999861)->firstOrFail()->getKey(),
                    'title'       => 'Ficha Individual - EJA',
                    'description' => null,
                    'link'        => '/module/Reports/StudentIndividualRecordEja',
                    'order'       => 0,
                    'old'         => 9999207,
                    'process'     => 9999207,
                    'active'      => true,
                ]
            );

            Menu::query()->updateOrCreate(
                [
                    'old' => 9999208
                ],
                [
                    'parent_id'   => Menu::query()->where('old', 999861)->firstOrFail()->getKey(),
                    'title'       => 'Ficha individual de avaliação formativa',
                    'description' => null,
                    'link'        => '/module/Reports/StudentIndividualInfant',
                    'order'       => 0,
                    'old'         => 9999208,
                    'process'     => 9999208,
                    'active'      => true,
                ]
            );
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Menu::query()->where('process', 9999200)->delete();
        Menu::query()->where('process', 9999207)->delete();
        Menu::query()->where('process', 9999208)->delete();
    }
}
