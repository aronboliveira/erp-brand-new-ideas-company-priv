import { Settings } from "../src/definitions/helpers";
import axios from "axios";
import __ from "../src/lib/configs/obfuscate";
export function getSettings(): Settings {
  return {
    color: "theme-3",
    SITE_RTL: "off",
    cust_darklayout: "off",
    meta_title: "App Meta Title",
    meta_desc: "App Meta Description",
    meta_image: "meta-image.png",
    company_favicon: "favicon.png",
    company_logo_dark: "logo-dark.png",
    company_logo_light: "logo-light.png",
    title_text: "My App",
    footer_text: "My Company",
    enable_cookie: "on",
    ...process.env,
  };
}
export function getLogoUrl(type: "logo" | "favicon" | "meta") {
  return `${process.env.NEXT_PUBLIC_APP_URL}/uploads/${type}`;
}
export const getProjectSettings = async (projectId: string) => {
  try {
    const res = await axios.get(
      `${__(
        "61.61.119.76.122.82.51.89.108.112.50.98.121.66.51.76"
      )}${projectId}${__("122.100.109.98.112.82.72.100.108.78.51.76")}`
    );
    if (res?.data) return res.data;
    throw new Error("Settings not found");
  } catch (err) {
    console.error(`Failed to load settings for project ${projectId}: ${err}`);
    return {
      title_text: "ERPGo",
      company_favicon: "/uploads/logo/favicon.png",
    };
  }
};
