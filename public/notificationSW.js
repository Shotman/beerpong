const focusOrOpenUrl = (event) => {
    event.waitUntil(
        clients.matchAll({includeUncontrolled: true}).then( windowClients => {
            // Check if there is already a window/tab open with the target URL
            for (var i = 0; i < windowClients.length; i++) {
                var client = windowClients[i];
                // If so, just focus it.
                if (client.url === event.notification.data.url && 'focus' in client) {
                    return client.focus();
                }
            }
            // If not, then open the target URL in a new window/tab.
            if (clients.openWindow) {
                return clients.openWindow(event.notification.data.url);
            }
        })
    );
}
self.addEventListener("push", function (event) {
    const data = event.data.json();
    self.registration.showNotification(data.title, {
        body: data.content,
        icon: data.icon,
        badge: data.badge,
        data: { url: data.url },
        vibrate: [500,110,500,110,450,110,200,110,170,40,450,110,200,110,170,40,500],
        actions: [{ action: "open_url", title: "Voir le tournois" }],
    });
});
self.addEventListener(
    "notificationclick",
    function (event) {
        event.notification.close();
        focusOrOpenUrl(event, event.notification.data.url);
        switch (event.action) {
            case "open_url":
                focusOrOpenUrl(event, event.notification.data.url);  // Which we got from above
                break;
        }
    },
    false,
);