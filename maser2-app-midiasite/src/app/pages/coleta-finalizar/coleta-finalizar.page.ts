import { Component, OnInit } from '@angular/core';
import { Router } from '@angular/router';
import { Location } from '@angular/common';
import { AlertController } from '@ionic/angular';

import { ProcsService } from '../../services/procs.service';
import { GlobalService } from '../../services/global.service';
import { MotoristaService } from '../../services/motorista.service';
import { ColetaService } from '../../services/coleta.service';

import * as moment from 'moment';
import 'moment/locale/pt-br';

@Component({
  selector: 'app-coleta-finalizar',
  templateUrl: './coleta-finalizar.page.html',
  styleUrls: ['./coleta-finalizar.page.scss'],
})
export class ColetaFinalizarPage implements OnInit {

  constructor(
    private router: Router,
    public location: Location,
    public alertController: AlertController,
    public procs: ProcsService,
    public global: GlobalService,
    public motorista: MotoristaService,
    public coleta: ColetaService) {
    console.log('ColetaFinalizarPage -> constructor');
  }

  ngOnInit() {
    console.log('ColetaFinalizarPage -> ngOnInit', this.coleta.dadosColetaAtual);

    // Inicializa campos.
    this.coleta.dadosColetaAtual.realizada = '';
    this.coleta.dadosColetaAtual.nfs_comerciais = '';
    this.coleta.dadosColetaAtual.mot_nao_entrega = '';
    this.coleta.dadosColetaAtual.obs_nao_entrega = '';

    if (this.coleta.dadosColetaAtual.ocup_veiculo == null) {
      this.coleta.dadosColetaAtual.ocup_veiculo = this.motorista.getOcupVeiculo();
    }

    if (this.coleta.dadosColetaAtual.img_base64 == null) {
      this.coleta.dadosColetaAtual.img_base64 = '';
    }

    if (this.coleta.dadosColetaAtual.img_rom_base64 == null) {
      this.coleta.dadosColetaAtual.img_rom_base64 = '';
    }

    // Inicializa a previsão da duração da coleta
    if (this.coleta.dadosColetaAtual.status == 'C2') {
      this.coleta.dadosColetaAtual.dur_prev_coleta = '00:15:00'
    }

    // Inicializa a coleta como realizada "S" caso tenha notas informadas
    if ((this.coleta.dadosColetaAtual.status == 'C4') && (this.coleta.notasFiscais.length > 0)) {
      this.coleta.dadosColetaAtual.realizada = 'S'
    }

    // Inicializa a previsão da duração da entrega
    if (this.coleta.dadosColetaAtual.status == 'E2') {
      this.coleta.dadosColetaAtual.dur_prev_entrega = '00:15:00'
    }

    // Inicializa a entrega como realizada "S" caso seja uma COMANDA de contrato.
    if ((this.coleta.dadosColetaAtual.status == 'E4') && (this.coleta.getEhComanda(this.coleta.dadosColetaAtual) == true)) {
      this.coleta.dadosColetaAtual.realizada = 'S'
    }
  }


  async pegarFotoGaleria(eh_romaneio: boolean = false) {
    console.log('ColetaFinalizarPage -> pegarFotoGaleria');
    if (eh_romaneio) {
      this.coleta.dadosColetaAtual.img_rom_base64 = await this.global.getFoto(this.global.getGaleriaOptions());
    } else {
      this.coleta.dadosColetaAtual.img_base64 = await this.global.getFoto(this.global.getGaleriaOptions());
    }
  }


  async tirarFoto(eh_romaneio: boolean = false) {
    console.log('ColetaFinalizarPage -> tirarFoto');
    if (eh_romaneio) {
      this.coleta.dadosColetaAtual.img_rom_base64 = await this.global.getFoto(this.global.getCameraOptions());
    } else {
      this.coleta.dadosColetaAtual.img_base64 = await this.global.getFoto(this.global.getCameraOptions());
    }
  }


  // COLETA - Funções
  // --------------------------------------------------
  async setarChegadaColeta() {
    console.log('ColetaFinalizarPage -> setarChegadaColeta');

    await this.procs.iniciarLoading();
    await this.coleta.apiSetarChegadaColeta(this.coleta.dadosColetaAtual);
    await this.procs.finalizarLoading();

    if (this.global.getRespostaAPI().retorno == true) {
      await this.coleta.carregarColetasPendentes();
      this.router.navigate(['']);
    } else {
      this.global.exibirRespostaAPI();
    }

  }

  async setarInicioAtendColeta() {
    console.log('ColetaFinalizarPage -> setarInicioAtendColeta');

    await this.procs.iniciarLoading();
    await this.coleta.apiSetarInicioAtendColeta(this.coleta.dadosColetaAtual);
    await this.procs.finalizarLoading();

    if (this.global.getRespostaAPI().retorno == true) {
      await this.coleta.carregarColetasPendentes();
      this.router.navigate(['']);
    } else {
      this.global.exibirRespostaAPI();
    }

  }

