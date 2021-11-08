<?php

namespace App;

use Carbon\Carbon;
use App\System\Event;

class Lead extends Model
{
    //
    protected $fillable = ['campanha', 'aluno', 'midia', 'curso', 'prova', 'status', 'dados_adicionais'];

    public function aluno () {
        return $this->belongsTo('App\Aluno');
    }

    public function campanha () {
        return $this->belongsTo('App\Campanha');
    }

    public function midia () {
        return $this->belongsTo('App\Midia_Tipo', 'midia_id');
    }

    public function curso () {
        return $this->belongsTo('App\Curso');
    }

    public function status () {
        return $this->belongsTo('App\Lead_Status', 'status_id');
    }

    public function prova () {
        return $this->belongsTo('App\Prova');
    }

    public function historico () {
        return $this->hasMany('App\Lead_History')->orderBy('at', 'DESC')->orderBy('id', 'DESC');
        // return $this->hasMany('App\Lead_History')->remember(2);
    }

    public function getCursoPrimeiraOpcaoAttribute () {
        return Curso::find($this->opcao_curso_1);
    }
    public function getCursoSegundaOpcaoAttribute () {
        return Curso::find($this->opcao_curso_2);
    }
    public function getCursoTerceiraOpcaoAttribute () {
        return Curso::find($this->opcao_curso_3);
    }

    // Validações Rápidas

    public function isInscrito () {
        return ($this->status->base_id > 0);
    }

    public function isMatriculado ($curso = null) {
        $status = $this->status;
        $status_matricula = ($status->base_id == 4 || $status->base_id == 6);

        // Se não tiver id de curso, retornar status
        if (is_null($curso))
            return $status_matricula;

        // Validar se é objeto, pegar id
        if (is_object($curso))
            $curso = $curso->id;

        // Caso contrário, retornar comparação
        return ($status_matricula && $this->curso_id == $curso);
    }

    // Permitir recadastrar data de prova?

    public function getPermitirMudancaDataAttribute () {
        switch ($this->status_id) {
            case 'LEAD':
            case 'INSC':
            case 'REPROVADO':
            case 'AUSENTE':
                return true;
                break;
            default:
                return false;
                break;
        }
    }

    // Listar leads por curso
    
    public function scopePorCurso ($query, $curso) {
        return $query->where('curso_id', $curso);
    }
    public function scopeGetPorCurso($query, $curso) {
        return $this->scopeOrdered($query)->get();
    }

    // Listar leads por aluno
    
    public function scopePorAluno ($query, $aluno) {
        return $query->where('aluno_id', $aluno);
    }
    public function scopeGetPorAluno($query, $aluno) {
        return $this->scopeOrdered($query)->get();
    }

    // Listar leads por data
    
    public function scopePorData ($query, $data) {
        return $query->where('updated_at', $data);
    }
    public function scopeGetPorData($query, $data) {
        return $this->scopeOrdered($query)->get();
    }

    public function criar_historico ($titulo = '', $descricao = '', $novo_status = null, $antigo_status = null, $at = null) {
        $history = new Lead_History ();

        // Horário do lead

        if (is_null($at))
            $at = Carbon::now();

        // Status novo

        if (is_null($novo_status))
            $novo_status = $this->status;

        if (is_object($novo_status))
            $novo_status = $novo_status->codigo;

        // Status anterior

        $status_antigo = is_null($this->status) ? 'LEAD' : $this->status->codigo;
        if (!is_null($antigo_status))
            $status_antigo = $antigo_status;

        // Criar histórico

        $history->at = $at;
        $history->lead()->associate($this);
        $history->campanha()->associate($this->campanha);
        $history->title = $titulo;
        $history->description = $descricao;
        $history->status_was = $status_antigo;
        $history->status_new = $novo_status;
        $history->save();

        return $history;
    }

    public function before_create () {
        if (!$this->attribute_exists('dados_adicionais'))
            $this->dados_adicionais = [];
    }

    public function converter ($status, $title = '', $descricao = '', $de = null, $at = null) {
        $event = Event::register('lead', 'Conversão de Lead');
        $event->meta('lead', $this->id);
        $event->meta('status', $status);

        return $event->run(function ($event) use ($status, $title, $descricao, $de, $at) {
            $historico = $this->criar_historico ($title, $descricao, $status, $de, $at);
            $this->status()->associate($historico->status_new);
            $this->save();

            // Converte no RD Station
            $this->converter_rd($historico->status_new);

            return $historico;
        });
    }

