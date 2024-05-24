import { defineStore } from 'pinia';
import {useRuntimeConfig} from "#imports";
import axios from "axios";
import {useSessionStore} from "~/src/entities/Session";
import { useRouter } from 'vue-router'
import {navigateTo} from "nuxt/app";
const router = useRouter()

type inputField = {
    name: string;
    title: string;
    type: string;
}
export type StudioFormValues = {
    logo: File | null;
    company: string;
    address: string;
    country: string;
    city: string;
    street: string;
    about: string;
    longitude: string;
    latitude: string;
    logo_preview: string | null
};
type formValues = {
    inputValues: StudioFormValues
    errors: string[];
    inputFields: inputField[];
}



export const useCreateStudioFormStore = defineStore({
    id: 'create-studio-store',
    state: (): formValues => ({
            inputValues: {
                company: '',
                logo: null,
                logo_preview: null,
                about: '',
                address:'',
                country: '',
                city: '',
                street: '',
                longitude: '',
                latitude: '',
            },
            errors: [],
            inputFields: [
                {name: 'name', title: 'Name', type: 'text'},
                {name: 'email', title: 'Email', type: 'email'},
                {name: 'password', title: 'Password', type: 'password'},
                {name: 'password_confirmation', title: 'Confirm Password', type: 'password'}]
    }),
    actions: {
        async submit(){
            const config = useRuntimeConfig()

            const formData = new FormData();
            formData.append('logo', this.inputValues.logo as File);
            formData.append('company', this.inputValues.company);
            formData.append('address', this.inputValues.address);
            formData.append('country', this.inputValues.country);
            formData.append('city', this.inputValues.city);
            formData.append('street', this.inputValues.street);
            formData.append('about', this.inputValues.about);
            formData.append('longitude', this.inputValues.longitude);
            formData.append('latitude', this.inputValues.latitude);

            let requestConfig = {
                method: 'post',
                credentials: true,
                url: `${config.public.apiBase}/v1/brand`,
                data: formData,
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'multipart/form-data',
                    'Authorization': 'Bearer ' + useSessionStore().accessToken
                }
            };
            axios.defaults.headers.common['X-Api-Client'] = `web`
            axios.request(requestConfig)
                .then((response) => {
                    //function that redirect to route /@[slug]
                    console.log('response123', response)
                    useSessionStore().setBrand(response.data?.data?.slug)
                    navigateTo(`/@${response.data?.data?.slug}/setup/${response.data?.data?.id}/hours`)

                })
                .catch((error) => {
                    console.log(error);
                    this.errors = error?.response?.data?.errors;
                });
        }
    }
})