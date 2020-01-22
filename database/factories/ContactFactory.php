<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Contact;
use App\User;
use Faker\Generator as Faker;

$factory->define(Contact::class, function (Faker $faker) {
    return [
        'user_id' => factory(User::class), // Ko có để tiếp "->create()" thì Laravel tự figure out: nếu ko có overwrite thì mới tạo, có overwrite thì cho overwrite bình thường.
        'name' => $faker->name,
        'email' => $faker->email,
        'birthday' => '08/17/1992',
        'company' => $faker->company
    ];
});
