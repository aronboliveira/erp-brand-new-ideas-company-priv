import { Box } from "@mui/material";
import { JSX } from "react";
import RegisterProvider from "@/components/register/providers/RegisterProvider";
export default function RegisterPage(): JSX.Element {
  return (
    <Box className='card-body p-4' id='register-page'>
      <RegisterProvider />
    </Box>
  );
}
