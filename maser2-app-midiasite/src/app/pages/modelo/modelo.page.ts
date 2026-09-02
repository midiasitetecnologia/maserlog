import { Component, OnInit } from '@angular/core';
import { Router, ActivatedRoute } from '@angular/router';
import { Location } from '@angular/common';
import { AlertController, ToastController, PopoverController } from '@ionic/angular';

import { BarcodeScannerService } from '../../services/barcodescanner.service';
import { HttpService } from '../../services/http.service';
import { ProcsService } from '../../services/procs.service';
import { GlobalService } from '../../services/global.service';
import { ColetaService } from '../../services/coleta.service';
import { MotoristaService } from '../../services/motorista.service';

@Component({
  selector: 'app-modelo',
  templateUrl: './modelo.page.html',
  styleUrls: ['modelo.page.scss']
})

export class ModeloPage implements OnInit {

  constructor(
    public router: Router,
    public activatedRoute: ActivatedRoute,
    public location: Location,
    public alertController: AlertController,
    public toastController: ToastController,
    public popoverController: PopoverController,
    public barcodeScanner: BarcodeScannerService,
    public http: HttpService,
    public procs: ProcsService,
    public global: GlobalService,
    public coleta: ColetaService,
    public motorista: MotoristaService) {
    console.log('ModeloPage -> constructor');
  }


  ngOnInit() {
    console.log('ModeloPage -> ngOnInit');
  }


  async scan() {

    const barcode = await this.barcodeScanner.lerBarcode128();

    if (barcode) {
      if (barcode.length == 44) {
        this.procs.exibirMensagem('CHAVE NFE 44', barcode);
      }
      else {
        this.procs.exibirMensagem('Barcode128', barcode);
      }
    } 
    else {
      this.procs.exibirMensagem('Barcode128', 'NÃO LEU NADA');
    }

  }


  async tirarFoto() {
    console.log('ModeloPage -> tirarFoto');
    let img_base64 = await this.global.getFoto(this.global.getCameraOptions());
    this.global.visualizarFoto('data:image/jpeg;base64,' + img_base64);
  }

  async pegarFotoGaleria() {
    console.log('ModeloPage -> pegarFotoGaleria');
    let img_base64 = await this.global.getFoto(this.global.getGaleriaOptions());
    this.global.visualizarFoto('data:image/jpeg;base64,' + img_base64);
  }

  async post() {

    let body = {
      email: 'leandro@rgsoft.com.br',
      password: '123456',
      id_disp: 'abc123'
    }

    this.global.resetRespostaAPI();
    try {

      //let retorno = await this.http.post(this.global.getUrlAPIMaser() + 'AutenticarMotorista', body, this.global.getHeadersAPIMaser(false));
      let retorno = await this.http.post(this.global.getUrlAPIMaser() + 'GetColetasPendentes', {}, this.global.getHeadersAPIMaser());

      if (retorno.retorno.cod_retorno == 'A100') {
        this.global.setDataRespostaAPI(true, retorno);
      }
      else {
        this.global.setDataRespostaAPI(false, retorno);
      }

    } catch (error) {
      console.log('Modelo -> error API AutenticarMotorista', error);
      this.global.setErroRespostaAPI(error);
    }

  }


}
