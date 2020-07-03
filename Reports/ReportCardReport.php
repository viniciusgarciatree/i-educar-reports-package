<?php

use iEducar\Reports\JsonDataSource;

require_once 'lib/Portabilis/Report/ReportCore.php';
require_once 'Reports/Tipos/TipoBoletim.php';
require_once 'App/Model/IedFinder.php';

class ReportCardReport extends Portabilis_Report_ReportCore
{
    use JsonDataSource;

    /**
     * @return string
     *
     * @throws App_Model_Exception
     */
    public function templateName()
    {
        $flagTipoBoletimTurma = App_Model_IedFinder::getTurma($codTurma = $this->args['turma']);

        if (
            $flagTipoBoletimTurma['tipo_boletim_diferenciado']
            && $flagTipoBoletimTurma['tipo_boletim_diferenciado'] != $flagTipoBoletimTurma['tipo_boletim']
            && $codMatricula = $this->args['matricula']
        ) {
            $matricula = App_Model_IedFinder::getMatricula($codMatricula);
            $possuiDeficiencia = App_Model_IedFinder::verificaSePossuiDeficiencia($matricula['ref_cod_aluno']);

            $flagTipoBoletimTurma = $flagTipoBoletimTurma['tipo_boletim' . ($possuiDeficiencia ? '_diferenciado' : '')];
        } elseif (
            $flagTipoBoletimTurma['tipo_boletim_diferenciado']
            && $flagTipoBoletimTurma['tipo_boletim_diferenciado'] != $flagTipoBoletimTurma['tipo_boletim']
            && $this->args['alunos_diferenciados'] == 2
        ) {
            $flagTipoBoletimTurma = $flagTipoBoletimTurma['tipo_boletim_diferenciado'];
        } else {
            $flagTipoBoletimTurma = $flagTipoBoletimTurma['tipo_boletim'];
        }

        if($this->args['modelo'] == 1){
            $template = 'report-card';
            if (empty($flagTipoBoletimTurma)) {
                throw new Exception('Não foi definido o tipo de boletim no cadastro de turmas.');
            }
    
            $templates = Portabilis_Model_Report_TipoBoletim::getInstance()->getReports();
            $template = !empty($templates[$flagTipoBoletimTurma]) ? $templates[$flagTipoBoletimTurma] : $template;

        }elseif($this->args['modelo'] == 2){
            $template = 'report-card-boletim';
        }

        if (empty($template)) {
            throw new Exception('Não foi possivel recuperar nome do template para o boletim.');
        }

        return $template;
    }

