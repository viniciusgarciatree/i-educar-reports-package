<?php

use iEducar\Reports\JsonDataSource;

require_once 'lib/Portabilis/Report/ReportCore.php';
require_once 'App/Model/IedFinder.php';

class AbsenceServantsReport extends Portabilis_Report_ReportCore
{
    use JsonDataSource;

    /**
     * @inheritdoc
     */
    public function templateName()
    {
        return 'absence-servants';
    }

    /**
     * @inheritdoc
     */
    public function requiredArgs()
    {
        $this->addRequiredArg('ano');
        $this->addRequiredArg('instituicao');
        $this->addRequiredArg('data_inicial');
        $this->addRequiredArg('data_final');
    }

    /**
     * Retorna o SQL para buscar os dados do relatório principal.
     *
     * @return string
     */
    public function getSqlMainReport()
    {
        $ano = $this->args['ano'] ?: 0;
        $instituicao = $this->args['instituicao'] ?: 0;
//        $escola = $this->args['escola'] ?: 0;
        $servidor = $this->args['servidor'] ?: 0;
        $dataInicial = $this->args['data_inicial'] ?: 0;
        $dataFinal = $this->args['data_final'] ?: 0;

        return 'select * from ( select 
distinct on(cod_servidor, sa.data_saida) cod_servidor,
servidor_funcao.matricula,
nome, 
nm_motivo, 
to_char(sa.data_saida,\'dd/MM/yyyy\') as data_saida, 
to_char(sa.data_retorno,\'dd/MM/yyyy\') as data_retorno
from pmieducar.servidor_afastamento sa
 left outer join pmieducar.servidor se on sa.ref_cod_servidor = se.cod_servidor
 LEFT JOIN pmieducar.servidor_alocacao ON (servidor_alocacao.ref_cod_servidor = se.cod_servidor)
  LEFT JOIN pmieducar.servidor_funcao ON (servidor_funcao.cod_servidor_funcao = servidor_alocacao.ref_cod_servidor_funcao)
left outer join cadastro.pessoa ps on se.cod_servidor = ps.idpes
left outer join pmieducar.motivo_afastamento ma on ma.cod_motivo_afastamento = sa.ref_cod_motivo_afastamento WHERE '.
            ($servidor > 0 ? "se.cod_servidor = $servidor AND " : '').
            ($dataInicial != 0 ? "sa.data_saida >= '$dataInicial' " : ' ').
            ($dataFinal != 0 ? "AND sa.data_saida <= '$dataFinal' " : ' ').
        ' ) as tmp order by nome';
    }
}
