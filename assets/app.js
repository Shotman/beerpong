import './bootstrap.js';

//3rd party libraries
import 'bootstrap'
import bootbox from 'bootbox'
import { Datepicker } from 'vanillajs-datepicker';
import fr from 'vanillajs-datepicker/locales/fr';
import htmx from 'htmx.org';
import Message from "./js/prophet.min.js";
window.htmx = htmx
window.bootbox = bootbox
window.Message = Message

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
    e.preventDefault()
    bootbox.confirm({
        title: "Confirmation ?",
        message: `${e.detail.question}`,
        callback: function(r){
            if(r){
                e.detail.issueRequest(true)
            }
        }
    })
})

//team creation duplicate test
document.getElementById("team-tournament-list")
    .addEventListener("change", function(e) {
        if (e.target.localName === "select") {
            const element = e.target;
            const value = element.value;
            const elementName = element.attributes.getNamedItem("name").value
            const prophetContainer = document.querySelector(".prophet")
            document.querySelectorAll("select:not([name='" + elementName + "'])").forEach((select) => {
                if(select.value === value){
                    prophetContainer.setAttribute("style","z-index:999999;margin-left:0;top:"+element.getBoundingClientRect().y+"px;left:"+element.getBoundingClientRect().y+"px")
                    element.tomselect.clear(true)
                    new Message("Ce joueur fait déjà partie d'une équipe", {type:"error",duration:4000}, function(){
                        setTimeout(function(){
                            prophetContainer.removeAttribute("style")
                        },500)
                    }).show()
                }
            })
        }
    })

//CSS imports
import "bootstrap/dist/css/bootstrap.min.css";
import "vanillajs-datepicker/dist/css/datepicker-bs5.min.css";
import "./styles/prophet.min.css"
import './styles/app.css';