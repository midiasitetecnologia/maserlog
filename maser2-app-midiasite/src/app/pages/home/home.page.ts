import { Component } from '@angular/core';
import { Router } from '@angular/router';
import { Platform } from '@ionic/angular';

import { ProcsService } from '../../services/procs.service';
import { GlobalService } from '../../services/global.service';
import { MotoristaService } from '../../services/motorista.service';
import { ColetaService } from '../../services/coleta.service';

@Component({
  selector: 'app-home',
  templateUrl: 'home.page.html',
  styleUrls: ['home.page.scss']
})
export class HomePage {

  public carregando: boolean = false;

  constructor(
    private router: Router,
    public platform: Platform,
    public procs: ProcsService,
    public global: GlobalService,
    public motorista: MotoristaService,
    public coleta: ColetaService) {
    console.log('HomePage -> constructor');
  }

  ngOnInit() {
    console.log('HomePage -> ngOnInit');
  }

  ionViewDidEnter() {
    console.log('CargaPage -> ionViewDidEnter');

    if (this.coleta.atualizarColetasPendentes == true) {
      this.atualizarHome(false);
    }
  }

  ionViewDidLeave() {
  }

  async atualizarHome(exibirMsg: boolean = true, refresher: any = '') {
    console.log('HomePage -> atualizarHome');

    this.carregando = true;
    try {
      await this.coleta.apiGetColetasPendentes();
    }
    finally {
      this.carregando = false;
      // Código para desligar o ion-refresher
      if (refresher) { refresher.target.complete(); }
    }

    if ((this.global.getRespostaAPI().retorno == false) && (exibirMsg == true)) {
      this.global.exibirRespostaAPI();
    }
  }


  async alterarVeiculo() {
    console.log('HomePage -> alterarVeiculo');

    await this.procs.iniciarLoading();
    await this.motorista.apiGetVeiculosDisponiveis();
    await this.procs.finalizarLoading();

    if (this.global.getRespostaAPI().retorno == true) {
      this.router.navigate(['/veiculo']);
    }
    else {
      this.global.exibirRespostaAPI();
    }
  }


  acessarModelo() {
    console.log('HomePage -> acessarModelo', (this.global.getModoDeveloper() == 'S'));
    if (this.global.getModoDeveloper() == 'S') {
      this.router.navigate(['/modelo']);
    }
  }

}
