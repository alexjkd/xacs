<?php

use Faker\Generator as Faker;

$factory->define(App\Models\CPE::class, function (Faker $faker) {
    return [
        'ConnectionRequestUser' => $faker->name,
        'ConnectionRequestPassword' => '$2y$10$TKh8H1.PfQx37YgCzwiKb.KjNyWgaHb9cbcoQgdIVFlYg7B77UdFm', // secret
    ];
});
