<?php

require_once 'lib/Portabilis/Controller/ReportCoreController.php';
require_once 'Reports/Reports/ReportAuditoriaGeralReport.php';
require_once 'Portabilis/Date/Utils.php';

class ReportAuditoriaGeralController extends Portabilis_Controller_ReportCoreController
{
    /**
     * @var int
     */
    protected $_processoAp = 9999205;

    /**
     * @var string
     */
    protected $_titulo = 'Relatório auditoria geral';

    /**
     * @inheritdoc
     */
    protected function _preRender()
    {
        parent::_preRender();

        //$this->acao_enviar = 'customPrintReport()';

        $this->breadcrumb('Relatório auditoria geral', [
            '/intranet/educar_index.php' => 'Escola'
        ]);
    }

    /**
     * @inheritdoc
     */
    public function form()
    {
        $this->inputsHelper()->dynamic(['instituicao']);

        $this->inputsHelper()->text('matricula', [
            'required' => false,
            'label' => 'Matrícula usuário'
        ]);

        $this->inputsHelper()->text('rotinas', [
            'required' => false,
            'label' => 'Rotinas'
        ]);

        $this->inputsHelper()->text('registro', [
            'required' => false,
            'label' => 'Código do registro'
        ]);

        $operacoes = \iEducar\Modules\AuditoriaGeral\Model\Operacoes::getDescriptiveValues();
        $operacoes = array_replace([null => 'Todas'], $operacoes);

        $options = [
            'label' => 'Operação',
            'resources' => $operacoes,
            'required' => false,
            'value' => 0
        ];
        $this->inputsHelper()->select('operacao', $options);

        $this->inputsHelper()->dynamic(['dataInicial','dataFinal']);

        $this->campoHora('hora_inicial', 'Hora Inicial', $this->hora_inicial, false);
        $this->campoHora('hora_final', 'Hora Final', $this->hora_final, false);

        $this->loadResourceAssets($this->getDispatcher());
    }

    /**
     * @inheritdoc
     */
    public function beforeValidation()
    {
        $this->report->addArg('instituicao', (int) $this->getRequest()->ref_cod_instituicao);
        $this->report->addArg('matricula', $this->getRequest()->matricula);
        $this->report->addArg('rotinas', $this->getRequest()->rotinas);
        $this->report->addArg('registro', $this->getRequest()->registro);
        $this->report->addArg('operacao', (int) $this->getRequest()->operacao_id);
        $this->report->addArg('data_inicial', $this->getRequest()->data_inicial);
        $this->report->addArg('data_final', $this->getRequest()->data_final);
        $this->report->addArg('hora_inicial', $this->getRequest()->hora_inicial);
        $this->report->addArg('hora_final', $this->getRequest()->hora_final);
    }

    /**
     * @return ReportCardChildishReport
     *
     * @throws Exception
     */
    public function report()
    {
        return new ReportAuditoriaGeralReport();
    }
}
