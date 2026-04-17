export type ApiListResponse<T> = {
  current_page: number;
  data: T[];
  first_page_url: string;
  from: number | null;
  last_page: number;
  last_page_url: string;
  links: Array<{ url: string | null; label: string; active: boolean }>;
  next_page_url: string | null;
  path: string;
  per_page: number;
  prev_page_url: string | null;
  to: number | null;
  total: number;
};

export type ApiSingleResponse<T> = {
  data: T;
};

export type ApiMessageResponse<T> = {
  message: string;
  data: T;
};

export type SettingMap = Record<string, string>;

export type Category = {
  id: number;
  name: string;
  slug: string;
  description: string | null;
  image: string | null;
  meta_title: string | null;
  meta_description: string | null;
};

export type Product = {
  id: number;
  name: string;
  slug: string;
  description: string | null;
  short_description: string | null;
  price: string;
  original_price: string | null;
  category: string | null;
  image_url: string | null;
  gallery: string[] | null;
  stock_quantity: number;
  sku: string | null;
  size_options: string[] | null;
  color_options: string[] | null;
  material: string | null;
  care_instructions: string | null;
  is_new: boolean;
  is_featured: boolean;
  meta_title: string | null;
  meta_description: string | null;
  created_at: string;
  updated_at: string;
};

export type BlogPost = {
  id: number;
  title: string;
  slug: string;
  content: string | null;
  image: string | null;
  status: boolean;
  created_at: string;
  updated_at: string;
};

export type OrderItemInput = {
  name: string;
  price: number;
  quantity: number;
  size?: string;
};

export type CreateOrderInput = {
  customer_name: string;
  customer_email: string;
  customer_phone?: string;
  shipping_address: string;
  city?: string;
  country?: string;
  items: OrderItemInput[];
  subtotal?: number;
  shipping_fee?: number;
  total_amount: number;
  payment_method?: string;
};

export type CreateOrderResponseData = {
  id: number;
  status: string;
};

export type AbandonedCartInput = {
  email: string;
  phone?: string;
  cart_data: Record<string, unknown>;
};

export type AbandonedCartResponseData = {
  id: number;
};
