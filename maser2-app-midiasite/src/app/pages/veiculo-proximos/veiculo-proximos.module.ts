import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';

import { IonicModule } from '@ionic/angular';

import { VeiculoProximosPageRoutingModule } from './veiculo-proximos-routing.module';

import { VeiculoProximosPage } from './veiculo-proximos.page';

@NgModule({
  imports: [
    CommonModule,
    FormsModule,
    IonicModule,
    VeiculoProximosPageRoutingModule
  ],
  declarations: [VeiculoProximosPage]
})
export class VeiculoProximosPageModule {}
