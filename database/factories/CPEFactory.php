<?php

use Faker\Generator as Faker;

$factory->define(App\Models\CPE::class, function (Faker $faker) {
    return [
        'connection_request_username' => $faker->name,
        'connection_request_password' => '$2y$10$TKh8H1.PfQx37YgCzwiKb.KjNyWgaHb9cbcoQgdIVFlYg7B77UdFm', // secret
        'connection_request_url' => str_random(10),
    ];
});
