import {
  Box,
  Button,
  Card,
  CardContent,
  Container,
  Grid,
  Typography,
  Accordion,
  AccordionSummary,
  AccordionDetails,
} from "@mui/material";
import ExpandMoreIcon from "@mui/icons-material/ExpandMore";
import styles from "../styles/modules/landingpage.module.scss";
import React, { JSX } from "react";
import { Parent } from "@/definitions/components";
import { lazy } from "react";
const Video = lazy(() => import("../components/root/assets/LandingVideo"));
export default function LandingPage({ children }: Parent): JSX.Element {
  return (
    <>
      <Box component='header' id='home' className={styles.home}>
        <Container>
          <Grid container alignItems='center' spacing={4}>
            <Grid item xs={12} sm={5}>
              <Typography variant='h2' component='h1' className={styles.title}>
                ERP Nova Prestech
              </Typography>
              <hr className={styles.separator} />
              <Typography
                variant='h5'
                component='h2'
                className={styles.subtitle}
              >
                ERP de Negócios completo, com recursos de CRM, HRM. Gerencie
                suas equipes e processos com excelência!
              </Typography>
              <Typography variant='body1' className={styles.description}>
                Use these awesome forms to login or create new account in your
                project for free.
              </Typography>
              <hr className={styles.separator} />
              <Box className={styles.buttonGroup}>
                <Button
                  variant='contained'
                  color='inherit'
                  className={`${styles.button} ${styles.demoButton}`}
                  href='/login'
                >
                  Live Demo
                </Button>
                <Button
                  variant='outlined'
                  color='inherit'
                  className={`${styles.button} ${styles.buyButton}`}
                  href='/'
                >
                  Buy now
                </Button>
              </Box>
            </Grid>
            <Grid item xs={12} sm={5}>
              <Video />
            </Grid>
          </Grid>
        </Container>
      </Box>
      <Box component='section' id='features' className={styles.features}>
        <Container>
          <Typography
            variant='h4'
            component='h2'
            className={styles.sectionTitle}
          >
            Features
          </Typography>
          <Grid container spacing={4}>
            {Array.from({ length: 4 }).map((_, i) => (
              <Grid item xs={12} sm={6} md={3} key={i}>
                <Card className={styles.featureCard}>
                  <CardContent className={styles.featureContent}>
                    <Box
                      component='i'
                      className={`ti ti-report-money ${styles.featureIcon}`}
                    />
                    <Typography variant='h6' className={styles.featureTitle}>
                      Feature
                    </Typography>
                    <Typography
                      variant='body2'
                      className={styles.featureDescription}
                    >
                      Use these awesome forms to login or create new account in
                      your project for free.
                    </Typography>
                  </CardContent>
                </Card>
              </Grid>
            ))}
          </Grid>
        </Container>
      </Box>
      <Box component='section' id='pricing' className={styles.pricing}>
        <Container>
          <Typography
            variant='h4'
            component='h2'
            className={styles.sectionTitle}
          >
            Pricing
          </Typography>
          <Grid container spacing={4}>
            {[59, 59, 119].map((price, i) => (
              <Grid item xs={12} md={4} key={i}>
                <Card
                  className={`${styles.priceCard} ${
                    i === 1 ? styles.activePriceCard : ""
                  }`}
                >
                  <CardContent className={styles.priceContent}>
                    <Box
                      component='span'
                      className={`${styles.badge} ${
                        i === 1 ? styles.activeBadge : ""
                      }`}
                    >
                      STARTER
                    </Box>
                    <Typography variant='h5' className={styles.price}>
                      ${price}/month
                    </Typography>
                    <Box component='ul' className={styles.priceFeatures}>
                      <li>2 team members</li>
                      <li>20GB Cloud storage</li>
                      <li>Integration help</li>
                    </Box>
                    <Button
                      variant='contained'
                      className={
                        i === 1 ? styles.activePlanButton : styles.planButton
                      }
                    >
                      Start with plan
                    </Button>
                  </CardContent>
                </Card>
              </Grid>
            ))}
          </Grid>
        </Container>
      </Box>
      <Box component='section' id='faq' className={styles.faq}>
        <Container>
          <Typography
            variant='h4'
            component='h2'
            className={styles.sectionTitle}
          >
            Frequently Asked Questions
          </Typography>
          {["How do I order?", "How to get support?", "What is included?"].map(
            (q, idx) => (
              <Accordion
                key={idx}
                defaultExpanded={idx === 0}
                className={styles.accordion}
              >
                <AccordionSummary
                  expandIcon={<ExpandMoreIcon />}
                  className={styles.accordionSummary}
                >
                  <Typography>{q}</Typography>
                </AccordionSummary>
                <AccordionDetails className={styles.accordionDetails}>
                  <Typography>
                    Use these awesome forms to login or create new account in
                    your project for free.
                  </Typography>
                </AccordionDetails>
              </Accordion>
            )
          )}
        </Container>
      </Box>
      {children}
    </>
  );
}
