import axios from "axios";
import { UIEvent, Dispatch, SetStateAction } from "react";
import Swal from "sweetalert2";
export async function postToDo(
  title: string,
  e: Event | UIEvent,
  dispatch: Dispatch<SetStateAction<any[]>>
): Promise<boolean> {
  try {
    const res = await axios.post("todo/create", { title });
    if (!res?.data) throw new Error(`Todo submit error: ${res.status}`);
    Swal.fire({ icon: "success", title: "Todo Added Successfully!" });
    dispatch(prev => [...prev, res.data]);
    if (e?.currentTarget && "value" in e.currentTarget)
      e.currentTarget.value = "";
    return true;
  } catch (error: any) {
    Swal.fire({ icon: "error", title: "Error", text: error.message });
    console.error(`Failed to POST: ${error}`);
    return false;
  }
}
