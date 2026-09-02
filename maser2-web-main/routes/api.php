<?php

use Illuminate\Support\Facades\Route;

/*
|----------------------------------------------------------------------------------------------------------------
|    As rotas abaixo não precisa passar o token na requisição.
|----------------------------------------------------------------------------------------------------------------
*/

Route::post('AutenticarUsuario', 'ApiUsuarioController@AutenticarUsuario');
Route::post('RegistrarDispositivoUsuario', 'ApiUsuarioController@RegistrarDispositivoUsuario');
Route::post('AutenticarMotorista', 'ApiMotoristaController@AutenticarMotorista');

//A API "AtivarContaUsuario" é uma API de método GET
Route::get('AtivarContaUsuario/{email}/{token}', 'ApiUsuarioController@AtivarContaUsuario');

//A API "GerarColetasFixas" é uma API de método GET
Route::get('GerarColetasFixas', 'ApiRoboController@GerarColetasFixas');

//A API "AtualizarGeoPosVeiculos" é uma API de método GET
Route::get('AtualizarGeoPosVeiculos', 'ApiRoboController@AtualizarGeoPosVeiculos');

//A API "DesconectarMotoristas" é uma API de método GET responsavel por desconectar usuários logados
Route::get('DesconectarMotoristas', 'ApiRoboController@DesconectarMotoristas');

