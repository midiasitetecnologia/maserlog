<?php

function rgGetMsgRetornoAPI($cod_retorno)
{
	$retorno = [
		//
		// CÓDIGOS: 'A' - Autenticação: (usuários, dispositivos)
		//
		'A100' => 'Usuário autenticado com sucesso.',
		'A200' => 'Conta de usuário ou senha inválidos.',
		'A201' => 'Token de ativação de conta inválido para "$email"',
		'A202' => 'Ops! Ocorreu um erro na atualização da sua senha.',
		'A203' => 'Ops! Não foi possível efetuar o "login" na sua conta.',
		'A204' => 'Ops! Não foi possível efetuar o "logout" da sua conta.',
		'A205' => 'API Key inválida para uso com a plataforma Maser.',
		'A206' => 'Conta de usuário não pode ser ativada porque foi bloqueada.',
		'A207' => 'Dispositivo não encontrado. ID: $id_disp',
		'A208' => 'Ops! Não foi possível ativar a sua conta. Tente mais tarde.',
		'A209' => 'Registro do usuário não encontrado. ID #$user_id',
		'A300' => 'ID do dispositivo está vazio.',
		'A301' => 'Não foi possível registrar o dispositivo: $id_disp',
		'A302' => 'Não foi possível atualizar os dados do dispositivo: $id_disp',

		//
		// CÓDIGOS: 'B' - Tabelas: (motoristas, veículos, ...)
		//
		'B100' => 'Importação de clientes concluída com SUCESSO ($funcao): ' .
			'Emp: $empresa | Registros: $cont_regs | Novos: $cont_novos | Atualizados: $cont_atlz',
		'B101' => 'Importação de motoristas concluída com SUCESSO ($funcao): ' .
			'Emp: $empresa | Registros: $cont_regs | Novos: $cont_novos | Atualizados: $cont_atlz',
		'B201' => 'Você não está vinculado a um veículo neste momento. ' .
			'Motorista sem veículo não pode atender solicitações.',
		'B202' => 'Ops! Não foi possível vincular o motorista ao veículo.',
		'B203' => 'Cadastro do motorista não encontrado para usuário: $email',
		'B204' => 'Erro ao inserir registro na tabela CLIENTES. Empresa: $empresa | Código: $codigo | Nome: $nome',
		'B205' => 'Erro ao atualizar registro na tabela CLIENTES. Empresa: $empresa | Código: $codigo | Nome: $nome',
		'B206' => 'Importação de clientes concluída com ERROS ($funcao): ' .
			'Emp: $empresa | Registros: $cont_regs | Novos: $cont_novos | Atualizados: $cont_atlz | Erros: $cont_erros',
		'B207' => 'Cadastro da empresa não encontrado. Código: $empresa',
		'B208' => 'Importação de motoristas concluída com ERROS ($funcao): ' .
			'Emp: $empresa | Registros: $cont_regs | Novos: $cont_novos | Atualizados: $cont_atlz | Erros: $cont_erros',
		'B209' => 'Erro ao inserir registro na tabela MOTORISTAS. Empresa: $empresa | CPF: $cpf | Nome: $nome',
		'B210' => 'Erro ao atualizar registro na tabela MOTORISTAS. Empresa: $empresa | CPF: $cpf | Nome: $nome',
		'B211' => 'Cadastro do veículo não encontrado. Placa: "$placa"',
		'B212' => 'Ops! Não foi possível gravar o motorista no registro do veículo.',
		'B213' => 'Ops! Não foi possível atualizar a localização no cadastro do motorista.',
		'B214' => 'Ops! Não foi possível atualizar a localização do cadastro do veículo.',
		'B215' => 'Ops! Não foi possível fazer "login" na sua conta de motorista.',
		'B216' => 'Ops! Não foi possível fazer "logout" da sua conta de motorista.',
		'B217' => 'Ops! Você não pode fazer isso. Seu cadastro de motorista está inativo.',
		'B218' => 'A foto do compartimento de carga é obrigatória quando o veículo não está vazio.',
		'B219' => 'Ops! Não foi possível atualizar os dados de ocupação do veículo.',
		'B220' => 'Código de baldeação inválido para veículo: $placa',
		'B221' => 'A baldeação deve ser feita para outro veículo.',
		'B222' => 'Erro ao desconectar automaticamente o motorista: $erro',
		'B223' => 'Cadastro do motorista não encontrado. ID: $motorista_id',

		//
		// CÓDIGOS: 'E' - Solicitações: (coletas, entregas, ...)
		//	

		'E100' => 'Importação de coletas concluída: ' .
			' Emp: $empresa | Registros: $cont_regs | Novos: $cont_novos | Existentes: $cont_exist | Erros: $cont_erros',
		'E101' => 'Exportação de coletas concluída com SUCESSO: Emp: $empresa | Registros: $cont_regs',
		'E102' => 'Marcação das coletas e entregas exportadas concluída. ' .
			'Emp: $empresa | Registros: $cont_regs | Coletas: $cont_coletas | Entregas: $cont_entregas | Erros: $cont_erros',
		'E103' => 'Atualização dos campos da nota de frete concluída. Emp: $empresa | ' .
			' Registros: $cont_regs | Atualizados: $cont_atlz | Erros: $cont_erros ',

		'E200' => 'Solicitação de coleta não encontrada. ID: $coleta_id',
		'E201' => 'Solicitação de coleta não encontrada. Empresa: #$empresa | Número: #$coleta',
		'E202' => 'Solicitação já finalizada não pode ser cancelada.',
		'E203' => 'Esta solicitação já consta como cancelada.',
		'E204' => 'Não há solicitações de coleta / entrega para você.',
		'E205' => 'O deslocamento pode não ser informado porque a coleta ainda não foi autorizada. Status atual: $status.',
		'E206' => 'Ops! Ocorreu um erro na atualização do status da solicitação. ID: #$coleta_id',
		'E207' => 'O deslocamento não pode ser informado porque a entrega ainda não foi autorizada. Status atual: "$status".',
		'E208' =>  'Você poderá iniciar o atendimento desta solicitação... somente depois que' .
			' finalizar a solicitação que está em andamento: #$numero',
		'E209' => 'Você não pode fazer solicitações. Sua conta de usuário não está ativa.',
		'E210' => 'Não foi possível cancelar esta solicitação porque ocorreu um erro na atualização da situação.',
		'E211' => 'Esta solicitação não pode ser baldeada ou transferida. Status atual: $status',
		'E212' => 'Antes de informar sua chegada, informe que você se deslocou para o local da coleta.',
		'E213' => 'Antes de informar o início do atendimento, informe a sua chegada ao local da coleta.',
		'E214' => 'Antes de finalizar a coleta você deve informar que o atendimento foi iniciado.',
		'E215' => 'Informe o motivo do cancelamento desta coleta.',
		'E216' => 'Erro ao marcar a COLETA como "exportada". ID: $coleta_id',
		'E217' => 'Erro ao marcar a ENTREGA como "exportada". ID: $coleta_id',
		'E218' => 'Antes de informar sua chegada, informe que você se deslocou para o local da entrega.',
		'E219' => 'Antes de informar o início do atendimento, informe a sua chegada ao local da entrega.',
		'E220' => 'Antes de finalizar a entrega você deve informar que o atendimento foi iniciado.',
		'E221' => 'Informe o nome da pessoa que recebeu a entrega.',
		'E222' => 'Todos os campos da nota fiscal são obrigatórios.',
		'E223' => 'Esta nota fiscal já foi incluída nesta coleta.',
		'E224' => 'Ops! Não foi possível incluir esta nota fiscal na coleta.',
		'E225' => 'Esta nota fiscal não pertence a esta coleta.',
		'E226' => 'Ops! Não foi possível excluir da coleta esta nota fiscal.',
		'E227' => 'Informe o local de coleta e o local de entrega da carga.',
		'E228' => 'A solicitação #$coleta_id não é do tipo "Contrato"... por isso não aceita a inclusão de comandas.',
		'E229' => 'A solicitação #$coleta_id aceita a inclusão de comandas quando o status for = "C4" (Atendimento iniciado). Status atual: "$status".',
		'E230' => 'Ops! Não foi possível incluir esta comanda.',
		'E231' => 'Ops! Não foi possível atualizar os dados desta comanda. ID #$coleta_id.',
		'E232' => 'Ops! Não foi possível excluir esta comanda. ID #$coleta_id.',
		'E233' => 'Você poderá iniciar o atendimento desta comanda... somente depois que finalizar a' .
			' comanda que está em andamento. ID #$coleta_id',
		'E234' => 'Antes de iniciar o expediente, informe a sua chegada ao local da coleta. ',
		'E235' => 'Antes de finalizar o expediente, encerre as comandas que estão em andamento.',
		'E236' => 'Você pode iniciar o expediente somente em solicitações de coletas fixas do tipo "contrato".',
		'E237' => 'Você pode finalizar o expediente somente em solicitações de coletas fixas do tipo "contrato".',
		'E238' => 'O expediente não pode ser finalizado porque não foi iniciado.',
		'E239' => 'A situação desta solicitação não permite descarga ou a descarga já foi realizada.',
		'E240' => 'A coleta foi realizada, então... envie a foto do compartimento de carga e informe a ocupação do veículo.',
		'E241' => 'Esta entrega não pode ser autorizada. O status da solicitação deve estar como: "coleta realizada", "carga definida" ou "entrega autorizada".',
		'E242' => 'Se esta nota fiscal foi entregue, envie a imagem do recibo e, se não foi entregue, informe o motivo.',
		'E243' => 'Ops! Não foi possível gravar a imagem do recibo da nota fiscal.',
		'E244' => 'Informe a duração prevista para a realização da coleta.',
		'E245' => 'Informe a duração prevista para a realização da entrega.',
		'E246' => 'Esta comanda não pode ser excluída porque já foi iniciada.',
		'E247' => 'Para finalizar esta entrega, envie a foto do canhoto (assinado pelo recebedor) de cada nota fiscal entregue. ' .
			'Se alguma nota não foi entregue, utilize a opção "Entrega Parcial".',
		'E248' => 'Esta nota fiscal não foi emitida para o destinatário: $nome',
		'E249' => 'A baldeação desta coleta não foi autorizada para este veículo.',
		'E250' => 'Se a coleta não foi realizada, exclua as notas fiscais informadas nesta solicitação.',
		'E251' => 'Informe o tipo de veículo necessário para atender a esta coleta.',
		'E252' => 'Antes de informar "sem atendimento", informe a sua chegada ao local da coleta.',
		'E253' => 'Informe o motivo do não atendimento desta coleta.',
		'E255' => 'Coleta com movimentação não pode ser cancelada como "sem deslocamento".',

		'E256' => 'Erro ao atualizar campos da nota de frete na solicitação: ID #$coleta_id',
		'E257' => 'Ops! Esta operação não pode ser executada. Esta solicitação não consta como "em deslocamento".',
		'E258' => 'Chave de acesso da nota fiscal está incorreto.',

		'E259' => 'Você não pode informar sua chegada a "$local_atual" porque você ainda não' .
			' finalizou as solicitações em "$outro_local".',
		'E260' => 'Solicitação com entrega realizada. Esta operação não pode ser desfeita.',
		'E261' => 'Solicitação com status => "$status". Esta operação não pode ser desfeita.',
		'E262' => 'Encerrou o prazo para desfazer a operação.',
		'E263' => 'Você não pode cancelar o atendimento porque já existem comandas para este contrato.',
		'E264' => 'Esta não é uma solicitação do tipo "Multi-destinos". ID #$coleta_id',
		'E265' => 'Ops! Não foi possível incluir solictação auxiliar para nota fiscal: ' . '$nro_nota  Local entrega: $local_entrega.',
		'E266' => 'Local de entrega não encontrado para a nota fiscal: ' . '$nro_nota  Empresa: $empresa  Código: $cod_local_entrega.',
		'E267' => 'Envie uma foto do romaneio de carga com a relação de notas fiscais da coleta.',
		'E268' => 'Envie uma foto do romaneio de carga com as assinaturas para comprovação de entrega.',
		'E269' => 'Informe as notas fiscais coletadas... porque esta carga será entregue em locais diferentes.',
		'E270' => 'Informe se existe uma ou mais notas fiscais com fins comerciais nesta coleta.',
		'E271' => 'O nome do arquivo do recibo enviado difere do que está gravado nesta nota fiscal. Envie o mesmo nome ou faça o upload de um novo arquivo.',
		'E272' => 'Esta solicitação não pode ser transferida porque possui comandas em situação de deslocamento.',
		'E273' => 'Não foi possível executar a transferência. Ocorreu um erro ao atualizar o registro da solicitação ou das comandas.',
		'E274' => 'Informe o motivo pelo qual a entrega não foi realizada.',
		'E275' => 'Informe o motivo em cada nota fiscal que não foi entregue.',
		'E277' => 'Reentrega ou devolução permitida apenas para solicitações Diárias ou Distribuição multi-destinos.',
		'E278' => 'A solicitação origem não consta como não realizada ou parcialmente realizada.',
		'E279' => 'Uma ou mais solicitações de reentrega já foram geradas para esta solicitação.',
		'E280' => 'Não foi possível criar a solicitação de reentrega.',
		'E281' => 'Não foi possível marcar a solicitação como reentrega gerada. ID #$coleta_id',
		'E282' => 'Não é possível desfazer a operação porque a solicitação de reentrega já foi gerada.',
		'E283' => 'Para "Entrega Parcial" você deve informar o motivo de não entrega somente nas notas que não foram entregues.',
		'E284' => 'Solicitação multi-destinos não pode ser encerrada porque existem solicitações auxiliares em aberto.',
		'E285' => 'Você não pode desfazer esta operação porque a solicitação consta como descarregada.',
		'E286' => 'Esta entrega não pode ser alocada para este veículo porque o status da solicitação não está como "coleta realizada".',
		'E287' => 'Reentrega permitida depois que a carga for descarregada no pavilhão.',
		'E288' => 'Motivo da não entrega inválido para notas fiscais. Motivo: $mot_nao_entrega',
		'E289' => 'Nenhuma nota fiscal em condições de ser adicionada ou substituída na solicitação de reentrega que será gerada.',
		'E290' => 'Existem notas fiscais não entregues nesta solicitação. Informe a lista de notas a serem adicionadas ou substituidas na solicitação de reentrega.',
		'E291' => 'Operação de "Entrega Parcial" ou "Entrega Não Realizada" não permitida para comandas de contratos.',
		'E292' => 'Solicitação de reentrega não pode ser cancelada. Se você não vai mais realizar a coleta, entre em contato com o escritório para resolver a situação.',
		'E293' => 'Uma solicitação de reentrega não pode ser excluída.',
		'E294' => 'Para marcar como "Entrega Consolidada" a solicitação deve estar aberta e na etapa de Entrega.',

		'E300' => 'Importação: Erro ao criar registro da solicitação. Emp: $empresa | Nro: $numero',
		'E301' => 'Erro ao criar registro da solicitação. ID coleta fixa: #$coleta_fixa_id ' .
			' |  Cliente: #$cod_cliente  |  Loc. coleta: $cod_loc_coleta  |  Loc. entrega: $cod_loc_entrega',
		'E302' => 'Coleta fixa bloqueada para hoje: ID #$coleta_fixa_id  | Cliente: $cod_cliente ' .
			' |  Loc. coleta: $cod_loc_coleta  |  Loc. entrega: $cod_loc_entrega',
		'E303' => 'Motorista inativo não pode ser atribuído a uma solicitação.',
		'E304' => 'Ops! Ocorreu um erro na atualização do campo motorista desta solicitação. ID: $coleta_id',
		'E305' => 'O motorista previsto para realizar a coleta não pode ser atribuído, pois a solicitação já está em andamento.',
		'E306' => 'O motorista previsto para realizar a entrega não pode ser atribuído, pois a solicitação já está em andamento.',
		'E307' => 'O motorista previsto para realizar a entrega não pode ser atribuído a uma solicitação multi-destinos com coleta realizada.',

		//
		// CÓDIGOS: 'N' - Notificações
		//				
		'N202' => 'Erro na atualização desta notificação.',

		//
		// CÓDIGOS: 'Z' - Códigos de retorno geral
		//
		'Z100' => 'Operação executada com sucesso.',
		'Z101' => 'Operação executada com sucesso. Não há dados para retornar.',
		'Z102' => 'Operação executada com sucesso. Registro já existe.',
		'Z103' => 'Operação executada com sucesso. Sem necessidade de atualização.',
		'Z200' => 'Falha na execução da operação.',
		'Z201' => 'Acesso negado: aplicação precisa de atualização.',
		'Z202' => 'Parâmetro inválido: <especificar o parâmetro e valores esperados>.',
		'Z203' => 'Informação obrigatória não cadastrada: "$info".',
		'Z300' => 'Erro no conteúdos dos campos.',
		'Z400' => 'Execução interrompida pela exceção: '
	];

	return $retorno[$cod_retorno];
}

