if ('serviceWorker' in navigator) {
    window.addEventListener("load", () => {
        navigator.serviceWorker.register('service-worker.js', { scope: '/___project path goes here___/' })
            .then(reg => console.log("Service worker registered", reg))
            .catch(err => console.error(`Service Worker Error: ${err}`));
    });
}
 else {
    console.log("Service Worker is not supported by browser.");
}

