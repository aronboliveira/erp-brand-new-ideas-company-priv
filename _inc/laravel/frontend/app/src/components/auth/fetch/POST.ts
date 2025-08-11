import axios from "axios";
import Swal from "sweetalert2";
export async function confirmPasswordAPI(password: string): Promise<boolean> {
  try {
    const res = await axios.post("/confirm-password", { password });
    if (!res?.data)
      throw new TypeError("No data returned from confirmPasswordAPI");
    Swal.fire({
      icon: "success",
      title: "Success",
      text: "Password confirmed successfully",
    });
    return res.data;
  } catch (error: any) {
    console.error(`confirmPasswordAPI error: ${error?.message}`);
    Swal.fire({
      icon: "error",
      title: "Error",
      text: error?.message,
    });
    return false;
  }
}
export async function postForgotPassword(email: string): Promise<boolean> {
  try {
    const res = await axios.post("/forgot-password", { email });
    if (!res?.data)
      throw new TypeError("No data returned from confirmPasswordAPI");
    Swal.fire({
      icon: "success",
      title: "Success",
      text: "Password request processed successfully",
    });
    return true;
  } catch (error: any) {
    console.error(`Failed to POST for forgot password: ${error?.message}`);
    Swal.fire({
      icon: "error",
      title: "Error",
      text: error?.message || "Error",
    });
    return false;
  }
}
export async function postResetPassword(
  token: string,
  email: string,
  password: string,
  password_confirmation: string
): Promise<boolean> {
  try {
    const res = await axios.post(`/reset-password/${token}`, {
      token,
      email,
      password,
      password_confirmation,
    });
    if (!res?.data)
      throw new TypeError("No data returned from confirmPasswordAPI");
    Swal.fire({
      icon: "success",
      title: "Success",
      text: "Password request processed successfully",
    });
    return true;
  } catch (error: any) {
    console.error(`Failed to POST for reset password: ${error?.message}`);
    Swal.fire({
      icon: "error",
      title: "Error",
      text: error?.message || "Error",
    });
    return false;
  }
}
export async function postPasswordResetEmail(
  email: string,
  recaptchaToken?: string
): Promise<boolean> {
  try {
    const res = await axios.post("/reset-password/", {
      email,
      recaptcha: recaptchaToken,
    });
    if (!res?.data)
      throw new TypeError("No data returned from confirmPasswordAPI");
    Swal.fire({
      icon: "success",
      title: "Success",
      text: "Password request processed successfully",
    });
    return true;
  } catch (error: any) {
    console.error(`Failed to POST for password reset email: ${error?.message}`);
    return false;
  }
}
export async function postLogin(
  email: string,
  password: string,
  recaptcha?: string
): Promise<boolean> {
  try {
    const res = await axios.post("/login", {
      email,
      password,
      recaptcha,
    });
    if (!res?.data) throw new TypeError("Login failed");
    Swal.fire({
      icon: "success",
      title: "Success",
      text: "Login successful",
    });
    return res.data;
  } catch (e) {
    console.error(`Failed to POST for login: ${(e as Error)?.message}`);
    Swal.fire({
      icon: "error",
      title: "Error Login",
      text: (e as Error)?.message || "Undefined error",
    });
    return false;
  }
}
export async function resendVerificationEmail(): Promise<boolean> {
  try {
    const res = await axios.post("/verification/send");
    if (!res?.data)
      throw new TypeError(
        "Failed to send verification email, no response data"
      );
    Swal.fire({
      icon: "success",
      title: "Success",
      text: "Verification email sent",
    });
    return true;
  } catch (e) {
    console.error(
      `Failed to POST for verification resend: ${(e as Error)?.message}`
    );
    Swal.fire({
      icon: "error",
      title: "Error verification resend",
      text: (e as Error)?.message || "Undefined error",
    });
    return false;
  }
}
export async function logoutUser(): Promise<boolean> {
  try {
    const res = await axios.post("/logout");
    if (!res?.data) throw new TypeError("Failed to logout, no response data");
    Swal.fire({
      icon: "success",
      title: "Success",
      text: "Logged out successfully",
    });
    window.location.href = "/auth/login";
    return true;
  } catch (e) {
    console.error(`Failed to POST for logout: ${(e as Error)?.message}`);
    Swal.fire({
      icon: "error",
      title: "Error logout",
      text: (e as Error)?.message || "Undefined error",
    });
    return false;
  }
}
