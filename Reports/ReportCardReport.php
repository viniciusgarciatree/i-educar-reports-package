<?php

use iEducar\Reports\JsonDataSource;

require_once 'lib/Portabilis/Report/ReportCore.php';
require_once 'Reports/Tipos/TipoBoletim.php';
require_once 'App/Model/IedFinder.php';
require_once 'Reports/Queries/GeneralOpinionsTrait.php';
require_once 'Reports/Queries/ReportCardTrait.php';
require_once 'Reports/Modifiers/ReportCardModifier.php';
require_once 'Reports/Queries/DescriptiveOpinionsTrait.php';

class ReportCardReport extends Portabilis_Report_ReportCore
{
    use JsonDataSource, GeneralOpinionsTrait, ReportCardTrait, DescriptiveOpinionsTrait {
        DescriptiveOpinionsTrait::query insteadof GeneralOpinionsTrait;
        GeneralOpinionsTrait::query insteadof ReportCardTrait;
        DescriptiveOpinionsTrait::query as QueryDescriptiveOpinions;
        GeneralOpinionsTrait::query as QueryGeneralOpinions;
        ReportCardTrait::query as QueryReportCard;
    }

    /**
     * @var array
     */
    public $modifiers = [
        ReportCardModifier::class,
    ];

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

        if ($this->args['modelo'] == 1) {
            $template = 'report-card';
            if (empty($flagTipoBoletimTurma)) {
                throw new Exception('Não foi definido o tipo de boletim no cadastro de turmas.');
            }

            $templates = Portabilis_Model_Report_TipoBoletim::getInstance()->getReports();
            $template = !empty($templates[$flagTipoBoletimTurma]) ? $templates[$flagTipoBoletimTurma] : $template;
        } elseif ($this->args['modelo'] == 2) {
            $template = 'report-card-boletim';
        }

        if ($this->args['orientacao'] == 2) {
            $template = $templates[Portabilis_Model_Report_TipoBoletim::CONCEPTUAL_LANDSCAPE];
        }

        if (empty($template)) {
            throw new Exception('Não foi possivel recuperar nome do template para o boletim.');
        }

        return $template;
    }

    public function getJsonData()
    {
        $template = $this->templateName();

        return [
            'main' => Portabilis_Utils_Database::fetchPreparedQuery($this->getQueryByTemplate()[$template]),
            'header' => Portabilis_Utils_Database::fetchPreparedQuery($this->getSqlHeaderReport())
        ];

        // versão da versa 2.3.0
        if ($this->templateName() == 'report-card-boletim') {
            $queryMainReport = $this->getSqlMainReport();
            $dados   = Portabilis_Utils_Database::fetchPreparedQuery($queryMainReport);
            $queryHeaderReport = $this->getSqlHeaderReport();

            $arrNota = [];
            $arrMain = [];
            $arrAreaConhecimento = [];
            $arrDisciplina = [];
            $indexMatricula = -1;
            $arrIndexMatricula = [];
            $arrMatricula = [];

            foreach ($dados as $key => $value) {
                $arrAreaConhecimento[$value['area_conhecimento_id']] = $value['area_conhecimento'];
                $arrDisciplina[$value['area_conhecimento_id']][$value['nome_disciplina_id']]=$value['nome_disciplina'];

                $indexMatricula  = isset($arrIndexMatricula[$value['matricula']]) ? $indexMatricula : $indexMatricula + 1;
                $arrIndexMatricula[$value['matricula']] = $indexMatricula;

                $arrMatricula[$value['matricula']] = $value;

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
                    'total_faltas_et1' => (int)$value['total_faltas_et1'],
                    'total_faltas_et2' => (int)$value['total_faltas_et2'],
                    'total_faltas_et3' => (int)$value['total_faltas_et3'],
                    'total_faltas_et4' => (int)$value['total_faltas_et4'],
                    'faltas_componente_et1' => (int)$value['faltas_componente_et1'],
                    'faltas_componente_et2' => (int)$value['faltas_componente_et2'],
                    'faltas_componente_et3' => (int)$value['faltas_componente_et3'],
                    'faltas_componente_et4' => (int)$value['faltas_componente_et4'],
                    'total_geral_faltas_componente' => $value['total_geral_faltas_componente'],
                    'total_faltas' => $value['total_faltas'],
                    'curso_hora_falta' => $value['curso_hora_falta'],
                    'dias_letivos_serie' => (int)$value['dias_letivos_serie'],
                    'carga_horaria_componente' => $value['carga_horaria_componente'],
                    'carga_horaria_serie' => $value['carga_horaria_serie'],
                    'media' => $value['media'],
                    'medianum' => (double)$value['medianum'],
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
                $value['observacoes'] = $this->args['observacoes'] ?: '';
                $arrMain[] = $value;
            }

            unset($this->args['modelo']);
            unset($this->args['observacoes']);

            return array_merge([
                'main' => $arrMain,
                'header' => Portabilis_Utils_Database::fetchPreparedQuery($queryHeaderReport),
            ]);
        } else {
            unset($this->args['modelo']);
            $queryMainReport = $this->getSqlMainReport();
            $queryHeaderReport = $this->getSqlHeaderReport();
            $arrMain = Portabilis_Utils_Database::fetchPreparedQuery($queryMainReport);
            foreach ($arrMain as $index => $value) {
                if (is_numeric($index)) {
                    $arrMain[$index]['observacoes'] =  $this->args['observacoes'] ?: '';
                }
            }
            unset($this->args['observacoes']);

            return array_merge([
                'main' => $arrMain,
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

    private function getQueryByTemplate()
    {
        $templates = Portabilis_Model_Report_TipoBoletim::getInstance()->getReports();

        return [
            $templates[Portabilis_Model_Report_TipoBoletim::NUMERIC] => $this->QueryReportCard(),
            $templates[Portabilis_Model_Report_TipoBoletim::CONCEPTUAL] => $this->QueryReportCard(),
            $templates[Portabilis_Model_Report_TipoBoletim::CONCEPTUAL_LANDSCAPE] => $this->QueryReportCard(),
            $templates[Portabilis_Model_Report_TipoBoletim::PARECER_DESCRITIVO_COMPONENTE] => $this->QueryDescriptiveOpinions(),
            $templates[Portabilis_Model_Report_TipoBoletim::PARECER_DESCRITIVO_GERAL] => $this->QueryGeneralOpinions(),

        ];
    }
}
