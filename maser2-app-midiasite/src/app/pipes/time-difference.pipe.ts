import { Pipe, PipeTransform } from '@angular/core';

import * as dayjs from 'dayjs';
import 'dayjs/locale/pt-br';
dayjs.locale('pt-br');

@Pipe({ name: 'appTimeDifference' })
export class TimeDifferencePipe implements PipeTransform {
  transform(value: any): number {
    //return dayjs(value).diff(dayjs(), 'day');
    return dayjs(value).diff(dayjs(), 'minute');
  }
}
