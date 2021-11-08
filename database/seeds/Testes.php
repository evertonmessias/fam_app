<?php

use App\Aluno;
use App\Curso;
use App\Campanha;
use App\Fornecedor;
use App\Grade;
use App\Lead;
use App\Lead_Status;
use App\Midia;
use App\Midia_Tipo;

use Illuminate\Database\Seeder;

class Testes extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //

        _ ('Criado dados de teste...');

        $aluno 			= factory('App\Aluno')->create();
        $fornecedor 	= factory('App\Fornecedor')->create();

        $midia			= new Midia (['nome' => 'Midia Teste']);
        $midia->fornecedor()->associate($fornecedor);
        $midia->tipo()->associate(Midia_Tipo::inRandomOrder()->get()[0]);
        $midia->save();

        $grade 			= Grade::create (['nome' => 'CVD']);

        $curso			= new Curso (['codigo' => 'CVD', 'nome' => 'Comunicação Visual e Design Gráfico', 'duracao' => 8, 'valor' => 1352.90]);
        $curso->grade()->associate($grade);
        $curso->save();

        $campanha		= Campanha::create(['nome' => 'Vestibular Inverno 2017', 'inicio' => '2017-03-14', 'fim' => '2017-07-20']);
        $campanha->cursos()->attach($curso);
        $campanha->save();

        $lead			= new Lead ();
        $lead->aluno()->associate($aluno);
        $lead->campanha()->associate($campanha);
        $lead->midia()->associate($midia->tipo);
        $lead->curso()->associate($curso);
        $lead->status()->associate(Lead_Status::find('LEAD'));
        $lead->save();
    }
}
