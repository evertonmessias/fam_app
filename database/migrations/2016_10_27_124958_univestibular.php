<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Univestibular extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        // Mídias e Tipos de Mídia
        Schema::create('midias_tipos', function (Blueprint $table) {
            $table->increments('id');
            $table->string('codigo');
            $table->string('nome');
            $table->integer('categoria_id')->unsigned()->nullable();

            $table->foreign('categoria_id')->references('id')->on('midias_tipos');
        });
        Schema::create('fornecedores', function (Blueprint $table) {
            $table->increments('id');
            $table->string('cnpj')->unique();
            $table->string('nome_fantasia');
            $table->string('razao_social');
            $table->string('email');
            $table->string('email_alt');
            $table->string('fone');
            $table->string('fone_alt');
            $table->string('c_nome');
            $table->string('c_cargo');
            $table->string('c_gerente');
        });
        Schema::create('midias', function (Blueprint $table) {
            $table->increments('id');
            $table->string('nome');
            $table->integer('fornecedor_id')->unsigned();
            $table->integer('tipo_id')->unsigned();

            $table->foreign('fornecedor_id')->references('id')->on('fornecedores');
            $table->foreign('tipo_id')->references('id')->on('midias_tipos');
        });

        ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        // Cidades e Estados
        Schema::create('estados', function (Blueprint $table) {
            $table->increments('id');
            $table->string('uf');
            $table->string('nome');
        });
        Schema::create('cidades', function (Blueprint $table) {
            $table->increments('id');
            $table->string('nome');
            $table->string('coordenadas')->nullable();
            $table->integer('estado_id')->unsigned();

            $table->foreign('estado_id')->references('id')->on('estados');
        });

        ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        // Alunos e Docentes
        Schema::create('alunos', function (Blueprint $table) {
            $table->increments('id');
            $table->string('nome');
            $table->string('sobrenome');
            $table->string('email');
            $table->string('cpf')->unique();
            $table->string('rg')->nullable();
            $table->date('datanascimento')->nullable();
            $table->string('sexo')->nullable();
            $table->string('endereco')->nullable();
            $table->string('numero')->nullable();
            $table->string('bairro')->nullable();
            $table->string('complemento')->nullable();
            $table->integer('cidade_id')->unsigned()->nullable();            
            $table->string('celular')->nullable();
            $table->string('telefone')->nullable();
            $table->string('nome_social')->nullable();
            $table->string('deficiencia')->nullable();
            $table->string('ingresso')->nullable(); 
            $table->string('arquivos')->nullable();            
            $table->string('aceite')->nullable();
            $table->string('distancia')->nullable();
            $table->timestamps();

            $table->foreign('cidade_id')->references('id')->on('cidades');
        });
        Schema::create('docentes', function (Blueprint $table) {
            $table->increments('id');
            $table->string('nome');
        });

        ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        // Cursos, Grades e Matérias
        Schema::create('materias', function (Blueprint $table) {
            $table->increments('id');
            $table->string('nome');
        });
        Schema::create('materias_docentes', function (Blueprint $table) {
            $table->integer('materia_id')->unsigned();
            $table->integer('docente_id')->unsigned();

            $table->foreign('materia_id')->references('id')->on('materias');
            $table->foreign('docente_id')->references('id')->on('docentes');
        });

        Schema::create('cursos_periodos', function (Blueprint $table) {
            $table->increments('id');
            $table->string('periodo');
        });
        Schema::create('cursos_grades', function (Blueprint $table) {
            $table->increments('id');
            $table->string('nome');
        });

        Schema::create('grade_materias', function (Blueprint $table) {
            $table->integer('grade_id')->unsigned();
            $table->integer('materia_id')->unsigned();
            $table->integer('semestre');

            $table->foreign('grade_id')->references('id')->on('cursos_grades');
            $table->foreign('materia_id')->references('id')->on('materias');
        });
        Schema::create('cursos', function (Blueprint $table) {
            $table->increments('id');
            $table->string('codigo');
            $table->string('nome');
            $table->integer('vagas');
            $table->integer('duracao');
            $table->float('valor');
            $table->integer('grade_id')->unsigned()->nullable();

            $table->foreign('grade_id')->references('id')->on('cursos_grades');
        });

        Schema::create('curso_periodo', function (Blueprint $table) {
            $table->integer('curso_id')->unsigned();
            $table->integer('periodo_id')->unsigned();

            $table->foreign('curso_id')->references('id')->on('cursos');
            $table->foreign('periodo_id')->references('id')->on('cursos_periodos');
        });

        ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        // Campanhas
        Schema::create('campanhas', function (Blueprint $table) {
            $table->increments('id');
            $table->string('nome');
            $table->float('budget');
            $table->date('inicio');
            $table->date('fim');
            $table->text('campos_personalizados');
            $table->timestamps();
        });
        Schema::create('campanha_curso', function (Blueprint $table) {
            $table->integer('campanha_id')->unsigned();
            $table->integer('curso_id')->unsigned();

            $table->foreign('campanha_id')->references('id')->on('campanhas');
            $table->foreign('curso_id')->references('id')->on('cursos');

            $table->unique(['campanha_id', 'curso_id']);
        });
        Schema::create('campanha_midia', function (Blueprint $table) {
            $table->integer('campanha_id')->unsigned();
            $table->integer('midia_id')->unsigned();

            $table->foreign('campanha_id')->references('id')->on('campanhas');
            $table->foreign('midia_id')->references('id')->on('midias_tipos');

            $table->unique(['campanha_id', 'midia_id']);
        });

        ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        // Unidades
        Schema::create('unidades', function (Blueprint $table) {
            $table->increments('id');
            $table->string('nome')->nullable();
            $table->string('endereco')->nullable();
            $table->string('numero')->nullable();
            $table->string('bairro')->nullable();
            $table->integer('cidade_id')->unsigned()->nullable();
            $table->string('telefone')->nullable();
            $table->string('email')->nullable();
            $table->string('coordenadas')->nullable();

            $table->foreign('cidade_id')->references('id')->on('cidades');
        });

        Schema::create('curso_unidade', function (Blueprint $table) {
            $table->integer('unidade_id')->unsigned();
            $table->integer('curso_id')->unsigned();

            $table->foreign('unidade_id')->references('id')->on('unidades');
            $table->foreign('curso_id')->references('id')->on('cursos');

            $table->unique(['unidade_id', 'curso_id']);
        });

        ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        // Provas
        Schema::create('provas_locais', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('unidade_id')->unsigned()->nullable();
            $table->string('local')->nullable();
            $table->string('endereco')->nullable();
            $table->string('telefone')->nullable();
            $table->string('email')->nullable();
            $table->string('coordenadas')->nullable();

            $table->foreign('unidade_id')->references('id')->on('unidades');
        });
        Schema::create('provas_datas', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('local_id')->unsigned();
            $table->datetime('hora');
            $table->integer('maximo');
            $table->boolean('disponivel');

            $table->foreign('local_id')->references('id')->on('provas_locais');
        });
        Schema::create('provas', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('data_id')->unsigned();
            $table->integer('local_id')->unsigned();
            $table->integer('aluno_id')->unsigned();
            $table->integer('curso_id')->unsigned();
            $table->integer('campanha_id')->unsigned();
            $table->boolean('participou')->unsigned()->default(false);
            $table->boolean('aprovado')->unsigned()->default(false);
            $table->float('nota')->nullable();

            $table->foreign('data_id')->references('id')->on('provas_datas');
            $table->foreign('local_id')->references('id')->on('provas_locais');
            $table->foreign('aluno_id')->references('id')->on('alunos');
            $table->foreign('curso_id')->references('id')->on('cursos');
            $table->foreign('campanha_id')->references('id')->on('campanhas');
        });

        ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        // Leads
        Schema::create('lead_status', function (Blueprint $table) {
            $table->string('codigo')->unique();
            $table->string('nome');
        });
        Schema::create('leads', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('campanha_id')->unsigned();
            $table->integer('aluno_id')->unsigned();
            $table->integer('midia_id')->unsigned()->nullable();
            $table->integer('curso_id')->unsigned();
            $table->integer('prova_id')->unsigned()->nullable();
            $table->string('status_id')->default('LEAD');
            $table->text('dados_adicionais');
            $table->timestamps();

            $table->foreign('campanha_id')->references('id')->on('campanhas');
            $table->foreign('aluno_id')->references('id')->on('alunos');
            $table->foreign('midia_id')->references('id')->on('midias_tipos');
            $table->foreign('curso_id')->references('id')->on('cursos');
            $table->foreign('prova_id')->references('id')->on('provas');
            $table->foreign('status_id')->references('codigo')->on('lead_status');
        });
        Schema::create('lead_history', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('campanha_id')->unsigned();
            $table->integer('lead_id')->unsigned();
            $table->datetime('at');
            $table->string('status_was');
            $table->string('status_new');
            $table->string('title')->nullable();
            $table->text('description');

            $table->foreign('campanha_id')->references('id')->on('campanhas');
            $table->foreign('lead_id')->references('id')->on('leads');
            $table->foreign('status_was')->references('codigo')->on('lead_status');
            $table->foreign('status_new')->references('codigo')->on('lead_status');
        });

        ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        // Modulos e Instalações
        Schema::create('modules', function (Blueprint $table) {
            $table->increments('id');
            $table->string('domain');
            $table->string('namespace');
            $table->boolean('www')->default(true);
            $table->string('root')->default('/');
            $table->text('options');
            $table->timestamps();
        });

        ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        // Notas Fiscais
        Schema::create('notas_fiscais', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('numero');
            $table->date('data');
            $table->date('vencimento');
            $table->string('descricao');
            $table->float('valor');
            $table->integer('fornecedor_id')->unsigned();
            $table->boolean('somar_filhas')->default(false);
            $table->timestamps();

            $table->foreign('fornecedor_id')->references('id')->on('fornecedores');
        });
        Schema::create('notas_fiscais_campanhas', function (Blueprint $table) {
            $table->integer('nota_id')->unsigned();
            $table->integer('midia_id')->unsigned();
            $table->integer('campanha_id')->unsigned();
            $table->float('porcentagem');

            $table->unique(['nota_id', 'campanha_id', 'midia_id']);

            $table->foreign('nota_id')->references('id')->on('notas_fiscais');
            $table->foreign('midia_id')->references('id')->on('midias');
            $table->foreign('campanha_id')->references('id')->on('campanhas');
        });
        Schema::create('notas_fiscais_relacionadas', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('nota_id')->unsigned();
            $table->integer('filha_id')->unsigned();

            $table->unique(['nota_id', 'filha_id']);
            
            $table->foreign('nota_id')->references('id')->on('notas_fiscais');
            $table->foreign('filha_id')->references('id')->on('notas_fiscais');
        });

        ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        // Cache dos Gráficos
        Schema::create('cached_charts', function (Blueprint $table) {
            $table->string('chart_id');
            $table->string('title');
            $table->date('day');
            $table->string('data');
            $table->timestamps();
        });

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
