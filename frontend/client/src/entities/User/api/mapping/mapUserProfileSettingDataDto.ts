import type {
    UserProfileSettingDataDto
} from '../index'

import type {
    UserProfileSettingData,
} from '../../../@abstract/User/types'

export const mapUserProfileSettingDataResponseDto = (data: UserProfileSettingDataDto): UserProfileSettingData  => {
    return {
        id: data.data.id,
        firstname: data.data.firstname,
        surname: data.data.surname,
        lastname: data.data.lastname,
        gender: data.data.gender,
        email: data.data.email,
        phone: data.data.phone,
        birthday: data.data.birthday,
    }
}