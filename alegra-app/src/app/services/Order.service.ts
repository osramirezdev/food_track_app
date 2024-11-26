import { Injectable, OnInit } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { BehaviorSubject, Observable, Subject } from 'rxjs';
import { OrderDTO } from '../dtos';

@Injectable({
  providedIn: 'root',
})
export class OrderService {
  private orders: Array<OrderDTO> = [];
  private ordersSubject = new BehaviorSubject<OrderDTO[]>([]);
   private baseUrl = 'http://localhost:8001/api/order';

  constructor(private http: HttpClient) {}

  get orders$(): Observable<OrderDTO[]> {
    return this.ordersSubject.asObservable();
  }

  fetchOrders(): void {
    console.log("pidiendo ordenes")
    this.http.get<OrderDTO[]>(`${this.baseUrl}/all`).subscribe({
      next: (response) => {
        console.log("ordenes", response)
        this.orders = response;
        this.ordersSubject.next(this.orders);
      },
      error: (err) => console.error('Error al obtener órdenes:', err),
    });
  }

  createOrder(): void {
    this.http.post<OrderDTO>(`${this.baseUrl}/create`, {}).subscribe({
      next: (newOrder) => {
        this.orders.push(newOrder);
        this.ordersSubject.next(this.orders);
      },
      error: (err) => console.error('Error al crear orden:', err),
    });
  }

  getOrders(): Observable<OrderDTO[]> {
    return this.http.get<OrderDTO[]>(`${this.baseUrl}/all`);
  }

}
