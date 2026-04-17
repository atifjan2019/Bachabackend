import { apiGet } from '../api-client';
import type { ApiListResponse, ApiSingleResponse, BlogPost, SettingMap } from '../api-types';

export async function getHealth(): Promise<{ ok: true }> {
  return apiGet<{ ok: true }>('/health');
}

export async function getSettings(): Promise<SettingMap> {
  const response = await apiGet<ApiSingleResponse<SettingMap>>('/settings');
  return response.data;
}

export async function getBlogPosts(perPage = 20): Promise<ApiListResponse<BlogPost>> {
  return apiGet<ApiListResponse<BlogPost>>(`/blog-posts?per_page=${perPage}`);
}

export async function getBlogPostBySlug(slug: string): Promise<BlogPost> {
  const response = await apiGet<ApiSingleResponse<BlogPost>>(`/blog-posts/${slug}`);
  return response.data;
}
