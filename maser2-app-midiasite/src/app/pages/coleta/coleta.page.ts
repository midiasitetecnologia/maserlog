import { Component, OnInit } from '@angular/core';
import { Router } from '@angular/router';
import { Location } from '@angular/common';
import { AlertController, PopoverController } from '@ionic/angular';

import { PopoverColetaComponent } from './../../components/popover-coleta/popover-coleta.component';

import { ProcsService } from '../../services/procs.service';
import { GlobalService } from '../../services/global.service';
import { ColetaService } from '../../services/coleta.service';
import { MotoristaService } from '../../services/motorista.service';

@Component({
  selector: 'app-coleta',
  templateUrl: './coleta.page.html',
  styleUrls: ['./coleta.page.scss'],
})
export class ColetaPage implements OnInit {

  public carregando: boolean = false;
  public segmento: string = '';

  constructor(
    private router: Router,
    public location: Location,
    private alertController: AlertController,
    private popoverController: PopoverController,
    public procs: ProcsService,
    public global: GlobalService,
    public coleta: ColetaService,
    public motorista: MotoristaService) {
    console.log('ColetaPage -> constructor');
  }

  ngOnInit() {
    console.log('ColetaPage -> ngOnInit');

    this.segmento = 'solic';
    if ('C1|C2|C3|C4|CR'.indexOf(this.coleta.dadosColetaAtual.status) > -1) {
      this.segmento = 'coleta';
    }
    else if ('E0|E1|E2|E3|E4'.indexOf(this.coleta.dadosColetaAtual.status) > -1) {
      this.segmento = 'entrega';
    }

    if ((this.coleta.getEhContrato(this.coleta.dadosColetaAtual) == false) && (this.coleta.dadosColetaAtual.status >= 'C4')) {
      this.getNotasFiscaisColeta();
    }
  }


  // CONTRATO - Funções
  // --------------------------------------------------
  async finalizarExpediente() {
    console.log('ColetaPage -> finalizarExpediente');

    const confirm = await this.alertController.create({
      header: 'Olá ' + this.motorista.getPrimeiroNome() + '!',
      subHeader: 'Você está finalizando agora o expediente da solicitação ' + this.coleta.getIdSolicitacao(this.coleta.dadosColetaAtual) + '.',
      message:
        '<strong>Local: </strong>' + this.coleta.dadosColetaAtual.local_coleta +
        '<br><strong>Hora: </strong>' + this.procs.getHoraAtual() +
        '<br><br>Confirma o fim do expediente da solicitação?',
      buttons: [
        {
          text: 'Não',
          handler: () => {
            console.log('finalizarExpediente?', 'Não');
          }
        },
        {
          text: 'Sim',
          handler: () => {
            console.log('finalizarExpediente?', 'Sim');
            this.confirmarFinalizarExpediente();
          }
        }
      ]
    });
    await confirm.present();
  }

  async confirmarFinalizarExpediente() {
    console.log('ColetaPage -> confirmarFinalizarExpediente');

    await this.procs.iniciarLoading();
    await this.coleta.apiFinalizarExpediente(this.coleta.dadosColetaAtual);
    await this.procs.finalizarLoading();

    if (this.global.getRespostaAPI().retorno == true) {
      await this.coleta.carregarColetasPendentes();
      this.router.navigate(['']);
    } else {
      this.global.exibirRespostaAPI();
    }
  }


  // COMANDA - Funções
  // --------------------------------------------------
  atualizarComanda() {
    console.log('ColetaPage -> atualizarComanda');

    // Inicializamos os dados da comanda
    this.coleta.dadosComandaAtual = {
      solic_origem_id: this.coleta.dadosColetaAtual.solic_origem_id,
      coleta_id: this.coleta.dadosColetaAtual.coleta_id,
      local_coleta: this.coleta.dadosColetaAtual.local_coleta,
      local_entrega: this.coleta.dadosColetaAtual.local_entrega,
      obs_coleta: this.coleta.dadosColetaAtual.obs_coleta
    }
    this.router.navigate(['/comanda']);
  }


  async excluirComanda() {
    console.log('ColetaPage -> excluirComanda');

    const confirm = await this.alertController.create({
      header: 'Excluir Comanda!',
      subHeader: 'Você está excluindo a ' + this.coleta.getIdSolicitacao(this.coleta.dadosColetaAtual, true) + '.',
      message: 'Confirma a exclusão da comanda?',
      buttons: [
        {
          text: 'Não',
          handler: () => {
            console.log('excluirComanda?', 'Não');
          }
        },
        {
          text: 'Sim',
          handler: () => {
            console.log('excluirComanda?', 'Sim');
            this.confirmarExcluirComanda();
          }
        }
      ]
    });
    await confirm.present();
  }

