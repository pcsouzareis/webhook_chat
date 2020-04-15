<?php

	# +-------------------------------------------------------------+
	# | EXECUTA UMA INSTRUÇÃO SQL SEM LINHA DE RETORNO              |
	# +-------------------------------------------------------------+
	function sc_exec_sql($sql){

		$conn_sql = New Conexao();
		$conn_sql->Open();

		return(@pg_query($conn_sql->StatusCon(), $sql));
	}

	# +------------------------------------------------+
	# | EXECUTA UMA INSTRUÇÃO SQL SEM LINHA DE RETORNO |
	# +------------------------------------------------+
	function sc_lookup($sql){

		$conn_up = New Conexao();
		$conn_up->Open();

		$result=@pg_query($conn_up->StatusCon(), $sql);
		if(!$result) {
				$result="";
		}elseif(pg_num_rows($result) == 0) {
			$result=0;
		}		
		return($result);
	}

	# +------------------------------------------------------+
	# | EXECUTA UMA INSTRUÇÃO SELECT SOMENTE 1 LINHA RETORNO |
	# +------------------------------------------------------+
	function sc_select($tabela, $array){

		$conn_sel = New Conexao();
		$conn_sel->Open();
		return(@pg_select ($conn_sel->StatusCon(), $tabela, $array));
	}

	# +---------------------------+
	# | REMOVE ACENTOS DA PALAVRA |
	# +---------------------------+
	function tirarAcentos($string){
		return strtoupper(preg_replace(array("/(á|à|ã|â|ä)/","/(Á|À|Ã|Â|Ä)/","/(é|è|ê|ë)/","/(É|È|Ê|Ë)/","/(í|ì|î|ï)/","/(Í|Ì|Î|Ï)/","/(ó|ò|õ|ô|ö)/","/(Ó|Ò|Õ|Ô|Ö)/","/(ú|ù|û|ü)/","/(Ú|Ù|Û|Ü)/","/(ñ)/","/(Ñ)/","/(ç|Ç)/"),explode(" ","a A e E i I o O u U n N c C"),$string));
	}

    # +---------------------------+
	# | DEIXA SOMENTE OS NUMEROS  |
	# +---------------------------+
    function soNumero($str) {
        return preg_replace("/[^0-9]/", "", $str);
    }

	# +------------------------------------------------------+
	# | TRANSFORMA "  " EM " "  / DOIS BRANCOS EM UM         |
	# +------------------------------------------------------+
	function transformaBB_B($txt){

		$return = "";
		$n      = 0;
		do{
			$return = str_replace("  "," ",$txt,$n);
		}while($n<>0);

		return($return);
	}

	# +------------------------------------------------------+
	# | VALIDA DATA                                          |
	# +------------------------------------------------------+
	function ValidaData($dat){

		$res = false;

		# SEPARA DIA/MES/ANO DA DATA
		$data = explode("/","$dat");

		if(isset($data[0]) and isset($data[1]) and isset($data[2])){
			$d = $data[0]; $m = $data[1]; $a = $data[2];
			if(is_numeric($d) and is_numeric($m) and is_numeric($a)){
				$res = checkdate($m,$d,$a);
			}
		}

		return($res);
	}

?>
