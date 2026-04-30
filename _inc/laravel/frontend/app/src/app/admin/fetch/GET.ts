import axios from "axios";
import Swal from "sweetalert2";
export async function getDashboardFiltered(keyword: string): Promise<any> {
  try {
    const res = await axios.get(`/dashboard/${encodeURIComponent(keyword)}`);
    if (!res?.data) throw new Error(`Fetch error: ${res.status}`);
    return res.data.html;
  } catch (error) {
    Swal.fire({
      icon: "error",
      title: "Error",
      text: (error as Error).message,
    });
    console.error(`Failed to GET: ${error}`);
  }
}
