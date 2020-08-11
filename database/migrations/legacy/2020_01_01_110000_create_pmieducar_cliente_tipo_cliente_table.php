<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;

class CreatePmieducarClienteTipoClienteTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if((DB::select("select EXISTS (SELECT FROM pg_catalog.pg_tables WHERE schemaname = 'pmieducar' AND tablename = 'cliente_tipo_cliente');"))[0]->exists == false) {
            DB::unprepared(
                '
                SET default_with_oids = false;

                CREATE TABLE pmieducar.cliente_tipo_cliente (
                    ref_cod_cliente_tipo integer NOT NULL,
                    ref_cod_cliente integer NOT NULL,
                    data_cadastro timestamp without time zone NOT NULL,
                    data_exclusao timestamp without time zone,
                    ref_usuario_cad integer NOT NULL,
                    ref_usuario_exc integer,
                    ativo smallint DEFAULT (1)::smallint,
                    ref_cod_biblioteca integer
                );
                
                ALTER TABLE ONLY pmieducar.cliente_tipo_cliente
                    ADD CONSTRAINT cliente_tipo_cliente_pk PRIMARY KEY (ref_cod_cliente_tipo, ref_cod_cliente);
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
        Schema::dropIfExists('pmieducar.cliente_tipo_cliente');
    }
}
