<?php

require_once 'lib/Portabilis/Controller/ReportCoreController.php';
require_once 'Reports/Reports/TransportationStudentsUseReport.php';
require_once 'include/modules/clsModulesRotaTransporteEscolar.inc.php';
require_once 'Portabilis/Date/Utils.php';

class TransportationStudentsUseController extends Portabilis_Controller_ReportCoreController
{
    /**
     * @var int
     */
    protected $_processoAp = 69;

    /**
     * @var string
     */
    protected $_titulo = 'Relação de alunos que usam o transporte escolar';

    /**
     * @inheritdoc
     */
    protected function _preRender()
    {
        parent::_preRender();

        Portabilis_View_Helper_Application::loadStylesheet($this, 'intranet/styles/localizacaoSistema.css');

        $this->breadcrumb('Relação de alunos que usam o transporte escolar', [
            'educar_transporte_escolar_index.php' => 'Transporte escolar',
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
    }

    /**
     * @return TransportationStudentsUseReport
     *
     * @throws Exception
     */
    public function report()
    {
        return new TransportationStudentsUseReport();
    }
}
