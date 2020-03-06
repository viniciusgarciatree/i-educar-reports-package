<?php

use iEducar\Reports\JsonDataSource;

require_once 'lib/Portabilis/Report/ReportCore.php';
require_once 'App/Model/IedFinder.php';

class PersonalDataServantsReport extends Portabilis_Report_ReportCore
{
    use JsonDataSource;

    /**
     * @inheritdoc
     */
    public function templateName()
    {
        return 'personal-data-servants';
    }

    /**
     * @inheritdoc
     */
    public function requiredArgs()
    {
        $this->addRequiredArg('ano');
        $this->addRequiredArg('instituicao');
    }

    /**
     * Retorna o SQL para buscar os dados do relatório principal.
     *
     * @return string
     *
     * @throws Exception
     */
    public function getSqlMainReport()
    {
        $ano = $this->args['ano'] ?: 0;
        $instituicao = $this->args['instituicao'] ?: 0;
        $escola = $this->args['escola'] ?: 0;
        $funcao = $this->args['funcao'] ?: 0;
        $vinculo = $this->args['vinculo'] ?: 0;
        $periodo = $this->args['periodo'] ?: 0;
        $nao_emitir_afastados = $this->args['nao_emitir_afastados'];

        return "select * from (SELECT 
distinct on(servidor.cod_servidor) cod_servidor,
servidor_funcao.matricula,
pessoa.nome AS nm_servidor,
public.formata_cpf(fisica.cpf) AS cpf,
fone_pessoa.fone::varchar AS telefone,
       fone_pessoa.ddd::varchar AS ddd_telefone,
       pessoa.email
  FROM pmieducar.instituicao
 INNER JOIN pmieducar.servidor ON (servidor.ref_cod_instituicao = instituicao.cod_instituicao)
 INNER JOIN cadastro.pessoa ON (pessoa.idpes = servidor.cod_servidor)
  LEFT JOIN pmieducar.servidor_alocacao ON (servidor_alocacao.ref_cod_servidor = servidor.cod_servidor)
  LEFT JOIN pmieducar.servidor_funcao ON (servidor_funcao.cod_servidor_funcao = servidor_alocacao.ref_cod_servidor_funcao)
  LEFT JOIN pmieducar.funcao ON (servidor_funcao.ref_cod_funcao = funcao.cod_funcao)
  LEFT JOIN pmieducar.subnivel ON (servidor.ref_cod_subnivel = subnivel.cod_subnivel)
  LEFT JOIN pmieducar.nivel ON (subnivel.ref_cod_nivel = nivel.cod_nivel)
  LEFT JOIN cadastro.escolaridade ON (escolaridade.idesco = servidor.ref_idesco)
  LEFT JOIN cadastro.fisica ON (fisica.idpes = pessoa.idpes)
  LEFT JOIN cadastro.fone_pessoa ON (fone_pessoa.idpes = pessoa.idpes
                                     AND fone_pessoa.tipo = (SELECT COALESCE(MIN(fone_pessoa_aux.tipo),1)
			                                       FROM cadastro.fone_pessoa AS fone_pessoa_aux
				 			      WHERE fone_pessoa_aux.fone <> 0
							        AND fone_pessoa_aux.idpes = pessoa.idpes))
  LEFT JOIN portal.funcionario ON (servidor.cod_servidor = funcionario.ref_cod_pessoa_fj)
  LEFT JOIN portal.funcionario_vinculo ON (funcionario_vinculo.cod_funcionario_vinculo = servidor_alocacao.ref_cod_funcionario_vinculo)
  LEFT JOIN pmieducar.escola ON (servidor_alocacao.ref_cod_escola = escola.cod_escola)
  LEFT JOIN cadastro.juridica pessoa_juridica ON (pessoa_juridica.idpes = escola.ref_idpes)
  LEFT JOIN pmieducar.escola_ano_letivo ON (escola_ano_letivo.ref_cod_escola = escola.cod_escola AND escola_ano_letivo.ano = {$ano})
 WHERE instituicao.cod_instituicao = {$instituicao}
   AND servidor.ativo = 1
   AND servidor_alocacao.ano = {$ano}
   AND (CASE WHEN {$escola} = 0 THEN TRUE ELSE escola.cod_escola = {$escola} END)
   AND (CASE WHEN {$funcao} = 0 THEN TRUE ELSE funcao.cod_funcao = {$funcao} END)
   AND (CASE WHEN {$vinculo} = 0 THEN TRUE ELSE funcionario_vinculo.cod_funcionario_vinculo = {$vinculo} END)
   AND (CASE WHEN {$periodo} = 0 THEN TRUE ELSE servidor_alocacao.periodo = {$periodo} END)
   AND (CASE WHEN {$nao_emitir_afastados} = TRUE
             THEN cod_servidor NOT IN (SELECT sa.ref_cod_servidor
				    FROM pmieducar.servidor_afastamento sa
                                       WHERE sa.ativo = 1)
             ELSE TRUE
        END)
 ORDER BY servidor.cod_servidor) as tmp  order by nm_servidor";
    }
}
