<?php

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| Here you may define all of your model factories. Model factories give
| you a convenient way to create models for testing and seeding your
| database. Just tell the factory how a default model should look.
|
*/

/** @var \Illuminate\Database\Eloquent\Factory $factory */
$factory->define(App\User::class, function (Faker\Generator $faker) {
    static $password;

    return [
        'name' => $faker->name,
        'email' => $faker->unique()->safeEmail,
        'password' => $password ?: $password = bcrypt('secret'),
        'remember_token' => str_random(10),
    ];
});

$factory->define('App\Aluno', function (Faker\Generator $faker) {

	$cpf = $faker->unique()->randomNumber(3) . $faker->unique()->randomNumber(3) . $faker->unique()->randomNumber(3) . $faker->unique()->randomNumber(2);
	$rg = $faker->unique()->randomNumber(2) . $faker->unique()->randomNumber(3) . $faker->unique()->randomNumber(3) . $faker->unique()->randomNumber(1);
	$cidade = App\Cidade::inRandomOrder()->get()[0];

    return [
        'nome' => $faker->name,
        'email' => $faker->unique()->safeEmail,
        'cpf' => $cpf,
        'rg' => $rg,
        'datanascimento' => $faker->dateTime(),
        'sexo' => $faker->randomElement(['m', 'f']),
        'endereco' => 'Rua Demonstrativa',
        'numero' => $faker->randomNumber(3),
        'bairro' => 'Jd. Eden',
        'cidade_id' => $cidade->id
    ];
});

$factory->define('App\Fornecedor', function (Faker\Generator $faker) {

	$cnpj = $faker->unique()->randomNumber(3) . $faker->unique()->randomNumber(3) . $faker->unique()->randomNumber(3) . $faker->unique()->randomNumber(2);

    return [
        'nome_fantasia' => $faker->name,
        'razao_social' => $faker->name . ' LTDA',
        'email' => $faker->unique()->safeEmail,
        'email_alt' => $faker->unique()->safeEmail,
        'fone' => '+551912345678',
        'fone_alt' => '+551922220091',
        'cnpj' => $cnpj,
        'c_nome' => $faker->name,
        'c_cargo' => $faker->word,
        'c_gerente' => $faker->name
    ];
});