import { LanguageProvider } from "@/components/auth/verify-email/providers/VerifyProvider";
import { Box, Typography } from "@mui/material";
import dynamic from "next/dynamic";
import { JSX } from "react";
const ResendVerificationButton = dynamic(
    () =>
      import("@/components/auth/verify-email/buttons/ResendVerificationButton"),
    { ssr: false }
  ),
  LogoutButton = dynamic(
    () =>
      import("@/components/auth/verify-email/buttons/ResendVerificationButton"),
    {
      ssr: false,
    }
  );
export default function VerifyEmailPage(): JSX.Element {
  return (
    <Box className='card-body p-4' id='verify-email-page'>
      <LanguageProvider>
        <Typography variant='h4' className='mb-3 font-bold' id='page-title'>
          Verify Email
        </Typography>
        <Box id='content'>
          <Box className='mb-4 font-medium text-sm text-green-600 text-primary'>
            A new verification link has been sent to the email address you
            provided during registration.
          </Box>
          <Box className='mb-4 text-sm text-gray-600'>
            Thanks for signing up! Before getting started, please verify your
            email address by clicking the link we just emailed to you. If you
            didn’t receive the email, we will gladly send you another.
          </Box>
          <Box className='mt-4 flex items-center justify-between'>
            <Box className='row'>
              <Box className='col-auto'>
                <ResendVerificationButton />
              </Box>
              <Box className='col-auto'>
                <LogoutButton />
              </Box>
            </Box>
          </Box>
        </Box>
      </LanguageProvider>
    </Box>
  );
}
