import { useEffect, useMemo } from "react";
import { useRouter } from "next/router";
import { AdminLayoutProps } from "../../definitions/components";
export default function AdminLayout({ title, settings }: AdminLayoutProps) {
  const router = useRouter(),
    isRTL = useMemo(
      () =>
        ["ar", "he"].includes(router.locale || "") ||
        settings.SITE_RTL === true,
      [router.locale, settings.SITE_RTL]
    ),
    themeColor = settings.color || "theme-3";
  useEffect(() => {
    document.body.className = themeColor;
  }, [themeColor]);
  return (
    <head>
<<<<<<< HEAD
      <title>
        {(settings.title_text || "ERP Brand New Ideas Company") +
          " - " +
          (title || "")}
      </title>
=======
      <title>{(settings.title_text || "ERPGO") + " - " + (title || "")}</title>
>>>>>>> 66cafc92b (fix: implement 3 orphan ProjectController routes; add 35 missing consts across 8 controllers; fix PurchaseController 12x ModelNotFoundException→404; convert 96 string literals to const refs in routes/web.php; DealController deal() visibility→protected)
      <meta name='title' content={settings.meta_title || ""} />
      <meta name='description' content={settings.meta_desc || ""} />
      <meta property='og:type' content='website' />
      <meta property='og:url' content={settings.APP_URL} />
      <meta property='og:title' content={settings.meta_title || ""} />
      <meta property='og:description' content={settings.meta_desc || ""} />
      <meta
        property='og:image'
        content={`${settings.APP_URL}/uploads/meta/${settings.meta_image}`}
      />
      <meta property='twitter:card' content='summary_large_image' />
      <meta property='twitter:url' content={settings.APP_URL} />
      <meta property='twitter:title' content={settings.meta_title || ""} />
      <meta property='twitter:description' content={settings.meta_desc || ""} />
      <meta
        property='twitter:image'
        content={`${settings.APP_URL}/uploads/meta/${settings.meta_image}`}
      />
      <meta charSet='utf-8' />
      <meta
        name='viewport'
        content='width=device-width,initial-scale=1,user-scalable=0,minimal-ui'
      />
      <meta httpEquiv='X-UA-Compatible' content='IE=edge' />
      <meta
        name='url'
        content={`${settings.APP_URL}/${settings.chatifyPath}`}
        data-user='{{userId}}'
      />
      <meta name='csrf-token' content={settings.csrf_token} />
      <link
        rel='icon'
        href={`${settings.APP_URL}/uploads/logo/${
          settings.company_favicon || "favicon.png"
        }`}
        sizes='16x16'
      />
      <link href='/assets/css/plugins/main.css' rel='stylesheet' />
      <link href='/assets/css/plugins/style.css' rel='stylesheet' />
      <link href='/assets/css/plugins/flatpickr.min.css' rel='stylesheet' />
      <link href='/assets/css/plugins/animate.min.css' rel='stylesheet' />
      <link href='/assets/fonts/tabler-icons.min.css' rel='stylesheet' />
      <link href='/assets/fonts/feather.css' rel='stylesheet' />
      <link href='/assets/fonts/fontawesome.css' rel='stylesheet' />
      <link href='/assets/fonts/material.css' rel='stylesheet' />
      <link
        href='/assets/css/plugins/bootstrap-switch-button.min.css'
        rel='stylesheet'
      />
      <link
        href={`/assets/css/${
          settings.cust_darklayout ? "style-dark.css" : "style.css"
        }`}
        rel='stylesheet'
      />
      {isRTL && <link href='/assets/css/style-rtl.css' rel='stylesheet' />}
      <link href='/assets/css/customizer.css' rel='stylesheet' />
      <link href='/css/custom.css' rel='stylesheet' />
      {settings.cust_darklayout && (
        <link href='/css/custom-dark.css' rel='stylesheet' />
      )}
    </head>
  );
}
