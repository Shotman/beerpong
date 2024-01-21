import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ["collectionContainer"]

    static values = {
        index    : Number,
        prototype: String,
    }

    addTeamCollectionElement(event)
    {
        const item = document.createElement('li');
        item.classList.add(...['team-collection-item','col-md-6','col-lg-4']);
        item.innerHTML = this.prototypeValue.replace(/__name__/g, this.indexValue);
        const collection = this
        collection.collectionContainerTarget.appendChild(item);
        collection.indexValue++;
        item.addEventListener('click',function(event){
            if(event.target.classList.contains('stimulus-delete')){
                collection.indexValue--;
                item.remove();
            }
        },{once:true} )
        const header = document.createElement('p');
        header.classList.value = 'h3 lead';
        header.innerHTML = "Équipe ";
        item.prepend(header);
    }
}