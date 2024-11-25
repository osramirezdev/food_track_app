import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable, Subject } from 'rxjs';
import Pusher from 'pusher-js';

@Injectable({
  providedIn: 'root',
})
export class OrderService {
  private pusher: Pusher;
  private orderUpdates = new Subject<any>();

  constructor(private http: HttpClient) {
    this.pusher = new Pusher('anyKey', {
      cluster: 'mt1',
      wsHost: 'localhost',
      wsPort: 6001,
      forceTLS: false,
      disableStats: true,
    });

    const channel = this.pusher.subscribe('orders');
    channel.bind('App\\Events\\OrderUpdated', (data: any) => {
      this.orderUpdates.next(data);
    });
  }

  createOrder(): Observable<any> {
    return this.http.post('http://localhost:8000/api/order/create', {});
  }

  getOrderUpdates(): Observable<any> {
    return this.orderUpdates.asObservable();
  }
}
