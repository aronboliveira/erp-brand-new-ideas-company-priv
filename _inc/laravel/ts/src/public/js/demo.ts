/**
 * @fileoverview TypeScript version of public/js/demo.js
 * @generated from original JavaScript - manual review recommended
 * @module demo
 */

// eslint-disable-next-line @typescript-eslint/no-unused-vars


declare function swal(
  options: Record<string, unknown>,
): Promise<{ value?: unknown }>;

("use strict");

$(document).ready(function (): void {
  try {
    $('[data-toggle="sweet-alert"]').on("click", function (): void {
      // eslint-disable-next-line @typescript-eslint/no-unsafe-argument
      const type = $(this).data("sweet-alert");

      switch (type) {
        case "basic":
          void swal({
            title: "Here's a message!",
            text: "A few words about this sweet alert ...",
            buttonsStyling: false,
            confirmButtonClass: "btn btn-primary",
          });
          break;

        case "info":
          void swal({
            title: "Info",
            text: "A few words about this sweet alert ...",
            type: "info",
            buttonsStyling: false,
            confirmButtonClass: "btn btn-info",
          });
          break;

        // eslint-disable-next-line no-duplicate-case
        case "info":
          void swal({
            title: "Info",
            text: "A few words about this sweet alert ...",
            type: "info",
            buttonsStyling: false,
            confirmButtonClass: "btn btn-info",
          });
          break;

        case "success":
          void swal({
            title: "Success",
            text: "A few words about this sweet alert ...",
            type: "success",
            buttonsStyling: false,
            confirmButtonClass: "btn btn-success",
          });
          break;

        case "warning":
          void swal({
            title: "Warning",
            text: "A few words about this sweet alert ...",
            type: "warning",
            buttonsStyling: false,
            confirmButtonClass: "btn btn-warning",
          });
          break;

        case "question":
          void swal({
            title: "Are you sure?",
            text: "A few words about this sweet alert ...",
            type: "question",
            buttonsStyling: false,
            confirmButtonClass: "btn btn-dark",
          });
          break;

        case "confirm":
          void swal({
            title: "Are you sure?",
            text: "You won't be able to revert this!",
            type: "warning",
            showCancelButton: true,
            buttonsStyling: false,
            confirmButtonClass: "btn btn-danger",
            confirmButtonText: "Yes, delete it!",
            cancelButtonClass: "btn btn-secondary",
          }).then(function (result) {
            if (result.value) {
              // Show confirmation
              void swal({
                title: "Deleted!",
                text: "Your file has been deleted.",
                type: "success",
                buttonsStyling: false,
                confirmButtonClass: "btn btn-primary",
              });
            }
          });
          break;

        case "image":
          void swal({
            title: "Sweet",
            text: "Modal with a custom image ...",
            imageUrl: "../../assets/img/prv/splash.png",
            buttonsStyling: false,
            confirmButtonClass: "btn btn-primary",
            confirmButtonText: "Super!",
          });
          break;

        case "timer":
          void swal({
            title: "Auto close alert!",
            text: "I will close in 2 seconds.",
            timer: 2000,
            showConfirmButton: false,
          });
          break;
      }
    });
  } catch (__moduleErr) {
    console.error("[demo] failed to initialise:", __moduleErr);
  }
});