  async confirmarExcluirComanda() {
    console.log('ColetaPage -> confirmarExcluirComanda');

    await this.procs.iniciarLoading();
    await this.coleta.apiExcluirComanda(this.coleta.dadosColetaAtual);
    await this.procs.finalizarLoading();

    if (this.global.getRespostaAPI().retorno == true) {
      await this.coleta.carregarColetasPendentes();
      this.router.navigate(['']);
    } else {
      this.global.exibirRespostaAPI();
    }
  }


  // COLETA - Funções
  // --------------------------------------------------
  cancelarColetaSemDesloc() {
    console.log('ColetaPage -> cancelarColetaSemDesloc');
    // Parâmetro que usamos na page "coleta-finalizar" para 
    // identificar CANCELAMENTO DE COLETA SEM DESLOCAMENTO.
    this.coleta.dadosColetaAtual.cancelar_sem_desloc = 'S';
    this.router.navigate(['/coleta-finalizar']);
  }

  async devolverSemAtendColeta() {
    console.log('ColetaPage -> devolverSemAtendColeta');
    // Parâmetro que usamos na page "coleta-finalizar" para identificar 
    // se é um INÍCIO de atendimento ou DEVOLUÇÃO de coleta.
    this.coleta.dadosColetaAtual.devolver_sem_atend = 'S'
    this.router.navigate(['/coleta-finalizar']);
  }


  // DESFAZER STATUS
  // --------------------------------------------------
  async desfazerStatusAtualColeta(acao: string) {
    console.log('ColetaPage -> desfazerStatusAtualColeta');

    // Coleta (default)
    let etapa = `Coleta ${this.coleta.dadosColetaAtual.txt_status}`;
    let destino = this.coleta.dadosColetaAtual.local_coleta;

    // Entrega
    if (this.coleta.dadosColetaAtual.status.substr(0, 1) == 'E') {
      etapa = `Entrega ${this.coleta.dadosColetaAtual.txt_status}`;
      destino = this.coleta.dadosColetaAtual.local_entrega;
    }

    let mensagem =
      '<strong>Solicitação: </strong>' + this.coleta.getIdSolicitacao(this.coleta.dadosColetaAtual) +
      '<br><strong>Etapa atual: </strong> ' + etapa +
      '<br><strong>Destino: </strong>' + destino +
      '<br><br>Você tem certeza que quer ' + acao + '?';

    const confirm = await this.alertController.create({
      header: 'Confirmação',
      message: mensagem,
      buttons: [
        {
          text: 'Não',
          handler: () => {
            console.log('desfazerStatusAtualColeta?', 'Não');
          }
        },
        {
          text: 'Sim',
          handler: () => {
            console.log('desfazerStatusAtualColeta?', 'Sim');
            this.confirmarDesfazerStatusAtualColeta();
          }
        }
      ]
    });
    await confirm.present();
  }

  async confirmarDesfazerStatusAtualColeta() {
    console.log('ColetaPage -> confirmarDesfazerStatusAtualColeta');

    await this.procs.iniciarLoading();
    await this.coleta.apiDesfazerStatusAtualColeta(this.coleta.dadosColetaAtual);
    await this.procs.finalizarLoading();

    if (this.global.getRespostaAPI().retorno == true) {
      await this.coleta.carregarColetasPendentes();
      this.router.navigate(['']);
    } else {
      this.global.exibirRespostaAPI();
    }

  }


  // Notas Fiscais - Funções
  // --------------------------------------------------
  async abrirNotasFiscais() {
    console.log('ColetaPage -> abrirNotasFiscais');

    await this.procs.iniciarLoading();
    await this.coleta.apiGetNotasFiscaisColeta(this.coleta.dadosColetaAtual);
    await this.procs.finalizarLoading();

    if (this.global.getRespostaAPI().retorno == true) {
      this.router.navigate(['/coleta-notas-fiscais']);
    } else {
      this.global.exibirRespostaAPI();
    }
  }

  async getNotasFiscaisColeta() {
    console.log('ColetaPage -> getNotasFiscaisColeta');
    this.carregando = true;
    try {
      await this.coleta.apiGetNotasFiscaisColeta(this.coleta.dadosColetaAtual);
    }
    finally {
      this.carregando = false;
    }
  }

  async showPopover(ev: any) {
    const popover = await this.popoverController.create({
      component: PopoverColetaComponent,
      event: ev,
      translucent: true
    });
    return await popover.present();
  }


  setSegmento(ev: any) {
    this.segmento = ev.detail.value;
  }

}

