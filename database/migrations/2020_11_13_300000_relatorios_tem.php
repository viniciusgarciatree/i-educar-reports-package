<?php

use App\Menu;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class RelatoriosTem extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
//        $result = DB::select("select EXISTS (SELECT FROM pmieducar.instituicao WHERE cnpj = '18334268000125');");
//        /* Para Caratinga verifica com cnpj */
//        if (count($result) > 0 && $result[0] == true)
//        {
        Menu::query()->updateOrCreate(
            [
                    'old' => 9999204
                ],
            [
                    'parent_id'   => Menu::query()->where('old', 25)->firstOrFail()->getKey(),
                    'title'       => 'Relatório',
                    'description' => null,
                    'link'        => null,
                    'order'       => 9,
                    'old'         => 9999204,
                    'process'     => 9999204,
                    'parent_old'  => 25,
                    'active'      => true,
                ]
        );

        // INSERT INTO public.menus VALUES (13, 8, 'Ferramentas', NULL, NULL, NULL, 3, 2, NULL, 999910, 25, true, NULL, NULL);

        Menu::query()->updateOrCreate(
            [
                    'old' => 9999205
                ],
            [
                    'parent_id'   => Menu::query()->where('old', 9999204)->firstOrFail()->getKey(),
                    'title'       => 'Relatório Auditoria geral',
                    'description' => 'Relatório Auditoria geral',
                    'link'        => '/module/Reports/ReportAuditoriaGeral',
                    'order'       => 0,
                    'old'         => 9999205,
                    'process'     => 9999205,
                    'parent_old'  => 9999204,
                    'active'      => true,
                ]
        );
//        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Menu::query()->where('process', 9999204)->delete();
        Menu::query()->where('process', 9999205)->delete();
    }
}
