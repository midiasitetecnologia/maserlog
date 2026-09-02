import { async, ComponentFixture, TestBed } from '@angular/core/testing';
import { IonicModule } from '@ionic/angular';

import { NotaFiscalPage } from './nota-fiscal.page';

describe('NotaFiscalPage', () => {
  let component: NotaFiscalPage;
  let fixture: ComponentFixture<NotaFiscalPage>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ NotaFiscalPage ],
      imports: [IonicModule.forRoot()]
    }).compileComponents();

    fixture = TestBed.createComponent(NotaFiscalPage);
    component = fixture.componentInstance;
    fixture.detectChanges();
  }));

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
