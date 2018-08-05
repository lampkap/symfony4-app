/**
 * Keep label out of the input field when there is value for that input field
 */
let inputs = document.querySelectorAll('.form--input');

if(inputs) {

    inputs.forEach(input => {

        stickyLabel(input);

        input.addEventListener('change', () => {

            stickyLabel(input);
    
        })
    })

}

function stickyLabel(input) {
    if (input.value === '')
        input.nextElementSibling.classList.remove("sticky");
    else
        input.nextElementSibling.classList.add("sticky");
}

/**
 * Initialize datepicker
 */
let dateField = document.querySelector('.form--input__date');

if(dateField) {

    // disable custom input in field
    dateField.addEventListener('keypress', e => {
        e.preventDefault();
    }) 

    let picker = new Pikaday({ 
        field: dateField,
        format: 'DD-MM-YYYY',
        //format: 'YYYY-MM-DD',
        yearRange: [1900, moment().year()],
        i18n: {
            previousMonth : 'Vorige maand',
            nextMonth     : 'Volgende maand',
            months        : ['januari','februari','maart','april','mei','juni','juli','augustus','september','october','november','december'],
            weekdays      : ['zondag','maandag','dinsdag','woensdag','donderdag','vrijdag','zaterdag'],
            weekdaysShort : ['zon','maa','din','woe','don','vri','zat']
        }
    });

}

/**
 * Handle image select
 */
let gifts = document.querySelectorAll('.gift'),
    formField = document.getElementById('gift_gift');

if(gifts) {

    gifts.forEach(gift => {

        gift.addEventListener('click', () => {
            // remove all active classes
            removeActiveClassOn(gifts);
            // add active class
            gift.classList.add('active');
            // set data-id as form value
            formField.value = gift.getAttribute('data-id');
        })

    })

}

function removeActiveClassOn(elements) {
    elements.forEach(element => {
        element.classList.remove('active');
    })
}