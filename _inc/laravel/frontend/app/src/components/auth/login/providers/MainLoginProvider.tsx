"use client";
import { createContext, Context, JSX, useState } from "react";
import { ILoginPageCtx } from "@/definitions/contexts";
import { Typography } from "@mui/material";
import Language from "../../../baseline/inputs/Language";
import LoginForm from "../forms/LoginForm";
import { Bin, LanguagesAcronyms } from "@/definitions/helpers";
const ctxDef: Readonly<ILoginPageCtx> = Object.freeze({
  settings: {
    recaptchaModule: "on" as Bin,
    enableSignUp: "on" as Bin,
    customDarkLayout: "off" as Bin,
  },
  settingsDispatchers: {
    setRecaptcha: null,
    setSignUp: null,
    setLayout: null,
  },
  language: "en",
  setLanguage: null,
});
export const LoginPageCtx: Context<ILoginPageCtx> = Object.seal(
  createContext(ctxDef)
);
export default function LoginProvider(): JSX.Element {
  const [recaptchaModule, setRecaptcha] = useState<Bin>("on"),
    [enableSignUp, setSignUp] = useState<Bin>("on"),
    [customDarkLayout, setLayout] = useState<Bin>("off"),
    [language, setLanguage] = useState<LanguagesAcronyms>("en");
  return (
    <LoginPageCtx.Provider
      value={{
        settings: { recaptchaModule, enableSignUp, customDarkLayout },
        settingsDispatchers: { setLayout, setRecaptcha, setSignUp },
        setLanguage,
        language,
      }}
    >
      <Language dispatch={setLanguage} />
      <Typography id='page-title' variant='h5' className='mb-4 text-center'>
        Login
      </Typography>
      <LoginForm />
    </LoginPageCtx.Provider>
  );
}
