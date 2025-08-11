"use client";
import { JSX } from "react";
import { IconButton } from "@mui/material";
import MenuIcon from "@mui/icons-material/Menu";
import { RMouseEvent } from "@/definitions/helpers";
export default function MenuIconWrapper({
  handleMenuOpen,
}: {
  handleMenuOpen: (event: RMouseEvent<HTMLElement>) => void;
}): JSX.Element {
  return (
    <IconButton
      edge='end'
      color='inherit'
      aria-label='menu'
      onClick={handleMenuOpen}
    >
      <MenuIcon />
    </IconButton>
  );
}
