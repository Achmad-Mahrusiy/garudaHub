// Konfirmasi sebelum submit form yang punya atribut data-confirm.
// Pengganti onsubmit="..." karena CSP melarang JavaScript inline.
document.addEventListener('submit', function (event) {
    var message = event.target.getAttribute('data-confirm');
    if (message && !window.confirm(message)) {
        event.preventDefault();
    }
});
