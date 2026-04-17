declare const process: { env?: Record<string, string | undefined> } | undefined;

const API_BASE = process?.env?.NEXT_PUBLIC_API_BASE_URL;

if (!API_BASE) {
  throw new Error('Missing NEXT_PUBLIC_API_BASE_URL environment variable.');
}

type RequestOptions = {
  method?: 'GET' | 'POST' | 'PUT' | 'PATCH' | 'DELETE';
  body?: unknown;
  cache?: RequestCache;
};

async function request<T>(path: string, options: RequestOptions = {}): Promise<T> {
  const { method = 'GET', body, cache = 'no-store' } = options;

  const response = await fetch(`${API_BASE}${path}`, {
    method,
    headers: {
      'Content-Type': 'application/json',
      Accept: 'application/json',
    },
    body: body ? JSON.stringify(body) : undefined,
    cache,
  });

  if (!response.ok) {
    let message = `${method} ${path} failed with status ${response.status}`;

    try {
      const payload = await response.json();
      message = payload?.message || payload?.error || message;
    } catch {
      // Keep fallback message when response body is not JSON.
    }

    throw new Error(message);
  }

  return response.json() as Promise<T>;
}

export function apiGet<T>(path: string, cache: RequestCache = 'no-store'): Promise<T> {
  return request<T>(path, { method: 'GET', cache });
}

export function apiPost<T>(path: string, body: unknown): Promise<T> {
  return request<T>(path, { method: 'POST', body });
}
