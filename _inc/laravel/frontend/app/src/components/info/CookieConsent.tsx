"use client";
import React, { JSX, memo, useEffect, useState } from "react";
import Script from "next/script";
import { postCookie } from "./fetch/POST";
import { CookieConsentProps } from "../../definitions/components";
import { ErrorBoundary } from "../../../node_modules/react-error-boundary/dist";
declare global {
  interface Window {
    initCookieConsent: () => {
      run: (config: any) => void;
    };
  }
}
const CookieConsent = ({
  cookieTitle,
  cookieDescription,
  strictlyCookieTitle,
  strictlyCookieDescription,
  moreInfoDescription,
  contactUrl,
}: CookieConsentProps): JSX.Element => {
  const [scriptLoaded, setScriptLoaded] = useState(false),
    handleScriptLoad = () => setScriptLoaded(true);
  useEffect(() => {
    if (
      !scriptLoaded ||
      typeof window === "undefined" ||
      !window.initCookieConsent
    )
      return;
    try {
      const cc = window.initCookieConsent();
      cc.run({
        current_lang: "en",
        autoclear_cookies: true,
        page_scripts: true,
        gui_options: {
          consent_modal: {
            layout: "cloud",
            position: "bottom center",
            transition: "slide",
            swap_buttons: false,
          },
          settings_modal: { layout: "box", transition: "slide" },
        },
        categories: {
          necessary: {
            enabled: true, // Always enabled
            readOnly: true, // User cannot toggle
          },
          analytics: {
            enabled: false,
            readOnly: false,
          },
        },
        onAccept: (cookie: any) => postCookie(cookie),
        languages: {
          en: {
            consent_modal: {
              title: cookieTitle,
              description: `${cookieDescription}. <button type="button" data-cc="c-settings" class="cc-link">Let me choose</button>`,
              primary_btn: { text: "Accept all", role: "accept_all" },
              secondary_btn: { text: "Reject all", role: "accept_necessary" },
            },
            settings_modal: {
              title: "Cookie preferences",
              save_settings_btn: "Save settings",
              accept_all_btn: "Accept all",
              reject_all_btn: "Reject all",
              close_btn_label: "Close",
              cookie_table_headers: [
                { col1: "Name" },
                { col2: "Domain" },
                { col3: "Expiration" },
                { col4: "Description" },
              ],
              blocks: [
                { title: cookieTitle, description: `${cookieDescription}.` },
                {
                  title: strictlyCookieTitle,
                  description: strictlyCookieDescription,
                  toggle: { value: "necessary", enabled: true, readonly: true },
                },
                {
                  title: "More information",
                  description: `${moreInfoDescription} <a class="cc-link" href="${contactUrl}">contact us</a>.`,
                },
              ],
            },
          },
        },
      });
    } catch (error) {
      console.error("Failed to initialize cookie consent:", error);
    }
  }, [
    scriptLoaded,
    cookieTitle,
    cookieDescription,
    strictlyCookieTitle,
    strictlyCookieDescription,
    moreInfoDescription,
    contactUrl,
  ]);
  return (
    <ErrorBoundary FallbackComponent={() => <></>}>
      <link
        rel='stylesheet'
        href='/css/cookieconsent.css'
        media='screen'
        id='cookieconsent-style'
      />
      <Script
        src='/js/cookieconsent.js'
        strategy='beforeInteractive'
        id='cookieconsent-script'
        onLoad={handleScriptLoad}
      />
    </ErrorBoundary>
  );
};
export default memo(CookieConsent);
