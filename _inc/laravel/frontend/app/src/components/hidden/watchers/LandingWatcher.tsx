"use client";
import { JSX, useEffect, useState } from "react";
import { QueryClient, QueryClientProvider } from "@tanstack/react-query";
import { ReactQueryDevtools } from "@tanstack/react-query-devtools";
export default function (): JSX.Element {
  const [queryClient] = useState(() => new QueryClient());
  useEffect(() => {
    // @ts-ignore
    import("wowjs").then(WOW => new WOW.WOW({ live: false }).init());
  }, []);
  return (
    <QueryClientProvider client={queryClient}>
      <span
        id='landingPageWatcher'
        className='watcher'
        style={{ display: "none" }}
      ></span>
      ;
      <ReactQueryDevtools initialIsOpen={false} position='top' />
    </QueryClientProvider>
  );
}
