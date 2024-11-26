// web-socket.service.ts
import { Injectable } from '@angular/core';
import Echo from 'laravel-echo';
import { Subject } from 'rxjs';
import { OrderDTO } from '../../dtos';

declare var Pusher: any;

@Injectable({
  providedIn: 'root'
})
export class WebSocketService {
  private echo: Echo<any> | null = null;
  private orderUpdatedSubject = new Subject<OrderDTO>();
  orderUpdated$ = this.orderUpdatedSubject.asObservable();

  constructor() {
    this.initializeWebSocket();
  }

  private initializeWebSocket() {
    this.echo = new Echo({
      broadcaster: 'pusher',
      key: "16a305cb64ba8c287109",
      cluster: "sa1",
      encrypted: true,
      forceTLS: true,
    });

    this.echo.channel('AllOrders')
      .listen('OrderUpdated', (event: OrderDTO) => {
        console.log('Pedido actualizado:', event);
        this.orderUpdatedSubject.next(event);
      });
  }
}
