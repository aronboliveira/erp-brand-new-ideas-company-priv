import Swal from "sweetalert2";
import axios from "axios";
import { IdentifiedHttpMethod } from "@/definitions/helpers";
export async function putToDo({
  id,
  url,
  dispatch,
}: IdentifiedHttpMethod<any[]>): Promise<boolean> {
  try {
    const res = await axios.put(url);
    if (!res?.data) throw new Error(`Todo update error: ${res.status}`);
    Swal.fire({ icon: "success", title: "Todo Updated Successfully!" });
    dispatch(prev =>
      prev.map(t => (t.id === id ? { ...t, is_complete: !t.is_complete } : t))
    );
    return true;
  } catch (error: any) {
    Swal.fire({ icon: "error", title: "Error", text: error.message });
    console.log(`Failed to PUT: ${error}`);
    return false;
  }
}
