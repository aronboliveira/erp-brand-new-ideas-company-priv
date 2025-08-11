import {
  AttendanceBulkFilter,
  Employee,
  GetAttendanceListParams,
} from "@/definitions/helpers";
import axios from "axios";
import Swal from "sweetalert2";
import __ from "@/lib/configs/obfuscate";
export async function fetchEmployees(
  filter: AttendanceBulkFilter
): Promise<Employee[]> {
  try {
    const res = await axios.get(
      __("61.61.119.99.108.86.87.101.118.120.71.99.116.86.50.76"),
      {
        params: filter,
      }
    );
    if (!res?.data) throw new Error(`Failed to fetch data`);
    return res.data;
  } catch (err: any) {
    Swal.fire({ icon: "error", title: "Error", text: `${err.message}` });
    console.error(`fetchEmployees error: ${err?.message}`);
    return [];
  }
}
export async function getBulkAttendance(): Promise<any> {
  try {
    const res = await axios.get(
      __(
        "61.61.81.90.106.53.87.89.107.53.87.90.48.82.88.89.114.120.87.100.105.57.83.90.108.108.51.98.115.66.88.98.108.86.50.89.117.70.71.90.117.86.71.100.48.70.50.76"
      )
    );
    if (!res?.data)
      throw new TypeError("No data returned from getBulkAttendance");
    Swal.fire({
      icon: "success",
      title: "Success",
      text: "Bulk attendance fetched successfully",
    });
    return res.data;
  } catch (error: any) {
    console.error(`GET error: ${error?.message}`);
    Swal.fire({ icon: "error", title: "Error", text: error?.message });
  }
}
export async function getEmployeeAttendances(): Promise<any> {
  try {
    const res = await axios.get(
      __(
        "61.61.81.90.108.108.51.98.115.66.88.98.108.86.50.89.117.70.71.90.117.86.71.100.48.70.50.76"
      )
    );
    if (!res?.data)
      throw new TypeError("No data returned from getEmployeeAttendances");
    Swal.fire({
      icon: "success",
      title: "Success",
      text: "Attendance employees fetched successfully",
    });
    return res.data;
  } catch (error: any) {
    console.error(`GET error: ${error?.message}`);
    Swal.fire({ icon: "error", title: "Error", text: error?.message });
  }
}
export async function getMonthlyAttendanceReport(): Promise<any> {
  try {
    const res = await axios.get(
      __(
        "108.78.109.98.104.82.109.98.108.82.72.100.104.49.83.101.115.104.71.100.117.57.87.98.116.77.72.100.121.57.71.99.108.74.51.76"
      )
    );
    if (!res?.data)
      throw new TypeError("No data returned from getMonthlyAttendanceReport");
    Swal.fire({
      icon: "success",
      title: "Success",
      text: "Monthly attendance report fetched successfully",
    });
    return res.data;
  } catch (error: any) {
    console.error(`GET error: ${error?.message}`);
    Swal.fire({ icon: "error", title: "Error", text: error?.message });
  }
}
export async function getAttendanceReportByParams(
  month: string,
  branch: string,
  department: string
): Promise<any> {
  try {
    const res = await axios.get(
      `${__(
        "61.61.119.76.108.78.109.98.104.82.109.98.108.82.72.100.104.57.67.100.121.57.71.99.108.74.51.76"
      )}${month}/${branch}/${department}`
    );
    if (!res?.data)
      throw new TypeError("No data returned from getAttendanceReportByParams");
    Swal.fire({
      icon: "success",
      title: "Success",
      text: "Attendance CSV report exported successfully",
    });
    return res.data;
  } catch (error: any) {
    console.error(`GET attendance CSV export error: ${error?.message}`);
    Swal.fire({ icon: "error", title: "Error", text: error?.message });
  }
}
export async function getAttendanceImportFile(): Promise<string> {
  try {
    const res = await axios.get(
      __(
        "61.61.81.90.115.108.109.90.118.81.110.99.118.66.88.98.112.57.83.90.106.53.87.89.107.53.87.90.48.82.88.89"
      )
    );
    if (!res?.data)
      throw new TypeError("No data returned from getAttendanceImportFile");
    Swal.fire({
      icon: "success",
      title: "Success",
      text: "Attendance import file URL fetched successfully",
    });
    return res.data;
  } catch (error: any) {
    console.error(`GET attendance import file error: ${error?.message}`);
    Swal.fire({ icon: "error", title: "Error", text: error?.message });
    return "";
  }
}
export async function getEmployeeAttendanceForEdit(id: string): Promise<any> {
  try {
    const res = await axios.get(
      `${__(
        "61.61.81.90.108.108.51.98.115.66.88.98.108.86.50.89.117.70.71.90.117.86.71.100.48.70.50.76"
      )}/${id}${__("118.81.88.97.107.86.50.76")}`
    );
    if (!res?.data)
      throw new TypeError("No data returned from getEmployeeAttendanceForEdit");
    Swal.fire({
      icon: "success",
      title: "Success",
      text: "Attendance employee details for edit fetched successfully",
    });
    return res.data;
  } catch (error: any) {
    console.error(`GET error: ${error?.message}`);
    Swal.fire({ icon: "error", title: "Error", text: error?.message });
  }
}
export async function getEmployeeAttendanceShow(): Promise<any> {
  try {
    const res = await axios.get(
      __(
        "61.61.119.76.51.57.71.97.122.57.83.90.108.108.51.98.115.66.88.98.108.86.50.89.117.70.71.90.117.86.71.100.48.70.50.76"
      )
    );
    if (!res?.data)
      throw new TypeError("No data returned from getEmployeeAttendanceShow");
    Swal.fire({
      icon: "success",
      title: "Success",
      text: "Attendance employee details fetched successfully",
    });
    return res.data;
  } catch (error: any) {
    console.error(`Error in getEmployeeAttendanceShow: ${error?.message}`);
    Swal.fire({ icon: "error", title: "Error", text: error?.message });
  }
}
export async function getAttendanceList({
  filterType,
  month = "",
  date = "",
  branch = "",
  department = "",
}: GetAttendanceListParams): Promise<any[]> {
  try {
    const params = {
      filterType,
      ...(filterType === "monthly" ? { month } : { date }),
      branch,
      department,
    };
    const res = await axios.get(
      __(
        "61.61.81.90.106.53.87.89.107.53.87.90.48.82.88.89.118.77.72.100.121.57.71.99.116.108.50.76"
      ),
      { params }
    );
    if (!res?.data) throw new TypeError("No data returned");
    Swal.fire({
      icon: "success",
      title: "Success",
      text: "Attendance imports fetched successfully",
    });
    return res.data;
  } catch (error: any) {
    console.error(`Failed to GET attendance list: ${error?.message}`);
    Swal.fire({ icon: "error", title: "Error", text: error?.message });
    return [];
  }
}
