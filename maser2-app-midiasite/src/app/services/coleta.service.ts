import { Injectable } from '@angular/core';

import { HttpService } from '../services/http.service';
import { ProcsService } from '../services/procs.service';
import { GlobalService } from '../services/global.service';
import { MotoristaService } from './motorista.service';

@Injectable({
  providedIn: 'root'
})
export class ColetaService {

  public atualizarColetasPendentes: boolean = true;

  // Variáveis usadas para carregar o retorno da API GetColetasPendentes
  public contrato: any;
  public rota = new Array<any>();
  public carga = new Array<any>();

  // Variáveis usadas para carregar o retorno da API GetDadosColeta
  public dadosColetaAtual: any;
  public tipos_veiculo = new Array<any>();

  // Variável usada para carregar o retorno da API getNotasFiscaisColeta
  public notasFiscais = new Array<any>();
  public nfSemRecibo = 0;
  public nfSemResposta = 0;
  public dadosNotaAtual: any;

  // Variável usada para interagir com as comandas das solicitações
  public dadosComandaAtual: any;

  constructor(
    private http: HttpService,
    private procs: ProcsService,
    private global: GlobalService,
    private motorista: MotoristaService) {
    console.log('ColetaService -> constructor');
  }


  // Reseta as variáveis usadas para exibir os dados de solicitações
  // --------------------------------------------------
  resetColetasPendentes() {
    console.log('ColetaService -> resetColetasPendentes');
    this.contrato = '';
    this.rota = [];
    this.carga = [];
  }


  // Retorna se a solicitação é um CONTRATO
  // --------------------------------------------------
  getEhContrato(solicitacao: any): boolean {

    if ((solicitacao.coleta_fixa == 'C') && (this.procs.tratarStrNull(solicitacao.solic_origem_id) == '')) {
      return true
    } else {
      return false
    }

  }


  // Retorna se a solicitação é uma COMANDA
  // --------------------------------------------------
  getEhComanda(solicitacao: any): boolean {

    if ((solicitacao.coleta_fixa == 'C') && (solicitacao.solic_origem_id > '')) {
      return true
    } else {
      return false
    }

  }


  // Retorna a identificação da solicitação
  // --------------------------------------------------
  getIdSolicitacao(solicitacao: any, exibir_prefixo: boolean = false): string {

    // O ID é a identificação padrão da solicitação para o usuário
    let id = (exibir_prefixo == true) ? `ID: ${solicitacao.coleta_id}` : `ID: ${solicitacao.coleta_id}`;

    // CONTRATO
    if (this.getEhContrato(solicitacao) == true) {
      id = (exibir_prefixo == true) ? `Contrato: ${solicitacao.coleta_id}` : `Contrato: ${solicitacao.coleta_id}`
    }

    // COMANDA
    if (this.getEhComanda(solicitacao) == true) {
      id = (exibir_prefixo == true) ? `Comanda: ${solicitacao.coleta_id}` : `Comanda: ${solicitacao.coleta_id}`
    }

    // O NÚMERO, quando existir, é a identificação principal da solicitação para o usuário
    if (solicitacao.numero > '') {
      id = (exibir_prefixo == true) ? solicitacao.numero : solicitacao.numero
    }

    // REENTREGA
    if ((this.procs.tratarStrNull(solicitacao.reentrega) == 'R') || (this.procs.tratarStrNull(solicitacao.reentrega) == 'D')) {
      id = `${id}  [reentrega]`;
    }

    return id;
  }



  // Retorna o tipo da solicitação:
  // T -> Contrato, C -> Coleta, E -> Entrega
  // --------------------------------------------------
  getTipoSolicitacao(solicitacao: any, ehcontrato: boolean = false): string {

    if (ehcontrato == true) {
      return 'T';
    } else {
      // Retorna o primeiro caracter do campo STATUS: 'C' ou 'E'
      return solicitacao.status.slice(0, 1);
    }

  }


  // Retorna a cor do theme a ser usado para a solicitação
  // --------------------------------------------------
  getCorSolicitacao(solicitacao: any, etapa: any = ''): string {

    if (etapa == '') {
      switch (solicitacao.status) {
        case 'C0': return 'vermelho-web';
        case 'C1': return 'vermelho-web';
        case 'C2': return 'amarelo-web';
        case 'C3': return 'amarelo-web';
        case 'C4': return 'amarelo-web';
        case 'CR': return 'verde-web';
        case 'CD': return 'verde-web';
        case 'CN': return 'medium';
        case 'E0': return 'vermelho-web';
        case 'E1': return 'vermelho-web';
        case 'E2': return 'amarelo-web';
        case 'E3': return 'amarelo-web';
        case 'E4': return 'amarelo-web';
        case 'ER': return 'verde-web';
        case 'EP': return 'amarelo-web';
        case 'EN': return 'azul-logo';
        default: return 'medium';
      }
    }
    else if (etapa == 'C') {
      switch (solicitacao.status) {
        case 'C0': return 'vermelho-web';
        case 'C1': return 'vermelho-web';
        case 'C2': return 'amarelo-web';
        case 'C3': return 'amarelo-web';
        case 'C4': return 'amarelo-web';
        case 'CR': return 'verde-web';
        case 'CD': return 'verde-web';
        case 'CN': return 'medium';
        default: return 'light';
      }
    }
    else if (etapa == 'E') {
      switch (solicitacao.status) {
        case 'E0': return 'vermelho-web';
        case 'E1': return 'vermelho-web';
        case 'E2': return 'amarelo-web';
        case 'E3': return 'amarelo-web';
        case 'E4': return 'amarelo-web';
        case 'ER': return 'verde-web';
        case 'EP': return 'amarelo-web';
        case 'EN': return 'azul-logo';
        default: return 'light';
      }
    }
    else {
      return 'medium';
    }
  }


