import {
    initializeConfigurationJS as ConfigJS,
    EventListenerUtils as EventUtils,
    IsScheduleDone,
    NotificationManager,
    ElectionSchedule,
    registrationNotifHandler,
    electionNotifHandler,
    checkNotificationCookie,
    setNotificationCookie,
    createToast,

} from './configuration.js';
import InputValidator from './input-validator.js';

/**
 * The ConfigPage object holds variables classes and function of the current page.
 * If ConfigPage is already defined, it retains its current value; otherwise, it is initialized as an empty object.
 * It will be reset to empty when another configuration script is added and executed.
 * @type {object}
 */
var ConfigPage = ConfigPage || {};

/**
 * Removes all event listeners stored in ConfigPage.eventListeners Map, if any.
 * It iterates over the Map and removes each event listener using removeEventListener(),
 * and then clears the Map.
 * @function
 * @name ConfigPage.removeEventListeners
 * @memberof ConfigPage
 */
ConfigPage.removeEventListeners = function () {
    if (ConfigPage.eventListeners && ConfigPage.eventListeners instanceof Map && ConfigPage.eventListeners.size > 0) {
        ConfigPage.eventListeners.forEach((listener, element) => {
            element.removeEventListener(listener.event, listener.handler);
        });

        ConfigPage.eventListeners.clear();
    }
};

ConfigPage.removeEventListeners();
ConfigPage = null;
ConfigPage = {};

/**
 * A Map that stores event listeners associated with elements.
 * This used to avoid duplicate event listeners.
 * @type {Map<Element, { event: string, handler: function }>}
 */
ConfigPage.eventListeners = new Map();

/**
 * Adds an event listener to the specified element and stores it in the ConfigPage.eventListeners Map.
 * @function
 * @name ConfigPage.addEventListenerAndStore
 * @memberof ConfigPage
 * @param {Element} element - The DOM element to which the event listener is added.
 * @param {string} event - The name of the event to listen for.
 * @param {function} handler - The function to be executed when the event is triggered.
 */
ConfigPage.addEventListenerAndStore = function (element, event, handler) {
    element.addEventListener(event, handler);
    const key = `${element}-${event}`;
    ConfigPage.eventListeners.set(key, handler);
}

/**
 * Removes the event listener associated with the specified element and deletes its entry from the ConfigPage.eventListeners Map.
 * @function
 * @name ConfigPage.delEventListener
 * @memberof ConfigPage
 * @param {Element} element - The DOM element from which the event listener is removed.
 */
ConfigPage.delEventListener = function (element, event) {
    const key = `${element}-${event}`;
    if (ConfigPage.eventListeners.has(key)) {
        const handler = ConfigPage.eventListeners.get(key);
        element.removeEventListener(event, handler);
        ConfigPage.eventListeners.delete(key);
    }
}

ConfigPage.allDayContainerClick = function (event) {
    if (event.target === ConfigPage.allDayContainer) {

        ConfigPage.toggleAllDayBtn.click();
        event.stopPropagation();
    }
};

ConfigPage.handleToggleAllDay = function (event) {
    if (event) {
        ConfigPage.isScheduleChanged = true;
    }
    let isToggled = ConfigPage.toggleAllDayBtn.checked;
    let startTimeContainer = document.querySelector('#datetime-start .time-group');
    let endTimeContainer = document.querySelector('#datetime-end .time-group');

    let startDateContainer = document.querySelector('#datetime-start .date-group');
    let endDateContainer = document.querySelector('#datetime-end .date-group');

    if (isToggled) {
        ConfigPage.timePickerStart.classList.remove('is-invalid');
        ConfigPage.timePickerEnd.classList.remove('is-invalid');

        ConfigPage.toggleEndDateTime();
        let datetimeStartFeedback = document.getElementById('datetime-start').nextElementSibling;
        let datetimeEndFeedback = document.getElementById('datetime-end').nextElementSibling;
        if (datetimeStartFeedback.textContent.toLowerCase().includes('start time is required.')) {
            datetimeStartFeedback.innerHTML = "&nbsp;";
        }

        if (datetimeEndFeedback.textContent.toLowerCase().includes('end time is required.')) {
            datetimeEndFeedback.innerHTML = "&nbsp;";
        }


        ConfigPage.timePickerStart.setAttribute('data-value', ConfigPage.timePickerStart.value);

        startTimeContainer.style.display = 'none';
        ConfigPage.timePickerStart.value = '00:00';
        ConfigPage.timePickerStart.min = '00:00';

        startDateContainer.classList.remove('col-6');
        startDateContainer.classList.add('col-12');


        ConfigPage.timePickerEnd.setAttribute('data-value', ConfigPage.timePickerEnd.value);

        endTimeContainer.style.display = 'none';
        ConfigPage.timePickerEnd.value = '23:59';
        ConfigPage.timePickerEnd.min = '23:59';

        endDateContainer.classList.remove('col-6');
        endDateContainer.classList.add('col-12');
    } else {
        startTimeContainer.style.display = '';
        endTimeContainer.style.display = '';

        startDateContainer.classList.remove('col-12');
        startDateContainer.classList.add('col-6');

        endDateContainer.classList.remove('col-12');
        endDateContainer.classList.add('col-6');
        ConfigPage.resetDatetime(false);
    }

    if (ConfigPage.isScheduleChanged) {
        ConfigPage.submitBtn.disabled = false;
    }
};

