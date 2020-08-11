<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;

class CreateAggregates extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(DB::select("select exists(SELECT * FROM pg_proc WHERE proname = 'textcat_all' AND proisagg); ")[0]->exists == false){
            DB::unprepared(
                '
                CREATE AGGREGATE public.textcat_all(text) (
                    SFUNC = public.commacat_ignore_nulls,
                    STYPE = text,
                    INITCOND = \'\'
                );
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
        DB::unprepared(
            '
                DROP AGGREGATE public.textcat_all(text);
            '
        );
    }
}
