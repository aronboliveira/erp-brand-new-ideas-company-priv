import type { Metadata } from "next";
import { Inter } from "next/font/google";
import Link from "next/link";
import "./globals.css";
import "bootstrap/dist/css/bootstrap.min.css";
const inter = Inter({ subsets: ["latin"] });
export const metadata: Metadata = {
  title: "ERP Brand New Ideas Company",
  description: "All In One Business ERP With Project, Account, HRM, CRM",
  metadataBase: new URL(process.env.NEXT_PUBLIC_APP_URL || ""),
  openGraph: {
    title: "ERP Brand New Ideas Company",
    description: "All In One Business ERP With Project, Account, HRM, CRM",
    images: "/uploads/meta/meta-image.png",
    type: "website",
  },
  twitter: {
    card: "summary_large_image",
    title: "ERP Brand New Ideas Company",
    description: "All In One Business ERP With Project, Account, HRM, CRM",
    images: "/uploads/meta/meta-image.png",
  },
};

export default function LandingLayout({
  children,
}: {
  children: React.ReactNode;
}) {
  return (
    <html lang='en'>
      <body className={`${inter.className} theme-3`}>
        <nav className='navbar navbar-expand-md navbar-dark default fixed-top'>
          <div className='container'>
            <Link className='navbar-brand bg-transparent' href='/'>
              <img
                src='/uploads/logo/logo-light.png'
                alt='Brand New Ideas Company Logo'
                style={{ width: "40%" }}
              />
            </Link>
            <button
              className='navbar-toggler'
              type='button'
              data-bs-toggle='collapse'
              data-bs-target='#navbarMenu'
            >
              <span className='navbar-toggler-icon'></span>
            </button>
            <div className='collapse navbar-collapse' id='navbarMenu'>
              <ul className='navbar-nav ms-auto'>
                {[
                  "home",
                  "features",
                  "layouts",
                  "testimonial",
                  "pricing",
                  "faq",
                ].map(link => (
                  <li key={link} className='nav-item'>
                    <Link href={`#${link}`} className='nav-link'>
                      {link.charAt(0).toUpperCase() + link.slice(1)}
                    </Link>
                  </li>
                ))}
                <li className='nav-item'>
                  <Link href='/login' className='btn btn-light ms-2'>
                    Login
                  </Link>
                </li>
                <li className='nav-item'>
                  <Link href='/register' className='btn btn-light ms-2'>
                    Register
                  </Link>
                </li>
              </ul>
            </div>
          </div>
        </nav>
        {children}
        <footer className='footer'>
          <div className='container text-end py-4'>
            <p>© 2026 Brand New Ideas Company</p>
          </div>
        </footer>
      </body>
    </html>
  );
}