function rgGetMsgRetornoExecaoAPI($msgerro, $get_message)
{
	if (MASER_APP_DEBUG == true) {
		return $msgerro . ' ' . $get_message;
	} else {
		return $msgerro;
	}
}


function rgGetMsgMotNaoEntregaColeta($mot_nao_entrega, $obs_nao_entrega = null)
{
	$motivo = match ($mot_nao_entrega) {
		'01' => 'Entrega cancelada',
		'11' => 'Empresa fechada',
		'12' => 'Fora do dia ou horário',
		'50' => 'Informado em cada nota',
		'51' => 'Mercadoria não conforme',
		'52' => 'Recusa de nota fiscal',
		'99' => 'Outro',
		default => $mot_nao_entrega,
	};

	if (rgDifTrimNull($motivo) && rgDifTrimNull($obs_nao_entrega)) {
		return $motivo . ' - ' . $obs_nao_entrega;
	} else {
		return $motivo;
	}
}


function rgGetMsgMotNaoEntregaColetaNf($mot_nao_entrega)
{
	return match ($mot_nao_entrega) {
		'51' => 'Mercadoria não conforme',
		'52' => 'Recusa de nota fiscal',
		default => $mot_nao_entrega,
	};
}


// Essa é a chave padrão da Maser para criptografia
function rgGetHashKeyMaser()
{
	return '*app@maser#';
}

