<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;

class CreatePmieducarEscolaUsuarioTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        # FIXME
        if((DB::select("select EXISTS (SELECT FROM pg_catalog.pg_tables WHERE schemaname = 'pmieducar' AND tablename = 'escola_usuario');"))[0]->exists == false) {
            DB::unprepared(
                '
                SET default_with_oids = false;

                CREATE SEQUENCE pmieducar.escola_usuario_id_seq
                    START WITH 1
                    INCREMENT BY 1
                    NO MINVALUE
                    NO MAXVALUE
                    CACHE 1;

                CREATE TABLE pmieducar.escola_usuario (
                    id integer NOT NULL,
                    ref_cod_usuario integer NOT NULL,
                    ref_cod_escola integer NOT NULL,
                    escola_atual integer
                );

                ALTER SEQUENCE pmieducar.escola_usuario_id_seq OWNED BY pmieducar.escola_usuario.id;
                
                ALTER TABLE ONLY pmieducar.escola_usuario ALTER COLUMN id SET DEFAULT nextval(\'pmieducar.escola_usuario_id_seq\'::regclass);
                
                ALTER TABLE ONLY pmieducar.escola_usuario
                    ADD CONSTRAINT escola_usuario_pkey PRIMARY KEY (id);

                SELECT pg_catalog.setval(\'pmieducar.escola_usuario_id_seq\', 1, false);
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
        Schema::dropIfExists('pmieducar.escola_usuario');
    }
}
