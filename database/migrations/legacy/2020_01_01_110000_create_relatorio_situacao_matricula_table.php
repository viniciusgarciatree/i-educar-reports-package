<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateRelatorioSituacaoMatriculaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if((DB::select("select EXISTS (SELECT FROM pg_catalog.pg_tables WHERE schemaname = 'relatorio' AND tablename = 'situacao_matricula');"))[0]->exists == false) {
            DB::unprepared(
                '
            CREATE TABLE relatorio.situacao_matricula (
                cod_situacao int4 NOT NULL,
                descricao varchar(50) NOT NULL,
                CONSTRAINT situacao_matricula_pkey PRIMARY KEY (cod_situacao)
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
        Schema::dropIfExists('relatorio.situacao_matricula');
    }
}
