import { AppraisalFormData } from "@/definitions/helpers";
import axios from "axios";
import Swal from "sweetalert2";
import __ from "@/lib/configs/obfuscate";
export async function updateAppraisal(
  appraisalId: string,
  formData: AppraisalFormData
): Promise<boolean> {
  try {
    const res = await axios.put(
      `${__("61.61.65.98.104.78.88.97.104.74.72.99.119.70.50.76")}${__(
        "61.61.119.76"
      )}${appraisalId}`,
      formData,
      {
        headers: { "Content-Type": "application/json" },
      }
    );
    if (!res?.data) throw new TypeError("Failed to update appraisal");
    Swal.fire({
      icon: "success",
      title: "Success",
      text: "Appraisal updated successfully",
    });
    return true;
  } catch (err: any) {
    console.error(`Error in updateAppraisal: ${err.message}`);
    Swal.fire({ icon: "error", title: "Error", text: `${err.message}` });
    return false;
  }
}
