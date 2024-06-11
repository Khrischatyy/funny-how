import { ACCESS_TOKEN_KEY } from './config'
import { computed, ref } from 'vue'
import { useSessionStore } from '~/src/entities/Session'
import { useCookie, useRuntimeConfig } from '#app'
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
    const apiBase = process.client ? 'http://127.0.0.1/api/v1' : config.public.apiBase

    const urlPath = computed<string>(() => {
        return typeof url === 'function' ? url() : url
    })

    const fetchOptions = ref({
        baseURL: apiBase,
        headers: {
            accept: 'application/json'
        } as Record<string, string>
    })

    if (auth) {
        let token: string | null | undefined = null
        if (process.server) {
            // Use server-side storage to get token
            const sessionCookie = useCookie(ACCESS_TOKEN_KEY)
            token = sessionCookie.value
        } else {
            token = sessionStore.accessToken || localStorage.getItem(ACCESS_TOKEN_KEY)
        }
        if (token) {
            fetchOptions.value.headers.Authorization = `Bearer ${token}`
        }
    }

    const handleResponse = (response: any) => {
        if (response.data.message && response.data.message !== 'OK') {
            console.log('Success:', response.data.message)  // Replace with any other message display method
        }
        return response.data
    }

    const handleError = (error: any) => {
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

    const makeRequest = async (method: 'GET' | 'POST' | 'PUT' | 'DELETE', data?: object, requestOptions?: object): Promise<MappedResponseT | null> => {
        try {
            const response = await $fetch(urlPath.value, {
                method,
                baseURL: fetchOptions.value.baseURL,
                headers: fetchOptions.value.headers,
                body: data,
                ...options,
                ...requestOptions
            }).catch(handleError)
            return options?.transformResponse ? options.transformResponse(response) : response
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