import { Component, OnInit, OnDestroy } from '@angular/core';
import { NzDescriptionsModule } from 'ng-zorro-antd/descriptions';
import { NzCardModule } from 'ng-zorro-antd/card';
import { OrderStatusEnum } from '../../enums';
import { OrderService } from '../../services/Order.service';
import { NzGridModule } from 'ng-zorro-antd/grid';
import { OrderDTO } from '../../dtos';
import { CommonModule } from '@angular/common';

@Component({
  selector: 'app-welcome',
  standalone: true,
  imports: [CommonModule, NzCardModule, NzDescriptionsModule, NzDescriptionsModule, NzGridModule],
  templateUrl: './order.component.html',
  styleUrls: ['./order.component.scss']
})
export class OrderComponent implements OnInit, OnDestroy {
  orders: OrderDTO[] = [];
  ordersInProcess: OrderDTO[] = [];
  completedOrders: OrderDTO[] = [];
  otherOrders: OrderDTO[] = [];
  loading: boolean = false;
  intervalId: any;

  constructor(private orderService: OrderService) { }

  ngOnInit(): void {
    this.fetchOrders();
    this.intervalId = setInterval(() => this.fetchOrders(), 3000);
  }

  ngOnDestroy(): void {
    if (this.intervalId) {
      clearInterval(this.intervalId);
    }
  }

  fetchOrders(): void {
    this.loading = true;
    this.orderService.getOrders().subscribe({
      next: (orders) => {
        this.orders = orders;
        this.filterOrders();
        this.loading = false
      },
      error: (error) => {
        console.error('Error fetching orders:', error);
        this.loading = false;
      }
    });
  }

  createOrder(): void {
    this.orderService.createOrder();
  }

  filterOrders(): void {
    this.ordersInProcess = this.orders.filter(order => order.status === OrderStatusEnum.PROCESANDO);
    this.completedOrders = this.orders.filter(order => order.status === OrderStatusEnum.LISTO);
    this.otherOrders = this.orders.filter(order => order.status !== OrderStatusEnum.PROCESANDO && order.status !== OrderStatusEnum.LISTO);
  }

}
