import { createI18n } from 'vue-i18n'
import en from './locales/en.json'
import ar from './locales/ar.json'
import es from './locales/es.json'
import fr from './locales/fr.json'
import ur from './locales/ur.json'

const messages = {
  en,
  ar,
  es,
  fr,
  ur
}

export function setupI18n() {
  const savedLanguage = localStorage.getItem('language') || 'en'
  const i18n = createI18n({
    legacy: false,
    locale: savedLanguage,
    fallbackLocale: 'en',
    messages,
    globalInjection: true
  })

  return i18n
}
