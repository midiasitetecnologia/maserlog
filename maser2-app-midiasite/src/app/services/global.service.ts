import { Injectable } from '@angular/core';
import { Router } from '@angular/router';
import { ProcsService } from '../services/procs.service';
import { Camera, CameraResultType, CameraSource, ImageOptions } from '@capacitor/camera';

let key_modo_developer = "modo_developer";
let key_id_device = "id_device";
let key_api_token = "api_token";

@Injectable({
  providedIn: 'root'
})
export class GlobalService {

  // URL padrão das APIs Maser
  // --------------------------------------------------
  private URL_PADRAO_API_MASER_PRODUCTION = 'https://app.masertransportes.com.br/api/';
  private URL_PADRAO_API_MASER_DEVELOPER = 'https://dev.masertransportes.com.br/api/';

  private respostaAPI: any = {
    retorno: false,
    data: '',
    falhou: false,
    erro: ''
  };

  // Variável usada para visualizar uma foto inteira na page/foto
  public fotoAmpliada: string = '';

  constructor(
    private router: Router,
    private procs: ProcsService) {
    console.log('GlobalService -> constructor');
  }

  // Retorno da URL padrão das APIs Maser
  // --------------------------------------------------
  getUrlAPIMaser(): string {
    let url = this.URL_PADRAO_API_MASER_PRODUCTION;

    if (this.getModoDeveloper() == 'S') {
      url = this.URL_PADRAO_API_MASER_DEVELOPER;
    }

    //console.log('GlobalService -> getUrlAPIMaser', url);
    return url;
  }


  // Retorno do Headers padrão para usar nas APIs Maser
  // --------------------------------------------------
  getHeadersAPIMaser(withAuthorization: boolean = true): any {
    if (withAuthorization) {
      return {
        'Authorization': `Bearer ${this.getAPIToken()}`,
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      }
    } 
    else {
      return {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      }
    }
  }


  // API Token
  // --------------------------------------------------
  getAPIToken(): string {
    return this.procs.tratarStrNull(localStorage.getItem(key_api_token));
  }

  setAPIToken(api_token: string) {
    localStorage.setItem(key_api_token, api_token);
  }


  // Motorista autenticado
  // --------------------------------------------------
  motoristaAutenticado(): boolean {
    return (this.getAPIToken() != '');
  }


  // Resposta API
  // --------------------------------------------------
  getRespostaAPI(): any {
    return this.respostaAPI;
  }

  resetRespostaAPI() {
    console.log('GlobalService -> resetRespostaAPI');
    this.respostaAPI.retorno = false;
    this.respostaAPI.data = '';
    this.respostaAPI.falhou = false;
    this.respostaAPI.erro = '';
  }

  async setDataRespostaAPI(retorno: boolean, data: any) {
    console.log('GlobalService -> setDataRespostaAPI', retorno, data);
    this.respostaAPI.retorno = retorno;
    this.respostaAPI.data = data;
  }

  async setErroRespostaAPI(erro: any) {
    console.log('GlobalService -> setErroRespostaAPI', erro);

    this.respostaAPI.falhou = true;
    this.respostaAPI.erro = erro;

    // Tratamento especial para o código 401 (Unauthorized)
    if (this.respostaAPI.erro.status == '401') {
      this.setAPIToken('');
      this.router.navigate(['login']);
      await this.procs.exibirToastOk('', 'Sua conta foi desconectada nesse dispositivo. Faça login novamente.');
    }
  }

