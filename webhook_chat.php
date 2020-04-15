<?php

	date_default_timezone_set("America/Belem");
	header("Access-Control-Allow-Origin: *");

	include("conexao.class.php");
	include("funcoes.chat.php");
	include("funcoes.sist.php");

    $res = file_get_contents("php://input");
    $out = json_decode($res);

	if (!empty($out)){

		$instance = $out->instanceId;
		$rs = pg_fetch_array(sc_lookup("Select login, instance, token, contador, site, txtopcoes, pedmail, pedcida, pednome, peddtn, array_to_string(feedback,',','*') as feedback, array_to_string(sairenvio,',','*') as sairenvio, array_to_string(cadbase,',','*') as cadbase, tbvsec, email, name, n003sai, n003cad From sec_users Where instance='$instance'"));

		# +----------------------------------------------------------+
		# | DEFINE VARIAVEIS DO USUARIO                              |
		# +----------------------------------------------------------+
		$login     = $rs["login"];
		$instance  = $rs["instance"];
		$token     = $rs["token"];
		$contador  = $rs["contador"];
		$site      = $rs["site"];
		$txtopcoes = $rs["txtopcoes"];
		$pedmail   = $rs["pedmail"];
		$pedcida   = $rs["pedcida"];
		$pednome   = $rs["pednome"];
		$peddtn    = $rs["peddtn"];
		$feedback  = explode(",",str_replace(", ",",",tirarAcentos(trim($rs["feedback" ]))));
		$sairenvio = explode(",",str_replace(", ",",",tirarAcentos(trim($rs["sairenvio"]))));
		$cadbase   = explode(",",str_replace(", ",",",tirarAcentos(trim($rs["cadbase"  ]))));
		$tbvsec    = $rs["tbvsec"];
		$email     = $rs["email"];
		$nome      = $rs["name"];
		$n003sai   = $rs["n003sai"];
		$n003cad   = $rs["n003cad"];

		# +----------------------------------------------+
		# | VERIFICA SE EXISTE CONTEUDO A SER PROCESSADO |
		# +----------------------------------------------+
        if(!empty($out->messages)){

    	# +------------------------------------------+
			# | MONTA INDICE DE TEXTO DE MENSAGENS       |
			# +------------------------------------------+
			$men_usu = array(1 => "Em quem posso ajudar, @nome ? digite o que procura para ficar mais fácil!",
							 2 => "Não conseguir localizar nada referente a essa palavra, a busca e somente para palavra! Tente novamente.",
							 3 => "Qual sua cidade ?",
							 4 => "Qual seu e-mail ?",
							 5 => "Qual seu nome ? Digite nome e sobrenome, ex. Roberto Alves",
							 6 => "Seu nome deve ter pelo menos 7 letras! ex. Roberto Alves.! Para não fornecer, digite *Não* ou *N*",
							 7 => "@nome, Sua Solicitação para *sair da lista* de _recebimento_ de mensagens, realizada com sucesso. Caso queria voltar a receber promoções e avisos, digite *RECEBER*, Obrigado.!!",
							 8 => "@nome, seu telefone já estava programado para *não receber mensagens*. Caso queria voltar a receber promoções e avisos, digite *RECEBER*, Obrigado.!!",
							 9 => "@nome, endereço de e-mail inválido! Para não fornecer, digite *Não* ou *N*",
							10 => "@nome, obrigado pela sua contribuição, isso é muito importante para nós !!",
							11 => "@nome, liberado o recebimento de promoções e avisos para o seu *whatsapp*. Obrigado!!",
							12 => "@nome, seu *whatsapp* já está programado para receber promoções e avisos. Obrigado!!",
							13 => "Qual sua data de Nascimento ? use *DD/MM/AAAA*",
							14 => "Data inválida! use *DD/MM/AAAA*, Para não fornecer, digite *Não* ou *N*",
							15 => "Nome da Cidade, não pode ser vazia! Para não fornecer, digite *Não* ou *N*"
						);

			# +------------------------------------------+
 			# | FLAG GERAIS DA ROTINA                    |
 			# +------------------------------------------+
			$producao     = 1;
			$pula_busca   = 0;
			$nova_visita  = 1;

			# +------------------------------------------+
			# | LÊ O CONTEUDO DO $_POST                  |
			# +------------------------------------------+
      foreach($out->messages as $r){
				$id           = $r->id;            #":"false_17472822486@c.us_DF38E6A25B42CC8CCE57EC40F",
				$body         = $r->body;          #":Ok!",
				$type         = $r->type;          #":"chat",
				$senderName   = $r->senderName;    #":"Ilya",
				$fromMe       = $r->fromMe;        #":true,
				$author       = $r->author;        #":"17472822486@c.us",
				$time         = $r->time;          #":1504208593,
				$chatId       = $r->chatId;        #":"17472822486@c.us",
				$messageNumber= $r->messageNumber; #":100
			}

			# +-----------------------------------------------------+
			# | INSTRUÇÃO PARA O INSERT SQL n008                    |
			# +-----------------------------------------------------+
			$n008_insert = "Insert Into n008 (id008, typ008, nam008, fme008, aut008, cha008, bod008, login, tim008, mnumber) Values ";
			$n008_values = "";
			$n008_exec   = "";

			# +-----------------------------------------------------+
			# | SOMENTE PROCESSA OS RECEBIDOS DE OUTRAS CONTATOS    |
			# +-----------------------------------------------------+
			$datahora = gmdate("Y-m-d H:i:s",$r->time+date("Z")); // DATA LOCAL

			# +-----------------------------------------------------+
			# | RESPONDE SOMENTE AS MENSAGEM RECEBIDAS PELO CONTATO |
			# +-----------------------------------------------------+
			if($fromMe<>true){

				# +----------------------------------+
				# | LOCALIZAÇÃO DA IMAGEM PARA ENVIO |
				# +----------------------------------+
				$loc_sit = "https://www.h2r.eng.br/mkt/_lib/file/img";
				$loc_img = "/imd001/$login/";

				# +-----------------------------------------+
				# | VERIFICA SE TEM QUE TIRAR O 9 DA FRENTE |
				# +-----------------------------------------+
				$tel004 = substr(soNumero($author),2);
				if(strlen($tel004)==10){
					$tel004 = substr_replace($tel004, "9", 2, 0);
				}

				# +-----------------------------------------------+
				# | MONTA STRING DE query PARA VERIFICAR CONTATO  |
				# +-----------------------------------------------+
				$n004_select = "Select cod004, cod007, opc004, atu004, sai004, dtc004, COALESCE(date_trunc('second', hrc004)::time+'00:30','00:00') as hrc004, qtc004, oke004, okc004, okn004, okd004 From n004";
				$n004_where  = "Where tel004 = '$tel004' And login = '$login'";

				# +-----------------------------------------------+
				# | VERIFICA SE O CONTATO EXISTE NA BASE DE DADOS |
				# +-----------------------------------------------+
				$r004 = sc_lookup($n004_select." ".$n004_where );
				if (empty($r004)){
					$n004_existe = 0;             // Não, Ele não está Cadastrado
				}else{
					$l004 = pg_fetch_array($r004);
					$n004_existe = true;            // Sim, Ele está Cadastrado
					$n004_cod004 = $l004["cod004"]; // Código do contato no sistema
					$n004_cod007 = $l004["cod007"]; // Código do Ultimo FeedBack do Contato
					$n004_opc004 = $l004["opc004"]; // Em Qual Opção Está o Contato
					$n004_atu004 = $l004["atu004"]; // Atualiza Cadastro do Contato
					$n004_sai004 = $l004["sai004"]; // Marca contato para não receber Mensagem
					$n004_dtc004 = $l004["dtc004"]; // Data do Contato
					$n004_hrc004 = strtotime($l004["hrc004"]); // Hora do Contato
					$n004_qtc004 = $l004["qtc004"]; // Quantidade de visitas
					$n004_oke004 = $l004["oke004"]; // Solicitação do E-mail
					$n004_okc004 = $l004["okc004"]; // Solicitação da Cidade
					$n004_okn004 = $l004["okn004"]; // Solicitação do Nome
					$n004_okd004 = $l004["okd004"]; // Solicitação Data de Nascimento
				}

				# +------------------------------------------------+
				# | VERIFICA SE A ULTIMA VISITA FOI NO DIA DE HOJE |
				# +------------------------------------------------+
				if($n004_existe){
					if(date("Y-m-d") > $n004_dtc004){
						sc_exec_sql ("UpDate n004 Set qtc004 = qtc004+1, dtc004 = current_date, hrc004 = current_time, opc004='' " . $n004_where);
					}elseif(date("H:i") > date("H:i",$n004_hrc004)){
						sc_exec_sql ("UpDate n004 Set hrc004 = current_time, opc004='' " . $n004_where);
					}else{
						$nova_visita = 0; // False
					}
				}

				# +-----------------------------------------+
				# | INICIALIZA A VARIAVEL DA MENSAGEM       |
				# +-----------------------------------------+
				$n007_men007 = "";
				$bodyUPR     = tirarAcentos(trim($body));

				# +-----------------------------------------+
				# | VERIFICA ATUALIZAÇÃO DE CADASTRO        |
				# +-----------------------------------------+
				if(!$n004_existe){
					$n004_opc004 = "";
					$n007_men007 = $tbvsec;
					sc_exec_sql ("Insert Into n004 ( des004, tel004, login, gru004, dcs004, atu004, qtc004) Values (Upper('".$senderName."'),'".$tel004."','$login','" .'$n003cad'. "',current_date,'N', 1)");

					# +--------------------------------------------+
					# | ATUALIZAÇÃO DO CADASTRO	                   |
					# +--------------------------------------------+
				}elseif($n004_opc004=="atu_cad"){

					# +--------------------------------------------+
					# | VERIFICA QUAL CAMPO TEM QUE SER ATUALIZADO |
					# +--------------------------------------------+
					$info = array(
									array($pednome, $n004_okn004, 'des004', 'okn004', $body, $bodyUPR, $n004_where, (strlen(trim($body))>7)                         , $men_usu[6] ),
									array($pedmail, $n004_oke004, "mai004", "oke004", $body, $bodyUPR, $n004_where, (filter_var(trim($body), FILTER_VALIDATE_EMAIL)), $men_usu[9] ),
									array($pedcida, $n004_okc004, "cid004", "okc004", $body, $bodyUPR, $n004_where, (!empty($body))                                 , $men_usu[15]),
									array($peddtn , $n004_okd004, "dtn004", "okd004", $body, $bodyUPR, $n004_where, ValidaData($bodyUPR)                            , $men_usu[14])
								);
					$n007_men007 = AtuCampoContato($info);

					# +-------------------------------------------+
					# | PEDE A CONTEUDO DO CAMPO OU EXIBE MENU    |
					# +-------------------------------------------+
					if(empty($n007_men007)){

						$n007_men007 = (MarcaCampo_S($bodyUPR, $n004_where, array(array('NM','okn004',$men_usu[5]),array('EM','oke004',$men_usu[4]),array('CI','okc004',$men_usu[3]),array('DN','pkd004',$men_usu[13]))));

						if($bodyUPR=="SAIR" Or ($n004_okn004.$n004_oke004.$n004_okc004.$n004_okd004=="NNNN") and ($bodyUPR=="N" or $bodyUPR=="NAO")){
							$n004_opc004 = "busca";
							$n007_men007 = $men_usu[1];

						}elseif(empty($n007_men007)){

							$n007_men007  = "Escolha o campo !\r\n\r\n";
							$n007_men007 .= "📓 *NM* - Atualiza o seu nome;\r\n\r\n";
							$n007_men007 .= "📧 *EM* - Atualiza E-mail;\r\n\r\n";
							$n007_men007 .= "📅 *DN* - Data de Nascimento;\r\n\r\n";
							$n007_men007 .= "🏘️ *CI* - Atualiza a cidade.\r\n\r\n.";
							$n007_men007 .= "🚪 *Sair* - Parar Atualizações";
						}
					}
				}else{
					# +----------------------------------------+
					# | INICIO DO CHATBOTS COM O CONTATO       |
					# +----------------------------------------+
					if(in_array($bodyUPR, $feedback)){
						$n004_opc004 = "busca";
						if($n004_existe){
							$n007_men007 = $body;
							if($nova_visita){
								$n007_men007 .= "\r\n$senderName, bom te ver novamente aqui, ";
							}else{
								$n007_men007 .= "\r\n$senderName, vamos re-iniciar a nossa busca, ";
							}
							$n007_men007 .= "\r\n$txtopcoes";
						}
					}else{
						# +----------------------------------------+
						# | SOLICITAÇÃO DE SAIDA DA LISTA DE ENVIO |
						# +----------------------------------------+
						if($n004_existe and in_array($bodyUPR, $sairenvio)){
							$n004_opc004 = "";
							if($bodyUPR=="RECEBER"){
								if($n004_sai004=="S"){
									$n007_men007=$men_usu[11];
									sc_exec_sql ("UpDate n004 Set sai004 = 'N' ". $n004_where);
								}else{
									$n007_men007=$men_usu[12];
								}
							}elseif($n004_sai004=="S"){
								$n007_men007=$men_usu[8];
							}else{

								$n007_men007=$men_usu[7];
								sc_exec_sql ("UpDate n004 Set sai004 = 'S' ". $n004_where);
								# +------------------------------------+
								# | INCLUI O CONTATO NO GRUPO DE SAIDA |
								# +------------------------------------+
								if(!empty($n003sai)){
									AlteraGrupoCadastro($n003sai, $tel004, $login);
								}
							}
						}else{
							# +----------------------------------------+
							# | SOLICITAÇÃO DE CADASTRO DO CONTATO     |
							# +----------------------------------------+
							if(in_array($bodyUPR,$cadbase)){
								$n004_opc004 = "";
								if($n004_existe){
									$n007_men007='@nome, seu telefone já estava cadastrado, Obrigado!!';
									if($n004_atu004=='N'){
										$n007_men007 .= "\r\n\r\nDeseja atualizar o cadastro ? \r\nDigite : ( *S* / *N* )";
										$n004_opc004 = "atu_cad";
										sc_exec_sql ("UpDate n004 Set oke004 = 'N', okc004 = 'N', okn004 = 'N', okd004 = 'N' " . $n004_where);
									}
								}else{
									$n007_men007="@nome, Sua Solicitação para *Cadastramento* realizada com sucesso, Obrigado!!";
									sc_exec_sql ("Insert Into n004 ( des004, tel004, login, gru004, dcs004, atu004, qtc004) Values (Upper('$senderName'),'$tel004','$login','$n003cad', current_date,'N', 1)");
								}
							} # if(in_array($feedback, $cadbase))
						} # if($n004_existe and in_array($feedback, $sairenvio) )
					} # if(in_array($feedback, $feedback))
				} # Verifica atualização de cadastro

				#  +-----------------------------------------+
				#  |  ENVIA MENSAGEM CONFIRMANDO SOLICITAÇÃO |
				#  +-----------------------------------------+
				if(!empty($n007_men007)){
					n004_Atualiza_opc($n004_opc004, $tel004, $login);
					EnviaMensagemZap($tel004, $r->senderName, $n007_men007, $instance, $token, $site);
				}

				/* +-----------------------------------------+
				 * | VERIFICA SE TEM MENU MONTADO            |
				 * +-----------------------------------------+
				 *********************************************/
				if(empty($n007_men007)){
					if($bodyUPR==99 or $bodyUPR=="AJUDA" or $bodyUPR=="?"){
						n013_GravaMenuPai007($tel004, 0, $login, $instance, $token, $site, $senderName, $loc_sit.$loc_img, $datahora);

					}elseif($body=="#"){
						n007_VoltaUltimoMenu($tel004, $login, $instance, $token, $site, $senderName, $loc_sit.$loc_img, $datahora);

					}else{
						if($n004_opc004=="menu_opc"){
							if(is_numeric($body)){
								n013_GravaFilhos007($tel004, $login, $body, $instance, $token, $site, $senderName, $loc_sit.$loc_img, $datahora);

							}else{
								n013_GravaMenuChave($tel004, $login, $body, $senderName, $instance, $token, $site, $loc_sit.$loc_img, $datahora);
							}
						}elseif($n004_opc004=="busca"){
							n013_GravaMenuTexto($tel004, $login, $body, $r->senderName, $instance, $token, $site, $loc_sit.$loc_img, $datahora);
						}else{
							n007_VoltaUltimoMenu($tel004, $login, $instance, $token, $site, $senderName, $loc_sit.$loc_img, $datahora);
						}
					}
				}

			} # (formMe <> true)

			# +-----------------------------------------------------------------------------+
			# | TESTE SE VAI FAZER A GRAVAÇÃO DA MENSAGEM NA BASE DE DADOS                  |
			# +-----------------------------------------------------------------------------+
			if($producao) {
				# +--------------------------------------------------------------------------+
				# | MONTA STRING de VALORES PARA O INSERT NO HISTORICO DE MENSGAEM RECEBIDAS |
				# +--------------------------------------------------------------------------+
				$body=str_replace("$", ".", $body);
				$n008_values = $n008_values . "('$id', '$type', $$$senderName$$, '$fromMe', $$$author$$, '$chatId', $$$body$$, '$login','$datahora',$messageNumber),";
				# +------------------------------------------------------------------+
				# | MONTA A INSTRUÇÃO DO INSERT PARA O BANCO - HISTORICO DE MENSAGEM |
				# +------------------------------------------------------------------+
				$n008_exec = $n008_insert . " " . substr($n008_values,0,strlen($n008_values)-1);
				try {
					sc_exec_sql ( $n008_exec );
					sc_exec_sql ("UpDate sec_users Set contador = $messageNumber Where login = '$login'");
				}catch(Exception $e){
					error_log("Error: " . $e->getMessage());
				}
			}

        }elseif(!empty($out->ack)){

			# +-----------------------------------------------+
			# | NOTIFICAÇÃO DE RECEBIMENTO E OU LEITURA       |
			# +-----------------------------------------------+
        	foreach($out->ack as $a){

        		$status        = $a->status;
        		if(isset($a->messageNumber)){
					$id            = $a->id;
					$messageNumber = $a->messageNumber;
					$chatId        = $a->chatId;

					# +-----------------------------------------------+
					# | VERIFICA O TIPO DO STATUS                     |
					# +-----------------------------------------------+
					if($status=="delivered")  { $GravaStatus = "rec008";
					}elseif($status=="viewed"){	$GravaStatus = "viu008";
					}else{
						error_log("Erro : $status / ack ");
					}

					# +-----------------------------------------------+
					# | ATUALIZA STATUS DA MENSAGEM                   |
					# +-----------------------------------------------+
					if(!empty($GravaStatus)){
						sc_exec_sql("UpDate n008 Set $GravaStatus=1 Where login='$login' and mnumber=$messageNumber");
					}
				}
			}
        }

	}else{

        http_response_code ( 200 );
        exit();

	}

?>
