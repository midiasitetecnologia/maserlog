import { Component } from '@angular/core';
import { Router } from '@angular/router';
import { Location } from '@angular/common';
import { Platform } from '@ionic/angular';
import { App } from '@capacitor/app';
import { StatusBar } from '@capacitor/status-bar';

import { AppVersion } from '@awesome-cordova-plugins/app-version/ngx';
import { Device } from '@awesome-cordova-plugins/device/ngx';

import { HttpService } from './services/http.service';
import { OnesignalService } from './services/onesignal.service';
import { GlobalService } from './services/global.service';
import { MotoristaService } from './services/motorista.service';
import { ColetaService } from './services/coleta.service';

import { Subscription } from 'rxjs';

@Component({
  selector: 'app-root',
  templateUrl: 'app.component.html',
  styleUrls: ['app.component.scss']
})
export class AppComponent {

  customBackActionSubscription: any = Subscription;

  constructor(
    private router: Router,
    private location: Location,
    private platform: Platform,
    private appVersion: AppVersion,
    private device: Device,
    private http: HttpService,
    private oneSignal: OnesignalService,
    private global: GlobalService,
    private motorista: MotoristaService,
    private coleta: ColetaService) {
    this.initializeApp();
  }

  initializeApp() {

    this.platform.ready().then(() => {

      StatusBar.setOverlaysWebView({
        overlay: false,
      });
      StatusBar.setBackgroundColor({
        color: '#f4f5f8', //color of the header
      });

      this.oneSignal.inicializar();
      this.registrarDispositivo();

      if (this.global.motoristaAutenticado() == true) {
        this.coleta.atualizarColetasPendentes = true;
        this.coleta.apiGetColetasPendentes();
        this.motorista.apiGetNotificacoesMotorista();
      }

      this.configurarBackButtonDispositivo();

    });

  }


  registrarDispositivo() {
    console.log('AppComponent -> registrarDispositivo');

    let dados = {
      apikey: '',
      id_disp: '',
      descricao: '',
      plataforma: '',
      versao_so: '',
      versao_app: '',
      push_token: ''
    };

    // Carrega os dados para registrar o dispositivo.
    dados.apikey = this.global.getApiKeyMaserRGSoft();
    dados.descricao = this.device.manufacturer + ' ' + this.device.model;
    dados.plataforma = this.device.platform;
    dados.versao_so = this.device.version;

    this.appVersion.getVersionNumber().then(versao => {
      console.log('AppComponent -> getVersionNumber', versao);
      dados.versao_app = versao;

      window["plugins"].OneSignal.getDeviceState(ds => {

        console.log('AppComponent -> getDeviceState()', ds);
        this.global.setIdDevice(ds.userId);

        // Carrega os dados de identificação do dispositivo.
        dados.id_disp = ds.userId;
        dados.push_token = ds.pushToken;

        this.apiRegistrarDispositivoUsuario(dados);

      }, errorGetDeviceState => { console.log('AppComponent -> error getDeviceState', errorGetDeviceState) });

    }).catch(error => {
      console.log('AppComponent -> error getVersionNumber', error);
    });

  }


  async apiRegistrarDispositivoUsuario(dados: any) {
    console.log('AppComponent -> apiRegistrarDispositivoUsuario', dados);

    this.global.resetRespostaAPI();
    try {

      let retorno = await this.http.post(this.global.getUrlAPIMaser() + 'RegistrarDispositivoUsuario', dados, this.global.getHeadersAPIMaser(false));

      if (retorno.retorno.cod_retorno == 'Z100') {
        this.global.setDataRespostaAPI(true, retorno);
      } else {
        this.global.setDataRespostaAPI(false, retorno);
      }

    } catch (error) {
      console.log('AppComponent -> error API RegistrarDispositivoUsuario', error);
      this.global.setErroRespostaAPI(error);
    }

  }

  configurarBackButtonDispositivo() {
    console.log('AppComponent -> configurarAcaoBackButtonDisp');

    // Código para fechar o app quando clicar no botão Back do dispositivo quando estiver na Home ou Login.
    this.customBackActionSubscription = this.platform.backButton.subscribe(() => {
      console.log('customBackActionSubscription', this.platform.url());

      if ((this.platform.url().indexOf('tabs/home') > 0) || (this.platform.url().indexOf('login') > 0)) {
        App.exitApp();
      }
      else if (this.platform.url().indexOf('tabs/') > 0) {
        this.router.navigate(['']);
      }
      else {
        this.location.back();
      }

    });

    // Esse código serve para desativar/desinscrever o método do botão Back do dispositivo. 
    // Manter esse código comentado apenas para deixar registrado como deve ser feito quando for preciso.
    //if (this.customBackActionSubscription) {
    //  this.customBackActionSubscription.unsubscribe();
    //}
  }

}
