"use client";
import axios from "axios";
import Swal from "sweetalert2";
import _ from "@/lib/configs/obfuscate";
export async function registerUser(
  name: string,
  email: string,
  password: string,
  password_confirmation: string
): Promise<boolean> {
  try {
    const res = await axios.post(
      _("121.86.71.100.122.108.50.90.108.74.51.76"),
      {
        name,
        email,
        password,
        password_confirmation,
      }
    );
    if (!res?.data) throw new TypeError("Failed to register, no response data");
    Swal.fire({
      icon: "success",
      title: "Success",
      text: "Registered successfully",
    });
    return true;
  } catch (e) {
    console.error(`Failed to POST for register: ${(e as Error)?.message}`);
    Swal.fire({
      icon: "error",
      title: "Error register",
      text: (e as Error)?.message || "Undefined error",
    });
    return false;
  }
}
