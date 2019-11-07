<?php

use iEducar\Reports\JsonDataSource;

require_once 'lib/Portabilis/Report/ReportCore.php';
require_once 'App/Model/IedFinder.php';

class TeachersByClassReport extends Portabilis_Report_ReportCore
{
    use JsonDataSource;

    /**
     * @inheritdoc
     */
    public function templateName()
    {
        return 'teachers-by-class';
    }

    /**
     * @inheritdoc
     */
    public function requiredArgs()
    {
        $this->addRequiredArg('ano');
        $this->addRequiredArg('instituicao');
//        $this->addRequiredArg('escola');
//        $this->addRequiredArg('curso');
//        $this->addRequiredArg('serie');
    }

    public function getSqlMainReport()
    {
        $ano = $this->args['ano'] ?: 0;
        $instituicao = $this->args['instituicao'] ?: 0;
        $escola = $this->args['escola'] ?: 0;
        $curso = $this->args['curso'] ?: 0;
        $serie = $this->args['serie'] ?: 0;
        $turma = $this->args['turma'] ?: 0;

        return "

SELECT 
    pe.nome as escola,
    nm_curso,
    nm_serie,
    turma.cod_turma,
    turma.nm_turma,
    pessoa.nome AS nm_docente,
    educacenso_cod_docente.cod_docente_inep AS inep,
    escolaridade.descricao AS escolaridade,
    (
        CASE WHEN MAX(tipo_vinculo) = 1 THEN
            'Efetivo'
        WHEN MAX(tipo_vinculo) = 2 THEN
            'Temporário'
        WHEN MAX(tipo_vinculo) = 3 THEN
            'Terceirizado'
        WHEN MAX(tipo_vinculo) = 4 THEN
            'CLT'
        END
    ) AS vinculo
FROM pmieducar.instituicao
INNER JOIN pmieducar.escola ON TRUE 
    AND escola.ref_cod_instituicao = instituicao.cod_instituicao
    left outer join cadastro.pessoa pe on escola.ref_idpes = pe.idpes
INNER JOIN pmieducar.escola_curso ON TRUE 
    AND escola_curso.ativo = 1
    AND escola_curso.ref_cod_escola = escola.cod_escola
INNER JOIN pmieducar.curso ON TRUE 
    AND curso.cod_curso = escola_curso.ref_cod_curso
    AND curso.ativo = 1
INNER JOIN pmieducar.escola_serie ON TRUE 
    AND escola_serie.ativo = 1
    AND escola_serie.ref_cod_escola = escola.cod_escola
INNER JOIN pmieducar.serie ON TRUE 
    AND serie.cod_serie = escola_serie.ref_cod_serie
    AND serie.ativo = 1
INNER JOIN pmieducar.turma ON TRUE 
    AND turma.ref_ref_cod_escola = escola.cod_escola
    AND turma.ref_cod_curso = escola_curso.ref_cod_curso
    AND turma.ref_ref_cod_serie = escola_serie.ref_cod_serie
    AND turma.ativo = 1
INNER JOIN modules.professor_turma ON TRUE 
    AND professor_turma.turma_id = turma.cod_turma
    AND professor_turma.funcao_exercida IN(1,5,6)
INNER JOIN pmieducar.servidor ON TRUE 
    AND servidor.cod_servidor = professor_turma.servidor_id
    AND servidor.ativo = 1
INNER JOIN cadastro.pessoa ON TRUE 
    AND pessoa.idpes = servidor.cod_servidor
INNER JOIN cadastro.fisica ON TRUE 
    AND fisica.idpes = servidor.cod_servidor
LEFT JOIN educacenso_cod_docente ON TRUE 
    AND educacenso_cod_docente.cod_servidor = servidor.cod_servidor
LEFT JOIN cadastro.escolaridade ON TRUE 
    AND escolaridade.idesco = servidor.ref_idesco
    WHERE TRUE 
    AND instituicao.cod_instituicao = {$instituicao}
    AND 
    (
        CASE WHEN {$escola} = 0 THEN 
            TRUE 
        ELSE 
            escola.cod_escola = {$escola}
        END
    )
    AND 
    (
        CASE WHEN {$curso} = 0 THEN 
            TRUE 
        ELSE 
            curso.cod_curso = {$curso}
        END
    )
    AND 
    (
        CASE WHEN {$serie} = 0 THEN 
            TRUE 
        ELSE 
            serie.cod_serie = {$serie}
        END
    )
    AND 
    (
        CASE WHEN {$turma} = 0 THEN 
            TRUE 
        ELSE 
            turma.cod_turma = {$turma} 
        END
    )
    AND turma.ano = {$ano}
 GROUP BY
    pe.nome,
    escola.cod_escola,
    curso.nm_curso,
    serie.nm_serie,
    turma.cod_turma,
    turma.nm_turma,
    pessoa.nome,
    fisica.data_nasc,
    educacenso_cod_docente.cod_docente_inep,
    escolaridade.descricao,
    servidor.cod_servidor
ORDER BY 
    pe.nome,
    pessoa.nome
    
        ";
    }
}
