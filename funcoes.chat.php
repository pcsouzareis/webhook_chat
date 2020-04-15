<?php	

	# +------------------------------------------------------+
	# | MARCA O CAMPO COM 'S'                                |
	# +------------------------------------------------------+	
	function MarcaCampo_S($texto, $where, $info){
		# 0-'NM', 1-'okn004', 2 - $men_usu[5]	
		$ret = NULL;
		foreach ($info as $value){
  			if($texto==$value[0]){
				$ret = $value[2];
				sc_exec_sql ("UpDate n004 Set $value[1] = 'S' $where");
				break;
			}
		}
		return $ret;
	}

	# +------------------------------------------------------+
	# | ATUALIZAR CADASTRO DO CONTATO                        |
	# +------------------------------------------------------+
	function AtuCampoContato($info){
		# 0=$pednome, 1=$n004_okn004, 2='des004', 3='okn004', 4=$body, 5=$bodyUPR, 6=$n004_where, 7=(strlen(trim($body))>7), 8=$men_usu[6]

		$ret=null;
		foreach ($info as $value){

			if($value[0]=="S" and $value[1]=="S"){

				if($value[5]=="N" or $value[5]=="NAO"){
					sc_exec_sql ("UpDate n004 Set $value[3] = 'N' $value[6]");
					$ret = "Atualização cancelada!";
				}elseif($value[7]){
					sc_exec_sql ("UpDate n004 Set $value[2] = '$value[4]', $value[3] = 'N' $value[6]");
					$ret = "Atualizado!";
				}else{
					$ret = $value[8];
				}

				break;
			}

		}
		return($ret);
	}

	# +------------------------------------------+
	# | ALTERA GRUPO QUE O USUARIO ESTÁ DEFINIDO |
	# +------------------------------------------+
	function AlteraGrupoCadastro($cod003, $tel004, $login){

		if(!empty($cod003)){
			sc_exec_sql("UpDate n004 Set gru004 = gru004 || ,'$cod003' Where tel004 = '$tel004' and login = '$login' and strpos(','||gru004||',', ',$cod003,') = 0");
		}
	}

	# +------------------------------------------+
	# | ENVIA A IMAGEM PARA O WHATSAPP           |
	# +------------------------------------------+
	function EnviaImagemZap($tel004, $img, $imd007, $instance, $token, $site){
		
		if(!empty($imd007)){
			$var_img=$img.$imd007;
			enviaZAPIMG ($tel004, $img, $imd007,  $instance, $token, $site);
			sleep(3);
		}
	}

	# +------------------------------------------+
	# | ENVIA MENSAGEM PARA O CONTATO            |
	# +------------------------------------------+
	function EnviaMensagemZap($tel004, $senderName, $men007, $instance, $token, $site){
		
		if(!empty($men007)){
			enviaZAP ($tel004, $senderName, $men007, $instance, $token, $site);
		}

	}

	# +------------------------------------------+   
	# | NOTIFICA O ATENDENTE OPÇÃO SELECIONADA   |
	# +------------------------------------------+
	function EnviaNotificaAte($des011, $tel011, $des007, $senderName, $instance, $token, $site, $datahora){
		
		if(!empty(trim($tel011))){
			$n011_men = "$des011\r\nO contato _".$senderName."_\r\ntelefone $tel011\r\nfeedback *".$des007."*\r\nData do recebimento : $datahora";
			enviaZAP ($tel011, $senderName, $n011_men , $instance, $token, $site);
		}
	}

	# +------------------------------------------+
	# | PEGA O ÚLTIMO NÚMERO DE MENSAGEM GRAVADA |
	# +------------------------------------------+
	function PegaNumeroUltimaMensagem($login){

		$l008=pg_fetch_array(sc_lookup("Select COALESCE (max( mnumber ),0) as contador From n008 Where login = '$login'"));
		return($l008["contador"]);
	}

	# +-------------------------------------------+
	# | ATUALIZA A AÇÃO DAS OPÇÕES NO REG USUARIO |
	# +-------------------------------------------+
	function n004_Atualiza_opc($opc, $tel, $login){

		sc_exec_sql ("UpDate n004 Set opc004 = '$opc' Where tel004 = '$tel' And login = '$login'");
		sc_exec_sql ("Delete From Only n013 Where login = '$login' and tel004 = '$tel'");
	}

	# +-------------------------------------------+
	# | ATUALIZA A AÇÃO DAS OPÇÕES NO REG USUARIO |
	# +-------------------------------------------+
	function n007_VoltaUltimoMenu($tel004, $login, $instance, $token, $site, $senderName, $loc_img, $datahora){
		
		try{
			n004_Atualiza_opc("menu_opc", $tel004, $login);
			$l007=pg_fetch_array(sc_lookup("Select pai007 From n007 Inner Join n004 on (n004.cod007=n007.cod007) Where tel004='$tel004' and n007.login='$login'"));
			n013_GravaMenuPai007($tel004, $l007["pai007"], $login, $instance, $token, $site, $senderName, $loc_img, $datahora);
		}catch(Exception $e){ 
			error_log("Error: $e->getMessage()");
		}
		
	}

	# +-------------------------------------------+
	# | MONTA MENU COM AS OÇÕES DA TABELA n013    |
	# +-------------------------------------------+
	function n013_EnviaMenu($tel004, $login, $senderName, $instance, $token, $site){
		$n007_men007 = n013_MontaTextoMenu("@nome, escolha uma opção : \r\n", $tel004, $login);
		if(!empty($n007_men007)){
			$n007_men007 .= "\r\nDigite : ";
			enviaZAP ($tel004, $senderName, $n007_men007, $instance, $token, $site);
		}
	}

	# +-------------------------------------------+
	# | ENVIA O MENU MONTADO PARA O WHATSAPP USER |
	# +-------------------------------------------+
	function n013_ExibeMenuTexto($nit013, $tel004, $login, $senderName, $instance, $token, $site, $loc_img, $datahora){
		#+----------------------------------------------------------+
		#|  SE HOUVER SOMENTE UMA OPÇÃO, MOSTRA DIRETO A INFORMAÇÃO |
		#+----------------------------------------------------------+
		
		if($nit013==1){
			n013_GravaFilhos007($tel004, $login, 1, $instance, $token, $site, $senderName, $loc_img, $datahora);
		}else{
			n013_EnviaMenu($tel004, $login, $senderName, $instance, $token, $site);
		}
		
	}

	# +-------------------------------------------+
	# | CAPTURA A OÇÃO DIGITADA PELO USER         |
	# +-------------------------------------------+
	function n013_GravaFilhos007($tel004, $login, $nit013, $instance, $token, $site, $senderName, $loc_img, $datahora){
		
		$l013=pg_fetch_array(sc_lookup("Select count(nit013) as nit013 From n013 Where tel004='$tel004' And login='$login'"));
		
		if($nit013>$l013["nit013"]){	
			$men007="@nome, á opção $nit013 é inválida, digite um número entre 1 a ".$l013["nit013"]."!!";	
			EnviaMensagemZap($tel004, $senderName, $men007, $instance, $token, $site);
	
		}else{
			$l007=pg_fetch_array(sc_lookup("Select n007.cod007, n007.des007, tip007, men007, imd007, cod003, n007.cod011, des011, tel011 From n013 Inner Join n007 on (n007.cod007=n013.cod007) Inner Join n011 on (n011.cod011=n007.cod011) Where tel004='$tel004' and n013.login='$login' and nit013=$nit013"));
			if(!$l007===false){
				$cod007 = $l007["cod007"]; // CODIGO DO FEEDBACK
				$des007 = $l007["des007"]; // DESCRIÇÃO DO FEEDBACK
				$tip007 = $l007["tip007"]; // TIPO DO REGISTRO
				$men007 = $l007["men007"]; // MENSAGEM
				$imd007 = $l007["imd007"]; // NOME DA IMAGEM
				$cod003 = $l007["cod003"]; // CODIGO DO GRUPO
				$cod011 = $l007["cod011"]; // CODIGO DO ATENDENTE
				$des011 = $l007["des011"]; // NOME DO ATENDENTE
				$tel011 = $l007["tel011"]; // TELEFONE DO ATENDENTE
		
				#+---------------------------------------------------+
				#| ATUALIZA O CODIGO DA ULTIMA SELEÇÃO DO USUARIO    |
				#+---------------------------------------------------+
				sc_exec_sql("UpDate n004 Set cod007 = $cod007 Where tel004='$tel004' and login='$login'");
		
				# +----------------------------------------+
		 		# | ENVIO DE IMAGENS                       |
		 		# +----------------------------------------+		 		 
				EnviaImagemZap($tel004, $loc_img.$imd007, $imd007, $instance, $token, $site);		
		
				# +---------------------------------------------------+
		 		# | DISPARA A MENSAGEM SELECIONADA PARA O USUARIO     |
		 		# +---------------------------------------------------+
				EnviaMensagemZap($tel004, $senderName, $men007, $instance, $token, $site);

				if($tip007==1){ # COM OPÇÕES DE ESCOLHAS
					n013_GravaMenuPai007($tel004, $cod007, $login, $instance, $token, $site, $senderName, $loc_img, $datahora);

				}elseif($tip007==2){ # SOMENTE INFORMAÇÃO SEM OPÇÕES DE FILHOS
				}elseif($tip007==3){ # ENQUETE			
				}elseif($tip007==4){ # ATENDENTE			
				}elseif($tip007==5){ # INTERAÇÕES
					n004_Atualiza_opc("interacao", $tel004, $login);
				}

				# +----------------------------------------+
		 		# | ALTERA INCLUI NO GRUPO DE USUARIOS     |
		 		# +----------------------------------------+
				AlteraGrupoCadastro($cod003, $tel004, $login);

				# +----------------------------------------+
		 		# | NOTIFICAÇÃO PARA O ATENDENTE           |
		 		# +----------------------------------------+
				EnviaNotificaAte($des011, $tel011, $des007, $senderName, $instance, $token, $site, $datahora);
			}
		}
	}

	# +-------------------------------+
	# | GRAVA AS OÇÕES NA TABELA n013 |
	# +-------------------------------+
	function n013_GravaItensMenu($cod007, $tel004, $des007, $login, $nit013){
		try{
			sc_exec_sql ("Insert Into n013(cod007, tel004, des007, login, nit013) Values ( $cod007, '$tel004', '$des007', '$login', $nit013)");
		}catch(Exception $e){ 
			error_log("Error: $e->getMessage()");
		}
	}

	# +------------------------------------------------------+
	# | GRAVA AS OÇÕES NA TABELA n013 PELA BUSCA DE PALAVRAS |
	# +------------------------------------------------------+
	function n013_GravaMenuChave($tel004, $login, $body, $senderName, $instance, $token, $site, $loc_img, $datahora){

		$texto  = str_replace(" ",",",transformaBB_B(tirarAcentos(trim($body))));
		$chv007 = "string_to_array(retira_acentuacao(upper(array_to_string(chv007,',','*'))),',','*')";
		$texbod = "string_to_array(retira_acentuacao(upper('$texto')),',','*')";

		$r007 = sc_lookup("Select n007.cod007, des007 From n007 Where tip007 = 2 and login = '$login' and $chv007 @> $texbod");
		error_log("Select n007.cod007, des007 From n007 Where tip007 = 2 and login = '$login' and $chv007 @> $texbod");
		if(empty($r007)){
			n013_GravaMenuTexto($tel004, $login, $body, $senderName, $instance, $token, $site, $loc_img, $datahora);

		}else{
	
			$n013_nit013 = 0;
			n004_Atualiza_opc("menu_opc", $tel004, $login);
			while($l007 = pg_fetch_array($r007)){

				$n007_cod007 = $l007["cod007"];
				$n007_des007 = $l007["des007"];
				$n013_nit013+=1;
		
				n013_GravaItensMenu ( $n007_cod007, $tel004, $n007_des007, $login, $n013_nit013);
			}	

			n013_ExibeMenuTexto ( $n013_nit013, $tel004, $login, $senderName, $instance, $token, $site, $loc_img, $datahora);
		}
	}

	# +-------------------------------------------------------------+
	# | GRAVA AS OÇÕES NA TABELA n013 PELA BUSCA DE PALAVRAS CHAVES |
	# +-------------------------------------------------------------+
	function n013_GravaMenuPai007 ( $tel004, $pai007, $login, $instance, $token, $site, $senderName, $loc_img, $datahora){
		
		# +----------------------------------------+
 		# | VERIFICA SE TEM ITEM CADASTRADO n013   |
 		# +----------------------------------------+
		if(!n013_TemItensCad($tel004, $login)){
			$pai007=0;	
		}

		# +----------------------------------------+
 		# | BUSCA OS ITENS PARA INCLUIR n013       |
 		# +----------------------------------------+
		$r007 = sc_lookup ("Select cod007, des007 From n007 Where pai007 = $pai007 And login = '$login' Order By pai007, des007");
		if(!empty($r007)){
	
			n004_Atualiza_opc("menu_opc", $tel004, $login);
			$n013_nit013 = 0;
	
			while($l007 = pg_fetch_array($r007)){
		
				$n007_cod007 = $l007['cod007'];
				$n007_des007 = $l007['des007'];
				$n013_nit013+=1;

				n013_GravaItensMenu($n007_cod007, $tel004, $n007_des007, $login, $n013_nit013);	
	
			}

			n013_ExibeMenuTexto($n013_nit013, $tel004, $login, $senderName, $instance, $token, $site, $loc_img, $datahora);
		}
	}

	# +---------------------------------------------------------------+
	# | GRAVA AS OÇÕES NA TABELA n013 PELA BUSCA DE PALAVRAS NO TEXTO |
	# +---------------------------------------------------------------+
 	function n013_GravaMenuTexto($tel004, $login, $body, $senderName, $instance, $token, $site, $loc_img, $datahora){
 		
 		$menzap = "@nome, não localizei *$body*. Tente outra vez, lembre-se *99* para _menu inicial_!!";
		$men007 = "string_to_array(retira_acentuacao(upper(replace(men007,' ',','))),' ','*')";
		$texto  = str_replace(" ",",",transformaBB_B(tirarAcentos(trim($body))));
		$texbod = "string_to_array(retira_acentuacao(upper('$texto')),' ','*')";

		$r007 = sc_lookup("Select cod007, des007 From n007 Where $men007 @> $texbod And n007.login = '$login' Order By pai007, des007");
		if(empty($r007)){
			EnviaMensagemZap($tel004, $senderName, $menzap, $instance, $token, $site);	
		}else{	

			$n013_nit013 = 0;
			n004_Atualiza_opc("menu_opc", $tel004, $login);	
		
			while($l007 = pg_fetch_array($r007)){

				$n007_cod007 = $l007["cod007"];
				$n007_des007 = $l007["des007"];		
				$n013_nit013 +=1;
		
				n013_GravaItensMenu($n007_cod007, $tel004, $n007_des007, $login, $n013_nit013);			
			}

			n013_ExibeMenuTexto($n013_nit013, $tel004, $login, $senderName, $instance, $token, $site, $loc_img, $datahora);
		}
 	}

	# +---------------------------------------------------------------+
	# | MONTA A LINHA DE OPÇÃO PARA SER ENVIADA PARA O WHATSAPP       |
	# +---------------------------------------------------------------+
 	function n013_MontaTextoMenu($texto, $tel004, $login){
 		
 		$retorno = ""; // False
		$r013 = sc_lookup ("Select nit013, n013.des007, emo015 From n013 Inner Join n007 on (n007.cod007=n013.cod007) Where tel004 = '$tel004' and n013.login = '$login' Order by nit013");
		if (!empty($r013)){

			$retorno = "$texto\r\n";	
	
			while($l013 = pg_fetch_array($r013)){
		
				$nit013 = $l013["nit013"]; // Nº DO ITEN DO MENU

				// EMOJI NÁO EXISTIR
				if(empty($l013["emo015"])){
					$emo015 = '◻️';
				}else{
					$emo015 = $l013["emo015"]; 
				}
				$des007 = $l013["des007"]; // DESCRIÇÃO DO ITEM
		
				$retorno .= "▪️$nit013 $emo015 $des007\r\n";		
			}
		}

		return $retorno;
 	}

	# +--------------------------------------------+
	# | VERIFICA SE TEM ITENS CADASTRADO PARA USER |
	# +--------------------------------------------+
 	function n013_TemItensCad($tel004, $login){ 		
 		$r013 = sc_lookup("Select count(nit013) as nit013 From n013 Where tel004='$tel004' And login='$login'");
	 	$l013 = pg_fetch_array($r013);
		if($l013==false or $l013["nit013"]==0){
			$ret=0;	
		}else{
			$ret=1;
		}

		return($ret);
 	}

	# +--------------------------------------------+
	# | VERIFICA SE TEM ITENS CADASTRADO PARA USER |
	# +--------------------------------------------+
 	function n014_GravaInteracao($tel004, $login, $body){
 		try{
			$l014 = pg_fetch_array(sc_lookup("Select cod004, cod007 From n004 Where tel004='$tel004' and login='$login'"));
			sc_exec_sql ("Insert Into n014 (cod004,cod007,tex014,dat014,hor014,login) Values(".$l014["cod004"].",".$l014["cod007"].",'$body',current_date,current_time,'$login')");
		}catch(Exception $e){
			error_log("Error: " . $e->getMessage()); 
		}
 	}

	# +------------------------------------------------+
	# | DISPARA O ENVIO DA MENSAGEM PARA O WHATAPPS    |
	# +------------------------------------------------+
	function enviaZAP ($var_phone, $var_nome, $var_conteudo, $var_instance, $var_token, $site){
		
		$var_conteudo = str_replace('@nome', ucwords(strtolower($var_nome)), $var_conteudo);
	    $var_conteudo = str_replace('@telefone', $var_phone, $var_conteudo);
	    $var_conteudo = str_replace('@user_name', $GLOBALS["nome"], $var_conteudo);
	    $var_conteudo = str_replace('@user_email', $GLOBALS["email"], $var_conteudo);
	
	   	# +-----------------------------------------+
	   	# | VERIFICA SE TEM QUE TIRAR O 9 DA FRENTE |
		# +-----------------------------------------+
		$r010=sc_lookup("Select ddd010 From n010 Where ddd010 = '".substr($var_phone,0,2)."'");
		if (empty($r010)){
			$var_phone = substr($var_phone,0,2).substr($var_phone,-8);
		}   
		$var_phone = '55'.$var_phone;
	
		$data = [
			'phone' => $var_phone,
			'body' => $var_conteudo,
		];

		$json = json_encode($data);
		$url = $site.$var_instance.'/message?token='.$var_token;

		$options = stream_context_create(['http' => [
			'method'  => 'POST',
			'header'  => 'Content-type: application/json',
			'content' => $json
			]
		]);

		$res = file_get_contents($url, false, $options);
	    $out = json_decode($res, true);

		return $out['sent'];
	}

	# +------------------------------------------------+
	# | DISPARA O ENVIO DA MENSAGEM PARA O WHATAPPS    |
	# +------------------------------------------------+
	function enviaZAPIMG ($var_phone, $var_img, $var_filename, $var_instance, $var_token, $site){

		# +--------------------------------------------+
	    # | VERIFICA SE TEM QUE TIRAR O 9 DA FRENTE    |
		# +--------------------------------------------+
		$rs010 = sc_lookup("Select ddd010 From n010 Where ddd010 = '".substr($var_phone,0,2)."'");
		if (empty($rs010)){
			$var_phone = substr($var_phone,0,2).substr($var_phone,-8);
		}
		$var_phone = '55'.$var_phone;

		$data = [
    		'chatId' => $var_phone."@c.us",
    		'body' => $var_img,
    		'filename' => $var_filename,
		];

		$json = json_encode($data);		
		$url = $site.$var_instance.'/sendFile?token='.$var_token;

		$options = stream_context_create(['http' => [
			'method'  => 'POST',
			'header'  => 'Content-type: application/json',
			'content' => $json
			]
		]);

		$res = file_get_contents($url, false, $options);
	    $out = json_decode($res, true);

		return $out['sent'];
	}

?>