function rgGetApiKeyValido($apikey)
{

	// Algumas API's tem a necessidade de uma ApiKey
	// Esta chave será enviada de forma criptografada 

	// -----------------------------------------------
	// CHAVE -> rgGetHashKeyMaser()
	// -----------------------------------------------

	$chave = rgGetHashKeyMaser();

	// Criptografamos a chave original.. para comparar chave criptografada recebida
	$apikey_calc = hash_hmac('sha256', $chave, $chave);

	if ($apikey_calc == $apikey) {
		return true;
	} else {
		return false;
	}
}

function rgStringToFloat($str)
{

	// Pegamos as configurações locais setadas como default em App.php
	$local_info = localeconv();

	//removemos o separador de milhar (seja qual for)
	$str = str_replace($local_info['thousands_sep'], '', $str);

	//Trocamos o separador de decimais (seja qual for) pelo ponto
	$str = str_replace($local_info['decimal_point'], '.', $str);

	//Deixamos apenas números, ponto decimal e sinal negativo
	$str = preg_replace("/[^0-9\.\-]/", "", $str);

	//Retornamos como valor float
	return floatval($str);
}

function rgFloatVal($str)
{
	// Pegamos as configurações locais setadas como default em App.php
	$local_info = localeconv();

	$str = floatval($str);

	//removemos o separador de milhar (seja qual for)
	$str = str_replace($local_info['thousands_sep'], '', $str);

	//Trocamos o separador de decimais (seja qual for) pelo virgula
	$str = str_replace($local_info['decimal_point'], ',', $str);

	//Deixamos apenas números, vírgula e sinal negativo
	$str = preg_replace("/[^0-9\,\-]/", "", $str);

	//Retornamos como valor float
	return $str;
}

// Converte um valor ponto flutuante em um string com PONTO DECIMAL
function rgFloatToString($val)
{

	// Pegamos as configurações locais setadas como default em App.php
	$local_info = localeconv();

	$val_str = strval($val);

	//Trocamos o separador de decimais (seja qual for) pelo ponto
	$val_str = str_replace($local_info['decimal_point'], '.', $val_str);

	return $val_str;
}

