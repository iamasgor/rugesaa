/**
 * Photo lightbox for gallery albums.
 *
 * Clicking a thumbnail opens the photo full screen in a native <dialog>.
 * Previous/next buttons, the arrow keys and swiping move through the album;
 * Escape, the close button or a click on the backdrop closes it.
 */
export function initGallery(root = document) {
    const dialog = root.querySelector('[data-lightbox]');
    const items = [...root.querySelectorAll('[data-gallery-item]')];

    if (!dialog || items.length === 0) {
        return;
    }

    const image = dialog.querySelector('[data-lightbox-image]');
    const title = dialog.querySelector('[data-lightbox-title]');
    const counter = dialog.querySelector('[data-lightbox-counter]');
    const prev = dialog.querySelector('[data-lightbox-prev]');
    const next = dialog.querySelector('[data-lightbox-next]');
    let current = 0;
    let opener = null;

    const preload = (index) => {
        const item = items[(index + items.length) % items.length];
        new Image().src = item.href;
    };

    const show = (index) => {
        current = (index + items.length) % items.length;
        const item = items[current];
        const caption = item.dataset.title ?? '';

        image.src = item.href;
        image.alt = caption;
        title.textContent = caption;
        counter.textContent = `${current + 1} / ${items.length}`;

        if (items.length > 1) {
            preload(current + 1);
            preload(current - 1);
        }
    };

    const single = items.length === 1;
    prev.hidden = single;
    next.hidden = single;

    items.forEach((item, index) => {
        item.addEventListener('click', (event) => {
            event.preventDefault();
            opener = item;
            show(index);
            dialog.showModal();
        });
    });

    prev.addEventListener('click', () => show(current - 1));
    next.addEventListener('click', () => show(current + 1));
    dialog.querySelector('[data-lightbox-close]').addEventListener('click', () => dialog.close());

    dialog.addEventListener('keydown', (event) => {
        if (event.key === 'ArrowLeft') {
            show(current - 1);
        } else if (event.key === 'ArrowRight') {
            show(current + 1);
        }
    });

    /* A click on the dark area around the photo closes the viewer. */
    dialog.addEventListener('click', (event) => {
        if (event.target === dialog || event.target === image.parentElement) {
            dialog.close();
        }
    });

    let touchStartX = null;
    dialog.addEventListener('touchstart', (event) => {
        touchStartX = event.touches[0].clientX;
    }, { passive: true });
    dialog.addEventListener('touchend', (event) => {
        if (touchStartX === null) {
            return;
        }
        const distance = event.changedTouches[0].clientX - touchStartX;
        touchStartX = null;
        if (Math.abs(distance) > 50) {
            show(current + (distance < 0 ? 1 : -1));
        }
    });

    dialog.addEventListener('close', () => {
        image.removeAttribute('src');
        opener?.focus();
    });
}
