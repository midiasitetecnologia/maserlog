import { Component, OnInit } from '@angular/core';
import { Router } from '@angular/router';
import { Location } from '@angular/common';

import { ProcsService } from '../../services/procs.service';
import { GlobalService } from '../../services/global.service';
import { MotoristaService } from '../../services/motorista.service';
import { ColetaService } from '../../services/coleta.service';

@Component({
  selector: 'app-veiculo',
  templateUrl: './veiculo.page.html',
  styleUrls: ['./veiculo.page.scss'],
})
export class VeiculoPage implements OnInit {

  private placa_veiculo_select: string = '';

  constructor(
    private router: Router,
    public location: Location,
    public procs: ProcsService,
    public global: GlobalService,
    public motorista: MotoristaService,
    public coleta: ColetaService) {
    console.log('VeiculoPage -> constructor');
  }

  ngOnInit() {
    console.log('VeiculoPage -> ngOnInit');
    this.setPlacaVeiculoSelect(this.motorista.getPlacaVeiculo());
  }

  selecionarVeiculo(e: any) {
    let placa_veiculo = e.detail.value;
    console.log('VeiculoPage -> trocarVeiculo', placa_veiculo);
    this.setPlacaVeiculoSelect(placa_veiculo);
  }

  setPlacaVeiculoSelect(placa_veiculo: any) {
    console.log('VeiculoPage -> setPlacaVeiculoSelect', placa_veiculo);
    this.placa_veiculo_select = placa_veiculo;
  }

  async alterarVeiculo() {
    console.log('VeiculoPage -> alterarVeiculo');

    await this.procs.iniciarLoading();
    await this.motorista.apiAlterarVeiculoMotorista(this.placa_veiculo_select);
    await this.procs.finalizarLoading();

    if (this.global.getRespostaAPI().retorno == true) {
      await this.procs.iniciarLoading();
      this.coleta.atualizarColetasPendentes = true;
      await this.coleta.apiGetColetasPendentes();
      await this.motorista.apiGetNotificacoesMotorista();
      await this.procs.finalizarLoading();
      this.router.navigate(['']);
    } else {
      this.global.exibirRespostaAPI();
    }
  }

}
