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
                    'old' => 9999202
                ],
                [
                    'parent_id'   => Menu::query()->where('old', 999923)->firstOrFail()->getKey(),
                    'title'       => 'Alunos matriculados por série',
                    'description' => null,
                    'link'        => '/module/Reports/StudentsBySeries',
                    'order'       => 0,
                    'old'         => 9999202,
                    'process'     => 9999202,
                    'active'      => true,
                ]
            );

            $result = DB::select("UPDATE public.menus SET active = false where id = 207 and old = 999203");
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Menu::query()->where('process', 9999202)->delete();
    }
}