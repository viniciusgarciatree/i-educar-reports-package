<?php

require_once 'lib/Portabilis/Date/Utils.php';
require_once 'lib/Portabilis/Controller/ReportCoreController.php';
require_once 'Reports/Reports/AverageOfClassesPerDisciplineGeneralReport.php';

class AverageOfClassesPerDisciplineGeneralController extends Portabilis_Controller_ReportCoreController
{
    /**
     * @inheritdoc
     */
    protected $_processoAp = 55;

    /**
     * @var string
     */
    protected $_titulo = 'Gráfico comparativo da média das turmas por disciplina e geral';

    /**
     * @inheritdoc
     */
    protected function _preRender()
    {
        parent::_preRender();

        Portabilis_View_Helper_Application::loadStylesheet($this, 'intranet/styles/localizacaoSistema.css');

        $this->breadcrumb('Gráfico comparativo da média das turmas por disciplina e geral', [
            'educar_index.php' => 'Escola',
        ]);
    }

    /**
     * @inheritdoc
     */
    public function form()
    {
        $this->inputsHelper()->dynamic('ano', ['required' => true]);
        $this->inputsHelper()->dynamic('instituicao', ['required' => true]);
        $this->inputsHelper()->dynamic('escola', ['required' => true]);
        $this->inputsHelper()->select('etapa', [
            'label' => 'Etapa',
            'resources' => [
                0 => 'Selecione',
                1 => 'Etapa 1',
                2 => 'Etapa 2',
                3 => 'Etapa 3',
                4 => 'Etapa 4',
            ],
            'value' => 0,
            'required' => false
        ]);
        $this->inputsHelper()->checkbox('separar', ['label' => 'Separar por disciplina', 'required' => false]);
        $this->loadResourceAssets($this->getDispatcher());
    }

    /**
     * @inheritdoc
     */
    public function beforeValidation()
    {
        $this->report->addArg('ano', (int) $this->getRequest()->ano);
        $this->report->addArg('instituicao', (int) $this->getRequest()->ref_cod_instituicao);
        $this->report->addArg('escola', (int) $this->getRequest()->ref_cod_escola);
        $this->report->addArg('etapa', (int) $this->getRequest()->etapa);
        $this->report->addArg('separar', (bool) $this->getRequest()->separar);
    }

    /**
     * @return AverageOfClassesPerDisciplineGeneralReport
     *
     * @throws Exception
     */
    public function report()
    {
        return new AverageOfClassesPerDisciplineGeneralReport();
    }
}
