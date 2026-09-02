const colPendFiltros =
{
  somenteHoje: true,
  antesTarde: false
};

const veiFrotaFiltros =
{
  comMotorista: true,
  comCarga: null  
};

const solicVeiculoCargaFiltros =
{
  placa: null,
  local_saida_descr: 'Veículo',
  local_saida: 'V',
  hora_saida: new Date()
};

export default {
  countColetasPendentesData: 0,
  countColetasAndamentoData: 0,
  countEntregasPendentesData: 0,
  countEntregasAndamentoData: 0,
  countSolicitacoesFinalizadasData: 0,

  coletasPendentesData: [],
  coletasPendentesFiltros: colPendFiltros,
  coletasAndamentoData: [],

  entregasPendentesData: [],  
  entregasAndamentoData: [],
  
  solicitacoesFinalizadasData: [],

  veiculosFrotaData: [],
  veiculosFrotaFiltros: veiFrotaFiltros,

  definirVeiculos: false,
  veiculosColetaModal: false,
  dadosColetaData: [],
  veiculosData: [],  
  dadosVeiculoCargaData: [],
  dadosEntregasPendCargaData: [],
  dadosColetasVeiculoCargaData: [],  

  coletasVeiculoCargaFiltros: solicVeiculoCargaFiltros,

  dadosColetasResumoDiaData: [],

  veiculoCarga: false,

  coletaUrlImgData: null,

  exibirVeiculosBaldeacao: false,
  veiculosBaldeacaoDados: [],
  veiculosBaldeacaoVeiculos: [],  
}
