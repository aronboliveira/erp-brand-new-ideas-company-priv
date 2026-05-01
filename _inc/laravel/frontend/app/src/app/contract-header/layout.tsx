// pages/contract/layout.tsx
import type { Metadata } from "next";
import Script from "next/script";
import { Inter } from "next/font/google";
import "@/styles/globals.css";
import "bootstrap/dist/css/bootstrap.min.css";
import { ContractLayoutProps } from "../../definitions/components";
const inter = Inter({ subsets: ["latin"] });
export const metadata: Metadata = {
<<<<<<< HEAD
  title: "ERP Brand New Ideas Company - Contract",
=======
  title: "ERPGo - Contract",
>>>>>>> 66cafc92b (fix: implement 3 orphan ProjectController routes; add 35 missing consts across 8 controllers; fix PurchaseController 12x ModelNotFoundException→404; convert 96 string literals to const refs in routes/web.php; DealController deal() visibility→protected)
  description: "Dashboard Template Description",
};
export default function ContractLayout({ children }: ContractLayoutProps) {
  const SITE_RTL = false;
  // TODO html and body expressions need to be passed to client watcher
  return (
    <html lang='en' dir={SITE_RTL ? "rtl" : "ltr"}>
      <head>
        <link rel='icon' href='/uploads/logo/favicon.png' type='image/x-icon' />
        <link href='/assets/css/plugins/main.css' rel='stylesheet' />
        <link href='/assets/css/plugins/style.css' rel='stylesheet' />
        <link
          href='/assets/css/plugins/bootstrap-switch-button.min.css'
          rel='stylesheet'
        />
        <link href='/assets/fonts/tabler-icons.min.css' rel='stylesheet' />
        <link href='/assets/fonts/feather.css' rel='stylesheet' />
        <link href='/assets/fonts/fontawesome.css' rel='stylesheet' />
        <link href='/assets/fonts/material.css' rel='stylesheet' />
        <link href='/assets/css/style.css' rel='stylesheet' />
        <link href='/css/custom.css' rel='stylesheet' />
      </head>
      <body className={inter.className}>
        <div className='container'>
          <div className='dash-content'>
            <header className='page-header'>
              <div className='page-block'>
                <div className='row align-items-center'>
                  <div className='col-md-12 mt-5 mb-4 d-flex align-items-center justify-content-between'>
                    <div>{/* Breadcrumb or Title */}</div>
                    <div>{/* Action Buttons Component */}</div>
                  </div>
                </div>
              </div>
            </header>

            <main>{children}</main>

            <footer>{/* Optional footer content */}</footer>
          </div>
        </div>
        <Script src='/js/jquery.min.js' strategy='beforeInteractive' />
        <Script
          src='/assets/js/plugins/bootstrap.min.js'
          strategy='afterInteractive'
        />
        <Script
          src='/assets/js/plugins/sweetalert2.all.min.js'
          strategy='afterInteractive'
        />
        <Script
          src='/assets/js/plugins/simple-datatables.js'
          strategy='lazyOnload'
        />
        <Script src='/js/custom.js' strategy='lazyOnload' />
      </body>
    </html>
  );
}
