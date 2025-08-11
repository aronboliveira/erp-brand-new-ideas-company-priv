import { JSX, ReactNode } from "react";
import { CssBaseline, Container } from "@mui/material";
import { QueryClient, QueryClientProvider } from "@tanstack/react-query";
import AdminLayout from "../admin/layout";
import { Metadata } from "next";
const queryClient = new QueryClient();
export const metadata: Metadata = {
  title: "Manage Appraisal",
};
export default function AppraisalLayout({
  children,
}: {
  children: ReactNode;
}): JSX.Element {
  return (
    <AdminLayout>
      <QueryClientProvider client={queryClient}>
        <CssBaseline />
        <Container maxWidth='lg' id='layout-container'>
          {children}
        </Container>
      </QueryClientProvider>
    </AdminLayout>
  );
}