    public function converter_rd ($status = null) {
        /////////////////////////////////////////////////////////////////////////////
        // Atualizar RD Station

        $dados_rd = $this->aluno->preparar_conversao();

        // Converter relacionamentos em texto

        $dados_rd['curso'] = isset($this->curso) ? $this->curso->nome : '';

        // Combinar dados adicionais
        $dados_rd = array_merge($dados_rd, $this->dados_adicionais);

        // Se tiver mídia definida (Como nos Conheceu)

        if (!is_null($this->midia)) {
            // $dados_rd['midia'] = $this->midia->toArray();
            $dados_rd['como_conheceu'] = $this->midia->nome;
        }

        // Se tiver prova definida

        if (!is_null($this->prova)) {
            $campanha = $this->campanha;
            $data = $this->prova->data;
            $local = $this->prova->local;

            $dados_rd['campanha'] = $campanha->nome;
            $dados_rd['campanha_id'] = $campanha->id;
            $dados_rd['prova_data'] = $data->hora()->format('d/m/Y');
            $dados_rd['prova_hora'] = $data->hora()->format('H:i');
            $dados_rd['prova_local'] = $local->local;
            $dados_rd['prova_local_endereco'] = $local->endereco;
        }

        // Cria objeto do RD, configurações e converte o lead (amém)

        if (is_null($status))
            $status = $this->status;

        $rd = new RDStation_Univestibular ();
        
        if (!env('APP_DEBUG') && isset($this->aluno->email))
            $rd->converter($dados_rd['email'], $status, $dados_rd); // Converte o Lead

        /////////////////////////////////////////////////////////////////////////////
    }

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

    public function export_listizer () {
        $lead = $this;

        $aluno = $lead->global_relation('aluno');
        $curso = $lead->global_relation('curso');
        $status = $lead->global_relation('status');
        $midia = $lead->global_relation('midia');
        $prova = $lead->global_relation('prova');
        $cidade = $aluno->global_relation('cidade');
    
        $historico = $lead->global_relation('historico');
    
        if (is_null($aluno))
            return null;
    
        /**
         * Essa linha transforma a base do CRM em um dos status 'i' para inscrito ou 'l' para lead,
         * não usei um if para manter compatibilidade, caso esteja como matriculado, etc (que o lliure não suportava)
         */
    
        $status_listizer = ($status->base_id) ? 'i' : 'l';
    
        // Aqui criamos o objeto
    
        $obj = [
            'nome' => $aluno->nome,
            'sobrenome' => $aluno->sobrenome,
            'email' => $aluno->email,
            'status' => $status->codigo,
            'base' => $status->base_id,
            'cel' => $aluno->celular,
            'tel' => $aluno->telefone,
            'cpf' => $aluno->cpf,
            'rg' => $aluno->rg,
            'datanascimento' => $aluno->data_nascimento()->format('Y-m-d'),
            'cep' => $aluno->cep,
            'endereco' => $aluno->endereco,
            'numero' => $aluno->numero,
            'complemento' => $aluno->complemento,
            'raca' => $aluno->dados_adicionais('raca'),
            'bairro' => $aluno->bairro,
            'cidade' => null,
            'uf' => null,
            'ondecursou' => $aluno->dados_adicionais('onde_cursou'),
            'anoconclusao' => $aluno->dados_adicionais('ano_conclusao'),
            'comoconheceu' => null,
            'sexo' => $aluno->sexo,
            'deficiencia' => $aluno->dados_adicionais('deficiencia'),
            'qualdeficiencia' => $aluno->dados_adicionais('deficiencia_qual'),
            'cond' => $aluno->dados_adicionais('deficiencia_condicoes'),
            'tipoescola' => $aluno->dados_adicionais('ensino_medio'),
            'usarenem' => $lead->dados_adicionais('enem'),
            'ajudafies' => $lead->dados_adicionais('fies'),
            'fezaprova' => null,
            'diadaprova' => null,
            'horaprova' => null,
            'datacadastro' => is_object($historico->first()) ? $historico->first()->at->format('Y-m-d H:i:s') : $historico->first(),
            'data_atualizacao' => is_object($historico->last()) ? $historico->last()->at->format('Y-m-d H:i:s') : $historico->last(),
            'curso' => is_null($curso) ? null : $curso->codigo,
            'curso_nome' => is_null($curso) ? null : $curso->nome,
            'tipo_cadastro' => $status_listizer,
            'ingresso' => $aluno->ingresso,
            'distancia' => $aluno->distancia,
            'nomepai' => $aluno->dados_adicionais('nome_pai'),
            'nomemae' => $aluno->dados_adicionais('nome_mae'),
            'receberinfos' => $lead->dados_adicionais('newsletter'),
        ];
    
        // Aqui se tiver setado cidade, colocamos ela...
    
        if (!is_null($cidade)) {
            $obj['cidade'] = $aluno->global_relation('cidade')->nome;
            $obj['uf'] = $aluno->global_relation('cidade')->global_relation('estado')->uf;
        }
    
        // ... e também como conheceu...
    
        if (!is_null($midia))
            $obj['comoconheceu'] = $midia->nome;
    
        // ... e a prova...
    
        if (!is_null($prova)) {
            $obj['fezaprova'] = $prova->participou;
            $obj['diadaprova'] = $prova->global_relation('data')->hora()->format('Y-m-d');
            $obj['horaprova'] = $prova->global_relation('data')->hora()->format('H:i:s');
        }
    
        // ... aí sim retornamos tudo
    
        return $obj;
    }

