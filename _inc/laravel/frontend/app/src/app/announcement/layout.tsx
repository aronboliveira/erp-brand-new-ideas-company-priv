import { JSX, ReactNode } from "react";
import { Container } from "@mui/material";
import AdminLayout from "../admin/layout";
import { Metadata } from "next";
export const metadata: Metadata = {
  title: "Manage Announcement",
};
export default function AnnouncementLayout({
  children,
}: {
  children: ReactNode;
}): JSX.Element {
  return (
    <AdminLayout>
      <Container maxWidth='lg' id='announcement-layout-container'>
        {children}
      </Container>
      e
    </AdminLayout>
  );
}