function rgDifZeroNull($var)
{

	$result = false;

	if ((rgStringToFloat($var) != 0) && ($var != '') && ($var != null)) {
		$result = true;
	}

	return $result;
}

function rgIgualZeroNull($var)
{

	$result = false;

	if ((rgStringToFloat($var) == 0) || ($var == '') || ($var == null)) {
		$result = true;
	}

	return $result;
}

function rgIgualTrimNull($var)
{

	$result = false;

	if (!isset($var) || trim($var) == '' || is_null($var)) {
		$result = true;
	}

	return $result;
}

function rgDifTrimNull($var)
{

	$result = false;

	if ((isset($var)) || (trim($var) != '') || !(is_null($var))) {
		$result = true;
	}

	return $result;
}

function rgNvl($str, $str_ini = '')
{

	if (!isset($str_ini) || ($str_ini == null)) {
		$str_ini = '';
	}

	if ($str == null) {
		$result = $str_ini;
	} else {
		$result = $str;
	}

	return $result;
}


function rgSetaDefault($str, $str_ini = '')
{

	if (!isset($str) || ($str == null) || $str == '') {
		$result = $str_ini;
	} else {
		$result = $str;
	}

	return $result;
}

function rgAjustaTelefone($str)
{
	if (!is_null($str)) {

		//Estamos ajustando dessa forma porque a importação do telefone do sistema DOMPER
		//traz os telefones vazio com máscara '() -"
		//Se após remover todos caracteres e ainda sobrar, significa que está correto e vamos manter.
		$retorno = preg_replace('/[()-]/', "", $str);
		$retorno = rgTrimAll($retorno);

		if (strlen($retorno) == 0) {
			return null;
		} else {
			return $str;
		}
	} else {
		return $str;
	}
}

// Esta função retorna somente os números de uma string informada.
function rgRetNumeros($str)
{
	return preg_replace("/[^0-9]/", "", $str);
}


function rgRetLetrasNumeros($str)
{
	return preg_replace("/[^A-Za-z0-9]/", "", $str);
}


function rgSumTimeToSeconds($time)
{
	// Pode receber uma variavel que representa tempo podendo exceder a 24 horas: 
	//                                     => horas:minutos:segundos 
	//                                     => horas:minutos
	// O resultado devolvido será o tempo correspondente em segundos
	$seconds = 0;

	$timeExploded = explode(':', $time);

	if (isset($timeExploded[2])) {
		$seconds = intVal($timeExploded[0]) * 3600 + intVal($timeExploded[1]) * 60 + intVal($timeExploded[2]);
	} else {
		if (isset($timeExploded[1])) {
			$seconds = intVal($timeExploded[0]) * 3600 + intVal($timeExploded[1]) * 60;
		}
	}

	return $seconds;
}


function rgTimeToSeconds($time)
{
	/* Não utilizar esta rotina, se o tempo ($time) representa uma hora acima de "838:59:59" que é o máximo do MySql */
	$parsed  = date_parse($time);
	$seconds = $parsed['hour'] * 3600 + $parsed['minute'] * 60 + $parsed['second'];

	return $seconds;
}


function rgSecondsToTime($seconds)
{
	if (rgIgualZeroNull($seconds)) {
		$seconds = 0;
	}

	$zero    = new DateTime("@0");
	$offset  = new DateTime("@$seconds");
	$diff    = $zero->diff($offset);

	return sprintf("%02d:%02d:%02d", $diff->days * 24 + $diff->h, $diff->i, $diff->s);
}

// Gera uma cadeia alfanumérica e aleatória de caracteres com 
// o tamanho especificado.// Pode ser usada para criar senhas e ID´s.
function rgQuickRandom($length = 16)
{
	$pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
	return substr(str_shuffle(str_repeat($pool, $length)), 0, $length);
}

// Gera uma cadeia numérica aleatória com o tamanho especificado.
function rgQuickRandomNum($length = 4)
{
	$pool = '0123456789';
	return substr(str_shuffle(str_repeat($pool, $length)), 0, $length);
}

// Gera uma cadeia alfanumérica e aleatória de caracteres com o
// tamanho especificado. Esta função foi copiada de quickRandom()
// do Laravel. Pode ser usada para criar senhas e ID´s. 
//
// Aqui eu NÃO quis simplesmente fazer um UpperCase() no resultado da
// função rgQuickRandom() para reduzir a possibilidade de gerar cadeias
// iguais, onde um resultado "a" minúsculo seria igual a "A" maiúsculo. 
//
// Com letras maiúsculas facilita a digitação pelo usuário.
function rgGenerateStrKey($length = 16)
{
	$pool = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
	return substr(str_shuffle(str_repeat($pool, $length)), 0, $length);
}