    public function export_zipzop () {
        $lead = $this;

        $aluno = $lead->global_relation('aluno');
        $campanha = $lead->global_relation('campanha');
    
        if (is_null($aluno) || is_null($campanha))
            return null;
    
        // Aqui criamos o objeto
    
        $obj = [
            'First Name' => $aluno->nome,
            'Middle Name' => $campanha->nome,
            'Last Name' => '', 'Title' => '', 'Suffix' => '', 'Initials' => '', 'Web Page' => '', 'Gender' => '', 'Birthday' => '', 'Anniversary' => '', 'Location' => '', 'Language' => '', 'Internet Free Busy' => '', 'Notes' => '',
            'E-mail Address' => $aluno->email,
            'E-mail 2 Address' => '', 'E-mail 3 Address' => '', 'Primary Phone' => '', 'Home Phone' => '', 'Home Phone 2' => '',
            'Mobile Phone' => $aluno->getCelular()->numeric(),
            'Pager' => '', 'Home Fax' => '', 'Home Address' => '', 'Home Street' => '', 'Home Street 2' => '', 'Home Street 3' => '', 'Home Address PO Box' => '', 'Home City' => '', 'Home State' => '', 'Home Postal Code' => '', 'Home Country' => '', 'Spouse' => '', 'Children' => '', 'Manager\'s Name' => '', 'Assistant\'s Name' => '', 'Referred By' => '', 'Company Main Phone' => '', 'Business Phone' => '', 'Business Phone 2' => '', 'Business Fax' => '', 'Assistant\'s Phone' => '', 'Company' => '', 'Job Title' => '', 'Department' => '', 'Office Location' => '', 'Organizational ID Number' => '', 'Profession' => '', 'Account' => '', 'Business Address' => '', 'Business Street' => '', 'Business Street 2' => '', 'Business Street 3' => '', 'Business Address PO Box' => '', 'Business City' => '', 'Business State' => '', 'Business Postal Code' => '', 'Business Country' => '', 'Other Phone' => '', 'Other Fax' => '', 'Other Address' => '', 'Other Street' => '', 'Other Street 2' => '', 'Other Street 3' => '', 'Other Address PO Box' => '', 'Other City' => '', 'Other State' => '', 'Other Postal Code' => '', 'Other Country' => '', 'Callback' => '', 'Car Phone' => '', 'ISDN' => '', 'Radio Phone' => '', 'TTY/TDD Phone' => '', 'Telex' => '', 'User 1' => '', 'User 2' => '', 'User 3' => '', 'User 4' => '', 'Keywords' => '', 'Mileage' => '', 'Hobby' => '', 'Billing Information' => '', 'Directory Server' => '', 'Sensitivity' => '', 'Priority' => '', 'Private' => '', 'Categories' => ''
        ];

        // Tá saindo da jaula o monstro, BIRL!!!
    
        return $obj;
    }
}