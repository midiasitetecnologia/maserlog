<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use DB;

class ApiIntegracao extends Model
{
	public function Local_ImportarClientes($funcao, $empresa, $lista_clientes)
	{
		$continuar = true;
		$retorno   = array();

		//Testamos se o site não foi colocado em manutenção
		$app = new ApiUsoComum();
		$retorno = $app->AplicacaoLiberada();

		if (($retorno['cod_retorno']) != 'Z998') {
			$continuar = false;
		}

		if ($continuar) {

			//Verificar parâmetros
			if (rgIgualZeroNull($empresa)) {
				$continuar = false;
				$retorno['cod_retorno'] = 'Z202';
				$retorno['msg_retorno'] = 'Parâmetro [empresa] obrigatório. Valor esperado: > zero.';
			} else {

				$emp = DB::table('empresa')->where('codigo', '=', $empresa)->first();

				if (empty($emp)) {
					$continuar = false;
					$retorno['cod_retorno'] = 'B207';
					$msg_erro = rgGetMsgRetornoAPI($retorno['cod_retorno']);
					$msg_erro = str_replace('$empresa', $empresa, $msg_erro);
					$retorno['msg_retorno'] = $msg_erro;
				}
			}
		}

		if ($continuar) {

			//Inicializar variáveis
			$timezone_app = date_default_timezone_get();

			$code_erro = "B206";
			$code_ok   = "B100";

			$cont_regs = 0;
			$cont_novos = 0;
			$cont_atlz = 0;
			$cont_erros = 0;

			$array_log  = array();

			if ($funcao == "load") {
				$msg_log    = 'Inicio: Carga inicial de clientes (Emp: ' . $empresa . ')';
				$evento_log = 'carga_inicial_clientes';
			} else {
				$msg_log    = 'Inicio: Sincronização de clientes (Emp: ' . $empresa . ')';
				$evento_log = 'sincronizacao_clientes';
			}

			//Verificar se tem clientes na lista
			if ((!isset($lista_clientes)) || ($lista_clientes == null) || (count($lista_clientes) == 0)) {
				$continuar = false;
				$retorno['cod_retorno'] = 'Z202';
				$retorno['msg_retorno'] = 'Parâmetro [lista_clientes] sem conteúdo.';
			}
		}

		if ($continuar) {

			// Inserir um elemento no array do log:
			$ind_log = 0;

			$array_log[$ind_log]['tipo']       = '0';
			$array_log[$ind_log]['msg']        = $msg_log;
			$array_log[$ind_log]['err']        = null;
			$array_log[$ind_log]['status']     = '0';
			$array_log[$ind_log]['created_at'] = Carbon::now($timezone_app)->format('Y-m-d H:i:s');

			foreach ($lista_clientes as $regcli) {

				$cont_regs = $cont_regs + 1;

				$cliente = Cliente::where('empresa', $empresa)
					->where('codigo', $regcli['codigo'])
					->first();

				if (empty($cliente)) { //Não achou

					try {

						$clienteInsert = new Cliente();

						$clienteInsert['empresa']     = $empresa;
						$clienteInsert['codigo']      = $regcli['codigo'];
						$clienteInsert['tipo_pessoa'] = $regcli['pessoa'];
						$clienteInsert['nome']        = rgTitleCase($regcli['nome']);
						$clienteInsert['fantasia']    = rgTitleCase($regcli['fantasia']);

						if ($regcli['pessoa'] == 'J') {
							$clienteInsert['cpf_cnpj'] = $regcli['cnpj'];
						} else {
							$clienteInsert['cpf_cnpj'] = $regcli['cpf'];
						}

						$clienteInsert['fone']     = rgAjustaTelefone($regcli['fone']);
						$clienteInsert['endereco'] = rgTitleCase($regcli['endereco']);
						$clienteInsert['bairro']   = rgTitleCase($regcli['bairro']);
						$clienteInsert['cidade']   = rgTitleCase($regcli['cidade']);
						$clienteInsert['cep']      = $regcli['cep'];
						$clienteInsert['uf']       = $regcli['estado'];

						$clienteInsert['geo_lat'] = 0;
						$clienteInsert['geo_lng'] = 0;

						// As coordenadas somente serão atualizadas para clientes inseridos
						// após a carga inicial. As coordenadas dos clientes que forem pelo evento
						// load (carga inicial), serão atualizadas posteriormente em processo Bat
						if (strtoupper($funcao) <> strtoupper('load')) {

							if (EnderecoEstaCorreto(
								$regcli['endereco'],
								$regcli['bairro'],
								$regcli['cidade'],
								$regcli['cep'],
								$regcli['estado']
							)) {

								$coordenadas = RetornarGeoPosition(
									$regcli['endereco'],
									$regcli['bairro'],
									$regcli['cidade'],
									$regcli['cep'],
									$regcli['estado']
								);

								$clienteInsert['geo_lat'] = $coordenadas['geo_lat'];
								$clienteInsert['geo_lng'] = $coordenadas['geo_lng'];
							}
						}

						$clienteInsert['dt_alt_cad']  = $regcli['data_alt'];
						$clienteInsert['hr_alt_cad']  = $regcli['hora_alt'];

						$clienteInsert['hr_ini_coleta_man']  = $regcli['hr_ini_col_man'];
						$clienteInsert['hr_fim_coleta_man']  = $regcli['hr_fim_col_man'];
						$clienteInsert['hr_ini_coleta_tar']  = $regcli['hr_ini_col_tar'];
						$clienteInsert['hr_fim_coleta_tar']  = $regcli['hr_fim_col_tar'];
						$clienteInsert['hr_ini_entrega_man'] = $regcli['hr_ini_ent_man'];
						$clienteInsert['hr_fim_entrega_man'] = $regcli['hr_fim_ent_man'];
						$clienteInsert['hr_ini_entrega_tar'] = $regcli['hr_ini_ent_tar'];
						$clienteInsert['hr_fim_entrega_tar'] = $regcli['hr_fim_ent_tar'];

						$clienteInsert['ass_user_id'] = auth()->user()->id;

						$clienteInsert->save();

						$cont_novos = $cont_novos + 1;
					} catch (\Exception $e) {
						// O indice do primeiro elemento do array_log é zero. Então com o count, obtemos sempre
						// o indice do próximo elemento a ser inserido
						$ind_log = count($array_log);

						// Inserir um elemento no array do log:
						$array_log[$ind_log]['tipo'] = '1';

						$msg_erro = rgGetMsgRetornoAPI('B204');
						$msg_erro = str_replace('$empresa', $empresa, $msg_erro);
						$msg_erro = str_replace('$codigo', $regcli['codigo'], $msg_erro);
						$msg_erro = str_replace('$nome', $regcli['nome'], $msg_erro);

						$array_log[$ind_log]['msg'] = $msg_erro;
						$array_log[$ind_log]['err'] = $e->getMessage();
						$array_log[$ind_log]['status'] = '1';
						$array_log[$ind_log]['created_at'] = Carbon::now($timezone_app)->format('Y-m-d H:i:s');

						$cont_erros = $cont_erros + 1;
					}
				} else { //Achou

					$data_alt   = $regcli['data_alt'];
					$dt_alt_cad = null;

					if (!is_null($cliente->dt_alt_cad) && ($cliente->dt_alt_cad != "")) {
						$dt_alt_cad = $cliente->dt_alt_cad;
					}

					if (($data_alt > $dt_alt_cad) || (($data_alt == $dt_alt_cad) && ($regcli['hora_alt'] > $cliente->hr_alt_cad))
					) {

						try {

							$cliente['tipo_pessoa'] = $regcli['pessoa'];
							$cliente['nome']        = rgTitleCase($regcli['nome']);
							$cliente['fantasia']    = rgTitleCase($regcli['fantasia']);

							if ($regcli['pessoa'] == 'J') {
								$cliente['cpf_cnpj'] = $regcli['cnpj'];
							} else {
								$cliente['cpf_cnpj'] = $regcli['cpf'];
							}

							$cliente['fone'] = rgAjustaTelefone($regcli['fone']);

							$enderecoModificado = $this->EnderecoModificado(
								$cliente,
								rgTitleCase($regcli['endereco']),
								rgTitleCase($regcli['bairro']),
								rgTitleCase($regcli['cidade']),
								$regcli['cep'],
								$regcli['estado']
							);

							if ($enderecoModificado) {

								$cliente['endereco'] = rgTitleCase($regcli['endereco']);
								$cliente['bairro']   = rgTitleCase($regcli['bairro']);
								$cliente['cidade']   = rgTitleCase($regcli['cidade']);
								$cliente['cep']      = $regcli['cep'];
								$cliente['uf']       = $regcli['estado'];

								if (EnderecoEstaCorreto(
									$cliente['endereco'],
									$cliente['bairro'],
									$cliente['cidade'],
									$cliente['cep'],
									$cliente['uf']
								)) {

									$coordenadas = RetornarGeoPosition(
										$cliente['endereco'],
										$cliente['bairro'],
										$cliente['cidade'],
										$cliente['cep'],
										$cliente['uf']
									);

									$cliente['geo_lat']  = $coordenadas['geo_lat'];
									$cliente['geo_lng']  = $coordenadas['geo_lng'];
								}
							}

							$cliente['dt_alt_cad']  = $regcli['data_alt'];
							$cliente['hr_alt_cad']  = $regcli['hora_alt'];

							$cliente['hr_ini_coleta_man']  = $regcli['hr_ini_col_man'];
							$cliente['hr_fim_coleta_man']  = $regcli['hr_fim_col_man'];
							$cliente['hr_ini_coleta_tar']  = $regcli['hr_ini_col_tar'];
							$cliente['hr_fim_coleta_tar']  = $regcli['hr_fim_col_tar'];
							$cliente['hr_ini_entrega_man'] = $regcli['hr_ini_ent_man'];
							$cliente['hr_fim_entrega_man'] = $regcli['hr_fim_ent_man'];
							$cliente['hr_ini_entrega_tar'] = $regcli['hr_ini_ent_tar'];
							$cliente['hr_fim_entrega_tar'] = $regcli['hr_fim_ent_tar'];

							$cliente['ass_user_id'] = auth()->user()->id;

							$cliente->save();
							$cont_atlz = $cont_atlz + 1;
						} catch (\Exception $e) {
							// O indice do primeiro elemento do array_log é zero. Então com o count, obtemos sempre
							// o indice do próximo elemento a ser inserido
							$ind_log = count($array_log);

							// Inserir um elemento no array do log:
							$array_log[$ind_log]['tipo'] = '1';

							$msg_erro = rgGetMsgRetornoAPI('B205');
							$msg_erro = str_replace('$empresa', $empresa, $msg_erro);
							$msg_erro = str_replace('$codigo', $regcli['codigo'], $msg_erro);
							$msg_erro = str_replace('$nome', $regcli['nome'], $msg_erro);

							$array_log[$ind_log]['msg'] = $msg_erro;
							$array_log[$ind_log]['err'] = $e->getMessage();
							$array_log[$ind_log]['status'] = '1';
							$array_log[$ind_log]['created_at'] = Carbon::now($timezone_app)->format('Y-m-d H:i:s');

							$cont_erros = $cont_erros + 1;
						}
					}
				}
			}

			$trailler = $this->AdicionarLogTrailler($array_log, $funcao, $empresa, $cont_erros, $cont_regs, $cont_novos, $cont_atlz, $code_erro, $code_ok);
			$this->GravarLog($array_log, $evento_log, 'ImportarClientesIntegracao');

			$retorno['cod_retorno'] = $trailler['cod_retorno'];
			$retorno['msg_retorno'] = $trailler['msg_retorno'];
		}

		$resultado['retorno']['cod_retorno'] = $retorno['cod_retorno'];
		$resultado['retorno']['msg_retorno'] = $retorno['msg_retorno'];

		return $resultado;
	}

