import AuthPage from "@/app/auth/page";
import { JSX } from "react";
export default function ConfirmPasswordLayout({
  children,
}: {
  children: React.ReactNode;
}): JSX.Element {
  return (
    <AuthPage
      metaTitle='Confirm Password'
      metaDescription='Please confirm your password before continuing.'
      metaImage=''
    >
      {children}
    </AuthPage>
  );
}
