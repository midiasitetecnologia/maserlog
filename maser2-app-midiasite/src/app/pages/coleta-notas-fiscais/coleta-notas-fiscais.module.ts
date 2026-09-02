import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';

import { IonicModule } from '@ionic/angular';

import { ColetaNotasFiscaisPageRoutingModule } from './coleta-notas-fiscais-routing.module';

import { ColetaNotasFiscaisPage } from './coleta-notas-fiscais.page';

@NgModule({
  imports: [
    CommonModule,
    FormsModule,
    IonicModule,
    ColetaNotasFiscaisPageRoutingModule
  ],
  declarations: [ColetaNotasFiscaisPage]
})
export class ColetaNotasFiscaisPageModule {}
