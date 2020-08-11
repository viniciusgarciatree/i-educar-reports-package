<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;

class CreatePmieducarAcervoAcervoAutorTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if((DB::select("select EXISTS (SELECT FROM pg_catalog.pg_tables WHERE schemaname = 'pmieducar' AND tablename = 'acervo_acervo_autor');"))[0]->exists == false) {
            DB::unprepared(
                '
                SET default_with_oids = true;
                
                CREATE TABLE pmieducar.acervo_acervo_autor (
                    ref_cod_acervo_autor integer NOT NULL,
                    ref_cod_acervo integer NOT NULL,
                    principal smallint DEFAULT (0)::smallint NOT NULL
                );
                
                ALTER TABLE ONLY pmieducar.acervo_acervo_autor
                    ADD CONSTRAINT acervo_acervo_autor_pkey PRIMARY KEY (ref_cod_acervo_autor, ref_cod_acervo);
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
        Schema::dropIfExists('pmieducar.acervo_acervo_autor');
    }
}
