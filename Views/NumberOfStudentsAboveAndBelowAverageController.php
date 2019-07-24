<?php

require_once 'lib/Portabilis/Date/Utils.php';
require_once 'lib/Portabilis/Controller/ReportCoreController.php';
require_once 'Reports/Reports/NumberOfStudentsAboveAndBelowAverageReport.php';

class NumberOfStudentsAboveAndBelowAverageController extends Portabilis_Controller_ReportCoreController
{
    /**
     * @inheritdoc
     */
    protected $_processoAp = 55;

    /**
     * @var string
     */
    protected $_titulo = 'Gráfico comparativo da quantidade de alunos acima e abaixo da média por disciplina';

    /**
     * @inheritdoc
     */
    protected function _preRender()
    {
        parent::_preRender();

        Portabilis_View_Helper_Application::loadStylesheet($this, 'intranet/styles/localizacaoSistema.css');

        $this->breadcrumb('Gráfico comparativo da quantidade de alunos acima e abaixo da média por disciplina', [
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
        $this->inputsHelper()->dynamic('escola', ['required' => false]);
        $this->inputsHelper()->dynamic('curso', ['required' => false]);
        $this->inputsHelper()->dynamic('serie', ['required' => false]);
        $this->inputsHelper()->dynamic('turma', ['required' => false]);
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
        $this->report->addArg('curso', (int) $this->getRequest()->ref_cod_curso);
        $this->report->addArg('serie', (int) $this->getRequest()->ref_cod_serie);
        $this->report->addArg('turma', (int) $this->getRequest()->ref_cod_turma);
        $this->report->addArg('etapa', (int) $this->getRequest()->etapa);
    }

    /**
     * @return NumberOfStudentsAboveAndBelowAverageReport
     *
     * @throws Exception
     */
    public function report()
    {
        return new NumberOfStudentsAboveAndBelowAverageReport();
    }
}
