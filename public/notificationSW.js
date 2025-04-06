self.addEventListener("push", function (event) {
    const data = event.data.json();
    console.log(data);
    self.registration.showNotification(data.title, {
        body: data.content,
        icon: data.icon,
        vibrate: [500,110,500,110,450,110,200,110,170,40,450,110,200,110,170,40,500]
    });
});