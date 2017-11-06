<?php

use App\User;
use Illuminate\Database\Seeder;

class UsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory(User::class)->create([
            'name' => 'Andrey',
            'email' => 'andriy.tarusin@binary-studio.com',
            'password' => bcrypt('123456'),
        ]);
    }
}
