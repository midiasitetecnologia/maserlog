import { Component, OnInit } from '@angular/core';
import { Router } from '@angular/router';
import { Location } from '@angular/common';

import { ProcsService } from '../../services/procs.service';
import { GlobalService } from '../../services/global.service';
import { MotoristaService } from '../../services/motorista.service';

@Component({
  selector: 'app-veiculo-ocupacao',
  templateUrl: './veiculo-ocupacao.page.html',
  styleUrls: ['./veiculo-ocupacao.page.scss'],
})
export class VeiculoOcupacaoPage implements OnInit {

  public atualizar: boolean = false;

  public veiculo: any = {
    ocup_veiculo: '',
    img_base64: ''
  }

  constructor(
    private router: Router,
    public location: Location,
    public procs: ProcsService,
    public global: GlobalService,
    public motorista: MotoristaService) {
    console.log('VeiculoOcupacaoPage -> constructor');
  }

  ngOnInit() {
    console.log('VeiculoOcupacaoPage -> ngOnInit');
    this.veiculo.ocup_veiculo = this.motorista.getOcupVeiculo();
  }

  async pegarFotoGaleria() {
    console.log('VeiculoOcupacaoPage -> pegarFotoGaleria');
    this.veiculo.img_base64 = await this.global.getFoto(this.global.getGaleriaOptions());
  }

  async tirarFoto() {
    console.log('VeiculoOcupacaoPage -> tirarFoto');
    this.veiculo.img_base64 = await this.global.getFoto(this.global.getCameraOptions());    
  }

  async atualizarOcupacaoVeiculo() {
    console.log('VeiculoOcupacaoPage -> atualizarOcupacaoVeiculo');

    await this.procs.iniciarLoading();
    await this.motorista.apiAtualizarOcupacaoVeiculo(this.veiculo);
    await this.procs.finalizarLoading();

    if (this.global.getRespostaAPI().retorno == true) {
      //this.router.navigate(['']);
      this.router.navigate(['/tabs/carga']);
    } else {
      this.global.exibirRespostaAPI();
    }
  }

}
