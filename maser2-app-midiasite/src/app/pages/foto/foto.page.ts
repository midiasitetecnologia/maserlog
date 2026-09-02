import { Component, OnInit } from '@angular/core';
import { Location } from '@angular/common';

import { GlobalService } from '../../services/global.service';

@Component({
  selector: 'app-foto',
  templateUrl: './foto.page.html',
  styleUrls: ['./foto.page.scss'],
})
export class FotoPage implements OnInit {

  constructor(
    public location: Location,
    public global: GlobalService) { 
    console.log('FotoPage -> constructor');
  }

  ngOnInit() {
    console.log('FotoPage -> ngOnInit');
  }

}
