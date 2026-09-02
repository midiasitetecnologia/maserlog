import { Component, OnInit, Input } from '@angular/core';
import { Router } from '@angular/router';
import { AlertController } from '@ionic/angular';

import { ProcsService } from '../../services/procs.service';
import { GlobalService } from '../../services/global.service';
import { ColetaService } from '../../services/coleta.service';
import { MotoristaService } from '../../services/motorista.service';

@Component({
  selector: 'app-card-solicitacao',
  templateUrl: './card-solicitacao.component.html',
  styleUrls: ['./card-solicitacao.component.scss'],
})
export class CardSolicitacaoComponent implements OnInit {
  @Input() solicitacao: any;
  @Input() ehcontrato: boolean = false;
  @Input() ehcarga: boolean = false;
  @Input() exibir_instrucao: boolean = false;
  @Input() exibir_receber_nf_frete: boolean = false;

  constructor(
    private router: Router,
    private alertController: AlertController,
    public procs: ProcsService,
    public global: GlobalService,
    public coleta: ColetaService,
    public motorista: MotoristaService) {
  }

  ngOnInit() { }

  async abrirColeta(coleta: any) {
    console.log('CardSolicitacaoComponent -> abrirColeta', coleta);

    let continuar = await this.coleta.carregarDadosColeta(coleta);

    if (continuar == true) {
      this.router.navigate(['/coleta']);
    }
  }


  // CONTRATO - Funções
  // --------------------------------------------------
  async iniciarExpediente(coleta: any) {
    console.log('CardSolicitacaoComponent -> iniciarExpediente');

    //let continuar = await this.coleta.carregarDadosColeta(coleta);
    let continuar = true;
    this.coleta.dadosColetaAtual = coleta;

    if (continuar == true) {

      const confirm = await this.alertController.create({
        header: 'Olá ' + this.motorista.getPrimeiroNome() + '!',
        subHeader: 'Você está iniciando agora o expediente da solicitação ' + this.coleta.getIdSolicitacao(this.coleta.dadosColetaAtual) + '.',
        message:
          '<strong>Local: </strong>' + this.coleta.dadosColetaAtual.local_coleta +
          '<br><strong>Hora: </strong>' + this.procs.getHoraAtual() +
          '<br><br>Confirma o início do expediente da solicitação?',
        buttons: [
          {
            text: 'Não',
            handler: () => {
              console.log('iniciarExpediente?', 'Não');
            }
          },
          {
            text: 'Sim',
            handler: () => {
              console.log('iniciarExpediente?', 'Sim');
              this.confirmarIniciarExpediente();
            }
          }
        ]
      });
      await confirm.present();

    }

  }

