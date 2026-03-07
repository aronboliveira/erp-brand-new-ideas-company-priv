/**
 * @fileoverview TypeScript version of public/js/cookie.notice.js
 * @generated from original JavaScript - manual review recommended
 * @module cookie.notice
 */

/**
 * Cookie Notice JS
 * @author Alessandro Benoit
 */

export {};

interface LocaleMap {
  [locale: string]: string;
  en: string;
}

interface CookieNoticeParams {
  messageLocales: LocaleMap;
  buttonLocales: LocaleMap;
  learnMoreLinkText: LocaleMap;
  learnMoreLinkHref: string;
  learnMoreLinkEnabled: boolean;
  noticeBgColor: string;
  noticeTextColor: string;
  linkColor: string;
  buttonBgColor: string;
  buttonTextColor: string;
  cookieNoticePosition: string;
  expiresIn: number;
}

type ExtendDefaultsFn = (
  source: CookieNoticeParams,
  properties: Partial<CookieNoticeParams>,
) => CookieNoticeParams;

interface CookieNoticeJSFunction {
  (this: object): void;
  extendDefaults?: ExtendDefaultsFn;
  clearInstance?: () => void;
}

// eslint-disable-next-line @typescript-eslint/explicit-function-return-type
(function () {
  try {
    ("use strict");

    /**
     * Store current instance
     */
    let instance: object | undefined;

    /**
     * Defaults values
     * @type object
     */
    const defaults: CookieNoticeParams = {
      messageLocales: {
        en: "We use cookies to ensure you get the best experience on our website.",
      },
      buttonLocales: {
        en: "Got it!",
      },
      learnMoreLinkText: {
        en: "Learn more",
      },
      learnMoreLinkHref: "/privacy",
      learnMoreLinkEnabled: false,
      noticeBgColor: "#000",
      noticeTextColor: "#fff",
      linkColor: "#009fdd",
      buttonBgColor: "#f1d600",
      buttonTextColor: "#000",
      cookieNoticePosition: "bottom",
      expiresIn: 30,
    };

    /**
     * Initialize cookie notice on DOMContentLoaded
     * if not already initialized with alt params
     */
    document.addEventListener("DOMContentLoaded", function (): void {
      if (!instance) {
        const CookieNotice = window.cookieNoticeJS as CookieNoticeJSFunction;
        CookieNotice.call({});
      }
    });

    /**
     * Constructor
     */
    (
      window as Window & { cookieNoticeJS: CookieNoticeJSFunction }
    ).cookieNoticeJS = function (this: object): void {
      // If an instance is already set stop here
      if (instance !== undefined) {
        return;
      }

      // Set current instance
      instance = this;

      // If cookies are not supported or notice cookie is already set
      if (!testCookie() || getNoticeCookie()) {
        return;
      }

      // Extend default params
      const params: CookieNoticeParams = extendDefaults(
        { ...defaults },
        (
          // eslint-disable-next-line prefer-rest-params
          arguments as unknown as Record<number, Partial<CookieNoticeParams>>
        )[0] || {},
      );

      // Get current locale for notice text
      const noticeText = getStringForCurrentLocale(params.messageLocales);

      // Create notice
      const notice = createNotice(
        noticeText,
        params.noticeBgColor,
        params.noticeTextColor,
        params.cookieNoticePosition,
      );

      let learnMoreLink;

      if (params.learnMoreLinkEnabled) {
        const learnMoreLinkText = getStringForCurrentLocale(
          params.learnMoreLinkText,
        );

        learnMoreLink = createLearnMoreLink(
          learnMoreLinkText,
          params.learnMoreLinkHref,
          params.linkColor,
        );
      }

      // Get current locale for button text
      const buttonText = getStringForCurrentLocale(params.buttonLocales);

      // Create dismiss button
      const dismissButton = createDismissButton(
        buttonText,
        params.buttonBgColor,
        params.buttonTextColor,
      );

      // Dismiss button click event
      dismissButton.addEventListener("click", function (e: Event) {
        e.preventDefault();
        setDismissNoticeCookie(
          parseInt(params.expiresIn + "", 10) * 60 * 1000 * 60 * 24,
        );
        fadeElementOut(notice);
      });

      // Append notice to the DOM
      const noticeDomElement = document.body.appendChild(notice);

      if (learnMoreLink) {
        noticeDomElement.appendChild(learnMoreLink);
      }

      noticeDomElement.appendChild(dismissButton);
    };

    /**
     * Get the string for the current locale
     * and fallback to "en" if none provided
     * @param locales
     * @returns {*}
     */
    // eslint-disable-next-line no-inner-declarations
    function getStringForCurrentLocale(locales: LocaleMap): string {
      const locale = (
        document.documentElement.lang ||
        navigator.language ||
        (navigator as Navigator & { userLanguage?: string }).userLanguage ||
        "en"
      ).substring(0, 2);

      return locales[locale] ? locales[locale] : locales.en;
    }

    /**
     * Test if cookies are enabled
     * @returns {boolean}
     */
    // eslint-disable-next-line no-inner-declarations, @typescript-eslint/explicit-function-return-type
    function testCookie() {
      document.cookie = "testCookie=1";
      return document.cookie.includes("testCookie");
    }

    /**
     * Test if notice cookie is there
     * @returns {boolean}
     */
    // eslint-disable-next-line no-inner-declarations, @typescript-eslint/explicit-function-return-type
    function getNoticeCookie() {
      return document.cookie.includes("cookie_notice");
    }

    /**
     * Create notice
     * @param message
     * @param bgColor
     * @param textColor
     * @param position
     * @returns {HTMLElement}
     */
    // eslint-disable-next-line no-inner-declarations
    function createNotice(
      message: string,
      bgColor: string,
      textColor: string,
      position: string,
    ): HTMLDivElement {
      const notice = document.createElement("div"),
        noticeStyle = notice.style;

      notice.innerHTML = message + "&nbsp;";
      notice.setAttribute("id", "cookieNotice");

      noticeStyle.position = "fixed";

      if (position === "top") {
        noticeStyle.top = "0";
      } else {
        noticeStyle.bottom = "0";
      }

      noticeStyle.left = "0";
      noticeStyle.right = "0";
      noticeStyle.background = bgColor;
      noticeStyle.color = textColor;
      noticeStyle.zIndex = "999";
      noticeStyle.padding = "10px 5px";
      noticeStyle.textAlign = "center";
      noticeStyle.fontSize = "12px";
      noticeStyle.lineHeight = "28px";
      noticeStyle.fontFamily = "Helvetica neue, Helvetica, sans-serif";

      return notice;
    }

    /**
     * Create dismiss button
     * @param message
     * @param buttonColor
     * @param buttonTextColor
     * @returns {HTMLElement}
     */
    // eslint-disable-next-line no-inner-declarations
    function createDismissButton(
      message: string,
      buttonColor: string,
      buttonTextColor: string,
    ): HTMLAnchorElement {
      const dismissButton = document.createElement("a"),
        dismissButtonStyle = dismissButton.style;

      // Dismiss button
      dismissButton.href = "#";
      dismissButton.innerHTML = message;

      dismissButton.className = "confirm";

      // Dismiss button style
      dismissButtonStyle.background = buttonColor;
      dismissButtonStyle.color = buttonTextColor;
      dismissButtonStyle.textDecoration = "none";
      dismissButtonStyle.display = "inline-block";
      dismissButtonStyle.padding = "0 15px";
      dismissButtonStyle.margin = "0 0 0 10px";

      return dismissButton;
    }

    /**
     * Create dismiss button
     * @param learnMoreLinkText
     * @param learnMoreLinkHref
     * @param linkColor
     * @returns {HTMLElement}
     */
    // eslint-disable-next-line no-inner-declarations
    function createLearnMoreLink(
      learnMoreLinkText: string,
      learnMoreLinkHref: string,
      linkColor: string,
    ): HTMLAnchorElement {
      const learnMoreLink = document.createElement("a"),
        learnMoreLinkStyle = learnMoreLink.style;

      // Dismiss button
      learnMoreLink.href = learnMoreLinkHref;
      learnMoreLink.textContent = learnMoreLinkText;
      learnMoreLink.target = "_blank";
      learnMoreLink.className = "learn-more";

      // Dismiss button style
      learnMoreLinkStyle.color = linkColor;
      learnMoreLinkStyle.textDecoration = "none";
      learnMoreLinkStyle.display = "inline";

      return learnMoreLink;
    }

    /**
     * Set sismiss notice cookie
     * @param expireIn
     */
    // eslint-disable-next-line no-inner-declarations
    function setDismissNoticeCookie(expireIn: number): void {
      const now = new Date(),
        cookieExpire = new Date();

      cookieExpire.setTime(now.getTime() + expireIn);
      document.cookie =
        "cookie_notice=1; expires=" + cookieExpire.toUTCString() + "; path=/;";
    }

    /**
     * Fade a given element out
     * @param element
     */
    // eslint-disable-next-line no-inner-declarations
    function fadeElementOut(element: HTMLElement): void {
      element.style.opacity = "1";
      (function fade(): void {
        const currentOpacity = parseFloat(element.style.opacity) - 0.1;
        element.style.opacity = String(currentOpacity);
        currentOpacity < 0.01
          ? element.parentNode?.removeChild(element)
          : setTimeout(fade, 40);
      })();
    }

    /**
     * Utility method to extend defaults with user options
     * @param source
     * @param properties
     * @returns {*}
     */
    // eslint-disable-next-line no-inner-declarations
    function extendDefaults(
      source: CookieNoticeParams,
      properties: Partial<CookieNoticeParams>,
    ): CookieNoticeParams {
      const result = { ...source };
      for (const property in properties) {
        if (Object.prototype.hasOwnProperty.call(properties, property)) {
          const key = property as keyof CookieNoticeParams;
          const sourceVal = result[key];
          const propVal = properties[key];
          if (
            typeof sourceVal === "object" &&
            sourceVal !== null &&
            typeof propVal === "object" &&
            propVal !== null
          ) {
            (result as Record<string, unknown>)[key] = {
              ...sourceVal,
              ...propVal,
            };
          } else if (propVal !== undefined) {
            (result as Record<string, unknown>)[key] = propVal;
          }
        }
      }
      return result;
    }

    /* test-code */
    (window.cookieNoticeJS as CookieNoticeJSFunction).extendDefaults =
      extendDefaults;
    (window.cookieNoticeJS as CookieNoticeJSFunction).clearInstance =
      function (): void {
        instance = undefined;
      };
    /* end-test-code */
  } catch (__moduleErr) {
    console.error("[cookie.notice] failed to initialise:", __moduleErr);
  }
})();
