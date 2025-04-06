document.addEventListener('DOMContentLoaded', function () {
    const form = document.querySelector('form.woocommerce-EditAccountForm');
    if (form && !form.hasAttribute('enctype')) {
        form.setAttribute('enctype', 'multipart/form-data');
    }
});
