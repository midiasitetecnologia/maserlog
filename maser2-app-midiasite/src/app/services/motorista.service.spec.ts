import { TestBed } from '@angular/core/testing';

import { MotoristaService } from './motorista.service';

describe('MotoristaService', () => {
  beforeEach(() => TestBed.configureTestingModule({}));

  it('should be created', () => {
    const service: MotoristaService = TestBed.get(MotoristaService);
    expect(service).toBeTruthy();
  });
});
