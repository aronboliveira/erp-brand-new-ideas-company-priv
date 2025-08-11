"use client";
import { Box, Typography } from "@mui/material";
import { createContext, JSX, useState } from "react";
import Language from "@/components/baseline/inputs/Language";
import RegisterForm from "@/components/register/forms/RegisterForm";
import { ErrorBoundary } from "../../../../node_modules/react-error-boundary/dist";
import { LanguagesAcronyms } from "@/definitions/helpers";
import { IRegisterCtx } from "@/definitions/contexts";
const defRegisterCtx: IRegisterCtx = {
  statusMessage: "[Status didn't load...]",
  language: "en",
};
export const RegisterCtx = createContext({ ...defRegisterCtx });
export default function RegisterProvider(): JSX.Element {
  const [statusMessage, setStatusMessage] = useState(""),
    [language, setLanguage] = useState<LanguagesAcronyms>("en");
  return (
    <ErrorBoundary FallbackComponent={() => <></>}>
      <RegisterCtx.Provider
        value={{ statusMessage, setStatusMessage, language, setLanguage }}
      >
        <Box id='language-bar' className='mb-4'>
          <Language dispatch={setLanguage} />
        </Box>
        <Typography variant='h4' className='mb-3 font-bold' id='page-title'>
          Register
        </Typography>
        {statusMessage && (
          <Typography
            variant='body1'
            className='mb-4 text-green-600 text-danger'
            id='status-message'
          >
            {statusMessage}
          </Typography>
        )}
        <RegisterForm />
        <Typography
          variant='body2'
          className='my-4 text-center'
          id='login-link'
        >
          Already have an account?{" "}
          <a href={`/auth/login?lang=${language}`} className='text-blue-600'>
            Login
          </a>
        </Typography>
      </RegisterCtx.Provider>
    </ErrorBoundary>
  );
}