  // Retorna a carga da solicitação.
  // --------------------------------------------------
  getCargaSolicitacao(solicitacao: any): string {
    let solic_carga = '-';

    if (solicitacao.volumes > '') {
      solic_carga = solicitacao.volumes;
    }

    if (solicitacao.especie > '') {
      solic_carga = solic_carga.length > 0 ? `${solic_carga} ${solicitacao.especie}` : solicitacao.especie;
    }

    if (solicitacao.peso > '') {
      solic_carga = solic_carga.length > 0 ? `${solic_carga} ${solicitacao.peso}` : solicitacao.peso;
    }

    return solic_carga;
  }


  // Retorna as dimensões da carga da solicitação.
  // --------------------------------------------------
  getDimensoesCargaSolicitacao(solicitacao: any): string {
    let dimensoes: string = '-';

    if (solicitacao.comp_carga > '') {
      dimensoes = parseFloat(solicitacao.comp_carga).toString();
    }

    if (solicitacao.larg_carga > '') {
      dimensoes = dimensoes.length > 0 ? `${dimensoes} x ${parseFloat(solicitacao.larg_carga).toString()}` : parseFloat(solicitacao.larg_carga).toString();
    }

    if (solicitacao.alt_carga > '') {
      dimensoes = dimensoes.length > 0 ? `${dimensoes} x ${parseFloat(solicitacao.alt_carga).toString()}` : parseFloat(solicitacao.alt_carga).toString();
    }

    return dimensoes;
  }


  // Retorna o endereço do local de Coleta/Entrega da solicitação.
  // --------------------------------------------------
  getEnderecoLocalSolicitacao(solicitacao: any, local: any = 'C'): string {

    let endereco: string = '';

    if (local == 'C') {
      if (solicitacao.endereco_coleta > '') {
        endereco = solicitacao.endereco_coleta
      }
      if (solicitacao.bairro_coleta > '') {
        endereco = endereco.length > 0 ? `${endereco} - ${solicitacao.bairro_coleta}` : solicitacao.bairro_coleta
      }
      if (solicitacao.cidade_coleta > '') {
        endereco = endereco.length > 0 ? `${endereco}, ${solicitacao.cidade_coleta}` : solicitacao.cidade_coleta
      }
      if (solicitacao.uf_coleta > '') {
        endereco = endereco.length > 0 ? `${endereco} - ${solicitacao.uf_coleta}` : solicitacao.uf_coleta
      }
    }
    else if (local == 'E') {
      if (solicitacao.endereco_entrega > '') {
        endereco = solicitacao.endereco_entrega
      }
      if (solicitacao.bairro_entrega > '') {
        endereco = endereco.length > 0 ? `${endereco} - ${solicitacao.bairro_entrega}` : solicitacao.bairro_entrega
      }
      if (solicitacao.cidade_entrega > '') {
        endereco = endereco.length > 0 ? `${endereco}, ${solicitacao.cidade_entrega}` : solicitacao.cidade_entrega
      }
      if (solicitacao.uf_entrega > '') {
        endereco = endereco.length > 0 ? `${endereco} - ${solicitacao.uf_entrega}` : solicitacao.uf_entrega
      }
    }

    return endereco;
  }


  // Retorna o endereço do local de Coleta/Entrega da solicitação.
  // Fonte: https://tebros.com/2016/02/launching-external-maps-app-from-ionic2/
  // --------------------------------------------------
  getLocalizacaoSolicitacao(solicitacao: any, local: any = "D"): string {

    let location: string = '';

    if (local == 'D') {
      if ((solicitacao.geo_lat_destino > '') && (solicitacao.geo_lng_destino > '')) {
        location = `geo:?q=${solicitacao.geo_lat_destino},${solicitacao.geo_lng_destino}`
      }
    }
    else if (local == 'C') {
      if ((solicitacao.geo_lat_coleta > '') && (solicitacao.geo_lng_coleta > '')) {
        location = `geo:?q=${solicitacao.geo_lat_coleta},${solicitacao.geo_lng_coleta}`
      }
    }
    else if (local == 'E') {
      if ((solicitacao.geo_lat_entrega > '') && (solicitacao.geo_lng_entrega > '')) {
        location = `geo:?q=${solicitacao.geo_lat_entrega},${solicitacao.geo_lng_entrega}`
      }
    }

    return location;
  }