function rgRetornaDiferencaDataExt($data, $data_dif)
{

	try {

		//Se as datas passadas por parametro estão com um timezone aonde tem horário de verão Ex: ('America/Sao_Paulo'), no cálculo de data->diff() retorna a hora negativa, isto é um bug no php. Para resolver o problema é recomendável que todos os calculos de datas no php sejam feitos usando timezone UTC.

		//Nesta rotina queremos saber apenas a difença da data por extenso, então cálculamos a data como vem e convertemos para o timezone UTC. 
		$data     = new DateTime($data, new DateTimeZone('UTC'));
		$data_dif = new DateTime($data_dif, new DateTimeZone('UTC'));

		//Função para por o Carbon com a mesma localidade do sistema. Ex: pt-BR
		//Desta forma os textos da função "forHumans" são traduzidos.
		Carbon\CarbonInterval::setLocale(app()->getLocale());

		$intervalo = $data->diff($data_dif);

		/* Aqui precismos criar um DateInterval que será instanciado na classe do Carbon.
       * Não podemos passar diretamente o retorno da função "$data->diff($data_dif)", isto ocasiona uma exception.
       * o parametro gerado está de acordo com o manual -> http://carbon.nesbot.com/docs/#api-interval
       */
		$di = new \DateInterval($intervalo->format('P%yY%mM%dDT%hH%iM%sS'));
		$ci = Carbon\CarbonInterval::instance($di);

		//Retorna todo o texto proveniente do intervalo. Ex: 2 anos 5 semanas 1 dia 1 hora 2 minutos 7 segundos.
		$intervalo_ext = $ci->forHumans();

		//Vamos quebrar o texto por seus espaços, porque precisamos apenas das duas primeiras datas de diferença.
		//Ex: 2 anos 5 meses 
		//    1 mês 3 semanas
		//    1 semana 4 dias
		//    1 dia 1 hora 
		//    2 minutos
		//    23 segundos
		$intervalo_ext = explode(' ', $intervalo_ext);

		if (count($intervalo_ext) > 2) {

			if (strpos($intervalo_ext[1], 'ano') !== FALSE) {

				if ((strpos($intervalo_ext[3], 'mês') !== FALSE) || (strpos($intervalo_ext[3], 'meses') !== FALSE)) {
					$intervalo_ext = $intervalo_ext[0] . ' ' . $intervalo_ext[1] . ' ' . $intervalo_ext[2] . ' ' . $intervalo_ext[3];
				} else {
					$intervalo_ext = $intervalo_ext[0] . ' ' . $intervalo_ext[1];
				}
			}

			if ((strpos($intervalo_ext[1], 'mês') !== FALSE) || (strpos($intervalo_ext[1], 'meses') !== FALSE)) {

				if ((strpos($intervalo_ext[3], 'semana') !== FALSE)) {
					$intervalo_ext = $intervalo_ext[0] . ' ' . $intervalo_ext[1] . ' ' . $intervalo_ext[2] . ' ' . $intervalo_ext[3];
				} else {
					$intervalo_ext = $intervalo_ext[0] . ' ' . $intervalo_ext[1];
				}
			}

			if ((strpos($intervalo_ext[1], 'semana') !== FALSE)) {

				if ((strpos($intervalo_ext[3], 'dia') !== FALSE)) {
					$intervalo_ext = $intervalo_ext[0] . ' ' . $intervalo_ext[1] . ' ' . $intervalo_ext[2] . ' ' . $intervalo_ext[3];
				} else {
					$intervalo_ext = $intervalo_ext[0] . ' ' . $intervalo_ext[1];
				}
			}

			if ((strpos($intervalo_ext[1], 'dia') !== FALSE)) {

				if ((strpos($intervalo_ext[3], 'hora') !== FALSE)) {
					$intervalo_ext = $intervalo_ext[0] . ' ' . $intervalo_ext[1] . ' ' . $intervalo_ext[2] . ' ' . $intervalo_ext[3];
				} else {
					$intervalo_ext = $intervalo_ext[0] . ' ' . $intervalo_ext[1];
				}
			}

			if ((strpos($intervalo_ext[1], 'hora') !== FALSE)) {

				if ((strpos($intervalo_ext[3], 'minuto') !== FALSE)) {
					$intervalo_ext = $intervalo_ext[0] . ' ' . $intervalo_ext[1] . ' ' . $intervalo_ext[2] . ' ' . $intervalo_ext[3];
				} else {
					$intervalo_ext = $intervalo_ext[0] . ' ' . $intervalo_ext[1];
				}
			}

			if ((strpos($intervalo_ext[1], 'minuto') !== FALSE)) {
				$intervalo_ext = $intervalo_ext[0] . ' ' . $intervalo_ext[1];
			}
		} else {

			if (isset($intervalo_ext[1]) === FALSE) {
				$intervalo_ext = '1 segundo';
			} else {
				$intervalo_ext = $intervalo_ext[0] . ' ' . $intervalo_ext[1];
			}
		}

		return $intervalo_ext;
	} catch (\Exception $e) {

		//Se der erro, não vamos retornar nenhum intervalo.      
		return '';
	}
}



function rgCompChaveArrayCrescente($a, $b, $chave)
{
	//Compara se $a é maior que $b
	return $a[$chave] > $b[$chave];
}


function rgRetornaArrayTimeZone()
{

	$timezone = [
		'America/Rio_branco'   => 'AC - America/Rio_branco',
		'America/Maceio'       => 'AL - America/Maceio',
		'America/Belem'        => 'AP - America/Belem',
		'America/Manaus'       => 'AM - America/Manaus',
		'America/Bahia'        => 'BA - America/Bahia',
		'America/Fortaleza'    => 'CE - America/Fortaleza',
		'America/Sao_Paulo'    => 'DF - America/Sao_Paulo',
		'America/Sao_Paulo'    => 'ES - America/Sao_Paulo',
		'America/Sao_Paulo'    => 'G0 - America/Sao_Paulo',
		'America/Fortaleza'    => 'MA - America/Fortaleza',
		'America/Cuiaba'       => 'MT - America/Cuiaba',
		'America/Campo_Grande' => 'MS - America/Campo_Grande',
		'America/Sao_Paulo'    => 'MG - America/Sao_Paulo',
		'America/Sao_Paulo'    => 'PR - America/Sao_Paulo',
		'America/Fortaleza'    => 'PB - America/Fortaleza',
		'America/Belem'        => 'PA - America/Belem',
		'America/Recife'       => 'PE - America/Recife',
		'America/Fortaleza'    => 'PI - America/Fortaleza',
		'America/Sao_Paulo'    => 'RJ - America/Sao_Paulo',
		'America/Fortaleza'    => 'RN - America/Fortaleza',
		'America/Sao_Paulo'    => 'RS - America/Sao_Paulo',
		'America/Porto_Velho'  => 'RO - America/Porto_Velho',
		'America/Boa_Vista'    => 'RR - America/Boa_Vista',
		'America/Sao_Paulo'    => 'SC - America/Sao_Paulo',
		'America/Maceio'       => 'SE - America/Maceio',
		'America/Sao_Paulo'    => 'SP - America/Sao_Paulo',
		'America/Araguaia'     => 'TO - America/Araguaia'
	];

	return $timezone;
}

function rgNoAccent($string)
{
	return transliterator_transliterate('Any-Latin; Latin-ASCII;', $string);
}

function rgTrimAll($string)
{
	return str_replace(' ', '', $string);
}

function rgRegexAlfaNum($string)
{

	// Primeiro removemos os acentos... porque a função 'preg_replace()' 
	// estava 'se borrando' quando convertia os caracteres "ô" e "è".
	//
	$string = preg_replace('/[`^~\'"]/', '', iconv('UTF-8', 'ASCII//TRANSLIT', $string));

	// '$pattern' define os caracteres que serão eliminados da string. J
	//
	// * * A T E N Ç Ã O !!  JAMAIS mude a ordem ou qualquer caracter nesta string, 
	// se você não souber o que está fazendo. Isso deu muito trabalho para fazer. 
	// Dependendo da ordem que coloca os caracteres não funciona. Fizemos a função 
	// no MySQL 'regex_replace' que faz a mesma coisa, porém, a ordem dos caracteres 
	// é ligeiramente diferente. Evandro e Jonas: 07/07/2017 - 14:18
	//
	$pattern = '/[' . '][><}{)(:;,!?*%~^`&#@ $¨_+=|' . '\\\\.' . '\/"´-' . ']/';

	$string = preg_replace($pattern, '', $string);

	return $string;
}

// Id da Aplicação MASER, cadastrado no One Signal, para envio de Push Notification.
function rgGetAppIdOneSignal()
{

	//Esta chave já esta correta na conta da "app.masertransportes"
	return '8be0d33b-95c9-4234-8557-8d57f39fc6e5';
}

