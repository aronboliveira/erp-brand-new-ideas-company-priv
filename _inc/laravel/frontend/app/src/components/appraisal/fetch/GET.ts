import { EmployeeOption } from "@/definitions/helpers";
import axios from "axios";
import Swal from "sweetalert2";
export async function getEmployeesByBranch(
  branchId: string
): Promise<EmployeeOption[]> {
  try {
    const res = await axios.post("/getemployee", {
      branch_id: branchId,
      _token: "CSRF_TOKEN_PLACEHOLDER",
    });
    if (!res?.data) throw new TypeError("Failed to fetch data");
    return res.data.employee;
  } catch (err: any) {
    console.error(`Failed to GET: ${err.message}`);
    Swal.fire({ icon: "error", title: "Error", text: `${err.message}` });
    return [];
  }
}
export async function getAppraisal(appraisalId: string): Promise<any> {
  try {
    const res = await axios.get(`/appraisals/${appraisalId}/show/`);
    if (!res?.data) throw new TypeError("No data returned from getAppraisal");
    Swal.fire({
      icon: "success",
      title: "Success",
      text: "Appraisal details fetched successfully",
    });
    return res.data;
  } catch (error: any) {
    console.error(`GET appraisal error: ${error?.message}`);
    Swal.fire({ icon: "error", title: "Error", text: error?.message });
  }
}