  // Retorna a descrição do sistema de carga da solicitação:
  // E -> Empilhadeira, P -> Ponte Rolante, M -> Manual
  // --------------------------------------------------
  getSisCargaSolicitacao(solicitacao: any): string {

    switch (solicitacao.sis_carga) {
      case 'E': return 'Empilhadeira';
      case 'P': return 'Ponte Rolante';
      case 'M': return 'Manual';
      case 'N': return 'Nenhum';
      default: return 'Indefinido';
    }

  }


  // API GetColetasPendentes
  // --------------------------------------------------
  async apiGetColetasPendentes() {
    console.log('ColetaService -> apiGetColetasPendentes');

    this.global.resetRespostaAPI();
    try {

      let retorno = await this.http.post(this.global.getUrlAPIMaser() + 'GetColetasPendentes', {}, this.global.getHeadersAPIMaser());

      if ((retorno.retorno.cod_retorno == 'Z100') || (retorno.retorno.cod_retorno == 'Z101')) {
        this.contrato = retorno.dados.contrato;
        this.rota = retorno.dados.rota;
        this.carga = retorno.dados.carga;
        this.global.setDataRespostaAPI(true, retorno);
      }
      // Limpamos os dados das coletas pendentes do app para não ficarem 
      // B201 - Você não está vinculado a um veículo neste momento. Motorista sem veículo não pode atender solicitações.
      else if (retorno.retorno.cod_retorno == 'B201') {
        this.resetColetasPendentes();
        this.motorista.setPlacaVeiculo('');
        this.motorista.setOcupVeiculo('');
        this.motorista.setUrlImgCarga('');
        this.motorista.setGPS('');
        this.motorista.notificacoes = [];
        this.motorista.notifNaoLida = 0;
        this.global.setDataRespostaAPI(false, retorno);
      }
      else {
        this.global.setDataRespostaAPI(false, retorno);
      }

      this.atualizarColetasPendentes = false;

    } catch (error) {
      console.log('ColetaService -> error API GetColetasPendentes', error);
      this.global.setErroRespostaAPI(error);
    }

  }

  // carregarColetasPendentes - API GetColetasPendentes + Loading
  async carregarColetasPendentes(refresh_notif: boolean = false) {
    console.log('ColetaService -> carregarColetasPendentes');

    await this.procs.iniciarLoading();
    try {
      this.atualizarColetasPendentes = true;
      await this.apiGetColetasPendentes();

      if (refresh_notif) {
        await this.motorista.apiGetNotificacoesMotorista();
      }
    }
    finally {
      await this.procs.finalizarLoading();
    }

  }


  // API GetDadosColeta
  // --------------------------------------------------
  async apiGetDadosColeta(coleta_id: string) {
    console.log('ColetaService -> apiGetDadosColeta', coleta_id);

    let body = {
      coleta_id: coleta_id
    }

    this.global.resetRespostaAPI();
    try {

      let retorno = await this.http.post(this.global.getUrlAPIMaser() + 'GetDadosColeta', body, this.global.getHeadersAPIMaser());

      if (retorno.retorno.cod_retorno == 'Z100') {
        this.dadosColetaAtual = retorno.dados;
        this.tipos_veiculo = retorno.tipos_veiculo;
        this.global.setDataRespostaAPI(true, retorno);
      }
      else {
        this.global.setDataRespostaAPI(false, retorno);
      }

    } catch (error) {
      console.log('ColetaService -> error API GetDadosColeta', error);
      this.global.setErroRespostaAPI(error);
    }

  }

  async carregarDadosColeta(coleta: any): Promise<boolean> {
    console.log('ColetaService -> carregarDadosColeta', coleta);

    await this.procs.iniciarLoading();
    await this.apiGetDadosColeta(coleta.coleta_id);
    await this.procs.finalizarLoading();

    if (this.global.getRespostaAPI().retorno == true) {
      return true;
    } else {
      this.global.exibirRespostaAPI();
      return false;
    }
  }


  // API SetarDeslocaColeta
  // --------------------------------------------------
  async apiSetarDeslocaColeta(coleta: any) {
    console.log('ColetaService -> apiSetarDeslocaColeta', coleta);

    let body = {
      coleta_id: coleta.coleta_id
    }

    this.global.resetRespostaAPI();
    try {

      let retorno = await this.http.post(this.global.getUrlAPIMaser() + 'SetarDeslocaColeta', body, this.global.getHeadersAPIMaser());

      if ((retorno.retorno.cod_retorno == 'Z100') || (retorno.retorno.cod_retorno == 'Z103')) {
        this.global.setDataRespostaAPI(true, retorno);
      } else {
        this.global.setDataRespostaAPI(false, retorno);
      }

    } catch (error) {
      console.log('ColetaService -> error API SetarDeslocaColeta', error);
      this.global.setErroRespostaAPI(error);
    }

  }


