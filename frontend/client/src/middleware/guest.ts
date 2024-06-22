import { defineNuxtRouteMiddleware, navigateTo } from 'nuxt/app'
import { useCookie } from "#app";
import { ACCESS_TOKEN_KEY } from "~/src/lib/api/config";

export default defineNuxtRouteMiddleware(() => {
    const token = useCookie(ACCESS_TOKEN_KEY).value;

    if (token) {
        navigateTo('/');
        return false;
    }

    return true;  // Continue with the navigation if no token is found
});