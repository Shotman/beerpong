import './bootstrap.js';

//3rd party libraries
import 'bootstrap'
import { alert, confirm, toast, prompt, message, load, i18n } from "@ymlluo/bs5dialog/dist/bs5dialog.js";
import { Datepicker } from 'vanillajs-datepicker';
import fr from 'vanillajs-datepicker/locales/fr';
import htmx from 'htmx.org';
window.htmx = htmx
window.bsAlert = alert;
window.bsConfirm = confirm;
window.bsToast = toast;
window.bsPrompt = prompt;
window.bsMessage = message;
window.bsLoad = load;
i18n.setCurrentLang('fr');

//Global datepicker handler
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
    console.log(e);
    bsConfirm(`${e.detail.question}`, {
        type: 'danger',
        cancelable: true,
        btnOkText: 'Oui',
        btnCancelText: 'Non',
        onOk: () => {
            e.detail.issueRequest()
        }
    })
})

//custom js imports
import './js/accordion.js';

//CSS imports
import "bootstrap/dist/css/bootstrap.min.css";
import "bootstrap-icons/font/bootstrap-icons.min.css";
import "vanillajs-datepicker/dist/css/datepicker-bs5.min.css";
import "@ymlluo/bs5dialog/dist/bs5dialog.css";
import './styles/app.css';