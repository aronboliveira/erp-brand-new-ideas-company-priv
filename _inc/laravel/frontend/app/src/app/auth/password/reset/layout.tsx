import { JSX } from "react";
import AuthLayout from "../../layout";
import { Metadata } from "next";
export const metadata: Metadata = {
  title: "Reset Password",
};
export default function ForgotPasswordLayout({
  children,
}: {
  children: React.ReactNode;
}): JSX.Element {
  return (
    <AuthLayout
      metaTitle='Forgot Password'
      metaDescription='We will send a link to reset your password.'
      metaImage=''
    >
      {children}
    </AuthLayout>
  );
}
