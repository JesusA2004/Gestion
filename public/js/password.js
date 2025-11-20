document.addEventListener('DOMContentLoaded', () => {
    // Toggle mostrar/ocultar contraseña
    document.querySelectorAll('[data-toggle-password]').forEach(button => {
    const targetId = button.getAttribute('data-target');
    const input = document.getElementById(targetId);
    if (!input) return;
        button.addEventListener('click', () => {
            input.type = input.type === 'password' ? 'text' : 'password';
        });
    });
});