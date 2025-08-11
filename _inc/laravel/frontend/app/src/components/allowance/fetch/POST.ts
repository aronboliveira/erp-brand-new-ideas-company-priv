import axios from "axios";
import Swal from "sweetalert2";
import __ from "@/lib/configs/obfuscate";
import { AllowanceBodyMethodProps } from "@/definitions/helpers";
export async function postAllowance({
  id,
  formData,
  submitDispatch,
  close,
}: AllowanceBodyMethodProps): Promise<boolean> {
  try {
    const res = await axios.post(
      `${__(
        "61.56.83.90.48.70.87.90.121.78.50.76.108.78.109.98.104.100.51.98.115.120.87.89"
      )}${id}`,
      {
        ...formData,
        employee_id: id,
      }
    );
    if (!res?.status.toString().startsWith("2"))
      throw new Error(`Submit error: ${res.status}`);
    submitDispatch && submitDispatch(await res.data);
    Swal.fire({
      icon: "success",
      title: "Allowance created successfully",
    });
    close();
    return true;
  } catch (error: any) {
    Swal.fire({
      icon: "error",
      title: "Error",
      text: error.message,
    });
    console.error(`Failed to POST: ${error}`);
    return false;
  }
}
