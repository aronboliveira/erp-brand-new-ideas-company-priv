import axios from "axios";
export const getCookie = (name: string) => {
  const cname = name + "=",
    decodedCookie = decodeURIComponent(document.cookie),
    ca = decodedCookie.split(";");
  for (let i = 0; i < ca.length; i++) {
    let c = ca[i].trim();
    if (c.indexOf(cname) === 0) return c.substring(cname.length, c.length);
  }
  return "";
};
export const setCookie = (name: string, value: string, days: number) => {
  const d = new Date();
  d.setTime(d.getTime() + days * 24 * 60 * 60 * 1000);
  document.cookie = `${name}=${value};expires=${d.toUTCString()};path=/`;
};
export async function postCookie(cookie: any) {
  try {
    if (!getCookie("cookie_consent_logged")) {
      await axios.post("/cookie-consent", {
        cookie: cookie.level,
      });
      setCookie("cookie_consent_logged", "1", 182);
    }
  } catch (error) {
    console.warn(`Failed to post cookie: ${error}`);
  }
}
