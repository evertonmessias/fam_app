<?php

namespace App;

use Carbon\Carbon;

class Aluno extends Model
{
    //
    protected $fillable = ['nome', 'sobrenome', 'email', 'cpf', 'rg', 'datanascimento', 'sexo', 'endereco', 'numero', 'bairro', 'cidade_id', 'celular', 'telefone'];

    public function cidade () {
    	return $this->belongsTo('App\Cidade');
    }

    public function leads () {
    	return $this->hasMany('App\Lead')->orderBy('updated_at', 'DESC');
    }

    public function provas () { return  $this->hasMany(Prova::class); }

    public function idadeNaData($data) {
        $d = strtotime($this->datanascimento);

        if ($d === false)
            return $d;

        if (!is_object($data))
            $data = Carbon::createFromTimestamp(strtotime($data));

        $idade = $data->diffInYears(Carbon::createFromTimestamp($d));

        /*$d = time() - $d;
        $d = date('Y', $d) - date('Y', 0);

        if ($d < 0)
            return false;*/

        return $idade;
    }

    public function getIdadeAttribute() {
    	/*$d = strtotime($this->datanascimento);

    	if ($d === false)
    		return $d;

    	$d = time() - $d;
    	$d = date('Y', $d) - date('Y', 0);

    	if ($d < 0)
    		return false;

    	return $d;*/
        return $this->idadeNaData(Carbon::now());
    }

    // Nome

    public function getNomeOficialAttribute () {
        return $this->attributes['nome'];
    }
    public function setNomeOficialAttribute ($value) {
        $this->attributes['nome'] = $value;
    }

    public function getNomeExibicaoAttribute () {
        if (!empty($this->nome_social))
            return $this->nome_social;
        return $this->nome_oficial;
    }
    public function setNomeExibicaoAttribute ($value) {
        if (!empty($this->nome_social))
            $this->nome_social = $value;
        else
            $this->nome_oficial = $value;
    }

    public function getPrimeiroNomeAttribute() {
        $nome = explode(' ', $this->nome_exibicao);
        return $nome[0];
    }

    public function getSobrenomeAttribute () {
        return $this->attributes['sobrenome'];
    }
    public function setSobrenomeAttribute ($value) {
        $this->attributes['sobrenome'] = $value;
    }

    // CPF
    public function getCpfAttribute() {
        $cpf = new CPF($this->attributes['cpf']);

        return $cpf->formatted();
    }
    public function setCpfAttribute($cpf) {
        $cpf = new CPF($cpf);

        $this->attributes['cpf'] = $cpf->numeric();
    }

    // RG
    public function getRgAttribute() {
        $rg = new RG($this->attributes['rg']);

        return $rg->formatted();
    }
    public function setRgAttribute($rg) {
        $rg = new RG($rg);

        $this->attributes['rg'] = $rg->numeric();
    }

    // Celular
    public function getCelular () { return new Celular($this->attributes['celular']); }
    public function getCelularAttribute() {
        $numero = new Celular($this->attributes['celular']);

        return $numero->formatted();
    }
    public function setCelularAttribute($numero) {
        $numero = new Celular($numero);

        $this->attributes['celular'] = $numero->numeric();
    }
    public function getCelularDdd () {
        return substr($this->getCelular()->numeric(), 0, 2);
    }
    public function getCelularNumero () {
        return substr($this->getCelular()->numeric(), 2);
    }

    // Telefone
    public function getTelefone () { return new Telefone($this->attributes['telefone']); }
    public function getTelefoneAttribute() {
        if (isset($this->attributes['telefone'])) {
            $numero = new Telefone($this->attributes['telefone']);
            return $numero->formatted();
        } else {
            return null;
        }

    }
    public function setTelefoneAttribute($numero) {
        $numero = new Celular($numero);

        $this->attributes['telefone'] = $numero->numeric();
    }
    public function getTelefoneDdd () {
        return substr($this->getTelefone()->numeric(), 0, 2);
    }
    public function getTelefoneNumero () {
        return substr($this->getTelefone()->numeric(), 2);
    }

    // Data de Nascimento
    public function setDataNascimentoAttribute ($value) {
        if (stripos($value, '-')) $value = date('d/m/Y', strtotime($value));
        
        // Caso a pessoa só tenha digitado um numero (celulares e pá)
        if (is_numeric($value))
            $value = substr($value, 0, 2) . '/' . substr($value, 2, 2) . '/' . substr($value, 4, 4);

        $this->attributes['datanascimento'] = Carbon::createFromFormat('d/m/Y', $value);
    }
    public function getDataNascimentoAttribute () {
        return $this->attributes['datanascimento'];
    }
    public function data_nascimento () {
        return Carbon::createFromTimestamp(strtotime($this->data_nascimento));
    }

    public function getCepAttribute () { return $this->dados_adicionais('cep'); }

    // Identidade de Gênero <3

