<?php

use Carbon\Carbon;

use App\Aluno;
use App\CPF;
use App\Curso;
use App\Lead;
use App\Lead_Status;

use App\Integracoes\Legacy as Legacy;
use App\System\Event;
use App\System\Process;

require_once ('Helpers.php');

$schedule->call(function () {

	Process::init('schedule')->text('Migrações CRM diárias')->run(function() {
		Legacy\Migracao_Base::rodar_migracoes();
	});
	
})->timezone('America/Sao_Paulo')->hourly(); // Por motivos de redundância, iremos rodar as conversões a cada hora, com limites (timeout) de 10 minutos

/*$schedule->call(function () {

	Process::init('schedule')->text('Migrações CRM diárias')->run(function() {
		Legacy\Migracao_Base::rodar_migracoes();
	});
	
})->timezone('America/Sao_Paulo')->hourly()->between('18:00', '23:00'); // Por motivos de redundância, iremos rodar as conversões a cada hora, com limites (timeout) de 10 minutos, entre 18:00 e 23:00

// Segunda Schedule

$schedule->call(function () {

	Process::init('schedule')->text('Migrações CRM diárias')->run(function() {
		Legacy\Migracao_Base::rodar_migracoes();
	});
	
})->timezone('America/Sao_Paulo')->hourly()->between('00:00', '06:00'); // Por motivos de redundância, iremos rodar as conversões a cada hora, com limites (timeout) de 10 minutos, entre 00:00 e 06:00*/