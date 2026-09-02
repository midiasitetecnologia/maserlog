import { Component, OnInit, Input } from '@angular/core';
import { Router } from '@angular/router';

import { ProcsService } from '../../services/procs.service';
import { GlobalService } from '../../services/global.service';
import { MotoristaService } from '../../services/motorista.service';

@Component({
  selector: 'app-selecionar-veiculo',
  templateUrl: './selecionar-veiculo.component.html',
  styleUrls: ['./selecionar-veiculo.component.scss'],
})
export class SelecionarVeiculoComponent implements OnInit {
  @Input() name: string = '';

  constructor(
    private router: Router,
    public procs: ProcsService,
    public global: GlobalService,
    public motorista: MotoristaService) {
    console.log('SelecionarVeiculoComponent -> constructor');
  }

  ngOnInit() {}

  async alterarVeiculo() {
    console.log('SelecionarVeiculoComponent -> alterarVeiculo');

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

}
