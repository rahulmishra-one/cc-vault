document.addEventListener('DOMContentLoaded', () => {
  const button = document.querySelector('.menu-button');
  const sidebar = document.querySelector('.sidebar');
  if (!button || !sidebar) return;
  button.addEventListener('click', () => sidebar.classList.toggle('is-open'));
});
