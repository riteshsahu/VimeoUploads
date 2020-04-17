<?php

use Illuminate\Database\Seeder;
use App\User;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        factory(User::class)->create([
            'name' => 'Ritesh',
            'email' => 'ritesh@gmail.com',
            'password' => '$2y$10$Gzm5cc8byig2GiVegMnZBOHDEPZhM.EPFSt.wzPETFXbdZhqPht5e'  // 12345678
        ]);
    }
}
