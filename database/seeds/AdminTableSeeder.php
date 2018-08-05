<?php

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class AdminTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();
        //        
        Model::reguard();
        \DB::table('admin_users')->truncate();
         DB::table('admin_users')->insert([
                'name' => 'Alexei',
                'email' => 'admin@admin.com',
                'password' => bcrypt('123456'),
                'remember_token'=>'Uqwbws5Vzku0ogctcoNdevieHXrjonFdfUsO5rLXObs9wN4311Qgmy4VIVKc',
            ]);         
    }
    
}
