<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddCnpjInstitucao extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('pmieducar.instituicao')) {
            if (!Schema::hasColumn('pmieducar.instituicao', 'cnpj')) {
                Schema::table(
                    'pmieducar.instituicao',
                    function (Blueprint $table) {
                        DB::unprepared(' ALTER TABLE pmieducar.instituicao ADD cnpj numeric(14) NULL; ');
                    }
                );
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('instituicao', function (Blueprint $table) {
            //
        });
    }
}
