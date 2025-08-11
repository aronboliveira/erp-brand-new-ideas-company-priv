"use client";
import React, { JSX, useCallback, useState } from "react";
import {
  Box,
  Button,
  TextField,
  Typography,
  Link,
  MenuItem,
  Select,
  FormControl,
  InputLabel,
  Stack,
  Alert,
} from "@mui/material";
import Swal from "sweetalert2";
import { postForgotPassword } from "@/components/auth/fetch/POST";
import LanguageIcon from "@mui/icons-material/Language";
import AuthPage from "../../page";
import { ErrorBoundary } from "../../../../../node_modules/react-error-boundary/dist";
export default function ForgotPasswordPage(): JSX.Element {
  const [email, setEmail] = useState(""),
    [status, setStatus] = useState(""),
    languages = ["en", "es", "fr"],
    currentLanguage = "en",
    handleLanguageChange = useCallback((e: { target: { value: string } }) => {
      const value = e.target.value;
      if (value) window.location.href = value;
    }, []),
    handleSubmit = useCallback(
      async (e: React.FormEvent<HTMLFormElement>) => {
        e.preventDefault();
        try {
          const data = await postForgotPassword(email);
          if (data) setStatus("We will send a link to reset your password.");
          Swal.fire({
            icon: "success",
            title: "Success",
            text: "Reset link sent.",
          });
        } catch (error: any) {
          // Error handling is done in postForgotPassword
        }
      },
      [email]
    );
  return (
    <ErrorBoundary FallbackComponent={() => <></>}>
      <AuthPage>
        <Box
          sx={{
            p: 4,
            maxWidth: 450,
            mx: "auto",
            display: "flex",
            flexDirection: "column",
            gap: 3,
          }}
        >
          {/* Language Selector */}
          <Stack direction='row' justifyContent='flex-end'>
            <FormControl size='small' sx={{ minWidth: 120 }}>
              <InputLabel id='language-select-label'>
                <LanguageIcon fontSize='small' sx={{ mr: 1 }} />
                Language
              </InputLabel>
              <Select
                labelId='language-select-label'
                id='language-select'
                value={`/login?lang=${currentLanguage}`}
                onChange={handleLanguageChange}
                label={
                  <>
                    <LanguageIcon fontSize='small' sx={{ mr: 1 }} />
                    Language
                  </>
                }
              >
                {languages.map(lang => (
                  <MenuItem key={lang} value={`/login?lang=${lang}`}>
                    {lang.charAt(0).toUpperCase() + lang.slice(1)}
                  </MenuItem>
                ))}
              </Select>
            </FormControl>
          </Stack>{" "}
          {/* Content */}
          <Typography variant='h5' component='h2' fontWeight='bold'>
            Forgot Password
          </Typography>
          <Typography variant='body1' color='text.secondary'>
            We will send a link to reset your password.
          </Typography>{" "}
          {status && (
            <Alert severity='info' sx={{ mb: 2 }}>
              {status}
            </Alert>
          )}{" "}
          {/* Form */}
          <Box component='form' onSubmit={handleSubmit} sx={{ mt: 1 }}>
            <TextField
              margin='normal'
              required
              fullWidth
              id='email'
              label='Email Address'
              name='email'
              autoComplete='email'
              autoFocus
              value={email}
              onChange={e => setEmail(e.target.value)}
            />{" "}
            <Button
              type='submit'
              fullWidth
              variant='contained'
              sx={{ mt: 3, mb: 2, py: 1.5 }}
            >
              Send Password Reset Link
            </Button>{" "}
            <Typography variant='body2' textAlign='center' sx={{ mt: 2 }}>
              OR{" "}
              <Link href='/login' underline='hover' fontWeight='medium'>
                Sign in
              </Link>
            </Typography>
          </Box>
        </Box>
      </AuthPage>
    </ErrorBoundary>
  );
}
