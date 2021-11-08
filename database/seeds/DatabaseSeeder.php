<?php

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;

use App\Module;
use App\Role;
use App\Permission;

if (!function_exists('_')) {
    function _ ($str) {
    	if (is_string($str))
    		print ($str . "\n");
    	else
    		var_dump($str);
    }
}

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        _ ('Inicializando seeder...');

        sleep(5);

        Model::unguard();
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // Cidades e Estados

        include ('exported.php');

        _ ('Limpando dados existentes...');
        App\Cidade::getQuery()->delete();
        App\Estado::getQuery()->delete();
        App\Midia_Tipo::getQuery()->delete();
        App\Lead_Status::getQuery()->delete();
        App\Unidade::getQuery()->delete();
        App\User::getQuery()->delete();

        // Módulos padrão
        Module::getQuery()->delete();
        Module::create(['domain' => 'localhost', 'namespace' => 'Admin', 'root' => '/', 'options' => '{}']);
        Module::create(['domain' => '192.168.5.164', 'namespace' => 'Admin', 'root' => '/', 'options' => '{}']);
        Module::create(['domain' => 'sistema.fam.local', 'namespace' => 'Admin', 'root' => '/', 'options' => '{}']);
        Module::create(['domain' => 'vestibular.fam.local', 'namespace' => 'AmbienteConversao', 'root' => '/', 'options' => ['campanha' => '2', 'url_edital' => 'http://fam.br/edital/2017-vest.pdf', 'diretorio' => 'vestibularfam2017']]);
        Module::create(['domain' => 'tec.fam.local', 'namespace' => 'AmbienteConversao', 'root' => '/', 'options' => ['campanha' => '2', 'url_edital' => 'http://fam.br/edital/2017-tecfam.pdf', 'diretorio' => 'tecfam2017']]); //

        _ ('Importando...');

        App\Estado::insert($estados);
        _ ('Importados ' . count($estados) . ' estados.');

        App\Cidade::insert($cidades);
        _ ('Importadas ' . count($cidades) . ' cidades.');

        // Criar tipos de mídia

        _ ('Criando tipos de mídia padrão...');

        App\Midia_Tipo::insert([
            ['id' => 1, 'codigo' => 'ONLINE', 'nome' => 'Online'],
            ['id' => 2, 'codigo' => 'OFFLINE', 'nome' => 'Offline'],
            ['id' => 3, 'codigo' => 'INDICACAO', 'nome' => 'Indicação'],
            ['id' => 4, 'codigo' => 'OUTROS', 'nome' => 'Outros'],
            ['id' => 5, 'codigo' => 'RADIO', 'nome' => 'Rádio'],
            ['id' => 6, 'codigo' => 'TV', 'nome' => 'TV'],
        ]);

        App\Midia_Tipo::insert([
            ['id' => 7, 'codigo' => 'INTERNET', 'nome' => 'Internet', 'categoria_id' => 1],
            ['id' => 8, 'codigo' => 'IMPRESSA', 'nome' => 'Impressa', 'categoria_id' => 2],
        ]);

        App\Midia_Tipo::insert([
            ['codigo' => 'OUTDOOR', 'nome' => 'Outdoor', 'categoria_id' => 2],
            ['codigo' => 'LED', 'nome' => 'Painel LED', 'categoria_id' => 2],
            ['codigo' => 'REDESSOCIAIS', 'nome' => 'Redes Sociais', 'categoria_id' => 7],
            ['codigo' => 'GOOGLE', 'nome' => 'Google', 'categoria_id' => 7],
            ['codigo' => 'JORNAL', 'nome' => 'Jornal', 'categoria_id' => 8],
            ['codigo' => 'REVISTA', 'nome' => 'Revista', 'categoria_id' => 8],
            ['codigo' => 'CATALOGO', 'nome' => 'Catálogo', 'categoria_id' => 8],
        ]);

        // Criar tipos de lead

        App\Lead_Status::insert([
            ['codigo' => 'LEAD', 'nome' => 'Lead'],
            ['codigo' => 'INSC', 'nome' => 'Inscrito'],
            ['codigo' => 'MATR', 'nome' => 'Matriculado']
        ]);

        // Criar roles padrão

        $dev = new Role();
        $dev->name = 'dev';
        $dev->display_name = 'Desenvolvedor';
        $dev->description = 'Acesso root ao Desenvolvedor.';
        $dev->save();

        $admin = new Role();
        $admin->name = 'admin';
        $admin->display_name = 'Administrador';
        $admin->description = 'Administrador do sistema.';
        $admin->save();

        // Criar permissões

        Permission::make ('gerenciamento', 'Gerenciamento', 'Acesso ao menu de gerenciamento.');
        Permission::make ('gerenciamento.dashboard', 'Dashboard', 'Acesso à dashboard do sistema.');
        Permission::make ('gerenciamento.campanhas', 'Campanhas', 'Acesso ao gerenciamento de campanhas.');
        Permission::make ('gerenciamento.modulos', 'Módulos', 'Gerenciamento de módulos.');
        Permission::make ('gerenciamento.alunos', 'Alunos', 'Gerenciamento de alunos.');
        Permission::make ('gerenciamento.unidades', 'Unidades', 'Gerenciamento de unidades.');
        Permission::make ('gerenciamento.provas', 'Provas', 'Gerenciamento de provas.');

        Permission::make ('financeiro', 'Financeiro', 'Acesso ao menu de financeiro.');
        Permission::make ('financeiro.fornecedores', 'Fornecedores', 'Acesso ao gerenciamento de fornecedores.');
        Permission::make ('financeiro.midias', 'Fornecedores', 'Acesso ao gerenciamento de mídias.');
        Permission::make ('financeiro.notas', 'Notas Fiscais', 'Acesso ao gerenciamento de notas fiscais.');

        $admin->attachPermissions (Permission::all());

        // Permissões para desenvolvedor

        Permission::make ('dev', 'Desenvolvedor', 'Acesso ao menu de desenvolvedor.');

        $dev->attachPermissions (Permission::all());

        // Criar usuários padrão

        App\User::create(['name' => 'Matt Pratta', 'email' => 'matpratta@gmail.com', 'password' => bcrypt('123456')])->attachRole($dev);
        App\User::create(['name' => 'Eryvelton Baldin', 'email' => 'eryvelton@fam.br', 'password' => bcrypt('123456')])->attachRole($admin);
        App\User::create(['name' => 'Bruno Valente', 'email' => 'valente@b2smarketing.com', 'password' => bcrypt('123456')])->attachRole($admin);

        unset ($alunos);
        unset ($cidades);
        unset ($estados);
        unset ($cursos);

        // Cria Escolas

        $this->call(EscolaTableSeeder::class);

        // Criar dados de teste

        $this->call(Limpeza::class);
        // $this->call(Testes::class);
        $this->call(Imports::class);

        Model::reguard();

        exit;
    }
}
