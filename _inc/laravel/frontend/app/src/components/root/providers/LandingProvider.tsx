"use client";
import { Parent } from "@/definitions/components";
import { NlHtEl, RMouseEvent } from "@/definitions/helpers";
import { createContext, JSX, useState } from "react";
import Image from "next/image";
import { ErrorBoundary } from "react-error-boundary";
import {
  AppBar,
  Box,
  Container,
  IconButton,
  Toolbar,
  Button,
} from "@mui/material";
import Anchor from "../bloc/Anchor";
import Link from "next/link";
import MenuIcon from "@mui/icons-material/Menu";
import styles from "@/styles/modules/landingpage.module.scss";
export const LandingCtx = createContext({});
export default function LandingProvider({ children }: Parent): JSX.Element {
  const [anchorEl, setAnchorEl] = useState<NlHtEl>(null),
    handleMenuOpen = (event: RMouseEvent<HTMLElement>): void =>
      setAnchorEl(event.currentTarget),
    handleMenuClose = (): void => setAnchorEl(null),
    navLinks = ["home", "features", "layouts", "testimonial", "pricing", "faq"];
  return (
    <ErrorBoundary FallbackComponent={() => <></>}>
      <LandingCtx.Provider value={{}}>
        <AppBar position='fixed' className={"appBar"}>
          <Container>
            <Toolbar disableGutters>
              <Link href='/' passHref>
                <Box component='div' className={"brand"}>
                  <Image
<<<<<<< HEAD
                    src='/logo-brand new ideas company-2.webp'
                    alt='Nova Brand New Ideas Company Logo'
=======
                    src='/logo-prestech-2.webp'
                    alt='Nova Prestech Logo'
>>>>>>> 66cafc92b (fix: implement 3 orphan ProjectController routes; add 35 missing consts across 8 controllers; fix PurchaseController 12x ModelNotFoundException→404; convert 96 string literals to const refs in routes/web.php; DealController deal() visibility→protected)
                    className={"brandImage"}
                    width={92}
                    height={32}
                    objectFit='contain'
                  />
                </Box>
              </Link>
              <Box sx={{ flexGrow: 1 }} />
              <Box className={"navLinksDesktop"}>
                {navLinks.map(link => (
                  <Link key={link} href={`#${link}`} passHref>
                    <Button className={styles.navLink}>
                      {link.charAt(0).toUpperCase() + link.slice(1)}
                    </Button>
                  </Link>
                ))}
                <Link href='/login' passHref>
                  <Button variant='contained' className={"navButton"}>
                    Login
                  </Button>
                </Link>
                <Link href='/register' passHref>
                  <Button variant='contained' className={"navButton"}>
                    Register
                  </Button>
                </Link>
              </Box>
              <Box className={"navLinksMobile"}>
                <IconButton
                  edge='end'
                  color='inherit'
                  aria-label='menu'
                  onClick={handleMenuOpen}
                >
                  <MenuIcon />
                </IconButton>
                <Anchor
                  anchorEl={anchorEl}
                  navLinks={navLinks}
                  handleMenuClose={handleMenuClose}
                />
              </Box>
            </Toolbar>
          </Container>
        </AppBar>
        {children}
      </LandingCtx.Provider>
    </ErrorBoundary>
  );
}