/*
|----------------------------------------------------------------------------------------------------------------
|    As rotas que estão com o "middleware(['auth:api'])" obrigatoriamente precisa passar o token na requesição.
|----------------------------------------------------------------------------------------------------------------
*/
Route::middleware(['auth:api'])->group(function () {

    //API's - Acionadas pela WEB
    Route::get('getDataAtual', 'ApiUsoComumController@getDataAtual');    

    //Rotas do Painel de Controle
    Route::get('controle/countColetasPendentes', 'ControleController@countColetasPendentes');
    Route::get('controle/countColetasAndamento', 'ControleController@countColetasAndamento');
    Route::get('controle/countEntregasPendentes', 'ControleController@countEntregasPendentes');
    Route::get('controle/countEntregasAndamento', 'ControleController@countEntregasAndamento');
    Route::get('controle/countSolicitacoesFinalizadas', 'ControleController@countSolicitacoesFinalizadas');
    Route::get('controle/getColetasPendentes', 'ControleController@getColetasPendentes');
    Route::get('controle/getEntregasPendentes', 'ControleController@getEntregasPendentes');
    Route::get('controle/getColetasAndamento', 'ControleController@getColetasAndamento');
    Route::get('controle/getEntregasAndamento', 'ControleController@getEntregasAndamento');
    Route::get('controle/getSolicitacoesFinalizadas', 'ControleController@getSolicitacoesFinalizadas');
    Route::get('controle/RetornarVeiculosColeta', 'ControleController@RetornarVeiculosColeta');
    Route::get('controle/RetornarVeiculosFrota', 'ControleController@RetornarVeiculosFrota');
    Route::get('controle/RetornarVeiculosBaldeacao', 'ControleController@RetornarVeiculosBaldeacao');
    Route::post('controle/DefinirVeiculoColeta', 'ControleController@DefinirVeiculoColeta');
    Route::post('controle/DefinirVeiculoEntrega', 'ControleController@DefinirVeiculoEntrega');
    Route::post('controle/EnviarInstrucaoColeta', 'ControleController@EnviarInstrucaoColeta');
    Route::post('controle/RetornarDadosVeiculoCarga', 'ControleController@RetornarDadosVeiculoCarga');
    Route::post('controle/RetornarEntregasPendentesCarga', 'ControleController@RetornarEntregasPendentesCarga');
    Route::post('controle/RetornarColetasVeiculoCarga', 'ControleController@RetornarColetasVeiculoCarga');
    Route::post('controle/DesvincularVeiculoSolicitacao', 'ControleController@DesvincularVeiculoSolicitacao');
    Route::post('controle/RetornarInstrucoesColeta', 'ControleController@RetornarInstrucoesColeta');
    Route::post('controle/RetornarVeiculosBaldeacaoSimples', 'ControleController@RetornarVeiculosBaldeacaoSimples');
    Route::post('controle/GravarSeqAtendRotaCarga', 'ControleController@GravarSeqAtendRotaCarga');
    Route::post('controle/ExecutarBaldeacaoPatio', 'ControleController@ExecutarBaldeacaoPatio');
    Route::post('controle/SetarEntregaConsolidada', 'ControleController@SetarEntregaConsolidada');
    Route::post('controle/RetornarColetasResumoDia', 'ControleController@RetornarColetasResumoDia');
    Route::post('controle/DefinirMotoristaPrevisto', 'ControleController@DefinirMotoristaPrevisto');

    Route::resource('motorista', 'MotoristaController');
    Route::post('getUsersMotorista', 'UserController@getUsersMotorista');
    Route::resource('veiculo', 'VeiculoController');
    Route::get('lerVeiculo', 'VeiculoController@lerVeiculo');
    Route::post('DesvincularMotoristaVeiculo', 'VeiculoController@DesvincularMotoristaVeiculo');
    Route::resource('tipo-veiculo', 'TipoVeiculoController');
    Route::get('lerTipoVeiculo', 'TipoVeiculoController@lerTipoVeiculo');
    Route::resource('cliente', 'ClienteController');
    Route::get('getUsersCliente', 'ClienteController@getUsersCliente');
    Route::post('getDadosCliente', 'ClienteController@getDadosCliente');
    Route::get('lerClienteFromUser', 'ClienteController@lerClienteFromUser');
    Route::resource('coleta-nf', 'ColetaNfController');
    Route::resource('coleta-fixa', 'ColetaFixaController');
    Route::resource('coleta-fixa-bloq', 'ColetaFixaBloqController');
    Route::resource('coleta', 'ColetaController');
    Route::get('coletaWeb', 'ColetaController@indexColetaWeb');
    Route::get('getNotasFiscais', 'ColetaController@getNotasFiscais');
    Route::get('getColetaPos', 'ColetaController@getColetaPos');
    Route::get('getColetaLog', 'ColetaController@getColetaLog');
    Route::get('getSolicitacoes', 'ColetaController@getSolicitacoes');
    Route::resource('users', 'UserController');
    Route::get('gerarIdLogin', 'UserController@gerarIdLogin');
    Route::resource('sys-cfg', 'SysCfgController');
    Route::resource('distance-matrix', 'DistanceMatrixController');
    Route::resource('empresa', 'EmpresaController');
    Route::resource('log-pro', 'LogProController');
    Route::resource('wisdom', 'WisdomController');
    Route::get('log-pro-detalhe', 'LogProController@detalhe');

    //Dashboard
    Route::get('RetornarClientesColetasCadIncomp', 'DashboardController@RetornarClientesColetasCadIncomp');
    Route::get('RetornarMsgWisdomUser', 'DashboardController@RetornarMsgWisdomUser');
    Route::get('RetornarResumoFrotaHome', 'DashboardController@RetornarResumoFrotaHome');
    Route::get('RetornarResumoColetasHome', 'DashboardController@RetornarResumoColetasHome');
    Route::get('RetornarResumoKmTempoHome', 'DashboardController@RetornarResumoKmTempoHome');
    Route::get('RetornarColetasEmissaoNotas', 'DashboardController@RetornarColetasEmissaoNotas');
    Route::get('RetornarMotoristasDisponiveis', 'DashboardController@RetornarMotoristasDisponiveis');
    Route::get('RetornarColetasMultiDestinosRealizadas', 'DashboardController@RetornarColetasMultiDestinosRealizadas');
    Route::get('RetornarEntregasNaoRealizadasReentrega', 'DashboardController@RetornarEntregasNaoRealizadasReentrega');
    Route::get('RetornarTarefasHome', 'DashboardController@RetornarTarefasHome');

    //Coleta
    Route::get('RetornarTotaisKmTempoCliente', 'ColetaController@RetornarTotaisKmTempoCliente');
    Route::get('RetornarTotaisKmTempoVeiculo', 'ColetaController@RetornarTotaisKmTempoVeiculo');
    Route::get('RetornarTotaisKmTempoTipoVeiculo', 'ColetaController@RetornarTotaisKmTempoTipoVeiculo');
    Route::get('RetornarTotaisKmTempoMotorista', 'ColetaController@RetornarTotaisKmTempoMotorista');
    Route::post('CancelarColetaSemDesloc', 'ColetaController@CancelarColetaSemDesloc');

    //API - UsoComum
    Route::post('GetTeste', 'ApiUsoComumController@GetTeste');
    Route::get('test-driving-distance', 'ApiUsoComumController@TestDrivingDistance');

    //API - Geral
    Route::post('GetVeiculosDisponiveis', 'ApiGeralController@GetVeiculosDisponiveis');
    Route::post('AtualizarOcupacaoVeiculo', 'ApiGeralController@AtualizarOcupacaoVeiculo');

    //API - Motorista
    Route::post('GetDadosMotorista', 'ApiMotoristaController@GetDadosMotorista');
    Route::post('AlterarVeiculoMotorista', 'ApiMotoristaController@AlterarVeiculoMotorista');
    Route::post('SetarLogoutMotorista', 'ApiMotoristaController@SetarLogoutMotorista');
    Route::post('GetNotificacoesMotorista', 'ApiMotoristaController@GetNotificacoesMotorista');
    Route::post('SetarNotifLidaMotorista', 'ApiMotoristaController@SetarNotifLidaMotorista');
    Route::post('GetTransferCodeVeiculo', 'ApiMotoristaController@GetTransferCodeVeiculo');

    //API - Integração
    Route::post('ImportarClientes', 'ApiIntegracaoController@ImportarClientes');
    Route::post('GetMaxDataAltClientes', 'ApiIntegracaoController@GetMaxDataAltClientes');
    Route::post('ImportarMotoristas', 'ApiIntegracaoController@ImportarMotoristas');
    Route::post('ImportarColetas', 'ApiIntegracaoController@ImportarColetas');
    Route::post('ExportarColetas', 'ApiIntegracaoController@ExportarColetas');
    Route::post('MarcarColetasExportadas', 'ApiIntegracaoController@MarcarColetasExportadas');
    Route::post('GetColetasNotaFrete', 'ApiIntegracaoController@GetColetasNotaFrete');
    Route::post('AtualizarNotaFreteColetas', 'ApiIntegracaoController@AtualizarNotaFreteColetas');
    Route::post('CarregarGeoCoordenadasCliente', 'ApiIntegracaoController@CarregarGeoCoordenadasCliente');
    Route::post('GetInfoColeta', 'ApiIntegracaoController@GetInfoColeta');

    //API - Coleta
    Route::post('GetDadosColeta', 'ApiColetaController@GetDadosColeta');
    Route::post('GetColetasPendentes', 'ApiColetaController@GetColetasPendentes');    
    Route::post('SetarDeslocaColeta', 'ApiColetaController@SetarDeslocaColeta');
    Route::post('SetarChegadaColeta', 'ApiColetaController@SetarChegadaColeta');
    Route::post('SetarInicioAtendColeta', 'ApiColetaController@SetarInicioAtendColeta');
    Route::post('IncluirNotaFiscalColeta', 'ApiColetaController@IncluirNotaFiscalColeta');
    Route::post('ExcluirNotaFiscalColeta', 'ApiColetaController@ExcluirNotaFiscalColeta');
    Route::post('AtualizarNotaFiscalColeta', 'ApiColetaController@AtualizarNotaFiscalColeta');
    Route::post('AtualizarReciboNotaFiscalColeta', 'ApiColetaController@AtualizarReciboNotaFiscalColeta');
    Route::post('GetNotasFiscaisColeta', 'ApiColetaController@GetNotasFiscaisColeta');
    Route::post('FinalizarColeta', 'ApiColetaController@FinalizarColeta');    
    Route::post('SetarDeslocaEntrega', 'ApiColetaController@SetarDeslocaEntrega');
    Route::post('SetarChegadaEntrega', 'ApiColetaController@SetarChegadaEntrega');
    Route::post('SetarInicioAtendEntrega', 'ApiColetaController@SetarInicioAtendEntrega');    
    Route::post('FinalizarEntrega', 'ApiColetaController@FinalizarEntrega');
    Route::post('IncluirComanda', 'ApiColetaController@IncluirComanda');
    Route::post('AtualizarComanda', 'ApiColetaController@AtualizarComanda');
    Route::post('ExcluirComanda', 'ApiColetaController@ExcluirComanda');
    Route::post('IniciarExpediente', 'ApiColetaController@IniciarExpediente');
    Route::post('FinalizarExpediente', 'ApiColetaController@FinalizarExpediente');
    Route::post('SetarDescargaColeta', 'ApiColetaController@SetarDescargaColeta');
    Route::post('BaldearColeta', 'ApiColetaController@BaldearColeta');
    Route::post('DevolverSemAtendColeta', 'ApiColetaController@DevolverSemAtendColeta');
    Route::post('DesfazerStatusAtualColeta', 'ApiColetaController@DesfazerStatusAtualColeta');
    Route::post('GerarSolicAuxiliarMultiDestinos', 'ApiColetaController@GerarSolicAuxiliarMultiDestinos');
    Route::post('GerarSolicReentrega', 'ApiColetaController@GerarSolicReentrega');
});