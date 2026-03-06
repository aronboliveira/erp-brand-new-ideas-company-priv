/**
 * @fileoverview TypeScript version of public/Modules/landingpage/js/pages/ac-alert.js
 * @generated from original JavaScript - manual review recommended
 * @module ac-alert
 */
/* eslint-disable @typescript-eslint/no-base-to-string, @typescript-eslint/no-unsafe-argument, @typescript-eslint/no-unsafe-assignment, @typescript-eslint/no-unsafe-call, @typescript-eslint/no-unsafe-member-access, @typescript-eslint/no-unsafe-return, @typescript-eslint/no-unused-vars, @typescript-eslint/require-await, @typescript-eslint/restrict-plus-operands, no-console */

/* global bootstrap, Swal */
'use strict';

document.querySelector<HTMLElement>(".bs-message")?.addEventListener("click", function (): void {
    void Swal.fire('Any fool can use a computer')
});
document.querySelector<HTMLElement>(".bs-tit-txt")?.addEventListener("click", function (): void {
    void Swal.fire(
        'The Internet?',
        'That thing is still around?',
        'question'
    )
});
document.querySelector<HTMLElement>(".bs-error-icon")?.addEventListener("click", function (): void {
    void Swal.fire({
        icon: 'error',
        title: 'Oops...',
        text: 'Something went wrong!',
        footer: '<a href>Why do I have this issue?</a>'
    })
});
document.querySelector<HTMLElement>(".bs-long-content")?.addEventListener("click", function (): void {
    void Swal.fire({
        imageUrl: 'https://placeholder.pics/svg/300x1500',
        imageHeight: 1500,
        imageAlt: 'A tall image'
    })
});
document.querySelector<HTMLElement>(".bs-cust-html")?.addEventListener("click", function (): void {
    void Swal.fire({
        title: '<strong>HTML <u>example</u></strong>',
        icon: 'info',
        html: 'You can use <b>bold text</b>, ' +
            '<a href="//sweetalert2.github.io">links</a> ' +
            'and other HTML tags',
        showCloseButton: true,
        showCancelButton: true,
        focusConfirm: false,
        confirmButtonText: '<i class="fa fa-thumbs-up"></i> Great!',
        confirmButtonAriaLabel: 'Thumbs up, great!',
        cancelButtonText: '<i class="fa fa-thumbs-down"></i>',
        cancelButtonAriaLabel: 'Thumbs down'
    })
});
document.querySelector<HTMLElement>(".bs-tre-button")?.addEventListener("click", function (): void {
    void Swal.fire({
        title: 'Do you want to save the changes?',
        showDenyButton: true,
        showCancelButton: true,
        confirmButtonText: `Save`,
        denyButtonText: `Don't save`,
    }).then((result) => {
        if (result.isConfirmed) {
            void Swal.fire('Saved!', '', 'success')
        } else if (result.isDenied) {
            void Swal.fire('Changes are not saved', '', 'info')
        }
    })
});
document.querySelector<HTMLElement>(".bs-cust-position")?.addEventListener("click", function (): void {
    void Swal.fire({
        position: 'top-end',
        icon: 'success',
        title: 'Your work has been saved',
        showConfirmButton: false,
        timer: 1500
    })
});
document.querySelector<HTMLElement>(".bs-cust-anim")?.addEventListener("click", function (): void {
    void Swal.fire({
        title: 'Custom animation with Animate.css',
        showClass: {
            popup: 'animated fadeInDown'
        },
        hideClass: {
            popup: 'animated fadeOutUp'
        }
    })
});
document.querySelector<HTMLElement>(".bs-pass-para")?.addEventListener("click", function (): void {
    const swalWithBootstrapButtons = Swal.mixin({
        customClass: {
            confirmButton: 'btn btn-success',
            cancelButton: 'btn btn-danger'
        },
        buttonsStyling: false
    })
    void swalWithBootstrapButtons.fire({
        title: 'Are you sure?',
        text: "You won't be able to revert this!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, delete it!',
        cancelButtonText: 'No, cancel!',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            void swalWithBootstrapButtons.fire(
                'Deleted!',
                'Your file has been deleted.',
                'success'
            )
        } else if (
            result.dismiss === Swal.DismissReason.cancel
        ) {
            void swalWithBootstrapButtons.fire(
                'Cancelled',
                'Your imaginary file is safe :)',
                'error'
            )
        }
    })
});
document.querySelector<HTMLElement>(".bs-cust-img")?.addEventListener("click", function (): void {
    void Swal.fire({
        title: 'Sweet!',
        text: 'Modal with a custom image.',
        imageUrl: 'https://unsplash.it/400/200',
        imageWidth: 400,
        imageHeight: 200,
        imageAlt: 'Custom image',
    })
});
document.querySelector<HTMLElement>(".bs-cust-full")?.addEventListener("click", function (): void {
    void Swal.fire({
        title: 'Custom width, padding, background.',
        width: 600,
        padding: '3em',
        background: '#fff url(assets/images/gallery-grid/img-grd-gal-2.jpg)',
        backdrop: `
                rgba(0,0,123,0.4)
                url("../assets/images/profile/bg-2.jpg")
                left top
                no-repeat
              `
    })
});
document.querySelector<HTMLElement>(".bs-auto-close")?.addEventListener("click", function (): void {
    let timerInterval
    void Swal.fire({
        title: 'Auto close alert!',
        html: 'I will close in <b></b> milliseconds.',
        timer: 2000,
        timerProgressBar: true,
        willOpen: (): void => {
            Swal.showLoading()
            timerInterval = setInterval((): void => {
                const content = Swal.getContent()
                if (content) {
                    const b = content.querySelector('b')
                    if (b) {
                        b.textContent = Swal.getTimerLeft()
                    }
                }
            }, 100)
        },
        onClose: (): void => {
            clearInterval(timerInterval)
        }
    }).then((result) => {
        if (result.dismiss === Swal.DismissReason.timer) {
            console.log('I was closed by the timer')
        }
    })
});
document.querySelector<HTMLElement>(".bs-rtl-lang")?.addEventListener("click", function (): void {
    void Swal.fire({
        title: 'هل تريد الاستمرار؟',
        icon: 'question',
        iconHtml: '؟',
        confirmButtonText: 'نعم',
        cancelButtonText: 'لا',
        showCancelButton: true,
        showCloseButton: true
    })
});
document.querySelector<HTMLElement>(".bs-ajex-req")?.addEventListener("click", function (): void {
    void Swal.fire({
        title: 'Submit your Github username',
        input: 'text',
        inputAttributes: {
            autocapitalize: 'off'
        },
        showCancelButton: true,
        confirmButtonText: 'Look up',
        showLoaderOnConfirm: true,
        preConfirm: (login) => {
            return fetch(`//api.github.com/users/` + login)
                .then(response => {
                    if (!response.ok) {
                        throw new Error(response.statusText)
                    }
                    return response.json()
                })
                .catch(error => {
                    Swal.showValidationMessage(
                        `Request failed: ` + error
                    )
                })
        },
        allowOutsideClick: () => !Swal.isLoading()
    }).then((result) => {
        if (result.isConfirmed) {
            void Swal.fire({
                title: result.value.login +`'s avatar`,
                imageUrl: result.value.avatar_url
            })
        }
    })
});
document.querySelector<HTMLElement>(".bs-mixin-exp")?.addEventListener("click", function (): void {
    const Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true,
        didOpen: (toast) => {
            toast.addEventListener('mouseenter', Swal.stopTimer)
            toast.addEventListener('mouseleave', Swal.resumeTimer)
        }
    })
    void Toast.fire({
        icon: 'success',
        title: 'Signed in successfully'
    })
});
document.querySelector<HTMLElement>(".bs-success-ico")?.addEventListener("click", function (): void {
    void Swal.fire({
        icon: "success",
        title: 'Success modal',
    })
});
document.querySelector<HTMLElement>(".bs-error-ico")?.addEventListener("click", function (): void {
    void Swal.fire({
        icon: "error",
        title: 'Error modal',
    })
});
document.querySelector<HTMLElement>(".bs-warning-ico")?.addEventListener("click", function (): void {
    void Swal.fire({
        icon: "warning",
        title: 'warning modal',
    })
});
document.querySelector<HTMLElement>(".bs-info-ico")?.addEventListener("click", function (): void {
    void Swal.fire({
        icon: "info",
        title: 'info modal',
    })
});
document.querySelector<HTMLElement>(".bs-question-ico")?.addEventListener("click", function (): void {
    void Swal.fire({
        icon: "question",
        title: 'question modal',
    })
});
document.querySelector<HTMLElement>(".bs-text-input")?.addEventListener("click", function (): void {
    (async (): void => {
        const ipAPI = '//api.ipify.org?format=json'
        const inputValue = fetch(ipAPI)
            .then(response => response.json().catch(console.error))
            .then(data => data.ip)
        const {
            value: ipAddress
        } = await Swal.fire({
            title: 'Enter your IP address',
            input: 'text',
            inputValue: inputValue,
            showCancelButton: true,
            inputValidator: (value) => {
                if (value === "") {
                    return 'You need to write something!'
                }
            }
        })
        if (ipAddress) {
            void Swal.fire(`Your IP address is ` + ipAddress)
        }
    })()
});
document.querySelector<HTMLElement>(".bs-email-input")?.addEventListener("click", function (): void {
    (async (): void => {
        const {
            value: email
        } = await Swal.fire({
            title: 'Input email address',
            input: 'email',
            inputPlaceholder: 'Enter your email address'
        })

        if (email) {
            void Swal.fire(`Entered email: ` + email)
        }
    })()
});
document.querySelector<HTMLElement>(".bs-url-input")?.addEventListener("click", function (): void {
    (async (): void => {
        const {
            value: url
        } = await Swal.fire({
            input: 'url',
            inputPlaceholder: 'Enter the URL'
        })
        if (url) {
            void Swal.fire(`Entered URL: ` + url)
        }
    })()
});
document.querySelector<HTMLElement>(".bs-password-input")?.addEventListener("click", function (): void {
    (async (): void => {
        const {
            value: password
        } = await Swal.fire({
            title: 'Enter your password',
            input: 'password',
            inputPlaceholder: 'Enter your password',
            inputAttributes: {
                maxlength: 10,
                autocapitalize: 'off',
                autocorrect: 'off'
            }
        })
        if (password) {
            void Swal.fire(`Entered password: `+ password)
        }
    })()
});
document.querySelector<HTMLElement>(".bs-textarea-input")?.addEventListener("click", function (): void {
    (async (): void => {
        const {
            value: text
        } = await Swal.fire({
            input: 'textarea',
            inputPlaceholder: 'Type your message here...',
            inputAttributes: {
                'aria-label': 'Type your message here'
            },
            showCancelButton: true
        })
        if (text) {
            void Swal.fire(text)
        }
    })()
});
document.querySelector<HTMLElement>(".bs-select-input")?.addEventListener("click", function (): void {
    (async (): void => {
        const {
            value: fruit
        } = await Swal.fire({
            title: 'Select field validation',
            input: 'select',
            inputOptions: {
                'Fruits': {
                    apples: 'Apples',
                    bananas: 'Bananas',
                    grapes: 'Grapes',
                    oranges: 'Oranges'
                },
                'Vegetables': {
                    potato: 'Potato',
                    broccoli: 'Broccoli',
                    carrot: 'Carrot'
                },
                'icecream': 'Ice cream'
            },
            inputPlaceholder: 'Select a fruit',
            showCancelButton: true,
            inputValidator: (value) => {
                return new Promise((resolve) => {
                    if (value === 'oranges') {
                        resolve()
                    } else {
                        resolve('You need to select oranges :)')
                    }
                })
            }
        })
        if (fruit) {
            void Swal.fire(`You selected: ` + fruit)
        }
    })()
});
document.querySelector<HTMLElement>(".bs-radio-input")?.addEventListener("click", function (): void {
    (async (): void => {
        const inputOptions = new Promise((resolve) => {
            setTimeout((): void => {
                resolve({
                    '#ff0000': 'Red',
                    '#00ff00': 'Green',
                    '#0000ff': 'Blue'
                })
            }, 1000)
        })
        const {
            value: color
        } = await Swal.fire({
            title: 'Select color',
            input: 'radio',
            inputOptions: inputOptions,
            inputValidator: (value) => {
                if (value === "") {
                    return 'You need to choose something!'
                }
            }
        })
        if (color) {
            void Swal.fire({
                html: `You selected: ` + color
            })
        }
    })()
});
document.querySelector<HTMLElement>(".bs-checkbox-input")?.addEventListener("click", function (): void {
    (async (): void => {
        const {
            value: accept
        } = await Swal.fire({
            title: 'Terms and conditions',
            input: 'checkbox',
            inputValue: 1,
            inputPlaceholder: 'I agree with the terms and conditions',
            confirmButtonText: 'Continue<i class="fa fa-arrow-right"></i>',
            inputValidator: (result) => {
                // eslint-disable-next-line @typescript-eslint/strict-boolean-expressions
                return !result && 'You need to agree with T&C'
            }
        })
        if (accept) {
            void Swal.fire('You agreed with T&C :)')
        }
    })()
});
document.querySelector<HTMLElement>(".bs-file-input")?.addEventListener("click", function (): void {
    (async (): void => {
        const {
            value: file
        } = await Swal.fire({
            title: 'Select image',
            input: 'file',
            inputAttributes: {
                'accept': 'image/*',
                'aria-label': 'Upload your profile picture'
            }
        })
        if (file) {
            const reader = new FileReader()
            reader.onload = (e: Event): void => {
                void Swal.fire({
                    title: 'Your uploaded picture',
                    imageUrl: (e.target as FileReader | null)?.result,
                    imageAlt: 'The uploaded picture'
                })
            }
            reader.readAsDataURL(file)
        }
    })()
});
document.querySelector<HTMLElement>(".bs-range-input")?.addEventListener("click", function (): void {
    (async (): void => {
        void Swal.fire({
            title: 'How old are you?',
            icon: 'question',
            input: 'range',
            inputAttributes: {
                min: 8,
                max: 120,
                step: 1
            },
            inputValue: 25
        })
    })()
});
document.querySelector<HTMLElement>(".bs-multiple-input")?.addEventListener("click", function (): void {
    (async (): void => {
        const {
            value: formValues
        } = await Swal.fire({
            title: 'Multiple inputs',
            html: '<input id="swal-input1" class="swal2-input">' +
                '<input id="swal-input2" class="swal2-input">',
            focusConfirm: false,
            preConfirm: (): void => {
                return [
                    document.getElementById('swal-input1')?.value,
                    document.getElementById('swal-input2')?.value
                ]
            }
        })
        if (formValues) {
            void Swal.fire(JSON.stringify(formValues))
        }
    })()
});