import { EmployeeAttendance } from "@/definitions/helpers";
import axios from "axios";
import Swal from "sweetalert2";
export async function updateEmployeeAttendance(
  data: EmployeeAttendance
): Promise<EmployeeAttendance | null> {
  try {
    const res = await axios.put(`/EmployeeAttendance/${data.id}/update`, data);
    if (!res?.data)
      throw new TypeError("No data returned from updateEmployeeAttendance");
    Swal.fire({
      icon: "success",
      title: "Success",
      text: "Attendance updated successfully",
    });
    return res.data;
  } catch (error: any) {
    console.error(`Failed to PUT: ${error?.message}`);
    Swal.fire({
      icon: "error",
      title: "Error",
      text: `${error?.message}`,
    });
    return null;
  }
}
