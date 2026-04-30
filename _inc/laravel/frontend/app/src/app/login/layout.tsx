import { Metadata } from "next";
import { JSX, ReactNode } from "react";
import AuthLayout from "../auth/layout";
export const metadata: Metadata = {
  title: "Login",
};
export default function LoginLayout({
  children,
}: {
  children: ReactNode;
}): JSX.Element {
  return <AuthLayout>{children}</AuthLayout>;
}
