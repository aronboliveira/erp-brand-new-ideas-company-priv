'use strict';
// PULL REQUEST START — Alteração customizada em arquivo vendor (null guards)
// Defensiva: null guard em querySelector antes de addEventListener
function _bindClick(selector, handler) {
    var el = document.querySelector(selector);
    if (el) el.addEventListener("click", handler);
}

_bindClick(".bs-message", function () {
    Swal.fire('Any fool can use a computer')
});
_bindClick('.bs-tit-txt', function () {
    Swal.fire(
        'The Internet?',
        'That thing is still around?',
        'question'
    )
});
_bindClick('.bs-error-icon', function () {
    Swal.fire({
        icon: 'error',
        title: 'Oops...',
        text: 'Something went wrong!',
        footer: '<a href>Why do I have this issue?</a>'
    })
});
_bindClick('.bs-long-content', function () {
    Swal.fire({
        imageUrl: 'https://placeholder.pics/svg/300x1500',
        imageHeight: 1500,
        imageAlt: 'A tall image'
    })
});
_bindClick('.bs-cust-html', function () {
    Swal.fire({
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
_bindClick('.bs-tre-button', function () {
    Swal.fire({
        title: 'Do you want to save the changes?',
        showDenyButton: true,
        showCancelButton: true,
        confirmButtonText: `Save`,
        denyButtonText: `Don't save`,
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire('Saved!', '', 'success')
        } else if (result.isDenied) {
            Swal.fire('Changes are not saved', '', 'info')
        }
    })
});
_bindClick('.bs-cust-position', function () {
    Swal.fire({
        position: 'top-end',
        icon: 'success',
        title: 'Your work has been saved',
        showConfirmButton: false,
        timer: 1500
    })
});
_bindClick('.bs-cust-anim', function () {
    Swal.fire({
        title: 'Custom animation with Animate.css',
        showClass: {
            popup: 'animated fadeInDown'
        },
        hideClass: {
            popup: 'animated fadeOutUp'
        }
    })
});
_bindClick('.bs-pass-para', function () {
    const swalWithBootstrapButtons = Swal.mixin({
        customClass: {
            confirmButton: 'btn btn-success',
            cancelButton: 'btn btn-danger'
        },
        buttonsStyling: false
    })
    swalWithBootstrapButtons.fire({
        title: 'Are you sure?',
        text: "You won't be able to revert this!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, delete it!',
        cancelButtonText: 'No, cancel!',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            swalWithBootstrapButtons.fire(
                'Deleted!',
                'Your file has been deleted.',
                'success'
            )
        } else if (
            result.dismiss === Swal.DismissReason.cancel
        ) {
            swalWithBootstrapButtons.fire(
                'Cancelled',
                'Your imaginary file is safe :)',
                'error'
            )
        }
    })
});
_bindClick('.bs-cust-img', function () {
    Swal.fire({
        title: 'Sweet!',
        text: 'Modal with a custom image.',
        imageUrl: 'https://unsplash.it/400/200',
        imageWidth: 400,
        imageHeight: 200,
        imageAlt: 'Custom image',
    })
});
_bindClick('.bs-cust-full', function () {
    Swal.fire({
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
_bindClick('.bs-auto-close', function () {
    let timerInterval
    Swal.fire({
        title: 'Auto close alert!',
        html: 'I will close in <b></b> milliseconds.',
        timer: 2000,
        timerProgressBar: true,
        willOpen: () => {
            Swal.showLoading()
            timerInterval = setInterval(() => {
                const content = Swal.getContent()
                if (content) {
                    const b = content.querySelector('b')
                    if (b) {
                        b.textContent = Swal.getTimerLeft()
                    }
                }
            }, 100)
        },
        onClose: () => {
            clearInterval(timerInterval)
        }
    }).then((result) => {
        if (result.dismiss === Swal.DismissReason.timer) {
            console.log('I was closed by the timer')
        }
    })
});
_bindClick('.bs-rtl-lang', function () {
    Swal.fire({
        title: 'هل تريد الاستمرار؟',
        icon: 'question',
        iconHtml: '؟',
        confirmButtonText: 'نعم',
        cancelButtonText: 'لا',
        showCancelButton: true,
        showCloseButton: true
    })
});
_bindClick('.bs-ajex-req', function () {
    Swal.fire({
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
            Swal.fire({
                title: result.value.login +`'s avatar`,
                imageUrl: result.value.avatar_url
            })
        }
    })
});
_bindClick('.bs-mixin-exp', function () {
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
    Toast.fire({
        icon: 'success',
        title: 'Signed in successfully'
    })
});
_bindClick('.bs-success-ico', function () {
    Swal.fire({
        icon: "success",
        title: 'Success modal',
    })
});
_bindClick('.bs-error-ico', function () {
    Swal.fire({
        icon: "error",
        title: 'Error modal',
    })
});
_bindClick('.bs-warning-ico', function () {
    Swal.fire({
        icon: "warning",
        title: 'warning modal',
    })
});
_bindClick('.bs-info-ico', function () {
    Swal.fire({
        icon: "info",
        title: 'info modal',
    })
});
_bindClick('.bs-question-ico', function () {
    Swal.fire({
        icon: "question",
        title: 'question modal',
    })
});
_bindClick('.bs-text-input', function () {
    (async () => {
        const ipAPI = '//api.ipify.org?format=json'
        const inputValue = fetch(ipAPI)
            .then(response => response.json())
            .then(data => data.ip)
        const {
            value: ipAddress
        } = await Swal.fire({
            title: 'Enter your IP address',
            input: 'text',
            inputValue: inputValue,
            showCancelButton: true,
            inputValidator: (value) => {
                if (!value) {
                    return 'You need to write something!'
                }
            }
        })
        if (ipAddress) {
            Swal.fire(`Your IP address is ` + ipAddress)
        }
    })()
});
_bindClick('.bs-email-input', function () {
    (async () => {
        const {
            value: email
        } = await Swal.fire({
            title: 'Input email address',
            input: 'email',
            inputPlaceholder: 'Enter your email address'
        })

        if (email) {
            Swal.fire(`Entered email: ` + email)
        }
    })()
});
_bindClick('.bs-url-input', function () {
    (async () => {
        const {
            value: url
        } = await Swal.fire({
            input: 'url',
            inputPlaceholder: 'Enter the URL'
        })
        if (url) {
            Swal.fire(`Entered URL: ` + url)
        }
    })()
});
_bindClick('.bs-password-input', function () {
    (async () => {
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
            Swal.fire(`Entered password: `+ password)
        }
    })()
});
_bindClick('.bs-textarea-input', function () {
    (async () => {
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
            Swal.fire(text)
        }
    })()
});
_bindClick('.bs-select-input', function () {
    (async () => {
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
            Swal.fire(`You selected: ` + fruit)
        }
    })()
});
_bindClick('.bs-radio-input', function () {
    (async () => {
        const inputOptions = new Promise((resolve) => {
            setTimeout(() => {
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
                if (!value) {
                    return 'You need to choose something!'
                }
            }
        })
        if (color) {
            Swal.fire({
                html: `You selected: ` + color
            })
        }
    })()
});
_bindClick('.bs-checkbox-input', function () {
    (async () => {
        const {
            value: accept
        } = await Swal.fire({
            title: 'Terms and conditions',
            input: 'checkbox',
            inputValue: 1,
            inputPlaceholder: 'I agree with the terms and conditions',
            confirmButtonText: 'Continue<i class="fa fa-arrow-right"></i>',
            inputValidator: (result) => {
                return !result && 'You need to agree with T&C'
            }
        })
        if (accept) {
            Swal.fire('You agreed with T&C :)')
        }
    })()
});
_bindClick('.bs-file-input', function () {
    (async () => {
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
            reader.onload = (e) => {
                Swal.fire({
                    title: 'Your uploaded picture',
                    imageUrl: e.target.result,
                    imageAlt: 'The uploaded picture'
                })
            }
            reader.readAsDataURL(file)
        }
    })()
});
_bindClick('.bs-range-input', function () {
    (async () => {
        Swal.fire({
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
_bindClick('.bs-multiple-input', function () {
    (async () => {
        const {
            value: formValues
        } = await Swal.fire({
            title: 'Multiple inputs',
            html: '<input id="swal-input1" class="swal2-input">' +
                '<input id="swal-input2" class="swal2-input">',
            focusConfirm: false,
            preConfirm: () => {
                return [
                    document.getElementById('swal-input1').value,
                    document.getElementById('swal-input2').value
                ]
            }
        })
        if (formValues) {
            Swal.fire(JSON.stringify(formValues))
        }
    })()
});
// PULL REQUEST END — Fim da alteração customizada
});