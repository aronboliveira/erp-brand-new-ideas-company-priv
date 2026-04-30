"use client";
import { JSX } from "react";
import { Menu, MenuItem } from "@mui/material";
import Link from "next/link";
import { AnchorComponentProps } from "@/definitions/components";
export default function Anchor({
  anchorEl,
  navLinks,
  handleMenuClose,
}: AnchorComponentProps): JSX.Element {
  return (
    <Menu
      anchorEl={anchorEl}
      open={Boolean(anchorEl)}
      onClose={handleMenuClose}
    >
      {navLinks.map(link => (
        <MenuItem key={link} onClick={handleMenuClose}>
          <Link href={`#${link}`}>
            {link.charAt(0).toUpperCase() + link.slice(1)}
          </Link>
        </MenuItem>
      ))}
      <MenuItem onClick={handleMenuClose}>
        <Link href='/login'>Login</Link>
      </MenuItem>
      <MenuItem onClick={handleMenuClose}>
        <Link href='/register'>Register</Link>
      </MenuItem>
    </Menu>
  );
}
