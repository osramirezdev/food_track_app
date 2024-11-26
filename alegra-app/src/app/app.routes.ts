import { Routes } from '@angular/router';

export const routes: Routes = [
  { path: '', pathMatch: 'full', redirectTo: '/order' },
  { path: 'order', loadChildren: () => import('./pages/order/order.routes').then(m => m.WELCOME_ROUTES) }
];
