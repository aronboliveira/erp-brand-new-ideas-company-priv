"use client";
import { JSX, useCallback, useContext, useState } from "react";
import { ErrorBoundary } from "../../../../node_modules/react-error-boundary/dist";
import {
  FormControl,
  InputLabel,
  MenuItem,
  Select,
  SelectChangeEvent,
} from "@mui/material";
import { useRouter } from "next/router";
import { Emitter } from "@/definitions/components";
import { Languages, LanguagesAcronyms } from "@/definitions/helpers";
export default function Language({
  dispatch,
}: Emitter<LanguagesAcronyms>): JSX.Element {
  let language = "en";
  const router = useRouter(),
    languages = {
      ar: "العربية",
      zh: "中文",
      da: "Dansk",
      de: "Deutsch",
      en: "English",
      es: "Español",
      fr: "Français",
      he: "עברית",
      it: "Italiano",
      ja: "日本語",
      nl: "Nederlands",
      pl: "Polski",
      pt: "Português",
      ru: "Русский",
      tr: "Türkçe",
      "pt-br": "Português (Brasil)",
    } as Languages,
    handleLanguageChange = useCallback(
      (e: SelectChangeEvent) => {
        const selectedLang = e.target.value as keyof Languages;
        dispatch(selectedLang);
        router.push(`/auth/login?lang=${selectedLang}`);
      },
      [dispatch]
    );

  return (
    <ErrorBoundary FallbackComponent={() => <></>}>
      <FormControl fullWidth variant='outlined' className='mb-4'>
        <InputLabel id='language-select-label'>Language</InputLabel>
        <Select
          labelId='language-select-label'
          id='language-select'
          value={language}
          onChange={handleLanguageChange}
          label='Language'
        >
          {Object.entries(languages)?.map(([code, lang]) => (
            <MenuItem key={code} value={code}>
              {lang.toUpperCase()}
            </MenuItem>
          ))}
        </Select>
      </FormControl>
    </ErrorBoundary>
  );
}
