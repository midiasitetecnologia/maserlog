import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';

import { IonicModule } from '@ionic/angular';

import { PopoverColetaComponentModule } from '../../components/popover-coleta/popover-coleta.module';

import { ColetaPageRoutingModule } from './coleta-routing.module';

import { ColetaPage } from './coleta.page';

@NgModule({
  imports: [
    CommonModule,
    FormsModule,
    IonicModule,
    ColetaPageRoutingModule,
    PopoverColetaComponentModule,
  ],
  declarations: [ColetaPage]
})
export class ColetaPageModule {}
