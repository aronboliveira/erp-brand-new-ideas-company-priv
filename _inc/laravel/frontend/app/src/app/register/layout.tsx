import { JSX } from "react";
import AuthLayout from "@/app/auth/layout";
import { Parent } from "@/definitions/components";
import { Metadata } from "next";
export const metadata: Metadata = {
  title: "Register",
};
export default function RegisterLayout({ children }: Parent): JSX.Element {
  return <AuthLayout>{children}</AuthLayout>;
}
