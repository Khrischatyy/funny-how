import { useNuxtApp } from '#app';

export async function getSideMenu() {
    const { $axios } = useNuxtApp();
    try {
        const response = await $axios.get('menu');
        return response.data;
    } catch (error) {
        console.error('Error fetching side menu:', error);
        throw error;
    }
}