    public function getJsonData()
    {
        if($this->templateName() == "report-card-boletim"){            
            $queryMainReport = $this->getSqlMainReport();        
            $dados   = Portabilis_Utils_Database::fetchPreparedQuery($queryMainReport);
            $queryHeaderReport = $this->getSqlHeaderReport();

            $arrNota = [];
            $arrMain = [];
            $arrAreaConhecimento = [];
            $arrDisciplina = [];
            $areaConhecimento = "";
            $indexMatricula = -1;
            $arrIndexMatricula = [];
            $arrMatricula = [];

            //dd($dados);

            foreach ($dados as $key => $value) {
                $id_area_diciplina = $value['area_conhecimento_id'] . ' - ' . $value['nome_disciplina_id'];

                $arrAreaConhecimento[$value['area_conhecimento_id']] = $value['area_conhecimento'];
                $arrDisciplina[$value['area_conhecimento_id']][$value['nome_disciplina_id']]=$value['nome_disciplina'];

                $areaConhecimento = $value['area_conhecimento_id'];

                $indexMatricula  = isset($arrIndexMatricula[$value['matricula']]) ? $indexMatricula : $indexMatricula + 1;
                $arrIndexMatricula[$value['matricula']] = $indexMatricula;

                $arrMatricula[$value['matricula']] = $value;
                /*[
                    'matricula' => $value['matricula'],
                    'nome_curso' => $value['nome_curso'],
                    'periodo' => $value['periodo'],
                    'etapa_ensino_descricao' => $value['etapa_ensino_descricao'],
                    'ano' => $value['ano'],
                    'nome_turma' => $value['nome_turma'],
                    'professor' => $value['professor'],
                    'nome_aluno' => $value['nome_aluno'],
                    'dt_nasc' => $value['dt_nasc'],
                    'situacaosituacao' => $value['situacao'],
                    'observacoes' => $value['observacoes'],
                    'data_area' => "",
                ];*/

                $arrNota[$value['matricula']][$value['area_conhecimento_id']][$value['nome_disciplina_id']] = [
                    'nome_disciplina_id' => $value['nome_disciplina_id'],
                    'nome_disciplina' => $value['nome_disciplina'],
                    'nota1num' => $value['nota1num'],
                    'nota1'    => $value['nota1'],
                    'nota2num' => $value['nota2num'],
                    'nota2'    => $value['nota2'],
                    'nota3num' => $value['nota3num'],
                    'nota3'    => $value['nota3'],
                    'nota4num' => $value['nota4num'],
                    'nota4'    => $value['nota4'],
                    'nota_exame exame' => $value['nota_exame exame'],
                    'matricula' => $value['matricula'],
                    'frequencia' => $value['frequencia'],
                    'nota1numturma' => $value['nota1numturma'],
                    'nota2numturma' => $value['nota2numturma'],
                    'nota3numturma' => $value['nota3numturma'],
                    'nota4numturma' => $value['nota4numturma'],
                    'total_faltas_et1' => $value['total_faltas_et1'],
                    'total_faltas_et2' => $value['total_faltas_et2'],
                    'total_faltas_et3' => $value['total_faltas_et3'],
                    'total_faltas_et4' => $value['total_faltas_et4'],
                    'faltas_componente_et1' => $value['faltas_componente_et1'],
                    'faltas_componente_et2' => $value['faltas_componente_et2'],
                    'faltas_componente_et3' => $value['faltas_componente_et3'],
                    'faltas_componente_et4' => $value['faltas_componente_et4'],
                    'total_geral_faltas_componente' => $value['total_geral_faltas_componente'],
                    'total_faltas' => $value['total_faltas'],
                    'curso_hora_falta' => $value['curso_hora_falta'],
                    'carga_horaria_componente' => $value['carga_horaria_componente'],
                    'carga_horaria_serie' => $value['carga_horaria_serie'],
                    'media' => $value['media'],
                    'medianum' => $value['medianum'],
                    'nota_exame' => $value['nota_exame'],
                    'media_recuperacao' => $value['media_recuperacao'],
                    'medianumturma' => $value['medianumturma'],
                    'total_faltas_component' => $value['total_faltas_component']
                ];
                
            }

            foreach ($arrMatricula as $key => $value) {
                $arrArea = [];
                foreach ($arrNota[$key] as $keyArea => $valArea) {
                    $arrArea[] = [
                      'id' => $keyArea,
                      'area' => $arrAreaConhecimento[$keyArea],
                      'data_disciplina' => array_values($valArea)
                    ];
                }
                $value['data_area'] = $arrArea;
                $arrMain[] = $value;
            }
            //dd($arrMain);

            unset($this->args['modelo']);
            return array_merge([
                'main' => $arrMain,
                'header' => Portabilis_Utils_Database::fetchPreparedQuery($queryHeaderReport),
            ]);
        }else{
            unset($this->args['modelo']);
            $queryMainReport = $this->getSqlMainReport();
            $queryHeaderReport = $this->getSqlHeaderReport();
            return array_merge([
                'main' => Portabilis_Utils_Database::fetchPreparedQuery($queryMainReport),
                'header' => Portabilis_Utils_Database::fetchPreparedQuery($queryHeaderReport),
            ]);
        }
    }

    /**
     * @inheritdoc
     */
    public function requiredArgs()
    {
        $this->addRequiredArg('ano');
        $this->addRequiredArg('instituicao');
        $this->addRequiredArg('escola');
        $this->addRequiredArg('curso');
        $this->addRequiredArg('serie');
        $this->addRequiredArg('turma');
    }