  async devolverSemAtendColeta() {
    console.log('ColetaFinalizarPage -> devolverSemAtendColeta');

    await this.procs.iniciarLoading();
    await this.coleta.apiDevolverSemAtendColeta(this.coleta.dadosColetaAtual);
    await this.procs.finalizarLoading();

    if (this.global.getRespostaAPI().retorno == true) {
      await this.coleta.carregarColetasPendentes(true);
      this.router.navigate(['']);
    } else {
      this.global.exibirRespostaAPI();
    }

  }


  podeFinalizarColeta(): boolean {

    let retorno = true;

    if (this.coleta.dadosColetaAtual.realizada == 'S') {
      if ((this.coleta.notasFiscais.length == 0) && (this.coleta.dadosColetaAtual.semNF != true)) {
        retorno = false;
      }
    }

    return retorno;
  }

  async finalizarColeta() {
    console.log('ColetaFinalizarPage -> finalizarColeta');

    await this.procs.iniciarLoading();
    await this.coleta.apiFinalizarColeta(this.coleta.dadosColetaAtual);
    await this.procs.finalizarLoading();

    if (this.global.getRespostaAPI().retorno == true) {
      await this.coleta.carregarColetasPendentes();
      this.router.navigate(['']);
    } else {
      this.global.exibirRespostaAPI();
    }
  }


  async cancelarColetaSemDesloc() {
    console.log('ColetaFinalizarPage -> cancelarColetaSemDesloc');

    await this.procs.iniciarLoading();
    await this.coleta.apiCancelarColetaSemDesloc(this.coleta.dadosColetaAtual);
    await this.procs.finalizarLoading();

    if (this.global.getRespostaAPI().retorno == true) {
      await this.coleta.carregarColetasPendentes(true);
      this.router.navigate(['']);
    } else {
      this.global.exibirRespostaAPI();
    }
  }


  // ENTREGA - Funções
  // --------------------------------------------------
  async setarChegadaEntrega() {
    console.log('ColetaPage -> setarChegadaEntrega');

    await this.procs.iniciarLoading();
    await this.coleta.apiSetarChegadaEntrega(this.coleta.dadosColetaAtual);
    await this.procs.finalizarLoading();

    if (this.global.getRespostaAPI().retorno == true) {
      await this.coleta.carregarColetasPendentes();
      this.router.navigate(['']);
    } else {
      this.global.exibirRespostaAPI();
    }

  }

  async setarInicioAtendEntrega() {
    console.log('ColetaFinalizarPage -> setarInicioAtendEntrega');

    await this.procs.iniciarLoading();
    await this.coleta.apiSetarInicioAtendEntrega(this.coleta.dadosColetaAtual);
    await this.procs.finalizarLoading();

    if (this.global.getRespostaAPI().retorno == true) {
      await this.coleta.carregarColetasPendentes();
      this.router.navigate(['']);
    } else {
      this.global.exibirRespostaAPI();
    }

  }

  async abrirNotasFiscais() {
    console.log('ColetaFinalizarPage -> abrirNotasFiscais');

    await this.procs.iniciarLoading();
    await this.coleta.apiGetNotasFiscaisColeta(this.coleta.dadosColetaAtual);
    await this.procs.finalizarLoading();

    if (this.global.getRespostaAPI().retorno == true) {
      this.router.navigate(['/coleta-notas-fiscais']);
    } else {
      this.global.exibirRespostaAPI();
    }
  }

  podeFinalizarEntrega(): boolean {

    let retorno = true;

    if (this.procs.tratarStrNull(this.coleta.dadosColetaAtual.realizada) == '') {
      retorno = false;
    }

    return retorno;
  }

  async finalizarEntrega() {
    console.log('ColetaFinalizarPage -> finalizarEntrega');

    await this.procs.iniciarLoading();
    await this.coleta.apiFinalizarEntrega(this.coleta.dadosColetaAtual);
    await this.procs.finalizarLoading();

    if (this.global.getRespostaAPI().retorno == true) {
      await this.coleta.carregarColetasPendentes(true);
      this.router.navigate(['']);
    } else {
      this.global.exibirRespostaAPI();
    }
  }


  getSaidaPrevista(duracao: any): string {

    let dhprevista = moment(Date.now());

    if ((duracao != null) && (duracao > '')) {
      // Formato hh:mm:ss
      // Adiciona as horas
      dhprevista.add(parseInt(duracao.substr(0, 2)), 'hours');
      // Adiciona os minutos
      dhprevista.add(parseInt(duracao.substr(3, 2)), 'minutes');
    }

    return dhprevista.format('HH:mm');
  }


