"use client";
import { JSX, useCallback, useState } from "react";
import { Box, Button, TextField } from "@mui/material";
import { registerUser } from "../fetch/POST";
export default function RegisterForm(): JSX.Element {
  const [name, setName] = useState(""),
    [email, setEmail] = useState(""),
    [password, setPassword] = useState(""),
    [passwordConfirmation, setPasswordConfirmation] = useState(""),
    handleSubmit = useCallback(
      async (e: React.FormEvent<HTMLFormElement>): Promise<void> => {
        e.preventDefault();
        await registerUser(name, email, password, passwordConfirmation);
      },
      [name, email, password, passwordConfirmation]
    );
  return (
    <form id='registerForm' onSubmit={handleSubmit} className='register-form'>
      <Box className='space-y-4'>
        <TextField
          id='name'
          type='text'
          label='Name'
          variant='outlined'
          fullWidth
          required
          value={name}
          onChange={e => setName(e.target.value)}
          autoComplete='name'
          autoFocus
        />
        <TextField
          id='email'
          type='email'
          label='Email'
          variant='outlined'
          fullWidth
          required
          value={email}
          onChange={e => setEmail(e.target.value)}
          autoComplete='email'
        />
        <TextField
          id='password'
          type='password'
          label='Password'
          variant='outlined'
          fullWidth
          required
          value={password}
          onChange={e => setPassword(e.target.value)}
          autoComplete='new-password'
        />
        <TextField
          id='password_confirmation'
          type='password'
          label='Password Confirmation'
          variant='outlined'
          fullWidth
          required
          value={passwordConfirmation}
          onChange={e => setPasswordConfirmation(e.target.value)}
          autoComplete='new-password'
        />
        {/* Insert recaptcha component here if settings.recaptcha_module is "on" */}
        <Box id='recaptcha' className='mt-3'>
          <span>Recaptcha Placeholder</span>
        </Box>
        <Button
          type='submit'
          variant='contained'
          fullWidth
          className='mt-2'
          id='registerBtn'
        >
          Register
        </Button>
      </Box>
    </form>
  );
}
