// Akkordeon-Funktion für die Startseite (index.html)
document.querySelectorAll('.accordion').forEach(acc => {
  acc.addEventListener('click', () => {
    acc.classList.toggle('active');
    const panel = acc.nextElementSibling;
    if (panel.style.maxHeight) {
      panel.style.maxHeight = null;
    } else {
      panel.style.maxHeight = panel.scrollHeight + "px";
    }
  });
});
