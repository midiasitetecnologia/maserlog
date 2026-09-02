import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';

import { IonicModule } from '@ionic/angular';

import { SelecionarVeiculoComponent } from './selecionar-veiculo.component';

@NgModule({
  imports: [CommonModule, FormsModule, IonicModule],
  declarations: [SelecionarVeiculoComponent],
  exports: [SelecionarVeiculoComponent]
})
export class SelecionarVeiculoComponentModule {}

