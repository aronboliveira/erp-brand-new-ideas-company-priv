// app/hr/layout.tsx
import HRSideNav from "@/components/HRSideNav";
export default function HRLayout({ children }: { children: React.ReactNode }) {
  const userPermissions = ["manage performance type"]; // fetched from your auth system
  return (
    <div className='container'>
      <div className='row'>
        <aside className='col-3'>
          <HRSideNav permissions={userPermissions} />
        </aside>
        <main className='col-9'>{children}</main>
      </div>
    </div>
  );
}