  // API SetarChegadaColeta
  // --------------------------------------------------
  async apiSetarChegadaColeta(coleta: any) {
    console.log('ColetaService -> apiSetarChegadaColeta', coleta);

    let body = {
      coleta_id: coleta.coleta_id,
      dur_prev_coleta: coleta.dur_prev_coleta
    }

    this.global.resetRespostaAPI();
    try {

      let retorno = await this.http.post(this.global.getUrlAPIMaser() + 'SetarChegadaColeta', body, this.global.getHeadersAPIMaser());

      if ((retorno.retorno.cod_retorno == 'Z100') || (retorno.retorno.cod_retorno == 'Z103')) {
        this.global.setDataRespostaAPI(true, retorno);
      } else {
        this.global.setDataRespostaAPI(false, retorno);
      }

    } catch (error) {
      console.log('ColetaService -> error API SetarChegadaColeta', error);
      this.global.setErroRespostaAPI(error);
    }

  }

  // API SetarInicioAtendColeta
  // --------------------------------------------------
  async apiSetarInicioAtendColeta(coleta: any) {
    console.log('ColetaService -> apiSetarInicioAtendColeta', coleta);

    let body = {
      coleta_id: coleta.coleta_id
    }

    this.global.resetRespostaAPI();
    try {

      let retorno = await this.http.post(this.global.getUrlAPIMaser() + 'SetarInicioAtendColeta', body, this.global.getHeadersAPIMaser());

      if ((retorno.retorno.cod_retorno == 'Z100') || (retorno.retorno.cod_retorno == 'Z103')) {
        this.global.setDataRespostaAPI(true, retorno);
      } else {
        this.global.setDataRespostaAPI(false, retorno);
      }

    } catch (error) {
      console.log('ColetaService -> error API SetarInicioAtendColeta', error);
      this.global.setErroRespostaAPI(error);
    }

  }


  // API FinalizarColeta
  // --------------------------------------------------
  async apiFinalizarColeta(coleta: any) {
    console.log('ColetaService -> apiFinalizarColeta', coleta);

    let body: any;

    if (coleta.realizada == 'S') {
      body = {
        coleta_id: coleta.coleta_id,
        realizada: coleta.realizada,
        img_carga_base64: coleta.img_base64,
        ocup_veiculo: coleta.ocup_veiculo,
        cod_tipo_veiculo_nec: coleta.cod_tipo_veiculo_nec,
        img_rom_base64: coleta.img_rom_base64,
        nfs_comerciais: coleta.nfs_comerciais
      }
    }
    else {
      body = {
        coleta_id: coleta.coleta_id,
        realizada: coleta.realizada,
        obs_nao_coleta: this.procs.tratarStrNull(coleta.obs_nao_coleta)
      }
    }

    this.global.resetRespostaAPI();
    try {

      let retorno = await this.http.post(this.global.getUrlAPIMaser() + 'FinalizarColeta', body, this.global.getHeadersAPIMaser());

      if (retorno.retorno.cod_retorno == 'Z100') {
        // Só precisa atualizar a ocupação do veículo quando a coleta for REALIZADA.
        if (coleta.realizada == 'S') {
          this.motorista.setUrlImgCarga(`data:image/jpeg;base64,${coleta.img_base64}`);
          this.motorista.setOcupVeiculo(coleta.ocup_veiculo);
        }
        this.global.setDataRespostaAPI(true, retorno);
      }
      else if (retorno.retorno.cod_retorno == 'Z103') {
        this.global.setDataRespostaAPI(true, retorno);
      }
      else {
        this.global.setDataRespostaAPI(false, retorno);
      }

    } catch (error) {
      console.log('ColetaService -> error API FinalizarColeta', error);
      this.global.setErroRespostaAPI(error);
    }

  }


  // API CancelarColetaSemDesloc
  // --------------------------------------------------
  async apiCancelarColetaSemDesloc(coleta: any) {
    console.log('ColetaService -> apiCancelarColetaSemDesloc', coleta);

    let body: any;

    body = {
      coleta_id: coleta.coleta_id,
      obs_nao_coleta: this.procs.tratarStrNull(coleta.obs_nao_coleta)
    }

    this.global.resetRespostaAPI();
    try {

      let retorno = await this.http.post(this.global.getUrlAPIMaser() + 'CancelarColetaSemDesloc', body, this.global.getHeadersAPIMaser());

      if (retorno.retorno.cod_retorno == 'Z100') {
        this.global.setDataRespostaAPI(true, retorno);
      }
      else if (retorno.retorno.cod_retorno == 'Z103') {
        this.global.setDataRespostaAPI(true, retorno);
      }
      else {
        this.global.setDataRespostaAPI(false, retorno);
      }

    } catch (error) {
      console.log('ColetaService -> error API CancelarColetaSemDesloc', error);
      this.global.setErroRespostaAPI(error);
    }

  }


  // API SetarDeslocaEntrega
  // --------------------------------------------------
  async apiSetarDeslocaEntrega(coleta: any) {
    console.log('ColetaService -> apiSetarDeslocaEntrega', coleta);

    let body = {
      coleta_id: coleta.coleta_id
    }

    this.global.resetRespostaAPI();
    try {

      let retorno = await this.http.post(this.global.getUrlAPIMaser() + 'SetarDeslocaEntrega', body, this.global.getHeadersAPIMaser());

      if ((retorno.retorno.cod_retorno == 'Z100') || (retorno.retorno.cod_retorno == 'Z103')) {
        this.global.setDataRespostaAPI(true, retorno);
      } else {
        this.global.setDataRespostaAPI(false, retorno);
      }

    } catch (error) {
      console.log('ColetaService -> error API SetarDeslocaEntrega', error);
      this.global.setErroRespostaAPI(error);
    }

  }


