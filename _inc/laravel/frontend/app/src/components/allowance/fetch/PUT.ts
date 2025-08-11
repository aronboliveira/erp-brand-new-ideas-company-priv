import { AllowanceBodyMethodProps } from "@/definitions/helpers";
import Swal from "sweetalert2";
import __ from "@/lib/configs/obfuscate";
export async function putAllowance({
  id,
  formData,
  submitDispatch,
  close,
}: AllowanceBodyMethodProps): Promise<boolean> {
  try {
    const res = await fetch(
      `${__(
        "118.85.71.100.104.82.71.99.49.57.83.90.106.53.87.89.51.57.71.98.115.70.50.76"
      )}${id}`,
      {
        method: "PUT",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(formData),
      }
    );
    if (!res.ok) throw new Error(`Update error: ${res.status}`);
    const data = await res.json();
    submitDispatch && submitDispatch(data);
    Swal.fire({
      icon: "success",
      title: "Allowance updated successfully",
    });
    close();
    return true;
  } catch (error: any) {
    Swal.fire({ icon: "error", title: "Error", text: error.message });
    console.error(`Failed to PUT: ${error}`);
    return false;
  }
}
