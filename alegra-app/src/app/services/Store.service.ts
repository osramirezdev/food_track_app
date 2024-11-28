import { Injectable, OnInit } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { BehaviorSubject, Observable, Subject } from 'rxjs';
import { IngredientsDTO } from '../dtos';

@Injectable({
  providedIn: 'root',
})
export class StoreService {
  private ingredients: Array<IngredientsDTO> = [];
  private ingredientsSubject = new BehaviorSubject<IngredientsDTO[]>([]);
   private baseUrl = 'http://localhost:8004/api/store';

  constructor(private http: HttpClient) {}

  get ingredients$(): Observable<IngredientsDTO[]> {
    return this.ingredientsSubject.asObservable();
  }

  fetchOrders(): void {
    console.log("consultando stock ingredientes")
    this.http.get<IngredientsDTO[]>(`${this.baseUrl}/all`).subscribe({
      next: (response) => {
        console.log("ingredientes", response)
        this.ingredients = response;
        this.ingredientsSubject.next(this.ingredients);
      },
      error: (err) => console.error('Error al obtener ingredientes:', err),
    });
  }

  getingredients(): Observable<IngredientsDTO[]> {
    return this.http.get<IngredientsDTO[]>(`${this.baseUrl}/all`);
  }

}
