import SideNav from "@/components/SideNav";
export default function Layout({ children }) {
  return (
    <div className='container'>
      <SideNav />
      <main>{children}</main>
    </div>
  );
}