  // API SetarChegadaEntrega
  // --------------------------------------------------
  async apiSetarChegadaEntrega(coleta: any) {
    console.log('ColetaService -> apiSetarChegadaEntrega', coleta);

    let body = {
      coleta_id: coleta.coleta_id,
      dur_prev_entrega: coleta.dur_prev_entrega
    }

    this.global.resetRespostaAPI();
    try {

      let retorno = await this.http.post(this.global.getUrlAPIMaser() + 'SetarChegadaEntrega', body, this.global.getHeadersAPIMaser());

      if ((retorno.retorno.cod_retorno == 'Z100') || (retorno.retorno.cod_retorno == 'Z103')) {
        this.global.setDataRespostaAPI(true, retorno);
      } else {
        this.global.setDataRespostaAPI(false, retorno);
      }

    } catch (error) {
      console.log('ColetaService -> error API SetarChegadaEntrega', error);
      this.global.setErroRespostaAPI(error);
    }

  }

  // API SetarInicioAtendEntrega
  // --------------------------------------------------
  async apiSetarInicioAtendEntrega(coleta: any) {
    console.log('ColetaService -> apiSetarInicioAtendEntrega', coleta);

    let body = {
      coleta_id: coleta.coleta_id
    }

    this.global.resetRespostaAPI();
    try {

      let retorno = await this.http.post(this.global.getUrlAPIMaser() + 'SetarInicioAtendEntrega', body, this.global.getHeadersAPIMaser());

      if ((retorno.retorno.cod_retorno == 'Z100') || (retorno.retorno.cod_retorno == 'Z103')) {
        this.global.setDataRespostaAPI(true, retorno);
      } else {
        this.global.setDataRespostaAPI(false, retorno);
      }

    } catch (error) {
      console.log('ColetaService -> error API SetarInicioAtendEntrega', error);
      this.global.setErroRespostaAPI(error);
    }

  }


  // API DevolverSemAtendColeta
  // --------------------------------------------------
  async apiDevolverSemAtendColeta(coleta: any) {
    console.log('ColetaService -> apiDevolverSemAtendColeta', coleta);

    let body = {
      coleta_id: coleta.coleta_id,
      obs_nao_coleta: this.procs.tratarStrNull(coleta.obs_nao_coleta)
    }

    this.global.resetRespostaAPI();
    try {

      let retorno = await this.http.post(this.global.getUrlAPIMaser() + 'DevolverSemAtendColeta', body, this.global.getHeadersAPIMaser());

      if ((retorno.retorno.cod_retorno == 'Z100') || (retorno.retorno.cod_retorno == 'Z103')) {
        this.global.setDataRespostaAPI(true, retorno);
      } else {
        this.global.setDataRespostaAPI(false, retorno);
      }

    } catch (error) {
      console.log('ColetaService -> error API DevolverSemAtendColeta', error);
      this.global.setErroRespostaAPI(error);
    }

  }


  // API FinalizarEntrega
  // --------------------------------------------------
  async apiFinalizarEntrega(coleta: any) {
    console.log('ColetaService -> apiFinalizarEntrega', coleta);

    let body: any;

    if ((coleta.realizada == 'S') || (coleta.realizada == 'P')) {
      body = {
        coleta_id: coleta.coleta_id,
        recebedor: this.procs.tratarStrNull(coleta.recebedor),
        img_rom_base64: coleta.img_rom_base64,
        realizada: coleta.realizada
      }
    }
    else {
      body = {
        coleta_id: coleta.coleta_id,
        realizada: coleta.realizada,
        mot_nao_entrega: coleta.mot_nao_entrega,
        obs_nao_entrega: this.procs.tratarStrNull(coleta.obs_nao_entrega)
      }
    }

    this.global.resetRespostaAPI();
    try {

      let retorno = await this.http.post(this.global.getUrlAPIMaser() + 'FinalizarEntrega', body, this.global.getHeadersAPIMaser());

      if ((retorno.retorno.cod_retorno == 'Z100') || (retorno.retorno.cod_retorno == 'Z103')) {
        this.global.setDataRespostaAPI(true, retorno);
      } else {
        this.global.setDataRespostaAPI(false, retorno);
      }

    } catch (error) {
      console.log('ColetaService -> error API FinalizarEntrega', error);
      this.global.setErroRespostaAPI(error);
    }

  }


