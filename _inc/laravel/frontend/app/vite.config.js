// ! THIS IS NOT BEING USED, THE PROJECT HAS BEEN MIGRATED TO NEXT.JS

// import { defineConfig } from "vite";
// import laravel from "laravel-vite-plugin";
// import { config } from "dotenv";
// import dotenvExpand from "dotenv-expand";
// import path from "path";
// import { fileURLToPath } from "url";
// const __filename = fileURLToPath(import.meta.url),
//   __dirname = path.dirname(__filename),
//   projectRoot = path.resolve(__dirname, "../"),
//   env = config({
//     path: path.join(projectRoot, ".env") /*, debug: true*/,
//   });
// dotenvExpand.expand(env);
// export default defineConfig({
//   build: {
//     outDir: path.join(projectRoot, "public/build-landingpage"),
//     emptyOutDir: true,
//     manifest: true,
//   },
//   plugins: [
//     laravel({
//       publicDirectory: path.join(projectRoot, "public"),
//       buildDirectory: "build-landingpage",
//       input: [
//         path.join(__dirname, "/Resources/assets/sass/app.scss"),
//         path.join(__dirname, "/Resources/assets/js/app.js"),
//       ],
//       refresh: true,
//     }),
//   ],
// });
