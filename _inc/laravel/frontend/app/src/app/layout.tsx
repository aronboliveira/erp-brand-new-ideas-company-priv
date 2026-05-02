import React, { JSX } from "react";
import { Box, Container, Typography } from "@mui/material";
import { Parent } from "@/definitions/components";
import type { Metadata } from "next";
import { Inter } from "next/font/google";
import styles from "../styles/modules/landingpage.module.scss";
import "../../globals.scss";
import "bootstrap/dist/css/bootstrap.min.css";
import LandingProvider from "@/components/root/providers/LandingProvider";
const inter = Inter({ subsets: ["latin"] });
export const metadata: Metadata = {
  title: "ERP Nova Brand New Ideas Company",
  description:
    "ERP de Negócios completo, com recursos de CRM, HRM. Gerencie suas equipes e processos com excelência!",
  metadataBase: new URL(process.env.NEXT_PUBLIC_APP_URL || ""),
  openGraph: {
    title: "ERP Nova Brand New Ideas Company",
    description:
      "ERP de Negócios completo, com recursos de CRM, HRM. Gerencie suas equipes e processos com excelência!",
    images: "/public/logo-brand new ideas company-2.png",
    type: "website",
  },
  twitter: {
    card: "summary_large_image",
    title: "ERP Nova Brand New Ideas Company",
    description:
      "ERP de Negócios completo, com recursos de CRM, HRM. Gerencie suas equipes e processos com excelência!",
    images: "/public/logo-brand new ideas company-2.png",
  },
};
export default function LandingLayout({ children }: Parent): JSX.Element {
  return (
    <html lang={process.env.DEFAULT_LOCALE || "en"}>
      <body className={`${inter.className} theme-3}`}>
        <LandingProvider>
          <Box component='main' className={styles.mainContent}>
            {children}
          </Box>
          <Box component='footer' className={styles.footer}>
            <Container>
              <Typography variant='body2' align='right'>
                © 2025 Nova Brand New Ideas Company
              </Typography>
            </Container>
          </Box>
        </LandingProvider>
      </body>
    </html>
  );
}
