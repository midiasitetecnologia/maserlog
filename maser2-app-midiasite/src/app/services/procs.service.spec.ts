import { TestBed } from '@angular/core/testing';

import { ProcsService } from './procs.service';

describe('ProcsService', () => {
  beforeEach(() => TestBed.configureTestingModule({}));

  it('should be created', () => {
    const service: ProcsService = TestBed.get(ProcsService);
    expect(service).toBeTruthy();
  });
});
