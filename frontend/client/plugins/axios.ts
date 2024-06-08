import { defineNuxtPlugin } from '#app';
import axios from 'axios';
import { useSessionStore } from '~/src/entities/Session/model';
import { useRouter } from 'vue-router';

export default defineNuxtPlugin(() => {
    const sessionStore = useSessionStore();
    const router = useRouter();

    const instance = axios.create({
        baseURL: process.env.AXIOS_BASEURL || 'http://localhost:3000',
    });

    instance.interceptors.request.use(config => {
        const token = sessionStore.accessToken;
        if (token) {
            config.headers.Authorization = `Bearer ${token}`;
        }
        return config;
    });

    instance.interceptors.response.use(
        response => response,
        error => {
            const code = error.response ? error.response.status : null;
            if (code === 401) {
                sessionStore.setAuthorized(false);
                router.push('/login');
            }
            return Promise.reject(error);
        }
    );

    return {
        provide: {
            axios: instance,
        },
    };
});
