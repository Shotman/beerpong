import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    initialize() {
        this._onPreConnect = this._onPreConnect.bind(this);
    }

    connect() {
        this.element.addEventListener('autocomplete:pre-connect', this._onPreConnect);
    }

    disconnect() {
        // You should always remove listeners when the controller is disconnected to avoid side-effects
        this.element.removeEventListener('autocomplete:pre-connect', this._onPreConnect);
    }

    _onPreConnect(event) {
        const element = event.target;
        event.detail.options.render.option_create = (data, escape) => {
            return '<div class="create">Ajouter <strong>' + escape(data.input) + '</strong>&hellip;</div>';
        };
        const otherSelects = document.querySelectorAll("select:not([name='" + element.name + "'])")
        event.detail.options.createFilter = (input) => {
            input = input.trim()
            return input;
        }
        event.detail.options.onOptionAdd = (value) => {
            const event = new CustomEvent("newTeamPlayerAdded",{detail: {"playerName": value}});
            otherSelects.forEach((select) => {
                select.add(new Option(value, value))
                select.tomselect.addOption({value: value, text: value})
            })
            document.dispatchEvent(event)

        }
        event.detail.options.onChange = (value) => {
            if(element.tomselect.wrapper.classList.contains("is-invalid")){
                element.tomselect.wrapper.classList.remove("is-invalid")
                element.parentNode.querySelectorAll(".alert").forEach((alert) => {
                    alert.remove()
                })
            }
            otherSelects.forEach((select) => {
                if(select.value === value && value !== "") {
                    let errorElement = document.createElement("div")
                    let closeElement = document.createElement("i")
                    closeElement.classList.add("bi","bi-x-circle",'float-end')
                    closeElement.setAttribute("data-bs-dismiss","alert")
                    closeElement.setAttribute("aria-label","Close")
                    errorElement.innerHTML = "<p class='small m-0 p-0' >Ce joueur est déjà présent dans une équipe.</p>"
                    errorElement.firstChild.appendChild(closeElement)
                    errorElement.addEventListener("click", function(e){
                        element.tomselect.wrapper.classList.remove("is-invalid")
                        errorElement.remove()
                    })
                    errorElement.setAttribute("style", "cursor:pointer;")
                    errorElement.classList.add("alert", "alert-danger","alert-dismissible","py-0",'px-2')
                    element.tomselect.wrapper.classList.add("is-invalid")
                    element.parentNode.appendChild(errorElement)
                    element.tomselect.clear(true)
                }
            })
        };
    }
}