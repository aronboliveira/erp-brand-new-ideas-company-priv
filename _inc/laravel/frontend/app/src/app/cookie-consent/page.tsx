import CookieConsent from "../../components/info/CookieConsent";
export default function CookieConsentLayout({ children }) {
  return (
    <>
      {children}
      <CookieConsent
        cookieTitle='Your cookie title'
        cookieDescription='Your cookie description'
        strictlyCookieTitle='Strictly necessary cookies'
        strictlyCookieDescription='These are essential...'
        moreInfoDescription='For more information'
        contactUrl='/contact-us'
      />
    </>
  );
}
