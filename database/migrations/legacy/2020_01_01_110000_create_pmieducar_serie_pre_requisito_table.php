<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;

class CreatePmieducarSeriePreRequisitoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if((DB::select("select EXISTS (SELECT FROM pg_catalog.pg_tables WHERE schemaname = 'pmieducar' AND tablename = 'serie_pre_requisito');"))[0]->exists == false) {
            DB::unprepared(
                '
                SET default_with_oids = true;

                CREATE TABLE pmieducar.serie_pre_requisito (
                    ref_cod_pre_requisito integer NOT NULL,
                    ref_cod_operador integer NOT NULL,
                    ref_cod_serie integer NOT NULL,
                    valor character varying
                );
                
                ALTER TABLE ONLY pmieducar.serie_pre_requisito
                    ADD CONSTRAINT serie_pre_requisito_pkey PRIMARY KEY (ref_cod_pre_requisito, ref_cod_operador, ref_cod_serie);
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
        Schema::dropIfExists('pmieducar.serie_pre_requisito');
    }
}