  async confirmarIniciarExpediente() {
    console.log('CardSolicitacaoComponent -> confirmarIniciarExpediente');

    await this.procs.iniciarLoading();
    await this.coleta.apiIniciarExpediente(this.coleta.dadosColetaAtual);
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
  incluirComanda(comanda: any) {
    console.log('CardSolicitacaoComponent -> incluirComanda', comanda);

    // Inicializamos os dados da comanda
    this.coleta.dadosComandaAtual = {
      solic_origem_id: comanda.coleta_id,
      coleta_id: '',
      local_coleta: '',
      local_entrega: '',
      obs_coleta: ''
    }
    this.router.navigate(['/comanda']);
  }



  // COLETA - Funções
  // --------------------------------------------------
  async setarDeslocaColeta(coleta: any) {
    console.log('CardSolicitacaoComponent -> setarDeslocaColeta');

    //Aqui precisa carregar os dados da coleta para retornar os campos 'distancia_destino' e 'prev_chegada_destino'.
    let continuar = await this.coleta.carregarDadosColeta(coleta);

    if (continuar == true) {

      let mensagem: string;

      if (this.coleta.getEhComanda(this.coleta.dadosColetaAtual) == true) {
        mensagem =
          '<strong>Local: </strong>' + this.coleta.dadosColetaAtual.local_coleta +
          '<br><strong>Hora: </strong>' + this.procs.getHoraAtual() +
          '<br><br>Confirma sua partida para o local da coleta?'
      } else {
        mensagem =
          '<strong>Local: </strong>' + this.coleta.dadosColetaAtual.local_coleta +
          '<br><strong>Hora: </strong>' + this.procs.getHoraAtual() +
          '<br><br><strong>Distância: </strong>' + this.coleta.dadosColetaAtual.distancia_destino +
          '<br><strong>Previsão de chegada: </strong>' + this.coleta.dadosColetaAtual.prev_chegada_destino +
          '<br><br>Confirma sua partida para o local da coleta?'
      }

      const confirm = await this.alertController.create({
        header: 'Olá ' + this.motorista.getPrimeiroNome() + '!',
        message: mensagem,
        buttons: [
          {
            text: 'Não',
            handler: () => {
              console.log('setarDeslocaColeta?', 'Não');
            }
          },
          {
            text: 'Sim',
            handler: () => {
              console.log('setarDeslocaColeta?', 'Sim');
              this.confirmarSetarDeslocaColeta();
            }
          }
        ]
      });
      await confirm.present();

    }
  }

  async confirmarSetarDeslocaColeta() {
    console.log('CardSolicitacaoComponent -> confirmarSetarDeslocaColeta');

    await this.procs.iniciarLoading();
    await this.coleta.apiSetarDeslocaColeta(this.coleta.dadosColetaAtual);
    await this.procs.finalizarLoading();

    if (this.global.getRespostaAPI().retorno == true) {
      await this.coleta.carregarColetasPendentes();
      this.router.navigate(['']);
    } else {
      this.global.exibirRespostaAPI();
    }
  }

  async setarChegadaColeta(coleta: any) {
    console.log('CardSolicitacaoComponent -> setarChegadaColeta');

    //let continuar = await this.coleta.carregarDadosColeta(coleta);
    let continuar = true;
    this.coleta.dadosColetaAtual = coleta;

    if (continuar == true) {
      this.router.navigate(['/coleta-finalizar']);
    }
  }

  async setarInicioAtendColeta(coleta: any) {
    console.log('CardSolicitacaoComponent -> setarInicioAtendColeta');

    //let continuar = await this.coleta.carregarDadosColeta(coleta);
    let continuar = true;
    this.coleta.dadosColetaAtual = coleta;

    if (continuar == true) {
      // Parâmetro que usamos na page "coleta-finalizar" para identificar 
      // se é um INÍCIO de atendimento ou DEVOLUÇÃO de coleta.
      this.coleta.dadosColetaAtual.devolver_sem_atend = 'N';
      this.router.navigate(['/coleta-finalizar']);
    }
  }

  async finalizarColeta(coleta: any) {
    console.log('CardSolicitacaoComponent -> finalizarColeta');

    //Aqui precisa carregar os dados da coleta para retornar na API os dados de 'tipos_veiculo'.
    let continuar = await this.coleta.carregarDadosColeta(coleta);

    if (continuar == true) {

      continuar = await this.coleta.carregarNotasFiscaisColeta(coleta);

      if (continuar == true) {
        this.router.navigate(['/coleta-finalizar']);
      }

    }
  }

  async setarDescargaColeta(coleta: any) {
    console.log('CardSolicitacaoComponent -> setarDescargaColeta');

    //let continuar = await this.coleta.carregarDadosColeta(coleta);
    let continuar = true;
    this.coleta.dadosColetaAtual = coleta;

    if (continuar == true) {
      const confirm = await this.alertController.create({
        header: 'Olá ' + this.motorista.getPrimeiroNome() + '!',
        subHeader:
          'A operação de Descarga indica que você está tirando uma carga ' +
          'do seu veículo e depositando no pavilhão ou outro depósito.',
        message:
          '<strong>Solicitação: </strong>' + this.coleta.getIdSolicitacao(this.coleta.dadosColetaAtual) +
          '<br><br>Confirma a descarga no pavilhão ou outro depósito?',
        buttons: [
          {
            text: 'Não',
            handler: () => {
              console.log('setarDescargaColeta?', 'Não');
            }
          },
          {
            text: 'Sim',
            handler: () => {
              console.log('setarDescargaColeta?', 'Sim');
              this.confirmarSetarDescargaColeta();
            }
          }
        ]
      });
      await confirm.present();
    }
  }

  async confirmarSetarDescargaColeta() {
    console.log('CardSolicitacaoComponent -> setarDescargaColeta');

    await this.procs.iniciarLoading();
    await this.coleta.apiSetarDescargaColeta(this.coleta.dadosColetaAtual);
    await this.procs.finalizarLoading();

    if (this.global.getRespostaAPI().retorno == true) {
      await this.coleta.carregarColetasPendentes(true);
      this.router.navigate(['/tabs/carga']);
    } else {
      this.global.exibirRespostaAPI();
    }
  }


  async baldearColeta(solicitacao: any) {
    console.log('CardSolicitacaoComponent -> baldearColeta');

    //let continuar = await this.coleta.carregarDadosColeta(solicitacao);
    let continuar = true;
    this.coleta.dadosColetaAtual = solicitacao;

    if (continuar == true) {
      // Inicializamos os dados de baldeação da coleta
      this.coleta.dadosColetaAtual.placa_destino = '';
      this.coleta.dadosColetaAtual.transfer_code = '';

      let descricao = 'coleta';
      let local = this.coleta.dadosColetaAtual.local_coleta;

      if (this.coleta.getTipoSolicitacao(this.coleta.dadosColetaAtual) == 'E') {
        descricao = 'entrega';        
        local = this.coleta.dadosColetaAtual.local_entrega;
      }
      
      const alert = await this.alertController.create({
        header: `Baldear ${descricao} ${this.coleta.getIdSolicitacao(this.coleta.dadosColetaAtual)}`,
        subHeader: local,
        message: `Informe o código de baldeação do veículo: <strong>${this.coleta.dadosColetaAtual.placa_baldeacao}</strong>`,
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
              console.log('baldearColeta?', 'Cancelar');
            }
          }, {
            text: 'Confirmar',
            handler: (data) => {
              console.log('baldearColeta?', data);
              this.coleta.dadosColetaAtual.placa_destino = this.coleta.dadosColetaAtual.placa_baldeacao;
              this.coleta.dadosColetaAtual.transfer_code = data.transfer_code;
              this.confirmarBaldeacaoColeta();
            }
          }
        ]
      });
      await alert.present();
    }
  }


  async confirmarBaldeacaoColeta() {
    console.log('CardSolicitacaoComponent -> confirmarBaldeacaoColeta');

    await this.procs.iniciarLoading();
    await this.coleta.apiBaldearColeta(this.coleta.dadosColetaAtual);
    await this.procs.finalizarLoading();

    if (this.global.getRespostaAPI().retorno == true) {
      await this.coleta.carregarColetasPendentes(true);
      this.router.navigate(['/tabs/carga']);
      this.procs.exibirToast('', 'Baldeação realizada com sucesso!', 'verde-web');
    } else {
      this.global.exibirRespostaAPI();
    }
  }



  // ENTREGA - Funções
  // --------------------------------------------------
  async setarDeslocaEntrega(coleta: any) {
    console.log('CardSolicitacaoComponent -> setarDeslocaEntrega');

    //Aqui precisa carregar os dados da coleta para retornar os campos 'distancia_destino' e 'prev_chegada_destino'.
    let continuar = await this.coleta.carregarDadosColeta(coleta);

    if (continuar == true) {

      let mensagem: string;

      if (this.coleta.getEhComanda(this.coleta.dadosColetaAtual) == true) {
        mensagem =
          '<strong>Local: </strong>' + this.coleta.dadosColetaAtual.local_entrega +
          '<br><strong>Hora: </strong>' + this.procs.getHoraAtual() +
          '<br><br>Confirma sua partida para o local da entrega?'
      } else {
        mensagem =
          '<strong>Local: </strong>' + this.coleta.dadosColetaAtual.local_entrega +
          '<br><strong>Hora: </strong>' + this.procs.getHoraAtual() +
          '<br><br><strong>Distância: </strong>' + this.coleta.dadosColetaAtual.distancia_destino +
          '<br><strong>Previsão de chegada: </strong>' + this.coleta.dadosColetaAtual.prev_chegada_destino +
          '<br><br>Confirma sua partida para o local da entrega?'
      }

      const confirm = await this.alertController.create({
        header: 'Olá ' + this.motorista.getPrimeiroNome() + '!',
        message: mensagem,
        buttons: [
          {
            text: 'Não',
            handler: () => {
              console.log('setarDeslocaEntrega?', 'Não');
            }
          },
          {
            text: 'Sim',
            handler: () => {
              console.log('setarDeslocaEntrega?', 'Sim');
              this.confirmarSetarDeslocaEntrega();
            }
          }
        ]
      });
      await confirm.present();
    }
  }

  async confirmarSetarDeslocaEntrega() {
    console.log('CardSolicitacaoComponent -> confirmarSetarDeslocaEntrega');

    await this.procs.iniciarLoading();
    await this.coleta.apiSetarDeslocaEntrega(this.coleta.dadosColetaAtual);
    await this.procs.finalizarLoading();

    if (this.global.getRespostaAPI().retorno == true) {
      await this.coleta.carregarColetasPendentes();
      this.router.navigate(['']);
    } else {
      this.global.exibirRespostaAPI();
    }
  }

  async setarChegadaEntrega(coleta: any) {
    console.log('CardSolicitacaoComponent -> setarChegadaEntrega');

    //let continuar = await this.coleta.carregarDadosColeta(coleta);
    let continuar = true;
    this.coleta.dadosColetaAtual = coleta;

    if (continuar == true) {
      this.router.navigate(['/coleta-finalizar']);
    }
  }

  async setarInicioAtendEntrega(coleta: any) {
    console.log('CardSolicitacaoComponent -> setarInicioAtendEntrega');

    //let continuar = await this.coleta.carregarDadosColeta(coleta);
    let continuar = true;
    this.coleta.dadosColetaAtual = coleta;

    if (continuar == true) {
      this.router.navigate(['/coleta-finalizar']);
    }
  }

  async finalizarEntrega(coleta: any) {
    console.log('CardSolicitacaoComponent -> finalizarEntrega');

    //let continuar = await this.coleta.carregarDadosColeta(coleta);
    let continuar = true;
    this.coleta.dadosColetaAtual = coleta;

    if (continuar == true) {

      continuar = await this.coleta.carregarNotasFiscaisColeta(coleta);

      if (continuar == true) {
        this.router.navigate(['/coleta-finalizar']);
      }

    }
  }


  // Notas Fiscais - Funções
  // --------------------------------------------------
  async abrirNotasFiscais(coleta: any) {
    console.log('CardSolicitacaoComponent -> abrirNotasFiscais');

    //let continuar = await this.coleta.carregarDadosColeta(coleta);
    let continuar = true;
    this.coleta.dadosColetaAtual = coleta;

    if (continuar == true) {

      continuar = await this.coleta.carregarNotasFiscaisColeta(coleta);

      if (continuar == true) {
        this.router.navigate(['/coleta-notas-fiscais']);
      }

    }
  }

}
