import axios from "axios";
import Swal from "sweetalert2";
import __ from "@/lib/configs/obfuscate";
export async function deleteAnnouncement(id: string): Promise<boolean> {
  try {
    const res = await axios.delete(
      `${__(
        "61.61.65.100.117.86.87.98.108.78.109.98.49.57.109.98.117.70.50.76"
      )}${__("61.61.119.76")}${id}${__(
        "118.107.51.98.121.82.51.99.108.82.50.76"
      )}`
    );
    if (!res?.data) throw new TypeError("Failed to delete data");
    return true;
  } catch (err: any) {
    console.error(`Error in deleteAnnouncement: ${err.message}`);
    Swal.fire({ icon: "error", title: "Error", text: `${err.message}` });
    return false;
  }
}
