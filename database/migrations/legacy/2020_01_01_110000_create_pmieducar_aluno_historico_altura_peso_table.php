<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;

class CreatePmieducarAlunoHistoricoAlturaPesoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if((DB::select("select EXISTS (SELECT FROM pg_catalog.pg_tables WHERE schemaname = 'pmieducar' AND tablename = 'aluno_historico_altura_peso');"))[0]->exists == false) {
            DB::unprepared(
                '
                SET default_with_oids = false;

                CREATE TABLE pmieducar.aluno_historico_altura_peso (
                    ref_cod_aluno integer NOT NULL,
                    data_historico date NOT NULL,
                    altura numeric(12,2) NOT NULL,
                    peso numeric(12,2) NOT NULL
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
        Schema::dropIfExists('pmieducar.aluno_historico_altura_peso');
    }
}
