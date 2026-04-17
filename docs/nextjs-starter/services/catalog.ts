import { apiGet } from '../api-client';
import type { ApiListResponse, ApiSingleResponse, Category, Product } from '../api-types';

export type ProductQuery = {
  category?: string;
  search?: string;
  is_new?: boolean;
  per_page?: number;
};

function toQueryString(query: ProductQuery): string {
  const params = new URLSearchParams();

  if (query.category) params.set('category', query.category);
  if (query.search) params.set('search', query.search);
  if (typeof query.is_new === 'boolean') params.set('is_new', query.is_new ? '1' : '0');
  if (query.per_page) params.set('per_page', String(query.per_page));

  const stringified = params.toString();
  return stringified ? `?${stringified}` : '';
}

export async function getCategories(): Promise<Category[]> {
  const response = await apiGet<ApiSingleResponse<Category[]>>('/categories');
  return response.data;
}

export async function getProducts(query: ProductQuery = {}): Promise<ApiListResponse<Product>> {
  const qs = toQueryString(query);
  return apiGet<ApiListResponse<Product>>(`/products${qs}`);
}

export async function getProductDetail(slugOrId: string | number): Promise<Product> {
  const response = await apiGet<ApiSingleResponse<Product>>(`/products/${slugOrId}`);
  return response.data;
}
