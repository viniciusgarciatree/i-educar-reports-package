<?php

require_once 'lib/Portabilis/View/Helper/DynamicInput/CoreSelect.php';

class Portabilis_View_Helper_DynamicInput_MatriculaAluno extends Portabilis_View_Helper_DynamicInput_CoreSelect
{
    protected function inputValue($value = null)
    {
        return 'cod_aluno';
    }

    protected function inputName()
    {
        return 'cod_aluno';
    }

    protected function inputOptions($options)
    {
        $resources = $options['resources'];
        $anoId = $options['ano'];
        $escolaId = $options['serieId'];
        $turmaId = $options['turmaId'];

            $sql = "
                SELECT  cod_aluno,nome
FROM pmieducar.aluno 
INNER JOIN cadastro.pessoa ON (pessoa.idpes = aluno.ref_idpes)
INNER JOIN pmieducar.matricula ON matricula.ref_cod_aluno = aluno.cod_aluno
INNER JOIN pmieducar.matricula_turma ON matricula_turma.ref_cod_matricula = matricula.cod_matricula
INNER JOIN pmieducar.turma ON turma.cod_turma = matricula_turma.ref_cod_turma
WHERE matricula.ano = :anoId
AND matricula.ref_ref_cod_escola = :escolaId
AND matricula_turma.ref_cod_turma = :turmaId
GROUP BY cod_aluno,nome
            ";

        $resources = Portabilis_Utils_Database::fetchPreparedQuery($sql, ['anoId' => $anoId, 'escolaId' => $escolaId, 'turmaId' => $turmaId]);
        $resources = Portabilis_Array_Utils::setAsIdValue($resources, 'cod_aluno', 'nome');

        return $this->insertOption(null, 'Selecione um Aluno', $resources);
    }
}
