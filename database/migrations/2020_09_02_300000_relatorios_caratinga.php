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
        $result = DB::select("select EXISTS (SELECT FROM pmieducar.instituicao WHERE cnpj = '18334292000164');");
        /* Para Ipanema verifica com cnpj */
        if (count($result) > 0 && $result[0] == true)
        {
            Menu::query()->updateOrCreate(
                [
                    'old' => 9999200
                ],
                [
                    'parent_id'   => Menu::query()->where('old', 999861)->firstOrFail()->getKey(),
                    'title'       => 'Ficha do Aluno',
                    'description' => 'Ficha do Aluno',
                    'link'        => '/module/Reports/StudentSheetByCustom',
                    'order'       => 0,
                    'old'         => 9999201,
                    'process'     => 9999201,
                    'parent_old'  => 999861,
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
        Menu::query()->where('process', 9999200)->delete();
    }
}