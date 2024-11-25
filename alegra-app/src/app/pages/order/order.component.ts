import { Component, OnInit } from '@angular/core';
import { WebSocketService } from '../../services';
import { OrderDTO } from '../../dtos/';

@Component({
  selector: 'app-welcome',
  standalone: true,
  templateUrl: './order.component.html',
  styleUrls: ['./order.component.scss']
})
export class OrderComponent implements OnInit {
  orders: Array<OrderDTO> = [];

  constructor(private wsService: WebSocketService) {}

  ngOnInit() {
    this.wsService.subscribeToOrders('orders', 'App\\Events\\OrderUpdated', (data: OrderDTO) => {
      console.log("data from socket", data);
      this.orders.push(data);
    });
  }

}
