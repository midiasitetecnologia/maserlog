import { Injectable } from '@angular/core';

import { HttpService } from '../services/http.service';
import { ProcsService } from '../services/procs.service';
import { GlobalService } from '../services/global.service';

let key_email = "motorista_email";
let key_id = "motorista_id";
let key_nome = "motorista_nome";
let key_celular = "motorista_celular";
let key_gps = "motorista_gps";
let key_placa_veiculo = "motorista_placa_veiculo";
let key_ocup_veiculo = "motorista_ocup_veiculo";
let key_url_img_carga = "motorista_url_img_carga";

@Injectable({
  providedIn: 'root'
})
export class MotoristaService {

  // Variáveis usadas para carregar o retorno da API GetVeiculosDisponiveis
  public veiculosDisp = new Array<any>();

  // Variáveis usadas para carregar o retorno da API GetVeiculosProximos
  public veiculosProx: any;
  public veiculosOutros = new Array<any>();

  // Variáveis usadas para carregar o retorno da API GetNotificacoesMotorista
  public notificacoes = new Array<any>();
  public notifNaoLida = 0;

  // Variável usada para carregar o retorno da API GetTransferCodeVeiculo
  public transferVeiculo: any;

  constructor(
    private http: HttpService,
    private procs: ProcsService,
    private global: GlobalService) {
    console.log('MotoristaService -> constructor');
  }

  // Email
  // --------------------------------------------------
  getEmail(): string {
    return this.procs.tratarStrNull(localStorage.getItem(key_email));
  }

  setEmail(email: string) {
    localStorage.setItem(key_email, email);
  }


  // ID
  // --------------------------------------------------
  getID(): string {
    return this.procs.tratarStrNull(localStorage.getItem(key_id));
  }

  setID(id: string) {
    localStorage.setItem(key_id, id);
  }


  // Nome
  // --------------------------------------------------
  setNome(nome: string) {
    localStorage.setItem(key_nome, nome);
  }

  getNome(): string {
    return this.procs.tratarStrNull(localStorage.getItem(key_nome));
  }

  getPrimeiroNome(): string {

    let nome = this.getNome();

    try {
      let nomes = nome.split(' ');
      nome = nomes[0];
      nome = nome.charAt(0).toUpperCase() + nome.slice(1).toLowerCase();
    } catch (error) {
      console.log('getPrimeiroNome -> erro: ' + error)
      nome = this.getNome();
    }

    return nome;
  }


  // Celular
  // --------------------------------------------------
  getCelular(): string {
    return this.procs.tratarStrNull(localStorage.getItem(key_celular));
  }

  setCelular(celular: string) {
    localStorage.setItem(key_celular, celular);
  }


  // Placa do Veículo
  // --------------------------------------------------
  getPlacaVeiculo(): string {
    return this.procs.tratarStrNull(localStorage.getItem(key_placa_veiculo));
  }

  setPlacaVeiculo(placa_veiculo: string) {
    localStorage.setItem(key_placa_veiculo, placa_veiculo);
  }


  // Ocupação do Veículo
  // --------------------------------------------------
  setOcupVeiculo(ocup_veiculo: string) {
    localStorage.setItem(key_ocup_veiculo, ocup_veiculo);
  }

  getOcupVeiculo(exibicao: boolean = false): string {
    let ocupacao = this.procs.tratarStrNull(localStorage.getItem(key_ocup_veiculo));

    if ((ocupacao == '') || (ocupacao == null)) {
      ocupacao = '0'
    }

    if (exibicao == true) {
      if (ocupacao == '0') {
        return 'Sem carga';
      } else {
        return ocupacao + '%';
      }
    } else {
      return ocupacao;
    }
  }

  getNumberOcupVeiculo(): number {
    let ocupacao = this.procs.tratarStrNull(localStorage.getItem(key_ocup_veiculo));

    if ((ocupacao == '') || (ocupacao == null)) {
      return 0
    } else {
      return parseInt(ocupacao);
    }
  }

  getCorOcupVeiculo(ocup_veiculo: number): string {

    if (ocup_veiculo > 75) {
      return 'vermelho-web';
    } else if (ocup_veiculo > 50) {
      return 'amarelo-web';
    } else if (ocup_veiculo > 0) {
      return 'verde-web';
    } else {
      //return 'medium';
      return 'primary';
    }

  }



