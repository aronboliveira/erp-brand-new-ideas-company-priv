import React from "react";
import Link from "next/link";
import { useRouter } from "next/router";
import {
  Card,
  List,
  ListItem,
  ListItemText,
  ListItemSecondaryAction,
} from "@mui/material";
import ChevronRightIcon from "@mui/icons-material/ChevronRight";
const navItems = [
  { label: "Taxes", href: "/taxes" },
  { label: "Category", href: "/product-category" },
  { label: "Unit", href: "/product-unit" },
  { label: "Custom Field", href: "/custom-field" },
];
export default function AccountSetup() {
  const router = useRouter(),
    isActive = (href: string) => router.pathname === href;
  return (
    <Card style={{ position: "sticky", top: 30 }}>
      <List disablePadding id='useradd-sidenav'>
        {navItems.map(item => (
          <Link href={item.href} passHref key={item.href}>
            <ListItem
              button
              selected={isActive(item.href)}
              style={{ border: 0 }}
            >
              <ListItemText primary={item.label} />
              <ListItemSecondaryAction>
                <ChevronRightIcon />
              </ListItemSecondaryAction>
            </ListItem>
          </Link>
        ))}
      </List>
    </Card>
  );
}
