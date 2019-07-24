<?php

require_once 'lib/Portabilis/Date/Utils.php';
require_once 'lib/Portabilis/Controller/ReportCoreController.php';
require_once 'Reports/Reports/StudentsByCoursesReport.php';

class StudentsByCoursesController extends Portabilis_Controller_ReportCoreController
{
    /**
     * @inheritdoc
     */
    protected $_processoAp = 55;

    /**
     * @var string
     */
    protected $_titulo = 'Alunos matriculados por curso';

    /**
     * @inheritdoc
     */
    protected function _preRender()
    {
        parent::_preRender();

        Portabilis_View_Helper_Application::loadStylesheet($this, 'intranet/styles/localizacaoSistema.css');

        $this->breadcrumb('Alunos matriculados por curso', [
            'educar_index.php' => 'Escola',
        ]);
    }

    /**
     * @inheritdoc
     */
    public function form()
    {
        $this->inputsHelper()->dynamic(['ano', 'instituicao']);
        $this->inputsHelper()->dynamic('escola', [], ['options' => ['required' => false]]);
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
        $this->inputsHelper()->checkbox('separar', ['label' => 'Separar por escola', 'required' => false]);
        $this->loadResourceAssets($this->getDispatcher());
    }

    /**
     * @inheritdoc
     */
    public function beforeValidation()
    {
        $this->report->addArg('ano', (int) $this->getRequest()->ano);
        $this->report->addArg('instituicao', (int) $this->getRequest()->ref_cod_instituicao);
        $this->report->addArg('separar', (bool) $this->getRequest()->separar);
        $this->report->addArg('escola', (int) $this->getRequest()->ref_cod_escola);
        $this->report->addArg('situacao', (int) $this->getRequest()->situacao);
    }

    /**
     * @return StudentsByCoursesReport
     *
     * @throws Exception
     */
    public function report()
    {
        return new StudentsByCoursesReport();
    }
}
