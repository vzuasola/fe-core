/**
 * Generates a "fake" select dropdown on top of the real dropdown.
 * Allows custom styling and logic of the select dropdown that is not available
 * by the native dropdown
 *
 * To use, mark a field as type "custom_select" in the Drupal Form configuration
 * Then in the frontend import this file and run CustomSelect.
 * You only need to import and initialize once.
 * It will recognize and initialize all custom dropdowns automatically.
 *
 * Example
 * Drupal:
 *
 * 'field_key' => [
 *   'name' => 'Field Name',
 *   'type' => 'custom_select',
 * ],
 *
 * JS:
 * import CustomSelect from "Base/custom-select";
 * CustomSelect();
 */
export default function CustomSelect() {
    "use strict";

    function init() {

        var customSelects, selElmnt, selectedOptionMarkup, optionList, optionMarkup;
        /* Look for any elements with the class "custom-select": */
        customSelects = Array.from(document.getElementsByClassName("custom-select"));
        const eventDropdownLoaded = new Event("dropdownLoaded");
        const eventOptionChanged = new Event("change");

        customSelects.forEach(function (customSelect) {
            selElmnt = customSelect.getElementsByTagName("select")[0];

            /* For each element, create a new DIV that will act as the selected item: */
            selectedOptionMarkup = document.createElement("DIV");
            selectedOptionMarkup.setAttribute("class", "select-selected arrow down");

            // Pre-populate div with selected option content
            // This will be the placeholder on first load but it can also be
            // one of the other options if the user refreshed the page after selecting one
            var selectedVal = selElmnt.options[selElmnt.selectedIndex].innerHTML;
            selectedOptionMarkup.innerHTML = selectedVal;

            customSelect.appendChild(selectedOptionMarkup);

            /* Create a new DIV that will contain the option list: */
            optionList = document.createElement("DIV");
            optionList.setAttribute("class", "select-items select-hide");
            Array.from(selElmnt.options).forEach(function (option) {
                /* For each option in the original select element,
                    create a new DIV that will act as an option item: */
                optionMarkup = document.createElement("DIV");

                // Check if the current item is the one selected and add a class
                // This will allow to style/hide the currently selected item
                if (selectedOptionMarkup.innerHTML === option.innerHTML) {
                    optionMarkup.classList.add("currently-selected");
                }

                optionMarkup.innerHTML = option.innerHTML;

                // Handle click event logic
                optionMarkup.addEventListener("click", function (e) {
                    /* When an item is clicked, update the original select box,
                        and the selected item: */
                    selElmnt.selectedIndex = Array.from(selElmnt.options)
                        .map(function (el) {
                            return el.innerHTML;
                        })
                        .indexOf(this.innerHTML);
                    selectedOptionMarkup.innerHTML = this.innerHTML;

                    // Change the currently-selected item in the optionlist
                    optionList.querySelector(".currently-selected")
                        .classList
                        .remove('currently-selected');

                    this.classList.add('currently-selected');

                    // As this is not a real dropdown, change events are not triggered
                    // We fire a change event manually here to allow EventListeners to work
                    selElmnt.dispatchEvent(eventOptionChanged);
                });

                // Finally. we add the option to the custom list
                optionList.appendChild(optionMarkup);
            });

            // Add the list of options to the fake select box
            customSelect.appendChild(optionList);

            // Logic for handling multiple custom select boxes in a page
            selectedOptionMarkup.addEventListener("click", function (e) {
                /* When the select box is clicked, close any other select boxes,
                    and open/close the current select box: */
                e.stopPropagation();
                closeAllSelect(this);
                this.nextSibling.classList.toggle("select-hide");
                this.classList.toggle("select-active");
                this.classList.toggle("up");
                this.classList.toggle("down");
            });

            // Dispatch an event on dropdown load
            selElmnt.dispatchEvent(eventDropdownLoaded);
        });

        // Clicking anywhere on the page closes the open dropdown
        document.addEventListener("click", closeAllSelect);
    }

    function closeAllSelect(elmnt) {
    /* A function that will close all select boxes in the document,
          except the current select box (if the element triggering is a select box) */
        var allSelectedItems;

        allSelectedItems = document.getElementsByClassName("select-selected");

        Array.from(allSelectedItems)
            .filter(function (el) {
                return el !== elmnt;
            })
            .map(function (el) {
                el.classList.remove("up");
                el.classList.add("down");
                el.classList.remove("select-active");
                el.parentElement.querySelector('.select-items').classList.add("select-hide");
            });
    }

    init();
}