	public function EnderecoModificado($cliente, $endereco, $bairro, $cidade, $cep, $estado)
	{
		$retorno = false;

		//Vamos testar campo a campo, para verificar se teve alteração no endereço ou se não temos coordenadas.
		//Esse teste é necessário porque buscamos as coordenadas através da Api do Google, e se passar muitas requesições (+500)
		//gera uma demora significativa podendo parar de responder por timeout.

		if (($retorno == false) && ($cliente->endereco != $endereco)) {
			$retorno = true;
		}

		if (($retorno == false) && ($cliente->bairro != $bairro)) {
			$retorno = true;
		}

		if (($retorno == false) && ($cliente->cidade != $cidade)) {
			$retorno = true;
		}

		if (($retorno == false) && ($cliente->cep != $cep)) {
			$retorno = true;
		}

		if (($retorno == false) && ($cliente->uf != $estado)) {
			$retorno = true;
		}

		if (($retorno == false) && (rgIgualZeroNull($cliente->geo_lat))) {
			$retorno = true;
		}

		if (($retorno == false) && (rgIgualZeroNull($cliente->geo_lng))) {
			$retorno = true;
		}

		return $retorno;
	}

	public function Local_GetMaxDataAltClientes($empresa)
	{
		$continuar = true;
		$retorno   = array();
		$dados     = array();

		//Testamos se o site não foi colocado em manutenção
		$app = new ApiUsoComum();
		$retorno = $app->AplicacaoLiberada();

		if (($retorno['cod_retorno']) != 'Z998') {
			$continuar = false;
		}

		if ($continuar) {

			//Verificar parâmetros
			if (rgIgualZeroNull($empresa)) {
				$continuar = false;
				$retorno['cod_retorno'] = 'Z202';
				$retorno['msg_retorno'] = 'Parâmetro [empresa] obrigatório. Valor esperado: > zero.';
			}

			$emp = DB::table('empresa')->where('codigo', '=', $empresa)->first();

			if (empty($emp)) {
				$continuar = false;
				$retorno['cod_retorno'] = 'B207';
				$msg_erro = rgGetMsgRetornoAPI($retorno['cod_retorno']);
				$msg_erro = str_replace('$empresa', $empresa, $msg_erro);
				$retorno['msg_retorno'] = $msg_erro;
			}
		}		

		if ($continuar) {
			$max_data_alt = null;
			$max_hora_alt = null;

			$max_data = DB::table('cliente')
				->select('dt_alt_cad')
				->where('empresa', $empresa)
				->orderBy('dt_alt_cad', 'desc')
				->limit(1)
				->first();

			if (is_null($max_data->dt_alt_cad)) {
				$retorno['cod_retorno'] = 'Z101';
				$retorno['msg_retorno'] = rgGetMsgRetornoAPI($retorno['cod_retorno']);
			} else {
				$max_data_alt = $max_data->dt_alt_cad;

				$max_hora = DB::table('cliente')
					->select('hr_alt_cad')
					->where('empresa', $empresa)
					->where('dt_alt_cad', $max_data_alt)
					->orderBy('hr_alt_cad', 'desc')
					->limit(1)
					->first();

				$max_hora_alt = $max_hora->hr_alt_cad;
				$retorno['cod_retorno'] = 'Z100';
				$retorno['msg_retorno'] = rgGetMsgRetornoAPI($retorno['cod_retorno']);
			}

			$dados['max_data_alt'] = $max_data_alt;
			$dados['max_hora_alt'] = $max_hora_alt;
		}		

		$resultado['retorno']['cod_retorno'] = $retorno['cod_retorno'];
		$resultado['retorno']['msg_retorno'] = $retorno['msg_retorno'];
		$resultado['dados'] = $dados;

		return $resultado;
	}

	public function Local_ImportarMotoristas($funcao, $empresa, $lista_motoristas)
	{

		$continuar = true;
		$retorno   = array();

		//Testamos se o site não foi colocado em manutenção
		$app = new ApiUsoComum();
		$retorno = $app->AplicacaoLiberada();

		if (($retorno['cod_retorno']) != 'Z998') {
			$continuar = false;
		}

		if ($continuar) {

			//Verificar parâmetros
			if (rgIgualZeroNull($empresa)) {
				$continuar = false;
				$retorno['cod_retorno'] = 'Z202';
				$retorno['msg_retorno'] = 'Parâmetro [empresa] obrigatório. Valor esperado: > zero.';
			} else {

				$emp = DB::table('empresa')->where('codigo', '=', $empresa)->first();

				if (empty($emp)) {
					$continuar = false;
					$retorno['cod_retorno'] = 'B207';
					$msg_erro = rgGetMsgRetornoAPI($retorno['cod_retorno']);
					$msg_erro = str_replace('$empresa', $empresa, $msg_erro);
					$retorno['msg_retorno'] = $msg_erro;
				}
			}
		}

		if ($continuar) {

			//Inicializar variáveis
			$timezone_app = date_default_timezone_get();

			$code_erro = "B208";
			$code_ok   = "B101";

			$cont_regs = 0;
			$cont_novos = 0;
			$cont_atlz = 0;
			$cont_erros = 0;

			$array_log  = array();

			if ($funcao == "load") {
				$msg_log    = 'Inicio: Carga inicial de motoristas (Emp: ' . $empresa . ')';
				$evento_log = 'carga_inicial_motoristas';
			} else {
				$msg_log    = 'Inicio: Sincronização de motoristas (Emp: ' . $empresa . ')';
				$evento_log = 'sincronizacao_motoristas';
			}

			//Verificar se tem motoristas na lista
			if ((!isset($lista_motoristas)) || ($lista_motoristas == null) || (count($lista_motoristas) == 0)) {
				$continuar = false;
				$retorno['cod_retorno'] = 'Z202';
				$retorno['msg_retorno'] = 'Parâmetro [lista_motoristas] sem conteúdo.';
			}
		}

		if ($continuar) {

			// Inserir um elemento no array do log:
			$ind_log = 0;

			$array_log[$ind_log]['tipo']       = '0';
			$array_log[$ind_log]['msg']        = $msg_log;
			$array_log[$ind_log]['err']        = null;
			$array_log[$ind_log]['status']     = '0';
			$array_log[$ind_log]['created_at'] = Carbon::now($timezone_app)->format('Y-m-d H:i:s');

			foreach ($lista_motoristas as $regmoto) {

				$cont_regs = $cont_regs + 1;

				$motorista = Motorista::where('cpf', $regmoto['cpf'])->first();

				if (empty($motorista)) { //Não achou

					try {

						$motoristaInsert = new Motorista();

						$motoristaInsert['cpf']         = $regmoto['cpf'];
						$motoristaInsert['nome']        = rgTitleCase($regmoto['nome']);
						$motoristaInsert['celular']     = rgAjustaTelefone($regmoto['celular']);
						$motoristaInsert['dt_alt_cad']  = $regmoto['data_alt'];
						$motoristaInsert['hr_alt_cad']  = $regmoto['hora_alt'];

						$motoristaInsert['ass_user_id'] = auth()->user()->id;

						$motoristaInsert->save();

						$cont_novos = $cont_novos + 1;
					} catch (\Exception $e) {
						// O indice do primeiro elemento do array_log é zero. Então com o count, obtemos sempre
						// o indice do próximo elemento a ser inserido
						$ind_log = count($array_log);

						// Inserir um elemento no array do log:
						$array_log[$ind_log]['tipo'] = '1';

						$msg_erro = rgGetMsgRetornoAPI('B209');
						$msg_erro = str_replace('$empresa', $empresa, $msg_erro);
						$msg_erro = str_replace('$cpf', $regmoto['cpf'], $msg_erro);
						$msg_erro = str_replace('$nome', $regmoto['nome'], $msg_erro);

						$array_log[$ind_log]['msg'] = $msg_erro;
						$array_log[$ind_log]['err'] = $e->getMessage();
						$array_log[$ind_log]['status'] = '1';
						$array_log[$ind_log]['created_at'] = Carbon::now($timezone_app)->format('Y-m-d H:i:s');

						$cont_erros = $cont_erros + 1;
					}
				} else { //Achou

					$data_alt   = $regmoto['data_alt'];
					$dt_alt_cad = null;

					if (!is_null($motorista->dt_alt_cad) && ($motorista->dt_alt_cad != "")) {
						$dt_alt_cad = $motorista->dt_alt_cad;
					}

					if (($data_alt > $dt_alt_cad) || (($data_alt == $dt_alt_cad) && ($regmoto['hora_alt'] > $motorista->hr_alt_cad))
					) {

						try {

							$motorista['nome']       = rgTitleCase($regmoto['nome']);
							$motorista['celular']    = rgAjustaTelefone($regmoto['celular']);
							$motorista['dt_alt_cad'] = $regmoto['data_alt'];
							$motorista['hr_alt_cad'] = $regmoto['hora_alt'];

							$motorista['ass_user_id'] = auth()->user()->id;

							$motorista->save();
							$cont_atlz = $cont_atlz + 1;
						} catch (\Exception $e) {
							// O indice do primeiro elemento do array_log é zero. Então com o count, obtemos sempre
							// o indice do próximo elemento a ser inserido
							$ind_log = count($array_log);

							// Inserir um elemento no array do log:
							$array_log[$ind_log]['tipo'] = '1';

							$msg_erro = rgGetMsgRetornoAPI('B210');
							$msg_erro = str_replace('$empresa', $empresa, $msg_erro);
							$msg_erro = str_replace('$cpf', $regmoto['cpf'], $msg_erro);
							$msg_erro = str_replace('$nome', $regmoto['nome'], $msg_erro);

							$array_log[$ind_log]['msg'] = $msg_erro;
							$array_log[$ind_log]['err'] = $e->getMessage();
							$array_log[$ind_log]['status'] = '1';
							$array_log[$ind_log]['created_at'] = Carbon::now($timezone_app)->format('Y-m-d H:i:s');

							$cont_erros = $cont_erros + 1;
						}
					}
				}
			}

			$trailler = $this->AdicionarLogTrailler($array_log, $funcao, $empresa, $cont_erros, $cont_regs, $cont_novos, $cont_atlz, $code_erro, $code_ok);

			//Para gravar o log apenas se tiver alguma alteração ou erro.
			if (($cont_erros > 0) || ($cont_novos > 0) || ($cont_atlz > 0)) {
				$this->GravarLog($array_log, $evento_log, 'ImportarMotoristasIntegracao');
			}

			$retorno['cod_retorno'] = $trailler['cod_retorno'];
			$retorno['msg_retorno'] = $trailler['msg_retorno'];
		}

		$resultado['retorno']['cod_retorno'] = $retorno['cod_retorno'];
		$resultado['retorno']['msg_retorno'] = $retorno['msg_retorno'];

		return $resultado;
	}

