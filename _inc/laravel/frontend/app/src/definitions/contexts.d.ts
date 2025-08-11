import { Bin, LanguagesAcronyms, RcDispatch } from "./helpers";
export interface LanguageCtx {
  language: LanguagesAcronyms;
  setLanguage?: RcDispatch<LanguagesAcronyms> | null;
}
export interface LoginPageSettings {
  recaptchaModule: Bin;
  enableSignUp: Bin;
  customDarkLayout: Bin;
}
export interface ILoginPageCtx extends LanguageCtx {
  settings: LoginPageSettings;
  settingsDispatchers: {
    setLayout: RcDispatch<Bin> | null;
    setRecaptcha: RcDispatch<Bin> | null;
    setSignUp: RcDispatch<Bin> | null;
  };
}
export interface IRegisterCtx extends LanguageCtx {
  statusMessage: string;
  setStatusMessage?: RcDispatch<string> | mull;
}
export interface IVerifyCtx extends LanguageCtx {}
