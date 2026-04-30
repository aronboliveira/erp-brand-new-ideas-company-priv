import axios from "axios";
import Swal from "sweetalert2";
export async function getAssets(): Promise<any> {
  try {
    const res = await axios.get("/account-assets/");
    if (!res?.data) throw new TypeError("No data returned from getAssets");
    Swal.fire({
      icon: "success",
      title: "Success",
      text: "Assets fetched successfully",
    });
    return res.data;
  } catch (error: any) {
    console.error(`GET assets error: ${error?.message}`);
    Swal.fire({ icon: "error", title: "Error", text: error?.message });
  }
}
export async function getAsset(assetId: string): Promise<any> {
  try {
    const res = await axios.get(`/assets/${assetId}/show/`);
    if (!res?.data) throw new TypeError("No data returned from getAsset");
    Swal.fire({
      icon: "success",
      title: "Success",
      text: "Asset details fetched successfully",
    });
    return res.data;
  } catch (error: any) {
    console.error(`GET error: ${error?.message}`);
    Swal.fire({ icon: "error", title: "Error", text: error?.message });
  }
}
