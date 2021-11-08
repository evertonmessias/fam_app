<?php

use Illuminate\Http\Request;

use Carbon\Carbon;

use App\Role;
use App\Permission;
use App\User;

$middle = function (Request $req, $next) {
	return $next($req);
};

Route::group(['middleware' => $middle], function () {

	// Exibe, altera e assigna permissões e funções
	Route::get('/roles', function () {
		$permissoes = Permission::all()->keyBy('id')->sortBy('name');
		$funcoes = Role::all()->keyBy('id')->sortBy('name');

		return view('Admin::Users.roles', [
			'funcoes' => $funcoes,
			'permissoes' => $permissoes
		]);
	});
	Route::post('/roles', function (Request $req) {
		// Criar nova função
		if (!empty($req->input('new_fn.name', ''))) {
			$role = new Role();
			$role->name = $req->input('new_fn.name');
			$role->display_name = $req->input('new_fn.display_name');
			$role->description = $req->input('new_fn.description');
			$role->save();
		}

		// Criar nova permissão
		if (!empty($req->input('new_perm.name', ''))) {
			$perm = new Permission();
			$perm->name = $req->input('new_perm.name');
			$perm->display_name = $req->input('new_perm.display_name');
			$perm->description = $req->input('new_perm.description');
			$perm->save();
		}

		// Puxar listas de funções e permissões
		$permissoes = Permission::all()->keyBy('name')->sortBy('name');
		$funcoes = Role::all()->keyBy('name')->sortBy('name');

		// Loop das funções
		foreach ($funcoes as $name => $funcao) {
			$perms = [];
			$perms_data = $req->input('funcao.' . $name, []);

			if ($name == 'dev')
				$perms_data = $permissoes->all();

			// Aqui filtramos as novas permissões
			foreach ($perms_data as $perm => $on) {
				$perms[] = $permissoes[$perm]->id;
			}

			// Syncamos pra salvar
			$funcao->perms()->sync($perms);
		}

		return redirect('/users/roles');
	});

	// Lista usuários e cria novos
	Route::get('/', function () {
		$users = User::with('roles')->get();
		$roles = Role::all()->keyBy('id')->sortBy('display_name');

		return view('Admin::Users.index', [
			'users' => $users,
			'roles' => $roles
		]);
	});
	Route::post('/', function (Request $req) {
		// Novo Usuario
		if (!empty($req->input('new_user.email'))) {
			$new = new User();
			$new->name = $req->input('new_user.name');
			$new->email = $req->input('new_user.email');
			$new->password = bcrypt($req->input('new_user.password'));
			$new->save();

			$new->attachRole(Role::find($req->input('new_user.role')));
			$new->save();
		}

		// Outros usuários
		foreach ($req->input('user', []) as $id => $data) {
			$user = User::find($id);

			// Resetar senhas
			if (!empty($data['reset_password']))
				$user->password = bcrypt($data['reset_password']);

			// Novas funções
			$user->roles()->sync([$data['role']]);

			// Salvar
			$user->save();
		}

		return redirect('/users');
	});
});