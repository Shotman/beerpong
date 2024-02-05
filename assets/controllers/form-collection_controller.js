import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ["collectionContainer"]

    static values = {
        index    : Number,
        prototype: String,
    }
    connect() {
        let container = this.targets.element;
        container.addEventListener('click', function(event){
            console.log(event)
            if(event.target.classList.contains('stimulus-delete')){
                event.target.closest('.team-collection-item').remove();
            }
        });
    }

    addTeamCollectionElement(event)
    {
        const collection = this
        document.addEventListener("newTeamPlayerAdded", (event) => {
            let parser = new DOMParser();
            let prototypeHtmlDoc = parser.parseFromString(collection.prototypeValue, 'text/html');
            let selects = prototypeHtmlDoc.querySelectorAll("select")
            selects.forEach((select) => {
                let optionElement = document.createElement("option")
                optionElement.value = event.detail.playerName
                optionElement.innerText = event.detail.playerName
                select.add(optionElement)
            })
            collection.prototypeValue = prototypeHtmlDoc.querySelector("body > div").outerHTML
        });
        const item = document.createElement('div');
        item.classList.add('team-collection-item','shadow','p-3', 'col-md-5','col-lg-3', 'col-sm-10','bg-white');
        item.innerHTML = this.prototypeValue.replace(/__name__/g, this.indexValue);
        collection.indexValue++;
        item.addEventListener('click',function(event){
            if(event.target.classList.contains('stimulus-delete')){
                collection.indexValue--;
                item.remove();
            }
        })
        const header = document.createElement('p');
        header.classList.value = 'h3 bold';
        header.innerHTML = "Équipe ";
        item.prepend(header);
        collection.collectionContainerTarget.appendChild(item);
        item.scrollIntoView()
    }
}