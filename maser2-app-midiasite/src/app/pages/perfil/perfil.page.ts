import { Component } from '@angular/core';
import { Router } from '@angular/router';
import { AlertController } from '@ionic/angular';

import { AppVersion } from '@awesome-cordova-plugins/app-version/ngx';

import { ProcsService } from '../../services/procs.service';
import { GlobalService } from '../../services/global.service';
import { MotoristaService } from '../../services/motorista.service';
import { ColetaService } from '../../services/coleta.service';

@Component({
  selector: 'app-perfil',
  templateUrl: 'perfil.page.html',
  styleUrls: ['perfil.page.scss']
})
export class PerfilPage {

  constructor(
    private router: Router,
    private alertController: AlertController,
    private appVersion: AppVersion,
    public procs: ProcsService,
    public global: GlobalService,
    public motorista: MotoristaService,
    public coleta: ColetaService) {
    console.log('PerfilPage -> constructor');
  }

  async fazerLogout() {
    console.log('PerfilPage -> fazerLogout');

    const confirm = await this.alertController.create({
      header: this.motorista.getNome(),
      subHeader: 'Deseja sair da sua conta?',
      message: 'Você deverá fazer login na próxima vez que entrar no aplicativo.',
      buttons: [
        {
          text: 'Cancelar',
          handler: () => {
            console.log('Deseja fazer logout?', 'Cancelar');
          }
        },
        {
          text: 'Sair',
          handler: () => {
            console.log('Deseja fazer logout?', 'Sair');
            this.confirmarLogout();
          }
        }
      ]
    });
    await confirm.present();
  }

  async confirmarLogout() {
    console.log('PerfilPage -> confirmarLogout');

    await this.procs.iniciarLoading();
    await this.motorista.apiSetarLogoutMotorista();
    await this.procs.finalizarLoading();

    if (this.global.getRespostaAPI().retorno == true) {
      this.coleta.resetColetasPendentes();
      this.router.navigate(['login']);

    } 
    else {
      this.global.exibirRespostaAPI();
    }
  }


  async exibirSobre() {
    console.log('PerfilPage -> exibirSobre');    

    let versao:string = '?';

    try {
      versao = await this.appVersion.getVersionNumber();
    } 
    catch (error) {
      console.log('Error exibirSobre', error);
    }

    const alert = await this.alertController.create({
      header: 'Aplicativo MASER',
      message: `Versão<br><strong>${versao}</strong><br><br>Desenvolvido por<br><strong>RGSOFT</strong>`,
      buttons: ['OK']
    });
    await alert.present();

  }


}
