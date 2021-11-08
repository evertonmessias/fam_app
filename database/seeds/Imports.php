<?php

use App\Aluno;
use App\Benchmark;
use App\Curso;
use App\CPF;
use App\Campanha;
use App\Fornecedor;
use App\Grade;
use App\Lead;
use App\Lead_Status;
use App\Midia;
use App\Midia_Tipo;
use App\Prova_Local;
use App\Unidade;

use Carbon\Carbon;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Cache;

function remove_accents($string) {
    if ( !preg_match('/[\x80-\xff]/', $string) )
        return $string;

    $chars = array(
    // Decompositions for Latin-1 Supplement
    chr(195).chr(128) => 'A', chr(195).chr(129) => 'A',
    chr(195).chr(130) => 'A', chr(195).chr(131) => 'A',
    chr(195).chr(132) => 'A', chr(195).chr(133) => 'A',
    chr(195).chr(135) => 'C', chr(195).chr(136) => 'E',
    chr(195).chr(137) => 'E', chr(195).chr(138) => 'E',
    chr(195).chr(139) => 'E', chr(195).chr(140) => 'I',
    chr(195).chr(141) => 'I', chr(195).chr(142) => 'I',
    chr(195).chr(143) => 'I', chr(195).chr(145) => 'N',
    chr(195).chr(146) => 'O', chr(195).chr(147) => 'O',
    chr(195).chr(148) => 'O', chr(195).chr(149) => 'O',
    chr(195).chr(150) => 'O', chr(195).chr(153) => 'U',
    chr(195).chr(154) => 'U', chr(195).chr(155) => 'U',
    chr(195).chr(156) => 'U', chr(195).chr(157) => 'Y',
    chr(195).chr(159) => 's', chr(195).chr(160) => 'a',
    chr(195).chr(161) => 'a', chr(195).chr(162) => 'a',
    chr(195).chr(163) => 'a', chr(195).chr(164) => 'a',
    chr(195).chr(165) => 'a', chr(195).chr(167) => 'c',
    chr(195).chr(168) => 'e', chr(195).chr(169) => 'e',
    chr(195).chr(170) => 'e', chr(195).chr(171) => 'e',
    chr(195).chr(172) => 'i', chr(195).chr(173) => 'i',
    chr(195).chr(174) => 'i', chr(195).chr(175) => 'i',
    chr(195).chr(177) => 'n', chr(195).chr(178) => 'o',
    chr(195).chr(179) => 'o', chr(195).chr(180) => 'o',
    chr(195).chr(181) => 'o', chr(195).chr(182) => 'o',
    chr(195).chr(182) => 'o', chr(195).chr(185) => 'u',
    chr(195).chr(186) => 'u', chr(195).chr(187) => 'u',
    chr(195).chr(188) => 'u', chr(195).chr(189) => 'y',
    chr(195).chr(191) => 'y',
    // Decompositions for Latin Extended-A
    chr(196).chr(128) => 'A', chr(196).chr(129) => 'a',
    chr(196).chr(130) => 'A', chr(196).chr(131) => 'a',
    chr(196).chr(132) => 'A', chr(196).chr(133) => 'a',
    chr(196).chr(134) => 'C', chr(196).chr(135) => 'c',
    chr(196).chr(136) => 'C', chr(196).chr(137) => 'c',
    chr(196).chr(138) => 'C', chr(196).chr(139) => 'c',
    chr(196).chr(140) => 'C', chr(196).chr(141) => 'c',
    chr(196).chr(142) => 'D', chr(196).chr(143) => 'd',
    chr(196).chr(144) => 'D', chr(196).chr(145) => 'd',
    chr(196).chr(146) => 'E', chr(196).chr(147) => 'e',
    chr(196).chr(148) => 'E', chr(196).chr(149) => 'e',
    chr(196).chr(150) => 'E', chr(196).chr(151) => 'e',
    chr(196).chr(152) => 'E', chr(196).chr(153) => 'e',
    chr(196).chr(154) => 'E', chr(196).chr(155) => 'e',
    chr(196).chr(156) => 'G', chr(196).chr(157) => 'g',
    chr(196).chr(158) => 'G', chr(196).chr(159) => 'g',
    chr(196).chr(160) => 'G', chr(196).chr(161) => 'g',
    chr(196).chr(162) => 'G', chr(196).chr(163) => 'g',
    chr(196).chr(164) => 'H', chr(196).chr(165) => 'h',
    chr(196).chr(166) => 'H', chr(196).chr(167) => 'h',
    chr(196).chr(168) => 'I', chr(196).chr(169) => 'i',
    chr(196).chr(170) => 'I', chr(196).chr(171) => 'i',
    chr(196).chr(172) => 'I', chr(196).chr(173) => 'i',
    chr(196).chr(174) => 'I', chr(196).chr(175) => 'i',
    chr(196).chr(176) => 'I', chr(196).chr(177) => 'i',
    chr(196).chr(178) => 'IJ',chr(196).chr(179) => 'ij',
    chr(196).chr(180) => 'J', chr(196).chr(181) => 'j',
    chr(196).chr(182) => 'K', chr(196).chr(183) => 'k',
    chr(196).chr(184) => 'k', chr(196).chr(185) => 'L',
    chr(196).chr(186) => 'l', chr(196).chr(187) => 'L',
    chr(196).chr(188) => 'l', chr(196).chr(189) => 'L',
    chr(196).chr(190) => 'l', chr(196).chr(191) => 'L',
    chr(197).chr(128) => 'l', chr(197).chr(129) => 'L',
    chr(197).chr(130) => 'l', chr(197).chr(131) => 'N',
    chr(197).chr(132) => 'n', chr(197).chr(133) => 'N',
    chr(197).chr(134) => 'n', chr(197).chr(135) => 'N',
    chr(197).chr(136) => 'n', chr(197).chr(137) => 'N',
    chr(197).chr(138) => 'n', chr(197).chr(139) => 'N',
    chr(197).chr(140) => 'O', chr(197).chr(141) => 'o',
    chr(197).chr(142) => 'O', chr(197).chr(143) => 'o',
    chr(197).chr(144) => 'O', chr(197).chr(145) => 'o',
    chr(197).chr(146) => 'OE',chr(197).chr(147) => 'oe',
    chr(197).chr(148) => 'R',chr(197).chr(149) => 'r',
    chr(197).chr(150) => 'R',chr(197).chr(151) => 'r',
    chr(197).chr(152) => 'R',chr(197).chr(153) => 'r',
    chr(197).chr(154) => 'S',chr(197).chr(155) => 's',
    chr(197).chr(156) => 'S',chr(197).chr(157) => 's',
    chr(197).chr(158) => 'S',chr(197).chr(159) => 's',
    chr(197).chr(160) => 'S', chr(197).chr(161) => 's',
    chr(197).chr(162) => 'T', chr(197).chr(163) => 't',
    chr(197).chr(164) => 'T', chr(197).chr(165) => 't',
    chr(197).chr(166) => 'T', chr(197).chr(167) => 't',
    chr(197).chr(168) => 'U', chr(197).chr(169) => 'u',
    chr(197).chr(170) => 'U', chr(197).chr(171) => 'u',
    chr(197).chr(172) => 'U', chr(197).chr(173) => 'u',
    chr(197).chr(174) => 'U', chr(197).chr(175) => 'u',
    chr(197).chr(176) => 'U', chr(197).chr(177) => 'u',
    chr(197).chr(178) => 'U', chr(197).chr(179) => 'u',
    chr(197).chr(180) => 'W', chr(197).chr(181) => 'w',
    chr(197).chr(182) => 'Y', chr(197).chr(183) => 'y',
    chr(197).chr(184) => 'Y', chr(197).chr(185) => 'Z',
    chr(197).chr(186) => 'z', chr(197).chr(187) => 'Z',
    chr(197).chr(188) => 'z', chr(197).chr(189) => 'Z',
    chr(197).chr(190) => 'z', chr(197).chr(191) => 's'
    );

    $string = strtr($string, $chars);

    return $string;
}

