<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;

class CreatePmieducarRelacaoCategoriaAcervoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if((DB::select("select EXISTS (SELECT FROM pg_catalog.pg_tables WHERE schemaname = 'pmieducar' AND tablename = 'relacao_categoria_acervo');"))[0]->exists == false) {
            DB::unprepared(
                '
                SET default_with_oids = false;

                CREATE TABLE pmieducar.relacao_categoria_acervo (
                    ref_cod_acervo integer NOT NULL,
                    categoria_id integer NOT NULL
                );
            '
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
        Schema::dropIfExists('pmieducar.relacao_categoria_acervo');
    }
}
