import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';

import { IonicModule } from '@ionic/angular';

import { CardSolicitacaoComponent } from './card-solicitacao.component';

@NgModule({
  imports: [CommonModule, FormsModule, IonicModule],
  declarations: [CardSolicitacaoComponent],
  exports: [CardSolicitacaoComponent]
})
export class CardSolicitacaoComponentModule { }