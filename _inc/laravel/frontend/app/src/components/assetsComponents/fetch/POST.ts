import axios from "axios";
import Swal from "sweetalert2";
import _ from "@/lib/configs/obfuscate";
export async function postAsset(data: any): Promise<boolean> {
  try {
    const res = await axios.post(
      _(
        "61.61.119.76.108.74.51.98.48.78.51.76.122.82.88.90.122.78.88.89.116.81.110.98.49.57.50.89.106.70.50.76"
      ),
      data
    );
    if (!res?.data) throw new TypeError("No data returned from postAsset");
    Swal.fire({
      icon: "success",
      title: "Success",
      text: "Asset created successfully",
    });
    return true;
  } catch (error: any) {
    console.error(`Error in POST: ${error?.message}`);
    Swal.fire({ icon: "error", title: "Error", text: error?.message });
    return false;
  }
}
