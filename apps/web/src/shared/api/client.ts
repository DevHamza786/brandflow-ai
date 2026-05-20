import axios, { type AxiosError, type AxiosInstance } from 'axios';
import { env } from '@/shared/config/env';
import { problemToError, unwrapApiResponse } from '@/shared/api/normalize';
import type { ApiError, ApiProblemResponse } from '@/shared/types/api';

const WORKSPACE_HEADER = 'X-Workspace-Id';

function createApiClient(): AxiosInstance {
  const client = axios.create({
    baseURL: env.apiBaseUrl,
    headers: {
      Accept: 'application/json',
      'Content-Type': 'application/json',
    },
    timeout: 60_000,
  });

  client.interceptors.request.use((config) => {
    config.headers.set(WORKSPACE_HEADER, env.workspaceId);
    return config;
  });

  client.interceptors.response.use(
    (response) => response,
    (error: AxiosError<ApiProblemResponse>) => {
      const data = error.response?.data;
      if (data && (data.detail || data.title || data.type)) {
        return Promise.reject(problemToError(data));
      }

      const apiError: ApiError = {
        message: error.message || 'Network error',
        status: error.response?.status,
      };
      return Promise.reject(apiError);
    },
  );

  return client;
}

export const apiClient = createApiClient();

export async function apiGet<T>(url: string): Promise<T> {
  const { data } = await apiClient.get<unknown>(url);
  return unwrapApiResponse<T>(data);
}

export async function apiPost<T>(url: string, body?: unknown): Promise<T> {
  const { data } = await apiClient.post<unknown>(url, body ?? {});
  return unwrapApiResponse<T>(data);
}

export async function apiPatch<T>(url: string, body?: unknown): Promise<T> {
  const { data } = await apiClient.patch<unknown>(url, body ?? {});
  return unwrapApiResponse<T>(data);
}

export async function apiDelete<T>(url: string): Promise<T> {
  const { data } = await apiClient.delete<unknown>(url);
  return unwrapApiResponse<T>(data);
}
