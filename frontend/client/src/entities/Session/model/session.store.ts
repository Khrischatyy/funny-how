import { defineStore } from "pinia"
import { navigateTo, useCookie } from "nuxt/app"
import { useApi } from "~/src/lib/api"

type UserRole = "user" | "studio_owner"
export const USER_ROLE = "user" as UserRole
export const STUDIO_OWNER_ROLE = "studio_owner" as UserRole

export const USER_INFO_KEY = "user-info-key"
export const USER_ROLE_KEY = "user-role-key"
export const ACCESS_TOKEN_KEY = "access-token-key"
export const BRAND_KEY = "brand-key"
export const RESERVES_KEY = "reserves-key"
export const PAYMENT_SESSION = "payment-session-key"

export const useSessionStore = defineStore({
  id: "session-store",
  state: () => ({
    userInfo: useCookie(USER_INFO_KEY).value,
    userRole: useCookie(USER_ROLE_KEY).value as UserRole | null,
    accessToken: useCookie(ACCESS_TOKEN_KEY).value,
    isAuthorized: false,
    isLoading: false,
    reservations: useCookie(RESERVES_KEY).value,
    payment_session: useCookie(PAYMENT_SESSION).value,
    brand: useCookie(BRAND_KEY).value,
  }),
  actions: {
    async fetchUserInfo() {
      if (!this.accessToken) {
        return
      }
      const api = useApi({ url: "/user/me", auth: true })
      try {
        const response = await api.fetch()
        if (response.data) {
          console.log("response.data:", response.data)
          this.setUserInfo(JSON.stringify(response.data))
          this.setBrand(response.data.company_slug)
          this.setAuthorized(true)
          return response.data
        }
      } catch (error) {
        console.error("Error fetching user info:", error)
        this.setAuthorized(false)
        throw error
      }
    },
    setAccessToken(token: string | null) {
      this.accessToken = token
      const tokenCookie = useCookie(ACCESS_TOKEN_KEY)
      tokenCookie.value = token
    },
    setReservations(reservations: string | null) {
      this.reservations = reservations
      const reservationsCookie = useCookie(RESERVES_KEY)
      reservationsCookie.value = reservations
    },
    setPaymentSession(session: string | null) {
      this.payment_session = session
      const paymentSessionCookie = useCookie(PAYMENT_SESSION)
      paymentSessionCookie.value = session
    },
    setUserInfo(userInfo: string | null) {
      this.userInfo = userInfo
      const userInfoCookie = useCookie(USER_INFO_KEY)
      userInfoCookie.value = userInfo
    },
    setAuthorized(isAuthorized: boolean) {
      this.isAuthorized = isAuthorized
      if (!isAuthorized) {
        this.clearSession()
      }
    },
    setUserRole(role: UserRole | null) {
      this.userRole = role
      const roleCookie = useCookie(USER_ROLE_KEY)
      roleCookie.value = role
    },
    setBrand(slug: string | null) {
      this.brand = slug
      const brandCookie = useCookie(BRAND_KEY)
      brandCookie.value = slug
    },
    setIsLoading(isLoading: boolean) {
      this.isLoading = isLoading
    },
    isGuest() {
      return !this.isAuthorized
    },
    getUser() {
      return JSON.parse(decodeURIComponent(this.userInfo?.user) || "{}")
    },
    clearSession() {
      this.setUserInfo(null)
      this.setUserRole(null)
      this.setAccessToken(null)
      this.setReservations(null)
      this.setPaymentSession(null)
      this.setBrand(null)
    },
    logout() {
      this.clearSession()
      this.setAuthorized(false)
      navigateTo("/login")
    },
  },
})
