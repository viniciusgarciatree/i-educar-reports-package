<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;

class CreateCadastroOcupacaoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if((DB::select("select EXISTS (SELECT FROM pg_catalog.pg_tables WHERE schemaname = 'cadastro' AND tablename = 'ocupacao');"))[0]->exists == false) {
            DB::unprepared(
                '
                SET default_with_oids = true;
                
                CREATE TABLE cadastro.ocupacao (
                    idocup numeric(6,0) NOT NULL,
                    descricao character varying(250) NOT NULL
                );
                
                ALTER TABLE ONLY cadastro.ocupacao
                    ADD CONSTRAINT pk_ocupacao PRIMARY KEY (idocup);
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
        Schema::dropIfExists('cadastro.ocupacao');
    }
}
