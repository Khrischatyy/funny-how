import { defineNuxtPlugin } from 'nuxt/app'
import {
    useSessionStore, USER_INFO_KEY,
    ROLE_INFO_KEY, ACCESS_TOKEN_KEY, RESERVES_KEY, PAYMENT_SESSION, BRAND_KEY
} from '~/src/entities/Session/model'
import {UserRoleEnum} from "~/src/entities/@abstract/User";
import {useCookie} from "#app";

export default defineNuxtPlugin((nuxtApp) => {
    nuxtApp.hook('app:beforeMount', async () => {
        const sessionStore = useSessionStore()
        sessionStore.setAuthorized(true)
        // Проверяем, выполняется ли код на клиенте
        if (process.client) {
            const accessToken = localStorage.getItem(ACCESS_TOKEN_KEY)
            const userRole = localStorage.getItem(ROLE_INFO_KEY) as UserRoleEnum
            const reserves = localStorage.getItem(RESERVES_KEY)
            const paymentSession = localStorage.getItem(PAYMENT_SESSION)
            const brand = localStorage.getItem(BRAND_KEY)

            if(brand) {
                sessionStore.setBrand(brand)
            }
            if(reserves) {
                sessionStore.setReservations(JSON.parse(reserves))
            }

            if(paymentSession) {
                sessionStore.setPaymentSession(JSON.parse(paymentSession))
            }

            if (accessToken) {
                // Если токен найден в localStorage
                sessionStore.setAccessToken(accessToken) // Сохраняем токен в сторе
                sessionStore.setUserRole(userRole) // Сохраняем информацию о пользователе в сторе
                sessionStore.setAuthorized(true) // Пользователь авторизован
            } else {
                // Если токен не найден в localStorage
                sessionStore.setAccessToken(null)
                sessionStore.setAuthorized(false) // Пользователь не авторизован
            }
        } else {
            sessionStore.setAuthorized(true)
            let token = useCookie(ACCESS_TOKEN_KEY).value
            if(token) {
                sessionStore.setAccessToken(token)
                sessionStore.setAuthorized(true)
            }
            // На стороне сервера пользователь считается неавторизованным
            sessionStore.setAuthorized(false)
        }
    })
})
