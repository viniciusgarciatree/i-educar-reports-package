<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;

class CreateSchemas extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if((DB::select("SELECT schema_name FROM information_schema.schemata WHERE schema_name = 'cadastro';"))==0){
            DB::unprepared('CREATE SCHEMA cadastro;');
        }

        if((DB::select("SELECT schema_name FROM information_schema.schemata WHERE schema_name = 'modules';"))==0){
            DB::unprepared('CREATE SCHEMA modules;');
        }

        if((DB::select("SELECT schema_name FROM information_schema.schemata WHERE schema_name = 'pmieducar';"))==0){
            DB::unprepared('CREATE SCHEMA pmieducar;');
        }

        if((DB::select("SELECT schema_name FROM information_schema.schemata WHERE schema_name = 'portal';"))==0){
            DB::unprepared('CREATE SCHEMA portal;');
        }

        if((DB::select("SELECT schema_name FROM information_schema.schemata WHERE schema_name = 'relatorio';"))==0){
            DB::unprepared('CREATE SCHEMA relatorio;');
        }

        if((DB::select("SELECT schema_name FROM information_schema.schemata WHERE schema_name = 'urbano';"))==0){
            DB::unprepared('CREATE SCHEMA urbano;');
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::unprepared(
            '
                DROP SCHEMA cadastro;
                DROP SCHEMA modules;
                DROP SCHEMA pmieducar;
                DROP SCHEMA portal;
                DROP SCHEMA relatorio;
                DROP SCHEMA urbano;
            '
        );
    }
}
