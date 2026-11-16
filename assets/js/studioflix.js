document.querySelectorAll('[data-password-toggle]').forEach(function (button) {
    button.addEventListener('click', function () {
        var input = document.getElementById(button.getAttribute('data-password-toggle'));
        if (!input) return;
        var showing = input.type === 'text';
        input.type = showing ? 'password' : 'text';
        button.textContent = showing ? 'Mostrar' : 'Ocultar';
        button.setAttribute('aria-label', showing ? 'Mostrar senha' : 'Ocultar senha');
    });
});

document.querySelectorAll('form[data-confirm]').forEach(function (form) {
    form.addEventListener('submit', function (event) {
        if (!window.confirm(form.getAttribute('data-confirm'))) {
            event.preventDefault();
        }
    });
});

document.querySelectorAll('img[data-fallback-src]').forEach(function (image) {
    image.addEventListener('error', function () {
        var fallback = image.getAttribute('data-fallback-src');
        if (!fallback || image.dataset.fallbackTried === 'true') return;
        image.dataset.fallbackTried = 'true';
        image.src = fallback;
    });
});

(function () {
    var modal = document.getElementById('auth-modal');
    if (!modal) return;

    var dialog = modal.querySelector('.auth-modal-dialog');
    var form = modal.querySelector('[data-auth-modal-form]');
    var lastTrigger = null;
    var closeTimer = null;

    function closeModal() {
        if (modal.hidden || !modal.classList.contains('is-open')) return;
        modal.classList.remove('is-open');
        window.clearTimeout(closeTimer);
        var closeDelay = window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 0 : 360;
        closeTimer = window.setTimeout(function () {
            if (!modal.classList.contains('is-open')) {
                modal.hidden = true;
                document.body.classList.remove('auth-modal-open');
                if (lastTrigger) lastTrigger.focus();
            }
        }, closeDelay);
    }

    function openModal(trigger) {
        lastTrigger = trigger;
        var next = trigger.dataset.authNext || '';
        var movie = trigger.dataset.authMovie || '';
        var title = modal.querySelector('[data-auth-modal-title]');
        var description = modal.querySelector('[data-auth-modal-description]');
        var eyebrow = modal.querySelector('[data-auth-modal-eyebrow]');
        var registerLink = modal.querySelector('[data-auth-modal-register]');
        var nextField = form.querySelector('input[name="next"]');
        var error = form.querySelector('[data-auth-modal-error]');

        nextField.value = next;
        registerLink.href = 'cadastro.php' + (next ? '?next=' + encodeURIComponent(next) : '');
        title.textContent = movie ? 'Entre para assistir' : 'Entre no Studioflix';
        description.textContent = movie
            ? 'Entre na sua conta para continuar com ' + movie + '.'
            : 'Acesse sua conta e continue de onde a sua próxima história começa.';
        eyebrow.textContent = movie ? 'SUA PRÓXIMA SESSÃO' : 'BEM-VINDO DE VOLTA';
        error.hidden = true;
        error.textContent = '';

        window.clearTimeout(closeTimer);
        modal.hidden = false;
        document.body.classList.add('auth-modal-open');
        window.requestAnimationFrame(function () {
            modal.classList.add('is-open');
            var focusDelay = window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 0 : 240;
            window.setTimeout(function () {
                if (modal.classList.contains('is-open')) {
                    form.querySelector('input[name="email"]').focus({ preventScroll: true });
                }
            }, focusDelay);
        });
    }

    document.querySelectorAll('[data-auth-modal]').forEach(function (trigger) {
        trigger.addEventListener('click', function (event) {
            if (event.button !== 0 || event.ctrlKey || event.metaKey || event.shiftKey || event.altKey) return;
            event.preventDefault();
            openModal(trigger);
        });
    });

    modal.querySelectorAll('[data-auth-modal-close]').forEach(function (control) {
        control.addEventListener('click', closeModal);
    });

    form.addEventListener('submit', async function (event) {
        event.preventDefault();
        if (form.dataset.submitting === 'true') return;

        var submit = form.querySelector('button[type="submit"]');
        var error = form.querySelector('[data-auth-modal-error]');
        var originalLabel = submit.textContent;
        form.dataset.submitting = 'true';
        submit.disabled = true;
        submit.textContent = 'Entrando…';
        error.hidden = true;

        try {
            var response = await fetch(form.action, {
                method: 'POST',
                body: new FormData(form),
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            });
            var result = await response.json();
            if (result.ok && result.redirect) {
                window.location.assign(result.redirect);
                return;
            }
            error.textContent = result.message || 'Não consegui entrar. Confira seus dados e tente novamente.';
            error.hidden = false;
        } catch (requestError) {
            error.textContent = 'Não consegui concluir o login. Confira sua conexão e tente novamente.';
            error.hidden = false;
        } finally {
            form.dataset.submitting = 'false';
            submit.disabled = false;
            submit.textContent = originalLabel;
        }
    });

    document.addEventListener('keydown', function (event) {
        if (modal.hidden || !modal.classList.contains('is-open')) return;
        if (event.key === 'Escape') {
            event.preventDefault();
            closeModal();
            return;
        }
        if (event.key !== 'Tab') return;

        var focusable = Array.from(dialog.querySelectorAll('a[href], input:not([disabled]), button:not([disabled])'));
        if (!focusable.length) return;
        var first = focusable[0];
        var last = focusable[focusable.length - 1];
        if (event.shiftKey && document.activeElement === first) {
            event.preventDefault();
            last.focus();
        } else if (!event.shiftKey && document.activeElement === last) {
            event.preventDefault();
            first.focus();
        }
    });
})();

document.querySelectorAll('[data-watch-transition]').forEach(function (link) {
    link.addEventListener('click', function (event) {
        if (event.button !== 0 || event.ctrlKey || event.metaKey || event.shiftKey || event.altKey) return;
        event.preventDefault();
        var destination = link.href;
        document.body.classList.add('watch-transition-out');
        var delay = window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 0 : 250;
        window.setTimeout(function () {
            window.location.assign(destination);
        }, delay);
    });
});

