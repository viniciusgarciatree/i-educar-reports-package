<?php

use iEducar\Support\View\SelectOptions;

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
        $this->inputsHelper()->dynamic('escola', ['required' => false]);

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

        $resources = SelectOptions::tiposVinculoServidor();

        $this->campoLista('vinculo', 'Tipo do vínculo', $resources, null, '', false, '', '', false, true);
    }

    /**
     * @inheritdoc
     */
    public function beforeValidation()
    {
        $this->report->addArg('ano', (int) $this->getRequest()->ano);
        $this->report->addArg('instituicao', (int) $this->getRequest()->ref_cod_instituicao);
        $this->report->addArg('escola', (int) $this->getRequest()->ref_cod_escola);
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