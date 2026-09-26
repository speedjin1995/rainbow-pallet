function formatDate(date) {
    const day = ('0' + date.getDate()).slice(-2);
    const month = ('0' + (date.getMonth() + 1)).slice(-2);
    const year = date.getFullYear();
    const hours = ('0' + date.getHours()).slice(-2);
    const minutes = ('0' + date.getMinutes()).slice(-2);
    const seconds = ('0' + date.getSeconds()).slice(-2);
    const ampm = date.getHours() >= 12 ? 'PM' : 'AM';

    //return `${day}/${month}/${year} ${hours}:${minutes}:${seconds} ${ampm}`;
    return `${year}-${month}-${day}`;
}

function formatDate2(date) {
    const day = ('0' + date.getDate()).slice(-2);
    const month = ('0' + (date.getMonth() + 1)).slice(-2);
    const year = date.getFullYear();
    const hours = ('0' + date.getHours()).slice(-2);
    const minutes = ('0' + date.getMinutes()).slice(-2);
    const seconds = ('0' + date.getSeconds()).slice(-2);
    const ampm = date.getHours() >= 12 ? 'PM' : 'AM';

    //return `${day}/${month}/${year} ${hours}:${minutes}:${seconds} ${ampm}`;
    return `${day}-${month}-${year}`;
}

function formatDate3(date) {
    const day = ('0' + date.getDate()).slice(-2);
    const month = ('0' + (date.getMonth() + 1)).slice(-2);
    const year = date.getFullYear();
    const hours = ('0' + date.getHours()).slice(-2);
    const minutes = ('0' + date.getMinutes()).slice(-2);
    const seconds = ('0' + date.getSeconds()).slice(-2);
    const ampm = date.getHours() >= 12 ? 'PM' : 'AM';

    return `${day}/${month}/${year} ${hours}:${minutes}:${seconds} ${ampm}`;
}

// ─── Null-safe display helpers ──────────────────────────────────────────────
// Use these when building HTML from row data so null / undefined never shows as "null".

// Value as is, or "" when null / undefined (0 is kept)
function displayValue(value) {
    return value === null || value === undefined ? '' : value;
}

// Number with fixed decimals; "" when it isn't a number
function displayNumber(value, decimals) {
    var number = parseFloat(value);
    return isNaN(number) ? '' : number.toFixed(decimals);
}

// Value followed by a unit (e.g. displayUnit(row.balance, 'KG') -> "100 KG"); "" when empty
function displayUnit(value, unit) {
    return value === null || value === undefined || value === '' ? '' : value + ' ' + unit;
}

// KG weight shown in MT (optionally with fixed decimals); "" when missing
function displayWeightMT(value, decimals) {
    var weight = parseFloat(value);
    if (isNaN(weight)) {
        return '';
    }
    var mt = weight / 1000;
    return (decimals === undefined ? mt : mt.toFixed(decimals)) + ' MT';
}

// "code - name", or just the part that exists; "" when both are empty
function displayPair(code, name) {
    return [displayValue(code), displayValue(name)].filter(function (part) {
        return part !== '';
    }).join(' - ');
}

// DataTables: show "" instead of null in every table cell (no "Requested unknown parameter" warning)
if (window.jQuery) {
    jQuery(function ($) {
        if ($.fn.dataTable) {
            $.fn.dataTable.defaults.column.sDefaultContent = '';
        }
    });
}