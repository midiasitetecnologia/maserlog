<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\ApiIntegracao;
use Carbon\Carbon;
use App\Cliente;
use DB;

class ApiIntegracaoController extends Controller
{
    public function ImportarClientes(Request $request)
    {

        try {

            $funcao         = $request->get('funcao');
            $empresa        = $request->get('empresa');
            $lista_clientes = $request->get('lista_clientes');

            //Setamos o tempo máximo de execução para 15 minutos(900 segundos).
            //O tempo padrão de execução de um processo no PHP está definido no arquivo 02phpini.config (.ebextensions).
            ini_set('max_execution_time', 900);

            $api = new ApiIntegracao();
            $resultado = $api->Local_ImportarClientes($funcao, $empresa, $lista_clientes);
        } catch (\Exception $e) {
            $resultado['retorno']['cod_retorno'] = 'Z400';
            $resultado['retorno']['msg_retorno'] = rgGetMsgRetornoAPI('Z400') . ' ' . $e->getMessage();
        }

        header("Content-Type: application/json");
        echo json_encode($resultado);
    }

    public function GetMaxDataAltClientes(Request $request)
    {
        try {
            $empresa = $request->get('empresa');

            $api = new ApiIntegracao();
            $resultado = $api->Local_GetMaxDataAltClientes($empresa);
        } catch (\Exception $e) {
            $resultado['retorno']['cod_retorno'] = 'Z400';
            $resultado['retorno']['msg_retorno'] = rgGetMsgRetornoAPI('Z400') . ' ' . $e->getMessage();
        }

        header("Content-Type: application/json");
        echo json_encode($resultado);
    }

    public function ImportarMotoristas(Request $request)
    {

        try {

            $funcao           = $request->get('funcao');
            $empresa          = $request->get('empresa');
            $lista_motoristas = $request->get('lista_motoristas');

            //Setamos o tempo máximo de execução para 15 minutos(900 segundos).
            //O tempo padrão de execução de um processo no PHP está definido no arquivo 02phpini.config (.ebextensions).
            ini_set('max_execution_time', 900);

            $api = new ApiIntegracao();
            $resultado = $api->Local_ImportarMotoristas($funcao, $empresa, $lista_motoristas);
        } catch (\Exception $e) {
            $resultado['retorno']['cod_retorno'] = 'Z400';
            $resultado['retorno']['msg_retorno'] = rgGetMsgRetornoAPI('Z400') . ' ' . $e->getMessage();
        }

        header("Content-Type: application/json");
        echo json_encode($resultado);
    }


    public function ImportarColetas(Request $request)
    {

        try {

            $empresa       = $request->get('empresa');
            $lista_coletas = $request->get('lista_coletas');

            //Setamos o tempo máximo de execução para 15 minutos(900 segundos).
            //O tempo padrão de execução de um processo no PHP está definido no arquivo 02phpini.config (.ebextensions).
            ini_set('max_execution_time', 900);

            $api = new ApiIntegracao();
            $resultado = $api->Local_ImportarColetas($empresa, $lista_coletas);
        } catch (\Exception $e) {
            $resultado['retorno']['cod_retorno'] = 'Z400';
            $resultado['retorno']['msg_retorno'] = rgGetMsgRetornoAPI('Z400') . ' ' . $e->getMessage();
        }

        header("Content-Type: application/json");
        echo json_encode($resultado);
    }

    public function ExportarColetas(Request $request)
    {

        try {

            $empresa = $request->get('empresa');

            //Setamos o tempo máximo de execução para 15 minutos(900 segundos).
            //O tempo padrão de execução de um processo no PHP está definido no arquivo 02phpini.config (.ebextensions).
            ini_set('max_execution_time', 900);

            $api = new ApiIntegracao();
            $resultado = $api->Local_ExportarColetas($empresa);
        } catch (\Exception $e) {
            $resultado['retorno']['cod_retorno'] = 'Z400';
            $resultado['retorno']['msg_retorno'] = rgGetMsgRetornoAPI('Z400') . ' ' . $e->getMessage();
        }

        header("Content-Type: application/json");
        echo json_encode($resultado);
    }

    public function MarcarColetasExportadas(Request $request)
    {

        try {

            $empresa       = $request->get('empresa');
            $lista_coletas = $request->get('lista_coletas');

            //Setamos o tempo máximo de execução para 15 minutos(900 segundos).
            //O tempo padrão de execução de um processo no PHP está definido no arquivo 02phpini.config (.ebextensions).
            ini_set('max_execution_time', 900);

            $api = new ApiIntegracao();
            $resultado = $api->Local_MarcarColetasExportadas($empresa, $lista_coletas);
        } catch (\Exception $e) {
            $resultado['retorno']['cod_retorno'] = 'Z400';
            $resultado['retorno']['msg_retorno'] = rgGetMsgRetornoAPI('Z400') . ' ' . $e->getMessage();
        }

        header("Content-Type: application/json");
        echo json_encode($resultado);
    }


