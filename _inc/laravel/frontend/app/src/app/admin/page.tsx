import { useEffect, useMemo } from "react";
import AdminMenu from "@/components/partials/AdminMenu";
import AdminHeader from "@/components/partials/AdminHeader";
import AdminFooter from "@/components/partials/AdminFooter";
import NotificationModal from "@/components/modals/NotificationModal";
import CommonModal from "@/components/modals/CommonModal";
import { AdminLayoutProps } from "../../definitions/components";
import { ErrorBoundary } from "../../../node_modules/react-error-boundary/dist";
export default function AdminPage({
  children,
  title,
  breadcrumb,
  actionBtn,
  settings,
}: AdminLayoutProps) {
  const themeColor = settings.color || "theme-3";
  useEffect(() => {
    document.body.className = themeColor;
  }, [themeColor]);
  return (
    <ErrorBoundary FallbackComponent={() => <></>}>
      <div className='loader-bg'>
        <div className='loader-track'>
          <div className='loader-fill'></div>
        </div>
      </div>
      <AdminMenu />
      <AdminHeader />
      <NotificationModal />
      <div className='dash-container'>
        <div className='dash-content'>
          <div className='page-header'>
            <div className='page-block'>
              <div className='row align-items-center'>
                <div className='col-auto'>
                  <h4 className='m-b-10'>{title}</h4>
                  <ul className='breadcrumb'>{breadcrumb}</ul>
                </div>
                <div className='col'>{actionBtn}</div>
              </div>
            </div>
          </div>
          {children}
        </div>
      </div>
      <CommonModal modalId='commonModal' />
      <CommonModal modalId='commonModalOver' />
      <div className='position-fixed top-0 end-0 p-3' style={{ zIndex: 99999 }}>
        <div
          id='liveToast'
          className='toast text-white fade'
          role='alert'
          aria-live='assertive'
          aria-atomic='true'
        >
          <div className='d-flex'>
            <div className='toast-body'></div>
            <button
              type='button'
              className='btn-close btn-close-white me-2 m-auto'
              data-bs-dismiss='toast'
              aria-label='Close'
            ></button>
          </div>
        </div>
      </div>
      <AdminFooter />
    </ErrorBoundary>
  );
}
