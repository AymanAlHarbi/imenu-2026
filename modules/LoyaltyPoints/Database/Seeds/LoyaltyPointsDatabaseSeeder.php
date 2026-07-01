<?php

namespace Modules\LoyaltyPoints\Database\Seeds;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;

class LoyaltyPointsDatabaseSeeder extends Seeder
{
    public function run()
    {
        Model::unguard();

        Model::reguard();
    }
}