  // API DesfazerStatusAtualColeta
  // --------------------------------------------------
  async apiDesfazerStatusAtualColeta(coleta: any) {
    console.log('ColetaService -> apiDesfazerStatusAtualColeta', coleta);

    let body = {
      coleta_id: coleta.coleta_id
    }

    this.global.resetRespostaAPI();
    try {

      let retorno = await this.http.post(this.global.getUrlAPIMaser() + 'DesfazerStatusAtualColeta', body, this.global.getHeadersAPIMaser());

      if ((retorno.retorno.cod_retorno == 'Z100') || (retorno.retorno.cod_retorno == 'Z103')) {
        this.global.setDataRespostaAPI(true, retorno);
      } else {
        this.global.setDataRespostaAPI(false, retorno);
      }

    } catch (error) {
      console.log('ColetaService -> error API DesfazerStatusAtualColeta', error);
      this.global.setErroRespostaAPI(error);
    }

  }


  // API GetNotasFiscaisColeta
  // --------------------------------------------------
  async apiGetNotasFiscaisColeta(coleta: any) {
    console.log('ColetaService -> apiGetNotasFiscaisColeta', coleta);

    let body = {
      coleta_id: coleta.coleta_id
    }

    this.global.resetRespostaAPI();
    try {

      let retorno = await this.http.post(this.global.getUrlAPIMaser() + 'GetNotasFiscaisColeta', body, this.global.getHeadersAPIMaser());

      if (retorno.retorno.cod_retorno == 'Z100') {
        this.notasFiscais = retorno.dados;
        this.nfSemRecibo = this.notasFiscais.filter(n => { return !(n.url_recibo > '') }).length;
        // Quantidade de notas que não tiveram um feedback informado ainda (sem recibo E sem motivo de não entrega)
        this.nfSemResposta = this.notasFiscais.filter(n => { return (!(n.url_recibo > '') && !(n.mot_nao_entrega > '')) }).length;
        this.global.setDataRespostaAPI(true, retorno);
      }
      else if (retorno.retorno.cod_retorno == 'Z101') {
        this.notasFiscais = [];
        this.nfSemRecibo = 0;
        this.nfSemResposta = 0;
        this.global.setDataRespostaAPI(true, retorno);
      } else {
        this.global.setDataRespostaAPI(false, retorno);
      }

    } catch (error) {
      console.log('ColetaService -> error API GetNotasFiscaisColeta', error);
      this.global.setErroRespostaAPI(error);
    }

  }

  async carregarNotasFiscaisColeta(coleta: any): Promise<boolean> {
    console.log('ColetaService -> carregarNotasFiscaisColeta', coleta);

    await this.procs.iniciarLoading();
    await this.apiGetNotasFiscaisColeta(coleta);
    await this.procs.finalizarLoading();

    if (this.global.getRespostaAPI().retorno == true) {
      return true;
    } else {
      this.global.exibirRespostaAPI();
      return false;
    }
  }


  // API IncluirNotaFiscalColeta
  // --------------------------------------------------
  async apiIncluirNotaFiscalColeta(nf: any) {
    console.log('ColetaService -> apiIncluirNotaFiscalColeta', nf);

    let body = {
      coleta_id: nf.coleta_id,
      cod_barras: nf.cod_barras,
      serie: nf.serie,
      numero: nf.numero,
      valor: nf.valor,
      volumes: nf.volumes,
      dig_cnpj: this.procs.tratarStrNull(nf.dig_cnpj)
    }

    // Como o campo que informa os dois últimos dígitos do CNPJ é numérico para exibir o teclado numérico, 
    // adicionamos o zero a esquerda para os casos em que o primeiro dígito é um zero, pois ele chega aqui
    // desconsiderando esse zero.
    if (parseFloat(nf.dig_cnpj) < 10) {
      body.dig_cnpj = "0" + nf.dig_cnpj.toString()
    }

    this.global.resetRespostaAPI();
    try {

      let retorno = await this.http.post(this.global.getUrlAPIMaser() + 'IncluirNotaFiscalColeta', body, this.global.getHeadersAPIMaser());

      if (retorno.retorno.cod_retorno == 'Z100') {
        this.global.setDataRespostaAPI(true, retorno);
      } else {
        this.global.setDataRespostaAPI(false, retorno);
      }

    } catch (error) {
      console.log('ColetaService -> error API IncluirNotaFiscalColeta', error);
      this.global.setErroRespostaAPI(error);
    }

  }

  // API AtualizarNotaFiscalColeta
  // --------------------------------------------------
  async apiAtualizarNotaFiscalColeta(nf: any) {
    console.log('ColetaService -> apiAtualizarNotaFiscalColeta', nf);

    let body = {
      coleta_nf_id: nf.coleta_nf_id,
      coleta_id: nf.coleta_id,
      cod_barras: nf.cod_barras,
      valor: nf.valor,
      volumes: nf.volumes
    }

    this.global.resetRespostaAPI();
    try {

      let retorno = await this.http.post(this.global.getUrlAPIMaser() + 'AtualizarNotaFiscalColeta', body, this.global.getHeadersAPIMaser());

      if (retorno.retorno.cod_retorno == 'Z100') {
        this.global.setDataRespostaAPI(true, retorno);
      } else {
        this.global.setDataRespostaAPI(false, retorno);
      }

    } catch (error) {
      console.log('ColetaService -> error API AtualizarNotaFiscalColeta', error);
      this.global.setErroRespostaAPI(error);
    }

  }