class Imports extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Tempo de execução de 15 minutos
        $tempo_limite = 15;
        set_time_limit($tempo_limite * 60);

        // Ligar o GC
        gc_enable();

        //

        // Criar unidade
        $unidade = Unidade::create(['nome' => 'FAM - Faculdade de Americana', 'endereco' => 'Av. Joaquim Bôer', 'numero' => 733, 'bairro' => 'Jardim Luciane', 'cidade_id' => 4724, 'telefone' => '1934658100', 'coordenadas' => 'https://www.google.com.br/maps/place/FAM+-+Faculdade+de+Americana/@-22.7337238,-47.2992278,17z/data=!3m1!4b1!4m5!3m4!1s0x94c89a0137dda3e1:0x872e3bfd5d73526d!8m2!3d-22.7337238!4d-47.2970391?hl=pt-BR']);

        $local_prova = Prova_Local::create(['unidade_id' => $unidade->id]);

        /*
         * 3 Indicação
         * 5 - Rádio
         * 6 - TV
         * 8 - Outdoor
         * 10 - Redes Sociais
         * 11 - Google
         * 12 - Jornal
         * 13 - Revista
         * 4 - Outros
         */

        // Criar campanhas
        $campanha_inverno = Campanha::create(['nome' => 'Vestibular Inverno 2016', 'inicio' => '14/03/2016', 'fim' => '01/10/2016', 'budget' => 350000.00]);
        $campanha_verao = Campanha::create(['nome' => 'Vestibular Verão 2017', 'inicio' => '02/10/2016', 'fim' => '13/03/2017', 'budget' => 650000.00]);

        _ ('Importando dados...');

        // Importar dados A/C antigo

        include ('exported.php');

        _ ('Criando cursos...');

        Curso::insert($cursos);

        // Setar unidade dos cursos
        foreach(Curso::cursor() as $curso) {
            $unidade->cursos()->attach($curso);
        }

        $time_start = microtime(true);

        $count = 0;
        $total = count ($alunos);

        // Minutos de Cache
        $minutes = $tempo_limite;

        gc_collect_cycles();

        $midia_outros = Midia_Tipo::find('Outros');

        $midia_tipos = [];
        foreach (Midia_Tipo::cursor() as $midia_tipo) {
            $nome = strtolower(remove_accents($midia_tipo->nome));
            $midia_tipos[$nome] = $midia_tipo;
        }

        _ ('Importando alunos...');

        Benchmark::hook_db();

        foreach ($alunos as $k => $aluno) {

            if ($count % 500 == 1) {
                Benchmark::results ();
                Benchmark::memory_clear(); // Limpar dados de consumo de memória
            }

                Benchmark::run ('counter');

            if ($count % 100 == 1) {
                $time_now = microtime(true);
                $time_run = $time_now - $time_start;
                _ ('[' . $time_run . 's] Criados ' . $count . ' de ' . $total . ' (' . round($count / $time_run) . '/s)');
            }

            $count++;

            try {

                Benchmark::run ('init');

        	if (empty($aluno['curso']) || !CPF::validate($aluno['cpf'])) continue;

        	$lead = new Lead ();

                Benchmark::run ('curso');

            $curso = Cache::remember('curso-' . md5($aluno['curso']), $minutes, function () use ($aluno) {
                return Curso::find($aluno['curso']);
            });

            // $lead->curso()->associate(Curso::find($aluno['curso']));
        	$lead->curso()->associate($curso);

                Benchmark::run ('cadastro');

        	switch (strtolower($aluno['tipo_cadastro'])) {
        		case 'm': $lead->status_id = 'MATR'; break;
        		case 'i': $lead->status_id = 'INSC'; break;
        		default: $lead->status_id = 'LEAD'; break;
        	}

                Benchmark::run ('variação');

            // variação aleatória
            if ($count % 7 == 1 && $lead->status_id == 'INSC') {
                $lead->status_id = 'MATR';
                $reg_matr = date('Y-m-d H:i:s');
            }

                Benchmark::run ('pre-sets');

            $reg_lead = (isset($aluno['reg_lead']) ? $aluno['reg_lead'] : null);
            $reg_insc = (isset($aluno['reg_inscricao']) ? $aluno['reg_inscricao'] : null);
            $reg_matr = (isset($aluno['reg_matricula']) ? $aluno['reg_matricula'] : null);
            $conheceu = (isset($aluno['comoconheceu']) ? $aluno['comoconheceu'] : null);

                Benchmark::run ('unsets');

        	unset($aluno['tipo_cadastro']);
        	unset($aluno['comoconheceu']);
        	unset($aluno['curso']);
            unset($aluno['id']);
            unset($aluno['reg_lead']);
            unset($aluno['reg_inscricao']);
        	unset($aluno['reg_matricula']);

                Benchmark::run ('nascimento');

            // Data de nascimento
            $aluno['datanascimento'] = array_reverse(explode('-', $aluno['datanascimento']));
            $aluno['datanascimento'] = implode('/', $aluno['datanascimento']);

                Benchmark::run ('create_aluno');

        	$aluno = Aluno::create($aluno);

            $t_c = strtotime($aluno['updated_at']);
            $t_d = strtotime('2016-10-02');

                Benchmark::run ('campanha');

            if ($t_c >= $t_d)
                $campanha = $campanha_verao;
            else
                $campanha = $campanha_inverno;

                Benchmark::run ('associate');

        	$lead->aluno()->associate($aluno);
            $lead->campanha()->associate($campanha);
            $lead->created_at = strtotime($aluno['created_at']);
            $lead->updated_at = strtotime($aluno['updated_at']);

                Benchmark::run ('midia');

            // Associar à mídia

            if (!is_null($conheceu)) {
                $conheceu = strtolower(remove_accents($conheceu));

                if (isset($midia_tipos[$conheceu]))
                    $midia = $midia_tipos[$conheceu];
                else
                    $midia = $midia_outros;

                $lead->midia()->associate($midia);
            }

                Benchmark::run ('lead-save');

                $lead->save(['timestamps' => false]);

                Benchmark::run ('convert');

            // Criar conversões

            // Lead
            if (!is_null($reg_lead))
                $lead->converter ('LEAD', 'Importação no Sistema', '', 'LEAD', Carbon::createFromTimestamp(strtotime($reg_lead)));

            // Inscrição
            if (!is_null($reg_insc))
                $lead->converter ('INSC', 'Conversão para Inscrito', '', 'LEAD', Carbon::createFromTimestamp(strtotime($reg_insc)));

            // Matrícula
            if (!is_null($reg_matr))
                $lead->converter ('MATR', 'Conversão para Matrícula', '', 'INSC', Carbon::createFromTimestamp(strtotime($reg_matr)));

                Benchmark::finish ();

            } catch (Exception $e) { _ ('! ' . $e->getMessage()); }
        }

        _ ('Concluído!');

        Benchmark::results ();
    }
}
