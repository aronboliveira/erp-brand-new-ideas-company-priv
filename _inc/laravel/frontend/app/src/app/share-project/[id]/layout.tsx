// app/(dashboard)/projects/[id]/layout.tsx
import type { Metadata } from "next";
import { getProjectSettings } from "../../../../frontend/settings";
import "bootstrap/dist/css/bootstrap.min.css";
import "@/assets/css/style.css";
import "@/assets/css/custom.css";
import { ShareProjectLayoutProps } from "../../../definitions/components";
export async function generateMetadata({
  params,
}: ShareProjectLayoutProps): Promise<Metadata> {
  const settings = await getProjectSettings(params.id);
  return {
    title: settings.title_text || "ERP Brand New Ideas Company",
    description: "Dashboard Template Description",
    icons: {
      icon: settings.company_favicon || "/uploads/logo/favicon.png",
    },
  };
}
// TODO html and body expressions need to be passed to client watcher
export default function ProjectLayout({ children }: ShareProjectLayoutProps) {
  return (
    <html lang='en'>
      <body className='theme-3'>
        <div className='container'>
          <div className='dash-content'>
            <div className='page-header'>
              <div className='page-block'>
                <div className='row align-items-center'>
                  <div className='col-md-12 my-4 d-flex justify-content-end'>
                    {/* @todo Add action button logic */}
                  </div>
                </div>
              </div>
            </div>
            {children}
          </div>
        </div>
        <div className='modal fade' id='commonModal' tabIndex={-1}>
          <div className='modal-dialog'>
            <div className='modal-content'>
              <div className='modal-header'>
                <h5 className='modal-title'></h5>
                <button className='btn-close' data-bs-dismiss='modal' />
              </div>
              <div className='body'></div>
            </div>
          </div>
        </div>
      </body>
    </html>
  );
}
