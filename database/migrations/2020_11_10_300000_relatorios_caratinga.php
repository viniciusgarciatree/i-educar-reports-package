<?php

use App\Menu;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class RelatoriosCaratinga extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $result = DB::select("select EXISTS (SELECT FROM pmieducar.instituicao WHERE cnpj = '18334268000125');");
        /* Para Caratinga verifica com cnpj */
        if (count($result) > 0 && $result[0] == true)
        {
            Menu::query()->updateOrCreate(
                [
                    'old' => 9999203
                ],
                [
                    'parent_id'   => Menu::query()->where('old', 999303)->firstOrFail()->getKey(),
                    'title'       => 'Relatório PPP',
                    'description' => 'Relatório Projeto Político Pedagógico (PPP)',
                    'link'        => '/module/Reports/ReportsPpp',
                    'order'       => 0,
                    'old'         => 9999203,
                    'process'     => 9999203,
                    'parent_old'  => 999303,
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
        Menu::query()->where('process', 9999203)->delete();
    }
}