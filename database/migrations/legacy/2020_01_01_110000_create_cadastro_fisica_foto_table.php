<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;

class CreateCadastroFisicaFotoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if((DB::select("select EXISTS (SELECT FROM pg_catalog.pg_tables WHERE schemaname = 'cadastro' AND tablename = 'fisica_foto');"))[0]->exists == false) {
            DB::unprepared(
                '
                SET default_with_oids = true;
                
                CREATE TABLE cadastro.fisica_foto (
                    idpes integer NOT NULL,
                    caminho character varying(255),
	                updated_at timestamp NULL DEFAULT now()
                );
                
                ALTER TABLE ONLY cadastro.fisica_foto
                    ADD CONSTRAINT fisica_foto_pkey PRIMARY KEY (idpes);
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
        Schema::dropIfExists('cadastro.fisica_foto');
    }
}
