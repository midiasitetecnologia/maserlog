import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';

import { IonicModule } from '@ionic/angular';

import { NotaFiscalPageRoutingModule } from './nota-fiscal-routing.module';

import { NotaFiscalPage } from './nota-fiscal.page';

@NgModule({
  imports: [
    CommonModule,
    FormsModule,
    IonicModule,
    NotaFiscalPageRoutingModule
  ],
  declarations: [NotaFiscalPage]
})
export class NotaFiscalPageModule {}