function rgGetChannelIDOneSignal()
{

	/*
      O som que está configurado no canal da categoria é oque é executado, se não encontrar
	  tocará o som padrão do dispositivo.
	  
      É necessário existir este canal, pois se mandar um canal sem valor, a notificação
      não é recebida nos aparelhos.       
    */

	/* 
      ***ATENÇÃO***

      No OneSignal, a propriedade "SOUND" para o som "CUSTOM" deve estar digitado com letras minúsculas, exatamente igual ao nome 
      do arquivo sem a extensão.

      O OneSignal exibe sempre com letras maiúsculas(uppercase), então é recomendável digitar apartir de um editor de texto e colar para 
      não se confundir.
	*/

	return 'a002cc50-22dd-4f59-9c5c-30c4e7b8da6f'; //Categoria no OneSignal "CUSTOM".

}

// Chave Maser para acesso as APIs do google
function rgGetKeyGoogleMapsApi()
{
	// Esta é a chave padrão da conta "app.masertransportes", utilizada para mapas.
	// Para a API Distance Matrix, priorizamos a chave obtida dos cadastros, e usamos esta apenas se nenhuma for encontrada.
	return config('app.google_maps_key');
}

function rgCryptMaser($string)
{

	$output = strrev($string);
	$output = base64_encode($output);

	return $output;
}

function rgDecryptMaser($string)
{

	$output = base64_decode($string);
	$output = strrev($output);

	return $output;
}

function rgFormataDistancia($distancia)
{
	if (floatval($distancia) > 1) {
		// Formata com 1 casa decimal, separador decimal vírgula e milhar com ponto
		$dist_str = number_format(floatval($distancia), 1, ',', '.') . ' km';
	} else {
		$metros = floatval($distancia) * 1000;
		// Formata sem casas decimais
		$dist_str = number_format($metros, 0, ',', '.') . ' m';
	}

	return $dist_str;
}

function rgFormataPesoVeiculo($peso)
{

	$val = floatval($peso);

	if ($val == 0) {
		$peso_str = '-';
	} else {

		if ($val < 1) {
			$peso_str = round($val * 1000, 1) . ' gr';
		} else {
			if ($val < 1000) {
				$peso_str = round($val, 1) . ' kg';
			} else {
				$peso_str = round($val / 1000, 1) . ' tn';
			}
		}
	}

	return $peso_str;
}

function rgFormataDimensoes($comprimento, $largura, $altura)
{

	// Montar string das dimensões. Ex: "3,50 x 2 x 1,80"
	if ((floatval($comprimento) == 0) && (floatval($largura) == 0)  && (floatval($altura) == 0)) {
		$dimensoes = '-';
	} else {
		$dimensoes = floatval($comprimento) . ' x ' . floatval($largura) . ' x ' . floatval($altura);
	}

	return $dimensoes;
}


function rgFormataHorarioExpediente($hora_ini, $hora_fim)
{

	$horario = null;

	if ($hora_ini != null) {
		$horario = substr($hora_ini, 0, 5);
	}

	if ($hora_fim != null) {
		if ($horario != null) {
			$horario =
				$horario . ' às ' . substr($hora_fim, 0, 5);
		} else {
			$horario = substr($hora_fim, 0, 5);
		}
	}

	return $horario;
}

function EnderecoEstaCorreto($endereco, $bairro, $cidade, $uf, $cep)
{
	// Precisamos do endereco completo o mais correto possível para que o google retorne as coordenadas com exatidão
	// Remover os caracteres do cep: "." ponto e "-" hifen
	$cep = preg_replace("/[.-]/", "", $cep);

	if ((rgNvl($endereco) != '') && (rgNvl($bairro) != '') && (rgNvl($cidade) != '') && (rgNvl($uf) != '') && (rgNvl($cep) != '')) {

		$ender = explode(',', trim($endereco));

		if (isset($ender[1])) {
			$ender1 = explode(' ', trim($ender[1]));
			$numero = $ender1[0];

			if (ctype_digit($numero) == true) {
				return  true;
			}
		}
	}

	return false;
}

function GetDrivingDistance($latOrig, $lngOrig, $latDest, $lngDest, $apiService, $apiKey)
{
	if ($apiService === 'google_cloud') {
		return GetDrivingDistanceGoogle($latOrig, $lngOrig, $latDest, $lngDest, $apiKey);
	}

	if ($apiService === 'mapbox') {
		return GetDrivingDistanceMapbox($latOrig, $lngOrig, $latDest, $lngDest, $apiKey);
	}

	return ['distance' => 0, 'duration' => 0];
}

function GetDrivingDistanceGoogle($latOrig, $lngOrig, $latDest, $lngDest, $apiKey)
{
	/* https://console.cloud.google.com/apis/dashboard
	*/

	try {
		$url = "https://maps.googleapis.com/maps/api/distancematrix/json?origins={$latOrig},{$lngOrig}&destinations={$latDest},{$lngDest}&key={$apiKey}&mode=driving&language=pt-BR";
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_PROXYPORT, 3128);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		$response = curl_exec($ch);
		curl_close($ch);
		$response_a = json_decode($response, true);

		$distance = $response_a['rows'][0]['elements'][0]['distance']['value']; // O valor está em metros
		$duration = $response_a['rows'][0]['elements'][0]['duration']['value']; // O valor está em segundos

		//Convertemos a distancia para km.
		$distance = ($distance / 1000);

		return ['distance' => $distance, 'duration' => $duration];
	} catch (\Exception $e) {
		return ['distance' => 0, 'duration' => 0];
	}
}

function GetDrivingDistanceMapbox($latOrig, $lngOrig, $latDest, $lngDest, $apiKey)
{
	/* https://console.mapbox.com/account/statistics
	*/

	try {
		$url = "https://api.mapbox.com/directions/v5/mapbox/driving-traffic/{$lngOrig},{$latOrig};{$lngDest},{$latDest}?access_token={$apiKey}&geometries=geojson&overview=false";

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_PROXYPORT, 3128);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		$response = curl_exec($ch);
		curl_close($ch);
		$response_a = json_decode($response, true);

		if (isset($response_a['routes'][0])) {
			$distance = $response_a['routes'][0]['distance']; // O valor está em metros
			$duration = $response_a['routes'][0]['duration']; // O valor está em segundos

			//Convertemos a distancia para km.
			$distance = ($distance / 1000);

			return ['distance' => $distance, 'duration' => $duration];
		}

		return ['distance' => 0, 'duration' => 0];
	} catch (\Exception $e) {
		return ['distance' => 0, 'duration' => 0];
	}
}

function GetDrivingDistanceHere($latOrig, $lngOrig, $latDest, $lngDest, $apiKey)
{
	/* https://platform.here.com/portal/
	*/

	try {
		$url = "https://router.hereapi.com/v8/routes?transportMode=car&origin={$latOrig},{$lngOrig}&destination={$latDest},{$lngDest}&return=summary&apikey={$apiKey}";

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		$response = curl_exec($ch);
		curl_close($ch);
		$response_a = json_decode($response, true);

		if (
			isset($response_a['routes'][0]['sections'][0]['summary']['length']) &&
			isset($response_a['routes'][0]['sections'][0]['summary']['duration'])
		) {
			$length = $response_a['routes'][0]['sections'][0]['summary']['length'];     // O valor está em metros
			$duration = $response_a['routes'][0]['sections'][0]['summary']['duration']; // O valor está em segundos

			//Convertemos a distancia para km.
			$distance = $length / 1000;

			return ['distance' => $distance, 'duration' => $duration];
		}

		return ['distance' => 0, 'duration' => 0];
	} catch (\Exception $e) {
		return ['distance' => 0, 'duration' => 0];
	}
}

