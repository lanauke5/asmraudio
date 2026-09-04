(() => {
  const button = document.querySelector('#theme');
  const saved = localStorage.getItem('asmr-theme');
  const prefersLight = window.matchMedia?.('(prefers-color-scheme: light)').matches;
  if (saved === 'light' || (!saved && prefersLight)) document.body.classList.add('light');
  if (!button) return;
  const updateLabel = () => button.setAttribute('aria-label', document.body.classList.contains('light') ? 'Switch to dark theme' : 'Switch to light theme');
  updateLabel();
  button.addEventListener('click', () => {
    document.body.classList.toggle('light');
    localStorage.setItem('asmr-theme', document.body.classList.contains('light') ? 'light' : 'dark');
    updateLabel();
  });
})();

(() => {
  const embeds = document.querySelectorAll('[data-youtube-id]');
  if (!('IntersectionObserver' in window)) return;
  const observer = new IntersectionObserver(entries => entries.forEach(entry => {
    if (!entry.isIntersecting) return;
    const box = entry.target, id = box.dataset.youtubeId;
    if (!id) return;
    const frame = document.createElement('iframe');
    frame.loading = 'lazy'; frame.title = box.dataset.youtubeTitle || 'Official YouTube video';
    frame.allow = 'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share'; frame.allowFullscreen = true;
    frame.src = 'https://www.youtube-nocookie.com/embed/' + encodeURIComponent(id);
    box.replaceChildren(frame); observer.unobserve(box);
  }));
  embeds.forEach(embed => observer.observe(embed));
})();

(() => {
  const player = document.querySelector('#audio-player');
  const timer = document.querySelector('#sleep-timer');
  if (!player || !timer) return;
  let timeout;
  timer.addEventListener('change', () => {
    clearTimeout(timeout);
    const minutes = Number(timer.value);
    if (minutes > 0) timeout = setTimeout(() => { player.pause(); player.currentTime = 0; timer.value = '0'; }, minutes * 60000);
  });
})();
