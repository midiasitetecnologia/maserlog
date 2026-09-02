import { TestBed } from '@angular/core/testing';

import { BarcodeScannerService } from './barcodescanner.service';

describe('BarcodeScannerService', () => {
  let service: BarcodeScannerService;

  beforeEach(() => {
    TestBed.configureTestingModule({});
    service = TestBed.inject(BarcodeScannerService);
  });

  it('should be created', () => {
    expect(service).toBeTruthy();
  });
});
