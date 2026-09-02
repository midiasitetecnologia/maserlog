import { async, ComponentFixture, TestBed } from '@angular/core/testing';
import { IonicModule } from '@ionic/angular';

import { ModeloPage } from './modelo.page';

describe('ModeloPage', () => {
  let component: ModeloPage;
  let fixture: ComponentFixture<ModeloPage>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ ModeloPage ],
      imports: [IonicModule.forRoot()]
    }).compileComponents();

    fixture = TestBed.createComponent(ModeloPage);
    component = fixture.componentInstance;
    fixture.detectChanges();
  }));

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
