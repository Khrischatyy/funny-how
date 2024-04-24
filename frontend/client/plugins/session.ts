import { defineNuxtPlugin } from 'nuxt/app'
import {
    useSessionStore, USER_INFO_KEY,
    ROLE_INFO_KEY, ACCESS_TOKEN_KEY
} from '~/src/entities/Session/model'

export default defineNuxtPlugin((nuxtApp) => {
    nuxtApp.hook('app:beforeMount', async () => {
        const sessionStore = useSessionStore()

        // Проверяем, выполняется ли код на клиенте
        if (process.client) {
            const accessToken = localStorage.getItem(ACCESS_TOKEN_KEY)
            const userInfo = localStorage.getItem(USER_INFO_KEY)

            if (accessToken) {
                // Если токен найден в localStorage
                sessionStore.setAccessToken(accessToken) // Сохраняем токен в сторе
                sessionStore.setUserInfo(userInfo) // Сохраняем информацию о пользователе в сторе
                sessionStore.setAuthorized(true) // Пользователь авторизован
            } else {
                // Если токен не найден в localStorage
                sessionStore.setAccessToken(null)
                sessionStore.setAuthorized(false) // Пользователь не авторизован
            }
        } else {
            // На стороне сервера пользователь считается неавторизованным
            sessionStore.setAuthorized(false)
        }
    })
})
