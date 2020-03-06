<?php

require_once 'lib/Portabilis/Date/Utils.php';
require_once 'lib/Portabilis/Controller/ReportCoreController.php';
require_once 'Reports/Reports/StudentsAllocationReport.php';

class StudentsAllocationController extends Portabilis_Controller_ReportCoreController
{
    /**
     * @inheritdoc
     */
    protected $_processoAp = 55;

    /**
     * @var string
     */
    protected $_titulo = 'Alunos enturmados/não enturmados';

    /**
     * @inheritdoc
     */
    protected function _preRender()
    {
        parent::_preRender();

        Portabilis_View_Helper_Application::loadStylesheet($this, 'intranet/styles/localizacaoSistema.css');

        $this->breadcrumb('Alunos enturmados/não enturmados', [
            'educar_index.php' => 'Escola',
        ]);
    }

    /**
     * @inheritdoc
     */
    public function form()
    {
//        $this->inputsHelper()->dynamic(['ano', 'instituicao', 'escola', 'curso']);
        $this->inputsHelper()->dynamic(['ano', 'instituicao', 'escola']);
        $this->inputsHelper()->dynamic('curso', ['required' => false]);
        $this->inputsHelper()->select('modelo', [
            'label' => 'Modelo',
            'resources' => [
                1 => 'Enturmados',
                2 => 'Não Enturmados',
            ],
            'value' => 1
        ]);
//        $this->inputsHelper()->dynamic('serie', ['required' => true]);
//        $this->inputsHelper()->dynamic('turma', ['required' => true]);
//        $this->inputsHelper()->dynamic('matricula', ['required' => false]);
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
        $this->report->addArg('modelo', (int) $this->getRequest()->modelo);
//        $this->report->addArg('serie', (int) $this->getRequest()->ref_cod_serie);
//        $this->report->addArg('turma', (int) $this->getRequest()->ref_cod_turma);
//        $this->report->addArg('matricula', (int) $this->getRequest()->ref_cod_matricula);
    }

    /**
     * @return StudentsAllocationReport
     *
     * @throws Exception
     */
    public function report()
    {
        return new StudentsAllocationReport();
    }
}
