<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DefaultPublicSettingsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $datas = [
            [
                'key' => 'legacy.report.logo2_file_name' ,
                'value' => 'sme.png',
                'type' => 'string'
            ]
        ];

        foreach ($datas as $data) {
            $existe = DB::table('public.settings')
                ->where('key', $data['key'])
                ->exists();

            if (!$existe) {
                DB::table('public.settings')->insert($data);
            }
        }
    }
}
