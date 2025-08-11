import axios from "axios";
import Swal from "sweetalert2";
import __ from "@/lib/configs/obfuscate";
export async function patchAllowance(id: string): Promise<any> {
  try {
    const res = await axios.get(
      `${__(
        "61.61.119.76.48.108.71.90.108.57.83.90.106.53.87.89.51.57.71.98.115.70.50.76"
      )}${id}/`
    );
    if (!res?.data)
      throw new TypeError("No data returned from getAllowanceForEdit");
    Swal.fire({
      icon: "success",
      title: "Success",
      text: "Allowance details for edit fetched successfully",
    });
    return res.data;
  } catch (error: any) {
    console.error(`GET allowance for edit error: ${error?.message}`);
    Swal.fire({ icon: "error", title: "Error", text: error?.message });
  }
}
