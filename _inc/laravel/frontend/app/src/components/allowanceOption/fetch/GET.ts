import axios from "axios";
import Swal from "sweetalert2";
import __ from "@/lib/configs/obfuscate";
export const getAllowanceOption = async (id: string) => {
  try {
    const res = await axios.get(
      `/${__(
        "61.61.119.76.117.57.87.97.48.66.51.98.108.78.109.98.104.100.51.98.115.120.87.89"
      )}${id}${__("118.99.51.98.111.78.51.76")}`
    );
    if (!res?.data) throw ReferenceError(`Failed to GET allowance option`);
    return res.data;
  } catch (err) {
    console.error("Failed to fetch allowance option:", err);
    Swal.fire({
      icon: "error",
      title: "Error",
      text: (err as Error).message,
    });
  }
};
export const getAllowanceOptions = async () => {
  try {
    const res = await axios.get(
      `${__(
        "61.61.103.98.118.108.71.100.119.57.87.90.106.53.87.89.51.57.71.98.115.70.50.76"
      )}`
    );
    if (!res?.data) throw ReferenceError(`Failed to GET allowance options`);
    return res.data;
  } catch (error) {
    Swal.fire({
      icon: "error",
      title: "Error",
      text: (error as Error).message,
    });
    console.error(`Failed to GET: ${error}`);
  }
};
