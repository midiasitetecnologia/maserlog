import { Injectable } from '@angular/core';
import { AlertController } from '@ionic/angular';
//import { BarcodeScanner, BarcodeFormat } from '@capacitor-mlkit/barcode-scanning';
import { CapacitorBarcodeScanner } from '@capacitor/barcode-scanner';

@Injectable({
  providedIn: 'root',
})
export class BarcodeScannerService {

  scannedResult: string = '';
  isScanning: boolean = false;

  constructor(
    public alertController: AlertController) {
    console.log('BarcodeScannerService -> constructor');
  }

  // Usando @capacitor-mlkit/barcode-scanning
  /*
  instalarGoogleBarcodeScannerModule() {
    BarcodeScanner.isGoogleBarcodeScannerModuleAvailable().then((result) => {
      if (result.available == false) {
        BarcodeScanner.installGoogleBarcodeScannerModule();
      }
    });
  }

  async barcodeRequestPermissions() {
    const { camera } = await BarcodeScanner.requestPermissions();
    return camera === 'granted' || camera === 'limited';
  }  
  
  async barcodeAlertPermissionDenied() {
    const alert = await this.alertController.create({
      header: 'Permissão negada',
      message: 'Conceda permissão à câmera para usar o leitor de código de barras.',
      buttons: ['OK'],
    });
    await alert.present();
  }
  
  async lerBarcode128() {
    
    const permission = await this.barcodeRequestPermissions();
    
    if (!permission) {
      this.barcodeAlertPermissionDenied();
      return '';
    }

    const { barcodes } = await BarcodeScanner.scan({ formats: [BarcodeFormat.Code128] });
    console.log('lerBarcode128 -> barcodes', barcodes);

    if (barcodes[0]) {
      return barcodes[0].rawValue
    } 
    else {
      return ''      
    }
  }
  */

  async lerBarcode128() {
    try {
      const result = await CapacitorBarcodeScanner.scanBarcode({
        hint: 5,
        cameraDirection: 1,
        scanOrientation: 2,
      });
      console.log('lerBarcode128 -> return', result);
      return result.ScanResult;
    } 
    catch (e) {
      throw e;
    }
  }

}
