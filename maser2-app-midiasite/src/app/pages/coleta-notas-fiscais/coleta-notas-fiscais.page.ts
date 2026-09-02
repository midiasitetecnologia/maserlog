import { Component, OnInit } from '@angular/core';
import { Router } from '@angular/router';
import { Location } from '@angular/common';
import { AlertController } from '@ionic/angular';

import { ProcsService } from '../../services/procs.service';
import { GlobalService } from '../../services/global.service';
import { ColetaService } from '../../services/coleta.service';
import { MotoristaService } from '../../services/motorista.service';

@Component({
  selector: 'app-coleta-notas-fiscais',
  templateUrl: './coleta-notas-fiscais.page.html',
  styleUrls: ['./coleta-notas-fiscais.page.scss'],
})
export class ColetaNotasFiscaisPage implements OnInit {

  public carregando: boolean = false;

  constructor(
    private router: Router,
    public location: Location,
    private alertController: AlertController,
    public procs: ProcsService,
    public global: GlobalService,
    public coleta: ColetaService,
    public motorista: MotoristaService) {
    console.log('ColetaNotasFiscaisPage -> constructor');
  }

  ngOnInit() {
    console.log('ColetaNotasFiscaisPage -> ngOnInit');
  }


  async atualizarNotas() {
    console.log('ColetaNotasFiscaisPage -> atualizarNotas');

    this.carregando = true;
    try {
      await this.coleta.apiGetNotasFiscaisColeta(this.coleta.dadosColetaAtual);
    }
    finally {
      this.carregando = false;
    }

    if (this.global.getRespostaAPI().retorno == true) {
      //não faz nada
    }
    else {
      this.global.exibirRespostaAPI();
    }
  }


  incluirNotaFiscal() {
    console.log('ColetaNotasFiscaisPage -> abrirNotaFiscal');

    // Inicializamos os dados da nota fiscal
    this.coleta.dadosNotaAtual = {
      coleta_nf_id: '',
      coleta_id: '',
      cod_barras: '',
      serie: '',
      numero: '',
      valor: '',
      volumes: ''
    }
    this.router.navigate(['/nota-fiscal'])
  }


  atualizarNotaFiscal(nf: any) {
    console.log('ColetaNotasFiscaisPage -> atualizarNotaFiscal', nf);

    // Inicializamos os dados da nota fiscal
    this.coleta.dadosNotaAtual = {
      coleta_nf_id: nf.coleta_nf_id,
      coleta_id: nf.coleta_id,
      cod_barras: nf.cod_barras,
      serie: nf.serie,
      numero: nf.numero,
      valor: nf.valor,
      volumes: nf.volumes
    }
    this.router.navigate(['/nota-fiscal'])
  }

  async excluirNotaFiscal(nf: any) {
    console.log('ColetaNotasFiscaisPage -> excluirNotaFiscal', nf);

    const confirm = await this.alertController.create({
      header: 'Excluir Nota Fiscal',
      subHeader: '',
      message: 'Deseja realmente excluir essa nota fiscal?',
      buttons: [
        {
          text: 'Cancelar',
          handler: () => {
            console.log('Excluir Nota Fiscal?', 'Cancelar');
          }
        },
        {
          text: 'Excluir',
          handler: () => {
            console.log('Excluir Nota Fiscal?', 'Excluir');
            this.confirmarExclusao(nf);
          }
        }
      ]
    });
    await confirm.present();
  }

  async confirmarExclusao(nf: any) {
    console.log('ColetaNotasFiscaisPage -> excluirNotaFiscal', nf);

    await this.procs.iniciarLoading();
    await this.coleta.apiExcluirNotaFiscalColeta(nf);
    await this.procs.finalizarLoading();

    if (this.global.getRespostaAPI().retorno == false) {
      this.global.exibirRespostaAPI();
    }
  }

  getTotalNotas(): string {
    //console.log('ColetaNotasFiscaisPage -> getTotalNotas');

    let total = 0;

    if (this.coleta.notasFiscais.length > 0) {
      this.coleta.notasFiscais.forEach(nf => {
        total += parseFloat(nf.valor)
      });
    }

    return total.toFixed(2)
  }

  getTotalVolumes(): string {
    //console.log('ColetaNotasFiscaisPage -> getTotalVolumes');

    let total = 0;

    if (this.coleta.notasFiscais.length > 0) {
      this.coleta.notasFiscais.forEach(nf => {
        total += parseInt(nf.volumes)
      });
    }

    return total.toString()
  }

