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
use App\Nota_Fiscal;
use App\Nota_Fiscal_Campanha;
use App\Prova;
use App\Prova_Data;
use App\Prova_Local;
use App\Unidade;

use Illuminate\Database\Seeder;

class Limpeza extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //

        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        _ ('Limpando tabelas...');

        Aluno::truncate();
        Curso::truncate();
        Campanha::truncate();
        Fornecedor::truncate();
        Grade::truncate();
        Lead::truncate();
        Midia::truncate();
        Nota_Fiscal::truncate();
        Nota_Fiscal_Campanha::truncate();
        Prova::truncate();
        Prova_Data::truncate();
        Prova_Local::truncate();
        Unidade::truncate();
        
        // Periodo::truncate();

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}
