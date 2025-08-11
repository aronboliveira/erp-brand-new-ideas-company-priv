import axios from "axios";
import Swal from "sweetalert2";
export async function getAppraisalForEdit(appraisalId: string): Promise<any> {
  try {
    const res = await axios.get(`/appraisals/${appraisalId}/edit/`);
    if (!res?.data)
      throw new TypeError("No data returned from getAppraisalForEdit");
    Swal.fire({
      icon: "success",
      title: "Success",
      text: "Appraisal details for edit fetched successfully",
    });
    return res.data;
  } catch (error: any) {
    console.error(`GET appraisal for edit error: ${error?.message}`);
    Swal.fire({ icon: "error", title: "Error", text: error?.message });
  }
}
