import axios from "axios";
import Swal from "sweetalert2";
export async function getAnnouncements(): Promise<any> {
  try {
    const res = await axios.get("/announcement/");
    if (!res?.data)
      throw new TypeError("No data returned from getAnnouncements");
    Swal.fire({
      icon: "success",
      title: "Success",
      text: "Announcements fetched successfully",
    });
    return res.data;
  } catch (error: any) {
    console.error(`GET announcements error: ${error?.message}`);
    Swal.fire({ icon: "error", title: "Error", text: error?.message });
  }
}
export async function getAnnouncement(id: string): Promise<any> {
  try {
    const res = await axios.get(`/announcement/${id}/show/`);
    if (!res?.data)
      throw new TypeError("No data returned from getAnnouncement");
    Swal.fire({
      icon: "success",
      title: "Success",
      text: "Announcement details fetched successfully",
    });
    return res.data;
  } catch (error: any) {
    console.error(`GET announcement error: ${error?.message}`);
    Swal.fire({ icon: "error", title: "Error", text: error?.message });
  }
}
