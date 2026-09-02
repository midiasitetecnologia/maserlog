import { Component } from '@angular/core';
import { MotoristaService } from './../services/motorista.service';
import { ColetaService } from './../services/coleta.service';

@Component({
  selector: 'app-tabs',
  templateUrl: 'tabs.page.html',
  styleUrls: ['tabs.page.scss']
})
export class TabsPage {

  constructor(
    public motorista: MotoristaService,
    public coleta: ColetaService) {
  }

}