  // Url da imagem da carga do veículo
  // --------------------------------------------------
  getUrlImgCarga(): string {
    return this.procs.tratarStrNull(localStorage.getItem(key_url_img_carga));
  }

  setUrlImgCarga(url_img_carga: string) {
    localStorage.setItem(key_url_img_carga, url_img_carga);
  }


  // GPS
  // --------------------------------------------------
  getGPS(): string {
    return this.procs.tratarStrNull(localStorage.getItem(key_gps));
  }

  setGPS(gps: string) {
    localStorage.setItem(key_gps, gps);
  }


  // API AutenticarMotorista
  // --------------------------------------------------
  async apiAutenticarMotorista(email: string, password: string) {
    console.log('MotoristaService -> apiAutenticarMotorista', email, password);

    let body = {
      email: email.toUpperCase(),
      password: password,
      id_disp: this.global.getIdDevice()
    }

    this.global.resetRespostaAPI();
    try {

      let retorno = await this.http.post(this.global.getUrlAPIMaser() + 'AutenticarMotorista', body, this.global.getHeadersAPIMaser(false));

      if (retorno.retorno.cod_retorno == 'A100') {
        this.global.setAPIToken(retorno.dados.api_token);
        this.setEmail(email.toUpperCase());
        this.setID(retorno.dados.userInfo.uid);
        this.setNome(retorno.dados.userInfo.displayName);
        this.setCelular(retorno.dados.celular);
        this.setPlacaVeiculo(retorno.dados.placa_veiculo);
        this.setOcupVeiculo(retorno.dados.ocup_veiculo);
        this.setUrlImgCarga(retorno.dados.url_img_carga);
        // Importante: Sempre setar a informação do GPS depois de setar a
        // Placa do Veículo, pois caso habilite o GPS do dispositivo a 
        // notificação de rastreamento é personalizada com a placa do veículo.
        this.setGPS(retorno.dados.gps);
        this.global.setDataRespostaAPI(true, retorno);
      }
      else {
        this.global.setDataRespostaAPI(false, retorno);
      }

    } catch (error) {
      console.log('MotoristaService -> error API AutenticarMotorista', error);
      this.global.setErroRespostaAPI(error);
    }

  }


  // API SetarLogoutMotorista
  // --------------------------------------------------
  async apiSetarLogoutMotorista() {
    console.log('MotoristaService -> apiSetarLogoutMotorista');

    this.global.resetRespostaAPI();
    try {

      let retorno = await this.http.post(this.global.getUrlAPIMaser() + 'SetarLogoutMotorista', {}, this.global.getHeadersAPIMaser());

      if (retorno.retorno.cod_retorno == 'Z100') {
        //A API SetarLogoutMotorista não invalida o TOKEN do motorista.
        //Logo, limpamos ele aqui para forçar o login para as próximas solicitações das APIs.
        //Também aproveitamos para limpar todos os outros dados do motorista.
        this.global.setAPIToken('');
        this.setID('');
        this.setNome('');
        this.setCelular('');
        this.setPlacaVeiculo('');
        this.setOcupVeiculo('');
        this.setUrlImgCarga('');
        this.setGPS('');
        this.global.setDataRespostaAPI(true, retorno);
      }
      else {
        this.global.setDataRespostaAPI(false, retorno);
      }

    } catch (error) {
      console.log('MotoristaService -> error API SetarLogoutMotorista', error);
      this.global.setErroRespostaAPI(error);
    }

  }


  // API GetVeiculosDisponiveis
  // --------------------------------------------------
  async apiGetVeiculosDisponiveis() {
    console.log('MotoristaService -> apiGetVeiculosDisponiveis');

    this.global.resetRespostaAPI();
    try {

      let retorno = await this.http.post(this.global.getUrlAPIMaser() + 'GetVeiculosDisponiveis', {}, this.global.getHeadersAPIMaser());

      if (retorno.retorno.cod_retorno == 'Z100') {
        this.veiculosDisp = retorno.dados;
        this.global.setDataRespostaAPI(true, retorno);
      } else {
        this.global.setDataRespostaAPI(false, retorno);
      }

    } catch (error) {
      console.log('MotoristaService -> error API GetVeiculosDisponiveis', error);
      this.global.setErroRespostaAPI(error);
    }

  }


