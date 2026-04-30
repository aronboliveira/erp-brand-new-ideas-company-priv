"use client";
import { JSX, useEffect, useMemo } from "react";
import { useRouter } from "next/router";
import { getSettings } from "../../../../frontend/settings";
import { Metadata } from "next";
import AdminLayout from "@/app/admin/layout";
export const metadata: Metadata = {
  title: "Manage Bulk Attendance",
};
export default function AttendanceBulkLayout({
  children,
}: {
  children: React.ReactNode;
}): JSX.Element {
  const settings = getSettings(),
    router = useRouter(),
    isRTL = useMemo(
      () =>
        ["ar", "he"].includes(router.locale || "") ||
        settings.SITE_RTL === "on",
      [router.locale, settings.SITE_RTL]
    );
  useEffect(() => {
    document.body.className = settings.color || "theme-3";
  }, [settings.color]);
  return (
    <AdminLayout title='Manage Bulk Attendance' settings={settings}>
      {children}
    </AdminLayout>
  );
}