function RetornarGeoPosition($endereco, $bairro, $cidade, $cep, $uf, $pais = 'Brasil')
{
	//Montar o endereço para Google
	$ender_google = '';

	$geo_lat = 0;
	$geo_lng = 0;

	//Exemplo de endereço do Google "Rua Hércules Galló,515,Centro,Caxias do Sul,RS"	

	if (rgNvl($endereco) != '') {
		$ender_google = trim($endereco);
	}

	if (rgNvl($bairro) != '') {
		if (rgNvl($ender_google) != '') {
			//Adicionamos separador
			$ender_google = $ender_google . ',';
		}
		$ender_google = $ender_google . trim($bairro);
	}

	if (rgNvl($cidade) != '') {
		if (rgNvl($ender_google) != '') {
			//Adicionamos separador
			$ender_google = $ender_google . ',';
		}
		$ender_google = $ender_google . trim($cidade);
	}

	if (rgNvl($uf) != '') {
		if (rgNvl($ender_google) != '') {
			//Adicionamos separador
			$ender_google = $ender_google . ',';
		}
		$ender_google = $ender_google . trim($uf);
	}

	//Remover os caracteres do cep: "." ponto e "-" hifen
	$cep = preg_replace("/[.-]/", "", $cep);

	if (rgNvl($cep) != '') {
		if (rgNvl($ender_google) != '') {
			//Adicionamos separador
			$ender_google = $ender_google . ',';
		}
		$ender_google = $ender_google . trim($cep);
	}

	if (rgNvl($pais) != '') {
		if (rgNvl($ender_google) != '') {
			//Adicionamos separador
			$ender_google = $ender_google . ',';
		}
		$ender_google = $ender_google . trim($pais);
	}

	if (rgNvl($ender_google) != '') {

		$coordenadas = GoogleApi_GetCoordsEnderecos($ender_google);

		if (!empty($coordenadas)) {
			$geo_lat = $coordenadas['geo_lat'];
			$geo_lng = $coordenadas['geo_lng'];
		}
	}

	return array('geo_lat' => $geo_lat, 'geo_lng' => $geo_lng);
}

function GoogleApi_GetEnderecosCoords($geo_lat, $geo_lng)
{

	$endereco = array();

	try {

		$url = "https://maps.googleapis.com/maps/api/geocode/json?latlng=" . $geo_lat . "," . $geo_lng . "&key=" . rgGetKeyGoogleMapsApi();

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_PROXYPORT, 3128);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		$response = curl_exec($ch);
		curl_close($ch);
		$response_a = json_decode($response, true);

		if (!empty($response_a)) {
			$endereco = GP_CarregarRetorno_GetEndereco_Google($response_a);
		}
	} catch (\Exception $e) {
		//Se der erro não fazemos nada, retornaremos um array vazio inicializado no início da rotina
	}

	return $endereco;
}


function GP_CarregarRetorno_GetEndereco_Google($vRetAPI)
{

	$endereco = array();

	try {

		// Pegamos apenas o primeiro resultado: É o mais preciso!!!
		$nQtdeResultados = 1;

		for ($r = 0; $r < $nQtdeResultados; $r++) {

			$nQtdeComponents = count($vRetAPI['results'][$r]['address_components']);

			for ($c = 0; $c < $nQtdeComponents; $c++) {

				$nQtdeTypes = count($vRetAPI['results'][$r]['address_components'][$c]['types']);

				for ($t = 0; $t < $nQtdeTypes; $t++) {
					// Número 
					if ($vRetAPI['results'][$r]['address_components'][$c]['types'][$t] == "street_number") {
						$endereco['ender_Nro'] = $vRetAPI['results'][$r]['address_components'][$c]['long_name'];
					}

					// Rua
					if ($vRetAPI['results'][$r]['address_components'][$c]['types'][$t] == "route") {
						$endereco['endereco'] = $vRetAPI['results'][$r]['address_components'][$c]['long_name'];
					}

					// Bairro
					if (($vRetAPI['results'][$r]['address_components'][$c]['types'][$t] == "sublocality") || ($vRetAPI['results'][$r]['address_components'][$c]['types'][$t] == "sublocality_level_1")
					) {
						$endereco['bairro'] = $vRetAPI['results'][$r]['address_components'][$c]['long_name'];
					}

					// Cidade
					if (($vRetAPI['results'][$r]['address_components'][$c]['types'][$t] == "locality") || ($vRetAPI['results'][$r]['address_components'][$c]['types'][$t] == "administrative_area_level_2")
					) {
						$endereco['cidade'] = $vRetAPI['results'][$r]['address_components'][$c]['long_name'];
					}

					// UF
					if ($vRetAPI['results'][$r]['address_components'][$c]['types'][$t] == "administrative_area_level_1") {
						$endereco['uf'] = $vRetAPI['results'][$r]['address_components'][$c]['short_name'];
					}

					// CEP
					if ($vRetAPI['results'][$r]['address_components'][$c]['types'][$t] == "postal_code") {
						$endereco['cep'] = rgRetNumeros($vRetAPI['results'][$r]['address_components'][$c]['long_name']);
					}
				}
			}
		}
	} catch (\Exception $e) {
		//Se der erro não fazemos nada, retornaremos um array vazio inicializado no início da rotina
	}

	return $endereco;
}

function GoogleApi_GetCoordsEnderecos($address)
{

	$coordenadas = array();

	try {

		//A função urlencode transforma os caracteres que não são validos para uma url em seus equivalentes:
		//Ex: " " -> %20
		$address = urlencode($address);

		$url = 'https://maps.googleapis.com/maps/api/geocode/json?address=' . $address . '&key=' . rgGetKeyGoogleMapsApi();

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_PROXYPORT, 3128);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		$response = curl_exec($ch);

		curl_close($ch);
		$response_a = json_decode($response, true);

		if (!empty($response_a)) {
			$coordenadas = GP_CarregarRetorno_GetCoordenadas_Google($response_a);
		}
	} catch (\Exception $e) {
		//Se der erro não fazemos nada, retornaremos um array vazio inicializado no início da rotina
	}

	return $coordenadas;
}

function GP_CarregarRetorno_GetCoordenadas_Google($vRetAPI)
{

	$coordenadas = array();

	try {

		// Pegamos apenas o primeiro resultado: É o mais preciso!!!
		$nQtdeResultados = 1;

		for ($r = 0; $r < $nQtdeResultados; $r++) {
			$coordenadas['geo_lat'] = $vRetAPI['results'][$r]['geometry']['location']['lat'];
			$coordenadas['geo_lng'] = $vRetAPI['results'][$r]['geometry']['location']['lng'];
		}
	} catch (\Exception $e) {
		//Se der erro não fazemos nada, retornaremos um array vazio inicializado no início da rotina
	}

	return $coordenadas;
}

