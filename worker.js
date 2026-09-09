/*
    First, you will need to register a service worker.
    You can choose to do it in a separate file and link it to the index.html (Or, the main HTML file in the root of your project). 
    But you will often see a service worker being registered in the HTML file itself, within a <script> tag.
*/
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

