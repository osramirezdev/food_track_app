import { Component, OnInit, OnDestroy } from '@angular/core';
import { NzDescriptionsModule } from 'ng-zorro-antd/descriptions';
import { NzCardModule } from 'ng-zorro-antd/card';
import { NzTableModule } from 'ng-zorro-antd/table';
import { StoreService } from '../../services';
import { NzGridModule } from 'ng-zorro-antd/grid';
import { IngredientsDTO } from '../../dtos';
import { CommonModule } from '@angular/common';

@Component({
  selector: 'app-welcome',
  standalone: true,
  imports: [CommonModule, NzCardModule, NzDescriptionsModule, NzDescriptionsModule, NzGridModule, NzTableModule],
  templateUrl: './store.component.html',
  styleUrls: ['./store.component.scss']
})
export class StoreComponent implements OnInit, OnDestroy {
  ingredients: IngredientsDTO[] = [];
  loading: boolean = false;
  intervalId: any;
  storeService: StoreService;

  constructor(private $store: StoreService) {
    this.storeService = $store;
  }

  ngOnInit(): void {
    this.fetchIngredients();
    this.intervalId = setInterval(() => this.fetchIngredients(), 5000);
  }

  ngOnDestroy(): void {
    if (this.intervalId) {
      clearInterval(this.intervalId);
    }
  }

  fetchIngredients(): void {
    this.loading = true;
    this.storeService.getingredients().subscribe({
      next: (ingredients) => {
        this.ingredients = ingredients;
        this.loading = false
      },
      error: (error) => {
        console.error('Error fetching ingredients:', error);
        this.loading = false;
      }
    });
  }

}
