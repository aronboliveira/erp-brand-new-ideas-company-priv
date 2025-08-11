import axios from "axios";
import Swal from "sweetalert2";
import __ from "@/lib/configs/obfuscate";
export async function deleteAsset(id: string): Promise<boolean> {
  try {
    const res = await axios.delete(
      `${__(
        "61.61.119.76.122.82.88.90.122.78.88.89.116.81.110.98.49.57.50.89.106.70.50.76"
      )}${id}/${__("61.61.81.90.48.86.71.98.108.82.50.76")}`
    );
    if (!res?.data) throw new TypeError("No data returned from deleteAsset");
    Swal.fire({
      icon: "success",
      title: "Deleted",
      text: "Asset deleted successfully",
    });
    return true;
  } catch (error: any) {
    console.error(`Error in deleteAsset: ${error?.message}`);
    Swal.fire({ icon: "error", title: "Error", text: error?.message });
    return false;
  }
}
