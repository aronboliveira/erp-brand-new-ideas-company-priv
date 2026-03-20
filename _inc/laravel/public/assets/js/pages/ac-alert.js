/**
 * @file ac-alert.js
 * @description SweetAlert2 demonstration page controller
 * @version 2.0.0
 */

(() => {
  "use strict";

  /**
   * SweetAlert2 demo page manager
   * @class SwalDemoController
   */
  class SwalDemoController {
    /** @type {string} */
    static #DATA_INIT = "data-swal-init";
    /** @type {string} */
    static #DATA_LISTENER = "data-swal-listener";
    /** @type {string} */
    static #BTN_DANGER = "btn btn-danger";
    /** @type {string} */
    static #BTN_SUCCESS = "btn btn-success";
    /** @type {string} */
    // eslint-disable-next-line no-unused-private-class-members
    static #BTN_PRIMARY = "btn btn-primary";

    /**
     * Initialize SweetAlert2 demos
     */
    init() {
      if (document.body?.hasAttribute(SwalDemoController.#DATA_INIT)) return;
      if (typeof Swal === "undefined") return console.warn("[SwalDemoController] Swal not loaded");

      document.body?.setAttribute(SwalDemoController.#DATA_INIT, "true");

      this.#setupBasicDemos();
      this.#setupPositionDemos();
      this.#setupAnimationDemos();
      this.#setupConfirmDemos();
      this.#setupInputDemos();
      this.#setupAdvancedDemos();
    }

    /**
     * Bind click handler to element with guard
     * @param {string} selector
     * @param {Function} handler
     * @private
     */
    #bind(selector, handler) {
      const el = document.querySelector(selector);
      if (!el || el.hasAttribute(SwalDemoController.#DATA_LISTENER)) return;
      el.setAttribute(SwalDemoController.#DATA_LISTENER, "true");
      el.addEventListener("click", handler);
    }

    /**
     * Setup basic message demos
     * @private
     */
    #setupBasicDemos() {
      this.#bind(".bs-message", () => Swal.fire("Any fool can use a computer"));

      this.#bind(".bs-tit-txt", () => Swal.fire("The Internet?", "That thing is still around?", "question"));

      this.#bind(".bs-fot-msg", () => Swal.fire({ icon: "error", title: "Oops...", text: "Something went wrong!", footer: "<a href>Why do I have this issue?</a>" }));

      this.#bind(".bs-lng-cnt", () => Swal.fire({ imageUrl: "https://placeholder.pics/svg/350", imageHeight: 1512, imageAlt: "A tall image" }));
    }

    /**
     * Setup position demos
     * @private
     */
    #setupPositionDemos() {
      const positions = [
        [".bs-pos-t", "top"],
        [".bs-pos-te", "top-end"],
        [".bs-pos-ts", "top-start"],
        [".bs-pos-c", "center"],
        [".bs-pos-ce", "center-end"],
        [".bs-pos-cs", "center-start"],
        [".bs-pos-b", "bottom"],
        [".bs-pos-be", "bottom-end"],
        [".bs-pos-bs", "bottom-start"],
      ];

      positions.forEach(([selector, position]) => {
        this.#bind(selector, () => Swal.fire({ position, icon: "success", title: "Your work has been saved", showConfirmButton: false, timer: 1500 }));
      });
    }

    /**
     * Setup animation demos
     * @private
     */
    #setupAnimationDemos() {
      const animations = [
        [".bs-anim-bb", "animate__animated animate__bounceInUp", "animate__animated animate__bounceOutDown"],
        [".bs-anim-fb", "animate__animated animate__fadeInDown", "animate__animated animate__fadeOutUp"],
        [".bs-anim-fd", "animate__animated animate__fadeInDown", "animate__animated animate__fadeOutDown"],
        [".bs-anim-rl", "animate__animated animate__rotateInDownLeft", "animate__animated animate__rotateOutUpRight"],
        [".bs-anim-ft", "animate__animated animate__fadeIn", "animate__animated animate__fadeOut"],
        [".bs-anim-zu", "animate__animated animate__zoomIn", "animate__animated animate__zoomOut"],
        [".bs-anim-slr", "animate__animated animate__slideInLeft", "animate__animated animate__slideOutRight"],
        [".bs-anim-srl", "animate__animated animate__slideInRight", "animate__animated animate__slideOutLeft"],
      ];

      animations.forEach(([selector, showClass, hideClass]) => {
        this.#bind(selector, () =>
          Swal.fire({
            title: "Custom animation with Animate.css",
            showClass: { popup: showClass },
            hideClass: { popup: hideClass },
          }),
        );
      });
    }

    /**
     * Setup confirmation dialog demos
     * @private
     */
    #setupConfirmDemos() {
      this.#bind(".bs-cnf-c", () =>
        Swal.fire({
          title: "Are you sure?",
          text: "You won't be able to revert this!",
          icon: "warning",
          showCancelButton: true,
          confirmButtonColor: "#3085d6",
          cancelButtonColor: "#d33",
          confirmButtonText: "Yes, delete it!",
        }).then(result => {
          if (result.isConfirmed) Swal.fire("Deleted!", "Your file has been deleted.", "success");
        }),
      );

      this.#bind(".bs-pas-p", () => {
        const swalWithBootstrapButtons = Swal.mixin({
          customClass: { confirmButton: SwalDemoController.#BTN_SUCCESS, cancelButton: SwalDemoController.#BTN_DANGER },
          buttonsStyling: false,
        });
        swalWithBootstrapButtons
          .fire({
            title: "Are you sure?",
            text: "You won't be able to revert this!",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Yes, delete it!",
            cancelButtonText: "No, cancel!",
            reverseButtons: true,
          })
          .then(result => {
            result.isConfirmed ? swalWithBootstrapButtons.fire("Deleted!", "Your file has been deleted.", "success") : result.dismiss === Swal.DismissReason.cancel && swalWithBootstrapButtons.fire("Cancelled", "Your imaginary file is safe :)", "error");
          });
      });

      this.#bind(".bs-img", () =>
        Swal.fire({
          title: "Sweet!",
          text: "Modal with a custom image.",
          imageUrl: "https://unsplash.it/400/200",
          imageWidth: 400,
          imageHeight: 200,
          imageAlt: "Custom image",
        }),
      );

      this.#bind(".bs-cst-wi", () =>
        Swal.fire({
          title: "Custom width, padding, color, background.",
          width: 600,
          padding: "3em",
          color: "#716add",
          background: "#fff url(https://sweetalert2.github.io/images/trees.png)",
          backdrop: `rgba(0,0,123,0.4) url("https://sweetalert2.github.io/images/nyan-cat.gif") left top no-repeat`,
        }),
      );

      this.#bind(".bs-auto-c", () => {
        let timerInterval;
        Swal.fire({
          title: "Auto close alert!",
          html: "I will close in <b></b> milliseconds.",
          timer: 2000,
          timerProgressBar: true,
          didOpen: () => {
            Swal.showLoading();
            timerInterval = setInterval(() => {
              const content = Swal.getHtmlContainer();
              if (content) {
                const b = content.querySelector("b");
                if (b) b.textContent = Swal.getTimerLeft();
              }
            }, 100);
          },
          willClose: () => clearInterval(timerInterval),
        }).then(result => {
          if (result.dismiss === Swal.DismissReason.timer) console.log("I was closed by the timer");
        });
      });
    }

    /**
     * Setup input dialog demos
     * @private
     */
    #setupInputDemos() {
      this.#bind(".bs-txt", async () => {
        const { value: text } = await Swal.fire({
          input: "text",
          inputLabel: "Your nickname",
          inputPlaceholder: "Type your nickname here",
          inputAttributes: { "aria-label": "Type your nickname here" },
          showCancelButton: true,
        });
        if (text) Swal.fire(`Entered nickname: ${text}`);
      });

      this.#bind(".bs-eml", async () => {
        const { value: email } = await Swal.fire({
          title: "Input email address",
          input: "email",
          inputLabel: "Your email address",
          inputPlaceholder: "Enter your email address",
        });
        if (email) Swal.fire(`Entered email: ${email}`);
      });

      this.#bind(".bs-url", async () => {
        const { value: url } = await Swal.fire({
          input: "url",
          inputLabel: "URL address",
          inputPlaceholder: "Enter the URL",
        });
        if (url) Swal.fire(`Entered URL: ${url}`);
      });

      this.#bind(".bs-pwd", async () => {
        const { value: password } = await Swal.fire({
          title: "Enter your password",
          input: "password",
          inputLabel: "Password",
          inputPlaceholder: "Enter your password",
          inputAttributes: { maxlength: "10", autocapitalize: "off", autocorrect: "off" },
        });
        if (password) Swal.fire(`Entered password: ${password}`);
      });

      this.#bind(".bs-txa", async () => {
        const { value: text } = await Swal.fire({
          input: "textarea",
          inputLabel: "Message",
          inputPlaceholder: "Type your message here...",
          inputAttributes: { "aria-label": "Type your message here" },
          showCancelButton: true,
        });
        if (text) Swal.fire(text);
      });

      this.#bind(".bs-slt", async () => {
        const { value: fruit } = await Swal.fire({
          title: "Select field validation",
          input: "select",
          inputOptions: { apples: "Apples", bananas: "Bananas", grapes: "Grapes", oranges: "Oranges" },
          inputPlaceholder: "Select a fruit",
          showCancelButton: true,
          inputValidator: value => new Promise(resolve => (value === "oranges" ? resolve() : resolve("You need to select oranges :)"))),
        });
        if (fruit) Swal.fire(`You selected: ${fruit}`);
      });

      this.#bind(".bs-rdo", async () => {
        const inputOptions = new Promise(resolve => setTimeout(() => resolve({ "#ff0000": "Red", "#00ff00": "Green", "#0000ff": "Blue" }), 1000));
        const { value: color } = await Swal.fire({
          title: "Select color",
          input: "radio",
          inputOptions,
          inputValidator: value => !value && "You need to choose something!",
        });
        if (color) Swal.fire({ html: `You selected: ${color}` });
      });

      this.#bind(".bs-chk", async () => {
        const { value: accept } = await Swal.fire({
          title: "Terms and conditions",
          input: "checkbox",
          inputValue: 1,
          inputPlaceholder: "I agree with the terms and conditions",
          confirmButtonText: 'Continue <i class="fa fa-arrow-right"></i>',
          inputValidator: result => !result && "You need to agree with T&C",
        });
        if (accept) Swal.fire("You agreed with T&C :)");
      });

      this.#bind(".bs-fil", () => {
        Swal.fire({
          title: "Select image",
          input: "file",
          inputAttributes: { accept: "image/*", "aria-label": "Upload your profile picture" },
        }).then(result => {
          if (result.value) {
            const reader = new FileReader();
            reader.onload = e => Swal.fire({ title: "Your uploaded picture", imageUrl: e.target.result, imageAlt: "The uploaded picture" });
            reader.readAsDataURL(result.value);
          }
        });
      });

      this.#bind(".bs-rng", async () => {
        const { value: number } = await Swal.fire({
          title: "How old are you?",
          icon: "question",
          input: "range",
          inputLabel: "Your age",
          inputAttributes: { min: "8", max: "120", step: "1" },
          inputValue: 25,
        });
        if (number) Swal.fire(`You are ${number} years old`);
      });

      this.#bind(".bs-mlt-inp", async () => {
        const { value: formValues } = await Swal.fire({
          title: "Multiple inputs",
          html: '<input id="swal-input1" class="swal2-input"><input id="swal-input2" class="swal2-input">',
          focusConfirm: false,
          preConfirm: () => [document.getElementById("swal-input1").value, document.getElementById("swal-input2").value],
        });
        if (formValues) Swal.fire(JSON.stringify(formValues));
      });
    }

    /**
     * Setup advanced dialog demos
     * @private
     */
    #setupAdvancedDemos() {
      this.#bind(".bs-a-q", () => Swal.fire({ icon: "error", title: "Oops...", text: "Something went wrong!", footer: '<a href="">Why do I have this issue?</a>' }));

      this.#bind(".bs-mod-dis", () =>
        Swal.fire({
          title: "Do you want to save the changes?",
          showDenyButton: true,
          showCancelButton: true,
          confirmButtonText: "Save",
          denyButtonText: "Don't save",
        }).then(result => {
          result.isConfirmed ? Swal.fire("Saved!", "", "success") : result.isDenied && Swal.fire("Changes are not saved", "", "info");
        }),
      );

      this.#bind(".bs-rtl", () =>
        Swal.fire({
          title: "!هل تريد الاستمرار؟",
          icon: "question",
          iconHtml: "؟",
          confirmButtonText: "نعم",
          cancelButtonText: "لا",
          showCancelButton: true,
          showCloseButton: true,
        }),
      );

      this.#bind(".bs-mixin", () => {
        const Toast = Swal.mixin({
          toast: true,
          position: "top-end",
          showConfirmButton: false,
          timer: 3000,
          timerProgressBar: true,
          didOpen: toast => {
            toast.addEventListener("mouseenter", Swal.stopTimer);
            toast.addEventListener("mouseleave", Swal.resumeTimer);
          },
        });
        Toast.fire({ icon: "success", title: "Signed in successfully" });
      });

      this.#bind(".bs-ajax", () =>
        Swal.fire({
          title: "Submit your Github username",
          input: "text",
          inputAttributes: { autocapitalize: "off" },
          showCancelButton: true,
          confirmButtonText: "Look up",
          showLoaderOnConfirm: true,
          preConfirm: login =>
            fetch(`//api.github.com/users/${login}`)
              .then(response => {
                if (!response.ok) throw new Error(response.statusText);
                return response.json();
              })
              .catch(error => Swal.showValidationMessage(`Request failed: ${error}`)),
          allowOutsideClick: () => !Swal.isLoading(),
        }).then(result => {
          if (result.isConfirmed) Swal.fire({ title: `${result.value.login}'s avatar`, imageUrl: result.value.avatar_url });
        }),
      );

      this.#bind(".bs-que", () =>
        Swal.mixin({ input: "text", confirmButtonText: "Next &rarr;", showCancelButton: true, progressSteps: ["1", "2", "3"] })
          .queue([{ title: "Question 1", text: "Chaining swal modals is easy" }, "Question 2", "Question 3"])
          .then(result => {
            if (result.value) {
              const answers = JSON.stringify(result.value);
              Swal.fire({ title: "All done!", html: `Your answers: <pre><code>${answers}</code></pre>`, confirmButtonText: "Lovely!" });
            }
          }),
      );

      this.#bind(".bs-dns", () =>
        Swal.fire({
          title: "Dynamically queue example",
          confirmButtonText: "Search!",
          text: "Configure ip-api.com" + "'s JSON API",
          footer: "<small>Query: <a href='http://ip-api.com/json'>ip-api.com/json</a></small>",
          showLoaderOnConfirm: true,
          preConfirm: () =>
            fetch("https://api.ipify.org?format=json")
              .then(response => response.json())
              .then(data => Swal.insertQueueStep(data.ip))
              .catch(() => Swal.insertQueueStep({ icon: "error", title: "Unable to get your public IP" })),
        }),
      );
    }
  }

  /**
   * Initialize SweetAlert2 demos when DOM ready
   */
  const initSwalDemos = () => {
    try {
      new SwalDemoController().init();
    } catch (err) {
      console.error("[SwalDemoController] Initialization error:", err);
    }
  };

  document.readyState === "loading" ? document.addEventListener("DOMContentLoaded", initSwalDemos) : initSwalDemos();
})();
