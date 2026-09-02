import { Component, OnInit } from '@angular/core';
import { ColetaService } from '../../services/coleta.service';

@Component({
  selector: 'app-proximas',
  templateUrl: './proximas.page.html',
  styleUrls: ['./proximas.page.scss'],
})
export class ProximasPage implements OnInit {

  constructor(
    public coleta: ColetaService) {
    console.log('ProximasPage -> constructor');
  }

  ngOnInit() {
  }  

}
