import { mapUserProfileSettingDataResponseDto } from '../index'
import type {
  UserProfileSettingDataDto
} from '../../index'

import type {
  UserProfileSettingData,
} from '../../../../@abstract/User/types'
import  { UserGenderEnum } from '../../../../@abstract/User/config'

describe('mapUserProfileSettingDataResponseDto', () => {
  let mockData: UserProfileSettingDataDto
  beforeEach(() => {
    mockData  = {
      success: true,
      message: 'Ok',
      data: {
        id: 1,
        firstname: 'John',
        surname: 'Doe',
        lastname: 'Doe',
        gender: UserGenderEnum.Male,
        email: 'johndoe@example.com',
        birthday: '01/01/1992',
        age: 30,
        phone: '1234567890',
        avatar: {
          original: 'http://example.com/avatar.jpg'
        }
      }
    }
  })

  it('возвращает корректный объект', () => {

    const expectedData: UserProfileSettingData = {
      id: mockData.data.id,
      firstname: mockData.data.firstname,
      surname: mockData.data.surname,
      lastname: mockData.data.lastname,
      gender: mockData.data.gender,
      email: mockData.data.email,
      phone: mockData.data.phone,
      birthday: mockData.data.birthday,
    }

    const actualData = mapUserProfileSettingDataResponseDto(mockData)

    expect(actualData).to.deep.equal(expectedData)
  })
})