	public function AdicionarLogTrailler(&$array_log, $funcao, $empresa, $cont_erros, $cont_regs, $cont_novos, $cont_atlz, $code_erro, $code_ok)
	{
		// Adicionar LOG: Trailler
		// Inserir um elemento no array do log:

		// O indice do primeiro elemento do array_log é zero. Então com o count, obtemos sempre
		// o indice do próximo elemento a ser inserido
		$retorno = array();
		$cod_retorno = 0;

		$ind_log = count($array_log);

		$array_log[$ind_log]['tipo'] = '9';

		$timezone_app = date_default_timezone_get();
		$array_log[$ind_log]['created_at'] = Carbon::now($timezone_app)->format('Y-m-d H:i:s');

		if ($cont_erros > 0) {
			$cod_retorno = $code_erro;
			$msg_erro = rgGetMsgRetornoAPI($code_erro);
			$msg_erro = str_replace('$funcao', $funcao, $msg_erro);
			$msg_erro = str_replace('$empresa', $empresa, $msg_erro);
			$msg_erro = str_replace('$cont_regs', $cont_regs, $msg_erro);
			$msg_erro = str_replace('$cont_novos', $cont_novos, $msg_erro);
			$msg_erro = str_replace('$cont_atlz', $cont_atlz, $msg_erro);
			$msg_erro = str_replace('$cont_erros', $cont_erros, $msg_erro);
			$array_log[$ind_log]['msg'] = $msg_erro;
			$array_log[$ind_log]['err'] = null;
			$array_log[$ind_log]['status'] = '1';
		} else {
			$cod_retorno = $code_ok;
			$msg_erro = rgGetMsgRetornoAPI($code_ok);
			$msg_erro = str_replace('$funcao', $funcao, $msg_erro);
			$msg_erro = str_replace('$empresa', $empresa, $msg_erro);
			$msg_erro = str_replace('$cont_regs', $cont_regs, $msg_erro);
			$msg_erro = str_replace('$cont_novos', $cont_novos, $msg_erro);
			$msg_erro = str_replace('$cont_atlz', $cont_atlz, $msg_erro);
			$array_log[$ind_log]['msg'] = $msg_erro;
			$array_log[$ind_log]['err'] = null;
			$array_log[$ind_log]['status'] = '0';
		}

		$retorno['cod_retorno'] = $cod_retorno;
		$retorno['msg_retorno'] = $msg_erro;

		return $retorno;
	}

	public function Local_ImportarColetas($empresa, $lista_coletas)
	{

		$continuar = true;
		$retorno   = array();
		$info_coleta = array();

		//Testamos se o site não foi colocado em manutenção
		$app = new ApiUsoComum();
		$retorno = $app->AplicacaoLiberada();

		if (($retorno['cod_retorno']) != 'Z998') {
			$continuar = false;
		}

		if ($continuar) {

			// Verificar parâmetros
			if (rgIgualZeroNull($empresa)) {
				$continuar = false;
				$retorno['cod_retorno'] = 'Z202';
				$retorno['msg_retorno'] = 'Parâmetro [empresa] obrigatório. Valor esperado: > zero.';
			} else {

				$emp = DB::table('empresa')->where('codigo', '=', $empresa)->first();

				if (empty($emp)) {
					$continuar = false;
					$retorno['cod_retorno'] = 'B207';
					$msg_erro = rgGetMsgRetornoAPI($retorno['cod_retorno']);
					$msg_erro = str_replace('$empresa', $empresa, $msg_erro);
					$retorno['msg_retorno'] = $msg_erro;
				}
			}
		}

		if ($continuar) {

			//Inicializar variáveis
			$timezone_app = date_default_timezone_get();

			$cont_regs  = 0;
			$cont_novos = 0;
			$cont_exist = 0;
			$cont_erros = 0;

			$array_log  = array();

			$msg_log    = 'Inicio: Importação das coletas em aberto (Emp: ' . $empresa . ')';
			$evento_log = 'importacao_coletas';

			//Verificar se tem clientes na lista
			if ((!isset($lista_coletas)) || ($lista_coletas == null) || (count($lista_coletas) == 0)) {
				$continuar = false;
				$retorno['cod_retorno'] = 'Z202';
				$retorno['msg_retorno'] = 'Parâmetro [lista_coletas] sem conteúdo.';
			}
		}

		if ($continuar) {
			// Adicionar LOG: HEADER
			// Inserir um elemento no array do log:

			$ind_log = 0;
			$ind_info = 0;

			$array_log[$ind_log]['tipo']       = '0';
			$array_log[$ind_log]['msg']        = $msg_log;
			$array_log[$ind_log]['err']        = null;
			$array_log[$ind_log]['status']     = '0';
			$array_log[$ind_log]['created_at'] = Carbon::now($timezone_app)->format('Y-m-d H:i:s');

			// Para cada elemento de $lista_coletas
			foreach ($lista_coletas as $reglista_coletas) {

				// Inicializar as variáveis
				$cont_regs = $cont_regs + 1;
				$motorista_id = null;

				// Variáveis para geolocalização
				$geo_lat_coleta  = 0;
				$geo_lng_coleta  = 0;
				$geo_lat_entrega = 0;
				$geo_lng_entrega = 0;
				$distancia_km    = 0;
				$tempo_estimado  = 0;

				$ind_info = count($info_coleta);

				$info_coleta[$ind_info]['cod_coleta']   = null;
				$info_coleta[$ind_info]['coleta_id']    = null;
				$info_coleta[$ind_info]['data_enviada'] = null;
				$info_coleta[$ind_info]['result']       = null;

				$coleta = Coleta::where('empresa', $empresa)
					->where('numero', $reglista_coletas['numero'])
					->first();

				if (!empty($coleta)) { // Não é vazio, achou a coleta

					// Adicionar no array de dados para retorno
					$info_coleta[$ind_info]['cod_coleta']   = $coleta->numero;
					$info_coleta[$ind_info]['coleta_id']    = $coleta->id;
					$info_coleta[$ind_info]['data_enviada'] =
						Carbon::createFromFormat('Y-m-d H:i:s', $coleta->created_at)->format('Y-m-d H:i:s');
					$info_coleta[$ind_info]['result']       = 'found';

					$cont_exist = $cont_exist + 1;
				} else {  // Não achou a coleta -> inserimos os dados de coleta enviados

					if (rgDifZeroNull($reglista_coletas['cpf_motorista'])) {
						$motorista = DB::table('motorista')
							->where('cpf', '=', $reglista_coletas['cpf_motorista'])
							->first();

						if (!empty($motorista)) {
							$motorista_id = $motorista->id;
						}
					}

					// No sistema da Domper estavam vindo placas só com um traço
					if (trim(str_replace('-', '', $reglista_coletas['placa_coleta'])) == '') {
						$reglista_coletas['placa_coleta'] = null;
					} else {

						$veiculo = DB::table('veiculo')
							->select('veiculo.*', 'tipo_veiculo.classe')

							// Aqui tem que ser OUTER JOIN para encontrar a placa mesmo 
							// que não tenha um tipo de veículo definido

							->leftjoin('tipo_veiculo', 'tipo_veiculo.codigo', '=', 'veiculo.cod_tipo_veiculo')
							->where('veiculo.placa', '=', strtoupper($reglista_coletas['placa_coleta']))
							->first();

						if (!empty($veiculo)) {
							if ($veiculo->classe == 'C') { // Cavalo
								// Veículos do tipo 'Cavalo' NÃO podem atender solicitações.
								$reglista_coletas['placa_coleta'] = null;
							}
						} else {
							// Não atribuimos placas não cadastradas
							$reglista_coletas['placa_coleta'] = null;
						}
					}

					if ($reglista_coletas['cod_tipo_veiculo'] != null) {

						$tipo_veiculo = DB::table('tipo_veiculo')
							->select('tipo_veiculo.codigo')
							->where('tipo_veiculo.codigo', '=', $reglista_coletas['cod_tipo_veiculo'])
							->first();

						if (empty($tipo_veiculo)) {
							// Não atribuimos tipos de veículo não cadastrados
							$reglista_coletas['cod_tipo_veiculo'] = null;
						}
					}

					$loc_coleta = DB::table('cliente')
						->where('cliente.codigo', '=', $reglista_coletas['cod_loc_coleta'])
						->where('cliente.empresa', '=', $empresa)
						->first();

					if (!empty($loc_coleta)) {
						$geo_lat_coleta = $loc_coleta->geo_lat;
						$geo_lng_coleta = $loc_coleta->geo_lng;
					}

					$loc_entrega = DB::table('cliente')
						->where('cliente.codigo', '=', $reglista_coletas['cod_loc_entrega'])
						->where('cliente.empresa', '=', $empresa)
						->first();

					if (!empty($loc_entrega)) {
						$geo_lat_entrega = $loc_entrega->geo_lat;
						$geo_lng_entrega = $loc_entrega->geo_lng;
					}

					$array_tempo_dist = $this->CalcularDistanciaOrigem_Destino(
						$geo_lat_coleta,
						$geo_lng_coleta,
						$geo_lat_entrega,
						$geo_lng_entrega
					);

					$distancia_km   = $array_tempo_dist['distance'];
					$tempo_estimado = $array_tempo_dist['duration'];

					//O tempo estimado está calculado em segundos. Fazemos a conversão para "H:i:s" 
					$tempo_estimado = rgSecondsToTime($tempo_estimado);

					try {

						$novaColeta = $this->InsereNovaColeta(
							$empresa,
							$reglista_coletas,
							$motorista_id,
							$distancia_km,
							$tempo_estimado
						);

						$info_coleta[$ind_info]['cod_coleta']   = $novaColeta['numero'];
						$info_coleta[$ind_info]['coleta_id']    = $novaColeta['id'];
						$info_coleta[$ind_info]['data_enviada'] =
							Carbon::createFromFormat('Y-m-d H:i:s', $novaColeta['created_at'])->format('Y-m-d H:i:s');

						$info_coleta[$ind_info]['result'] = 'ok';
						$cont_novos = $cont_novos + 1;
					} catch (\Exception $e) {

						// O indice do primeiro elemento do array_log é zero. Então com o count, 
						// obtemos sempre o indice do próximo elemento a ser inserido
						$ind_log = count($array_log);

						// Adicionar LOG: Detail

						$info_coleta[$ind_info]['cod_coleta'] = $reglista_coletas['numero'];
						$info_coleta[$ind_info]['result'] = 'error';

						$cont_erros = $cont_erros + 1;

						// Inserir um elemento no array do log:
						$array_log[$ind_log]['tipo'] = '1';

						$msg_erro = rgGetMsgRetornoAPI('E300');
						$msg_erro = str_replace('$empresa', $empresa, $msg_erro);
						$msg_erro = str_replace('$numero', $reglista_coletas['numero'], $msg_erro);

						$array_log[$ind_log]['msg']    = $msg_erro;
						$array_log[$ind_log]['err']    = $e->getMessage();
						$array_log[$ind_log]['status'] = '1';
						$array_log[$ind_log]['created_at'] = Carbon::now($timezone_app)->format('Y-m-d H:i:s');
					}
				}
			}

			// Adicionar Log: Trailler
			$trailler = $this->AdicionarLogTraillerColeta(
				$array_log,
				$empresa,
				$cont_regs,
				$cont_erros,
				$cont_novos,
				$cont_exist
			);

			$this->GravarLog($array_log, $evento_log, 'ImportarColetas');

			$retorno['cod_retorno'] = $trailler['cod_retorno'];
			$retorno['msg_retorno'] = $trailler['msg_retorno'];
		}

		$resultado['retorno']['cod_retorno'] = $retorno['cod_retorno'];
		$resultado['retorno']['msg_retorno'] = $retorno['msg_retorno'];

		$resultado['dados'] = $info_coleta;

		return $resultado;
	}

	public function CalcularDistanciaOrigem_Destino($geo_lat_coleta, $geo_lng_coleta, $geo_lat_entrega, $geo_lng_entrega)
	{
		$distance_matrix = new DistanceMatrix();
		$route = $distance_matrix->getServiceRoutes();

		$api_service = $route['api_service'];
		$api_key = $route['api_key'];

		$array_tempo_dist = GetDrivingDistance(
			$geo_lat_coleta,
			$geo_lng_coleta,
			$geo_lat_entrega,
			$geo_lng_entrega,
			$api_service,
			$api_key
		);

		return $array_tempo_dist;
	}

