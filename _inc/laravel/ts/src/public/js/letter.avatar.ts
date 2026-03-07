/**
 * @fileoverview TypeScript version of public/js/letter.avatar.js
 * @generated from original JavaScript - manual review recommended
 * @module letter.avatar
 */

interface LetterAvatarFunction {
  (name: string, size?: number | string): string;
  transform: () => void;
}

interface DefineFunction {
  (dependencies: string[], factory: (...args: unknown[]) => unknown): void;
  (factory: () => unknown): void;
  amd?: boolean;
}

/*
 * LetterAvatar
 *
 * Artur Heinze
 * Create Letter avatar based on Initials
 * based on https://gist.github.com/leecrossley/6027780
 */
(function (w: Window, d: Document): void {
  function LetterAvatar(name: string, size?: number | string): string {
    name = name ?? "";
    let computedSize: number =
      typeof size === "number" ? size : parseInt(String(size), 10) || 60;

    const colours: string[] = [
      "#1abc9c",
      "#2ecc71",
      "#3498db",
      "#9b59b6",
      "#34495e",
      "#16a085",
      "#27ae60",
      "#2980b9",
      "#8e44ad",
      "#2c3e50",
      "#f1c40f",
      "#e67e22",
      "#e74c3c",
      "#ecf0f1",
      "#95a5a6",
      "#f39c12",
      "#d35400",
      "#c0392b",
      "#bdc3c7",
      "#7f8c8d",
    ];

    const nameSplit: string[] = String(name).toUpperCase().split(" ");
    let initials: string;
    let charIndex: number;
    let colourIndex: number;
    let canvas: HTMLCanvasElement | null;
    let context: CanvasRenderingContext2D | null;
    let dataURI: string;

    if (nameSplit.length === 1) {
      initials = nameSplit[0] ? nameSplit[0].charAt(0) : "?";
    } else {
      initials = nameSplit[0].charAt(0) + nameSplit[1].charAt(0);
    }

    if (w.devicePixelRatio !== 0) {
      computedSize = computedSize * w.devicePixelRatio;
    }

    charIndex = (initials === "?" ? 72 : initials.charCodeAt(0)) - 64;
    colourIndex = charIndex % 20;
    canvas = d.createElement("canvas");
    canvas.width = computedSize;
    canvas.height = computedSize;
    context = canvas.getContext("2d");

    if (context) {
      context.fillStyle = colours[colourIndex - 1];
      context.fillRect(0, 0, canvas.width, canvas.height);
      context.font = Math.round(canvas.width / 2) + "px Arial";
      context.textAlign = "center";
      context.fillStyle = "#FFF";
      context.fillText(initials, computedSize / 2, computedSize / 1.5);
    }

    dataURI = canvas.toDataURL();
    canvas = null;

    return dataURI;
  }

  LetterAvatar.transform = function (): void {
    Array.prototype.forEach.call(
      d.querySelectorAll("img[avatar]"),
      function (img: HTMLImageElement) {
        const avatarName: string = img.getAttribute("avatar") ?? "";
        img.src = LetterAvatar(
          avatarName,
          img.getAttribute("width") ?? undefined,
        );
        img.removeAttribute("avatar");
        img.setAttribute("alt", avatarName);
      },
    );
  };

  const LetterAvatarExport = LetterAvatar as LetterAvatarFunction;

  // Type-safe reference to global define (AMD)
  const globalDefine =
    typeof window !== "undefined"
      ? (window as Window & { define?: DefineFunction }).define
      : undefined;
  // Type-safe reference to CommonJS module/exports
  const globalModule =
    typeof globalThis !== "undefined"
      ? (globalThis as typeof globalThis & { module?: { exports?: unknown } })
          .module
      : undefined;
  const globalExports =
    typeof globalThis !== "undefined"
      ? (
          globalThis as typeof globalThis & {
            exports?: { LetterAvatar?: LetterAvatarFunction };
          }
        ).exports
      : undefined;

  // AMD support
  if (typeof globalDefine === "function" && globalDefine.amd) {
    globalDefine(function (): LetterAvatarFunction {
      return LetterAvatarExport;
    });

    // CommonJS and Node.js module support.
  } else if (typeof globalExports !== "undefined") {
    // Support Node.js specific `module.exports` (which can be a function)
    if (typeof globalModule !== "undefined" && globalModule.exports) {
      globalModule.exports = LetterAvatarExport;
    }

    // But always support CommonJS module 1.1.1 spec (`exports` cannot be a function)
    if (globalExports) {
      globalExports.LetterAvatar = LetterAvatarExport;
    }
  } else {
    (window as Window & { LetterAvatar?: LetterAvatarFunction }).LetterAvatar =
      LetterAvatarExport;

    d.addEventListener("DOMContentLoaded", function (_event: Event): void {
      LetterAvatar.transform();
    });
  }
})(window, document);