// Make the date time act like constant
Object.defineProperty(ConfigPage, 'NOW', {
    value: JS_DATE_TZ(),
    writable: false,
    enumerable: true,
    configurable: false
});

Object.defineProperty(ConfigPage, 'TODAY', {
    get: function () {
        const today = new Date(ConfigPage.NOW);
        today.setHours(0, 0, 0, 0);
        let isoToday = ConfigPage.isoDateConverter(today);
        return isoToday;
    },
    enumerable: true,
    configurable: false,
});

Object.defineProperty(ConfigPage, 'FIVE_YEARS_AHEAD', {
    get: function () {
        const futureDate = new Date(ConfigPage.NOW);
        futureDate.setFullYear(futureDate.getFullYear() + 5);
        futureDate.setMonth(futureDate.getMonth() + 1, 0);
        futureDate.setHours(23, 59, 59, 999);
        return futureDate;
    },
    enumerable: true,
    configurable: false,
});

Object.defineProperty(ConfigPage, 'DATE_REGEX', {
    get: function () {
        let regex = new RegExp(`^[0-9]+$`);
        let regexString = regex.toString().slice(1, -1);
        return regexString;
    },
    enumerable: true,
    configurable: false,
});

ConfigPage.dateGroupStart = document.getElementById(`datetime-start`);
ConfigPage.datePickerStart = ConfigPage.dateGroupStart.querySelector(`input[type="date"]`);
ConfigPage.timePickerStart = ConfigPage.dateGroupStart.querySelector(`input[type="time"]`);
ConfigPage.dateGroupEnd = document.getElementById(`datetime-end`);
ConfigPage.datePickerEnd = ConfigPage.dateGroupEnd.querySelector(`input[type="date"]`);
ConfigPage.timePickerEnd = ConfigPage.dateGroupEnd.querySelector(`input[type="time"]`);

ConfigPage.resetDatetime = function (date = true, time = true) {

    if (date) {
        let startDateValue = ConfigPage.datePickerStart.getAttribute('data-value');
        let endDateValue = ConfigPage.datePickerEnd.getAttribute('data-value');

        ConfigPage.datePickerStart.value = startDateValue;
        ConfigPage.datePickerStart.min = ConfigPage.TODAY;
        ConfigPage.datePickerEnd.value = endDateValue;
        ConfigPage.datePickerEnd.min = ConfigPage.TODAY;
    }

    if (time) {
        let schedSettings = document.querySelector(`.schedule.card-box`);

        let startTimeValue = ConfigPage.timePickerStart.getAttribute('data-value');
        let endTimeValue = ConfigPage.timePickerEnd.getAttribute('data-value');

        ConfigPage.timePickerStart.value = startTimeValue;
        ConfigPage.timePickerStart.min = '';
        ConfigPage.timePickerEnd.value = endTimeValue;
        ConfigPage.timePickerEnd.min = '';

    }
}

ConfigPage.allDayContainer = document.querySelector('.all-day');
ConfigPage.toggleAllDayBtn = document.getElementById('all-day-input');
ConfigPage.addEventListenerAndStore(ConfigPage.toggleAllDayBtn, 'click', ConfigPage.handleToggleAllDay);

