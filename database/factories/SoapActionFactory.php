<?php

use Faker\Generator as Faker;

$factory->define(App\Models\SoapAction::class, function (Faker $faker) {
    return [
        'fk_cpe_id'=> function() {
            print_r("IActionContract Factory: before get first CPE from database\n");
            $cpe = App\Models\CPE::first();
            print_r("IActionContract Factory: after get first CPE from database\n");
            if(empty($cpe))
            {
                return factory(App\Models\CPE::class)->create()->id;
            }
            return $cpe->id;
        },
        'cwmpid'=>$faker->uuid,
    ];
});
