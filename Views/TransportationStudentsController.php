<?php

require_once 'lib/Portabilis/Controller/ReportCoreController.php';
require_once 'Reports/Reports/TransportationStudentsReport.php';
require_once 'include/modules/clsModulesRotaTransporteEscolar.inc.php';
require_once 'Portabilis/Date/Utils.php';

class TransportationStudentsController extends Portabilis_Controller_ReportCoreController
{
    /**
     * @var int
     */
    protected $_processoAp = 69;

    /**
     * @var string
     */
    protected $_titulo = 'Relação de alunos usuários do transporte escolar';

    /**
     * @inheritdoc
     */
    protected function _preRender()
    {
        parent::_preRender();

        Portabilis_View_Helper_Application::loadStylesheet($this, 'intranet/styles/localizacaoSistema.css');

        $this->breadcrumb('Relação de alunos usuários do transporte escolar', [
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
        // Montar o inputsHelper->select \/
        // Cria lista de rotas
        $obj_rota = new clsModulesRotaTransporteEscolar();
        $obj_rota->setOrderBy(' descricao asc ');
        $lista_rota = $obj_rota->lista();
        $rota_resources = ['' => 'Selecione uma rota'];
        foreach ($lista_rota as $reg) {
            $rota_resources["{$reg['cod_rota_transporte_escolar']}"] = "{$reg['descricao']} - {$reg['ano']}";
        }

        $this->inputsHelper()->select('modelo', [
            'label' => 'Modelo',
            'resources' => [
                1 => 'Por Rotas',
                2 => 'Por Transporte',
            ],
            'value' => 1
        ]);

        $this->inputsHelper()->select('transporte', [
            'label' => 'Usa transporte',
            'resources' => [
                1 => 'Selecione se usa transporte',
                2 => 'Sim',
                3 => 'Não',
                4 => 'Ambos',
            ],
            'value' => 1
        ]);

        // Rota
        $options = [
            'label' => 'Rota',
            'required' => false,
            'resources' => $rota_resources
        ];
        $this->inputsHelper()->select('rota', $options);
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
        $this->report->addArg('modelo', (int) $this->getRequest()->rota);
        $this->report->addArg('rota', (int) $this->getRequest()->rota);
        $this->report->addArg('transporte', (int) $this->getRequest()->rota);
    }

    /**
     * @return TransportationStudentsReport
     *
     * @throws Exception
     */
    public function report()
    {
        return new TransportationStudentsReport();
    }
}
