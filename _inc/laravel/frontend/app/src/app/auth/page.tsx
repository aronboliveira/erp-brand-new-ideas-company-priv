"use client";
import { useEffect } from "react";
import Image from "next/image";
import {
  Box,
  AppBar,
  Toolbar,
  Container,
  Card,
  Typography,
  IconButton,
  CssBaseline,
  ThemeProvider,
  createTheme,
} from "@mui/material";
import MenuIcon from "@mui/icons-material/Menu";
import { getSettings, getLogoUrl } from "../../../frontend/settings";
import { AuthLayoutProps } from "../../definitions/components";
import CookieConsent from "@/components/info/CookieConsent";
import { ErrorBoundary } from "../../../node_modules/react-error-boundary/dist";
export default function AuthPage({ children }: AuthLayoutProps) {
  const settings = getSettings(),
    locale = navigator.language || "en",
    isRTL = ["ar", "he"].includes(locale) || settings.SITE_RTL === "on",
    darkMode = settings.cust_darklayout === "on",
    theme = createTheme({
      direction: isRTL ? "rtl" : "ltr",
      palette: {
        mode: darkMode ? "dark" : "light",
        primary: {
          main: settings.color ? `#${settings.color}` : "#3f51b5",
        },
      },
    });
  useEffect(() => {
    document.documentElement.dir = theme.direction;
  }, [theme.direction]);
  return (
    <ThemeProvider theme={theme}>
      <CssBaseline />
      <ErrorBoundary FallbackComponent={() => <></>}>
        <Box
          sx={{
            position: "relative",
            minHeight: "100vh",
            display: "flex",
            flexDirection: "column",
          }}
        >
          {/* Background Images */}
          <Box
            sx={{
              position: "fixed",
              top: 0,
              left: 0,
              width: "100vw",
              height: "100vh",
              zIndex: -1,
            }}
          >
            <Box
              sx={{
                position: "absolute",
                top: 0,
                left: 0,
                width: "100%",
                height: "100%",
              }}
            >
              <Image
                src={`/assets/images/auth/${settings.color || "theme-3"}.svg`}
                alt='Background Image'
                layout='fill'
                objectFit='cover'
                priority
              />
            </Box>
            <Box
              sx={{
                position: "absolute",
                top: 0,
                left: 0,
                width: "100%",
                height: "100%",
              }}
            >
              <Image
                src='/assets/images/auth/common.svg'
                alt='Background Overlay'
                layout='fill'
                objectFit='cover'
              />
            </Box>
          </Box>
          {/* Header */}
          <AppBar position='static' color='transparent' elevation={0}>
            <Container maxWidth='lg'>
              <Toolbar>
                <Box sx={{ flexGrow: 1 }}>
                  {/* <Image
                    src={
                      darkMode
                        ? `${getLogoUrl("logo")}/${
                            settings.company_logo_dark || "logo-light.png"
                          }`
                        : `${getLogoUrl("logo")}/${
                            settings.company_logo_light || "logo-dark.png"
                          }`
                    }
                    alt='Company Logo'
                    width={150}
                    height={40}
                  /> */}
                </Box>
                <IconButton
                  color='inherit'
                  edge='start'
                  aria-label='menu'
                  sx={{ display: { xs: "flex", md: "none" } }}
                >
                  <MenuIcon />
                </IconButton>
              </Toolbar>
            </Container>
          </AppBar>{" "}
          {/* Main Content */}
          <Container
            component='main'
            maxWidth='xs'
            sx={{
              flexGrow: 1,
              display: "flex",
              alignItems: "center",
              py: 4,
            }}
          >
            <Card
              sx={{
                width: "100%",
                p: 4,
                borderRadius: 2,
                boxShadow: theme.shadows[4],
              }}
            >
              {children}
            </Card>
          </Container>{" "}
          {/* Footer */}
          <Box component='footer' sx={{ py: 3 }}>
            <Container maxWidth='lg'>
              <Typography variant='body2' color='text.secondary' align='center'>
                &copy; {new Date().getFullYear()}{" "}
                {settings.footer_text ||
                  process.env.NEXT_PUBLIC_APP_NAME ||
                  "Storego SaaS"}
              </Typography>
            </Container>
          </Box>
        </Box>{" "}
        {/* {settings.enable_cookie === "on" &&  */}
        //TODO ENABLE COOKIES LATER
        {/* <CookieConsent /> */}
        {/* } */}
      </ErrorBoundary>
    </ThemeProvider>
  );
}