ConfigPage.dateGroupStart = document.getElementById(`datetime-start`);
ConfigPage.datePickerStart = ConfigPage.dateGroupStart.querySelector(`input[type="date"]`);
ConfigPage.timePickerStart = ConfigPage.dateGroupStart.querySelector(`input[type="time"]`);
ConfigPage.dateGroupEnd = document.getElementById(`datetime-end`);
ConfigPage.datePickerEnd = ConfigPage.dateGroupEnd.querySelector(`input[type="date"]`);
ConfigPage.timePickerEnd = ConfigPage.dateGroupEnd.querySelector(`input[type="time"]`);
ConfigPage.datetimePickers = document.querySelectorAll('.schedule-group input');

ConfigPage.addEventListenerAndStore(ConfigPage.allDayContainer, 'click', ConfigPage.allDayContainerClick);

for (const dateTimePicker of ConfigPage.datetimePickers) {

    ConfigPage.addEventListenerAndStore(dateTimePicker, 'click', function () {
        try {
            this.showPicker();
        } catch (error) {
            // Use external library when this fails.
        }
    });

}

ConfigPage.fetchData = function (requestData) {
    let url = `src/includes/classes/config-registration-sched-controller.php`;
    const queryParams = new URLSearchParams(requestData);
    url = `${url}?${queryParams.toString()}`;

    fetch(url)
        .then(function (response) {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(function (data) {

            ConfigPage.setErrorDictionary(data.error_codes);

            ConfigPage.setFetchedSchedule(data);

        })
        .catch(function (error) {
            // console.error('GET request error:', error);
        });
};

ConfigPage.isoDateConverter = function (date) {
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');

    return `${year}-${month}-${day}`;
}


ConfigPage.registrationNotifHandlerStart = function (notifId) {
    let pushId = registrationNotifHandler(notifId, ConfigPage.toastContainer, true);
};

ConfigPage.registrationNotifHandlerEnd = function (notifId) {
    let pushId = registrationNotifHandler(notifId, ConfigPage.toastContainer, false);

};

ConfigPage.setFetchedSchedule = function (data, isUTC = false) {
    ConfigPage.datePickerStart.setAttribute('min', ConfigPage.TODAY);
    ConfigPage.datePickerEnd.setAttribute('min', ConfigPage.TODAY);

    let scheduleSettings = document.querySelector(`.schedule.card-box`);

    let schedule;
    let isDone;

    try {
        schedule = new IsScheduleDone(data[0].registrationEnd);
        isDone = schedule.check();
    } catch (error) {

    }

    if (data[0] && isDone) {

        if (!this.registrationStartNotif) {
            this.registrationStartNotif = new NotificationManager(data[0].registrationStart, ConfigPage.registrationNotifHandlerStart);
            this.registrationStartNotif.push();
        } else {
            this.registrationStartNotif.update(data[0].registrationStart);
        }

        if (!this.registrationEndNotif) {
            this.registrationEndNotif = new NotificationManager(data[0].registrationEnd, ConfigPage.registrationNotifHandlerEnd);
            this.registrationEndNotif.push();
        } else {

            this.registrationEndNotif.update(data[0].registrationEnd);
        }

        const { startDateTime, endDateTime, startDate, startTime, endDate, endTime } = ConfigPage.processDateTime(data, isUTC);

        if ((startDateTime[1] == '00:00' || startDateTime[1] == '00:00:00') && (endDateTime[1] === '23:59' || endDateTime[1] === '23:59:00')) {
            ConfigPage.toggleAllDayBtn.checked = true;
        } else {
            ConfigPage.toggleAllDayBtn.checked = false;
        }
        ConfigPage.toggleAllDayBtn.setAttribute('data-value', ConfigPage.toggleAllDayBtn.checked);


        ConfigPage.datePickerStart.value = startDate;
        ConfigPage.timePickerStart.value = startTime;
        ConfigPage.datePickerEnd.value = endDate;
        ConfigPage.timePickerEnd.value = endTime;

        ConfigPage.datePickerStart.setAttribute('data-value', startDate);
        ConfigPage.timePickerStart.setAttribute('data-value', startTime);
        ConfigPage.datePickerEnd.setAttribute('data-value', endDate);
        ConfigPage.timePickerEnd.setAttribute('data-value', endTime);

        ConfigPage.handleToggleAllDay();

        scheduleSettings.setAttribute('data-state', 'view');
        ConfigPage.datePickerStart.setAttribute('readOnly', true);
        ConfigPage.timePickerStart.setAttribute('readOnly', true);
        ConfigPage.datePickerEnd.setAttribute('readOnly', true);
        ConfigPage.timePickerEnd.setAttribute('readOnly', true);

        let editScheduleBtn = document.getElementById(`edit-schedule`);
        ConfigPage.delEventListener(editScheduleBtn, 'click');
        ConfigPage.addEventListenerAndStore(editScheduleBtn, 'click', ConfigPage.toggleEditState);

        ConfigPage.setDateTimeInfo(startDate, startTime, endDate, endTime);


    } else {
        scheduleSettings.setAttribute('data-state', 'set');

    }
}



ConfigPage.processDateTime = function (data, isUTC = false) {
    let startDateTime;
    let endDateTime;

    if (isUTC) {
        startDateTime = data[0].registrationStart.split('T');
        endDateTime = data[0].registrationEnd.split('T');
    } else {
        startDateTime = data[0].registrationStart.split(' ');
        endDateTime = data[0].registrationEnd.split(' ');
    }

    let startDate = startDateTime[0]; // "2024-06-10"
    let startTime = startDateTime[1]; // "12:12:00"
    let endDate = endDateTime[0]; // "2024-06-30"
    let endTime = endDateTime[1]; // "12:09:00"

    try {
        startTime = startTime.substring(0, 5); // "12:12"
        endTime = endTime.substring(0, 5);   // "12:09"
    } catch (error) {
        // console.warn(error);
    }

    return { startDateTime, endDateTime, startDate, startTime, endDate, endTime };
}

ConfigPage.formatDate = function (dateString) {
    const date = new Date(dateString);
    const month = date.getMonth(); // Months are zero-indexed
    const day = date.getDate();
    const year = date.getFullYear();

    return new Intl.DateTimeFormat('en-US', {
        month: 'long',  // Long month name
        day: 'numeric',  // Day with padding (01)
        year: 'numeric', // Year
    }).format(date);
}

ConfigPage.setDateTimeInfo = function (startDate, startTime, endDate, endTime) {
    let schedInfoContainer = document.querySelector(`.shedule-date span.data`);
    let startDateInfo = schedInfoContainer.querySelector(`date.start`);
    let startTimeInfo = schedInfoContainer.querySelector(`time.start`);
    let endDateInfo = schedInfoContainer.querySelector(`date.end`);
    let endTimeInfo = schedInfoContainer.querySelector(`time.end`);

    startDateInfo.textContent = ConfigPage.formatDate(startDate); //2024-07-02
    startTimeInfo.textContent = ConfigPage.formatTime(startTime); // 00:00
    endDateInfo.textContent = ConfigPage.formatDate(endDate); //2024-07-16
    endTimeInfo.textContent = ConfigPage.formatTime(endTime); //23:59
}

ConfigPage.formatTime = function (time) {
    // Check correct time format and split into components
    time = time.toString().match(/^([01]\d|2[0-3])(:)([0-5]\d)(:[0-5]\d)?$/) || [time];
    if (time.length > 1) {
        // If time format is correct, adjust hours and add AM/PM
        time = time.slice(1);
        time[5] = +time[0] < 12 ? ' AM' : ' PM';
        time[0] = +time[0] % 12 || 12;
    }
    return time.join('');
}


ConfigPage.toggleEditState = function () {

    let schedSettings = document.querySelector(`.schedule.card-box`);
    let schedSettingsState = schedSettings.getAttribute('data-state');

    if (schedSettingsState == 'view') {
        schedSettings.setAttribute('data-state', 'edit');
        ConfigPage.datePickerStart.removeAttribute('readonly');
        ConfigPage.timePickerStart.removeAttribute('readonly');
        ConfigPage.datePickerEnd.removeAttribute('readonly');
        ConfigPage.timePickerEnd.removeAttribute('readonly');

        let saveBtn = document.getElementById(`submit-schedule`)
        saveBtn.textContent = "Save Changes";
    } else {

        if (schedSettingsState == 'set') {
            return;
        }

        schedSettings.setAttribute('data-state', 'view');
        ConfigPage.datePickerStart.setAttribute('readOnly', true);
        ConfigPage.timePickerStart.setAttribute('readOnly', true);
        ConfigPage.datePickerEnd.setAttribute('readOnly', true);
        ConfigPage.timePickerEnd.setAttribute('readOnly', true);
    }

}



ConfigPage.postData = function (post_data) {
    let url = 'src/includes/classes/config-registration-sched-controller.php';
    let method = 'PUT';
    post_data.csrf_token = `${ConfigPage.CSRF_TOKEN}`;
    let json_data = JSON.stringify(post_data);

    return fetch(url, {
        method: method,
        body: json_data,
        headers: {
            'Content-Type': 'application/json'
        }
    })
        .then(async function (response) {
            if (!response.ok) {
                let data = await response.json();
                throw { response, data };
            }
            return Promise.all([response.clone(), response.json()]);
        })
        .then(async ([response, data]) => {
            // sucess

            ConfigPage.handleSucessResponse();

            try {
                const originalObject = data.data;
                const iterableArray = [originalObject];
                ConfigPage.setFetchedSchedule(iterableArray, true);

            } catch (error) {
                // console.error(error);
            }

            return { data, success: true };
        })
        .catch(function (error) {
            ConfigPage.handleResponseStatus(error.response.status, error.data);
            return { data: error.data, success: false };
        });
};

ConfigPage.handleResponseStatus = function (statusCode, data) {
    if (statusCode >= 400) {
        // if (statusCode == 401) {
        createToast(ConfigPage.errorDictionary[data.message] || data.message, 'danger', ConfigPage.toastContainer);
    }
}

ConfigPage.CurrentModal = { html: null };
ConfigPage.CurrModalInstance = { instance: null };

ConfigPage.showModal = function (modal) {
    ConfigPage.CurrentModal.html = modal;
    ConfigPage.CurrModalInstance.instance = new bootstrap.Modal(modal);
    ConfigPage.CurrModalInstance.instance.show();

    ConfigPage.CurrentModal.html.removeEventListener('hidden.bs.modal', ConfigPage.handleModalDispose)
    ConfigPage.CurrentModal.html.addEventListener('hidden.bs.modal', ConfigPage.handleModalDispose)
}

ConfigPage.handleSucessResponse = function () {
    try {
        ConfigPage.showModal(document.getElementById('success-modal'));
    } catch (error) {
        // console.error(error);
    }
}

ConfigPage.handleModalDispose = function () {
    ConfigPage.CurrModalInstance.instance.dispose();
}


ConfigJS();

Object.defineProperty(ConfigPage, 'CSRF_TOKEN', {
    value: setCSRFToken(),
    writable: false,
    enumerable: false,
    configurable: false
});

ConfigPage.fetchData({ csrf: ConfigPage.CSRF_TOKEN });

ConfigPage.toastContainer = document.querySelector('.toast-container-unstacked');
let electionWatcher = new ElectionSchedule({ csrf: ConfigPage.CSRF_TOKEN }, ConfigPage.toastContainer);
electionWatcher.fetch();

ConfigPage.startDateValidation = {
    clear_invalid: false,
    attributes: {
        type: 'date',
        // pattern: ConfigPage.DATE_REGEX,
        required: true,
        min: ConfigPage.TODAY,
        max: ConfigPage.FIVE_YEARS_AHEAD.toISOString().split('T')[0],
    },
    customMsg: {
        // pattern: 'Only date in numbers are allowed.',
        required: true,
        min: 'Date cannot be past',
        max: '',
    },
    errorFeedback: {
        required: 'ERR_MISSING_START_DATE',
        min: 'ERR_START_DATE_EXCEEDS_LIMIT',
        max: 'ERR_START_DATE_EXCEEDS_LIMIT',
    }
}


ConfigPage.endDateValidation = {
    clear_invalid: false,
    attributes: {
        type: 'date',
        // pattern: ConfigPage.DATE_REGEX,
        required: true,
        min: ConfigPage.TODAY,
        max: ConfigPage.FIVE_YEARS_AHEAD.toISOString().split('T')[0],
    },
    customMsg: {
        // pattern: 'Only date in numbers are allowed.',
        required: true,
        min: 'Date cannot be past',
        max: '',
    },
    errorFeedback: {
        required: 'ERR_MISSING_END_DATE',
        min: 'ERR_END_DATE_EXCEEDS_LIMIT',
        max: 'ERR_END_DATE_EXCEEDS_LIMIT',
    }
}

ConfigPage.startTimeValidation = {
    clear_invalid: false,
    attributes: {
        type: 'time',
        // pattern: ConfigPage.DATE_REGEX,
        required: true,
    },
    customMsg: {
        // pattern: 'Only date in numbers are allowed.',
        required: true,
    },
    errorFeedback: {
        required: 'ERR_MISSING_START_TIME',
    }
}


ConfigPage.endTimeValidation = {
    clear_invalid: false,
    attributes: {
        type: 'time',
        // pattern: ConfigPage.DATE_REGEX,
        required: true,
    },
    customMsg: {
        // pattern: 'Only date in numbers are allowed.',
        required: true,
    },
    errorFeedback: {
        required: 'ERR_MISSING_END_TIME',
    }
}


ConfigPage.startDateValidator = new InputValidator(ConfigPage.startDateValidation);
ConfigPage.endDateValidator = new InputValidator(ConfigPage.endDateValidation);
ConfigPage.startTimeValidator = new InputValidator(ConfigPage.startTimeValidation);
ConfigPage.endTimeValidator = new InputValidator(ConfigPage.endTimeValidation);



ConfigPage.typingTimeout;

ConfigPage.getDatetimeInput = function (dateGroup) {
    let datePicker = dateGroup.querySelector(`input[type="date"]`);
    let timePicker = dateGroup.querySelector(`input[type="time"]`);

    let dateValue = datePicker.value;
    let timeValue = timePicker.value;

    // Combine the date and time into a single string
    let dateTimeString = `${dateValue}T${timeValue}`;
    const utcDateString = dateTimeString.toLocaleString('en-US', { timeZone: 'UTC' });
    return utcDateString;
}



ConfigPage.inputFeedbackHandler = function (event, feedbackId) {
    try {
        const inputElement = event;
        const parentElement = inputElement.closest('.datetime');

        const feedbackField = parentElement.nextElementSibling;

        feedbackField.textContent = ConfigPage.errorDictionary[feedbackId];
    } catch (error) {

    }
}

ConfigPage.setErrorDictionary = function (definitions) {
    ConfigPage.errorDictionary = definitions;
}

ConfigPage.handleValidation = function (inputElement, validatorObj, isInput = true) {
    let parentElement = inputElement.closest('.datetime');

    let feedbackField = parentElement.nextElementSibling;

    if (validatorObj.validate(inputElement, ConfigPage.inputFeedbackHandler)) {
        if (isInput === true) {
            inputElement.classList.remove('is-invalid');
            feedbackField.textContent = "\u00A0";
        }
    } else {
        inputElement.classList.add('is-invalid');
    }

    if (parentElement && parentElement.id === 'datetime-start') {
        ConfigPage.toggleEndDateTime();
    }

    ConfigPage.toggleSaveBtn();
}

ConfigPage.handleInput = function (event, validatorObj) {
    ConfigPage.isScheduleChanged = true;
    const inputElement = event.target;

    clearTimeout(ConfigPage.typingTimeout);
    ConfigPage.typingTimeout = setTimeout(() => {
        try {

            ConfigPage.handleValidation(inputElement, validatorObj);
        } catch (error) {
            // console.error('Validation error:', error);
        }
    }, 400);
}



ConfigPage.toggleEndDateTime = function () {

    const hasInvalidStart = ConfigPage.dateGroupStart.querySelector('.is-invalid')?.matches('.form-control');
    let isAllDay = ConfigPage.toggleAllDayBtn.checked;

    if (hasInvalidStart) {
        ConfigPage.datePickerEnd.value = '';
        if (!isAllDay) {
            ConfigPage.timePickerEnd.value = '';
        }

        ConfigPage.datePickerEnd.disabled = true;
        ConfigPage.timePickerEnd.disabled = true;

    } else {
        ConfigPage.datePickerEnd.disabled = false;
        ConfigPage.timePickerEnd.disabled = false;
    }
};

ConfigPage.toggleSaveBtn = function () {
    const hasInvalidStart = ConfigPage.dateGroupStart.querySelector('.is-invalid')?.matches('.form-control');
    const hasInvalidEnd = ConfigPage.dateGroupEnd.querySelector('.is-invalid')?.matches('.form-control');

    if (hasInvalidStart || hasInvalidEnd) {

        ConfigPage.submitBtn.disabled = true;

    } else {

        ConfigPage.submitBtn.disabled = false;
    }
};


ConfigPage.addEventListenerAndStore(ConfigPage.datePickerStart, 'input', (event) => ConfigPage.handleInput(event, ConfigPage.startDateValidator));
ConfigPage.addEventListenerAndStore(ConfigPage.datePickerEnd, 'input', (event) => ConfigPage.handleInput(event, ConfigPage.endDateValidator));
ConfigPage.addEventListenerAndStore(ConfigPage.timePickerStart, 'input', (event) => ConfigPage.handleInput(event, ConfigPage.startTimeValidator));
ConfigPage.addEventListenerAndStore(ConfigPage.timePickerEnd, 'input', (event) => ConfigPage.handleInput(event, ConfigPage.endTimeValidator));

ConfigPage.submitBtn = document.querySelector('section.schedule .action-btn #submit-schedule');
ConfigPage.submitBtn.disabled = true;
ConfigPage.cancelBtn = document.querySelector('section.schedule .action-btn #cancel-schedule');
ConfigPage.editBtn = document.querySelector('section.schedule .action-btn #edit-schedule');

ConfigPage.handleSetSchedule = function () {
    ConfigPage.handleValidation(ConfigPage.datePickerStart, ConfigPage.startDateValidator, false);
    ConfigPage.handleValidation(ConfigPage.datePickerEnd, ConfigPage.endDateValidator, false);
    ConfigPage.handleValidation(ConfigPage.timePickerStart, ConfigPage.startTimeValidator, false);
    ConfigPage.handleValidation(ConfigPage.timePickerEnd, ConfigPage.endTimeValidator, false);

    let schedule = {
        registrationStart: ConfigPage.getDatetimeInput(ConfigPage.dateGroupStart),
        registrationEnd: ConfigPage.getDatetimeInput(ConfigPage.dateGroupEnd),
    }

    ConfigPage.postData(schedule);
}

ConfigPage.warningModal = new bootstrap.Modal(document.getElementById('warning-modal'));

ConfigPage.isScheduleChanged = false;

ConfigPage.handleDiscardSchedule = async function () {
    let schedSettings = document.querySelector(`.schedule.card-box`);

    if (ConfigPage.isScheduleChanged && ConfigPage.checkUnsaveSchedule) {

        if (await ConfigPage.showDiscardModal() == 'true') {
            ConfigPage.resetDatetime();
            ConfigPage.isScheduleChanged = false;
            ConfigPage.toggleAllDayBtn.checked = (ConfigPage.toggleAllDayBtn.getAttribute('data-value') == 'true');
            ConfigPage.handleToggleAllDay();

            ConfigPage.toggleEditState();

        }
    }
    else {
        ConfigPage.toggleEditState();
    }
}

ConfigPage.checkUnsaveSchedule = function () {
    let isStartDateChange = ConfigPage.datePickerStart.value == ConfigPage.datePickerStart.getAttribute('data-value');
    let isEndDateChange = ConfigPage.datePickerStart.value == ConfigPage.datePickerEnd.getAttribute('data-value');
    let isStartTimeChange = ConfigPage.datePickerStart.value == ConfigPage.timePickerStart.getAttribute('data-value');
    let isEndTimeChange = ConfigPage.datePickerStart.value == ConfigPage.timePickerEnd.getAttribute('data-value');
    let conditions = [isStartDateChange, isEndDateChange, isStartTimeChange, isEndTimeChange];

    return conditions.every(condition => condition === true);
}

ConfigPage.showDiscardModal = async function () {
    // https://stackoverflow.com/questions/65454144/javascript-await-bootstrap-modal-close-by-user
    ConfigPage.warningModal.show();

    return new Promise(resolve => {
        $('.prompt-action button').off('click');
        $('.prompt-action button').on('click', (event) => {
            const buttonValue = event.currentTarget.value;

            ConfigPage.warningModal.hide();
            resolve(buttonValue);
        });
    });
}


ConfigPage.addEventListenerAndStore(ConfigPage.submitBtn, 'click', ConfigPage.handleSetSchedule);
ConfigPage.addEventListenerAndStore(ConfigPage.cancelBtn, 'click', ConfigPage.handleDiscardSchedule);

