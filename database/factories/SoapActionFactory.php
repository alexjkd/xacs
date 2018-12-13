<?php

use Faker\Generator as Faker;

$factory->define(App\Models\SoapAction::class, function (Faker $faker) {
    return [
        'fk_cpe_id'=> function() {
            $cpe = App\Models\CPE::first();
            if(empty($cpe))
            {
                return factory(App\Models\CPE::class)->create()->id;
            }
            return $cpe->id;
        },
    ];
});
