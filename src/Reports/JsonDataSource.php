<?php

namespace iEducar\Reports;

use Exception;
use Portabilis_Utils_Database;

trait JsonDataSource
{
    /**
     * @inheritdoc
     */
    public function useJson()
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function getJsonData()
    {
        $queryMainReport = $this->getSqlMainReport();
        $queryHeaderReport = $this->getSqlHeaderReport();

        return [
            'main' => Portabilis_Utils_Database::fetchPreparedQuery($queryMainReport),
            'header' => Portabilis_Utils_Database::fetchPreparedQuery($queryHeaderReport),
        ];
    }

    /**
     * @inheritdoc
     */
    public function getJsonQuery()
    {
        return 'main';
    }

    /**
     * Retorna o SQL para buscar os dados que serão adicionados ao cabeçalho.
     *
     * @return string
     *
     * @throws Exception
     */
    public function getSqlHeaderReport()
    {
        $instituicao = $this->args['instituicao'] ?: 0;
        $escola = $this->args['escola'] ?: 0;
        $notSchool = empty($this->args['escola']) ? 'true' : 'false';

        $select = "";
        if($notSchool == 'true'){
            $select .= " '' AS nm_escola, ";
            $select .= " instituicao.ref_idtlog, ";
            $select .= " instituicao.logradouro, ";
            $select .= " instituicao.bairro, ";
            $select .= " instituicao.ddd_telefone AS fone_ddd, ";
            $select .= " 0  AS cel_ddd, ";
            $select .= " to_char(instituicao.telefone, '99999-9999') AS fone, ";
            $select .= " ' ' AS cel, ";
            $select .= " ' ' AS email, "; 
        }else{
            $select .= " fcn_upper(view_dados_escola.nome) AS nm_escola, ";
            $select .= " null AS tipo_logradouro, ";
            $select .= " view_dados_escola.logradouro, ";
            $select .= " view_dados_escola.bairro, ";
            $select .= " view_dados_escola.telefone_ddd  AS fone_ddd, ";
            $select .= " view_dados_escola.celular_ddd AS cel_ddd, ";
            $select .= " view_dados_escola.telefone AS fone, ";
            $select .= " view_dados_escola.celular AS cel, ";
            $select .= " view_dados_escola.email AS email, "; 
        }

        $sql = "

            SELECT
                public.fcn_upper(instituicao.nm_instituicao) AS nm_instituicao,
                public.fcn_upper(instituicao.nm_responsavel) AS nm_responsavel,
                $select
                instituicao.ref_sigla_uf AS uf,
                instituicao.cidade,
                a.address AS logradouro,
                a.number AS numero,
                a.postal_code AS cep,
                view_dados_escola.inep
            FROM
                pmieducar.instituicao
            INNER JOIN pmieducar.escola ON TRUE
                AND (instituicao.cod_instituicao = escola.ref_cod_instituicao)
            INNER JOIN relatorio.view_dados_escola ON TRUE
                AND (escola.cod_escola = view_dados_escola.cod_escola)
            LEFT JOIN person_has_place php ON TRUE
                AND php.person_id = escola.ref_idpes AND php.type = 1
            LEFT JOIN addresses a ON TRUE
                AND a.id = php.place_id
            LEFT JOIN addresses ad ON ad.id = php.place_id
            WHERE TRUE
                AND instituicao.cod_instituicao = {$instituicao}
                AND
                (
                    CASE WHEN {$notSchool} THEN
                        TRUE
                    ELSE
                        view_dados_escola.cod_escola = {$escola}
                    END
                )
            LIMIT 1

        ";

        return $sql;
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
        throw new Exception('Missing implementation.');
    }
}
