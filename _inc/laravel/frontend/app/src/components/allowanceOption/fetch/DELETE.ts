import axios from "axios";
import Swal from "sweetalert2";
import __ from "@/lib/configs/obfuscate";
export async function deleteAllowanceOption({
  id,
  queryClient,
}: {
  id: string;
  queryClient: any;
}): Promise<boolean> {
  try {
    const res = await axios.delete(
      `${__(
        "61.61.119.76.117.57.87.97.48.66.51.98.108.78.109.98.104.100.51.98.115.120.87.89"
      )}${id}${__("61.61.81.90.48.86.71.98.108.82.50.76")}`
    );
    if (!res?.data) throw new Error(`Delete error: ${res.status}`);
    await Swal.fire({
      icon: "success",
      title: "Deleted",
      text: "Allowance option deleted successfully",
    });
    queryClient.invalidateQueries("allowanceOptions");
    return true;
  } catch (err: any) {
    Swal.fire({ icon: "error", title: "Error", text: err.message });
    console.error(`Failed to DELETE: ${err}`);
    return false;
  }
}
