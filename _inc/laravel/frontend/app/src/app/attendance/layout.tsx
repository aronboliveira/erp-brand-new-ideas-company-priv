import { Breadcrumbs, Typography, Link } from "@mui/material";
import { Metadata } from "next";
import React from "react";
import { ErrorBoundary } from "../../../node_modules/react-error-boundary/dist";
export const metadata: Metadata = {
  title: "Manage Attendance List",
};
export default function AttendanceLayout({
  children,
}: {
  children: React.ReactNode;
}) {
  return (
    <ErrorBoundary FallbackComponent={() => <></>}>
      <Breadcrumbs aria-label='breadcrumb' className='mb-4'>
        <Link href='/dashboard' underline='hover'>
          Dashboard
        </Link>
        <Typography color='text.primary'>Attendance</Typography>
      </Breadcrumbs>
      {children}
    </ErrorBoundary>
  );
}
