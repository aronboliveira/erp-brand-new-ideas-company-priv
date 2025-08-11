import Swal from "sweetalert2";
import axios from "axios";
import o from "@/lib/configs/obfuscate";
export async function putAnnouncement({
  id,
  formData,
  router,
}: {
  id: string;
  formData: object;
  router: any;
}): Promise<boolean> {
  try {
    const res = await axios.put(
      `${o(
        "61.61.65.100.117.86.87.98.108.78.109.98.49.57.109.98.117.70.50.76"
      )}${o("61.61.119.76")}${id}${o("61.56.83.90.48.70.71.90.119.86.51.76")}`,
      formData,
      {
        headers: { "Content-Type": "application/json" },
      }
    );
    if (!res?.data)
      throw new TypeError(
        `No data received when updating announcement id ${id}`
      );
    await Swal.fire({
      icon: "success",
      title: "Deleted",
      text: "Announcement updated successfully",
    });
    router.push("/announcements");
    return true;
  } catch (error: any) {
    Swal.fire({ icon: "error", title: "Error", text: error.message });
    console.error(`Failed to PUT: ${error.message}`);
    return false;
  }
}
