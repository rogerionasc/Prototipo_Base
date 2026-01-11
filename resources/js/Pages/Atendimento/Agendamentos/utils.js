

let eventGuid = 0;
// let todayStr = new Date().toISOString().replace(/T.*$/, '') // YYYY-MM-DD of today
var date = new Date();
var d = date.getDate();
var m = date.getMonth();
var y = date.getFullYear();
export const INITIAL_EVENTS = [];

export function createEventId() {
    return String(eventGuid++);
}

export const categories = [
    {
        name: 'Danger',
        value: 'bg-danger-subtle'
    },
    {
        name: 'Success',
        value: 'bg-success-subtle'
    },
    {
        name: 'Primary',
        value: 'bg-primary-subtle'
    },
    {
        name: 'Info',
        value: 'bg-info-subtle'
    },
    {
        name: 'Dark',
        value: 'bg-dark-subtle'
    },
    {
        name: 'Warning',
        value: 'bg-warning-subtle'
    },
];
