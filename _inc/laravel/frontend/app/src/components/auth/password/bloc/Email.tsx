"use client";
import React, { useCallback, useState } from "react";
import Swal from "sweetalert2";
import { Button, TextField } from "@mui/material";
import { postForgotPassword } from "../../fetch/POST";
export default function AuthPasswordForgotEmail() {
  const [email, setEmail] = useState(""),
    [status, setStatus] = useState(""),
    languages = ["en", "es", "fr"],
    currentLanguage = "en",
    handleLanguageChange = useCallback(
      (e: React.ChangeEvent<HTMLSelectElement>) => {
        const value = e.target.value;
        if (value) window.location.href = value;
      },
      []
    ),
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
        } catch (error: any) {}
      },
      [postForgotPassword, setStatus]
    );
  return (
    <div id='forgot-password' className='p-4'>
      <div id='auth-topbar'>
        <ul className='flex'>
          <li id='language-select' className='list-none'>
            <select
              className='btn btn-primary my-1 me-2'
              id='language'
              onChange={handleLanguageChange}
            >
              {languages.map(lang => (
                <option
                  key={lang}
                  value={`/login?lang=${lang}`}
                  selected={lang === currentLanguage}
                >
                  {lang.charAt(0).toUpperCase() + lang.slice(1)}
                </option>
              ))}
            </select>
          </li>
        </ul>
      </div>
      <div id='content'>
        <h2 className='mb-3 font-semibold'>Forgot Password</h2>
        <p className='mb-4 text-muted'>
          We will send a link to reset your password.
        </p>
        {status && (
          <p id='status-message' className='mb-4 text-muted'>
            {status}
          </p>
        )}
      </div>
      <form
        id='forgot-password-form'
        onSubmit={handleSubmit}
        className='w-full'
      >
        <div id='form-fields' className='mb-3'>
          <div id='email-field' className='form-group mb-3'>
            <label htmlFor='email' className='form-label'>
              E-Mail Address
            </label>
            <TextField
              id='email'
              type='email'
              variant='outlined'
              fullWidth
              value={email}
              onChange={e => setEmail(e.target.value)}
              required
              autoComplete='email'
              autoFocus
            />
          </div>
          <div id='submit-button' className='d-grid'>
            <Button
              type='submit'
              variant='contained'
              className='btn btn-primary btn-block mt-2'
              fullWidth
            >
              Send Password Reset Link
            </Button>
          </div>
        </div>
        <p id='alternative' className='my-4'>
          OR{" "}
          <a href='/login' className='font-medium text-primary ml-1'>
            Signin
          </a>
        </p>
      </form>
    </div>
  );
}
