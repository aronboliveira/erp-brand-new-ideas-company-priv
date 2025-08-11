"use client";
import { Parent } from "@/definitions/components";
import { LanguageCtx, IVerifyCtx } from "@/definitions/contexts";
import { JSX, createContext, useState } from "react";
import { Box } from "@mui/material";
import Language from "@/components/baseline/inputs/Language";
const defVerifyCtx: LanguageCtx = Object.freeze({
  language: "en",
});
export const VerifyCtx = createContext<IVerifyCtx>({ ...defVerifyCtx });
export function LanguageProvider({ children }: Parent): JSX.Element {
  const [language, setLanguage] = useState(defVerifyCtx.language);
  return (
    <VerifyCtx.Provider value={{ language, setLanguage }}>
      <Box id='language-bar' className='mb-4'>
        <Language dispatch={setLanguage} />
      </Box>
      {children}
    </VerifyCtx.Provider>
  );
}
