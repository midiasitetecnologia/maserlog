import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';

import { IonicModule } from '@ionic/angular';

import { PopoverColetaComponent } from './popover-coleta.component';

@NgModule({
  imports: [CommonModule, FormsModule, IonicModule],
  declarations: [PopoverColetaComponent],
  exports: [PopoverColetaComponent]
})
export class PopoverColetaComponentModule {}