    public function getGeneroMasculinoAttribute () { return (strtolower($this->sexo) == 'masculino'); }
    public function getGeneroFemininoAttribute () { return (strtolower($this->sexo) == 'feminino'); }
    public function getGeneroLetraAttribute () { if ($this->genero_masculino) return 'o'; if ($this->genero_feminino) return 'a'; return 'x'; }

    // Conversões RD

    public function preparar_conversao () {
        $dados_rd = $this->toArray();

        $dados_rd['datanascimento'] = $this->data_nascimento()->format('d/m/Y');

        // Cidade

        $dados_rd['cidade'] = $dados_rd['uf'] = null;

        if (!is_null($this->cidade)) {
            $dados_rd['cidade'] = $this->cidade->nome;
            $dados_rd['uf'] = $this->cidade->estado->uf;
        }

        unset($dados_rd['cidade_id']);
        unset($dados_rd['dados_adicionais']);

        $dados_rd = array_merge($dados_rd, $this->dados_adicionais);

        // Autodeclaração de Raça/Cor

        if (isset($dados_rd['raca'])) {
            $r = Autodeclaracao_Raca::where('codigo', $dados_rd['raca'])->first();
            if (!is_null($r))
                $dados_rd['raca'] = $r->raca;
        }

        // Autodeclaração de Deficiência

        if (isset($dados_rd['deficiencia_qual'])) {
            $r = Autodeclaracao_Deficiencia::where('codigo', $dados_rd['deficiencia_qual'])->first();
            if (!is_null($r))
                $dados_rd['deficiencia_qual'] = $r->deficiencia;
        }
        
        // Retornar dados

        return $dados_rd;
    }

    public function converter ($msg) {

        $dados_rd = $this->preparar_conversao();

        // Cria objeto do RD, configurações e converte o lead (amém)

        $rd = new RDStation_Univestibular ();

        $rd->converter_manual($dados_rd['email'], $msg, $dados_rd); // Conversão manual no RD
    }

    // Busca por CPF

    public static function porCPF ($cpf) {
        if (!CPF::validate($cpf))
            return null;

        $cpf = new CPF ($cpf);

        return static::where('cpf', $cpf->numeric())->first();
    }

    // Dados Adicionais

    public function getDadosAdicionaisAttribute () {
        $json = $this->attribute_get('dados_adicionais', '[]');
        return json_decode($json, true);
    }
    public function setDadosAdicionaisAttribute ($value) { $this->attribute_set('dados_adicionais', json_encode($value)); }
    public function dados_adicionais ($prop, $value = null) {
        $da = $this->dados_adicionais;

        // Caso vá sobrescrever a propriedade

        if (!is_null($value))
            $da[$prop] = $value;

        // Se não existir a propriedade

        if (!isset($da[$prop]))
            return null;

        // Salvar

        $this->dados_adicionais = $da;

        return $da[$prop];
    }

    // Dados do Responsável

    public function getDadosResponsavelAttribute () {
        if (is_null($this->dados_adicionais('responsavel_cpf')))
            return null;

        return [
            'cpf' => $this->dados_adicionais('responsavel_cpf'),
            'nome' => $this->dados_adicionais('responsavel_nome'),
            'telefone' => $this->dados_adicionais('responsavel_telefone'),
            'nascimento' => $this->dados_adicionais('responsavel_nascimento'),
        ];
    }

    // Dados do ENEM

    public function getCamposEnem ($forceDefault = false) {
        // Verificar se os campos já existem nos dados atuais (para possível retrocompatibilidade)
        if (!$forceDefault && !empty($this->enem) && isset($this->enem['__campos']))
            return $this->enem['__campos'];

        // Campos Padrão
        return [
            ['name' => 'linguagens', 'label' => 'Linguagens, Códigos e suas Tecnologias'],
            ['name' => 'natureza', 'label' => 'Ciências da Natureza e suas Tecnologias'],
            ['name' => 'humanas', 'label' => 'Ciências Humanas e suas Tecnologias'],
            ['name' => 'matematica', 'label' => 'Matemática e suas Tecnologias'],
            ['name' => 'redacao', 'label' => 'Redação'],
        ];
    }
    public function getEnemAttribute () {
        $json = $this->attribute_get('enem', '[]');
        return json_decode($json, true);
    }
    public function setEnemAttribute ($attrValue) {        
        // Salvar cópia dos campos padrão junto
        $attrValue['__campos'] = $this->getCamposEnem(true);

        // Converte os campos em float
        foreach ($attrValue as $key => $value) {
            // Ignorar a cópia dos campos padrão
            if ($key == '__campos') continue;

            // Validar se estamos lidando com string
            if (is_string($value)) {
                // Substituir vírgulas (decimais) por ponto
                $value = str_replace(',', '.', $value);

                // Converter em float
                $value = floatval($value);

                // Substituir entrada na array
                $attrValue[$key] = $value;
            }
        }

        $this->attribute_set('enem', json_encode($attrValue));
    }
}
