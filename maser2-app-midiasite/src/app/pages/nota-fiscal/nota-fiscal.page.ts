import { Component, OnInit } from '@angular/core';
import { Location } from '@angular/common';
import { AlertController } from '@ionic/angular';

import { BarcodeScannerService } from '../../services/barcodescanner.service';
import { ProcsService } from '../../services/procs.service';
import { GlobalService } from '../../services/global.service';
import { ColetaService } from '../../services/coleta.service';

@Component({
  selector: 'app-nota-fiscal',
  templateUrl: './nota-fiscal.page.html',
  styleUrls: ['./nota-fiscal.page.scss'],
})
export class NotaFiscalPage implements OnInit {

  public chave_invalida: boolean = false;
  public valor_invalido: boolean = false;

  constructor(
    public location: Location,
    public alertController: AlertController,
    public barcodeScanner: BarcodeScannerService,
    public procs: ProcsService,
    public global: GlobalService,
    public coleta: ColetaService) {
    console.log('NotaFiscalPage -> constructor');
  }


  ngOnInit() {
    console.log('NotaFiscalPage -> ngOnInit');
  }


  ionViewDidEnter() {
    console.log('NotaFiscalPage -> ionViewDidEnter');
  }


  async incluirNotaFiscal(nf: any) {
    console.log('NotaFiscalPage -> incluirNotaFiscal');

    // Setamos o ID da coleta para a nota fiscal
    nf.coleta_id = this.coleta.dadosColetaAtual.coleta_id;

    await this.procs.iniciarLoading();
    await this.coleta.apiIncluirNotaFiscalColeta(nf);
    await this.procs.finalizarLoading();

    if (this.global.getRespostaAPI().retorno == true) {
      await this.getNotasFiscaisColeta();
      // TEMP - aqui temos que VOLTAR. Ver se esse código serve (parece que sim) ou precisamos de outro!!!
      //this.router.navigate(['/coleta-notas-fiscais']);
      this.location.back();
    } else {
      this.global.exibirRespostaAPI();
    }
  }


  async atualizarNotaFiscal(nf: any) {
    console.log('NotaFiscalPage -> atualizarNotaFiscal');

    await this.procs.iniciarLoading();
    await this.coleta.apiAtualizarNotaFiscalColeta(nf);
    await this.procs.finalizarLoading();

    if (this.global.getRespostaAPI().retorno == true) {
      await this.getNotasFiscaisColeta();
      // TEMP - aqui temos que VOLTAR. Ver se esse código serve ou precisamos de outro!!!
      //this.router.navigate(['/coleta-notas-fiscais']);
      this.location.back();
    } else {
      this.global.exibirRespostaAPI();
    }
  }


  async getNotasFiscaisColeta() {
    console.log('NotaFiscalPage -> getNotasFiscaisColeta');

    await this.procs.iniciarLoading();
    await this.coleta.apiGetNotasFiscaisColeta(this.coleta.dadosColetaAtual);
    await this.procs.finalizarLoading();
  }


  async lerCodBarras() {
    console.log('NotaFiscalPage -> lerCodBarras');

    const barcode = await this.barcodeScanner.lerBarcode128();
    
    if (barcode) {
      this.coleta.dadosNotaAtual.cod_barras = barcode;
      this.carregarDadosNota();
    }
  }


  carregarDadosNota() {
    console.log('NotaFiscalPage -> carregarDadosNota');

    let chave = this.coleta.dadosNotaAtual.cod_barras;

    if (chave.length == 44) {
      // Série (posição 23 da chave + 3 dígitos)
      // Pegamos a partir da posição 22, pois o substr 
      // considera a primeira posição da string como 0.
      this.coleta.dadosNotaAtual.serie = parseInt(chave.substr(22, 3));
      // Número (posição 26 da chave + 9 dígitos)
      this.coleta.dadosNotaAtual.numero = parseInt(chave.substr(25, 9));
      this.chave_invalida = false;
    } else {
      this.chave_invalida = true;
    }
  }

  tratarValor() {
    console.log('NotaFiscalPage -> tratarValor');

    let valor = parseFloat(this.coleta.dadosNotaAtual.valor);
    this.coleta.dadosNotaAtual.valor = valor.toFixed(2);

    if (valor > -1) {
      this.valor_invalido = false;
    } else {
      this.valor_invalido = true;
    }
  }

}
