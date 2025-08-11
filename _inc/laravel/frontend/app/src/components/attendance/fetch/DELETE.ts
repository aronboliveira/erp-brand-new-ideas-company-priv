import axios from "axios";
import Swal from "sweetalert2";

export async function deleteEmployeeAttendance(id: string): Promise<any> {
  try {
    const res = await axios.delete(`/EmployeeAttendance/${id}/delete`);
    if (!res?.data)
      throw new TypeError("No data returned from deleteEmployeeAttendance");
    Swal.fire({
      icon: "success",
      title: "Success",
      text: "Attendance employee deleted successfully",
    });
    return res.data;
  } catch (error: any) {
    console.error(`DELETE error: ${error?.message}`);
    Swal.fire({ icon: "error", title: "Error", text: error?.message });
  }
}