	public function InsereNovaColeta(
		$empresa,
		$reglista_coletas,
		$motorista_id,
		$distancia_km,
		$tempo_estimado
	) {

		$coletaInsert = new Coleta();

		$coletaInsert['empresa']         = $empresa;
		$coletaInsert['numero']          = $reglista_coletas['numero'];
		$coletaInsert['data_cad']        = $reglista_coletas['data_cad'];
		$coletaInsert['hora_cad']        = $reglista_coletas['hora_cad'];
		$coletaInsert['dt_prev_coleta']  = $reglista_coletas['dt_prev_coleta'];
		$coletaInsert['hr_prev_coleta']  = $reglista_coletas['hr_prev_coleta'];
		$coletaInsert['dt_prev_entrega'] = $reglista_coletas['dt_prev_entrega'];
		$coletaInsert['hr_prev_entrega'] = $reglista_coletas['hr_prev_entrega'];
		$coletaInsert['entrega_urgente'] = rgSetaDefault($reglista_coletas['entrega_urgente'], 'N');
		$coletaInsert['cod_cliente']     = $reglista_coletas['cod_cliente'];
		$coletaInsert['solicitante']     = $reglista_coletas['solicitante'];
		$coletaInsert['cod_loc_coleta']  = $reglista_coletas['cod_loc_coleta'];
		$coletaInsert['cod_loc_entrega'] = $reglista_coletas['cod_loc_entrega'];
		$coletaInsert['recebedor']       = $reglista_coletas['recebedor'];
		$coletaInsert['peso']            = $reglista_coletas['peso'];
		$coletaInsert['volumes']         = $reglista_coletas['volumes'];
		$coletaInsert['especie']         = rgTitleCase($reglista_coletas['especie']);
		$coletaInsert['sis_carga']       = rgSetaDefault($reglista_coletas['sis_carga'], 'M');
		$coletaInsert['alt_carga']       = $reglista_coletas['alt_carga'];
		$coletaInsert['larg_carga']      = $reglista_coletas['larg_carga'];
		$coletaInsert['comp_carga']      = $reglista_coletas['comp_carga'];
		$coletaInsert['placa_coleta']    = rgSetaDefault(strtoupper($reglista_coletas['placa_coleta']), null);

		// Não vem nos parâmetros. Gravamos Fixo "D" //Diária
		$coletaInsert['coleta_fixa']      = 'D';
		$coletaInsert['tipo_frete'] 	  = rgSetaDefault($reglista_coletas['tipo_frete'], 'N');
		$coletaInsert['cod_tipo_veiculo'] = $reglista_coletas['cod_tipo_veiculo'];
		$coletaInsert['caract_coleta']    = $reglista_coletas['caract_coleta'];
		$coletaInsert['distancia_km']     = $distancia_km;
		$coletaInsert['tempo_estimado']   = $tempo_estimado;
		$coletaInsert['motor_coleta_id']  = $motorista_id;
		$coletaInsert['obs_coleta']       = $reglista_coletas['obs_coleta'];

		$coletaInsert['receber_nf_frete'] = rgSetaDefault($reglista_coletas['receber_nf_frete'], 'N');
		$coletaInsert['origem_reg']       = 'SD';
		$coletaInsert['status']           = 'C0';

		// Não vem nos parâmetros. Gravamos Fixo "N"
		$coletaInsert['coleta_export'] = 'N';

		// Não vem nos parâmetros. Gravamos Fixo "N"
		$coletaInsert['entrega_export'] = 'N';

		$coletaInsert['ass_user_id'] = auth()->user()->id;

		$coletaInsert->save();

		return $coletaInsert;
	}


	public function AdicionarLogTraillerColeta(
		&$array_log,
		$empresa,
		$cont_regs,
		$cont_erros,
		$cont_novos,
		$cont_exist
	) {
		// O indice do primeiro elemento do array_log é zero. Então com o count, obtemos sempre
		// o indice do próximo elemento a ser inserido
		$retorno = array();
		$cod_retorno = 0;

		$ind_log = count($array_log);

		$array_log[$ind_log]['tipo'] = '9';

		$timezone_app = date_default_timezone_get();
		$array_log[$ind_log]['created_at'] = Carbon::now($timezone_app)->format('Y-m-d H:i:s');

		$cod_retorno = 'E100';
		$msg_retorno = rgGetMsgRetornoAPI($cod_retorno);
		$msg_retorno = str_replace('$empresa', $empresa, $msg_retorno);
		$msg_retorno = str_replace('$cont_regs', $cont_regs, $msg_retorno);
		$msg_retorno = str_replace('$cont_novos', $cont_novos, $msg_retorno);
		$msg_retorno = str_replace('$cont_exist', $cont_exist, $msg_retorno);
		$msg_retorno = str_replace('$cont_erros', $cont_erros, $msg_retorno);

		$array_log[$ind_log]['msg']  = $msg_retorno;
		$array_log[$ind_log]['err'] = null;

		if ($cont_erros > 0) {
			$array_log[$ind_log]['status'] = '1';
		} else {
			$array_log[$ind_log]['status'] = '0';
		}

		$retorno['cod_retorno'] = $cod_retorno;
		$retorno['msg_retorno'] = $msg_retorno;

		return $retorno;
	}

