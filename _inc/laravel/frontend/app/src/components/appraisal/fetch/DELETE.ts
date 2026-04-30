import axios from "axios";
import Swal from "sweetalert2";
export async function deleteAppraisal(appraisalId: string): Promise<any> {
  try {
    const res = await axios.delete(`/appraisals/${appraisalId}/delete/`);
    if (!res?.data)
      throw new TypeError("No data returned from deleteAppraisal");
    Swal.fire({
      icon: "success",
      title: "Deleted",
      text: "Appraisal deleted successfully",
    });
    return res.data;
  } catch (error: any) {
    console.error(`DELETE appraisal error: ${error?.message}`);
    Swal.fire({ icon: "error", title: "Error", text: error?.message });
  }
}
