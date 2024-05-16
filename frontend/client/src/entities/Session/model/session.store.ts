import { defineStore } from 'pinia'
import {navigateTo} from "nuxt/app";

type UserRole = 'user' | 'studio_owner';

type State = {
	userInfo: string | null,
	accessToken: string | null,
	isAuthorized: boolean,
	userRole: UserRole | null,
	isLoading: boolean,
	brand: string | null,
};

export const USER_INFO_KEY = 'user-info-key'

export const USER_ROLE_KEY = 'user-role-key'
export const ROLE_INFO_KEY = 'role-info-key'

export const ACCESS_TOKEN_KEY = 'access_token_key'
export const BRAND_KEY = 'brand-key'

export const useSessionStore = defineStore({
	id: 'session-store',
	state: (): State => ({
		userInfo: null,
		userRole: process.client ? (localStorage.getItem(ROLE_INFO_KEY) as UserRole | null) : null,
		accessToken: process.client ? localStorage.getItem(ACCESS_TOKEN_KEY) : null,
		isAuthorized: false,
		isLoading: false,
		brand: '',
	}),
	actions: {
		setAccessToken(token: string | null) {
			this.accessToken = token
			if (process.client) {
				if (token) {
					localStorage.setItem(ACCESS_TOKEN_KEY, token)
				} else {
					localStorage.removeItem(ACCESS_TOKEN_KEY)
				}
			}
		},
		setUserInfo(userInfo: string | null) {
			this.userInfo = userInfo
			if (process.client) {
				if (userInfo) {
					localStorage.setItem(USER_INFO_KEY, userInfo)
				} else {
					localStorage.removeItem(USER_INFO_KEY)
				}
			}
		},
		setAuthorized(isAuthorized: boolean) {
			if (!isAuthorized) {
				this.userInfo = null
				this.accessToken = null
				localStorage.removeItem(USER_INFO_KEY)
				localStorage.removeItem(ACCESS_TOKEN_KEY)
				localStorage.removeItem(ROLE_INFO_KEY)
			}
			this.isAuthorized = isAuthorized
		},
		setUserRole(role: UserRole){
				this.userRole = role;
			if (process.client) {
				if (role) {
					localStorage.setItem(ROLE_INFO_KEY, role)
				} else {
					localStorage.removeItem(ROLE_INFO_KEY)
				}
			}
		},
		setBrand(slug: string) {
			this.brand = slug
			if (process.client) {
				if (slug) {
					localStorage.setItem(BRAND_KEY, slug)
				} else {
					localStorage.removeItem(BRAND_KEY)
				}
			}
		},
		setIsLoading(isLoading: boolean) {
			this.isLoading = isLoading
		},
		logout() {
			this.setAuthorized(false)
			navigateTo('/login')
		}
	}
})
