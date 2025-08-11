import Swal from "sweetalert2";
import axios from "axios";
import { Dispatch, SetStateAction } from "react";
export async function deleteToDo({
  id,
  url,
  dispatch,
}: {
  id: string;
  url: string;
  dispatch: Dispatch<SetStateAction<any[]>>;
}): Promise<boolean> {
  try {
    const res = await axios.delete(url);
    if (!res?.data) throw new Error(`Todo delete error: ${res.status}`);
    Swal.fire({ icon: "success", title: "Todo Deleted Successfully!" });
    dispatch(prev => prev.filter(t => t.id !== id));
    return true;
  } catch (error: any) {
    Swal.fire({ icon: "error", title: "Error", text: error.message });
    console.error(`Failed to DELETE: ${error}`);
    return false;
  }
}