  // API AtualizarReciboNotaFiscalColeta
  // --------------------------------------------------
  async apiAtualizarReciboNotaFiscalColeta(nf: any) {
    console.log('ColetaService -> apiAtualizarReciboNotaFiscalColeta', nf);

    let body = {
      coleta_nf_id: nf.coleta_nf_id,
      coleta_id: nf.coleta_id,
      img_base64: nf.img_base64,
      mot_nao_entrega: nf.mot_nao_entrega
    }

    this.global.resetRespostaAPI();
    try {

      let retorno = await this.http.post(this.global.getUrlAPIMaser() + 'AtualizarReciboNotaFiscalColeta', body, this.global.getHeadersAPIMaser());

      if (retorno.retorno.cod_retorno == 'Z100') {

        // Atualizamos o campo "url_recibo" da nota que está no array notasFiscais.
        // Dessa forma não precisa consumir a API que busca as notas para trazer com essa atualização.
        if (nf.img_base64 > '') {
          this.notasFiscais.forEach(n => { if (n.coleta_nf_id == nf.coleta_nf_id) { n.url_recibo = 'data:image/jpeg;base64,' + nf.img_base64 } });
        }

        this.nfSemRecibo = this.notasFiscais.filter(n => { return !(n.url_recibo > '') }).length;
        // Quantidade de notas que não tiveram um feedback informado ainda (sem recibo E sem motivo de não entrega)
        this.nfSemResposta = this.notasFiscais.filter(n => { return (!(n.url_recibo > '') && !(n.mot_nao_entrega > '')) }).length;


        this.global.setDataRespostaAPI(true, retorno);
      } else {
        this.global.setDataRespostaAPI(false, retorno);
      }

    } catch (error) {
      console.log('ColetaService -> error API AtualizarReciboNotaFiscalColeta', error);
      this.global.setErroRespostaAPI(error);
    }

  }


  // API ExcluirNotaFiscalColeta
  // --------------------------------------------------
  async apiExcluirNotaFiscalColeta(nf: any) {
    console.log('ColetaService -> apiExcluirNotaFiscalColeta', nf);

    let body = {
      coleta_nf_id: nf.coleta_nf_id,
      coleta_id: nf.coleta_id,
      cod_barras: nf.cod_barras
    }

    this.global.resetRespostaAPI();
    try {

      let retorno = await this.http.post(this.global.getUrlAPIMaser() + 'ExcluirNotaFiscalColeta', body, this.global.getHeadersAPIMaser());

      if (retorno.retorno.cod_retorno == 'Z100') {
        // Se excluiu a nota no servidor, excluímos ela do array para evitar de chamar a api que carrega as notas.
        // Usamos a função filter do array para retornar todas as notas menos a que foi excluída.
        this.notasFiscais = this.notasFiscais.filter(nota => { return nota.coleta_nf_id != nf.coleta_nf_id });
        this.nfSemRecibo = this.notasFiscais.filter(n => { return !(n.url_recibo > '') }).length;
        // Quantidade de notas que não tiveram um feedback informado ainda (sem recibo E sem motivo de não entrega)
        this.nfSemResposta = this.notasFiscais.filter(n => { return (!(n.url_recibo > '') && !(n.mot_nao_entrega > '')) }).length;
        this.global.setDataRespostaAPI(true, retorno);
      } else {
        this.global.setDataRespostaAPI(false, retorno);
      }

    } catch (error) {
      console.log('ColetaService -> error API ExcluirNotaFiscalColeta', error);
      this.global.setErroRespostaAPI(error);
    }

  }


  // API IniciarExpediente
  // --------------------------------------------------
  async apiIniciarExpediente(coleta: any) {
    console.log('ColetaService -> apiIniciarExpediente', coleta);

    let body = {
      coleta_id: coleta.coleta_id
    }

    this.global.resetRespostaAPI();
    try {

      let retorno = await this.http.post(this.global.getUrlAPIMaser() + 'IniciarExpediente', body, this.global.getHeadersAPIMaser());

      if (retorno.retorno.cod_retorno == 'Z100') {
        this.global.setDataRespostaAPI(true, retorno);
      } else {
        this.global.setDataRespostaAPI(false, retorno);
      }

    } catch (error) {
      console.log('ColetaService -> error API IniciarExpediente', error);
      this.global.setErroRespostaAPI(error);
    }

  }


  // API FinalizarExpediente
  // --------------------------------------------------
  async apiFinalizarExpediente(coleta: any) {
    console.log('ColetaService -> apiFinalizarExpediente', coleta);

    let body = {
      coleta_id: coleta.coleta_id
    }

    this.global.resetRespostaAPI();
    try {

      let retorno = await this.http.post(this.global.getUrlAPIMaser() + 'FinalizarExpediente', body, this.global.getHeadersAPIMaser());

      if (retorno.retorno.cod_retorno == 'Z100') {
        this.global.setDataRespostaAPI(true, retorno);
      } else {
        this.global.setDataRespostaAPI(false, retorno);
      }

    } catch (error) {
      console.log('ColetaService -> error API FinalizarExpediente', error);
      this.global.setErroRespostaAPI(error);
    }

  }


