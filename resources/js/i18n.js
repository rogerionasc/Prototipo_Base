import {createI18n} from 'vue-i18n'

import en from './lang/en.json'
import pt_BR from './lang/ptBR.json'

const messages = {
  en,
  pt_BR,

}

const locale = sessionStorage.getItem("locale");

const i18n = createI18n({
  locale:  locale || "pt_BR",
  fallbackLocale: "pt_BR",
  messages: messages
});
export default i18n;
