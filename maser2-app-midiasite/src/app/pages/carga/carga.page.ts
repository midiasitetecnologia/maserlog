import { Component } from '@angular/core';
import { Router } from '@angular/router';
import { AlertController } from '@ionic/angular';

import { ProcsService } from '../../services/procs.service';
import { GlobalService } from '../../services/global.service';
import { MotoristaService } from '../../services/motorista.service';
import { ColetaService } from '../../services/coleta.service';

@Component({
  selector: 'app-carga',
  templateUrl: 'carga.page.html',
  styleUrls: ['carga.page.scss']
})
export class CargaPage {

  public carregando: boolean = false;

  constructor(
    private router: Router,
    private alertController: AlertController,
    public procs: ProcsService,
    public global: GlobalService,
    public motorista: MotoristaService,
    public coleta: ColetaService) {
    console.log('CargaPage -> constructor');
  }

  ngOnInit() {
    console.log('CargaPage -> ngOnInit');
  }

  ionViewWillEnter() {
    console.log('CargaPage -> ionViewWillEnter');
  }

  ionViewDidEnter() {
    console.log('CargaPage -> ionViewDidEnter');
  }

  ionViewWillLeave() {
    console.log('CargaPage -> ionViewWillLeave');
  }

  ionViewDidLeave() {
    console.log('CargaPage -> ionViewDidLeave');
  }

  ngOnDestroy() {
    console.log('CargaPage -> ngOnDestroy');
  }

  async exibirCodigoTransferVeiculo() {
    console.log('CargaPage -> exibirCodigoTransferVeiculo');

    await this.procs.iniciarLoading();
    await this.motorista.apiGetTransferCodeVeiculo();
    await this.procs.finalizarLoading();

    if (this.global.getRespostaAPI().retorno == true) {
      this.exibirMensagemTransfer()
    }
    else {
      this.global.exibirRespostaAPI();
    }

  }

  async exibirMensagemTransfer() {
    console.log('CargaPage -> exibirMensagemTransfer');

    const alert = await this.alertController.create({
      header: `Código baldeação: ${this.motorista.transferVeiculo.transfer_code}`,
      subHeader: 'Validade: ' + this.procs.getDataHoraFormat(this.motorista.transferVeiculo.dt_transfer_code),
      message: 'Informe o <strong>código</strong> acima para fazer baldeação para este veículo.',
      buttons: ['OK']
    });
    await alert.present();
  }

  async abrirColeta(coleta: any) {
    console.log('CargaPage -> abrirColeta', coleta);

    await this.procs.iniciarLoading();
    await this.coleta.apiGetDadosColeta(coleta.coleta_id);
    await this.procs.finalizarLoading();

    if (this.global.getRespostaAPI().retorno == true) {
      this.router.navigate(['/coleta']);
    } else {
      this.global.exibirRespostaAPI();
    }
  }

  async atualizarCarga(refresher: any) {
    console.log('CargaPage -> atualizarHome');

    this.carregando = true;
    try {
      await this.coleta.apiGetColetasPendentes();
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


  atualizarVeiculo() {
    console.log('CargaPage -> atualizarVeiculo');
    this.router.navigate(['/veiculo-ocupacao']);
  }


}
