"use client";
import { JSX, useContext } from "react";
import Link from "next/link";
import { LoginPageCtx } from "../providers/MainLoginProvider";
import { ILoginPageCtx } from "@/definitions/contexts";
import { Bin } from "@/definitions/helpers";
export default function LoginPageLinks(): JSX.Element {
  let enableSignUp: Bin = "off";
  const ctx = useContext<ILoginPageCtx>(LoginPageCtx);
  if (ctx) enableSignUp = ctx.settings.enableSignUp;
  return (
    <div id='links' className='text-center'>
      <Link href='/auth/password-request' className='text-primary block mb-2'>
        Forgot your password?
      </Link>
      {enableSignUp === "on" && (
        <Link href='/auth/register' className='text-primary block'>
          Don't have an account? Register
        </Link>
      )}
    </div>
  );
}
