import { ACCESS_TOKEN_KEY } from './config'
import { computed, ref } from 'vue'
import { useSessionStore } from '~/src/entities/Session'
import { useCookie, useRuntimeConfig } from '#app'
import { useRouter } from 'vue-router'
import {navigateTo} from "nuxt/app";

interface FetchOptions {
    baseURL: string;
    headers: Record<string, string>;
}

interface RequestOptions {
    method: 'GET' | 'POST' | 'PUT' | 'DELETE';
    params?: Record<string, any>;
    body?: any;
}

interface ApiResponse<T> {
    data: T;
    message: string;
    status: number;
}

export function useApi<ResponseT, MappedResponseT = ResponseT>({
                                                                   url,
                                                                   options,
                                                                   auth = false
                                                               }: {
    url: (() => string) | string
    options?: { transformResponse?: (data: ResponseT) => MappedResponseT };
    auth?: boolean
}) {
    const sessionStore = useSessionStore()
    const config = useRuntimeConfig()
    const apiBase = process.client ? config.public.apiBaseClient : config.public.apiBase

    const urlPath = computed<string>(() => {
        return typeof url === 'function' ? url() : url
    })

    const fetchOptions = ref<FetchOptions>({
        baseURL: apiBase,
        headers: { accept: 'application/json' },
    });

    if (auth) {
        const sessionCookie = useCookie(ACCESS_TOKEN_KEY);
        const token = sessionCookie.value;
        if (token) {
            fetchOptions.value.headers.Authorization = `Bearer ${decodeURIComponent(token)}`;
        }
    }

    const handleError = (error: any): Promise<Awaited<{ message: string; status: any }>> => {
        console.error("API Error:", error);

        // Extract the error response which is nested under `_data`
        const response = error.response._data;
        const status = response?.status || error.response?.status;
        const errorData = response;

        console.log('Error:', response);

        // Define common error messages based on status codes
        const nativeMessages: Record<number, string> = {
            401: 'Authorization error',
            404: 'Page not found',
            422: 'Validation error', // Specific message for validation errors
            500: 'Server error'
        };
        console.log('status', status)

        if (status === 404) {
            if(process.client)
                navigateTo('/404');
            return Promise.resolve({ status, message: 'Redirecting to 404 page' }); // Consider handling as resolved to prevent further error propagation.
        }
        if (status === 401) {
            sessionStore.setAuthorized(false);
            sessionStore.setAccessToken(null);
            if(process.client)
                navigateTo('/login');
            return Promise.resolve({ status, message: 'Redirecting to login page' });
        }

        // Check for specific error messages from the server
        if (errorData) {
            if (errorData.errors) {
                // Server provided specific field errors
                return Promise.reject({
                    status: status,
                    message: errorData.message,
                    errors: errorData.errors
                });
            } else if (errorData.message) {
                // Server provided a general error message
                return Promise.reject({
                    status: status,
                    message: errorData.message
                });
            }
        }

        // Default error handling if no specific messages are found
        const message = nativeMessages[status] || `Unknown error: ${status}`;
        return Promise.reject({ status, message });
    }

    const makeRequest = async (requestOptions: RequestOptions): Promise<MappedResponseT | null> => {
        try {
            const response = await $fetch<ApiResponse<ResponseT>>(urlPath.value, {
                ...fetchOptions.value,
                ...requestOptions,
            });

            if (options?.transformResponse) {
                return options.transformResponse(response.data);
            }

            return response as MappedResponseT; // Call handleResponse here
        } catch (error) {
            await handleError(error); // Process and rethrow
            throw error; // Make sure to rethrow the error
        }
    }

    return {
        fetch: (requestOptions?: RequestOptions) => makeRequest({ ...requestOptions, method: 'GET' }),
        post: (data: any, requestOptions?: RequestOptions) => makeRequest({ ...requestOptions, method: 'POST', body: data }),
        put: (data: any, requestOptions?: RequestOptions) => makeRequest({ ...requestOptions, method: 'PUT', body: data }),
        delete: (requestOptions?: RequestOptions) => makeRequest({ ...requestOptions, method: 'DELETE' })
    }
}