	public function Local_ExportarColetas($empresa)
	{
		$continuar = true;
		$retorno   = array();

		// Array para armazenar as coletas para retorno
		$lista_coletas = array();

		//Testamos se o site não foi colocado em manutenção
		$app = new ApiUsoComum();
		$retorno = $app->AplicacaoLiberada();

		if (($retorno['cod_retorno']) != 'Z998') {
			$continuar = false;
		}

		if ($continuar) {

			// Verificar parâmetros
			if (rgIgualZeroNull($empresa)) {
				$continuar = false;
				$retorno['cod_retorno'] = 'Z202';
				$retorno['msg_retorno'] = 'Parâmetro [empresa] obrigatório. Valor esperado: > zero.';
			} else {

				$emp = DB::table('empresa')->where('codigo', '=', $empresa)->first();

				if (empty($emp)) {
					$continuar = false;
					$retorno['cod_retorno'] = 'B207';
					$msg_erro = rgGetMsgRetornoAPI($retorno['cod_retorno']);
					$msg_erro = str_replace('$empresa', $empresa, $msg_erro);
					$retorno['msg_retorno'] = $msg_erro;
				}
			}
		}

		if ($continuar) {

			$cont_regs = 0;

			// Aqui interessam os registros nos quais a operação de COLETA ainda não exportada 
			// OU operação de ENTREGA não exportada para o ERP. 

			// As coletas NOVAS (criadas no sistema web) e que ainda não foram exportadas para o ERP...
			// também estarão como coletas não exportadas (coleta_export  = 'N').			

			// Solicitações origem (solic_origem_id = 0) com 'coleta_fixa' = 'C' - contrato: exportamos SOMENTE 
			// quando stauts for = 'ER' - Entrega realizada. Para estas solicitações... consideramos as 
			// HORAS DA ENTREGA como FIM DE EXPEDIENTE. Assim... todas as notas fiscais
			// das COMANDAS  (solic_origem_id <> 0) serão exportadas para o sistema ERP.

			$coletas = DB::table('coleta')
				->select('coleta.*', 'motorista.cpf as cpf_motorista', 'tvn.descricao AS descr_tipo_veiculo_nec')
				->leftjoin('motorista', 'motorista.id', '=', 'coleta.motor_coleta_id')
				->leftjoin('tipo_veiculo AS tvn', 'tvn.codigo', '=', 'coleta.cod_tipo_veiculo_nec')

				->where('coleta.empresa', '=', $empresa)

				// DESCONSIDERAMOS comandas de contratos e solicitações auxiliares Multi-destinos
				// CONSIDERAMOS as solicitações de REENTREGA ou DEVOLUÇÃO
				//
				->where(function ($query) {
					$query->where(function ($query1) {
						$query1->whereNull('coleta.solic_origem_id')
							->orWhere('coleta.solic_origem_id', '=', '0');
					})->orWhereIn('coleta.reentrega', ['R', 'D']);
				})

				// => Solicitações DIÁRIAS ou MULTI-DESTINOS: 
				//  
				//       Selecionamos as solicitações na fase de COLETA quando status que indica 
				//       que a coleta já foi FEITA ou ENCERRADA => 'CN' ou 'CR'
				//  
				//       Selecionamos também aquelas que estão na fase de ENTREGA com qualquer
				//       status... porque também indicam que a fase de COLETA já foi FEITA. Com isso,
				//       SE a solicitação não foi exportada anteriormente na fase de coleta por demora
				//       ou problemas de internet... será exportada neste momento. 
				//
				// => Solicitações CONTRATO: 
				//  
				//       Selecionamos somente as solicitações com EXPEDIENTE ENCERRADO => 'ER' 
				//
				->where(function ($query) {
					$query->where(function ($query1) {
						$query1->where('coleta.coleta_fixa', '=', 'C')
							->where('coleta.status', '=', 'ER');
					})
						->orWhere(function ($query2) {
							$query2->where('coleta.coleta_fixa', '!=', 'C')
								->whereIn('coleta.status', ['CN', 'CR', 'EN', 'EP', 'ER']);
						})->orWhere(function ($query3) {

							// Aqui repetimos a condição de exportação da solicitação na fase de COLETA...porque 
							// queremos exportar registros que estão na fase de ENTREGA EM ANDAMENTO... quando
							// os dados da fase de COLETA não foram exportados anteriormente quando a coleta foi
							// REALIZADA (ex: falha de comunicação com o servidor do ERP , internet, ...
							//
							$query3->where('coleta.coleta_fixa', '!=', 'C')
								->whereIn('coleta.status', ['E0', 'E1', 'E2', 'E3', 'E4'])
								->where(function ($query) {
									$query->where(function ($query1) {
										$query1->whereRaw("(coleta.coleta_export IS NULL OR coleta.coleta_export = 'N')")
											->whereNotNull('coleta.dt_efet_coleta');
									})->orWhere(function ($query2) {
										$query2->where('coleta.coleta_export', '=', 'S')
											->whereRaw('( coleta.dt_efet_coleta > date(coleta.dt_coleta_export) ' .
												'OR (coleta.dt_efet_coleta = date(coleta.dt_coleta_export) AND coleta.hr_sai_coleta > time(coleta.dt_coleta_export) ) )');
									});
								});
						});
				})

				// Quando os dados da etapa COLETA já foram exportados (coleta_export = 'S') e a hora de saída da coleta
				// for MAIOR que a hora de exportação => a coleta foi REABERTA. Neste caso... exportamos novamente a
				// solicitação para atualizar o sistema de gestão.
				->where(function ($query) {
					$query->where(function ($query1) {
						$query1->whereRaw("(coleta.coleta_export IS NULL OR coleta.coleta_export = 'N')")
							->whereNotNull('coleta.dt_efet_coleta');
					})->orWhere(function ($query2) {
						$query2->where('coleta.coleta_export', '=', 'S')
							->whereRaw('( coleta.dt_efet_coleta > date(coleta.dt_coleta_export) ' .
								'OR (coleta.dt_efet_coleta = date(coleta.dt_coleta_export) AND coleta.hr_sai_coleta > time(coleta.dt_coleta_export) ) )');
					})->orWhere(function ($query3) {
						$query3->whereRaw("(coleta.entrega_export IS NULL OR coleta.entrega_export = 'N')")
							->whereNotNull('coleta.dt_efet_entrega');
					});
				})
				->get();

			if (empty($coletas)) {
				$continuar = false;
				$retorno['cod_retorno'] = 'Z101';
				$retorno['msg_retorno'] = rgGetMsgRetornoAPI($retorno['cod_retorno']);
			}
		}

		if ($continuar) {

			$cont_regs = count($coletas);
			$ind_col   = 0;

			// Para cada elemento de $lista_coletas
			foreach ($coletas as $regcol) {

				$info_coleta = array();

				// Dados gerais do registro de coleta
				$info_coleta['coleta_id']       = $regcol->id;

				// Se o número da coleta for nullo, setamos zero para não dar 
				// erro nas store procedures do Domper
				if ($regcol->numero == null) {
					$info_coleta['cod_coleta']  = 0;
				} else {
					$info_coleta['cod_coleta']  = $regcol->numero;
				}

				$info_coleta['data_cad']        = $regcol->data_cad;
				$info_coleta['hora_cad']        = $regcol->hora_cad;
				$info_coleta['dt_prev_coleta']  = $regcol->dt_prev_coleta;
				$info_coleta['hr_prev_coleta']  = $regcol->hr_prev_coleta;
				$info_coleta['dt_prev_entrega'] = $regcol->dt_prev_entrega;
				$info_coleta['hr_prev_entrega'] = $regcol->hr_prev_entrega;
				$info_coleta['entrega_urgente'] = $regcol->entrega_urgente;

				$info_coleta['cod_cliente']     = $regcol->cod_cliente;
				$info_coleta['cod_loc_coleta']  = $regcol->cod_loc_coleta;

				// 'solicitante': setamos valores default para que o usuário 
				// não precise preencher no sistema de gestão.
				$info_coleta['solicitante']     = rgSetaDefault($regcol->solicitante, '-');

				$info_coleta['cod_loc_entrega'] = $regcol->cod_loc_entrega;
				$info_coleta['recebedor']       = $regcol->recebedor;

				// 'peso', 'volumes' e 'especie': setamos valores default para que o usuário 
				// não precise preencher no sistema de gestão.
				$info_coleta['peso']            = rgSetaDefault($regcol->peso, 0);
				$info_coleta['volumes']         = rgSetaDefault($regcol->volumes, 0);
				$info_coleta['especie']         = rgSetaDefault($regcol->especie, 'Volumes');

				$info_coleta['caract_coleta']   = $regcol->caract_coleta;
				$info_coleta['obs_coleta']      = $regcol->obs_coleta;

				// 'sis_carga', 'alt_carga', 'larg_carga', 'comp_carga': setamos valores default 
				// para que o usuário não precise preencher no sistema de gestão.
				$info_coleta['sis_carga']       = rgSetaDefault($regcol->sis_carga, 'M');
				$info_coleta['alt_carga']       = rgSetaDefault($regcol->alt_carga, 1);
				$info_coleta['larg_carga']      = rgSetaDefault($regcol->larg_carga, 1);
				$info_coleta['comp_carga']      = rgSetaDefault($regcol->comp_carga, 1);

				$info_coleta['cod_tipo_veiculo'] = $regcol->cod_tipo_veiculo;
				$info_coleta['placa_coleta']     = $regcol->placa_coleta;
				$info_coleta['cpf_motorista']    = $regcol->cpf_motorista;

				// Dados da operação de COLETA
				$info_coleta['dt_efet_coleta']    = $regcol->dt_efet_coleta;
				$info_coleta['hr_partida_coleta'] = $regcol->hr_partida_coleta;
				$info_coleta['hr_cheg_coleta']    = $regcol->hr_cheg_coleta;
				$info_coleta['hr_atend_coleta']   = $regcol->hr_atend_coleta;
				$info_coleta['hr_sai_coleta']     = $regcol->hr_sai_coleta;

				// Notas fiscais da COLETA
				$notas_coleta = $this->RetonarStrNotasFiscaisColeta($regcol->id);

				$info_coleta['notas_fiscais']   = $notas_coleta['notas_fiscais'];
				$info_coleta['valor_notas']     = rgFloatToString($notas_coleta['valor_notas']);

				// Romaneio como documento de carga
				$info_coleta['aceitar_foto_rom'] = $regcol->aceitar_foto_rom;
				$info_coleta['tipo_frete'] = $regcol->tipo_frete;

				// Dados da operação de ENTREGA
				$info_coleta['placa_entrega']      = null;
				$info_coleta['dt_efet_entrega']    = null;
				$info_coleta['hr_partida_entrega'] = null;
				$info_coleta['hr_cheg_entrega']    = null;
				$info_coleta['hr_atend_entrega']   = null;
				$info_coleta['hr_sai_entrega']     = null;

				// Carregamos os dados da Entrega somente depois que ela está concluída.
				if (in_array($regcol->status, ['EN', 'EP', 'ER'])) {
					$info_coleta['placa_entrega']      = $regcol->placa_entrega;
					$info_coleta['dt_efet_entrega']    = $regcol->dt_efet_entrega;
					$info_coleta['hr_partida_entrega'] = $regcol->hr_partida_entrega;
					$info_coleta['hr_cheg_entrega']    = $regcol->hr_cheg_entrega;
					$info_coleta['hr_atend_entrega']   = $regcol->hr_atend_entrega;
					$info_coleta['hr_sai_entrega']     = $regcol->hr_sai_entrega;
				}

				$info_coleta['tempo_desloc_pavilhao'] = $regcol->tempo_desloc_pavilhao;
				$info_coleta['entrega_consolidada']   = $regcol->entrega_consolidada;

				// Outros dados do registro
				$info_coleta['receber_nf_frete'] = $regcol->receber_nf_frete;
				$info_coleta['comanda']          = $regcol->id;

				$info_coleta['data_enviada']     = $regcol->created_at;

				// Campo 'sucesso' define se a COLETA ou a ENTREGA foi efetivamente realizada
				$info_coleta['sucesso']      = null;

				$info_coleta['origem_reg']   = null;

				$info_coleta['acao_coleta']  = null;
				$info_coleta['acao_entrega'] = null;

				// A rotina "AjustaValores" faz os ajustes necessários na coleta
				// corrente e devolve um array que será adicionado no array
				// que vai conter todas as coletas que serão devolvidas ao final da API
				$lista_coletas[$ind_col] = $this->AjustaValores($regcol, $info_coleta);

				$ind_col++;
			}

			// Geração do LOG
			if ($cont_regs <= 0) {
				$retorno['cod_retorno'] = 'Z101';
				$retorno['msg_retorno'] = rgGetMsgRetornoAPI($retorno['cod_retorno']);
			} else {
				$retorno = $this->GravaLogExportaColetas($empresa, $cont_regs);
			}
		}

		$resultado['retorno']['cod_retorno'] = $retorno['cod_retorno'];
		$resultado['retorno']['msg_retorno'] = $retorno['msg_retorno'];

		$resultado['dados'] = $lista_coletas;

		return $resultado;
	}


	public function AjustaValores($coleta, $info_coleta)
	{

		//No sistema DOMPER "1" = Sucesso, "2" = Não ok
		// Esses status indicam que a coleta FOI REALIZADA = > "CR", 'E0', 'E1', 'E2', 'E3', 'E4'
		if (in_array($coleta->status, ['CR', 'E0', 'E1', 'E2', 'E3', 'E4'])) {

			$info_coleta['sucesso'] = '1';

			if ($coleta->cod_tipo_veiculo_nec <> $coleta->cod_tipo_veiculo) {

				/* O tipo de veículo NECESSÁRIO para fazer a coleta FOI diferente do tipo de 
				   veículo SOLICITADO pelo cliente. O tipo de veículo NECESSÁRIO é o veículo 
				   que teria a capacidade para fazer a coleta (dimensoes, capacid. KG). 
				*/
				if (trim($info_coleta['obs_coleta'] <> '')) {
					$info_coleta['obs_coleta'] = $info_coleta['obs_coleta'] . ' | ';
				}

				$info_coleta['obs_coleta'] = $info_coleta['obs_coleta'] .
					'VEICULO NECESSÁRIO: ' . $coleta->descr_tipo_veiculo_nec;
			}
		} else {

			if ($coleta->status == 'CN') {

				$info_coleta['sucesso'] = '2';

				if (trim($info_coleta['obs_coleta'] <> '')) {
					$info_coleta['obs_coleta'] = $info_coleta['obs_coleta'] . ' | ';
				}

				if ($coleta->mot_nao_coleta == '01') {

					// Quando coleta NÃO REALIZADA - COM DESLOCAMENTO:
					//
					// Gravamos os horários da ENTREGA com os mesmos valores que a
					// COLETA para que no DOMPER a solicitação fique como FINALIZADA. 
					//
					$info_coleta['dt_efet_entrega']    = $coleta->dt_efet_coleta;
					$info_coleta['hr_partida_entrega'] = $coleta->hr_partida_coleta;
					$info_coleta['hr_cheg_entrega']    = $coleta->hr_cheg_coleta;
					$info_coleta['hr_atend_entrega']   = $coleta->hr_atend_coleta;
					$info_coleta['hr_sai_entrega']     = $coleta->hr_sai_coleta;

					// Formatamos a distância para 'm' ou 'km' 
					//
					$info_coleta['obs_coleta'] = $info_coleta['obs_coleta'] .
						'COLETA CANCELADA (com deslocamento: ' . rgFormataDistancia($coleta->distancia_total) . ')';
				}

				if ($coleta->mot_nao_coleta == '02') {
					$info_coleta['obs_coleta'] = $info_coleta['obs_coleta'] . 'COLETA CANCELADA (sem deslocamento)';
				}

				if (trim($coleta->obs_nao_coleta <> '')) {
					$info_coleta['obs_coleta'] = $info_coleta['obs_coleta'] . ' | OBS. NÃO COLETA: ' . $coleta->obs_nao_coleta;
				}
			}
		}

		// Entrega NÃO REALIZADA
		if ($coleta->status == 'EN') {
			$info_coleta['obs_coleta'] = $info_coleta['obs_coleta'] . ' | ENTREGA NÃO REALIZADA: ' . rgGetMsgMotNaoEntregaColeta($coleta->mot_nao_entrega);
			$info_coleta['sucesso'] = '2';
		} else {
			// Entrega PARCIAL
			if ($coleta->status == 'EP') {
				$info_coleta['obs_coleta'] = $info_coleta['obs_coleta'] .  ' | ENTREGA PARCIAL';
				$info_coleta['sucesso'] = '1';
			} else {
				// Entrega REALIZADA
				if ($coleta->status == 'ER') {
					$info_coleta['sucesso'] = '1';
				}
			}
		}

		if ($coleta->reentrega == 'R') {
			$info_coleta['obs_coleta'] = $info_coleta['obs_coleta'] . ' | * REENTREGA *';
		} else {
			if ($coleta->reentrega == 'D') {
				$info_coleta['obs_coleta'] = $info_coleta['obs_coleta'] . ' | * DEVOLUÇÃO *';
			}
		}

		if ($coleta->origem_reg == 'SD') {
			// 'D' - Sistema Domper
			$info_coleta['origem_reg'] = 'D';
		} else {
			// 'A' - Aplicativo Maser
			$info_coleta['origem_reg'] = "A";
		}

		// Testamos "coleta_export" <> "S" para não dar problema quando é NULL
		// O PHP considera NULL diferente de "S" neste caso
		// SE os dados da COLETA não foram exportados e tem uma data de coleta
		if (($coleta->coleta_export <> 'S') && (empty($coleta->dt_efet_coleta) == false)) {

			if (rgIgualZeroNull($coleta->numero)) {
				// Este registro foi criado no sistema web => (numero = 0) 
				// NÃO foi exportado para o ERP => (coleta_export <> 'S')
				//
				// Então: o serviço de integração deverá INSERIR o registro da solicitação.
				$info_coleta['acao_coleta'] = 'insert';
			} else {

				// Aqui... o registro foi criado no sistema ERP => (numero <> 0) 
				// E ainda NÃO foi exportado para o ERP => (coleta_export <> 'S')
				//
				// Então: o serviço de integração deverá apenas ATUALIZAR os dados 
				// do processo de COLETA
				$info_coleta['acao_coleta'] = 'update';
			}
		} else {

			// Só por segurança testamos se a data não é nulla
			if ($coleta->dt_coleta_export <> null) {
				$dt_coleta_export = Carbon::createFromFormat('Y-m-d H:i:s', $coleta->dt_coleta_export)->format('Y-m-d');
				$hr_coleta_export = Carbon::createFromFormat('Y-m-d H:i:s', $coleta->dt_coleta_export)->format('H:i:s');
			}

			if (($coleta->coleta_export == 'S') && (($coleta->dt_efet_coleta > $dt_coleta_export) || ($coleta->dt_efet_coleta = $dt_coleta_export && $coleta->hr_sai_coleta > $hr_coleta_export))
			) {

				// Este tratamento garante a atualização dos dados da COLETA no
				// ERP quando houve uma reabertura de coleta... e os dados já tinham
				// sido exportados para o ERP. 
				//
				// Então: o serviço de integração deverá apenas ATUALIZAR os dados 
				// do processo de COLETA
				$info_coleta['acao_coleta'] = 'update';
			} else {
				// Aqui... os dados do processo de COLETA já foram exportados:
				// Então: o serviço de integração NÃO deverá fazer nada
				$info_coleta['acao_coleta'] = 'none';
			}
		}

		// SE os dados da ENTREGA não foram exportados, SE a entrega foi REALIZADA ou PARCIAL E  tem uma data de entrega.... 
		// OU SE a coleta foi cancelada COM DESLOCAMENTO ou se a ENTREGA não foi realizada, 
		// porque precisamos enviar a ação 'update' para que o SOFTWARE INTEGRADOR entenda que tem que atualizar os campos da ENTREGA também para este caso.

		// Testamos "entrega_export" <> "S" para não dar problema quando é NULL
		// O PHP considera NULL diferente de "S" neste caso
		if (
			($coleta->entrega_export <> 'S') && (
				(in_array($coleta->status, ['EP', 'ER']) && empty($coleta->dt_efet_entrega) == false) || ($coleta->status == 'CN' && $coleta->mot_nao_coleta == '01') || ($coleta->status == 'EN'))
		) {

			// Para definir a ação do processo de ENTREGA... NÃO testamos o campo 'numero = zero/null' 
			// porque... o registro da solicitação já foi criado pelo processo da COLETA, se foi necessário.
			// 
			// Aqui... o registro já deve existir no BD do ERP => (numero <> 0)
			// E os dados da ENTREGA NÃO foram exportados => (entrega_export <> 'S')
			//
			// Então: o serviço de integração deverá apenas ATUALIZAR os dados do processo de ENTREGA

			$info_coleta['acao_entrega'] = 'update';
		} else {

			// Aqui... os dados do processo de ENTREGA já foram exportados:
			//
			// Então: o serviço de integração NÃO deverá fazer nada com os  campos da ENTREGA
			$info_coleta['acao_entrega'] = 'none';
		}

		return $info_coleta;
	}

