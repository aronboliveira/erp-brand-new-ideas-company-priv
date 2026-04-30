import { Button } from "@mui/material";
import { JSX, useCallback } from "react";
import { resendVerificationEmail } from "@/components/auth/fetch/POST";
export default function ResendVerificationButton(): JSX.Element {
  const handleClick = useCallback(async () => {
    await resendVerificationEmail();
  }, []);
  return (
    <Button
      type='button'
      variant='contained'
      size='small'
      onClick={handleClick}
      id='resend-verification-btn'
    >
      Resend Verification Email
    </Button>
  );
}