  // API IncluirComanda
  // --------------------------------------------------
  async apiIncluirComanda(comanda: any) {
    console.log('ColetaService -> apiIncluirComanda', comanda);

    let body = {
      solic_origem_id: comanda.solic_origem_id,
      local_coleta: this.procs.tratarStrNull(comanda.local_coleta),
      local_entrega: this.procs.tratarStrNull(comanda.local_entrega),
      obs_coleta: this.procs.tratarStrNull(comanda.obs_coleta)
    }

    this.global.resetRespostaAPI();
    try {

      let retorno = await this.http.post(this.global.getUrlAPIMaser() + 'IncluirComanda', body, this.global.getHeadersAPIMaser());

      if (retorno.retorno.cod_retorno == 'Z100') {
        this.global.setDataRespostaAPI(true, retorno);
      } else {
        this.global.setDataRespostaAPI(false, retorno);
      }

    } catch (error) {
      console.log('ColetaService -> error API IncluirComanda', error);
      this.global.setErroRespostaAPI(error);
    }

  }


  // API AtualizarComanda
  // --------------------------------------------------
  async apiAtualizarComanda(comanda: any) {
    console.log('ColetaService -> apiAtualizarComanda', comanda);

    let body = {
      solic_origem_id: comanda.solic_origem_id,
      coleta_id: comanda.coleta_id,
      local_coleta: this.procs.tratarStrNull(comanda.local_coleta),
      local_entrega: this.procs.tratarStrNull(comanda.local_entrega),
      obs_coleta: this.procs.tratarStrNull(comanda.obs_coleta)
    }

    this.global.resetRespostaAPI();
    try {

      let retorno = await this.http.post(this.global.getUrlAPIMaser() + 'AtualizarComanda', body, this.global.getHeadersAPIMaser());

      if (retorno.retorno.cod_retorno == 'Z100') {
        this.dadosColetaAtual.local_coleta = comanda.local_coleta;
        this.dadosColetaAtual.local_entrega = comanda.local_entrega;
        this.dadosColetaAtual.obs_coleta = comanda.obs_coleta;
        this.global.setDataRespostaAPI(true, retorno);
      } else {
        this.global.setDataRespostaAPI(false, retorno);
      }

    } catch (error) {
      console.log('ColetaService -> error API AtualizarComanda', error);
      this.global.setErroRespostaAPI(error);
    }

  }


  // API ExcluirComanda
  // --------------------------------------------------
  async apiExcluirComanda(comanda: any) {
    console.log('ColetaService -> apiExcluirComanda', comanda);

    let body = {
      solic_origem_id: comanda.solic_origem_id,
      coleta_id: comanda.coleta_id
    }

    this.global.resetRespostaAPI();
    try {

      let retorno = await this.http.post(this.global.getUrlAPIMaser() + 'ExcluirComanda', body, this.global.getHeadersAPIMaser());

      if (retorno.retorno.cod_retorno == 'Z100') {
        this.global.setDataRespostaAPI(true, retorno);
      } else {
        this.global.setDataRespostaAPI(false, retorno);
      }

    } catch (error) {
      console.log('ColetaService -> error API ExcluirComanda', error);
      this.global.setErroRespostaAPI(error);
    }

  }


  // API SetarDescargaColeta
  // --------------------------------------------------
  async apiSetarDescargaColeta(coleta: any) {
    console.log('ColetaService -> apiSetarDescargaColeta', coleta);

    let body = {
      coleta_id: coleta.coleta_id
    }

    this.global.resetRespostaAPI();
    try {

      let retorno = await this.http.post(this.global.getUrlAPIMaser() + 'SetarDescargaColeta', body, this.global.getHeadersAPIMaser());

      if (retorno.retorno.cod_retorno == 'Z100') {
        this.global.setDataRespostaAPI(true, retorno);
      } else {
        this.global.setDataRespostaAPI(false, retorno);
      }

    } catch (error) {
      console.log('ColetaService -> error API SetarDescargaColeta', error);
      this.global.setErroRespostaAPI(error);
    }

  }


  // API BaldearColeta
  // --------------------------------------------------
  async apiBaldearColeta(coleta: any) {
    console.log('ColetaService -> apiBaldearColeta', coleta);

    let body = {
      coleta_id: coleta.coleta_id,
      placa_destino: coleta.placa_destino,
      transfer_code: coleta.transfer_code
    }

    this.global.resetRespostaAPI();
    try {

      let retorno = await this.http.post(this.global.getUrlAPIMaser() + 'BaldearColeta', body, this.global.getHeadersAPIMaser());

      if (retorno.retorno.cod_retorno == 'Z100') {
        this.global.setDataRespostaAPI(true, retorno);
      } else {
        this.global.setDataRespostaAPI(false, retorno);
      }

    } catch (error) {
      console.log('ColetaService -> error API BaldearColeta', error);
      this.global.setErroRespostaAPI(error);
    }

  }

}