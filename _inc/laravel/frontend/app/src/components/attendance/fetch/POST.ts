import axios from "axios";
import Swal from "sweetalert2";
import _ from "@/lib/configs/obfuscate";
export async function submitBulkAttendance(data: any): Promise<boolean> {
  try {
    const res = await axios.post(
      _(
        "61.61.81.90.106.53.87.89.107.53.87.90.48.82.88.89.114.120.87.100.105.57.83.90.108.108.51.98.115.66.88.98.108.86.50.89.117.70.71.90.117.86.71.100.48.70.50.76"
      ),
      data
    );
    if (!res?.data) throw new TypeError(`Failed to fetch data`);
    Swal.fire({
      icon: "success",
      title: "Success",
      text: "Attendance created successfully",
    });
    return true;
  } catch (err: any) {
    Swal.fire({ icon: "error", title: "Error", text: `${err.message}` });
    console.error(`Failed to POST: ${err?.message}`);
    return false;
  }
}
export async function createEmployeeAttendance(
  data: any,
  onSuccess?: Function
): Promise<any> {
  try {
    const res = await axios.post(
      _(
        "61.85.71.100.104.86.109.99.106.57.83.90.108.108.51.98.115.66.88.98.108.86.50.89.117.70.71.90.117.86.71.100.48.70.50.76"
      ),
      data
    );
    if (!res?.data) throw new TypeError(`Failed to fetch data`);
    Swal.fire({
      icon: "success",
      title: "Success",
      text: "Attendance Employee created successfully",
    });
    onSuccess && onSuccess();
    return res.data;
  } catch (err: any) {
    Swal.fire({ icon: "error", title: "Error", text: `${err.message}` });
    console.error(`Failed to POST: ${err?.message}`);
  }
}
export async function storeEmployeeAttendance(
  data: any,
  onSuccess?: Function
): Promise<any> {
  try {
    const res = await axios.post(
      _(
        "61.56.83.90.121.57.71.100.122.57.83.90.108.108.51.98.115.66.88.98.108.86.50.89.117.70.71.90.117.86.71.100.48.70.50.76"
      ),
      data
    );
    if (!res?.data)
      throw new TypeError("No data returned from createEmployeeAttendance");
    Swal.fire({
      icon: "success",
      title: "Success",
      text: "Attendance employee created successfully",
    });
    onSuccess && onSuccess();
    return res.data;
  } catch (err: any) {
    console.error(`Failed to create attendance employee: ${err?.message}`);
    Swal.fire({ icon: "error", title: "Error", text: err.message });
  }
}
export async function importAttendanceFile(
  formData: FormData,
  onClose?: Function
): Promise<any> {
  try {
    const res = await axios.post("/attendance/import", formData, {
      headers: { "Content-Type": "multipart/form-data" },
    });
    if (!res?.data)
      throw new TypeError("No data returned from importAttendanceFile");
    Swal.fire({
      icon: "success",
      title: "Uploaded",
      text: "Attendance file uploaded successfully",
    });
    onClose && onClose();
    return res.data;
  } catch (error: any) {
    console.error(`importAttendanceFile error: ${error?.message}`);
    Swal.fire({
      icon: "error",
      title: "Error",
      text: `${error?.message}`,
    });
  }
}
export async function postBulkAttendanceData(data: any): Promise<any> {
  try {
    const res = await axios.post("/EmployeeAttendance/bulk/store", data);
    if (!res?.data)
      throw new TypeError("No data returned from postBulkAttendanceData");
    Swal.fire({
      icon: "success",
      title: "Success",
      text: "Bulk attendance data posted successfully",
    });
    return res.data;
  } catch (error: any) {
    console.error(`POST error: ${error?.message}`);
    Swal.fire({ icon: "error", title: "Error", text: error?.message });
  }
}
export async function postAttendance(data: any): Promise<any> {
  try {
    const res = await axios.post("/EmployeeAttendance/attendance", data);
    if (!res?.data) throw new TypeError("No data returned from postAttendance");
    Swal.fire({
      icon: "success",
      title: "Success",
      text: "Attendance submitted successfully",
    });
    return res.data;
  } catch (error: any) {
    console.error(`POST attendance error: ${error?.message}`);
    Swal.fire({ icon: "error", title: "Error", text: error?.message });
  }
}
export async function postAttendanceImport(formData: FormData): Promise<any> {
  try {
    const res = await axios.post("/import/attendance", formData, {
      headers: { "Content-Type": "multipart/form-data" },
    });
    if (!res?.data)
      throw new TypeError("No data returned from postAttendanceImport");
    Swal.fire({
      icon: "success",
      title: "Success",
      text: "Attendance imported successfully",
    });
    return res.data;
  } catch (error: any) {
    console.error(`POST attendance import error: ${error?.message}`);
    Swal.fire({ icon: "error", title: "Error", text: error?.message });
  }
}
export async function getReportDepartment(data: any): Promise<any> {
  try {
    const res = await axios.post(
      "/reports-monthly-attendance/getdepartment",
      data
    );
    if (!res?.data)
      throw new TypeError("No data returned from getReportDepartment");
    Swal.fire({
      icon: "success",
      title: "Success",
      text: "Departments fetched successfully",
    });
    return res.data;
  } catch (error: any) {
    console.error(`POST error: ${error?.message}`);
    Swal.fire({ icon: "error", title: "Error", text: error?.message });
  }
}
export async function getReportEmployee(data: any): Promise<any> {
  try {
    const res = await axios.post(
      "/reports-monthly-attendance/getemployee",
      data
    );
    if (!res?.data)
      throw new TypeError("No data returned from getReportEmployee");
    Swal.fire({
      icon: "success",
      title: "Success",
      text: "Employees fetched successfully",
    });
    return res.data;
  } catch (error: any) {
    console.error(`POST getReportEmployee error: ${error?.message}`);
    Swal.fire({ icon: "error", title: "Error", text: error?.message });
  }
}
export async function postAttendanceClock(data: any): Promise<any> {
  try {
    const res = await axios.post("/EmployeeAttendance/clock/", data);
    if (!res?.data)
      throw new TypeError("No data returned from postAttendanceClock");
    Swal.fire({
      icon: "success",
      title: "Success",
      text: "Attendance clock updated successfully",
    });
    return res.data;
  } catch (error: any) {
    console.error(`Failed to update attendance clock: ${error?.message}`);
    Swal.fire({ icon: "error", title: "Error", text: error?.message });
  }
}
