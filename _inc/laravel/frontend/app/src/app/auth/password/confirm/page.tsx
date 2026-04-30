"use client";
import React, { JSX, useCallback, useState } from "react";
import { Box, Button, TextField, Typography } from "@mui/material";
import { confirmPasswordAPI } from "@/components/auth/fetch/POST";
import { ErrorBoundary } from "../../../../../node_modules/react-error-boundary/dist";
import AuthPage from "../../page";
export default function ConfirmPasswordPage(): JSX.Element {
  const [password, setPassword] = useState(""),
    handleSubmit = useCallback(
      async (e: React.FormEvent<HTMLFormElement>): Promise<void> => {
        e.preventDefault();
        await confirmPasswordAPI(password);
      },
      [password, confirmPasswordAPI]
    );
  return (
    <ErrorBoundary FallbackComponent={() => <></>}>
      <AuthPage>
        <Box className='card-body p-4' id='confirm-password-component'>
          <Typography variant='h4' className='mb-3 font-bold' id='page-title'>
            Confirm Password
          </Typography>
          <Typography className='mb-4 text-gray-500' id='page-subtitle'>
            Please confirm your password before continuing.
          </Typography>
          <form onSubmit={handleSubmit} id='confirm-password-form'>
            <TextField
              id='password-input'
              label='Password'
              type='password'
              variant='outlined'
              fullWidth
              required
              value={password}
              onChange={e => setPassword(e.target.value)}
              autoComplete='current-password'
            />
            <Box mt={3}>
              <Button
                type='submit'
                variant='contained'
                fullWidth
                id='confirm-password-btn'
              >
                Confirm Password
              </Button>
            </Box>
            <Box mt={3} textAlign='center' id='forgot-password'>
              <Typography variant='body2'>
                OR{" "}
                <a
                  href='/password/request'
                  className='text-blue-600'
                  id='forgot-password-link'
                >
                  Forgot Your Password?
                </a>
              </Typography>
            </Box>
          </form>
        </Box>
      </AuthPage>
    </ErrorBoundary>
  );
}
