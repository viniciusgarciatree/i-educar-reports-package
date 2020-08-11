<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;

class CreateModulesAuditoriaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if((DB::select("select EXISTS (SELECT FROM pg_catalog.pg_tables WHERE schemaname = 'modules' AND tablename = 'auditoria');"))[0]->exists == false) {
            DB::unprepared(
                '
                SET default_with_oids = false;

                CREATE TABLE modules.auditoria (
                    usuario character varying(300),
                    operacao smallint,
                    rotina character varying(300),
                    valor_antigo text,
                    valor_novo text,
                    data_hora timestamp without time zone
                );
                
                ALTER TABLE ONLY modules.auditoria_geral
                    ADD CONSTRAINT auditoria_geral_pkey PRIMARY KEY (id);
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
        Schema::dropIfExists('modules.auditoria');
    }
}
