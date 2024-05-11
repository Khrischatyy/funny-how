import { defineStore } from 'pinia';
import {useRuntimeConfig} from "#imports";
import axios from "axios";
import {useSessionStore} from "~/src/entities/Session";

type inputField = {
    name: string;
    title: string;
    type: string;
}
export type StudioFormValues = {
    studio_logo: File | null;
    studio_name: string;
    address: string;
    about: string;
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
                studio_name: '',
                address:'',
                studio_logo: null,
                about: ''
            },
            errors: [],
            inputFields: [
                {name: 'name', title: 'Name', type: 'text'},
                {name: 'email', title: 'Email', type: 'email'},
                {name: 'password', title: 'Password', type: 'password'},
                {name: 'password_confirmation', title: 'Confirm Password', type: 'password'}]
    }),
    actions: {
        submit(){
            const config = useRuntimeConfig()

            let requestConfig = {
                method: 'post',
                credentials: true,
                url: `${config.public.apiBase}/v1/brand`,
                data: this.inputValues,
                headers: {
                    'Accept': 'application/json'
                }
            };
            axios.defaults.headers.common['X-Api-Client'] = `web`
            axios.request(requestConfig)
                .then((response) => {
                    if(response.data.token){
                        let session = useSessionStore()
                        session.setAccessToken(response.data.token)
                        session.setAuthorized(true)
                        isLoading.value = false;
                        emit('stepUpdate', 'account')
                    }
                })
                .catch((error) => {
                    console.log(error);
                });
        }
    }
})