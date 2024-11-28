import { Routes } from '@angular/router';

export const routes: Routes = [
  { path: '', pathMatch: 'full', redirectTo: '/order' },
  { path: 'order', loadChildren: () => import('./pages/order/order.routes').then(m => m.ORDER_ROUTES) },
  { path: 'store', loadChildren: () => import('./pages/store/store.routes').then(m => m.STORE_ROUTES) }
];
