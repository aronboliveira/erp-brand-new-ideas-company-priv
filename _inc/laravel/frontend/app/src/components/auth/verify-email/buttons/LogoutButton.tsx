"use client";
import { Button } from "@mui/material";
import { JSX, useCallback } from "react";
import { logoutUser } from "../../fetch/POST";
export default function LogoutButton(): JSX.Element {
  const handleClick = useCallback(async () => {
    await logoutUser();
  }, []);
  return (
    <Button
      type='button'
      variant='contained'
      color='error'
      size='small'
      onClick={handleClick}
      id='logout-btn'
    >
      Logout
    </Button>
  );
}