    /**
     * Retorna o SQL para buscar os dados do relatório principal.
     *
     * @return string
     *
     * @throws Exception
     */
    public function getSqlMainReport()
    {
        $instituicao = $this->args['instituicao'] ?: 0;
        $escola = $this->args['escola'] ?: 0;
        $curso = $this->args['curso'] ?: 0;
        $serie = $this->args['serie'] ?: 0;
        $turma = $this->args['turma'] ?: 0;
        $ano = $this->args['ano'] ?: 0;
        $situacao_matricula = $this->args['situacao_matricula'] ?: 0;
        $alunos_diferenciados = $this->args['alunos_diferenciados'] ?: 0;
        $matricula = $this->args['matricula'] ?: 0;

        return "SELECT fcn_upper(instituicao.nm_instituicao) AS nome_instituicao,
          fcn_upper(instituicao.nm_responsavel) AS nome_responsavel,
          relatorio.get_nome_escola(escola.cod_escola) AS nm_escola,
          escola_ano_letivo.ano AS ano,
          view_dados_escola.logradouro AS logradouro,
          view_dados_escola.telefone AS fone,
          view_dados_escola.email AS email,
          curso.nm_curso AS nome_curso,
          serie.nm_serie AS nome_serie,
          turma.nm_turma AS nome_turma,
          public.fcn_upper(pessoa.nome) AS nome_aluno,
          turma_turno.nome AS periodo,
          view_situacao.texto_situacao AS situacao,
          area_conhecimento.id as area_conhecimento_id,
          area_conhecimento.nome as area_conhecimento,
          view_componente_curricular.id AS nome_disciplina_id,
          view_componente_curricular.nome AS nome_disciplina,
          TRUNC(nota_etapa1.nota::NUMERIC, 1) AS nota1num,
          nota_etapa1.nota_arredondada AS nota1,
          TRUNC(nota_etapa2.nota::NUMERIC, 1) AS nota2num,
          nota_etapa2.nota_arredondada AS nota2,
          TRUNC(nota_etapa3.nota::NUMERIC, 1) AS nota3num,
          nota_etapa3.nota_arredondada AS nota3,
          TRUNC(nota_etapa4.nota::NUMERIC, 1) AS nota4num,
          nota_etapa4.nota_arredondada AS nota4,
          TRUNC(nota_exame.nota::NUMERIC, 1) AS exame,
          matricula.cod_matricula AS matricula,
          modules.frequencia_da_matricula(matricula.cod_matricula) AS frequencia,
          fisica.data_nasc AS dt_nasc,
          relatorio.get_media_turma(turma.cod_turma, view_componente_curricular.id, 1) AS nota1numturma,
          relatorio.get_media_turma(turma.cod_turma, view_componente_curricular.id, 2) AS nota2numturma,
          relatorio.get_media_turma(turma.cod_turma, view_componente_curricular.id, 3) AS nota3numturma,
          relatorio.get_media_turma(turma.cod_turma, view_componente_curricular.id, 4) AS nota4numturma,
          falta_etapa1.quantidade AS total_faltas_et1,
          falta_etapa2.quantidade AS total_faltas_et2,
          falta_etapa3.quantidade AS total_faltas_et3,
          falta_etapa4.quantidade AS total_faltas_et4,
          falta_componente1.quantidade AS faltas_componente_et1,
          falta_componente2.quantidade AS faltas_componente_et2,
          falta_componente3.quantidade AS faltas_componente_et3,
          falta_componente4.quantidade AS faltas_componente_et4,
          relatorio.get_total_geral_falta_componente(matricula.cod_matricula) AS total_geral_faltas_componente,
          relatorio.get_total_faltas(matricula.cod_matricula) AS total_faltas,
          curso.hora_falta * 100 AS curso_hora_falta,
          componente_curricular_ano_escolar.carga_horaria::int AS carga_horaria_componente,
          serie.carga_horaria AS carga_horaria_serie,
          nota_componente_curricular_media.media_arredondada AS media,
          TRUNC(nota_componente_curricular_media.media::NUMERIC, 1) AS medianum,
          TRUNC(nota_exame.nota_arredondada::NUMERIC, 1) AS nota_exame,
          TRUNC(coalesce(regra_avaliacao.media, 0.00), 1) AS media_recuperacao,
          relatorio.get_media_geral_turma(turma.cod_turma, view_componente_curricular.id) AS medianumturma,
          relatorio.get_total_falta_componente(matricula.cod_matricula, view_componente_curricular.id) AS total_faltas_componente,
          (SELECT string_agg(DISTINCT pessoa.nome, ', ') FROM  pmieducar.turma
            INNER JOIN modules.professor_turma ON TRUE 
                AND professor_turma.turma_id = turma.cod_turma
                AND professor_turma.funcao_exercida IN(1)
            INNER JOIN pmieducar.servidor ON TRUE 
                AND servidor.cod_servidor = professor_turma.servidor_id
                AND servidor.ativo = 1  
            INNER JOIN cadastro.pessoa ON TRUE 
                AND pessoa.idpes = servidor.cod_servidor
            INNER JOIN cadastro.fisica ON TRUE 
                AND fisica.idpes = servidor.cod_servidor
            LEFT JOIN pmieducar.servidor as educacenso_cod_docente ON TRUE 
                AND educacenso_cod_docente.cod_servidor = servidor.cod_servidor
            LEFT JOIN cadastro.escolaridade ON TRUE 
                AND escolaridade.idesco = servidor.ref_idesco
            INNER JOIN relatorio.view_componente_curricular ON TRUE 
                AND view_componente_curricular.cod_turma = turma.cod_turma
            INNER JOIN modules.professor_turma_disciplina ON TRUE  AND professor_turma_disciplina.professor_turma_id = professor_turma.id AND professor_turma_disciplina.componente_curricular_id = view_componente_curricular.id
            WHERE turma.cod_turma = {$turma} and professor_turma.ano = {$ano}
            group by turma.cod_turma) as professor,
    CASE WHEN UPPER(substring(serie.nm_serie,1,5)) = 'MULTI'  THEN 
          (CASE WHEN etapa_ensino.descricao = '' or etapa_ensino.descricao is null  THEN 'ETAPA DO ALUNO NÃO INFORMADA' ELSE substring(etapa_ensino.descricao,6,length(etapa_ensino.descricao)) END )   
        ELSE serie.nm_serie END as etapa_ensino_descricao
   FROM pmieducar.instituicao
   INNER JOIN pmieducar.escola ON (escola.ref_cod_instituicao = instituicao.cod_instituicao)
   INNER JOIN pmieducar.escola_ano_letivo ON (escola_ano_letivo.ref_cod_escola = escola.cod_escola)
   INNER JOIN relatorio.view_dados_escola ON (view_dados_escola.cod_escola = escola.cod_escola)
   INNER JOIN pmieducar.escola_curso ON (escola_curso.ativo = 1
                                         AND escola_curso.ref_cod_escola = escola.cod_escola)
   INNER JOIN pmieducar.curso ON (curso.cod_curso = escola_curso.ref_cod_curso
                                  AND curso.ativo = 1)
   INNER JOIN pmieducar.escola_serie ON (escola_serie.ativo = 1
                                         AND escola_serie.ref_cod_escola = escola.cod_escola)
   INNER JOIN pmieducar.serie ON (serie.cod_serie = escola_serie.ref_cod_serie
                                  AND serie.ativo = 1)
   INNER JOIN pmieducar.turma ON (turma.ref_ref_cod_escola = escola.cod_escola
                                  AND turma.ref_cod_curso = escola_curso.ref_cod_curso
                                  AND (
                                           turma.ref_ref_cod_serie = escola_serie.ref_cod_serie OR
                                           turma.ref_ref_cod_serie_mult = escola_serie.ref_cod_serie
                                       )
                                  AND turma.ativo = 1)
   INNER JOIN relatorio.view_componente_curricular ON (view_componente_curricular.cod_turma = turma.cod_turma)
   INNER JOIN pmieducar.matricula_turma ON (matricula_turma.ref_cod_turma = turma.cod_turma)
   INNER JOIN pmieducar.matricula ON (matricula.cod_matricula = matricula_turma.ref_cod_matricula
                                      AND matricula.ref_ref_cod_escola = escola.cod_escola
                                      AND matricula.ref_cod_curso = curso.cod_curso
                                      AND matricula.ref_ref_cod_serie = serie.cod_serie
                                      AND matricula.ano = turma.ano
                                      AND matricula.ativo = 1)
   INNER JOIN relatorio.view_situacao ON (view_situacao.cod_matricula = matricula.cod_matricula
                                          AND view_situacao.cod_turma = turma.cod_turma
                                          AND matricula_turma.sequencial = view_situacao.sequencial)
   LEFT JOIN pmieducar.turma_turno ON (turma_turno.id = turma.turma_turno_id
                                       AND turma.cod_turma = matricula_turma.ref_cod_turma)
   INNER JOIN pmieducar.aluno ON (matricula.ref_cod_aluno = aluno.cod_aluno)
   INNER JOIN cadastro.pessoa ON (pessoa.idpes = aluno.ref_idpes)
   INNER JOIN cadastro.fisica ON (fisica.idpes = aluno.ref_idpes)
   LEFT JOIN modules.parecer_aluno parecer ON (parecer.matricula_id = matricula.cod_matricula)
   LEFT JOIN modules.nota_aluno ON (nota_aluno.matricula_id = matricula.cod_matricula)
   LEFT JOIN modules.nota_componente_curricular nota_etapa1 ON (nota_etapa1.nota_aluno_id = nota_aluno.id
                                                                AND nota_etapa1.componente_curricular_id = view_componente_curricular.id
                                                                AND nota_etapa1.etapa = '1')
   LEFT JOIN modules.parecer_componente_curricular parecer_componente_etapa1 ON (nota_etapa1.componente_curricular_id = parecer_componente_etapa1.componente_curricular_id
                                                                AND parecer.id = parecer_componente_etapa1.parecer_aluno_id
                                                                AND parecer_componente_etapa1.etapa = '1')
   LEFT JOIN modules.nota_componente_curricular nota_etapa2 ON (nota_etapa2.nota_aluno_id = nota_aluno.id
                                                                AND nota_etapa2.componente_curricular_id = view_componente_curricular.id
                                                                AND nota_etapa2.etapa = '2')
   LEFT JOIN modules.parecer_componente_curricular parecer_componente_etapa2 ON (nota_etapa2.componente_curricular_id = parecer_componente_etapa2.componente_curricular_id
                                                                AND parecer.id = parecer_componente_etapa2.parecer_aluno_id
                                                                AND parecer_componente_etapa2.etapa = '2')
   LEFT JOIN modules.nota_componente_curricular nota_etapa3 ON (nota_etapa3.nota_aluno_id = nota_aluno.id
                                                                AND nota_etapa3.componente_curricular_id = view_componente_curricular.id
                                                                AND nota_etapa3.etapa = '3')
   LEFT JOIN modules.parecer_componente_curricular parecer_componente_etapa3 ON (nota_etapa3.componente_curricular_id = parecer_componente_etapa3.componente_curricular_id
                                                                AND parecer.id = parecer_componente_etapa3.parecer_aluno_id
                                                                AND parecer_componente_etapa3.etapa = '3')                                                             
   LEFT JOIN modules.nota_componente_curricular nota_etapa4 ON (nota_etapa4.nota_aluno_id = nota_aluno.id
                                                                AND nota_etapa4.componente_curricular_id = view_componente_curricular.id
                                                                AND nota_etapa4.etapa = '4')
   LEFT JOIN modules.parecer_componente_curricular parecer_componente_etapa4 ON (nota_etapa4.componente_curricular_id = parecer_componente_etapa4.componente_curricular_id
                                                                AND parecer.id = parecer_componente_etapa4.parecer_aluno_id
                                                                AND parecer_componente_etapa4.etapa = '4')                                                                                                                          
   LEFT JOIN modules.nota_componente_curricular nota_exame ON (nota_exame.nota_aluno_id = nota_aluno.id
                                                               AND nota_exame.componente_curricular_id = view_componente_curricular.id
                                                               AND nota_exame.etapa = 'Rc')
   LEFT JOIN modules.nota_componente_curricular_media ON (nota_componente_curricular_media.nota_aluno_id = nota_aluno.id
                                                          AND nota_componente_curricular_media.componente_curricular_id = view_componente_curricular.id)
   LEFT JOIN modules.falta_aluno ON (falta_aluno.matricula_id = matricula.cod_matricula)
   LEFT JOIN modules.falta_geral falta_etapa1 ON (falta_etapa1.falta_aluno_id = falta_aluno.id
                                                  AND falta_etapa1.etapa = '1')
   LEFT JOIN modules.falta_geral falta_etapa2 ON (falta_etapa2.falta_aluno_id = falta_aluno.id
                                                  AND falta_etapa2.etapa = '2')
   LEFT JOIN modules.falta_geral falta_etapa3 ON (falta_etapa3.falta_aluno_id = falta_aluno.id
                                                  AND falta_etapa3.etapa = '3')
   LEFT JOIN modules.falta_geral falta_etapa4 ON (falta_etapa4.falta_aluno_id = falta_aluno.id
                                                  AND falta_etapa4.etapa = '4')
   LEFT JOIN modules.falta_componente_curricular falta_componente1 ON (falta_componente1.falta_aluno_id = falta_aluno.id
                                                                       AND falta_componente1.componente_curricular_id = view_componente_curricular.id
                                                                       AND falta_componente1.etapa = '1')
   LEFT JOIN modules.falta_componente_curricular falta_componente2 ON (falta_componente2.falta_aluno_id = falta_aluno.id
                                                                       AND falta_componente2.componente_curricular_id = view_componente_curricular.id
                                                                       AND falta_componente2.etapa = '2')
   LEFT JOIN modules.falta_componente_curricular falta_componente3 ON (falta_componente3.falta_aluno_id = falta_aluno.id
                                                                       AND falta_componente3.componente_curricular_id = view_componente_curricular.id
                                                                       AND falta_componente3.etapa = '3')
   LEFT JOIN modules.falta_componente_curricular falta_componente4 ON (falta_componente4.falta_aluno_id = falta_aluno.id
                                                                       AND falta_componente4.componente_curricular_id = view_componente_curricular.id
                                                                       AND falta_componente4.etapa = '4')
   LEFT JOIN modules.componente_curricular_ano_escolar ON (componente_curricular_ano_escolar.ano_escolar_id = serie.cod_serie
                                                           AND componente_curricular_ano_escolar.componente_curricular_id = view_componente_curricular.id
                                                           AND matricula.ano = any(componente_curricular_ano_escolar.anos_letivos)
                                                           )
   LEFT JOIN modules.regra_avaliacao_serie_ano rasa on(serie.cod_serie = rasa.serie_id AND matricula.ano = rasa.ano_letivo)
   LEFT JOIN modules.regra_avaliacao on(rasa.regra_avaliacao_id = regra_avaliacao.id)
   LEFT JOIN modules.area_conhecimento ON area_conhecimento.id = view_componente_curricular.area_conhecimento_id
   LEFT JOIN cadastro.etapa_ensino ON etapa_ensino.codigo = matricula_turma.etapa_educacenso
   WHERE instituicao.cod_instituicao = {$instituicao}
     AND escola.cod_escola = {$escola}
     AND curso.cod_curso = {$curso}
     AND serie.cod_serie = {$serie}
     AND turma.cod_turma = {$turma}
     AND escola_ano_letivo.ano = {$ano}
     AND view_situacao.cod_situacao = {$situacao_matricula}
     AND relatorio.exibe_aluno_conforme_parametro_alunos_diferenciados(aluno.cod_aluno, {$alunos_diferenciados})
     AND (CASE WHEN {$matricula} = 0 THEN TRUE ELSE matricula.cod_matricula = {$matricula} END)
   ORDER BY sequencial_fechamento,
            relatorio.get_texto_sem_caracter_especial(pessoa.nome),
            area_conhecimento.ordenamento_ac,
            view_componente_curricular.ordenamento,
            area_conhecimento.nome,
            view_componente_curricular.nome";
    }
}
