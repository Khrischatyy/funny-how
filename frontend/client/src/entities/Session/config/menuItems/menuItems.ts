import type { MenuItem } from 'shared/ui/common'
import { IconCard, IconClipboardText, IconGear, IconMedicalCase } from 'shared/ui/common'
import { useTranslation } from 'shared/lib/i18n'
import Localization from './localization.json'

export const getPatientLkMenuItems = (): MenuItem[] => {
    const { t } = useTranslation(Localization)

    return [
        {
            name: 'medicalCard',
            title: t('medicalCard'),
            routeLocationRaw: { path: '/home/medicalCard' },
            icon: IconCard
        },
        {
            name: 'myConsultations',
            title: t('myConsultations'),
            routeLocationRaw: { path: '/home/myConsultations' },
            icon: IconMedicalCase
        },
        {
            name: 'medicalTests',
            title: t('medicalTests'),
            routeLocationRaw: { path: '/home/medicalTests' },
            icon: IconClipboardText
        },
        // {
        //     name: 'notifications',
        //     title: t('notifications'),
        //     routeLocationRaw: { path: '/home/notifications' },
        //     icon: IconBell
        // },
        {
            name: 'settings',
            title: t('settings'),
            routeLocationRaw: { path: '/home/settings' },
            icon: IconGear
        }
    ]
}
