import { Component, OnInit } from '@angular/core';
import { AlertController } from '@ionic/angular';
import { ProcsService } from '../../services/procs.service';
import { GlobalService } from '../../services/global.service';
import { MotoristaService } from '../../services/motorista.service';

@Component({
  selector: 'app-notificacoes',
  templateUrl: './notificacoes.page.html',
  styleUrls: ['./notificacoes.page.scss'],
})
export class NotificacoesPage implements OnInit {

  public carregando: boolean = false;

  constructor(
    private alertController: AlertController,
    public procs: ProcsService,
    public global: GlobalService,
    public motorista: MotoristaService) {
    console.log('NotificacoesPage -> constructor');
  }

  ngOnInit() {
    console.log('NotificacoesPage -> ngOnInit');
    //this.atualizarNotificacoes();
  }

  async atualizarNotificacoes(refresher: any = '') {
    console.log('NotificacoesPage -> atualizarNotificacoes');

    this.carregando = true;
    try {
      await this.motorista.apiGetNotificacoesMotorista();
    }
    finally {
      this.carregando = false;
      // Código para desligar o ion-refresher
      if (refresher) { refresher.target.complete(); }
    }

    if (this.global.getRespostaAPI().retorno == false) {
      this.global.exibirRespostaAPI();
    }
  }

  async exibirNotificacao(notif: any) {
    console.log('NotificacoesPage -> exibirNotificacao', notif);

    if (notif.lida == 'N') {
      this.motorista.apiSetarNotifLidaMotorista(notif);
    }    
    
    const alert = await this.alertController.create({
      header: notif.titulo,
      message: notif.texto,
      buttons: ['OK']
    });
    await alert.present();
  }

}
