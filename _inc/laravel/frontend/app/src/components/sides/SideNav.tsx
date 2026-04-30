"use client";
import Link from "next/link";
import { usePathname } from "next/navigation";
import { NavLink } from "../../definitions/helpers";
const navLinks: NavLink[] = [
  { href: "/pipelines", label: "Pipeline" },
  { href: "/lead-stages", label: "Lead Stages" },
  { href: "/stages", label: "Deal Stages" },
  { href: "/sources", label: "Sources" },
  { href: "/labels", label: "Labels" },
  { href: "/contract-type", label: "Contract Type" },
];
export default function SideNav() {
  const pathname = usePathname();
  return (
    <div className='card sticky-top' style={{ top: "30px" }}>
      <div className='list-group list-group-flush' id='useradd-sidenav'>
        {navLinks.map(({ href, label }) => (
          <Link
            key={href}
            href={href}
            className={`list-group-item list-group-item-action border-0${
              pathname === href ? " active" : ""
            }`}
          >
            {label}
            <div className='float-end'>
              <i className='ti ti-chevron-right' />
            </div>
          </Link>
        ))}
      </div>
    </div>
  );
}
