// subescreve parametro escola_id definido em SimpleSearchAluno.js, listando alunos de todas escolas
//simpleSearchAlunoOptions.params.escola_id = 0;

$j("#nm_secretario").closest('tr').hide();
$j("#nm_diretor").closest('tr').hide();
$j("#ano_ini").closest('tr').hide();
$j("#ano_fim").closest('tr').hide();
$j("#cursoaluno").closest('tr').hide();
$j("#apenas_ultimo_registro").closest('tr').hide();

$j("#imprime_diretor_secretario").on('click', function(){
	if($j('#imprime_diretor_secretario').prop('checked')){
		$j("#nm_secretario").closest('tr').show();
		$j("#nm_diretor").closest('tr').show();
	}else{
		$j("#nm_secretario").closest('tr').hide();
		$j("#nm_diretor").closest('tr').hide();
	}
});

$j('#lote').click(function(){
    if ($j('#lote').prop('checked')){
	  $j('#ref_cod_matricula').closest('tr').hide();
    }
    else{
	  ref_cod_turma.value = 0;
	  ref_cod_serie.value = 0;
	  ref_cod_curso.value = 0;
      ref_cod_matricula.value = 0;
	  $j('#ref_cod_matricula').closest('tr').show();
    }
});

$j("#modelo").on('click', function(){
    var template = $j("#modelo").val();
    if (template == 2) {
        $j("#ano_ini").closest('tr').show();
        $j("#ano_fim").closest('tr').show();
        $j("#apenas_ultimo_registro").closest('tr').show();
        $j("#cursoaluno").closest('tr').show();
    } else {
        $j("#ano_ini").closest('tr').hide();
        $j("#ano_fim").closest('tr').hide();
        $j("#apenas_ultimo_registro").closest('tr').hide();
        $j("#cursoaluno").closest('tr').hide();
    }
});