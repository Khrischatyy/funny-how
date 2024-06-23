import type {ResponseDto} from "~/src/lib/api";
import {UserRoleEnum} from "~/src/entities/@abstract/User";
import {navigateTo} from "nuxt/app";
import type {meResponse} from "~/plugins/session";

export function authorizeUser(sessionStore: any, response:ResponseDto<meResponse> | null, route: any, token: string | null) {
    sessionStore.setUserRole(response?.data.role as UserRoleEnum)
    sessionStore.setAccessToken(token);
    sessionStore.setAuthorized(true);
    if(response?.data.has_company) {
        sessionStore.setBrand(response?.data.company_slug.toString())
    }

    if(!response?.data.role){
        navigateTo('/settings/role')
        return
    }

    if (response?.data.company_slug && route.path === '/create') {
        navigateTo(`/@${response?.data.company_slug}`)
        return
    }else{
        return true;
    }
}