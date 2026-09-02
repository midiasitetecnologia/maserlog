<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Code\Validator\Cnpj;
use Code\Validator\Cpf;
use Carbon\Carbon;
use DB;

class ApiUsoComum extends Model
{

    public function AplicacaoLiberada()
    {

        $retorno = array();

        $retorno['cod_retorno'] = 'Z998';
        $retorno['msg_retorno'] = 'Tudo Ok';

        //  $retorno['cod_retorno'] = 'Z997';
        //  $retorno['msg_retorno'] = 'Site em manutencao';       

        return $retorno;
    }


    public function ValidarCpf_Cnpj($tipodoc, $valor)
    {

        if ($tipodoc == 'cpf') {
            $documentValidator = new Cpf();
        } else {
            $documentValidator = new Cnpj();
        }

        return $documentValidator->isValid($valor);
    }

    //Esta função Não está na Helpers porque precisamos fazer gravações.
    public function RetornarVeiculosRastreaveis()
    {
        //Inicializar variáveis
        $continuar = true;
        $dados = null;
        $array_log = array();
        $timezone_app = date_default_timezone_get();
        $evento_log = 'erros_sis_track';
        $funcao_log = 'RetornarVeiculosRastreaveis';
        $msg_log = 'Erro na integração com sistema de rastreamento de veículos';

        // Inserir um elemento no array do log:
        $ind_log = 0;

        $array_log[$ind_log]['tipo']       = '0';
        $array_log[$ind_log]['msg']        = 'Início do processo';
        $array_log[$ind_log]['err']        = null;
        $array_log[$ind_log]['status']     = '0';
        $array_log[$ind_log]['created_at'] = Carbon::now($timezone_app)->format('Y-m-d H:i:s');

        $sys_cfg = DB::table('sys_cfg')->orderBy('id', 'asc')->first();

        if (
            empty($sys_cfg) ||
            rgIgualTrimNull($sys_cfg->url_sis_track) ||
            rgIgualTrimNull($sys_cfg->user_sis_track) ||
            rgIgualTrimNull($sys_cfg->pwd_sis_track)
        ) {
            $continuar = false;

            // O indice do primeiro elemento do array_log é zero. Então com o count, obtemos sempre
            // o indice do próximo elemento a ser inserido
            $ind_log = count($array_log);

            // Inserir um elemento no array do log:
            $array_log[$ind_log]['tipo'] = '1';
            $array_log[$ind_log]['msg'] = 'Falta configurações do sistema de rastreamento: URL | usuário | senha';
            $array_log[$ind_log]['err'] = null;
            $array_log[$ind_log]['status'] = '1';
            $array_log[$ind_log]['created_at'] = Carbon::now($timezone_app)->format('Y-m-d H:i:s');

            $this->AdicionarLogTrailler($array_log, $msg_log, $evento_log, $funcao_log, true);
        } else {
            // O indice do primeiro elemento do array_log é zero. Então com o count, obtemos sempre
            // o indice do próximo elemento a ser inserido
            $ind_log = count($array_log);

            // Inserir um elemento no array do log:
            $array_log[$ind_log]['tipo'] = '1';
            $array_log[$ind_log]['msg'] = 'Configurações OK';
            $array_log[$ind_log]['err'] = null;
            $array_log[$ind_log]['status'] = '0';
            $array_log[$ind_log]['created_at'] = Carbon::now($timezone_app)->format('Y-m-d H:i:s');
        }

        if ($continuar) {

            //Autenticação sistema rastreamento
            $http_url = $sys_cfg->url_sis_track . '/login/entrar/';

            try {

                // Campos para autenticação
                $data = [
                    'usuario' => $sys_cfg->user_sis_track,
                    'senha' => $sys_cfg->pwd_sis_track
                ];

                // Inicializa uma sessão cURL
                $ch = curl_init($http_url);

                // Configura as opções da requisição
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
                curl_setopt($ch, CURLOPT_POST, TRUE);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    'Content-Type: application/json'
                ]);

                // Executa a requisição
                $response = curl_exec($ch);

                // Verifica se houve algum erro
                $err = curl_error($ch);

                // Fecha a sessão cURL
                curl_close($ch);

                $response_a = json_decode($response, true);

                // Código == 0, significa que os dados foram obtidos com sucesso.
                if (!empty($response_a) && ($response_a['codigo'] == 0)) {
                    $token = $response_a['token'];
                } else {
                    $continuar = false;
                    // O indice do primeiro elemento do array_log é zero. Então com o count, obtemos sempre
                    // o indice do próximo elemento a ser inserido
                    $ind_log = count($array_log);

                    $erro = $err;

                    if (!empty($response_a) && !empty($response_a['mensagem'])) {
                        $erro = $response_a['mensagem'];
                    }

                    // Inserir um elemento no array do log:
                    $array_log[$ind_log]['tipo'] = '1';
                    $array_log[$ind_log]['msg'] = 'Falha na autenticação';
                    $array_log[$ind_log]['err'] = 'URL: ' . $http_url . ' | HTTP Error: ' . $erro;
                    $array_log[$ind_log]['status'] = '1';
                    $array_log[$ind_log]['created_at'] = Carbon::now($timezone_app)->format('Y-m-d H:i:s');

                    $this->AdicionarLogTrailler($array_log, $msg_log, $evento_log, $funcao_log, true);
                }
            } catch (\Exception $e) {
                $continuar = false;
                // O indice do primeiro elemento do array_log é zero. Então com o count, obtemos sempre
                // o indice do próximo elemento a ser inserido
                $ind_log = count($array_log);

                // Inserir um elemento no array do log:
                $array_log[$ind_log]['tipo'] = '1';
                $array_log[$ind_log]['msg'] = 'Erro na execução da API de autenticação';
                $array_log[$ind_log]['err'] = 'URL: ' . $http_url . ' | Erro: ' . $e->getMessage();
                $array_log[$ind_log]['status'] = '1';
                $array_log[$ind_log]['created_at'] = Carbon::now($timezone_app)->format('Y-m-d H:i:s');

                $this->AdicionarLogTrailler($array_log, $msg_log, $evento_log, $funcao_log, true);
            }
        }

        if ($continuar) {

            //Buscar dispositivos rastreáveis
            $http_url = $sys_cfg->url_sis_track . '/ultimas/listar/';

            try {

                // Campos para autenticação
                $data = [
                    'token' => $token
                ];

                // Inicializa uma sessão cURL
                $ch = curl_init($http_url);

                // Configura as opções da requisição
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
                curl_setopt($ch, CURLOPT_POST, TRUE);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    'Content-Type: application/json'
                ]);

                // Executa a requisição
                $response = curl_exec($ch);

                // Verifica se houve algum erro
                $err = curl_error($ch);

                // Fecha a sessão cURL
                curl_close($ch);

                $response_a = json_decode($response, true);

                // Código == 0, significa que os dados foram obtidos com sucesso.
                if (!empty($response_a) && ($response_a['codigo'] == 0)) {
                    // A coleção de dados "['data'][0]" representa as últimas coordenadas de cada veículo.
                    $dados['data'] = $response_a['data'][0];
                } else {
                    $continuar = false;
                    // O indice do primeiro elemento do array_log é zero. Então com o count, obtemos sempre
                    // o indice do próximo elemento a ser inserido
                    $ind_log = count($array_log);

                    $erro = $err;

                    if (!empty($response_a) && !empty($response_a['mensagem'])) {
                        $erro = $response_a['mensagem'];
                    }

                    // Inserir um elemento no array do log:
                    $array_log[$ind_log]['tipo'] = '1';
                    $array_log[$ind_log]['msg'] = 'Falha ao obter os dispositivos rastreáveis';
                    $array_log[$ind_log]['err'] = 'URL: ' . $http_url . ' | HTTP Error: ' . $erro;
                    $array_log[$ind_log]['status'] = '1';
                    $array_log[$ind_log]['created_at'] = Carbon::now($timezone_app)->format('Y-m-d H:i:s');

                    $this->AdicionarLogTrailler($array_log, $msg_log, $evento_log, $funcao_log, true);
                }
            } catch (\Exception $e) {
                $continuar = false;
                // O indice do primeiro elemento do array_log é zero. Então com o count, obtemos sempre
                // o indice do próximo elemento a ser inserido
                $ind_log = count($array_log);

                // Inserir um elemento no array do log:
                $array_log[$ind_log]['tipo'] = '1';
                $array_log[$ind_log]['msg'] = 'Erro na execução da API que lista dispositivos';
                $array_log[$ind_log]['err'] = 'URL: ' . $http_url . ' | Erro: ' . $e->getMessage();
                $array_log[$ind_log]['status'] = '1';
                $array_log[$ind_log]['created_at'] = Carbon::now($timezone_app)->format('Y-m-d H:i:s');

                $this->AdicionarLogTrailler($array_log, $msg_log, $evento_log, $funcao_log, true);
            }
        }

        return $dados;
    }

    public function AdicionarLogTrailler(&$array_log, $msg_log, $evento_log, $funcao_log, $gravar_log = true)
    {
        $timezone_app = date_default_timezone_get();
        // O indice do primeiro elemento do array_log é zero. Então com o count, obtemos sempre
        // o indice do próximo elemento a ser inserido
        $ind_log = count($array_log);

        // Inserir um elemento no array do log: trailler
        $array_log[$ind_log]['tipo'] = '9';
        $array_log[$ind_log]['msg'] = $msg_log;
        $array_log[$ind_log]['err'] = null;
        $array_log[$ind_log]['status'] = '1';
        $array_log[$ind_log]['created_at'] = Carbon::now($timezone_app)->format('Y-m-d H:i:s');

        if ($gravar_log) {
            $this->GravarLog($array_log, $evento_log, $funcao_log);
        }
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

    public function RetornarAreaLocalVeiculo($placa)
    {

        //Inicializar variáveis
        $continuar = true;
        $dados = null;
        $geo_lat = 0;
        $geo_lng = 0;
        $dt_geopos = '';
        $local_veiculo = '';

        $sys_cfg = DB::table('sys_cfg')->orderBy('id', 'asc')->first();

        if (empty($sys_cfg)) {
            $continuar = false;
        }

        if ($continuar) {

            $veiculo = DB::table('veiculo')
                ->select('geo_lat', 'geo_lng', 'dt_geopos', 'ignicao')
                ->where('placa', '=', $placa)
                ->first();

            if (empty($veiculo)) {
                $continuar = false;
            }
        }

        if ($continuar) {
            if (rgDifZeroNull($veiculo->geo_lat) && rgDifZeroNull($veiculo->geo_lat)) {
                $geo_lat = $veiculo->geo_lat;
                $geo_lng = $veiculo->geo_lng;
                $dt_geopos = $veiculo->dt_geopos;
            } else {
                $continuar = false;
            }
        }

        if ($continuar) {

            $ponto = array('lat' => $geo_lat, 'lng' => $geo_lng);

            if (!rgIgualTrimNull($sys_cfg->office_area) && rgIgualTrimNull($local_veiculo)) {
                if ($this->VerificarPolygon($ponto, $sys_cfg->office_area)) {
                    $local_veiculo = 'Escritório';
                }
            }

            if (!rgIgualTrimNull($sys_cfg->garage_area) && rgIgualTrimNull($local_veiculo)) {
                if ($this->VerificarPolygon($ponto, $sys_cfg->garage_area)) {
                    $local_veiculo = 'Garagem';
                }
            }

            if (!rgIgualTrimNull($sys_cfg->pavilion_area) && rgIgualTrimNull($local_veiculo)) {
                if ($this->VerificarPolygon($ponto, $sys_cfg->pavilion_area)) {
                    $local_veiculo = 'Pavilhão';
                }
            }

            if (rgIgualTrimNull($local_veiculo)) {
                if ($veiculo->ignicao == 'S') {
                    $local_veiculo = 'Em trânsito';
                } else {
                    $local_veiculo = 'Estacionado';
                }
            }
        }

        if (rgIgualTrimNull($local_veiculo)) {
            $local_veiculo = 'Não definida';
        }

        $dados['local_veiculo'] = $local_veiculo;
        $dados['geo_lat'] = $geo_lat;
        $dados['geo_lng'] = $geo_lng;
        $dados['dt_geopos'] = $dt_geopos;

        return $dados;
    }

    public function VerificarPolygon($ponto, $polygon)
    {
        $retorno = false;

        try {

            $arraycoords = json_decode($polygon, true);

            foreach ($arraycoords as $polig) {

                $localizouponto = $this->ContemLocalizacao($ponto, $polig['polygon']);

                //Se localizou o ponto, interrompe a pesquisa. Está no poligono.
                if ($localizouponto) {
                    $retorno = true;
                    break;
                }
            }
        } catch (\Exception $e) {
            //Não faz nada           
        }

        return $retorno;
    }

    public function ContemLocalizacao($ponto, $coords)
    {
        $response = \GeometryLibrary\PolyUtil::containsLocation($ponto, $coords);
        return $response;
    }


    public function Local_GetTeste($request)
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

            //Esta Api serve para realizar teste de qualquer finalidade.
            //Deixamos preparado uma requisição POST com todos os campos do request.
            //Basta implementar neste bloco.

        }

        $resultado['retorno']['cod_retorno'] = $retorno['cod_retorno'];
        $resultado['retorno']['msg_retorno'] = $retorno['msg_retorno'];
        $resultado['dados'] = $dados;

        return $resultado;
    }
}
