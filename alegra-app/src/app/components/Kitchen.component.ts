import { Component } from '@angular/core';
import { NzCardModule } from 'ng-zorro-antd/card';  // Importa el módulo de tarjeta de NG-ZORRO
import { NzTagModule } from 'ng-zorro-antd/tag';    // Importa el módulo de etiquetas de NG-ZORRO
import { OrderStatusEnum } from '../enums';

@Component({
  selector: 'app-kitchen-status',
  template: `
    <nz-card *ngFor="let order of kitchenOrders" [nzTitle]="'Pedido #' + order.orderId">
      <p>Status: <nz-tag [nzColor]="getOrderStatusColor(order.status)">{{ order.status }}</nz-tag></p>
    </nz-card>
  `,
  styles: [`
    nz-card {
      margin-bottom: 16px;
    }
  `]
})
export class KitchenStatusComponent {
  kitchenOrders = [
    { orderId: 1, status: 'PROCESANDO' },
    { orderId: 2, status: 'LISTO' },
  ];

  getOrderStatusColor(status: OrderStatusEnum): string {
    switch (status) {
      case OrderStatusEnum.PROCESANDO:
        return 'processing';
      case OrderStatusEnum.LISTO:
        return 'success';
      default:
        return 'default';
    }
  }
}