	/*
    |--------------------------------------------------------------------------
    | Retornar as notas fiscais agrupadas por série. (ATUAL)
    |--------------------------------------------------------------------------
    |
    | Esta rotina é a atual representação do retorno das Notas Fiscais.
	| Ela está agrupando as notas com as séries e parenteses.
    | Vamos manter ela por enquanto, enquanto o cliente não confirma 
    | a nova definição do retorno de notas.
    |
    */
	public function RetonarStrNotasFiscaisColeta($coleta_id)
	{
		// Inicializa Variáveis
		$notas_fiscais = '';
		$valor_notas   = 0;
		$dados = array();

		$coleta_nf = DB::table('coleta_nf')
			->select('coleta_nf.numero', 'coleta_nf.serie', 'coleta_nf.valor')
			->join('coleta', 'coleta.id', '=', 'coleta_nf.coleta_id')

			->where(function ($query) use ($coleta_id) {

				// Coleta DIÁRIA: pega notas ligadas diretamente à solicitação
				$query->where(function ($query1) use ($coleta_id) {
					$query1->where('coleta.coleta_fixa', '=',  'D')
						->where('coleta.id', '=', $coleta_id);
				})
					// Coleta CONTRATO: pega as notas das COMANDAS
					->orWhere(function ($query2) use ($coleta_id) {
						$query2->where('coleta.coleta_fixa', '=',  'C')
							->where('coleta.solic_origem_id', '=', $coleta_id);
					})
					// Coleta MULTI-DESTINOS: pega as notas da solicitação ORIGEM (mãe)
					->orwhere(function ($query1) use ($coleta_id) {
						$query1->where(function ($query2) {
							$query2->whereNull('coleta.solic_origem_id')
								->orWhere('coleta.solic_origem_id', '=', '0');
						})->Where(function ($query3) use ($coleta_id) {
							$query3->where('coleta.coleta_fixa', '=', 'M')
								->where('coleta.id', '=', $coleta_id);
						});
					});
			})

			->orderBy('coleta_nf.serie', 'asc')
			->orderBy('coleta_nf.numero', 'asc')
			->get();

		$serie_ant = '';
		$um_cinco_nf_ant = '';
		$str_num_nf = '';
		$notas_fiscais = '';
		$separador = '';

		foreach ($coleta_nf as $regcol_nf) {

			if ($regcol_nf->serie <> $serie_ant) {

				if ($serie_ant <> '') {
					$notas_fiscais = $notas_fiscais . $separador . 'S' . $serie_ant . '-(' . $str_num_nf . ')';
					$str_num_nf = '';
					$separador = ' ';
				}

				$serie_ant = $regcol_nf->serie;
			}

			$um_oito_nf_atu   = str_pad($regcol_nf->numero, 8, '0', STR_PAD_LEFT);
			$um_cinco_nf_atu  = substr($um_oito_nf_atu, 0, 5);
			$seis_oito_nf_atu = substr($um_oito_nf_atu, 5, 8);

			if ($um_cinco_nf_atu == $um_cinco_nf_ant) {
				$str_num_nf = $str_num_nf . '/' . ltrim($seis_oito_nf_atu, '0');
			} else {
				if (trim($str_num_nf) <> '') {
					$str_num_nf = $str_num_nf . ' ';
				}
				$str_num_nf = $str_num_nf . ltrim($um_oito_nf_atu, '0');
				$um_cinco_nf_ant = $um_cinco_nf_atu;
			}

			// Acumula o valor das notas fiscais
			$valor_notas   = $valor_notas + $regcol_nf->valor;
		}

		// Acrescenta os números de notas da última série que ainda não foi acrescenta a string das notas
		if ($str_num_nf <> '') {
			$notas_fiscais = $notas_fiscais . $separador . 'S' . $serie_ant . '-(' . $str_num_nf . ')';
		}

		// Vamos truncar em 300 caracteres, este é o tamanho máximo do campo que grava as notas.
		$notas_fiscais = mb_strimwidth($notas_fiscais, 0, 300);

		$dados['notas_fiscais'] = $notas_fiscais;
		$dados['valor_notas']   = $valor_notas;

		return $dados;
	}

	/*
    |--------------------------------------------------------------------------
    | Retornar as notas fiscais agrupadas por série. (NOVA)
    |--------------------------------------------------------------------------
    |
    | Esta rotina é a nova representação do retorno das Notas Fiscais.
	| Ela está separando as notas apenas por uma "/".
    | Vamos manter ela comentada, porque estamos esperando a confirmação
    | do cliente.	
    |
    */
	// public function RetonarStrNotasFiscaisColeta($coleta_id)
	// {

	// 	// Inicializa Variáveis
	// 	$notas_fiscais = '';
	// 	$valor_notas = 0;
	// 	$separador = ' / ';
	// 	$dados = array();

	// 	$coleta_nf = DB::table('coleta_nf')
	// 		->select('coleta_nf.numero', 'coleta_nf.serie', 'coleta_nf.valor')
	// 		->join('coleta', 'coleta.id', '=', 'coleta_nf.coleta_id')

	// 		->where(function ($query) use ($coleta_id) {

	// 			// Coleta DIÁRIA: pega notas ligadas diretamente à solicitação
	// 			$query->where(function ($query1) use ($coleta_id) {
	// 				$query1->where('coleta.coleta_fixa', '=',  'D')
	// 					->where('coleta.id', '=', $coleta_id);
	// 			})
	// 				// Coleta CONTRATO: pega as notas das COMANDAS
	// 				->orWhere(function ($query2) use ($coleta_id) {
	// 					$query2->where('coleta.coleta_fixa', '=',  'C')
	// 						->where('coleta.solic_origem_id', '=', $coleta_id);
	// 				})
	// 				// Coleta MULTI-DESTINOS: pega as notas da solicitação ORIGEM (mãe)
	// 				->orwhere(function ($query1) use ($coleta_id) {
	// 					$query1->where(function ($query2) {
	// 						$query2->whereNull('coleta.solic_origem_id')
	// 							->orWhere('coleta.solic_origem_id', '=', '0');
	// 					})->Where(function ($query3) use ($coleta_id) {
	// 						$query3->where('coleta.coleta_fixa', '=', 'M')
	// 							->where('coleta.id', '=', $coleta_id);
	// 					});
	// 				});
	// 		})

	// 		->orderBy('coleta_nf.serie', 'asc')
	// 		->orderBy('coleta_nf.numero', 'asc')
	// 		->get();

	// 	foreach ($coleta_nf as $regcol_nf) {

	// 		// Monta a string das notas fiscais separando por uma barra.
	// 		if ($notas_fiscais <> '') {
	// 			$notas_fiscais = $notas_fiscais . $separador . $regcol_nf->numero;
	// 		} else {
	// 			$notas_fiscais = $regcol_nf->numero;
	// 		}

	// 		// Acumula o valor das notas fiscais
	// 		$valor_notas = $valor_notas + $regcol_nf->valor;
	// 	}

	// 	// Vamos truncar em 300 caracteres, este é o tamanho máximo do campo que grava as notas.
	// 	$notas_fiscais = mb_strimwidth($notas_fiscais, 0, 300);

	// 	$dados['notas_fiscais'] = $notas_fiscais;
	// 	$dados['valor_notas']   = $valor_notas;

	// 	return $dados;
	// }


	public function GravaLogExportaColetas($empresa, $cont_regs)
	{

		$array_log  = array();
		$evento_log = 'exportacao_coletas';

		$timezone_app = date_default_timezone_get();

		// Adicionar LOG: Header
		// Inserir um elemento no array do log:
		$ind_log = count($array_log);
		$array_log[$ind_log]['tipo']   = '0';
		$array_log[$ind_log]['msg']    = 'Inicio: Exportação de coletas (Emp: ' . $empresa . ')';
		$array_log[$ind_log]['err']    = null;
		$array_log[$ind_log]['status'] = '0';
		$array_log[$ind_log]['created_at'] = Carbon::now($timezone_app)->format('Y-m-d H:i:s');

		// Adicionar LOG: Trailler

		$cod_retorno = 'E101';
		$msg_retorno = rgGetMsgRetornoApi($cod_retorno);
		$msg_retorno = str_replace('$empresa', $empresa, $msg_retorno);
		$msg_retorno = str_replace('$cont_regs', $cont_regs, $msg_retorno);

		// Inserir um elemento no array do log:

		$ind_log = count($array_log);

		$array_log[$ind_log]['tipo']   = '9';
		$array_log[$ind_log]['msg']    = $msg_retorno;
		$array_log[$ind_log]['err']    = null;
		$array_log[$ind_log]['status'] = '0';
		$array_log[$ind_log]['created_at'] = Carbon::now($timezone_app)->format('Y-m-d H:i:s');

		$this->GravarLog($array_log, $evento_log, 'ExportarColetas');

		$retorno['cod_retorno'] = $cod_retorno;
		$retorno['msg_retorno'] = $msg_retorno;

		return $retorno;
	}

