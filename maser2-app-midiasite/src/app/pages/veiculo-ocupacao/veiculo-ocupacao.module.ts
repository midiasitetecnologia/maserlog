import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';

import { IonicModule } from '@ionic/angular';

import { VeiculoOcupacaoPageRoutingModule } from './veiculo-ocupacao-routing.module';

import { VeiculoOcupacaoPage } from './veiculo-ocupacao.page';

@NgModule({
  imports: [
    CommonModule,
    FormsModule,
    IonicModule,
    VeiculoOcupacaoPageRoutingModule
  ],
  declarations: [VeiculoOcupacaoPage]
})
export class VeiculoOcupacaoPageModule {}