  // API AlterarVeiculoMotorista
  // --------------------------------------------------
  async apiAlterarVeiculoMotorista(placa_veiculo: string) {
    console.log('MotoristaService -> apiAlterarVeiculoMotorista', placa_veiculo);

    let body = {
      placa: placa_veiculo,
      utilizar: 'S'
    }

    // Quando a placa for igual a '' ou null significa que o usuário selecionou 
    // a opção "Nenhum (não estou atendendo)". Então, de acordo com as regras da
    // API, devemos passar a placa atual do motorista e o parâmetro "utilizar" 
    // igual a "N" para sinalizar que está deixando de usar este veículo.
    if ((placa_veiculo == '') || (placa_veiculo == null)) {
      body.placa = this.getPlacaVeiculo()
      body.utilizar = 'N'
    }

    this.global.resetRespostaAPI();
    try {

      let retorno = await this.http.post(this.global.getUrlAPIMaser() + 'AlterarVeiculoMotorista', body, this.global.getHeadersAPIMaser());

      if (retorno.retorno.cod_retorno == 'Z100') {
        this.setPlacaVeiculo(placa_veiculo);
        this.setOcupVeiculo(retorno.dados.ocup_veiculo);
        this.setUrlImgCarga(retorno.dados.url_img_carga);
        // Importante: Sempre setar a informação do GPS depois de setar a
        // Placa do Veículo, pois caso habilite o GPS do dispositivo a 
        // notificação de rastreamento é personalizada com a placa do veículo.
        this.setGPS(retorno.dados.gps);
        this.global.setDataRespostaAPI(true, retorno);
      }
      else if (retorno.retorno.cod_retorno == 'Z103') {
        this.global.setDataRespostaAPI(true, retorno);
      }
      else {
        this.global.setDataRespostaAPI(false, retorno);
      }

    } catch (error) {
      console.log('MotoristaService -> error API AlterarVeiculoMotorista', error);
      this.global.setErroRespostaAPI(error);
    }

  }


  // API AtualizarOcupacaoVeiculo
  // --------------------------------------------------
  async apiAtualizarOcupacaoVeiculo(veiculo: any) {
    console.log('MotoristaService -> apiAtualizarOcupacaoVeiculo', veiculo);

    let body = {
      img_base64: veiculo.img_base64,
      ocup_veiculo: veiculo.ocup_veiculo
    }

    this.global.resetRespostaAPI();
    try {

      let retorno = await this.http.post(this.global.getUrlAPIMaser() + 'AtualizarOcupacaoVeiculo', body, this.global.getHeadersAPIMaser());

      if (retorno.retorno.cod_retorno == 'Z100') {
        this.setOcupVeiculo(veiculo.ocup_veiculo);
        if (veiculo.img_base64 > '') {
          this.setUrlImgCarga(`data:image/jpeg;base64,${veiculo.img_base64}`);
        } else {
          this.setUrlImgCarga('');
        }
        this.global.setDataRespostaAPI(true, retorno);
      } else {
        this.global.setDataRespostaAPI(false, retorno);
      }

    } catch (error) {
      console.log('MotoristaService -> error API AtualizarOcupacaoVeiculo', error);
      this.global.setErroRespostaAPI(error);
    }

  }


  // API GetVeiculosProximos
  // --------------------------------------------------
  async apiGetVeiculosProximos() {
    console.log('MotoristaService -> apiGetVeiculosProximos');

    this.global.resetRespostaAPI();
    try {

      let retorno = await this.http.post(this.global.getUrlAPIMaser() + 'GetVeiculosProximos', {}, this.global.getHeadersAPIMaser());

      if ((retorno.retorno.cod_retorno == 'Z100') || (retorno.retorno.cod_retorno == 'Z101')) {
        this.veiculosProx = retorno.dados.proximo;
        this.veiculosOutros = retorno.dados.outros;
        this.global.setDataRespostaAPI(true, retorno);
      } else {
        this.global.setDataRespostaAPI(false, retorno);
      }

    } catch (error) {
      console.log('MotoristaService -> error API GetVeiculosProximos', error);
      this.global.setErroRespostaAPI(error);
    }

  }


