import { Dispatch, SetStateAction } from "react";
import Swal from "sweetalert2";
import axios from "axios";
import _ from "@/lib/configs/obfuscate";
export async function postAllowanceOption({
  bodyData,
  successDispatch,
  onClose,
}: {
  bodyData: any;
  successDispatch?: Dispatch<SetStateAction<any>>;
  onClose: Function;
}): Promise<boolean> {
  try {
    const res = await axios.post(
      _(
        "118.85.71.100.104.86.109.99.106.57.105.98.118.108.71.100.119.57.87.90.106.53.87.89.51.57.71.98.115.70.50.76"
      ),
      bodyData
    );
    if (!res?.data) throw new Error(`Submit error: ${res.status}`);
    successDispatch && successDispatch(res.data);
    Swal.fire({
      icon: "success",
      title: "Allowance option created successfully",
    });
    onClose();
    return true;
  } catch (error: any) {
    Swal.fire({ icon: "error", title: "Error", text: error.message });
    console.error(`Failed to POST: ${error}`);
    return false;
  }
}
