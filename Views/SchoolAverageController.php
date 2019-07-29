<?php

require_once 'lib/Portabilis/Date/Utils.php';
require_once 'lib/Portabilis/Controller/ReportCoreController.php';
require_once 'Reports/Reports/SchoolAverageReport.php';

class SchoolAverageController extends Portabilis_Controller_ReportCoreController
{
    /**
     * @inheritdoc
     */
    protected $_processoAp = 55;

    /**
     * @var string
     */
    protected $_titulo = 'Gráfico comparativo da média das escolas';

    /**
     * @inheritdoc
     */
    protected function _preRender()
    {
        parent::_preRender();

        Portabilis_View_Helper_Application::loadStylesheet($this, 'intranet/styles/localizacaoSistema.css');

        $this->breadcrumb('Gráfico comparativo da média das escolas', [
            'educar_index.php' => 'Escola',
        ]);
    }

    /**
     * @inheritdoc
     */
    public function form()
    {
        $this->inputsHelper()->dynamic(['ano', 'instituicao']);
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
        $this->report->addArg('etapa', (int) $this->getRequest()->etapa);
    }

    /**
     * @return SchoolAverageReport
     *
     * @throws Exception
     */
    public function report()
    {
        return new SchoolAverageReport();
    }
}