	public function Local_MarcarColetasExportadas($empresa, $lista_coletas)
	{

		$continuar = true;
		$retorno   = array();

		//Testamos se o site não foi colocado em manutenção
		$app = new ApiUsoComum();
		$retorno = $app->AplicacaoLiberada();

		if (($retorno['cod_retorno']) != 'Z998') {
			$continuar = false;
		}

		if ($continuar) {

			// Verificar parâmetros
			if (rgIgualZeroNull($empresa)) {
				$continuar = false;
				$retorno['cod_retorno'] = 'Z202';
				$retorno['msg_retorno'] = 'Parâmetro [empresa] obrigatório. Valor esperado: > zero.';
			} else {

				$emp = DB::table('empresa')->where('codigo', '=', $empresa)->first();

				if (empty($emp)) {
					$continuar = false;
					$retorno['cod_retorno'] = 'B207';
					$msg_erro = rgGetMsgRetornoAPI($retorno['cod_retorno']);
					$msg_erro = str_replace('$empresa', $empresa, $msg_erro);
					$retorno['msg_retorno'] = $msg_erro;
				}
			}
		}

		if ($continuar) {

			//Inicializar variáveis
			$timezone_app = date_default_timezone_get();

			$cont_regs  = 0;
			$cont_coletas = 0;
			$cont_entregas = 0;
			$cont_erros = 0;

			$array_log  = array();

			$msg_log    = 'Inicio: Marcando coletas exportadas para ERP (Emp: ' . $empresa . ')';
			$evento_log = 'marca_coletas_exportadas';

			//Verificar se tem coletas na lista
			if ((!isset($lista_coletas)) || ($lista_coletas == null) || (count($lista_coletas) == 0)) {
				$continuar = false;
				$retorno['cod_retorno'] = 'Z202';
				$retorno['msg_retorno'] = 'Parâmetro [lista_coletas] sem conteúdo.';
			}
		}

		if ($continuar) {
			// Adicionar LOG: HEADER
			// Inserir um elemento no array do log:

			$ind_log = 0;

			$array_log[$ind_log]['tipo']       = '0';
			$array_log[$ind_log]['msg']        = $msg_log;
			$array_log[$ind_log]['err']        = null;
			$array_log[$ind_log]['status']     = '0';
			$array_log[$ind_log]['created_at'] = Carbon::now($timezone_app)->format('Y-m-d H:i:s');

			// Para cada elemento de $lista_coletas
			foreach ($lista_coletas as $reglista_coletas) {

				// Inicializar as variáveis
				$cont_regs = $cont_regs + 1;

				if ($reglista_coletas['result'] == 'S') {

					$coleta = Coleta::where('coleta.id', $reglista_coletas['coleta_id'])
						->first();

					if (empty($coleta)) { // Não achou a coleta

						// O indice do primeiro elemento do array_log é zero. Então com o count, obtemos sempre
						// o indice do próximo elemento a ser inserido
						$ind_log = count($array_log);

						// Inserir um elemento no array do log:
						$array_log[$ind_log]['tipo'] = '1';

						$msg_erro = rgGetMsgRetornoAPI('E200');
						$msg_erro = str_replace('$coleta_id', $reglista_coletas['coleta_id'], $msg_erro);

						$array_log[$ind_log]['msg'] = $msg_erro;
						$array_log[$ind_log]['err'] = null;
						$array_log[$ind_log]['status'] = '1';
						$array_log[$ind_log]['created_at'] = Carbon::now($timezone_app)->format('Y-m-d H:i:s');

						$cont_erros = $cont_erros + 1;
					} else {  // Achou a coleta

						if ($reglista_coletas['operacao'] == "coleta") {

							//Marcar coleta
							try {
								//Gravamos os campos que indicam que a operação de COLETA foi exportada

								$coleta['numero'] = $reglista_coletas['cod_coleta'];
								$coleta['coleta_export'] = "S";
								$coleta['dt_coleta_export'] = Carbon::now($timezone_app)->format('Y-m-d H:i:s');
								$coleta['ass_user_id'] = auth()->user()->id;

								$coleta->save();
								$cont_coletas = $cont_coletas + 1;
							} catch (\Exception $e) {
								// O indice do primeiro elemento do array_log é zero. Então com o count, obtemos sempre
								// o indice do próximo elemento a ser inserido
								$ind_log = count($array_log);

								// Inserir um elemento no array do log:
								$array_log[$ind_log]['tipo'] = '1';

								$msg_erro = rgGetMsgRetornoAPI('E216');
								$msg_erro = str_replace('$coleta_id', $reglista_coletas['coleta_id'], $msg_erro);

								$array_log[$ind_log]['msg'] = $msg_erro;
								$array_log[$ind_log]['err'] = $e->getMessage();
								$array_log[$ind_log]['status'] = '1';
								$array_log[$ind_log]['created_at'] = Carbon::now($timezone_app)->format('Y-m-d H:i:s');

								$cont_erros = $cont_erros + 1;
							}
						} else {

							//Marcar entrega
							try {
								// Gravamos os campos que indicam que a operação de ENTREGA foi exportada
								// Aqui NÃO precisamos gravar o campo 'numero'.

								$coleta['entrega_export'] = "S";
								$coleta['dt_entrega_export'] = Carbon::now($timezone_app)->format('Y-m-d H:i:s');
								$coleta['ass_user_id'] = auth()->user()->id;

								$coleta->save();
								$cont_entregas = $cont_entregas + 1;
							} catch (\Exception $e) {
								// O indice do primeiro elemento do array_log é zero. Então com o count, obtemos sempre
								// o indice do próximo elemento a ser inserido
								$ind_log = count($array_log);

								// Inserir um elemento no array do log:
								$array_log[$ind_log]['tipo'] = '1';

								$msg_erro = rgGetMsgRetornoAPI('E217');
								$msg_erro = str_replace('$coleta_id', $reglista_coletas['coleta_id'], $msg_erro);

								$array_log[$ind_log]['msg'] = $msg_erro;
								$array_log[$ind_log]['err'] = $e->getMessage();
								$array_log[$ind_log]['status'] = '1';
								$array_log[$ind_log]['created_at'] = Carbon::now($timezone_app)->format('Y-m-d H:i:s');

								$cont_erros = $cont_erros + 1;
							}
						}
					}
				} else {
					// O indice do primeiro elemento do array_log é zero. Então com o count, obtemos sempre
					// o indice do próximo elemento a ser inserido
					$ind_log = count($array_log);

					// Inserir um elemento no array do log:
					$array_log[$ind_log]['tipo'] = '1';
					$array_log[$ind_log]['msg'] = $reglista_coletas['msg'];
					$array_log[$ind_log]['err'] = $reglista_coletas['err'];
					$array_log[$ind_log]['status'] = '1';
					$array_log[$ind_log]['created_at'] = Carbon::now($timezone_app)->format('Y-m-d H:i:s');

					$cont_erros = $cont_erros + 1;
				}
			}

			// Adicionar Log: Trailler
			$trailler = $this->AdicionarLogTraillerMarcarColeta(
				$array_log,
				$empresa,
				$cont_regs,
				$cont_erros,
				$cont_coletas,
				$cont_entregas
			);

			$this->GravarLog($array_log, $evento_log, 'MarcarColetasExportadas');

			$retorno['cod_retorno'] = $trailler['cod_retorno'];
			$retorno['msg_retorno'] = $trailler['msg_retorno'];
		}

		$resultado['retorno']['cod_retorno'] = $retorno['cod_retorno'];
		$resultado['retorno']['msg_retorno'] = $retorno['msg_retorno'];

		return $resultado;
	}

	public function AdicionarLogTraillerMarcarColeta(
		&$array_log,
		$empresa,
		$cont_regs,
		$cont_erros,
		$cont_novos,
		$cont_exist
	) {
		// O indice do primeiro elemento do array_log é zero. Então com o count, obtemos sempre
		// o indice do próximo elemento a ser inserido
		$retorno = array();
		$cod_retorno = 0;

		$ind_log = count($array_log);

		$array_log[$ind_log]['tipo'] = '9';

		$timezone_app = date_default_timezone_get();
		$array_log[$ind_log]['created_at'] = Carbon::now($timezone_app)->format('Y-m-d H:i:s');

		$cod_retorno = 'E102';
		$msg_retorno = rgGetMsgRetornoAPI($cod_retorno);
		$msg_retorno = str_replace('$empresa', $empresa, $msg_retorno);
		$msg_retorno = str_replace('$cont_regs', $cont_regs, $msg_retorno);
		$msg_retorno = str_replace('$cont_coletas', $cont_novos, $msg_retorno);
		$msg_retorno = str_replace('$cont_entregas', $cont_exist, $msg_retorno);
		$msg_retorno = str_replace('$cont_erros', $cont_erros, $msg_retorno);

		$array_log[$ind_log]['msg'] = $msg_retorno;
		$array_log[$ind_log]['err'] = null;

		if ($cont_erros > 0) {
			$array_log[$ind_log]['status'] = '1';
		} else {
			$array_log[$ind_log]['status'] = '0';
		}

		$retorno['cod_retorno'] = $cod_retorno;
		$retorno['msg_retorno'] = $msg_retorno;

		return $retorno;
	}

	public function GravarLog($array_log, $evento_log, $funcao_log)
	{
		// Variável que vai guardar o ID de processamento do registro HEADER
		$proc_id = 0;

		// Para cada elemento de array_log, inserir registro na tabela LOG_PRO com 
		// os dados de $array_log
		foreach ($array_log as $reglog) {

			try {

				$log_pro = new LogPro;

				$log_pro['evento'] = $evento_log;
				$log_pro['tipo']   = $reglog['tipo'];
				$log_pro['msg']    = $reglog['msg'];
				$log_pro['err']    = $reglog['err'];
				$log_pro['status'] = $reglog['status'];

				// O primeiro elemento de 'array_log' a ser gravado será o registro HEADER (tipo = '0').
				// O campo 'proc_id' do registro HEADER será gravado com ZERO porque é o valor inicial da variável. 
				// Antigamente este valor era gerado através de uma TRIGGER do banco de dados.
				// Com a mudança para o MySql 8.0.36, analisamos a estrutura do log e chegamos na conclusão que não precisamos da trigger.
				$log_pro['proc_id']    = $proc_id;
				$log_pro['created_at'] = $reglog['created_at'];

				$log_pro->save();

				// Guardamos o ID do registro HEADER (tipo = '0') para gravarmos no campo 'proc_id' 
				// de todos os registros deste processamento.
				if ($log_pro['tipo'] == '0') {
					$proc_id = $log_pro->id;
				}
			} catch (\Exception $e) {

				\Log::info('Computador Local (server): ' . gethostname() . ' ' .
					'[' . $funcao_log . ']' . $e->getMessage());
			}
		}
	}

