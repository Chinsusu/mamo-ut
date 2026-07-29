// UI-only bootstrap for the Laravel handoff.
// Cart, checkout, product selection and persistence should be implemented
// as Livewire components / Laravel actions, not by importing data.js.

document.addEventListener('DOMContentLoaded', () => {
  const button = document.querySelector('[data-mobile-menu-button]');
  const menu = document.querySelector('[data-mobile-menu]');
  if (button && menu) {
    button.addEventListener('click', () => {
      const open = !menu.classList.contains('is-open');
      menu.classList.toggle('is-open', open);
      button.setAttribute('aria-expanded', String(open));
      menu.setAttribute('aria-hidden', String(!open));
      document.body.classList.toggle('menu-open', open);
    });
  }
});
