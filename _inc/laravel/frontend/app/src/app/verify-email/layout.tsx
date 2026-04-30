import { JSX } from "react";
import { Parent } from "@/definitions/components";
import AuthLayout from "../auth/layout";
import { Metadata } from "next";
export const metadata: Metadata = {
  title: "Verify Email",
};
export default function VerifyEmailLayout({ children }: Parent): JSX.Element {
  return <AuthLayout>{children}</AuthLayout>;
}
