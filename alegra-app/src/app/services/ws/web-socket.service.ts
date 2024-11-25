import { Injectable } from '@angular/core';
import Pusher from 'pusher-js';

@Injectable({
  providedIn: 'root'
})
export class WebSocketService {
  private pusher: Pusher;

  constructor() {
    this.pusher = new Pusher('anyKey', {
      cluster: 'mt1',
      wsHost: 'localhost',
      wsPort: 6001,
      forceTLS: false,
      disableStats: true,
    });
  }

  subscribeToOrders(channelName: string, eventName: string, callback: (data: any) => void) {
    const channel = this.pusher.subscribe(channelName);
    channel.bind(eventName, callback);
  }
}