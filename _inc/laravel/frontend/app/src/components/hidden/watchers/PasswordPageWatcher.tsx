// 'min-h-screen bg-gray-100'
"use client";
import { JSX, useEffect } from "react";
export default function PasswordPageWatcher(): JSX.Element {
  useEffect(() => {
    if (!document.body) return;
    ["min-h-screen", "bg-gray-100"].forEach(cls => {
      !document.body.classList.contains(cls) &&
        document.body.classList.add(cls);
    });
  }, []);
  return (
    <span
      id='passwordPageWatcher'
      className='watcher'
      style={{ display: "none" }}
    ></span>
  );
}
