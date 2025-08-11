import { JSX, ReactNode } from "react";
import { CssBaseline, Container } from "@mui/material";
import { QueryClient, QueryClientProvider } from "@tanstack/react-query";
import AdminLayout from "../admin/layout";
const queryClient = new QueryClient();
export default function Layout({
  children,
}: {
  children: ReactNode;
}): JSX.Element {
  return (
    <QueryClientProvider client={queryClient}>
      <CssBaseline />
      <AdminLayout>
        <Container maxWidth='lg' id='allowance-layout-container'>
          {children}
        </Container>
      </AdminLayout>
    </QueryClientProvider>
  );
}
