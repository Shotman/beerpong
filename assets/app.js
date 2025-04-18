import './init.js';

//3rd party libraries
import "jquery";
import $ from "jquery";
import 'bootstrap'
import * as bootstrap from 'bootstrap';
import _hyperscript from  "hyperscript.org";
window.jQuery = $
window.$ = $;
window.bootstrap = bootstrap;
import { Datepicker } from 'vanillajs-datepicker';
import fr from 'vanillajs-datepicker/locales/fr';
import htmx from 'htmx.org';
import Pushmatic from 'pushmatic';
window.Pushmatic = Pushmatic;
import * as bs5dialog from '@ymlluo/bs5dialog/dist/bs5dialog.js'
window.bs5dialog = bs5dialog
import 'bootstrap-table'
window.htmx = htmx
jQuery.noConflict();

bs5dialog.startup();
bs5dialog.setSystemLang("fr-FR");
_hyperscript.browserInit();

Object.assign(Datepicker.locales, fr);
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('input[type="text"].datepicker-input').forEach((input) => {
        new Datepicker(input,{
            language: 'fr',
            buttonClass: 'btn',
        });
    });
    const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]')
    const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl))
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

document.body.addEventListener("hx-showModal", function(evt){
    setTimeout(function(e){
        let modal
        modal = new Modal(e.detail.value)
        modal.show()
    },10,evt)
})

document.body.addEventListener("hx-hideModal", function(evt){
    let modal = document.querySelector(evt.detail.value)
    modal.querySelector(".btn-close").click()
})

const options = {
    userVisibleOnly: true,
    applicationServerKey: "BBijTi82POzrkVulgdLriplyFZb7j4HwMM2XEYOhM4T9vQasrHlT3Y7hm504Zbhk-3-R0bElrWMOjY3zyJFJkHA",
};

const registerNotification = async (context) => {
    const permission = await Pushmatic.requestPermission();
    if(permission === "granted") {
        Pushmatic.registerServiceWorker("/notificationSW.js")
            .then((registration) => Pushmatic.subscribeToPush(registration, options))
            .then(function (subscription) {
                const body = JSON.stringify({"sub": subscription, "context": context});
                window.localStorage.setItem("subNotificationSW"+context, body)
                fetch("/tournaments/registerWebPushSub", {
                    method: "POST", // *GET, POST, PUT, DELETE, etc.
                    mode: "cors", // no-cors, *cors, same-origin
                    cache: "no-cache", // *default, no-cache, reload, force-cache, only-if-cached
                    credentials: "same-origin", // include, *same-origin, omit
                    headers: {
                        "Content-Type": "application/json",
                    },
                    redirect: "follow", // manual, *follow, error
                    referrerPolicy: "no-referrer", // no-referrer, *no-referrer-when-downgrade, origin, origin-when-cross-origin, same-origin, strict-origin, strict-origin-when-cross-origin, unsafe-url
                    body: body, // le type utilisé pour le corps doit correspondre à l'en-tête "Content-Type"
                });
            })
            .catch(console.error);
    }
}

const unregisterNotification = (context) => {
    const body = window.localStorage.getItem("subNotificationSW"+context);
    fetch("/tournaments/unregisterWebPushSub", {
        method: "POST", // *GET, POST, PUT, DELETE, etc.
        mode: "cors", // no-cors, *cors, same-origin
        cache: "no-cache", // *default, no-cache, reload, force-cache, only-if-cached
        credentials: "same-origin", // include, *same-origin, omit
        headers: {
            "Content-Type": "application/json",
        },
        redirect: "follow", // manual, *follow, error
        referrerPolicy: "no-referrer", // no-referrer, *no-referrer-when-downgrade, origin, origin-when-cross-origin, same-origin, strict-origin, strict-origin-when-cross-origin, unsafe-url
        body: body, // le type utilisé pour le corps doit correspondre à l'en-tête "Content-Type"
    }).then((response) => {
        window.localStorage.removeItem("subNotificationSW"+context)
    })
    ;
}

const toggleRegisterNotification = (context) => {
    if(window.localStorage.getItem("subNotificationSW"+context) != null) {
        unregisterNotification(context)
    }
    else{
        registerNotification(context)
    }
}

// window.setupNotifications = registerNotification;
// window.unregisterNotification = unregisterNotification;
window.toggleRegisterNotification = toggleRegisterNotification;

//custom js imports
import './js/accordion.js';

//CSS imports
import "bootstrap/dist/css/bootstrap.min.css";
import "bootstrap-icons/font/bootstrap-icons.min.css";
import "vanillajs-datepicker/dist/css/datepicker-bs5.min.css";
import "@ymlluo/bs5dialog/dist/bs5dialog.css";
import "bootstrap-table/dist/bootstrap-table.min.css";
import './styles/app.css';
import {Modal} from "bootstrap";
import e from "vanillajs-datepicker/locales/fr";