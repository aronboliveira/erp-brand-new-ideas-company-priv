"use client";
import { useEffect } from "react";
import { getSettings, getLogoUrl } from "../../../frontend/settings";
import { AuthLayoutProps } from "../../definitions/components";
import { ErrorBoundary } from "../../../node_modules/react-error-boundary/dist";
export default function AuthLayout({
  metaTitle,
  metaDescription,
  metaImage,
  children,
}: AuthLayoutProps) {
  const settings = getSettings(),
    locale = navigator.language || "en",
    isRTL = ["ar", "he"].includes(locale) || settings.SITE_RTL === "on";
  useEffect(() => {
    document.documentElement.dir = isRTL ? "rtl" : "ltr";
    document.body.className = settings.color || "theme-3";
  }, [isRTL, settings.color]);
  return (
    <>
      <head>
        <meta charSet='utf-8' />
        <meta
          name='viewport'
          content='width=device-width, initial-scale=1, user-scalable=0'
        />
        <meta
          name='description'
          content={metaDescription || settings.meta_desc || ""}
        />
        <meta name='title' content={metaTitle || settings.meta_title || ""} />

        {/* Open Graph */}
        <meta property='og:type' content='website' />
        <meta property='og:url' content={process.env.NEXT_PUBLIC_APP_URL} />
        <meta
          property='og:title'
          content={metaTitle || settings.meta_title || ""}
        />
        <meta
          property='og:description'
          content={metaDescription || settings.meta_desc || ""}
        />
        <meta
          property='og:image'
          content={
            metaImage ||
            `${getLogoUrl("meta")}/${settings.meta_image || "meta.png"}`
          }
        />

        {/* Twitter */}
        <meta property='twitter:card' content='summary_large_image' />
        <meta
          property='twitter:url'
          content={process.env.NEXT_PUBLIC_APP_URL}
        />
        <meta
          property='twitter:title'
          content={metaTitle || settings.meta_title || ""}
        />
        <meta
          property='twitter:description'
          content={metaDescription || settings.meta_desc || ""}
        />
        <meta
          property='twitter:image'
          content={
            metaImage ||
            `${getLogoUrl("meta")}/${settings.meta_image || "meta.png"}`
          }
        />

        <link
          rel='icon'
          href={`${getLogoUrl("favicon")}/${
            settings.company_favicon || "favicon.png"
          }`}
        />
      </head>
      {children}
    </>
  );
}
