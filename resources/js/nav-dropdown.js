/**
 * Closes open header dropdowns (native <details>) on an outside click or Escape.
 */
export function initNavDropdowns(root = document) {
    const dropdowns = [...root.querySelectorAll('[data-nav-dropdown]')];

    if (dropdowns.length === 0) {
        return;
    }

    root.addEventListener('click', (event) => {
        dropdowns.forEach((dropdown) => {
            if (dropdown.open && !dropdown.contains(event.target)) {
                dropdown.open = false;
            }
        });
    });

    root.addEventListener('keydown', (event) => {
        if (event.key !== 'Escape') {
            return;
        }

        dropdowns.forEach((dropdown) => {
            if (dropdown.open) {
                dropdown.open = false;
                dropdown.querySelector('summary')?.focus();
            }
        });
    });
}
