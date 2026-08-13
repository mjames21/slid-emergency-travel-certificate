if ('serviceWorker' in navigator) {
  window.addEventListener('load', function () {
    navigator.serviceWorker.register('/service-worker.js').catch(function () {
      // Registration failures must not block the ETC application flow.
    });
  });
}
