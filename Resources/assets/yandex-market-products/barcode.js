/*
 *  Copyright 2026.  Baks.dev <admin@baks.dev>
 *  
 *  Permission is hereby granted, free of charge, to any person obtaining a copy
 *  of this software and associated documentation files (the "Software"), to deal
 *  in the Software without restriction, including without limitation the rights
 *  to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 *  copies of the Software, and to permit persons to whom the Software is furnished
 *  to do so, subject to the following conditions:
 *  
 *  The above copyright notice and this permission notice shall be included in all
 *  copies or substantial portions of the Software.
 *  
 *  THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 *  IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 *  FITNESS FOR A PARTICULAR PURPOSE AND NON INFRINGEMENT. IN NO EVENT SHALL THE
 *  AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 *  LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 *  OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 *  THE SOFTWARE.
 *
 */


var main = document.getElementById("ya_market_barcode_form_main");

changeObjectOffers(main);

addProperty = document.getElementById("ya_market_barcode_form_addProperty");
if(addProperty)
{
    addProperty.addEventListener("click", addPropertyPrototype);
}


var addCustom = document.getElementById("ya_market_barcode_form_addCustom");
if(addCustom)
{
    addCustom.addEventListener("click", addCustomPrototype);
}


var $blockCollection = document.getElementById("collectionProperty");

/* Удалить при клике колекцию СЕКЦИЙ */
$blockCollection?.querySelector(".del-item-custom")?.addEventListener("click", function()
{

    this.closest(".item-collection-custom").remove();
    index = addCustom.dataset.index * 1;
    addCustom.dataset.index = (index - 1).toString();
});


/* Удалить при клике колекцию */
$blockCollection?.querySelector(".del-item-property")?.addEventListener("click", function()
{

    this.closest(".item-collection-property").remove();
    index = addProperty.dataset.index * 1;
    addProperty.dataset.index = (index - 1).toString();

});


function addCustomPrototype()
{
    let $blockCollection = document.getElementById("collectionProperty");

    /* Получить прототип коллекции  */
    let $addButtonCustom = this;

    let newForm = $addButtonCustom.dataset.prototype;
    let index = $addButtonCustom.dataset.index * 1;

    /* Заменить '__name__' в HTML-коде прототипа на
     вместо этого будет число, основанное на том, сколько коллекций */
    newForm = newForm.replace(/__custom__/g, index);

    /* Вставить новую коллекцию */
    let div = document.createElement("div");
    div.classList.add("item-collection-custom");
    div.classList.add("w-100");
    div.innerHTML = newForm;
    $blockCollection.append(div);

    /* Удалить при клике колекцию СЕКЦИЙ */
    div.querySelector(".del-item-custom").addEventListener("click", function()
    {

        this.closest(".item-collection-custom").remove();
        index = $addButtonCustom.dataset.index * 1;
        $addButtonCustom.dataset.index = (index - 1).toString();
    });


    div.querySelector("#ya_market_barcode_form_custom_" + index + "_sort").value = (index + 1);

    /* Увеличить data-index на 1 после вставки новой коллекции */
    $addButtonCustom.dataset.index = (index + 1).toString();

}


function addPropertyPrototype()
{

    let $blockCollection = document.getElementById("collectionProperty");

    /* Получить прототип коллекции  */
    let $addButtonProperty = this;

    let newForm = $addButtonProperty.dataset.prototype;
    let index = $addButtonProperty.dataset.index * 1;

    /* Заменить '__name__' в HTML-коде прототипа на
     вместо этого будет число, основанное на том, сколько коллекций */
    newForm = newForm.replace(/__property__/g, index);

    /* Вставить новую коллекцию */
    let div = document.createElement("div");
    div.classList.add("item-collection-property");
    div.classList.add("w-100");
    div.innerHTML = newForm;
    $blockCollection.append(div);

    
    /* Удалить при клике колекцию */
    div.querySelector(".del-item-property").addEventListener("click", function()
    {

        this.closest(".item-collection-property").remove();
        index = $addButtonProperty.dataset.index * 1;
        $addButtonProperty.dataset.index = (index - 1).toString();

    });


    div.querySelector("#ya_market_barcode_form_property_" + index + "_sort").value = (index + 1);

    let $offer = div.querySelector("#ya_market_barcode_form_property_" + index + "_offer");

    /* Заполнить список новыми элеиентами */
    $prototypeOffer = document.getElementById("ya_market_barcode_form_offer_prototype");
    Array.from($prototypeOffer.children).forEach((option) =>
    {
        $offer.appendChild(option.cloneNode(true));
    });


    /* Увеличить data-index на 1 после вставки новой коллекции */
    $addButtonProperty.dataset.index = (index + 1).toString();

    div.querySelectorAll("[data-select=\"select2\"]").forEach(function(item)
    {
        new NiceSelect(item, {searchable : true});
    });

}


/** Обновить список торговых предложений в SELECT */
function changeObjectOffers(main, index = 0)
{

    main.addEventListener("change", function()
    {

        let replaceId = "ya_market_barcode_form_offer_prototype";

        let replaceElement = document.getElementById(replaceId + "_select2");
        if(replaceElement)
        {
            replaceElement.classList.add("disabled");
        }

        /* Создать объект класса XMLHttpRequest */
        const requestModalName = new XMLHttpRequest();
        requestModalName.responseType = "document";

        /* Имя формы */
        let incomingForm = document.forms.ya_market_barcode_form;
        let formData = new FormData();

        let mainName = document.getElementById("ya_market_barcode_form_main");
        formData.append(mainName.getAttribute("name"), this.value);

        requestModalName.open(incomingForm.getAttribute("method"), incomingForm.getAttribute("action"), true);

        /* Получить ответ от сервера на запрос*/
        requestModalName.addEventListener("readystatechange", function()
        {
            /* request.readyState - возвращает текущее состояние объекта XHR(XMLHttpRequest) */
            if(requestModalName.readyState === 4 && requestModalName.status === 200)
            {

                let result = requestModalName.response.getElementById("ya_market_barcode_form_offer_prototype"); 

                /* Замена прототипа коллекции */
                document.getElementById(replaceId).replaceWith(result);

                /* Активировать кнопку Добавить коллелкцию */
                let addProperty = document.getElementById("ya_market_barcode_form_addProperty");
                addProperty.dataset.index = "0";
                addProperty.disabled = result.disabled;

                /* Сбросить список ранее заполненных коллекции */
                document.getElementById("collectionProperty").innerHTML = "";

            }

            return false;
        });

        requestModalName.send(formData);

    });
}