"use client";
import axios from "axios";
import React, { useCallback, useState } from "react";
import Swal from "sweetalert2";
import {
  Alert,
  Box,
  Button,
  Card,
  CardContent,
  FormControl,
  InputLabel,
  MenuItem,
  Select,
  SelectChangeEvent,
  TextField,
} from "@mui/material";
export async function postPasswordResetEmail(
  email: string,
  recaptchaToken?: string
): Promise<any> {
  try {
    const response = await axios.post("/api/password/email", {
      email,
      recaptcha: recaptchaToken,
    });
    if (response && response.data) return response.data;
    return null;
  } catch (error: any) {
    console.error(`Failed to POST for password reset email: ${error?.message}`);
  }
}
interface ResetPasswordProps {
  settings: { recaptcha_module: string; cust_darklayout: string };
  languages: Record<string, string>;
  currentLang: string;
}
export default function ResetPassword({
  settings,
  languages,
  currentLang,
}: ResetPasswordProps) {
  const [email, setEmail] = useState("");
  const [recaptchaToken, setRecaptchaToken] = useState("");
  const [status, setStatus] = useState("");
  const [lang, setLang] = useState(currentLang);
  const handleLanguageChange = useCallback((e: SelectChangeEvent) => {
    const selectedLang = e.target.value as string;
    setLang(selectedLang);
    window.location.href = `/password-request?lang=${selectedLang}`;
  }, []);
  const handleSubmit = useCallback(
    async (e: React.FormEvent<HTMLFormElement>) => {
      e.preventDefault();
      try {
        const data = await postPasswordResetEmail(email, recaptchaToken);
        if (data) {
          setStatus("Reset link sent successfully");
          Swal.fire({
            icon: "success",
            title: "Success",
            text: "Password reset link sent",
          });
        }
      } catch (error: any) {
        Swal.fire({
          icon: "error",
          title: "Error",
          text: error?.message || "Error",
        });
      }
    },
    [email, recaptchaToken]
  );
  return (
    <Card id='reset-password' className='p-4'>
      <CardContent>
        <Box id='language-bar' className='mb-4'>
          <FormControl variant='outlined' size='small'>
            <InputLabel id='language-select-label'>Language</InputLabel>
            <Select
              labelId='language-select-label'
              id='language-select'
              value={lang}
              onChange={handleLanguageChange}
              label='Language'
            >
              {Object.entries(languages).map(([code, language]) => (
                <MenuItem key={code} value={code}>
                  {language.charAt(0).toUpperCase() + language.slice(1)}
                </MenuItem>
              ))}
            </Select>
          </FormControl>
        </Box>
        <Box id='form-header' className='mb-4'>
          <h2 className='mb-3 font-semibold text-primary'>Reset Password</h2>
        </Box>
        <form id='reset-password-form' onSubmit={handleSubmit}>
          <Box id='form-fields' className='space-y-4'>
            <TextField
              id='email'
              type='email'
              label='E-Mail'
              variant='outlined'
              fullWidth
              required
              value={email}
              onChange={e => setEmail(e.target.value)}
              autoComplete='email'
              autoFocus
            />
            {settings.recaptcha_module === "on" ? (
              <Box id='recaptcha' className='mb-4'>
                {/* Logic for recaptcha integration goes here */}
                <div>Recaptcha Placeholder</div>
              </Box>
            ) : null}
            <Button
              type='submit'
              variant='contained'
              fullWidth
              className='mt-2'
            >
              Send Password Reset Link
            </Button>
          </Box>
          <Box id='back-to-login' className='mt-4 text-center'>
            <a href='/login' className='text-primary'>
              Back to Login
            </a>
          </Box>
        </form>
        {status ? (
          <Alert id='status-message' severity='success' className='mt-4'>
            {status}
          </Alert>
        ) : null}
      </CardContent>
    </Card>
  );
}
