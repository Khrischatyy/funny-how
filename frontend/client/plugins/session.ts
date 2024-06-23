import {defineNuxtPlugin, navigateTo} from 'nuxt/app'
import {
    useSessionStore, USER_INFO_KEY,
    ROLE_INFO_KEY, ACCESS_TOKEN_KEY, RESERVES_KEY, PAYMENT_SESSION, BRAND_KEY
} from '~/src/entities/Session/model'
import {UserRoleEnum} from "~/src/entities/@abstract/User";
import {useCookie} from "#app";
import {useApi} from "~/src/lib/api";
import type { ResponseDto } from "~/src/lib/api/types";

type meResponse = {
    role: string;
    has_company: boolean;
    company_slug: number;
}
function getMe() {
    const api = useApi<ResponseDto<meResponse>>({ url: '/me', 'auth': true });

    return api.fetch();
}

export default defineNuxtPlugin((nuxtApp) => {
    nuxtApp.hook('app:beforeMount', async () => {
        let token = useCookie(ACCESS_TOKEN_KEY).value
        console.log('tokentokentoken', token)
        if(token){
            await getMe().then((response) => {
                const sessionStore = useSessionStore()
                sessionStore.setUserRole(response?.data.role as UserRoleEnum)
                sessionStore.setAuthorized(true)
                if(response?.data.has_company) {
                    sessionStore.setBrand(response?.data.company_slug.toString())
                }
                const route = nuxtApp._route
                if (response?.data.company_slug && route.path === '/create') {
                    navigateTo(`/@${response?.data.company_slug}`)
                }
            })
        }

        const sessionStore = useSessionStore()
        sessionStore.setAuthorized(true)
        // Проверяем, выполняется ли код на клиенте
        if (process.client) {
            const accessToken = localStorage.getItem(ACCESS_TOKEN_KEY)

            const reserves = localStorage.getItem(RESERVES_KEY)
            const paymentSession = localStorage.getItem(PAYMENT_SESSION)

            if(reserves) {
                sessionStore.setReservations(JSON.parse(reserves))
            }

            if(paymentSession) {
                sessionStore.setPaymentSession(JSON.parse(paymentSession))
            }

            if (accessToken) {
                // Если токен найден в localStorage
                sessionStore.setAccessToken(accessToken) // Сохраняем токен в сторе
                sessionStore.setAuthorized(true) // Пользователь авторизован
            } else {
                // Если токен не найден в localStorage
                sessionStore.setAccessToken(null)
                sessionStore.setAuthorized(false) // Пользователь не авторизован
            }
        } else {
            sessionStore.setAuthorized(true)
            if(token) {
                sessionStore.setAccessToken(token)
                sessionStore.setAuthorized(true)
            }
        }
    })
})
