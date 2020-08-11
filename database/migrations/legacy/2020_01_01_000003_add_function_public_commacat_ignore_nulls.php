<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddFunctionPublicCommacatIgnoreNulls extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(DB::select("select exists(SELECT * FROM pg_proc WHERE proname = 'commacat_ignore_nulls' AND proisagg); ")[0]->exists == false) {
            DB::unprepared(
                file_get_contents(__DIR__ . '/../../sqls/functions/public.commacat_ignore_nulls.sql')
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
        DB::unprepared(
            'DROP FUNCTION public.commacat_ignore_nulls(acc text, instr text);'
        );
    }
}
