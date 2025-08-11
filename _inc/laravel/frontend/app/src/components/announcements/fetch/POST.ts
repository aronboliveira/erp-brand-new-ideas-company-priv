import { AnnouncementFormData, Option } from "@/definitions/helpers";
import axios from "axios";
import { Dispatch, SetStateAction } from "react";
import Swal from "sweetalert2";
import __ from "@/lib/configs/obfuscate";
export async function postBranches(
  dispatch: Dispatch<SetStateAction<any>>
): Promise<boolean> {
  try {
    const res = await axios.get(__("122.86.71.97.106.53.87.89.121.74.50.76"));
    if (!res?.data) throw new TypeError("Failed to fetch data");
    dispatch(res.data);
    Swal.fire({
      icon: "success",
      title: "Success",
      text: "Branches fetched successfully",
    });
    return true;
  } catch (err: any) {
    console.error(`POST branches error: ${err.message}`);
    Swal.fire({
      icon: "error",
      title: "Error",
      text: `Fetching branches error: ${err.message}`,
    });
    return false;
  }
}
export async function postDepartments(
  id: string,
  dispatch: Dispatch<SetStateAction<any>>
): Promise<boolean> {
  try {
    const res = await axios.post(
      __("122.82.110.98.108.49.71.100.121.70.71.99.108.82.50.76"),
      { branch_id: id }
    );
    if (!res?.data) throw new TypeError("Failed to fetch data");
    dispatch(res.data);
    Swal.fire({
      icon: "success",
      title: "Success",
      text: "Departments fetched successfully",
    });
    return true;
  } catch (err: any) {
    console.error(`POST departments error: ${err.message}`);
    Swal.fire({
      icon: "error",
      title: "Error",
      text: `Fetching departments error: ${err.message}`,
    });
    return false;
  }
}
export async function postEmployees(
  id: string,
  dispatch: Dispatch<SetStateAction<any>>
): Promise<boolean> {
  try {
    const res = await axios.post(
      __("61.61.119.99.108.86.87.101.118.120.71.99.116.86.50.76"),
      { department_id: id }
    );
    if (!res?.data) throw new TypeError("Failed to fetch data");
    dispatch(res.data);
    Swal.fire({
      icon: "success",
      title: "Success",
      text: "Employees fetched successfully",
    });
    return true;
  } catch (err: any) {
    console.error(`POST employees error: ${err.message}`);
    Swal.fire({
      icon: "error",
      title: "Error",
      text: `Fetching employees error: ${err.message}`,
    });
    return false;
  }
}
export async function postAnnouncement(
  form: AnnouncementFormData
): Promise<boolean> {
  try {
    const res = await axios.post(
      __("61.61.65.100.117.86.87.98.108.78.109.98.49.57.109.98.117.70.50.76"),
      form
    );
    if (!res?.data) throw new TypeError("Failed to fetch data");
    Swal.fire({
      icon: "success",
      title: "Success",
      text: "Announcement posted successfully",
    });
    return true;
  } catch (err: any) {
    console.error(`POST announcement error: ${err.message}`);
    Swal.fire({
      icon: "error",
      title: "Error",
      text: `Posting announcement error: ${err.message}`,
    });
    return false;
  }
}
export async function fetchDepartment(bid: string): Promise<Option[]> {
  try {
    const res = await axios.post(
      __(
        "48.53.87.90.116.82.110.99.104.66.88.90.107.82.88.90.110.57.67.100.117.86.87.98.108.78.109.98.49.57.109.98.117.70.50.76"
      ),
      {
        branch_id: bid,
      }
    );
    if (!res?.data) throw new TypeError("Failed to fetch data");
    return Object.entries(res.data).map(([k, v]) => ({
      value: k,
      label: v as string,
    }));
  } catch (err: any) {
    console.error(`Error in fetchDepartment: ${err.message}`);
    Swal.fire({ icon: "error", title: "Error", text: `${err.message}` });
    return [];
  }
}
export async function fetchEmployee(did: string): Promise<Option[]> {
  try {
    const res = await axios.post(
      __(
        "61.61.81.90.108.108.51.98.115.66.88.98.108.82.88.90.110.57.67.100.117.86.87.98.108.78.109.98.49.57.109.98.117.70.50.76"
      ),
      {
        department_id: did,
      }
    );
    if (!res?.data) throw new TypeError("Failed to fetch data");
    return Object.entries(res.data).map(([k, v]) => ({
      value: k,
      label: v as string,
    }));
  } catch (err: any) {
    console.error(`Error in fetchEmployee: ${err.message}`);
    Swal.fire({ icon: "error", title: "Error", text: `${err.message}` });
    return [];
  }
}
