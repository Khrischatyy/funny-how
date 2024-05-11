import { watch } from 'vue'
import { onBeforeMount } from 'vue'
import {defineNuxtRouteMiddleware, navigateTo} from 'nuxt/app'
import { useRouter } from 'vue-router'
import { useSessionStore } from '~/src/entities/Session'

export default defineNuxtRouteMiddleware(() => {
    if (process.server) return true

    const router = useRouter()
    const sessionStore = useSessionStore()

    onBeforeMount(() => {
        watch(() => sessionStore.isAuthorized, (isAuthorized) => {
            if (!isAuthorized) {
                navigateTo(router.resolve({ name: 'login' }).href)
            }
        }, { immediate: true })
    })

    return true
})