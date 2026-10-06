/**
 * Taallum BD Frontend i18n Utilities
 */

const BN_DIGITS = ['০', '১', '২', '৩', '৪', '৫', '৬', '৭', '৮', '৯'];
const AR_DIGITS = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];

export function formatNumber(number, locale = 'bn') {
    if (number === null || number === undefined) return '';
    const str = String(number);

    if (locale === 'bn') {
        return str.replace(/\d/g, (d) => BN_DIGITS[Number(d)]);
    }

    if (locale === 'ar') {
        return str.replace(/\d/g, (d) => AR_DIGITS[Number(d)]);
    }

    return str;
}

export function formatPrice(amount, currency = 'BDT', locale = 'bn') {
    const num = Number(amount || 0);

    if (currency === 'USD') {
        const formatted = num.toFixed(2);
        return `$${formatNumber(formatted, locale)}`;
    }

    // BDT default
    const formatted = Math.round(num).toLocaleString('en-US');
    const localized = formatNumber(formatted, locale);

    if (locale === 'ar') {
        return `${localized} د.ب`;
    }

    return `৳${localized}`;
}

export function translate(translations, key, fallback = null, replacements = {}) {
    let result = (translations && translations[key]) || fallback || key;

    if (typeof result === 'string' && Object.keys(replacements).length > 0) {
        Object.entries(replacements).forEach(([k, v]) => {
            result = result.replace(new RegExp(`:${k}|{${k}}`, 'g'), v);
        });
    }

    return result;
}