// Retorna 'file system' configurado como default em 'config/filesystems.php'
//
function rgRetornarDefaultDisk()
{
	return Config::get('filesystems.default');
}

// Retorna a URL da pasta de armazenamento das imagens
//
function rgRetornarPastaRaizImagens()
{
	return 'imagens';
}

// Retorna a URL do arquivo de imagem de uma coleta
//
function rgRetornarUrlImagens($arq_img)
{
	$file_path = \Storage::url(rgRetornarPastaRaizImagens() . '/' . $arq_img);
	return $file_path;
}

function rgTextoValidoJson($texto)
{
	/*
      O campo 'texto' pode ser um campo blob, que pode ser informado qualquer valor, 
      se NÃO for possível retornar em formato Json, vai dar problema no retorno das API's.

      Desta forma estamos garantindo que o valor pode ser devolvido, ou então
      retornará "null".
    */
	if (json_encode($texto) == false) {
		return null;
	} else {
		return $texto;
	}
}


function rgTitleCase($string)
{

	$delimiters = array(" ", "-", ".", "'", "O'", "Mc", "/");

	$exceptions = array(
		"e",
		"em",
		"de",
		"da",
		"do",
		"das",
		"dos",
		"I",
		"II",
		"III",
		"IV",
		"V",
		"VI",
		"VII",
		"VIII",
		"IX",
		"X",
		"XI",
		"XII",
		"XIII",
		"XIV",
		"XV",
		"XVI",
		"XVII",
		"XVIII",
		"XIX",
		"XX",
		"XXI",
		"XXII",
		"XXIII",
		"XXIV",
		"XXV",
		"XXVI",
		"XXVII",
		"XXVIII",
		"XXIX",
		"XXX"
	);

	/*
    * Exceptions in lower case are words you don't want converted
    * Exceptions all in upper case are any words you don't want converted to title case
    *   but should be converted to upper case, e.g.:
    *   king henry viii or king henry Viii should be King Henry VIII
    */

	$string = mb_convert_case($string, MB_CASE_TITLE, "UTF-8");

	foreach ($delimiters as $dlnr => $delimiter) {

		$words = explode($delimiter, $string);
		$newwords = array();

		foreach ($words as $wordnr => $word) {

			if (in_array(mb_strtoupper($word, "UTF-8"), $exceptions)) {
				// check exceptions list for any words that should be in upper case
				$word = mb_strtoupper($word, "UTF-8");
			} elseif (in_array(mb_strtolower($word, "UTF-8"), $exceptions)) {
				// check exceptions list for any words that should be in upper case
				$word = mb_strtolower($word, "UTF-8");
			} elseif (!in_array($word, $exceptions)) {
				// convert to uppercase (non-utf8 only)
				$word = ucfirst($word);
			}

			array_push($newwords, $word);
		}

		$string = join($delimiter, $newwords);
	} //foreach

	return $string;
}



function rgRetornaFormataTempoExt($tempo, $sufix_completo = false, $considera_seg = false)
{

	try {

		// Esta função espera um time string no formato "hh:mm:ss"

		$result = '';
		$tempo_exp = explode(':', $tempo);

		if (count($tempo_exp) > 2) {

			if (floatval($tempo_exp[0]) > 0) {

				if ($sufix_completo) {
					$result = floatval($tempo_exp[0]) . ' horas';
				} else {
					// Aqui preferimos o "h" sem espaços antes
					$result = floatval($tempo_exp[0]) . 'h';
				}
			}

			if (floatval($tempo_exp[1]) > 0) {

				if (trim($result) <> '') {
					$result = $result . ' ';
				}

				if ($sufix_completo) {
					$result = $result . floatval($tempo_exp[1]) . ' minutos';
				} else {
					$result = $result . floatval($tempo_exp[1]) . ' min';
				}
			}

			if ($considera_seg) {
				if (floatval($tempo_exp[2]) > 0) {

					if (trim($result) <> '') {
						$result = $result . ' ';
					}

					if ($sufix_completo) {
						$result = $result . floatval($tempo_exp[2]) . ' segundos';
					} else {
						$result = $result . floatval($tempo_exp[2]) . ' seg';
					}
				}
			}
		}
	} catch (\Exception $e) {
		$result = '';
	}

	return $result;
}

function rgIntervaloDatasSemFormatacao($data, $datadif)
{

	/* Esta funcao retorna um objeto "DateInterval" onde podemos ter acesso as seguintes informações:
      'y' -> Número de anos.
      'm' -> Número de meses.
      'd' -> Número de dias.
      'h' -> Número de horas.
      'i' -> Número de minutos.
      's' -> Número de segundos.
      'invert'-> Será 1 se o intervalo representa um período negativo de tempo e 0 (zero) caso contrário.
	  'days'  -> Representa o número total de dias entre as duas datas (data inicial e data final).
	*/

	// Exemplo de uso: $result = $intervalo->h . ' horas e ' . $intervalo->i . ' minutos';

	$data     = new DateTime($data, new DateTimeZone('UTC'));
	$data_dif = new DateTime($datadif, new DateTimeZone('UTC'));

	//Função para por o Carbon com a mesma localidade do sistema. Ex: pt-BR
	Carbon\CarbonInterval::setLocale(app()->getLocale());

	$intervalo = $data->diff($data_dif);

	return $intervalo;
}

/**
 * calculaDV
 * Função para o cálculo do digito verificador da chave da NFe
 * Fonte "https://github.com/nfephp-org/nfephp/blob/master/libs/Common/Keys/Keys.php"
 *
 * @param  string $chave43
 * @return string
 */
function calculaDV($chave43)
{
	$multiplicadores = array(2, 3, 4, 5, 6, 7, 8, 9);
	$iCount = 42;
	$somaPonderada = 0;

	while ($iCount >= 0) {
		for ($mCount = 0; $mCount < count($multiplicadores) && $iCount >= 0; $mCount++) {
			$num = (int) substr($chave43, $iCount, 1);
			$peso = (int) $multiplicadores[$mCount];
			$somaPonderada += $num * $peso;
			$iCount--;
		}
	}

	$resto = $somaPonderada % 11;

	if ($resto == '0' || $resto == '1') {
		$cDV = 0;
	} else {
		$cDV = 11 - $resto;
	}

	return (string) $cDV;
}

/**
 * testaChaveNFe
 * Testa a chave com o digito verificador no final
 *
 * @param  string $chave
 * @return boolean
 */
function testaChaveNFe($chave = '')
{
	if (strlen($chave) != 44) {
		return false;
	}

	$cDV = substr($chave, -1);
	$calcDV = calculaDV(substr($chave, 0, 43));

	if ($cDV === $calcDV) {
		return true;
	}

	return false;
}