  // API GetNotificacoesMotorista
  // --------------------------------------------------
  async apiGetNotificacoesMotorista() {
    console.log('MotoristaService -> apiGetNotificacoesMotorista');

    this.global.resetRespostaAPI();
    try {

      let retorno = await this.http.post(this.global.getUrlAPIMaser() + 'GetNotificacoesMotorista', {}, this.global.getHeadersAPIMaser());

      if ((retorno.retorno.cod_retorno == 'Z100') || (retorno.retorno.cod_retorno == 'Z101')) {
        this.notificacoes = retorno.dados;
        this.notifNaoLida = this.notificacoes.filter(n => { return n.lida == 'N' }).length;
        this.global.setDataRespostaAPI(true, retorno);
      } else {
        this.notificacoes = [];
        this.notifNaoLida = 0;
        this.global.setDataRespostaAPI(false, retorno);
      }

    } catch (error) {
      console.log('MotoristaService -> error API GetNotificacoesMotorista', error);
      this.global.setErroRespostaAPI(error);
    }

  }


  // API SetarNotifLidaMotorista
  // --------------------------------------------------
  async apiSetarNotifLidaMotorista(notif: any) {
    console.log('MotoristaService -> apiSetarNotifLidaMotorista', notif);

    let body = {
      notif_id: notif.notif_id
    }

    this.global.resetRespostaAPI();
    try {

      let retorno = await this.http.post(this.global.getUrlAPIMaser() + 'SetarNotifLidaMotorista', body, this.global.getHeadersAPIMaser());

      if (retorno.retorno.cod_retorno == 'Z100') {
        // Atualizamos o campo "lida" da notificação que está no array notificacoes.
        // Dessa forma não precisa consumir a API que busca as notificações para trazer com essa atualização.
        this.notificacoes.forEach(n => { if (n.notif_id == notif.notif_id) { n.lida = 'S'; n.dt_lida = Date.now() } });
        this.notifNaoLida = this.notificacoes.filter(n => { return n.lida == 'N' }).length;
        this.global.setDataRespostaAPI(true, retorno);
      } else {
        this.global.setDataRespostaAPI(false, retorno);
      }

    } catch (error) {
      console.log('MotoristaService -> error API SetarNotifLidaMotorista', error);
      this.global.setErroRespostaAPI(error);
    }

  }


  // API SetarPosGeoMotorista
  // --------------------------------------------------
  apiSetarPosGeoMotorista(geolocation: any) {
    console.log('MotoristaService -> apiSetarPosGeoMotorista', geolocation);

    // IMPORTANTE:
    // Não usar nesta API as funções global.resetRespostaAPI(), global.setDataRespostaAPI() e global.setErroRespostaAPI().
    // Ela é executada eventualmente conforme as mudanças da localização do motorista quando etiver usando o GPS e não 
    // deve entrar em conflito com as respostar de outras APIs.

    let body = {
      geo_lat: geolocation.latitude,
      geo_lng: geolocation.longitude
    }

    this.http.post(this.global.getUrlAPIMaser() + 'SetarPosGeoMotorista', body, this.global.getHeadersAPIMaser())
      .then(data => { console.log('MotoristaService -> retorno API SetarPosGeoMotorista', data) })
      .catch(err => { console.log('MotoristaService -> error API SetarPosGeoMotorista', err) });
  }


  // API GetTransferCodeVeiculo
  // --------------------------------------------------
  async apiGetTransferCodeVeiculo() {
    console.log('MotoristaService -> apiGetTransferCodeVeiculo');

    this.global.resetRespostaAPI();
    try {

      let retorno = await this.http.post(this.global.getUrlAPIMaser() + 'GetTransferCodeVeiculo', {}, this.global.getHeadersAPIMaser());

      if ((retorno.retorno.cod_retorno == 'Z100') || (retorno.retorno.cod_retorno == 'Z103')) {
        this.transferVeiculo = retorno.dados;
        this.global.setDataRespostaAPI(true, retorno);
      } else {
        this.global.setDataRespostaAPI(false, retorno);
      }

    } catch (error) {
      console.log('MotoristaService -> error API GetTransferCodeVeiculo', error);
      this.global.setErroRespostaAPI(error);
    }

  }


}
