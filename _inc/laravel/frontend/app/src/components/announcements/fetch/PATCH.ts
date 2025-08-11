import axios from "axios";
import Swal from "sweetalert2";

export async function getAnnouncementForEdit(id: string): Promise<any> {
  try {
    const res = await axios.get(`/announcement/${id}/edit/`);
    if (!res?.data)
      throw new TypeError("No data returned from getAnnouncementForEdit");
    Swal.fire({
      icon: "success",
      title: "Success",
      text: "Announcement details for edit fetched successfully",
    });
    return res.data;
  } catch (error: any) {
    console.error(`PATCH announcement for edit error: ${error?.message}`);
    Swal.fire({ icon: "error", title: "Error", text: error?.message });
  }
}
