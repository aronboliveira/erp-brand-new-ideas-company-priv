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
<<<<<<< HEAD
  title: "ERP Nova Brand New Ideas Company",
=======
  title: "ERP Nova Prestech",
>>>>>>> 66cafc92b (fix: implement 3 orphan ProjectController routes; add 35 missing consts across 8 controllers; fix PurchaseController 12x ModelNotFoundException→404; convert 96 string literals to const refs in routes/web.php; DealController deal() visibility→protected)
  description:
    "ERP de Negócios completo, com recursos de CRM, HRM. Gerencie suas equipes e processos com excelência!",
  metadataBase: new URL(process.env.NEXT_PUBLIC_APP_URL || ""),
  openGraph: {
<<<<<<< HEAD
    title: "ERP Nova Brand New Ideas Company",
    description:
      "ERP de Negócios completo, com recursos de CRM, HRM. Gerencie suas equipes e processos com excelência!",
    images: "/public/logo-brand new ideas company-2.png",
=======
    title: "ERP Nova Prestech",
    description:
      "ERP de Negócios completo, com recursos de CRM, HRM. Gerencie suas equipes e processos com excelência!",
    images: "/public/logo-prestech-2.png",
>>>>>>> 66cafc92b (fix: implement 3 orphan ProjectController routes; add 35 missing consts across 8 controllers; fix PurchaseController 12x ModelNotFoundException→404; convert 96 string literals to const refs in routes/web.php; DealController deal() visibility→protected)
    type: "website",
  },
  twitter: {
    card: "summary_large_image",
<<<<<<< HEAD
    title: "ERP Nova Brand New Ideas Company",
    description:
      "ERP de Negócios completo, com recursos de CRM, HRM. Gerencie suas equipes e processos com excelência!",
    images: "/public/logo-brand new ideas company-2.png",
=======
    title: "ERP Nova Prestech",
    description:
      "ERP de Negócios completo, com recursos de CRM, HRM. Gerencie suas equipes e processos com excelência!",
    images: "/public/logo-prestech-2.png",
>>>>>>> 66cafc92b (fix: implement 3 orphan ProjectController routes; add 35 missing consts across 8 controllers; fix PurchaseController 12x ModelNotFoundException→404; convert 96 string literals to const refs in routes/web.php; DealController deal() visibility→protected)
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
<<<<<<< HEAD
                © 2025 Nova Brand New Ideas Company
=======
                © 2025 Nova Prestech
>>>>>>> 66cafc92b (fix: implement 3 orphan ProjectController routes; add 35 missing consts across 8 controllers; fix PurchaseController 12x ModelNotFoundException→404; convert 96 string literals to const refs in routes/web.php; DealController deal() visibility→protected)
              </Typography>
            </Container>
          </Box>
        </LandingProvider>
      </body>
    </html>
  );
}
