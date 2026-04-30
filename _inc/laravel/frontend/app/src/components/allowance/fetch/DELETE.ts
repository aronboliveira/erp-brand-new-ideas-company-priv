import axios from "axios";
import Swal from "sweetalert2";
import __ from "@/lib/configs/obfuscate";
export async function deleteAllowance(id: string): Promise<any> {
  try {
    const res = await axios.delete(
      `${__(
        "61.61.119.76.53.57.109.99.48.78.88.90.107.57.83.90.106.53.87.89.51.57.71.98.115.70.50.76"
      )}${id}/`
    );
    if (!res?.data)
      throw new TypeError("No data returned from deleteAllowance");
    Swal.fire({
      icon: "success",
      title: "Deleted",
      text: "Allowance deleted successfully",
    });
    return res.data;
  } catch (error: any) {
    console.error(`DELETE allowance error: ${error?.message}`);
    Swal.fire({ icon: "error", title: "Error", text: error?.message });
  }
}
