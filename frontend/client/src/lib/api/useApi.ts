import { ACCESS_TOKEN_KEY } from './config'
import axios from 'axios'
import type { InternalAxiosRequestConfig, AxiosRequestHeaders } from 'axios'
import { computed } from 'vue'
import { useSessionStore } from '~/src/entities/Session'
import { useRuntimeConfig } from '#app'
import { useRouter } from 'vue-router'

export function useApi<ResponseT, MappedResponseT = ResponseT>({
                                                                   url,
                                                                   options,
                                                                   auth = false
                                                               }: {
    url: (() => string) | string
    options?: { transformResponse?: (data: any) => MappedResponseT }
    auth?: boolean
}) {
    const sessionStore = useSessionStore()
    const router = useRouter()
    const config = useRuntimeConfig()
    const apiBase = process.client ? '/api/v1' : config.public.apiBase

    const axiosInstance = axios.create({
        baseURL: apiBase
    })

    axiosInstance.interceptors.request.use((config: InternalAxiosRequestConfig) => {
        if (auth) {
            const token = sessionStore.accessToken || (process.client ? localStorage.getItem(ACCESS_TOKEN_KEY) : null)
            if (token) {
                if (!config.headers) {
                    config.headers = {} as AxiosRequestHeaders
                }
                (config.headers as AxiosRequestHeaders).Authorization = `Bearer ${token}`
            }
        }
        if (!config.headers) {
            config.headers = {} as AxiosRequestHeaders
        }
        (config.headers as AxiosRequestHeaders).accept = 'application/json'
        return config
    })

    axiosInstance.interceptors.response.use(
        response => {
            if (response.data.message && response.data.message !== 'OK') {
                console.log('Success:', response.data.message)  // Replace with any other message display method
            }
            return response
        },
        error => {
            const response = error.response
            const responseErrorMessage = response?.data?.messageError?.error

            const nativeMessages: Record<number, string> = {
                401: 'Authorization error',
                404: 'Page not found',
                500: 'Server error'
            }

            const responseError = {
                message: nativeMessages[response?.status]
            }

            const unknownError = 'Unknown error'

            if (typeof responseError.message === 'undefined') {
                if (responseErrorMessage) responseError.message = responseErrorMessage
                else responseError.message = unknownError + `: ${response?.status}`
            }

            console.error('Error:', responseError.message)  // Replace with any other message display method

            if (response?.status === 401) {
                sessionStore.setAuthorized(false)
                router.push('/login')
            }

            return Promise.reject(error)
        }
    )

    const urlPath = computed<string>(() => {
        return typeof url === 'function' ? url() : url
    })


    const makeRequest = async (method: string, data?: object, requestOptions?: object): Promise<MappedResponseT | null> => {
        try {
            const response = await axiosInstance.request({
                method,
                url: urlPath.value,
                data,
                ...options,
                ...requestOptions
            })
            return options?.transformResponse ? options.transformResponse(response.data) : response.data
        } catch (error) {
            console.error(error)
            return null
        }
    }

    return {
        fetch: (requestOptions?: object) => makeRequest('GET', undefined, requestOptions),
        post: (data: object, requestOptions?: object) => makeRequest('POST', data, requestOptions),
        put: (data: object, requestOptions?: object) => makeRequest('PUT', data, requestOptions),
        delete: (requestOptions?: object) => makeRequest('DELETE', undefined, requestOptions)
        // other methods can be added similarly
    }
}