  async reatualizarRecibo(nf: any) {
    console.log('ColetaNotasFiscaisPage -> reatualizarRecibo');

    const confirm = await this.alertController.create({
      header: 'Recibo',
      subHeader: 'O que deseja fazer?',
      buttons: [
        {
          text: 'Visualizar',
          handler: () => {
            console.log('O que deseja fazer?', 'Visualizar');
            this.global.visualizarFoto(nf.url_recibo);
          }
        },
        {
          text: 'Atualizar',
          handler: () => {
            console.log('O que deseja fazer?', 'Atualizar');
            this.tirarFotoReciboNotaFiscal(nf);
          }
        }
      ]
    });
    await confirm.present();
  }


  async tirarFotoReciboNotaFiscal(nf: any) {
    console.log('ColetaNotasFiscaisPage -> atualizarReciboNotaFiscal', nf);
    nf.img_base64 = await this.global.getFoto(this.global.getCameraOptions());
    this.atualizarReciboNotaFiscal(nf);    
  }


  async atualizarReciboNotaFiscal(nf: any) {
    console.log('ColetaNotasFiscaisPage -> atualizarReciboNotaFiscal', nf);

    let bkp_nf = {
      img_base64: nf.img_base64,
      url_recibo: nf.url_recibo,
      mot_nao_entrega: nf.mot_nao_entrega
    };

    nf.mot_nao_entrega = '';

    await this.procs.iniciarLoading();
    await this.coleta.apiAtualizarReciboNotaFiscalColeta(nf);
    await this.procs.finalizarLoading();

    if (this.global.getRespostaAPI().retorno == true) {
      this.procs.exibirToast('', 'Recibo enviado com sucesso.');
    }
    else {
      nf.img_base64 = bkp_nf.img_base64;
      nf.url_recibo = bkp_nf.url_recibo;
      nf.mot_nao_entrega = bkp_nf.mot_nao_entrega;
      this.global.exibirRespostaAPI();
    }
  }


  async informarMotivoNaoEntregaNotaFiscal(nf: any) {
    console.log('ModeloPage -> informarMotivoNaoEntregaNotaFiscal');

    const confirm = await this.alertController.create({
      //mode: 'ios',
      cssClass: 'maser-alert',
      header: 'Selecione o motivo',
      subHeader: '',
      message: '',
      inputs: [
        {
          name: 'radio1',
          type: 'radio',
          label: 'Mercadoria não conforme',
          value: '51',
          checked: (nf.mot_nao_entrega == '51')
        },
        {
          name: 'radio2',
          type: 'radio',
          label: 'Recusa de nota fiscal',
          value: '52',
          checked: (nf.mot_nao_entrega == '52')
        }
      ],
      buttons: [
        {
          text: 'Cancelar',
          handler: () => {
            console.log('informarMotivoNaoEntrega?', 'Cancelar');
          }
        },
        {
          text: 'Confirmar',
          handler: (data) => {
            console.log('informarMotivoNaoEntrega?', 'Confirmar', data);
            nf.mot_nao_entrega = data;
            this.atualizarMotivoNaoEntregaNotaFiscal(nf);
          }
        }
      ]
    });
    await confirm.present();
  }


  async atualizarMotivoNaoEntregaNotaFiscal(nf: any) {
    console.log('ColetaNotasFiscaisPage -> atualizarMotivoNaoEntregaNotaFiscal', nf);

    let bkp_nf = {
      img_base64: nf.img_base64,
      url_recibo: nf.url_recibo,
      mot_nao_entrega: nf.mot_nao_entrega
    };

    nf.img_base64 = '';
    nf.url_recibo = '';

    await this.procs.iniciarLoading();
    await this.coleta.apiAtualizarReciboNotaFiscalColeta(nf);
    await this.procs.finalizarLoading();

    if (this.global.getRespostaAPI().retorno == true) {
      this.procs.exibirToast('', 'Nota fiscal atualizada.');
    }
    else {
      nf.img_base64 = bkp_nf.img_base64;
      nf.url_recibo = bkp_nf.url_recibo;
      nf.mot_nao_entrega = bkp_nf.mot_nao_entrega;
      this.global.exibirRespostaAPI();
    }
  }


  alterarSituacaoNota(nf: any) {
    console.log('ColetaNotasFiscaisPage -> alterarSituacaoNota', nf);
    nf.url_recibo = '';
    nf.mot_nao_entrega = '';
  }


}
