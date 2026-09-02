import { Injectable } from '@angular/core';
import { AlertController, LoadingController, ToastController } from '@ionic/angular';

import * as moment from 'moment';
import 'moment/locale/pt-br';

@Injectable({
  providedIn: 'root'
})
export class ProcsService {

  private loading: any;

  constructor(
    private alertController: AlertController,
    private loadingController: LoadingController,
    private toastController: ToastController) {
    console.log('ProcsService -> constructor');
  }


  // Criptografia especial
  // --------------------------------------------------
  rgCryptMaser(str: string): string {
    // Reverte a string.
    let strRetorno = str.split('').reverse().join('');
    // Codifica para base64.
    strRetorno = btoa(strRetorno);
    return strRetorno;
  }


  // Descriptografia especial para reverter a rgCryptMaser
  // --------------------------------------------------
  rgDecryptMaser(str: string): string {
    // Decodifica para base64.
    let strRetorno = atob(str);
    // Reverte a string.
    strRetorno = strRetorno.split('').reverse().join('');
    return strRetorno;
  }


  // Exibir Mensagem
  // --------------------------------------------------
  async exibirMensagem(titulo: string, mensagem: string) {

    const alert = await this.alertController.create({
      header: titulo,
      message: mensagem,
      buttons: ['OK']
    });
    await alert.present();
  }


  // Exibir Toast
  // --------------------------------------------------
  async exibirToast(titulo: string, mensagem: string, color: string = 'azul-logo', duration: number = 2000) {
    const toast = await this.toastController.create({
      header: titulo,
      message: mensagem,
      color,
      duration
    });
    toast.present();
  }


  // Exibir Toast com botão OK
  // --------------------------------------------------
  async exibirToastOk(titulo: string, mensagem: string, color: string = 'azul-logo') {
    const toast = await this.toastController.create({
      header: titulo,
      message: mensagem,
      color,
      buttons: [
        {
          text: 'OK',
          role: 'cancel',
          handler: () => {/* não faz nada. Apenas fecha o toast quando clicar no OK. */ }
        }
      ]
    });
    toast.present();
  }


  // Apaga todo conteúdo de LocalStorage
  // --------------------------------------------------
  limparLocalStorage() {
    console.log('ProcsService -> limparLocalStorage - antes: ' + localStorage.length);
    localStorage.clear();
    console.log('ProcsService -> limparLocalStorage - depois: ' + localStorage.length);
  }


  // Tratamento especial para retornar 'vazio' quando uma string for null ou undefined
  // --------------------------------------------------
  tratarStrNull(valor: any, valor_default: string = ''): string {
    if ((valor == null) || (valor == 'null') || (valor == undefined)) {
      return valor_default;
    } else {
      return valor;
    }
  }


  // Retorna a data e hora formatados.
  // Se a data for igual oo dia atual retorna apenas a hora.
  // --------------------------------------------------
  getDataHoraFormat(data: any, hora: any = ''): string {

    let dh: any

    if (hora != '') {
      dh = moment(data + ' ' + hora);
    } else {
      dh = moment(data);
    }

    if (dh.format('YYYY-MM-DD') == moment().format('YYYY-MM-DD')) {
      return dh.format('HH:mm');
    } else {
      return dh.format('DD MMM HH:mm').toLowerCase();
    }
  }


  // Retorna a hora atual.
  // --------------------------------------------------
  getHoraAtual(): string {
    return moment(Date.now()).format('HH:mm');
  }


  // Retorna a data e a hora atual.
  // --------------------------------------------------
  getDataHoraAtual(formato: string = 'DD MMM HH:mm'): string {
    return moment(Date.now()).format(formato).toLowerCase();
  }


  // Loading
  // --------------------------------------------------
  async iniciarLoading(mensagem: string = 'Aguarde...') {
    this.loading = await this.loadingController.create({
      message: mensagem
    });
    await this.loading.present();
  }

  async finalizarLoading() {
    await this.loading.dismiss();
  }

}
