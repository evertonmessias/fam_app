<?php

namespace App;

use App\SimpleModel;

class Fornecedor extends SimpleModel
{
    //
    protected $table = 'fornecedores';

    protected $fillable = ['cnpj', 'nome_fantasia', 'razao_social', 'email', 'email_alt', 'fone', 'fone_alt', 'c_nome', 'c_cargo', 'c_gerente'];

    public function getNomeAttribute () { return $this->nome_fantasia; }

    public function getContatoAttribute ($value) {
    	return [
    		'nome' => $this->c_nome,
    		'cargo' => $this->c_cargo,
    		'gerente' => $this->c_gerente
    	];
    }

    public function midias () {
    	return $this->hasMany('App\Midia');
    }

    public function getTiposMidiasAttribute () {
        $tipos = [];
        foreach($this->midias()->cursor() as $midia) {
            $tipos[] = $midia->tipo;
        }
        return collect($tipos);
    }

    public function midia($tipo) {
        return $this->midias()->where('tipo_id', $tipo->id)->first();
    }

    // CNPJ
    public function getCnpjAttribute() {
        $cnpj = new CNPJ($this->attributes['cnpj']);

        return $cnpj->formatted();
    }
    public function setCnpjAttribute($cnpj) {
        $cnpj = new CNPJ($cnpj);

        $this->attributes['cnpj'] = $cnpj->numeric();
    }
}