	public function Local_GetColetasNotaFrete($empresa)
	{

		$continuar = true;
		$dados     = array();

		//Testamos se o site não foi colocado em manutenção
		$app = new ApiUsoComum();
		$retorno = $app->AplicacaoLiberada();

		if (($retorno['cod_retorno']) != 'Z998') {
			$continuar = false;
		}

		if ($continuar) {

			//Verificar parâmetros
			if (rgIgualZeroNull($empresa)) {
				$continuar = false;
				$retorno['cod_retorno'] = 'Z202';
				$retorno['msg_retorno'] = 'Parâmetro [empresa] obrigatório. Valor esperado: > zero.';
			} else {

				$emp = DB::table('empresa')->where('codigo', '=', $empresa)->first();

				if (empty($emp)) {
					$continuar = false;
					$retorno['cod_retorno'] = 'B207';
					$msg_erro = rgGetMsgRetornoAPI($retorno['cod_retorno']);
					$msg_erro = str_replace('$empresa', $empresa, $msg_erro);
					$retorno['msg_retorno'] = $msg_erro;
				}
			}
		}

		if ($continuar) {

			$timezone_app = date_default_timezone_get();
			$data_atual_serv = Carbon::now($timezone_app)->format('Y-m-d');

			$coletas =  DB::table('coleta as col')
				->select('col.id AS coleta_id')
				->selectRaw('IFNULL(col.numero, 0) as cod_coleta')

				// Somente da empresa passada com parâmetro
				->where('col.empresa', '=', $empresa)

				// Não pegamos solicitações com DATA DE COLETA futura
				->where('col.dt_prev_coleta', '<=', $data_atual_serv)

				// Consideramos coletas realizadas: 'CR'
				// ou coleta não-realizadas 'CN' com deslocamento (motivo = '01)
				->where(function ($query) {
					$query->where('col.status', '=', 'CR')
						->orWhere(function ($query1) {
							$query1->where('col.status', '=', 'CN')
								->where('col.mot_nao_coleta', '=', '01');
						});
				})

				// Consideramos as solicitações DIÁRIAS, CONTRATOS e MULTI-DESTINOS.
				// Desconsideramos as COMANDAS e SOLICITAÇÕES AUXILIARES MULTI-DESTINOS. 
				//				
				->where(function ($query) {
					$query->whereNull('col.solic_origem_id')
						->orWhere('col.solic_origem_id', '=', '0');
				})

				// Aqui não testamos o campo 'receber_nf_frete'... porque queremos buscar os
				// números das notas de todas as solicitações.

				// Desconsideramos aquelas com NF já emitida ou que NÃO serão cobradas
				->where(function ($query) {
					$query->whereNull('col.nf_frete')
						->orWhere('col.nf_frete', '=', '0');
				})
				->where(function ($query) {
					$query->whereNull('col.sit_nf_frete')
						->orWhere('col.sit_nf_frete', '=', 'N');
				})

				->get();

			if (count($coletas) > 0) {
				$dados = $coletas;
				$retorno['cod_retorno'] = 'Z100';
				$retorno['msg_retorno'] = rgGetMsgRetornoAPI($retorno['cod_retorno']);
			} else {
				$retorno['cod_retorno'] = 'Z101';
				$retorno['msg_retorno'] = rgGetMsgRetornoAPI($retorno['cod_retorno']);
			}
		}

		$resultado['retorno']['cod_retorno'] = $retorno['cod_retorno'];
		$resultado['retorno']['msg_retorno'] = $retorno['msg_retorno'];
		$resultado['dados'] = $dados;

		return $resultado;
	}

	public function Local_AtualizarNotaFreteColetas($empresa, $lista_coletas)
	{

		$continuar = true;
		$retorno   = array();

		//Testamos se o site não foi colocado em manutenção
		$app = new ApiUsoComum();
		$retorno = $app->AplicacaoLiberada();

		if (($retorno['cod_retorno']) != 'Z998') {
			$continuar = false;
		}

		if ($continuar) {

			// Verificar parâmetros
			if (rgIgualZeroNull($empresa)) {
				$continuar = false;
				$retorno['cod_retorno'] = 'Z202';
				$retorno['msg_retorno'] = 'Parâmetro [empresa] obrigatório. Valor esperado: > zero.';
			} else {

				$emp = DB::table('empresa')->where('codigo', '=', $empresa)->first();

				if (empty($emp)) {
					$continuar = false;
					$retorno['cod_retorno'] = 'B207';
					$msg_erro = rgGetMsgRetornoAPI($retorno['cod_retorno']);
					$msg_erro = str_replace('$empresa', $empresa, $msg_erro);
					$retorno['msg_retorno'] = $msg_erro;
				}
			}
		}

		if ($continuar) {

			//Inicializar variáveis
			$timezone_app = date_default_timezone_get();

			$cont_atlz  = 0;
			$cont_regs  = 0;
			$cont_erros = 0;

			$array_log  = array();

			$evento_log = 'atualizar_nota_frete';

			//Verificar se tem coletas na lista
			if ((!isset($lista_coletas)) || ($lista_coletas == null) || (count($lista_coletas) == 0)) {
				$continuar = false;
				$retorno['cod_retorno'] = 'Z202';
				$retorno['msg_retorno'] = 'Parâmetro [lista_coletas] sem conteúdo.';
			}
		}

		if ($continuar) {
			// Adicionar LOG: HEADER - Inserir um elemento no array do log:

			$ind_log = 0;

			$msg_log = 'Inicio: Atualizando campos da nota de frete (Emp: ' . $empresa . ')';
			$array_log[$ind_log]['tipo']       = '0';
			$array_log[$ind_log]['msg']        = $msg_log;
			$array_log[$ind_log]['err']        = null;
			$array_log[$ind_log]['status']     = '0';
			$array_log[$ind_log]['created_at'] = Carbon::now($timezone_app)->format('Y-m-d H:i:s');

			// Para cada elemento de $lista_coletas
			foreach ($lista_coletas as $reglista_coletas) {

				// Inicializar as variáveis
				$cont_regs = $cont_regs + 1;

				if ($reglista_coletas['result'] != 'S') {
					// [LOG-Detail] ==> Adicionar um elemento no array do log
					$ind_log = count($array_log);
					$array_log[$ind_log]['tipo'] = '1';
					$array_log[$ind_log]['msg'] = $reglista_coletas['msg'];
					$array_log[$ind_log]['err'] = '';
					$array_log[$ind_log]['status'] = '1';
					$array_log[$ind_log]['created_at'] = Carbon::now($timezone_app)->format('Y-m-d H:i:s');
				}

				$coleta = Coleta::where('coleta.id', $reglista_coletas['coleta_id'])
					->first();

				if (empty($coleta)) { // Não achou a coleta

					// O indice do primeiro elemento do array_log é zero. Então com o count, obtemos sempre
					// o indice do próximo elemento a ser inserido
					$ind_log = count($array_log);

					// Inserir um elemento no array do log:
					$array_log[$ind_log]['tipo'] = '1';

					$msg_erro = rgGetMsgRetornoAPI('E200');
					$msg_erro = str_replace('$coleta_id', $reglista_coletas['coleta_id'], $msg_erro);

					$array_log[$ind_log]['msg'] = $msg_erro;
					$array_log[$ind_log]['err'] = null;
					$array_log[$ind_log]['status'] = '1';
					$array_log[$ind_log]['created_at'] = Carbon::now($timezone_app)->format('Y-m-d H:i:s');

					$cont_erros = $cont_erros + 1;
				} else {  // Achou a coleta

					// Atualizamos os campos da nota fiscal de frete
					// Aproveitamos a leitura do registro da coleta que foi feito acima
					try {

						$coleta['nf_frete'] = $reglista_coletas['nf_frete'];
						$coleta['sit_nf_frete'] = 'E';    // 'E' - Emitida

						$coleta['ass_user_id'] = auth()->user()->id;

						$coleta->save();

						$cont_atlz = $cont_atlz + 1;
					} catch (\Exception $e) {
						// O indice do primeiro elemento do array_log é zero. Então com o count, obtemos sempre
						// o indice do próximo elemento a ser inserido
						$ind_log = count($array_log);

						// Inserir um elemento no array do log:
						$array_log[$ind_log]['tipo'] = '1';

						$msg_erro = rgGetMsgRetornoAPI('E256');
						$msg_erro = str_replace('$coleta_id', $reglista_coletas['coleta_id'], $msg_erro);

						$array_log[$ind_log]['msg'] = $msg_erro;
						$array_log[$ind_log]['err'] = $e->getMessage();
						$array_log[$ind_log]['status'] = '1';
						$array_log[$ind_log]['created_at'] = Carbon::now($timezone_app)->format('Y-m-d H:i:s');

						$cont_erros = $cont_erros + 1;
					}
				}
			}

			// Adicionar Log: Trailler

			$ind_log = count($array_log);
			$array_log[$ind_log]['tipo'] = '9';

			$cod_retorno = 'E103';
			$msg_retorno = rgGetMsgRetornoAPI($cod_retorno);
			$msg_retorno = str_replace('$empresa', $empresa, $msg_retorno);
			$msg_retorno = str_replace('$cont_regs', $cont_regs, $msg_retorno);
			$msg_retorno = str_replace('$cont_atlz', $cont_atlz, $msg_retorno);
			$msg_retorno = str_replace('$cont_erros', $cont_erros, $msg_retorno);

			$array_log[$ind_log]['created_at'] = Carbon::now($timezone_app)->format('Y-m-d H:i:s');

			$array_log[$ind_log]['msg'] = $msg_retorno;
			$array_log[$ind_log]['err'] = null;

			if ($cont_erros > 0) {
				$array_log[$ind_log]['status'] = '1';
			} else {
				$array_log[$ind_log]['status'] = '0';
			}

			$retorno['cod_retorno'] = $cod_retorno;
			$retorno['msg_retorno'] = $msg_retorno;

			$this->GravarLog($array_log, $evento_log, 'MarcarColetasExportadas');
		}

		$resultado['retorno']['cod_retorno'] = $retorno['cod_retorno'];
		$resultado['retorno']['msg_retorno'] = $retorno['msg_retorno'];

		return $resultado;
	}

	public function Local_GetInfoColeta($empresa, $coleta)
	{
		$continuar = true;
		$dados     = array();

		//Testamos se o site não foi colocado em manutenção
		$app = new ApiUsoComum();
		$retorno = $app->AplicacaoLiberada();

		if (($retorno['cod_retorno']) != 'Z998') {
			$continuar = false;
		}

		if ($continuar) {

			$dados['url_img_carga'] = null;

			$info_coleta = DB::table('coleta')
				->select('img_carga')
				->where('empresa', '=', $empresa)
				->where('numero', '=', $coleta)
				->first();

			if (empty($info_coleta) == false) {

				if (rgIgualTrimNull($info_coleta->img_carga) == false) {
					$dados['url_img_carga'] = rgRetornarUrlImagens($info_coleta->img_carga);
				}

				$retorno['cod_retorno'] = 'Z100';
				$retorno['msg_retorno'] = rgGetMsgRetornoAPI($retorno['cod_retorno']);
			} else {
				$retorno['cod_retorno'] = 'E201';
				$msg_erro = rgGetMsgRetornoAPI($retorno['cod_retorno']);
				$msg_erro = str_replace('$empresa', $empresa, $msg_erro);
				$msg_erro = str_replace('$coleta', $coleta, $msg_erro);
				$retorno['msg_retorno'] = $msg_erro;
			}
		}

		$resultado['retorno']['cod_retorno'] = $retorno['cod_retorno'];
		$resultado['retorno']['msg_retorno'] = $retorno['msg_retorno'];
		$resultado['dados'] = $dados;

		return $resultado;
	}
}
