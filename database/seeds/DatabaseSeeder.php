<?php

use Illuminate\Database\Seeder;

use Illuminate\Support\Facades\File;

use App\Http\Controllers\MigrationMController;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // $this->call(UsersTableSeeder::class);

        DB::unprepared(File::get(base_path() . '/tmp/statu.sql'));
        DB::unprepared(File::get(base_path() . '/tmp/estados.sql'));
        DB::unprepared(File::get(base_path() . '/tmp/centers.sql'));
        DB::unprepared(File::get(base_path() . '/tmp/cbo.sql'));
        DB::unprepared(File::get(base_path() . '/tmp/municipio.sql'));
        DB::unprepared(File::get(base_path() . '/tmp/roles.sql'));
        DB::unprepared(File::get(base_path() . '/tmp/types.sql'));

        MigrationMController::populateDatabase();
    }
}
//
