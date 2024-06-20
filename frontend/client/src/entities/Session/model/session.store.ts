import { defineStore } from 'pinia'
import {navigateTo} from "nuxt/app";
import {useCookie} from "#app";

type UserRole = 'user' | 'studio_owner';

type State = {
	userInfo: string | null,
	accessToken: string | null,
	isAuthorized: boolean,
	userRole: UserRole | null,
	isLoading: boolean,
	brand: string | null,
	reservations: string | null,
	payment_session: string | null
};

export const USER_INFO_KEY = 'user-info-key'

export const USER_ROLE_KEY = 'user-role-key'
export const ROLE_INFO_KEY = 'role-info-key'

export const ACCESS_TOKEN_KEY = 'access_token_key'
export const BRAND_KEY = 'brand-key'

export const RESERVES_KEY = 'reserves-key'
export const PAYMENT_SESSION = 'payment-session-key'
export const useSessionStore = defineStore({
	id: 'session-store',
	state: (): State => ({
		userInfo: null,
		userRole: process.client ? (localStorage.getItem(ROLE_INFO_KEY) as UserRole | null) : null,
		accessToken: process.client ? localStorage.getItem(ACCESS_TOKEN_KEY) : null,
		isAuthorized: false,
		isLoading: false,
		reservations: process.client ? localStorage.getItem(RESERVES_KEY) : null,
		payment_session: process.client ? localStorage.getItem(PAYMENT_SESSION) : null,
		brand: '',
	}),
	actions: {
		setAccessToken(token: string | null) {
			this.accessToken = token
			const tokenCookie = useCookie(ACCESS_TOKEN_KEY);
			tokenCookie.value = token;

			if (process.client) {
				if (token) {
					localStorage.setItem(ACCESS_TOKEN_KEY, token)
				} else {
					localStorage.removeItem(ACCESS_TOKEN_KEY)
				}
			}
		},
		setReservations(reservations: string | null) {
			this.reservations = reservations
			if (process.client) {
				if (reservations) {
					localStorage.setItem(RESERVES_KEY, JSON.stringify(reservations))
				} else {
					localStorage.removeItem(RESERVES_KEY)
				}
			}
		},
		setPaymentSession(session: string | null) {
			this.payment_session = session
			if (process.client) {
				if (session) {
					localStorage.setItem(PAYMENT_SESSION, JSON.stringify(session))
				} else {
					localStorage.removeItem(PAYMENT_SESSION)
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
				localStorage.removeItem(BRAND_KEY)
				localStorage.removeItem(RESERVES_KEY)
				localStorage.removeItem(PAYMENT_SESSION)
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
		isGuest() {
			return !this.isAuthorized
		},
		logout() {
			this.setAuthorized(false)
			navigateTo('/login')
		}
	}
})
