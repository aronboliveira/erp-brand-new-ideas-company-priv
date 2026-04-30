"use client";
import { JSX, ReactNode } from "react";
import { Container } from "@mui/material";
import AdminLayout from "../admin/layout";
import { Metadata } from "next";
export const metadata: Metadata = {
  title: "Assets",
};
export default function AssetsLayout({
  children,
}: {
  children: ReactNode;
}): JSX.Element {
  return (
    <AdminLayout>
      <Container maxWidth='lg' id='assets-layout-container'>
        {children}
      </Container>
    </AdminLayout>
  );
}
