<?php

use Illuminate\Database\Seeder;
use App\Models\User;//添加

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $users = factory(User::class)->times(50)->make();
        User::insert($users->toArray());

        $user = User::find(1);
        $user->name = 'Fang';
        $user->email = '52putixin@gmail.com';
        $user->password = bcrypt('password');
        $user->save();
    }
}
