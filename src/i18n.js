// Translations
import Vue from 'vue';
import VueI18n from 'vue-i18n'
Vue.use(VueI18n)

const CONFIG = typeof ajax_object !== 'undefined' ? ajax_object : {};

function loadLocaleMessages () {
  const locales = require.context('./locales', true, /[A-Za-z0-9-_,\s]+\.json$/i)
  const messages = {}
  locales.keys().forEach(key => {
    const matched = key.match(/([A-Za-z0-9-_]+)\./i)
    if (matched && matched.length > 1) {
      const locale = matched[1]
      messages[locale] = locales(key)
    }
  })
  return messages
}

export default new VueI18n({
  locale: CONFIG.locale || process.env.MIX_I18N_LOCALE || 'en',
  fallbackLocale: CONFIG.local_fallback || process.env.MIX_I18N_FALLBACK_LOCALE || 'en',
  messages: loadLocaleMessages()
})

/**
 * Usage:
 *
 * template   : {{ $t('your.translation') }}
 * component  : this.$t('your.translation')
 * outside Vue: Vue.t('your.translation')
 * data()     : this.i18n.t('your.translation')
 */