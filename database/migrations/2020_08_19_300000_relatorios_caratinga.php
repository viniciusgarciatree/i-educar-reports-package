<?php

use App\Menu;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RelatoriosCaratinga extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasColumn('pmieducar.instituicao', 'cnpj')){
            Schema::table('pmieducar.instituicao', function (Blueprint $table)
            {
                DB::unprepared(' ALTER TABLE pmieducar.instituicao ADD cnpj numeric(14) NULL; ');
            });
        }


        $result = DB::select("select EXISTS (SELECT FROM pmieducar.instituicao WHERE cnpj = '18334268000125');");
        /* Para caratinga verifica com cnpj */
        if(count($result) > 0 && $result[0] == true) {
            Menu::query()->updateOrCreate(
                [
                    'old' => 999732
                ],
                [
                    'parent_id'   => Menu::query()->where('old', 999300)->firstOrFail()->getKey(),
                    'title'       => 'Relatório de dados familiares do aluno',
                    'description' => null,
                    'link'        => '/module/Reports/StudentsByRelatives',
                    'order'       => 0,
                    'old'         => 999732,
                    'process'     => 999732,
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
        Menu::query()->where('old', 999732)->delete();
    }
}
