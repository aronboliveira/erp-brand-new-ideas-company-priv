/**
 * @fileoverview TypeScript version of public/js/cookie.notice.js
 * @generated from original JavaScript - manual review recommended
 * @module cookie.notice
 */
// @ts-nocheck

/**
 * Cookie Notice JS
 * @author Alessandro Benoit
 */
(function () {
    "use strict";
    /**
     * Store current instance
     */
    let instance;
    /**
     * Defaults values
     * @type object
     */
    /**
     * Initialize cookie notice on DOMContentLoaded
     * if not already initialized with alt params
     */
    document.addEventListener("DOMContentLoaded", function () {
        if (!instance)
            new cookieNoticeJS();
    });
    /**
     * Constructor
     */
    window.cookieNoticeJS = function () {
        // If an instance is already set stop here
        if (instance !== undefined)
            return;
        // Set current instance
        instance = this;
        // If cookies are not supported or notice cookie is already set
        if (!testCookie() || getNoticeCookie())
            return;
        // Extend default params
        const params = extendDefaults(defaults, arguments[0] || {});
        // Get current locale for notice text
        const noticeText = getStringForCurrentLocale(params.messageLocales);
        // Create notice
        const notice = createNotice(noticeText, params.noticeBgColor, params.noticeTextColor, params.cookieNoticePosition);
        let learnMoreLink;
        if (params.learnMoreLinkEnabled) {
            const learnMoreLinkText = getStringForCurrentLocale(params.learnMoreLinkText);
            learnMoreLink = createLearnMoreLink(learnMoreLinkText, params.learnMoreLinkHref, params.linkColor);
        }
        // Get current locale for button text
        const buttonText = getStringForCurrentLocale(params.buttonLocales);
        // Create dismiss button
        const dismissButton = createDismissButton(buttonText, params.buttonBgColor, params.buttonTextColor);
        // Dismiss button click event
        if (!dismissButton.getAttribute("data-listener-bound-click")) {
            dismissButton.setAttribute("data-listener-bound-click", "1");
            dismissButton.addEventListener("click", function (e) {
                e.preventDefault();
                setDismissNoticeCookie(parseInt(params.expiresIn + "", 10) * 60 * 1000 * 60 * 24);
                fadeElementOut(notice);
            });
        }
        // Append notice to the DOM
        const noticeDomElement = document.body.appendChild(notice);
        if (learnMoreLink)
            noticeDomElement.appendChild(learnMoreLink);
        noticeDomElement.appendChild(dismissButton);
    };
    /**
     * Get the string for the current locale
     * and fallback to "en" if none provided
     * @param locales
     * @returns {*}
     */
    function getStringForCurrentLocale(locales) {
        const locale = (document.documentElement.lang ||
            navigator.language ||
            navigator.userLanguage).substr(0, 2);
        return locales[locale] ? locales[locale] : locales.en;
    }
    /**
     * Test if cookies are enabled
     * @returns {boolean}
     */
    function testCookie() {
        document.cookie = "testCookie=1";
        return document.cookie.includes("testCookie");
    }
    /**
     * Test if notice cookie is there
     * @returns {boolean}
     */
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
    function createNotice(message, bgColor, textColor, position) {
        const notice = document.createElement("div"), noticeStyle = notice.style;
        notice.innerHTML = message + "&nbsp;";
        notice.setAttribute("id", "cookieNotice");
        noticeStyle.position = "fixed";
        if (position === "top") {
            noticeStyle.top = "0";
        }
        else {
            noticeStyle.bottom = "0";
        }
        for (const [k, v] of Object.entries({
            left: "0",
            right: "0",
            background: bgColor,
            color: textColor,
        }))
            noticeStyle[k] = v;
        noticeStyle["z-index"] = "999";
        noticeStyle.padding = "10px 5px";
        noticeStyle["text-align"] = "center";
        noticeStyle["font-size"] = "12px";
        noticeStyle["line-height"] = "28px";
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
    function createDismissButton(message, buttonColor, buttonTextColor) {
        const dismissButton = document.createElement("a"), dismissButtonStyle = dismissButton.style;
        // Dismiss button
        dismissButton.href = "#";
        dismissButton.innerHTML = message;
        dismissButton.className = "confirm";
        // Dismiss button style
        dismissButtonStyle.background = buttonColor;
        dismissButtonStyle.color = buttonTextColor;
        dismissButtonStyle["text-decoration"] = "none";
        for (const [k, v] of Object.entries({
            display: "inline-block",
            padding: "0 15px",
            margin: "0 0 0 10px",
        }))
            dismissButtonStyle[k] = v;
        return dismissButton;
    }
    /**
     * Create dismiss button
     * @param learnMoreLinkText
     * @param learnMoreLinkHref
     * @param linkColor
     * @returns {HTMLElement}
     */
    function createLearnMoreLink(learnMoreLinkText, learnMoreLinkHref, linkColor) {
        const learnMoreLink = document.createElement("a"), learnMoreLinkStyle = learnMoreLink.style;
        // Dismiss button
        for (const [k, v] of Object.entries({
            href: learnMoreLinkHref,
            textContent: learnMoreLinkText,
            target: "_blank",
            className: "learn-more",
        }))
            learnMoreLink[k] = v;
        // Dismiss button style
        learnMoreLinkStyle.color = linkColor;
        learnMoreLinkStyle["text-decoration"] = "none";
        learnMoreLinkStyle.display = "inline";
        return learnMoreLink;
    }
    /**
     * Set sismiss notice cookie
     * @param expireIn
     */
    function setDismissNoticeCookie(expireIn) {
        const now = new Date(), cookieExpire = new Date();
        cookieExpire.setTime(now.getTime() + expireIn);
        document.cookie =
            "cookie_notice=1; expires=" + cookieExpire.toUTCString() + "; path=/;";
    }
    /**
     * Fade a given element out
     * @param element
     */
    function fadeElementOut(element) {
        element.style.opacity = 1;
        (function fade() {
            (element.style.opacity -= 0.1) < 0.01
                ? element.parentNode.removeChild(element)
                : setTimeout(fade, 40);
        })();
    }
    /**
     * Utility method to extend defaults with user options
     * @param source
     * @param properties
     * @returns {*}
     */
    function extendDefaults(source, properties) {
        let property;
        for (property in properties) {
            if (properties.hasOwnProperty(property)) {
                if (typeof source[property] === "object") {
                    source[property] = extendDefaults(source[property], properties[property]);
                }
                else {
                    source[property] = properties[property];
                }
            }
        }
        return source;
    }
    /* test-code */
    cookieNoticeJS.extendDefaults = extendDefaults;
    cookieNoticeJS.clearInstance = function () {
        instance = undefined;
    };
    /* end-test-code */
})();
//# sourceMappingURL=cookie.notice.js.map