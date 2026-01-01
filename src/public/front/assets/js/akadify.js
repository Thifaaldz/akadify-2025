document.addEventListener('livewire:load', () => {
    const form = document.querySelector('.akadify-form');
    if (!form) return;

    form.addEventListener('submit', () => {
        const btn = form.querySelector('.akadify-btn');
        if (btn) {
            btn.disabled = true;
            btn.innerText = 'Memverifikasi...';
        }
    });
});
