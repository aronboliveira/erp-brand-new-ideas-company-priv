// next.config.mjs
const nextConfig = {
  trailingSlash: true,
  reactStrictMode: true,
  headers: async () => [],
  redirects: async () => [],
  compress: true,
  productionBrowserSourceMaps: false,
  images: {
    domains: [
      process.env.NEXT_PUBLIC_APP_URL_SHORT || "",
      process.env.NEXT_API_URL || "",
    ],
    remotePatterns: [],
  },
};

export default nextConfig;
