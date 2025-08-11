import Swal from "sweetalert2";
import axios from "axios";
import { AppraisalFormData, EmployeeOption } from "@/definitions/helpers";
import b from "@/lib/configs/obfuscate";
export async function postAppraisal(form: AppraisalFormData): Promise<boolean> {
  try {
    const res = await axios.post(
      b("61.61.65.98.104.78.88.97.104.74.72.99.119.70.50.76"),
      form,
      {
        headers: { "Content-Type": "application/json" },
      }
    );
    if (!res?.data) throw new TypeError("Failed to create appraisal");
    Swal.fire({
      icon: "success",
      title: "Success",
      text: "Appraisal created successfully",
    });
    return true;
  } catch (err: any) {
    console.error(`Error in postAppraisal: ${err.message}`);
    Swal.fire({ icon: "error", title: "Error", text: `${err.message}` });
    return false;
  }
}
export async function fetchEmployeesByBranch(
  branchId: string
): Promise<EmployeeOption[]> {
  try {
    const res = await axios.post(
      b("108.86.87.101.118.120.71.99.116.86.71.100.108.100.50.76"),
      {
        branch_id: branchId,
        _token: "CSRF_TOKEN_PLACEHOLDER",
      }
    );
    if (!res?.data) throw new TypeError("Failed to fetch employee data");
    return res.data.employee;
  } catch (err: any) {
    console.error(`Error in fetchEmployeesByBranch: ${err.message}`);
    Swal.fire({ icon: "error", title: "Error", text: `${err.message}` });
    return [];
  }
}
export async function fetchEmpByStar(employeeId: string): Promise<string> {
  try {
    const res = await axios.post(
      b("61.77.72.98.104.78.88.97.104.74.72.99.119.70.50.76"),
      {
        employee: employeeId,
        _token: "CSRF_TOKEN_PLACEHOLDER",
      }
    );
    if (!res?.data) throw new TypeError("Failed to fetch star data");
    return res.data.html;
  } catch (err: any) {
    console.error(`Error in fetchEmpByStar: ${err.message}`);
    Swal.fire({ icon: "error", title: "Error", text: `${err.message}` });
    return "";
  }
}
export async function fetchEmpByStar1(
  employeeId: string,
  appraisalId: string
): Promise<string> {
  try {
    const res = await axios.post(
      b("120.77.72.98.104.78.88.97.104.74.72.99.119.70.50.76"),
      {
        employee: employeeId,
        appraisal: appraisalId,
        _token: "CSRF_TOKEN_PLACEHOLDER",
      }
    );
    if (!res?.data)
      throw new TypeError("Failed to fetch star data (empByStar1)");
    return res.data.html;
  } catch (err: any) {
    console.error(`Error in fetchEmpByStar1: ${err.message}`);
    Swal.fire({ icon: "error", title: "Error", text: `${err.message}` });
    return "";
  }
}
