"use client";
import Link from "next/link";
import { usePathname } from "next/navigation";
import { HRNavLink, HRSideNavProps } from "../../definitions/helpers";
const hrNavLinks: HRNavLink[] = [
  { href: "/branch", label: "Branch" },
  { href: "/department", label: "Department" },
  { href: "/designation", label: "Designation" },
  { href: "/leave-type", label: "Leave Type" },
  { href: "/document-type", label: "Document Type" },
  { href: "/payslip-type", label: "Payslip Type" },
  { href: "/allowance-option", label: "Allowance Option" },
  { href: "/loan-option", label: "Loan Option" },
  { href: "/deduction-option", label: "Deduction Option" },
  { href: "/goal-type", label: "Goal Type" },
  { href: "/training-type", label: "Training Type" },
  { href: "/award-type", label: "Award Type" },
  { href: "/termination-type", label: "Termination Type" },
  { href: "/job-category", label: "Job Category" },
  { href: "/job-stage", label: "Job Stage" },
  {
    href: "/performance-type",
    label: "Performance Type",
    permission: "manage performance type",
  },
  { href: "/competencies", label: "Competencies" },
];
export default function HRSideNav({ permissions = [] }: HRSideNavProps) {
  const pathname = usePathname();
  const hasPermission = (permission?: string) =>
    permission ? permissions.includes(permission) : true;
  return (
    <div className='card sticky-top' style={{ top: "30px" }}>
      <div className='list-group list-group-flush' id='useradd-sidenav'>
        {hrNavLinks.map(
          ({ href, label, permission }) =>
            hasPermission(permission) && (
              <Link
                key={href}
                href={href}
                className={`list-group-item list-group-item-action border-0${
                  pathname.startsWith(href) ? " active" : ""
                }`}
              >
                {label}
                <div className='float-end'>
                  <i className='ti ti-chevron-right' />
                </div>
              </Link>
            )
        )}
      </div>
    </div>
  );
}
