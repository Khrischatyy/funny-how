import { defineStore } from 'pinia'

type State = {
	userInfo: string | null,
	accessToken: string | null,
	isAuthorized: boolean,
};

export const USER_INFO_KEY = 'user-info-key'
export const ROLE_INFO_KEY = 'role-info-key'

export const ACCESS_TOKEN_KEY = 'access_token_key'

export const useSessionStore = defineStore({
	id: 'session-store',
	state: (): State => ({
		userInfo: null,
		accessToken: process.client ? localStorage.getItem(ACCESS_TOKEN_KEY) : null,
		isAuthorized: false,
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
		logout() {
			this.setAuthorized(false)
		}
	}
})
