self.addEventListener("push", function (event) {
    const data = event.data.json();
    console.log(data);
    self.registration.showNotification(data.title, {
        body: data.content,
        icon: 'data:image/svg+xml;base64,PHN2ZyB2aWV3Qm94PScwIDAgOTAgMTAwJyB4bWxucz0naHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmcnPgogIDxwYXRoIGQ9J002MiwwYzIsMTAtOSwyNC0yMCwyNGMtMy0xNCw5LTIyLDIwLTI0TTUsMzZjNS04LDEzLTEyLDIxLTEyYzcsMCwxMiw0LDE5LDRjNiwwLDEwLTQsMTktNGM2LDAsMTQsMywxOSwxMGMtMTYsNC0xNSwzNSwzLDM5Yy03LDE3LTE4LDI3LTI0LDI3Yy03LDAtOC01LTE3LTVjLTksMC0xMSw1LTE3LDVjLTctMS0xMy03LTE3LTEzYy05LTEwLTE1LTQwLTYtNTEnIGZpbGw9JyNBQUEnLz4KPC9zdmc+Cg==',
        vibrate: [200, 100, 200, 100, 200, 100, 200],
    });
});