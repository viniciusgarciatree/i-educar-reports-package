<?php

require_once 'lib/Portabilis/Controller/ReportCoreController.php';
require_once 'Reports/Reports/ReportsPppReport.php';
require_once 'Portabilis/Date/Utils.php';

class ReportsPppController extends Portabilis_Controller_ReportCoreController
{
    /**
     * @var int
     */
    protected $_processoAp = 9999203;

    /**
     * @var string
     */
    protected $_titulo = 'Relatório Projeto Político Pedagógico (PPP)';

    /**
     * @inheritdoc
     */
    protected function _preRender()
    {
        parent::_preRender();

        Portabilis_View_Helper_Application::loadStylesheet($this, 'intranet/styles/localizacaoSistema.css');

        $this->breadcrumb('Relatório Projeto Político Pedagógico (PPP)', [
            'educar_index.php' => 'Escola',
        ]);
    }

    /**
     * @inheritdoc
     */
    public function form()
    {
        $this->inputsHelper()->dynamic(['ano', 'instituicao'],['required' => true]);

        $this->inputsHelper()->select('situacao', [
            'label' => 'Situação',
            'resources' => [
                1 => 'Aprovado',
                2 => 'Reprovado',
                3 => 'Em Andamento',
                4 => 'Transferido',
                6 => 'Abandono',
                9 => 'Todas'
            ],
            'value' => 3
        ]);

        $obj_vinculos = new clsPmieducarFuncionarioVinculo();
        $opcoes = $obj_vinculos->lista();

        $this->campoLista('vinculo', 'Vinulo do servidor', $opcoes, null, '', false, '', '', false, true);

        $obj_funcoes = new clsPmieducarFuncao();
        $lista_funcoes = $obj_funcoes->lista();
        $opcoes = ['' => 'Selecione'];

        if ($lista_funcoes) {
            foreach ($lista_funcoes as $funcao) {
                $opcoes[$funcao['cod_funcao']] = $funcao['nm_funcao'];
            }
        }
        $this->campoLista('funcao', 'Fun&ccedil;&atilde;o do servidor', $opcoes, $this->cod_servidor_funcao, '', false, '', '', false, false);
    }

    /**
     * @inheritdoc
     */
    public function beforeValidation()
    {
        $this->report->addArg('ano', (int) $this->getRequest()->ano);
        $this->report->addArg('instituicao', (int) $this->getRequest()->ref_cod_instituicao);
        $this->report->addArg('situacao', (int) $this->getRequest()->situacao);
        $this->report->addArg('vinculo', (int) $this->getRequest()->vinculo);
    }

    /**
     * @return ClassAverageComparativeReport
     *
     * @throws Exception
     */
    public function report()
    {
        return new ReportsPppReport();
    }
}