import { Component, OnInit } from '@angular/core';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';
import { Router } from '@angular/router';
import { Location } from '@angular/common';

import { ProcsService } from '../../services/procs.service';
import { GlobalService } from '../../services/global.service';
import { ColetaService } from '../../services/coleta.service';

@Component({
  selector: 'app-comanda',
  templateUrl: './comanda.page.html',
  styleUrls: ['./comanda.page.scss'],
})
export class ComandaPage implements OnInit {

  public fGroup: FormGroup;

  constructor(
    private formBuilder: FormBuilder,
    private router: Router,
    public location: Location,
    public procs: ProcsService,
    public global: GlobalService,
    public coleta: ColetaService) {
    console.log('ComandaPage -> constructor');

    this.fGroup = this.formBuilder.group({
      'local_coleta': [null, Validators.compose([
        Validators.required
      ])],
      'local_entrega': [null, Validators.compose([
        Validators.required
      ])],
      'obs_coleta': ['']
    });
  }

  ngOnInit() {
    console.log('ComandaPage -> ngOnInit');
    
    // Atualização de Comanda
    if (this.coleta.dadosComandaAtual.coleta_id > '') {
      this.fGroup.get('local_coleta').setValue(this.coleta.dadosComandaAtual.local_coleta);
      this.fGroup.get('local_entrega').setValue(this.coleta.dadosComandaAtual.local_entrega);
      this.fGroup.get('obs_coleta').setValue(this.coleta.dadosComandaAtual.obs_coleta);
    }
  }


  async incluirComanda() {
    console.log('ComandaPage -> incluirComanda', this.fGroup.value);

    this.coleta.dadosComandaAtual.local_coleta = this.fGroup.get('local_coleta').value;
    this.coleta.dadosComandaAtual.local_entrega = this.fGroup.get('local_entrega').value;
    this.coleta.dadosComandaAtual.obs_coleta = this.fGroup.get('obs_coleta').value;
    
    await this.procs.iniciarLoading();
    await this.coleta.apiIncluirComanda(this.coleta.dadosComandaAtual);
    await this.procs.finalizarLoading();

    if (this.global.getRespostaAPI().retorno == true) {
      await this.coleta.carregarColetasPendentes();
      this.router.navigate(['']);
    } else {
      this.global.exibirRespostaAPI();
    }
  }


  async atualizarComanda() {
    console.log('ComandaPage -> atualizarComanda', this.fGroup.value);

    this.coleta.dadosComandaAtual.local_coleta = this.fGroup.get('local_coleta').value;
    this.coleta.dadosComandaAtual.local_entrega = this.fGroup.get('local_entrega').value;
    this.coleta.dadosComandaAtual.obs_coleta = this.fGroup.get('obs_coleta').value;

    await this.procs.iniciarLoading();
    await this.coleta.apiAtualizarComanda(this.coleta.dadosComandaAtual);
    await this.procs.finalizarLoading();

    if (this.global.getRespostaAPI().retorno == true) {
      await this.coleta.carregarColetasPendentes();
      //this.router.navigate(['/coleta']);
      this.location.back();
    } else {
      this.global.exibirRespostaAPI();
    }
  }


}
