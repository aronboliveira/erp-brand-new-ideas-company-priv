"use client";
import React, { useCallback, useState } from "react";
import Swal from "sweetalert2";
import {
  Alert,
  Box,
  Button,
  Card,
  CardContent,
  CardHeader,
  TextField,
} from "@mui/material";
import { postResetPassword } from "../../fetch/POST";
import { ResetPasswordProps } from "@/definitions/components";
export function ResetPassword({ token }: ResetPasswordProps) {
  const [email, setEmail] = useState(""),
    [password, setPassword] = useState(""),
    [passwordConfirmation, setPasswordConfirmation] = useState(""),
    [status, setStatus] = useState(""),
    handleSubmit = useCallback(
      async (e: React.FormEvent<HTMLFormElement>) => {
        e.preventDefault();
        try {
          const data = await postResetPassword(
            token,
            email,
            password,
            passwordConfirmation
          );
          if (data) {
            setStatus("Password reset successfully");
            Swal.fire({
              icon: "success",
              title: "Success",
              text: "Password has been reset",
            });
          }
        } catch (error: any) {}
      },
      [token, email, password, passwordConfirmation, setStatus]
    ),
    handleEmailChange = useCallback(
      (e: React.ChangeEvent<HTMLInputElement>) => {
        setEmail(e.target.value);
      },
      [setEmail]
    ),
    handlePasswordChange = useCallback(
      (e: React.ChangeEvent<HTMLInputElement>) => {
        setPassword(e.target.value);
      },
      [setPassword]
    ),
    handlePasswordConfirmationChange = useCallback(
      (e: React.ChangeEvent<HTMLInputElement>) => {
        setPasswordConfirmation(e.target.value);
      },
      [setPasswordConfirmation]
    );
  return (
    <Card id='reset-password' sx={{ p: 2 }}>
      <CardHeader
        id='header'
        title='Reset Password!'
        titleTypographyProps={{ variant: "h5", sx: { color: "primary.main" } }}
      />
      <CardContent>
        <Box
          component='form'
          id='reset-password-form'
          onSubmit={handleSubmit}
          sx={{ width: "100%" }}
        >
          <input type='hidden' name='token' value={token} />
          <Box
            id='form-fields'
            sx={{ display: "flex", flexDirection: "column", gap: 2 }}
          >
            <TextField
              id='email-field'
              label='E-Mail Address'
              type='email'
              value={email}
              onChange={handleEmailChange}
              fullWidth
              required
              variant='outlined'
            />
            <TextField
              id='password-field'
              label='Password'
              type='password'
              value={password}
              onChange={handlePasswordChange}
              fullWidth
              required
              variant='outlined'
            />
            <TextField
              id='password-confirmation-field'
              label='Password Confirmation'
              type='password'
              value={passwordConfirmation}
              onChange={handlePasswordConfirmationChange}
              fullWidth
              required
              variant='outlined'
            />
            <Button id='resetBtn' type='submit' variant='contained' fullWidth>
              Reset
            </Button>
          </Box>
        </Box>
        {status && (
          <Alert id='status-message' severity='success' sx={{ mt: 2 }}>
            {status}
          </Alert>
        )}
      </CardContent>
    </Card>
  );
}

export default ResetPassword;
