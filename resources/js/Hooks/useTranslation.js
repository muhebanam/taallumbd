import { usePage, router } from '@inertiajs/react';
import { translate, formatNumber as fmtNum, formatPrice as fmtPrice } from '../utils/i18n';

export function useTranslation() {
    const { props } = usePage();
    const locale = props.locale || 'bn';
    const dir = props.dir || (locale === 'ar' ? 'rtl' : 'ltr');
    const isRtl = props.isRtl ?? (dir === 'rtl');
    const currency = props.currency || (locale === 'bn' ? 'BDT' : 'USD');
    const hijriDate = props.hijriDate || '';
    const availableLocales = props.availableLocales || {
        bn: { name: 'বাংলা', dir: 'ltr', flag: '🇧🇩' },
        en: { name: 'English', dir: 'ltr', flag: '🇬🇧' },
        ar: { name: 'العربية', dir: 'rtl', flag: '🇸🇦' },
    };
    const translations = props.translations || {};

    const t = (key, fallback = null, replacements = {}) => {
        return translate(translations, key, fallback, replacements);
    };

    const formatNumber = (num) => {
        return fmtNum(num, locale);
    };

    const formatPrice = (bdtAmount, usdAmount = null) => {
        if (currency === 'USD') {
            const amount = usdAmount !== null && usdAmount !== undefined && Number(usdAmount) > 0
                ? Number(usdAmount)
                : Number(bdtAmount || 0) / 120.0;
            return fmtPrice(amount, 'USD', locale);
        }
        return fmtPrice(bdtAmount, 'BDT', locale);
    };

    const switchLocale = (targetLocale) => {
        router.get(`/locale/${targetLocale}`, {}, {
            preserveScroll: true,
            preserveState: false,
        });
    };

    const switchCurrency = (targetCurrency) => {
        router.get(`/currency/${targetCurrency}`, {}, {
            preserveScroll: true,
            preserveState: false,
        });
    };

    return {
        t,
        locale,
        dir,
        isRtl,
        currency,
        hijriDate,
        availableLocales,
        formatNumber,
        formatPrice,
        switchLocale,
        switchCurrency,
    };
}

export default useTranslation;