  exibirRespostaAPI() {
    console.log('GlobalService -> exibirRespostaAPI', this.respostaAPI);

    if (this.respostaAPI.falhou == false) {
      this.procs.exibirMensagem('Ops!', this.respostaAPI.data.retorno.msg_retorno);
    }
    else {

      // Se o erro for um Object, corresponde ao erro gerado pelo HTTP.
      // Neste caso teremos Object {error.status, error.error, error.headers}.
      if (this.respostaAPI.erro.status != null) {

        // Valores positivos são códigos de status HTTP. 
        // Valores negativos representam códigos de erro interno.
        if (this.respostaAPI.erro.status < 0) {
          if (this.respostaAPI.erro.status == -3) {
            this.procs.exibirToastOk('', 'Sem conexão com a internet. Verifique sua conexão e tente novamente.');
          } else if (this.respostaAPI.erro.status == -4) {
            this.procs.exibirToastOk('', 'Sem conexão ou conexão lenta com a internet. Verifique e tente novamente.');
          } else {
            this.procs.exibirMensagem('Ops!', 'Não foi possível acessar os serviços da plataforma. Tente novamente.');
          }
        }
        else {
          // Não exibimos a mensagem de erro para o código 401, pois existe um tratamento especial na função setErroRespostaAPI.
          if (this.respostaAPI.erro.status != '401') {
            this.procs.exibirMensagem('Ops!', 'Não foi possível acessar os serviços da plataforma. ' +
              this.respostaAPI.erro.error + ' (' + this.respostaAPI.erro.status + ')');
          }
        }

      }
      else if (this.respostaAPI.erro != '') {
        this.procs.exibirMensagem('Ops!', 'Não foi possível acessar os serviços da plataforma. ' + this.respostaAPI.erro);
      }
      else {
        this.procs.exibirMensagem('Ops!', 'Não foi possível acessar os serviços da plataforma. Tente novamente mais tarde.');
      }

    }
  }


  // Modo Developer
  // --------------------------------------------------
  getModoDeveloper(): string {
    //console.log('GlobalService -> getModoDeveloper');
    let retorno = localStorage.getItem(key_modo_developer);
    return (retorno != null) ? retorno : '';
  }

  setModoDeveloper(modo_developer: string) {
    //console.log('GlobalService -> setModoDeveloper', modo_developer);
    localStorage.setItem(key_modo_developer, modo_developer);
  }


  // Id Device
  // --------------------------------------------------
  getIdDevice(): string {
    //console.log('GlobalService -> getIdDevice');
    let retorno = localStorage.getItem(key_id_device);
    return (retorno != null) ? retorno : '';
  }

  setIdDevice(id_device: string) {
    //console.log('GlobalService -> setIdDevice', id_device);
    localStorage.setItem(key_id_device, id_device);
  }


  // Id do App Maser no OneSignal
  // --------------------------------------------------
  getAppIdOneSignal(): string {
    // Conta OneSignal Maser
    return '8be0d33b-95c9-4234-8557-8d57f39fc6e5';
  }


  // GoogleProjectNumber da Maser no Firebase para uso no OneSignal
  // --------------------------------------------------
  getGoogleProjectNumber(): string {
    // Conta Firebase Maser
    //return '581589546562'; //Código alterado em 16/09/20
    return '910592746813';
  }


  // Id do App Maser para uso nas APIs Maser
  // --------------------------------------------------
  getIdAppMaser(): string {
    return '1';
  }


  // Chave da RGSoft para uso nas APIs Maser
  // --------------------------------------------------
  getApiKeyMaserRGSoft(): string {
    //return this.procs.rgCryptMaser('*app@maser#');
    return 'a13d02efebd5d09d31ae4f5f15fab111b65487ac3746d4350e3350135c6b3457';
  }


  visualizarFoto(foto: string) {
    console.log('GlobalService -> visualizarFoto', foto);

    if (foto > '') {
      this.fotoAmpliada = foto;
    } else {
      this.fotoAmpliada = '../../assets/sem-foto.jpg';
    }

    this.router.navigate(['/foto']);
  }


  async getFoto(options: ImageOptions) {
    console.log('GlobalService -> getFoto');
    const image = await Camera.getPhoto(options);
    return image.base64String;
  };


  // Opções da camera para tirar foto ou buscar da galeria.
  // --------------------------------------------------
  getCameraOptions(): ImageOptions {
    return {
      quality: 80,
      //allowEditing: false;
      resultType: CameraResultType.Base64,
      //saveToGallery: false,
      width: 1280, // Largura em pixels para dimensionar a imagem (resolução HD).
      height: 1280, // Altura em pixels para dimensionar a imagem (resolução HD).
      //correctOrientation: true,
      source: CameraSource.Camera
    }
  }

  getGaleriaOptions(): ImageOptions {
    return {
      quality: 80,
      //allowEditing: false;
      resultType: CameraResultType.Base64,
      //saveToGallery: false,
      width: 1280, // Largura em pixels para dimensionar a imagem (resolução HD).
      height: 1280, // Altura em pixels para dimensionar a imagem (resolução HD).
      //correctOrientation: true,
      source: CameraSource.Photos
    }
  }


}
