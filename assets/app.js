import './init.js';

//3rd party libraries
import 'bootstrap/dist/js/bootstrap.bundle.min.js'
import * as bs5dialog from "@ymlluo/bs5dialog/dist/bs5dialog.js";
import { Datepicker } from 'vanillajs-datepicker';
import fr from 'vanillajs-datepicker/locales/fr';
import htmx from 'htmx.org';
import Pushmatic from 'pushmatic';
window.Pushmatic = Pushmatic;
window.bs5dialog = bs5dialog
window.htmx = htmx

bs5dialog.startup();
bs5dialog.i18n.setCurrentLang('fr-FR');
Object.assign(Datepicker.locales, fr);
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('input[type="text"].datepicker-input').forEach((input) => {
        new Datepicker(input,{
            language: 'fr',
            buttonClass: 'btn',
        });
    });
})

//htmx confirm override
document.addEventListener("htmx:confirm", function(e) {
    if (!e.target.hasAttribute('hx-confirm')) return;
    e.preventDefault()
    bs5dialog.confirm(`${e.detail.question}`, {
        type: e.target.dataset.type ?? 'danger',
        cancelable: true,
        btnOkText: 'Oui',
        btnCancelText: 'Non',
        onConfirm: () => {
            e.detail.issueRequest(true)
        }
    })
})



const options = {
    userVisibleOnly: true,
    applicationServerKey: "BBijTi82POzrkVulgdLriplyFZb7j4HwMM2XEYOhM4T9vQasrHlT3Y7hm504Zbhk-3-R0bElrWMOjY3zyJFJkHA",
};

const setupNotifications = () => {
    Pushmatic.registerServiceWorker("/notificationSW.js")
        .then((registration) => Pushmatic.subscribeToPush(registration, options))
        .then(function(subscription){
            fetch("/registerWebPushSub", {
                method: "POST", // *GET, POST, PUT, DELETE, etc.
                mode: "cors", // no-cors, *cors, same-origin
                cache: "no-cache", // *default, no-cache, reload, force-cache, only-if-cached
                credentials: "same-origin", // include, *same-origin, omit
                headers: {
                    "Content-Type": "application/json",
                },
                redirect: "follow", // manual, *follow, error
                referrerPolicy: "no-referrer", // no-referrer, *no-referrer-when-downgrade, origin, origin-when-cross-origin, same-origin, strict-origin, strict-origin-when-cross-origin, unsafe-url
                body: JSON.stringify(subscription), // le type utilisé pour le corps doit correspondre à l'en-tête "Content-Type"
            });
        })
        .catch(console.error);
}
window.setupNotifications = setupNotifications

//custom js imports
import './js/accordion.js';

//CSS imports
import "bootstrap/dist/css/bootstrap.min.css";
import "bootstrap-icons/font/bootstrap-icons.min.css";
import "vanillajs-datepicker/dist/css/datepicker-bs5.min.css";
import "@ymlluo/bs5dialog/dist/bs5dialog.css";
import './styles/app.css';