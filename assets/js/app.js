document.addEventListener('DOMContentLoaded', () => {
  const button = document.querySelector('.menu-button');
  const sidebar = document.querySelector('.sidebar');
  if (button && sidebar) {
    button.addEventListener('click', () => sidebar.classList.toggle('is-open'));
  }

  document.querySelectorAll('form[data-confirm]').forEach((form) => {
    form.addEventListener('submit', (event) => {
      if (!window.confirm(form.dataset.confirm || 'Are you sure?')) event.preventDefault();
    });
  });
});
