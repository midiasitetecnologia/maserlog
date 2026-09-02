import { Component, OnInit } from '@angular/core';
import { Router } from '@angular/router';
import { PopoverController } from '@ionic/angular';

import { ProcsService } from '../../services/procs.service';
import { GlobalService } from '../../services/global.service';
import { ColetaService } from '../../services/coleta.service';

@Component({
  selector: 'app-popover-coleta',
  templateUrl: './popover-coleta.component.html',
  styleUrls: ['./popover-coleta.component.scss'],
})
export class PopoverColetaComponent implements OnInit {

  constructor(
    private router: Router,
    private popoverController: PopoverController,
    public procs: ProcsService,
    public global: GlobalService,
    public coleta: ColetaService) {
  }

  ngOnInit() {}

  //TEMP: Recurso transferido para a coleta (botão). Remover código após 01/08/2020.
  cancelarColetaSemDesloc() {
    console.log('PopoverColetaComponent -> cancelarColetaSemDesloc');

    // Fechar pop após clicar
    this.popoverController.dismiss();

    // Parâmetro que usamos na page "coleta-finalizar" para 
    // identificar CANCELAMENTO DE COLETA SEM DESLOCAMENTO.
    this.coleta.dadosColetaAtual.cancelar_sem_desloc = 'S';
    this.router.navigate(['/coleta-finalizar']);
  }

}
