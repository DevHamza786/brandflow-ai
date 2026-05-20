import type { ApiError, ApiProblemResponse, ApiSuccessResponse } from '@/shared/types/api';

export function isRecord(value: unknown): value is Record<string, unknown> {
  return typeof value === 'object' && value !== null && !Array.isArray(value);
}

/** Normalize Laravel envelope — always returns typed data or throws ApiError */
export function unwrapApiResponse<T>(payload: unknown): T {
  if (isRecord(payload) && payload.success === true && 'data' in payload) {
    return (payload as ApiSuccessResponse<T>).data;
  }

  if (isRecord(payload) && (payload.type || payload.detail || payload.title)) {
    throw problemToError(payload as ApiProblemResponse);
  }

  if (isRecord(payload) && 'data' in payload) {
    return payload.data as T;
  }

  return payload as T;
}

export function problemToError(problem: ApiProblemResponse): ApiError {
  return {
    message: problem.detail ?? problem.title ?? 'Request failed',
    status: problem.status,
    detail: problem.detail,
    context: problem.context,
  };
}

/** Ensure array fields from API (empty object vs array) */
export function asArray<T>(value: unknown): T[] {
  return Array.isArray(value) ? (value as T[]) : [];
}

/** Ensure object fields from API */
export function asRecord(value: unknown): Record<string, unknown> {
  return isRecord(value) ? value : {};
}
