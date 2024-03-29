import {
    useApi,
    type ResponseDto
} from 'shared/lib/api'
import { UserGenderEnum } from '../../@abstract/User'
import { mapUserProfileSettingDataResponseDto } from './mapping'

export type UserProfileSettingDataDto = ResponseDto<
{
    id: number,
    firstname: string,
    surname: string | null,
    lastname: string,
    gender: UserGenderEnum,
    email: string,
    birthday: string,
    age: number | null,
    phone: string,
    avatar: {
        original: string | null
    }
}>

export function getUserProfileSettingData() {
    return useApi({
        url: '/api/profile/settings/info',
        options: {
            method: 'GET',
            transform: mapUserProfileSettingDataResponseDto
        },
    })
}