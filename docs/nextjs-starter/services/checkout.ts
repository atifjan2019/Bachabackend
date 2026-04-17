import { apiPost } from '../api-client';
import type {
  AbandonedCartInput,
  AbandonedCartResponseData,
  ApiMessageResponse,
  CreateOrderInput,
  CreateOrderResponseData,
} from '../api-types';

export async function createOrder(payload: CreateOrderInput): Promise<CreateOrderResponseData> {
  const response = await apiPost<ApiMessageResponse<CreateOrderResponseData>>('/orders', payload);
  return response.data;
}

export async function captureAbandonedCart(
  payload: AbandonedCartInput,
): Promise<AbandonedCartResponseData> {
  const response = await apiPost<ApiMessageResponse<AbandonedCartResponseData>>(
    '/abandoned-carts',
    payload,
  );

  return response.data;
}