  async duracaoPrevColeta() {

    const alert = await this.alertController.create({
      //mode: 'ios',
      header: 'Duração prevista',
      inputs: [
        {
          type: 'radio',
          label: '15 min',
          value: '00:15:00',
          checked: (this.coleta.dadosColetaAtual.dur_prev_coleta == '00:15:00')
        },
        {
          type: 'radio',
          label: '30 min',
          value: '00:30:00',
          checked: (this.coleta.dadosColetaAtual.dur_prev_coleta == '00:30:00')
        },
        {
          type: 'radio',
          label: '45 min',
          value: '00:45:00',
          checked: (this.coleta.dadosColetaAtual.dur_prev_coleta == '00:45:00')
        },
        {
          type: 'radio',
          label: '1 hora',
          value: '01:00:00',
          checked: (this.coleta.dadosColetaAtual.dur_prev_coleta == '01:00:00')
        },
        {
          type: 'radio',
          label: '1h 15min',
          value: '01:15:00',
          checked: (this.coleta.dadosColetaAtual.dur_prev_coleta == '01:15:00')
        },
        {
          type: 'radio',
          label: '1h 30min',
          value: '01:30:00',
          checked: (this.coleta.dadosColetaAtual.dur_prev_coleta == '01:30:00')
        },
        {
          type: 'radio',
          label: '1h 45min',
          value: '01:45:00',
          checked: (this.coleta.dadosColetaAtual.dur_prev_coleta == '01:45:00')
        },
        {
          type: 'radio',
          label: '2 horas',
          value: '02:00:00',
          checked: (this.coleta.dadosColetaAtual.dur_prev_coleta == '02:00:00')
        }
      ],
      buttons: [
        {
          text: 'Cancelar',
          role: 'cancel',
          cssClass: 'secondary',
          handler: () => {
            console.log('duracaoPrevColeta', 'Cancel');
          }
        }, {
          text: 'Ok',
          handler: (data) => {
            console.log('duracaoPrevColeta', data);
            this.coleta.dadosColetaAtual.dur_prev_coleta = data;
          }
        }
      ]
    });

    await alert.present();
  }


  async duracaoPrevEntrega() {

    const alert = await this.alertController.create({
      //mode: 'ios',
      header: 'Duração prevista',
      inputs: [
        {
          type: 'radio',
          label: '15 min',
          value: '00:15:00',
          checked: (this.coleta.dadosColetaAtual.dur_prev_entrega == '00:15:00')
        },
        {
          type: 'radio',
          label: '30 min',
          value: '00:30:00',
          checked: (this.coleta.dadosColetaAtual.dur_prev_entrega == '00:30:00')
        },
        {
          type: 'radio',
          label: '45 min',
          value: '00:45:00',
          checked: (this.coleta.dadosColetaAtual.dur_prev_entrega == '00:45:00')
        },
        {
          type: 'radio',
          label: '1 hora',
          value: '01:00:00',
          checked: (this.coleta.dadosColetaAtual.dur_prev_entrega == '01:00:00')
        },
        {
          type: 'radio',
          label: '1h 15min',
          value: '01:15:00',
          checked: (this.coleta.dadosColetaAtual.dur_prev_entrega == '01:15:00')
        },
        {
          type: 'radio',
          label: '1h 30min',
          value: '01:30:00',
          checked: (this.coleta.dadosColetaAtual.dur_prev_entrega == '01:30:00')
        },
        {
          type: 'radio',
          label: '1h 45min',
          value: '01:45:00',
          checked: (this.coleta.dadosColetaAtual.dur_prev_entrega == '01:45:00')
        },
        {
          type: 'radio',
          label: '2 horas',
          value: '02:00:00',
          checked: (this.coleta.dadosColetaAtual.dur_prev_entrega == '02:00:00')
        }
      ],
      buttons: [
        {
          text: 'Cancelar',
          role: 'cancel',
          cssClass: 'secondary',
          handler: () => {
            console.log('duracaoPrevEntrega', 'Cancel');
          }
        }, {
          text: 'Ok',
          handler: (data) => {
            console.log('duracaoPrevEntrega', data);
            this.coleta.dadosColetaAtual.dur_prev_entrega = data;
          }
        }
      ]
    });

    await alert.present();
  }


  setNFSComerciais(ev: any) {
    this.coleta.dadosColetaAtual.nfs_comerciais = ev.detail.value;
  }


  setRealizada(ev: any) {
    this.coleta.dadosColetaAtual.realizada = ev.detail.value;
  }


  setCodTipoVeiculoNec(ev: any) {
    this.coleta.dadosColetaAtual.cod_tipo_veiculo_nec = ev.detail.value;
  }


  setMotNaoEntrega(ev: any) {
    this.coleta.dadosColetaAtual.mot_nao_entrega = ev.detail.value;
  }

}