    public function CarregarGeoCoordenadasCliente(Request $request)
    {

        $result = false;

        try {

            //Setamos o tempo máximo de execução para 15 minutos(900 segundos).
            //O tempo padrão de execução de um processo no PHP está definido no arquivo 02phpini.config (.ebextensions).
            ini_set('max_execution_time', 900);

            $cliente = Cliente::where(function ($query) {
                $query->where(function ($query1) {
                    $query1->where('geo_lat', '=', 0)
                        ->orwhereNull('geo_lat');
                })->orWhere(function ($query2) {
                    $query2->where('geo_lng', '=', 0)
                        ->orwhereNull('geo_lng');
                });
            })
                ->get();

            if (empty($cliente) == false) {

                $cont = 0;
                $result = true;

                foreach ($cliente as $cli) {

                    if ($cont >= 100) {
                        sleep(1);
                        $cont = 0;
                    } else {

                        try {

                            if (EnderecoEstaCorreto($cli->endereco, $cli->bairro, $cli->cidade, $cli->cep, $cli->uf)) {

                                $coordenadas = RetornarGeoPosition($cli->endereco, $cli->bairro, $cli->cidade, $cli->cep, $cli->uf);

                                // Espera 1/4 de segundo (25 milissegundos) antes de antes atualizar o registro do cliente    
                                usleep(25000);

                                $timezone_app = date_default_timezone_get();

                                DB::table('cliente')
                                    ->where('id', '=', $cli->id)
                                    ->update([
                                        'geo_lat' => $coordenadas['geo_lat'],
                                        'geo_lng' => $coordenadas['geo_lng'],
                                        'updated_at' => Carbon::now($timezone_app)->format('Y-m-d H:i:s')
                                    ]);
                            }
                        } catch (\Exception $e) {
                            // Se der erro não fazemos nada. Só tratamos a excecao
                        }
                    }

                    $cont++;
                }
            }
        } catch (\Exception $e) {
            $result = false;
        }

        $resultado['status'] = $result;

        header("Content-Type: application/json");
        echo json_encode($resultado);
    }


    public function GetColetasNotaFrete(Request $request)
    {

        try {

            $empresa = $request->get('empresa');

            //Setamos o tempo máximo de execução para 15 minutos(900 segundos).
            //O tempo padrão de execução de um processo no PHP está definido no arquivo 02phpini.config (.ebextensions).
            ini_set('max_execution_time', 900);

            $api = new ApiIntegracao();
            $resultado = $api->Local_GetColetasNotaFrete($empresa);
        } catch (\Exception $e) {
            $resultado['retorno']['cod_retorno'] = 'Z400';
            $resultado['retorno']['msg_retorno'] = rgGetMsgRetornoAPI('Z400') . ' ' . $e->getMessage();
        }

        header("Content-Type: application/json");
        echo json_encode($resultado);
    }


    public function AtualizarNotaFreteColetas(Request $request)
    {

        try {

            $empresa       = $request->get('empresa');
            $lista_coletas = $request->get('lista_coletas');

            //Setamos o tempo máximo de execução para 15 minutos(900 segundos).
            //O tempo padrão de execução de um processo no PHP está definido no arquivo 02phpini.config (.ebextensions).
            ini_set('max_execution_time', 900);

            $api = new ApiIntegracao();
            $resultado = $api->Local_AtualizarNotaFreteColetas($empresa, $lista_coletas);
        } catch (\Exception $e) {
            $resultado['retorno']['cod_retorno'] = 'Z400';
            $resultado['retorno']['msg_retorno'] = rgGetMsgRetornoAPI('Z400') . ' ' . $e->getMessage();
        }

        header("Content-Type: application/json");
        echo json_encode($resultado);
    }


    public function GetInfoColeta(Request $request)
    {

        try {

            $empresa = $request->get('empresa');
            $coleta = $request->get('coleta');

            $api = new ApiIntegracao();
            $resultado = $api->Local_GetInfoColeta($empresa, $coleta);
        } catch (\Exception $e) {
            $resultado['retorno']['cod_retorno'] = 'Z400';
            $resultado['retorno']['msg_retorno'] = rgGetMsgRetornoAPI('Z400') . ' ' . $e->getMessage();
        }

        header("Content-Type: application/json");
        echo json_encode($resultado);
    }
}
