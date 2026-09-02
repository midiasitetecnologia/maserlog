import { Component, OnInit } from '@angular/core';
import { Location } from '@angular/common';
import { Router } from '@angular/router';
import { AlertController } from '@ionic/angular';

import { ProcsService } from '../../services/procs.service';
import { GlobalService } from '../../services/global.service';
import { MotoristaService } from '../../services/motorista.service';
import { ColetaService } from '../../services/coleta.service';

@Component({
  selector: 'app-veiculo-proximos',
  templateUrl: './veiculo-proximos.page.html',
  styleUrls: ['./veiculo-proximos.page.scss'],
})
export class VeiculoProximosPage implements OnInit {

  constructor(
    public location: Location,
    public router: Router,
    public alertController: AlertController,
    public procs: ProcsService,
    public global: GlobalService,
    public motorista: MotoristaService,
    public coleta: ColetaService) {
    console.log('VeiculoProximosPage -> constructor');
  }

  ngOnInit() {
    console.log('VeiculoProximosPage -> ngOnInit');

    // Inicializamos os dados de baldeação da coleta
    this.coleta.dadosColetaAtual.placa_destino = '';
    this.coleta.dadosColetaAtual.transfer_code = '';
  }

  async informarCodigoBaldeacao(placa_destino: string) {
    console.log('VeiculoProximosPage -> informarCodigoBaldeacao');

    const alert = await this.alertController.create({
      header: `Baldear coleta ${this.coleta.getIdSolicitacao(this.coleta.dadosColetaAtual)}`,
      subHeader: this.coleta.dadosColetaAtual.nome_cliente,
      message: `Informe o código de baldeação do veículo: <strong>${placa_destino}</strong>`,
      inputs: [
        {
          name: 'transfer_code',
          type: 'number',
          placeholder: '* * * *'
        }
      ],
      buttons: [
        {
          text: 'Cancelar',
          handler: () => {
            console.log('informarCodigoBaldeacao?', 'Cancelar');
          }
        }, {
          text: 'Confirmar',
          handler: (data) => {
            console.log('informarCodigoBaldeacao?', data);
            this.coleta.dadosColetaAtual.placa_destino = placa_destino;
            this.coleta.dadosColetaAtual.transfer_code = data.transfer_code;
            this.baldearColeta();
          }
        }
      ]
    });
    await alert.present();
  }


  async baldearColeta() {
    console.log('VeiculoProximosPage -> baldearColeta');

    await this.procs.iniciarLoading();
    await this.coleta.apiBaldearColeta(this.coleta.dadosColetaAtual);
    await this.procs.finalizarLoading();

    if (this.global.getRespostaAPI().retorno == true) {
      await this.coleta.carregarColetasPendentes(true);
      this.router.navigate(['tabs/carga']);
      this.procs.exibirToast('', 'Baldeação realizada com sucesso!', 'verde-web');
    } else {
      this.global.exibirRespostaAPI();
    }

  }

}
