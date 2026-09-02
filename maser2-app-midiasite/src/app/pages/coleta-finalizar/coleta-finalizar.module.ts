import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';

import { IonicModule } from '@ionic/angular';

import { ColetaFinalizarPageRoutingModule } from './coleta-finalizar-routing.module';

import { ColetaFinalizarPage } from './coleta-finalizar.page';

@NgModule({
  imports: [
    CommonModule,
    FormsModule,
    IonicModule,
    ColetaFinalizarPageRoutingModule
  ],
  declarations: [ColetaFinalizarPage]
})
export class ColetaFinalizarPageModule {}
