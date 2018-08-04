/**
 * Keep label out of the input field when there is value for that input field
 */
var inputs = document.querySelectorAll('.form--input');

inputs.forEach(input => {

    input.addEventListener('change', () => {

        if (input.value === '') 
            input.nextElementSibling.classList.remove("sticky");
        else 
            input.nextElementSibling.classList.add("sticky");

    })
})

/**
 * Initialize datepicker
 */

var dateField = document.querySelector('.form--input__date');

dateField.addEventListener('keypress', e => {
    e.preventDefault();
}) 

var picker = new Pikaday({ 
    field: dateField,
    format: 'D-MM-YYYY',
    yearRange: [1900, moment().year()],
    i18n: {
        previousMonth : 'Vorige maand',
        nextMonth     : 'Volgende maand',
        months        : ['januari','februari','maart','april','mei','juni','juli','augustus','september','october','november','december'],
        weekdays      : ['zondag','maandag','dinsdag','woensdag','donderdag','vrijdag','zaterdag'],
        weekdaysShort : ['zon','maa','din','woe','don','vri','zat']
    }
});