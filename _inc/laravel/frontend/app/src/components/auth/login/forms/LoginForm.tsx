"use client";
import { JSX, useCallback, useContext, useState } from "react";
import { TextField, Button } from "@mui/material";
import { postLogin } from "../../fetch/POST";
import { Bin } from "@/definitions/helpers";
import { LoginPageCtx } from "../providers/MainLoginProvider";
export default function LoginForm(): JSX.Element {
  let recaptcha_module: Bin = "off";
  const [password, setPassword] = useState(""),
    [recaptchaToken, setRecaptchaToken] = useState(""),
    [email, setEmail] = useState(""),
    handleSubmit = useCallback(
      async (e: React.FormEvent<HTMLFormElement>) => {
        e.preventDefault();
        await postLogin(email, password, recaptchaToken);
      },
      [email, password, recaptchaToken, postLogin]
    ),
    ctx = useContext(LoginPageCtx);
  if (ctx) recaptcha_module = ctx.settings.recaptcha_module;
  return (
    <form id='login-form' onSubmit={handleSubmit}>
      <TextField
        id='email'
        type='email'
        label='Email'
        variant='outlined'
        fullWidth
        required
        value={email}
        onChange={e => setEmail(e.target.value)}
        className='mb-4'
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
        className='mb-4'
      />
      {recaptcha_module === "on" && (
        <div id='recaptcha' className='mb-4'>
          {/* Recaptcha integration logic goes here */}
          <span>Recaptcha Placeholder</span>
        </div>
      )}
      <Button
        id='login-button'
        type='submit'
        variant='contained'
        fullWidth
        className='mb-4'
      >
        Login
      </Button>
    </form>
  );
}
