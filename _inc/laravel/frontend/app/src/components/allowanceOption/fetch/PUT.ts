import Swal from "sweetalert2";
import axios from "axios";
import __ from "@/lib/configs/obfuscate";
export async function putAllowanceOption({
  id,
  onClose,
  submissionDispatch,
}: {
  id: string;
  submissionDispatch?: Function;
  onClose: Function;
}): Promise<boolean> {
  try {
    const res = await axios.put(
      `/${__(
        "61.61.119.76.117.57.87.97.48.66.51.98.108.78.109.98.104.100.51.98.115.120.87.89"
      )}${id}${__("61.56.83.90.48.70.71.90.119.86.51.76")}`,
      { name }
    );
    if (!res?.data) throw new Error(`Update error: ${res.status}`);
    submissionDispatch && submissionDispatch(res.data);
    Swal.fire({
      icon: "success",
      title: "Allowance option updated successfully",
    });
    onClose();
    return true;
  } catch (error: any) {
    Swal.fire({ icon: "error", title: "Error", text: error.message });
    console.error(`Failed to PUT: ${error}`);
    return false;
  }
}
