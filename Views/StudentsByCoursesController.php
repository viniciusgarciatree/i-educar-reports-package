<?php

require_once 'lib/Portabilis/Date/Utils.php';
require_once 'lib/Portabilis/Controller/ReportCoreController.php';
require_once 'Reports/Reports/StudentsByCoursesReport.php';

class StudentsByCoursesController extends Portabilis_Controller_ReportCoreController
{
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
