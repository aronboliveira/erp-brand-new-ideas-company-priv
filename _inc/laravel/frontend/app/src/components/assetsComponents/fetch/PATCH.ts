import axios from "axios";
import Swal from "sweetalert2";
export async function getAssetForEdit(id: string): Promise<any> {
  try {
    const res = await axios.get(`/account-assets/${id}/edit/`);
    if (!res?.data)
      throw new TypeError("No data returned from getAssetForEdit");
    Swal.fire({
      icon: "success",
      title: "Success",
      text: "Asset details for edit fetched successfully",
    });
    return res.data;
  } catch (error: any) {
    console.error(`Error in getAssetForEdit: ${error?.message}`);
    Swal.fire({ icon: "error", title: "Error", text: error?.message });
  }
}
