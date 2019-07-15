<?php

require_once 'lib/Portabilis/Date/Utils.php';
require_once 'lib/Portabilis/Controller/ReportCoreController.php';
require_once 'Reports/Reports/StudentsByNeighborhoodReport.php';

class StudentsByNeighborhoodController extends Portabilis_Controller_ReportCoreController
{
    /**
     * @var string
     */
    protected $_titulo = 'Alunos matriculados por bairro';

    /**
     * @inheritdoc
     */
    protected function _preRender()
    {
        parent::_preRender();

        Portabilis_View_Helper_Application::loadStylesheet($this, 'intranet/styles/localizacaoSistema.css');

        $this->breadcrumb('Alunos matriculados por bairro', [
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
     * @return StudentsByNeighborhoodReport
     *
     * @throws Exception
     */
    public function report()
    {
        return new StudentsByNeighborhoodReport();
    }
}
