import type { UserGenderEnum } from '../'

export type UserBaseData = {
    firstname: string,
    surname: string | null,
    lastname: string,
    email: string,
    phone: string,
}

export type UserProfileSettingData = UserBaseData & {
    id: number,
    gender: UserGenderEnum,
    birthday: string,
}