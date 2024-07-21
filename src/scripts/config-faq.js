import { initializeConfigurationJS as ConfigJS } from './configuration.js';
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

ConfigJS(ConfigPage);
ConfigPage.removeEventListeners();
ConfigPage = null;
ConfigPage = {};

ConfigPage = {

    fetchSchedule: function (requestData) {
        let url = `src/includes/classes/config-election-sched-controller.php`;
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
                // process shedule data

            })
            .catch(function (error) {
                console.error('GET request error:', error);
            });
    },

    fetchData: function (requestData) {
        let url = `src/includes/classes/config-faq-controller.php`;
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

                const errorCodes = ConfigPage.extractErrorCodes(data);

                ConfigPage.setErrorDictionary(errorCodes);

                const voteGuidelines = ConfigPage.removeErrorCodes(data);



                const guidelineData = ConfigPage.processData(voteGuidelines);

                ConfigPage.TableHandler.setData(guidelineData, ConfigPage.table)
                // TABLE.rows.add(TABLE_DATA).draw(true);

            })
            .catch(function (error) {
                ConfigPage.table.draw(false);
                console.error('GET request error:', error);
            });
    },

    handleTableRowLongPress: function (event) {

        const hasFocusedInput = event.target.querySelectorAll('input:focus-visible').length > 0;

        if (hasFocusedInput) {

            return;
        }


        ConfigPage.showCandidatePositionDialog(event.target);
    },

    handleTableRowClick: function (event) {

        const INPUT_FOCUSED = event.currentTarget.querySelectorAll('input:focus-visible');
        if (INPUT_FOCUSED.length > 0) {
            return;
        }

        event.currentTarget.classList.toggle('selected');

        const SELECTED_COUNT = ConfigPage.TableHandler.countSelectedRows();

        ConfigPage.TableHandler.updateToolbarButton(SELECTED_COUNT);
    },

    handleTableRowDblClick: function (event) {
        // const INPUT_FOCUSED = event.currentTarget.querySelectorAll('input:focus-visible');

        try {
            const dataContainer = event.currentTarget.querySelector('div.vote-rule-text');
            const answerContainer = event.currentTarget.querySelector('.faq-answer');

            let data = {
                sequence: dataContainer.getAttribute('data-seq'),
                faq_id: dataContainer.id,
                description: dataContainer.textContent,
                answer: answerContainer.textContent
            }

            ConfigPage.EditorModal.show(data, false);

        } catch (error) {

        }

    },

    customValidation: {
        clear_invalid: true,
        trailing: {
            '-+': '-',    // Replace consecutive dashes with a single dash
            '\\.+': '.',  // Replace consecutive periods with a single period
            ' +': ' ',    // Replace consecutive spaces with a single space
            '(\\w)\\1{2,}': '$1$1' //remove trailing character if three consecutive /* strict dahil si kath ay bug daw */
        },
        attributes: {
            required: true,
            max_length: 500,
        },
        customMsg: {
            required: true,
            max_length: 'Question length limit reached.',
        },
        errorFeedback: {
            required: 'ERR_BLANK_QUERY',
            max_length: 'ERR_MAX_QUERY_LENGTH',
        }
    },

    descriptionLimit: 1000,

}

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


ConfigPage.isoDateConverter = function (date) {
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');

    return `${year}-${month}-${day}`;
}

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

Object.defineProperty(ConfigPage, 'CSRF_TOKEN', {
    value: setCSRFToken(),
    writable: false,
    enumerable: false,
    configurable: false
});

ConfigPage.vote_rule_validate = new InputValidator(ConfigPage.customValidation);

try {
    ConfigPage.fetchSchedule({ csrf: ConfigPage.CSRF_TOKEN });
} catch (error) {
    console.warn(error);
}



ConfigPage.processData = function (data) {
    const TABLE_DATA = [];
    // console.log('data')
    // console.log(data)
    try {
        for (const key in data) {
            const item = data[key];
            // console.log('process for loop key')
            // console.log(key)
            // console.log('process for loop item')
            // console.log(item)



            if (typeof item === 'object' && !Array.isArray(item)) {

                console.log(item);
                // let answer = (item.answer !== undefined && item.answer !== '') ? JSON.parse(item.answer) : '';
                let answer = JSON.stringify(item.answer);


                const tableItem = {
                    0: item.sequence,
                    1: {
                        data_id: item.faq_id || null,
                        sequence: item.sequence,
                        description: item.description,
                    },
                    2: { answer: answer }
                };
                TABLE_DATA.push(tableItem);
            }
        }
    } catch (error) {
        console.warn(error);
    }




    return TABLE_DATA;
}

ConfigPage.postData = function (post_data, method) {
    let url = 'src/includes/classes/config-faq-controller.php';
    post_data.push({ csrf_token: `${ConfigPage.CSRF_TOKEN}` });
    let json_data = JSON.stringify(post_data);
    return fetch(url, {
        method: method,
        body: json_data,
        headers: {
            'Content-Type': 'application/json'
        }
    })
        .then(function (response) {

            if (!response.ok) {
                return response.json().then(data => {
                    throw data;
                });
            }
            return response.json();
        })
        .then(function (response) {
            console.log('POST request successful:', response.data);
            let data = [];
            data = response.data;

            return { data, success: true };
        })
        .catch(function (error) {
            console.error('POST request error:', error);
            return { error, success: false };
        });
}


ConfigPage.extractErrorCodes = function (data) {
    if ("error_codes" in data) {
        const errorCodes = data.error_codes;
        return errorCodes;
    }

    return {};
}

ConfigPage.removeErrorCodes = function (data) {

    const cleanData = Object.assign({}, data);
    delete cleanData.error_codes;
    return cleanData;
}


try {
    ConfigPage.fetchData({ csrf: ConfigPage.CSRF_TOKEN });
} catch (error) {
    console.warn(error);
}

ConfigPage.setErrorDictionary = function (definitions) {
    ConfigPage.errorDictionary = definitions;
    console.log(ConfigPage.errorDictionary);
}


ConfigPage.touchStartHandler = function (callback) {
    return (event) => {
        clearTimeout(ConfigPage.longPressTimer);

        ConfigPage.longPressTimer = setTimeout(() => {
            ConfigPage.longPressTimer = null;
            callback(event);
        }, 600);
    };
}

ConfigPage.cancelTouch = () => {
    clearTimeout(ConfigPage.longPressTimer);
}

ConfigPage.onLongPress = function (element, callback) {
    const touchStart = ConfigPage.touchStartHandler(callback);

    // If there are existing longPressHandlers, remove them
    ConfigPage.delEventListener(element, 'touchstart');
    ConfigPage.delEventListener(element, 'touchend');
    ConfigPage.delEventListener(element, 'touchmove');

    // Add new longPressHandlers
    ConfigPage.addEventListenerAndStore(element, 'touchstart', touchStart);
    ConfigPage.addEventListenerAndStore(element, 'touchend', ConfigPage.cancelTouch);
    ConfigPage.addEventListenerAndStore(element, 'touchmove', ConfigPage.cancelTouch);

}

ConfigPage.longPressTimer = null;

ConfigPage.TableHandler = class {


    static addListener(table_id) {
        /**
         * Retrieves all table rows within the specified table.
         * @type {NodeListOf<HTMLTableRowElement>}
         */
        const TABLE_ROW = document.querySelectorAll(`#${table_id} tbody tr`);

        this.updateTableRowListeners(TABLE_ROW);


        // Add event listeners for touch events
        TABLE_ROW.forEach(row => {
            ConfigPage.onLongPress(row, (event) => {
                // ConfigPage.handleTableRowLongPress(event);
            });

        });
    }

    static updateTableRowListeners(tableRows) {

        tableRows.forEach(row => {
            ConfigPage.delEventListener(row, 'click');
            ConfigPage.delEventListener(row, 'dblclick');
        });

        tableRows.forEach(row => {
            ConfigPage.addEventListenerAndStore(row, 'click', ConfigPage.handleTableRowClick);
        });

        tableRows.forEach(row => {
            ConfigPage.addEventListenerAndStore(row, 'dblclick', ConfigPage.handleTableRowDblClick);
        });
    }

    static countSelectedRows() {
        const SELECTED_ROWS = ConfigPage.TABLE_BODY.querySelectorAll('tr.selected');
        return SELECTED_ROWS.length;
    }

    static updateToolbarButton(SELECTED_COUNT) {
        if (SELECTED_COUNT > 0) {
            ConfigPage.DELETE_BUTTON.setAttribute('data-selected', SELECTED_COUNT);

            if (ConfigPage.DELETE_BUTTON && ConfigPage.DELETE_LABEL) {
                this.handleDeleteLabel(false, SELECTED_COUNT);
                ConfigPage.delEventListener(ConfigPage.DELETE_BUTTON, 'click');
                ConfigPage.addEventListenerAndStore(ConfigPage.DELETE_BUTTON, 'click', this.handleDeleteBtn.bind(this));
            }
            ConfigPage.DELETE_BUTTON.disabled = false;
        } else {
            ConfigPage.DELETE_BUTTON.setAttribute('data-selected', '');

            if (ConfigPage.DELETE_BUTTON && ConfigPage.DELETE_LABEL) {
                this.handleDeleteLabel(true);
                ConfigPage.delEventListener(ConfigPage.DELETE_BUTTON, 'click');
            }
            ConfigPage.DELETE_BUTTON.disabled = true;
        }
    }

    static initReorderListener() {

        ConfigPage.table.on('row-reorder', function (e, diff, edit) {
            let data = [];
            // let result = 'Reorder started on row: ' +  + '<br>';
            for (var i = 0, ien = diff.length; i < ien; i++) {

                let row = $(diff[i].node).find('td .vote-rule-text');
                let answerContainer = $(diff[i].node).find('td .faq-answer');
                let sequence = diff[i].newData;
                let faq_id = row.attr('id');
                let description = row.text().trim();
                let answer = answerContainer.text();
                let extractedNumber;
                if (typeof faq_id === 'string' && faq_id.startsWith('rule-')) {
                    extractedNumber = parseInt(faq_id.substring(5), 10);
                }

                const NEW_DATA_SEQ = {
                    faq_id: extractedNumber,
                    sequence: sequence,
                    description: description,
                    answer: answer,
                };

                data.push(NEW_DATA_SEQ);
            }

            let isEmpty = Object.keys(data).length === 0 || data.length === 0;
            if (isEmpty) {
                return;
            }

            console.log(data);

            ConfigPage.postData(data, 'PATCH')
                .then(function (result) {
                    const { data, success, error } = result;
                    if (success) {
                        try {
                            const { data, success, error } = result;

                            if (success) {
                                ConfigPage.handleResponseStatus(200, data, 'FAQ updated successfuly.');
                                // let processedData = ConfigPage.processData(data);
                                // console.log(processedData)
                                // this.updateData(processedData);
                            } else if (error.data) {
                                // error.data.forEach(item => {


                                // });
                            }
                        } catch (e) {
                            console.error('POST request failed:', e);
                        }
                    } else {
                        console.error('POST request failed:', error);
                    }
                })

        });

        ConfigPage.table.on('draw', function () {
            if (ConfigPage.table.data().any()) {
                ConfigPage.TableHandler.addListener('config-table');
                $('div.toolbar').show();
                $('table#config-table').show();
                $(this).parent().show();
            } else {
                $('div.toolbar').hide();
                $('table#config-table').hide();
                $(this).parent().hide();
            }
            ConfigPage.TableHandler.deselectAll();
        });


    }

    static handleDeleteLabel(isDisabled, SELECTED_COUNT = 0) {

        let tooltip = bootstrap.Tooltip.getInstance("#delete-label");

        if (isDisabled) {
            tooltip._config.title = 'No item selected.';

        } else {
            if (SELECTED_COUNT > 1) {
                tooltip._config.title = `${SELECTED_COUNT} items selected.`;
            } else {
                tooltip._config.title = `${SELECTED_COUNT} item selected.`;
            }

        }
        tooltip.update();
    }

    static extractData(selected) {

        let data = [];
        selected.forEach((row) => {
            const dataContainer = row.querySelector('div.vote-rule-text');
            const answerContainer = row.querySelector('div.faq-answer');

            const faqId = dataContainer.id;
            let extractedNumber;

            if (typeof faqId === 'string' && faqId.startsWith('rule-')) {
                extractedNumber = parseInt(faqId.substring(5), 10);
            }

            let item = {
                sequence: dataContainer.getAttribute('data-seq'),
                faq_id: extractedNumber,
                description: dataContainer.textContent,
                answer: answerContainer.textContent
            }
            data.push(item);
        });

        return data;
    }

    static async handleDeleteBtn() {
        ConfigPage.DELETE_BUTTON.disabled = true;

        if (await
            ConfigPage.showConfirmModal(ConfigPage.ConfirmDeleteModal, ConfigPage.ConfirmModalInstance, 'confirmDeleteInput', 'Confirm Delete', true)
            == 'true') {
            const selectedData = document.querySelectorAll(`table tbody tr.selected`);
            const deleteData = this.extractData(selectedData);

            ConfigPage.postData(deleteData, 'DELETE')
                .then(function (result) {
                    try {
                        const { data, success, error } = result;

                        if (success) {
                            console.log(data)
                            let processedData = ConfigPage.processData(data);
                            console.log(processedData)
                            this.deleteEntry(processedData)
                                .then(() => {
                                    // ConfigPage.handleSucessResponse();

                                    ConfigPage.handleResponseStatus(200, data, 'FAQ deleted successfuly.');
                                })
                                .catch((error) => {
                                    console.error("Error inserting data:", error);
                                });

                        } else if (error.data) {
                            // error.data.forEach(item => {


                            // });
                        }
                    }
                    catch (e) {
                        console.error('POST request failed:', e);
                    }
                }.bind(this))
        }

    }

    static deleteEntry(DATA, isdraw = false) {

        return new Promise((resolve, reject) => {
            try {
                for (const item of DATA) {


                    let rowId = document.getElementById(`rule-${item[1].data_id}`);

                    let DATA_ROW = rowId.closest(`tr`);

                    if (DATA_ROW) {
                        ConfigPage.table.row(DATA_ROW).remove().draw(isdraw);
                    } else {

                        console.error(`Input element with ID not found.`);
                    }

                }

                resolve();
            } catch (error) {
                reject(error);
            }
        })




    }

    static deselectAll() {
        const selectedRows = ConfigPage.TABLE_BODY.querySelectorAll('tr.selected');
        selectedRows.forEach(row => {
            row.classList.remove('selected');
        });
        const SELECTED_COUNT = this.countSelectedRows();
        this.updateToolbarButton(SELECTED_COUNT);
    }


    static insertData(DATA, isdraw = false) {
        return new Promise((resolve, reject) => {
            try {
                for (const item of DATA) {

                    ConfigPage.table.row.add(item).draw(isdraw);

                }
                resolve();
            } catch (error) {
                reject(error);
            }
        })

    }

    static updateData(DATA, isdraw = false) {

        return new Promise((resolve, reject) => {
            try {
                for (const item of DATA) {

                    let rowId = document.getElementById(`rule-${item[1].data_id}`);

                    let DATA_ROW = rowId.closest(`tr`);

                    if (DATA_ROW) {

                        // ConfigPage.table.row(DATA_ROW).data(rowData).draw(false);
                        ConfigPage.table.row(DATA_ROW).data(item).draw(isdraw);
                    } else {

                        console.error(`Input element with ID not found.`);
                    }
                    resolve();
                }
            } catch (error) {
                reject(error);
            }
        })


    }

    static setData(TABLE_DATA, TABLE) {
        TABLE.clear();
        TABLE.rows.add(TABLE_DATA).draw(true);

    }
}

try {
    ConfigPage.table.destroy();
} catch (error) {
    console.warn(error);
}

ConfigPage.table = new DataTable('#config-table', {
    rowReorder: {
        cancelable: true,
        snapX: true,
    },
    columnDefs: [
        {
            targets: 0, className: `text-center col-1 grab`,
            render: function (data) {
                return `<span class="d-none">${data}</span>
            <span class="fas fa-grip-lines"></span>`;
            }
        },
        {
            targets: 1, className: ``,
            render: function (data) {
                return `
                    <div class="vote-rule-text" id="rule-${data.data_id}" data-seq="${data.sequence}">
                        ${data.description}
                    </div>
                `;
            }
        },

        {
            targets: 2, className: ``,
            render: function (data) {

                return `<div class="faq-answer d-none">${data.answer}</div>`;
            }
        }
    ],
    select: {
        style: 'multi',
        selector: 'row'
    },
    layout: {
        bottomStart: null,
        bottomEnd: null,
        topStart: null,
        topEnd: null,
        bottom: function () {
            let toolbar = document.createElement('div');
            toolbar.innerHTML = `<button class="add-new " id="add-new">
                                    Add New Question
                                </button>`;

            return toolbar;
        }
    },
    scrollY: '4.5rem ',
    scrollCollapse: true,
    paging: false,
    initComplete: function (settings, json) {
        ConfigPage.addEventListenerAndStore(document.getElementById('add-new'), 'click', function () {
            let lastSequence = ConfigPage.FindLastSequence();
            let data = {
                sequence: ++lastSequence,
                faq_id: `rule-${lastSequence}`,
                description: '',
                answer: '',
            }
            ConfigPage.EditorModal.show(data, false, true);
        })


    }
});

ConfigPage.ConfirmDeleteModal = document.getElementById('delete-modal');
ConfigPage.ConfirmModalInstance = { instance: null };

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
    ConfigPage.showModal(document.getElementById('success-modal'));
}

ConfigPage.updateSuccessSubtitle = function () {
    // ConfigPage.showModal(document.getElementById('success-modal'));
}

ConfigPage.handleModalDispose = function () {
    ConfigPage.modalInstance.instance.dispose();
}

ConfigPage.showConfirmModal = async function (modal, instanceRef, inputId = null, inputVal = null, isDisabled = false) {
    // https://stackoverflow.com/questions/65454144/javascript-await-bootstrap-modal-close-by-user
    instanceRef.instance = new bootstrap.Modal(modal);
    instanceRef.instance.show();

    if (isDisabled) {
        ConfigPage.handleConfirmInput(modal, inputId, inputVal);
    }

    modal.removeEventListener('hidden.bs.modal', ConfigPage.handleConfirmModalDispose)
    modal.addEventListener('hidden.bs.modal', ConfigPage.handleConfirmModalDispose)

    return new Promise(resolve => {

        $(modal).find('.prompt-action button').off('click');
        $(modal).find('.prompt-action button').on('click', (event) => {
            const buttonValue = event.currentTarget.value;


            if (isDisabled) {
                let inputElement = modal.querySelector(`#${inputId}`);
                inputElement.classList.remove('is-invalid');
                inputElement.value = '';
                $(modal).find('.prompt-action .btn.primary').prop('disabled', true)
                    .val('false')
            }

            instanceRef.instance.hide();
            resolve(buttonValue);
        });
    });
}

ConfigPage.handleConfirmModalDispose = function () {
    ConfigPage.ConfirmModalInstance.instance.dispose();
}

ConfigPage.handleConfirmInput = function (modal, inputId, inputVal) {
    let inputElement = modal.querySelector(`#${inputId}`);

    ConfigPage.delEventListener(inputElement, 'input');
    ConfigPage.addEventListenerAndStore(inputElement, 'input', function () {
        if (inputElement.value == inputVal) {
            $(modal).find('.prompt-action .btn.primary').prop('disabled', false)
                .val('true');
            inputElement.classList.remove('is-invalid');
        }
        else {
            $(modal).find('.prompt-action .btn.primary').prop('disabled', true)
                .val('false');
            inputElement.classList.add('is-invalid')
        }

    })

}

ConfigPage.toastContainer = document.querySelector('.toast-container');

ConfigPage.handleResponseStatus = function (statusCode, data, message = '') {
    if (statusCode >= 400) {
        // if (statusCode == 401) {
        ConfigPage.createToast(ConfigPage.errorDictionary[data.message] || data.message, 'danger');
    }
    else if (statusCode == 200) {
        ConfigPage.createToast(message, 'success');
    }
}

ConfigPage.createToast = function (message, type) {
    const toast = document.createElement('div');
    toast.classList.add('toast');

    const toastBody = document.createElement('div');
    toastBody.classList.add('toast-body', `text-bg-${type}`);
    const messageDiv = document.createElement('div');
    messageDiv.classList.add('toast-content');
    messageDiv.textContent = message;
    toastBody.prepend(messageDiv);


    const closeContainer = document.createElement('div');
    const closeButton = document.createElement('button');
    closeButton.classList.add('btn-close');
    closeButton.setAttribute('type', 'button');
    closeButton.setAttribute('data-bs-dismiss', 'toast');
    closeButton.setAttribute('aria-label', 'Close');

    closeContainer.appendChild(closeButton);
    toastBody.appendChild(closeContainer);
    toast.appendChild(toastBody);

    ConfigPage.toastContainer.appendChild(toast);

    toast.addEventListener('hidden.bs.toast', () => {
        toast.remove();
    });

    new bootstrap.Toast(toast).show();
}

ConfigPage.FindLastSequence = function (table_id = 'config-table') {
    try {
        const TABLE = document.querySelector(`table#${table_id}`);

        if (!TABLE) {
            throw new Error(`Table with id '${table_id}' not found.`);
        }

        const LAST_ROW = TABLE.querySelector('tbody tr:last-child');


        if (!LAST_ROW) {
            return 0;
            // throw new Error(`No last row found in table '${table_id}'.`);
        } else {
            console.log('last row ' + LAST_ROW.outerHTML);
        }

        const LAST_SEQUENCE_SPAN = LAST_ROW.querySelector('.dt-type-numeric > span.d-none:first-child');

        if (!LAST_SEQUENCE_SPAN) {
            return 0;
            // throw new Error(`No sequence span found in last row of table '${table_id}'.`);
        }

        let last_sequence = LAST_SEQUENCE_SPAN.textContent.trim();
        last_sequence = parseInt(last_sequence);

        if (isNaN(last_sequence)) {
            throw new Error(`Invalid sequence number found in table '${table_id}'.`);
        }

        return last_sequence;
    } catch (error) {
        console.error(`Error finding last sequence for table '${table_id}':`, error);
        // Optionally handle the error by returning a default sequence or rethrowing
        throw error; // Rethrow the error to propagate it to the caller
    }
}

ConfigPage.TABLE_BODY = document.querySelector(`#config-table tbody`);
ConfigPage.DELETE_BUTTON = document.getElementById('delete');
ConfigPage.DELETE_LABEL = document.getElementById('delete-label');
ConfigPage.TableHandler.initReorderListener();
ConfigPage.itemSequence;
ConfigPage.itemId;
ConfigPage.description;
ConfigPage.faqAnswer;

ConfigPage.inputFeedbackHandler = function (event, feedbackId) {
    console.log(event);
    console.log(feedbackId);
    console.log(ConfigPage.errorDictionary[feedbackId]);
    try {
        const inputElement = event;

        const feedbackField = inputElement.nextElementSibling;

        feedbackField.textContent = ConfigPage.errorDictionary[feedbackId];
    } catch (error) {

    }
}

ConfigPage.validateTextEditor = function (event) {

    clearTimeout(ConfigPage.typingTimeout);
    ConfigPage.typingTimeout = setTimeout(() => {
        try {
            let inputElement = event.target;
            console.log(!inputElement);
            if (!inputElement) {
                inputElement = document.getElementById('faq-question');
            }
            const primaryBtn = document.getElementById('modal-action-primary');

            const feedbackAlert = inputElement.nextElementSibling;

            if (ConfigPage.vote_rule_validate.validate(inputElement, ConfigPage.inputFeedbackHandler)) {
                inputElement.classList.remove('is-invalid');
                primaryBtn.disabled = false;
                if (feedbackAlert.classList.contains('feedback-alert')) {
                    feedbackAlert.innerHTML = "&nbsp;";
                }

            } else {
                inputElement.classList.add('is-invalid');
                primaryBtn.disabled = true;
            }
        } catch (error) {
            console.error('Validation error:', error);
        }
    }, 300);
}

ConfigPage.handleDescValidate = function (delta, old, source) {

    clearTimeout(ConfigPage.typingTimeout);
    ConfigPage.typingTimeout = setTimeout(() => {
        if (source == 'user' || source == 'save-btn') {

            const questionInput = document.getElementById('faq-question');

            if (!ConfigPage.vote_rule_validate.validate(questionInput, ConfigPage.inputFeedbackHandler)) {
                questionInput.classList.add('is-invalid');
            }

            let descriptionInput = document.querySelector(`.ql-editor`);
            let saveButton = document.querySelector(`#modal-action-primary`);

            let parentElement = descriptionInput.closest('.answer-container');

            let feedbackField;
            if (parentElement.nextElementSibling.classList.contains('feedback-alert')) {
                feedbackField = parentElement.nextElementSibling;
            }


            // console.log(saveButton);
            // console.log(descriptionInput);
            descriptionInput.classList.add('is-invalid', 'form-control');
            saveButton.disabled = true;


            if (ConfigPage.quill.getLength() > ConfigPage.descriptionLimit) {
                ConfigPage.quill.deleteText(ConfigPage.descriptionLimit, ConfigPage.quill.getLength());
                feedbackField.textContent = ConfigPage.errorDictionary['ERR_MAX_ANS_LENGTH'];
                return false;
            }

            // console.log(ConfigPage.quill.getLength());
            // console.log(ConfigPage.quill.getContents());

            let descriptionVal = ConfigPage.quill.getContents();
            // console.log("length ", descriptionVal.ops.length);


            const originalVal = descriptionVal.slice();
            let modifiedVal = originalVal.slice();

            if (descriptionVal.ops.length < 2) {
                modifiedVal.ops[0].insert = originalVal.ops[0].insert.replace(/(^\s+|\s+$)/g, '');
                if (!modifiedVal.ops[0].insert.endsWith('\n')) {
                    modifiedVal.ops[0].insert += '\n';
                }

                if (modifiedVal.ops[0].insert == "\n") {
                    ConfigPage.quill.setContents(modifiedVal);
                    feedbackField.textContent = ConfigPage.errorDictionary['ERR_BLANK_ANS'];
                    return false;
                }
            }

            console.log(originalVal);
            console.log(modifiedVal);

            console.log(modifiedVal.ops[0].insert);

            for (let i = 0; i < originalVal.ops.length; i++) {

                let originalText = originalVal.ops[i].insert;

                const regex = /(\w|\s)\1{2,}/g;
                const newText = originalText.replace(regex, '$1$1');


                if (newText != originalText) {
                    console.log("originalText");
                    console.log(originalText);
                    console.log("newText");
                    console.log(newText);

                    modifiedVal.ops[i].insert = newText;
                }
            }


            if (JSON.stringify(modifiedVal) !== JSON.stringify(originalVal)) {
                ConfigPage.quill.setContents(modifiedVal);
            }

            descriptionInput.classList.remove('is-invalid', 'form-control');
            feedbackField.innerHTML = "&nbsp;"

            if (!questionInput.classList.contains('is-invalid')) {
                saveButton.disabled = false;
            }
            return true;
        }

    }, 300);

}

ConfigPage.EditorModal = class {
    static modalElement = document.querySelector('.modal:has(.modal-header.editor)');
    static data;
    static mode;
    static modalInstance;

    static show(data, isEdit, isAdd = false) {
        this.isEdit = isEdit;
        this.isAdd = isAdd;
        this.mode = 'view';

        if (this.isEdit) {
            this.mode = 'edit';
        } else if (this.isAdd) {
            this.mode = 'add';
        }

        this.#updateContent(data);

        if (this.modalElement) {

            this.modalInstance = new bootstrap.Modal(this.modalElement);
            this.modalInstance.show();

            ConfigPage.delEventListener(this.modalElement, 'close');
            ConfigPage.addEventListenerAndStore(this.modalElement, 'close', ConfigPage.TableHandler.deselectAll());

            this.modalElement.removeEventListener('hidden.bs.modal', event => {
                this.modalInstance.dispose();
                ConfigPage.TableHandler.deselectAll();
            }).bind(this)
            this.modalElement.addEventListener('hidden.bs.modal', event => {
                this.modalInstance.dispose();
                ConfigPage.TableHandler.deselectAll();
            }).bind(this)
        }
    }

    static #updateContent(data) {
        this.data = data;

        this.#setEditField();
        let modalActionDiv = this.#initBtn();
        let modalBody = this.modalElement.querySelector('.modal-body');
        modalBody.append(modalActionDiv);

        if (this.mode === 'view') {
            let editBtn = this.modalElement.querySelector('#modal-action-edit');
            ConfigPage.delEventListener(editBtn, 'click');
            ConfigPage.addEventListenerAndStore(editBtn, 'click', this.#toggleEditState.bind(this));
        } else {
            this.#toggleEditState();
        }


        let primaryBtn = this.modalElement.querySelector('label[for="modal-action-primary"]');

        let cancelBtn = this.modalElement.querySelector('.modal-body .cancel-btn');

        if (this.mode === 'view') {
            primaryBtn.classList.add('d-none');
            cancelBtn.classList.add('d-none');
        }


        let closeBtn = this.modalElement.querySelector('button.modal-close');
        ConfigPage.delEventListener(closeBtn, 'click');
        ConfigPage.addEventListenerAndStore(closeBtn, 'click', this.close.bind(this));

        let textarea = this.modalElement.querySelector('textarea');
        ConfigPage.delEventListener(textarea, 'input');
        ConfigPage.addEventListenerAndStore(textarea, 'input', ConfigPage.validateTextEditor);

        ConfigPage.quill.off('text-change', ConfigPage.handleDescValidate);

        ConfigPage.quill.on('text-change', ConfigPage.handleDescValidate);

    }

    static #setEditField() {
        this.#removeEditor();
        let prompTitle = this.modalElement.querySelector('h5.modal-title span.guideline-num');
        let modalBody = this.modalElement.querySelector('.modal-body');

        prompTitle.textContent = this.data.sequence;

        let descriptionTextArea = document.createElement('textarea');
        descriptionTextArea.id = 'faq-question';
        descriptionTextArea.maxLength = 500;
        descriptionTextArea.classList.add('form-control', 'input-question', 'form-content');
        descriptionTextArea.required = true;
        const trimmedDescription = this.data.description.trim();

        let textareaAlert = document.createElement('span');
        textareaAlert.classList.add('feedback-alert', 'text-danger', 'form-content');


        let textareaLabel = document.createElement('label');
        textareaLabel.setAttribute('for', 'faq-question');
        textareaLabel.setAttribute('anchor', 'faq-question');
        textareaLabel.classList.add('label', 'form-content');

        let textareaLabelText = document.createElement('span');
        textareaLabelText.classList.add('label-text', 'form-content');
        textareaLabelText.textContent = 'Question';
        textareaLabel.prepend(textareaLabelText);

        let textareaLabelIcon = document.createElement('span');
        textareaLabelIcon.classList.add('label-icon', 'form-content');
        textareaLabelIcon.textContent = '?';
        textareaLabel.prepend(textareaLabelIcon);



        descriptionTextArea.value = trimmedDescription;
        if (this.mode === 'view') {
            descriptionTextArea.readOnly = true;
            // descriptionTextArea.disabled = true;
        }

        let answerContainer = document.createElement('div');
        answerContainer.classList.add('answer-container', 'form-content');
        let answerLabelContainer = document.createElement('div');
        answerLabelContainer.classList.add('label-container', 'form-content');


        let answerTextArea = document.createElement('div');
        answerTextArea.maxLength = 500;
        answerTextArea.id = "faq-answer";
        answerTextArea.classList.add('form-content');
        answerTextArea.required = true;

        let answerTextAreaLabel = document.createElement('label');
        answerTextAreaLabel.setAttribute('for', 'faq-answer');
        answerTextAreaLabel.classList.add('label', 'form-content');
        answerTextAreaLabel.textContent = 'Answer';

        let answerTextAreaLabelIcon = document.createElement('span');
        answerTextAreaLabelIcon.classList.add('label-icon', 'form-content');
        const svg = document.createElementNS("http://www.w3.org/2000/svg", "svg");
        svg.setAttribute("width", "13");
        svg.setAttribute("height", "13");
        svg.setAttribute("viewBox", "0 0 13 13");
        svg.setAttribute("fill", "none");

        const path = document.createElementNS("http://www.w3.org/2000/svg", "path");
        path.setAttribute("d", "M12.35 2.6H11.05V8.45H2.6V9.75C2.6 10.1075 2.8925 10.4 3.25 10.4H10.4L13 13V3.25C13 2.8925 12.7075 2.6 12.35 2.6ZM9.75 6.5V0.65C9.75 0.2925 9.4575 0 9.1 0H0.65C0.2925 0 0 0.2925 0 0.65V9.75L2.6 7.15H9.1C9.4575 7.15 9.75 6.8575 9.75 6.5Z");
        path.setAttribute("fill", "white");

        svg.appendChild(path); // Add path to the svg element

        answerTextAreaLabelIcon.prepend(svg);
        answerTextAreaLabel.prepend(answerTextAreaLabelIcon);

        let answerAlert = document.createElement('span');
        answerAlert.classList.add('feedback-alert', 'text-danger', 'form-content');
        answerAlert.classList.id = "answer-alert";

        ConfigPage.itemSequence = this.data.sequence;

        const faqId = this.data.faq_id;

        if (typeof faqId === 'string' && faqId.startsWith('rule-')) {
            const extractedNumber = parseInt(faqId.substring(5), 10);
            ConfigPage.itemId = extractedNumber;
        }


        answerLabelContainer.prepend(answerTextAreaLabel);
        answerContainer.prepend(answerLabelContainer);
        answerContainer.append(answerTextArea);
        modalBody.insertBefore(answerAlert, modalBody.firstChild);
        modalBody.insertBefore(answerContainer, modalBody.firstChild);

        modalBody.insertBefore(textareaAlert, modalBody.firstChild);
        modalBody.insertBefore(descriptionTextArea, modalBody.firstChild);
        modalBody.insertBefore(textareaLabel, modalBody.firstChild);

        ConfigPage.quill = new Quill('#faq-answer', {
            modules: {
                toolbar: false
            },
            placeholder: 'Type FAQ answer here.',
        });


        try {
            let answer = (this.data.answer !== undefined && this.data.answer !== '') ? JSON.parse(this.data.answer) : '';
            console.log(answer);

            ConfigPage.quill.setContents(answer);

            if (this.mode === 'view') {
                answerTextArea.querySelector('.ql-editor').setAttribute("contenteditable", false);
            }
        } catch (error) {
            console.error('FAQ answer has been corrupted.');
        }

    }

    static #initBtn() {

        this.#removeBtn();

        const modalActionDiv = document.createElement('div');
        modalActionDiv.classList.add('modal-action', 'w-100');

        // Create the label and primary button
        const label = document.createElement('label');
        label.setAttribute('for', 'modal-action-primary');

        let editBtn;
        if (this.mode === 'view') {
            editBtn = document.createElement('button');
            editBtn.id = 'modal-action-edit';
            editBtn.type = 'button';
            editBtn.classList.add('btn', 'btn-sm', 'btn-outline-primary');
            editBtn.textContent = 'Edit';
        }

        const primaryButton = document.createElement('button');
        primaryButton.id = 'modal-action-primary';
        primaryButton.type = 'button';
        primaryButton.disabled = true;
        primaryButton.classList.add('btn', 'btn-sm', 'btn-primary');
        primaryButton.textContent = 'Save Changes';

        if (this.mode === 'add') {
            primaryButton.textContent = 'Add FAQ';
        }

        label.appendChild(primaryButton);

        const cancelButton = document.createElement('button');
        cancelButton.type = 'button';
        cancelButton.classList.add('btn', 'btn-sm', 'btn-secondary', 'cancel-btn');
        cancelButton.textContent = 'Cancel';

        // Append everything to the main div
        if (this.mode === 'view') {
            modalActionDiv.appendChild(editBtn);
        }

        modalActionDiv.appendChild(cancelButton);
        modalActionDiv.appendChild(label);

        return modalActionDiv;
    }

    static #toggleEditState() {

        if (this.mode !== 'add') {
            let editBtn = this.modalElement.querySelector('#modal-action-edit');
            editBtn.classList.add('d-none');
        }

        let descriptionTextArea = this.modalElement.querySelector('.modal-body textarea');
        descriptionTextArea.readOnly = false;
        descriptionTextArea.disabled = false;

        let answerTextArea = this.modalElement.querySelector("#faq-answer");
        answerTextArea.querySelector('.ql-editor').setAttribute("contenteditable", true);

        let primaryBtn = this.modalElement.querySelector('label[for="modal-action-primary"]');
        primaryBtn.classList.remove('d-none');
        ConfigPage.delEventListener(primaryBtn, 'click');
        ConfigPage.addEventListenerAndStore(primaryBtn, 'click', this.#handlePrimaryBtn.bind(this));

        let cancelBtn = this.modalElement.querySelector('.modal-body .cancel-btn');
        cancelBtn.classList.remove('d-none');
        ConfigPage.delEventListener(cancelBtn, 'click');
        ConfigPage.addEventListenerAndStore(cancelBtn, 'click', this.close.bind(this));
    }

    static #removeBtn() {
        let modalBody = this.modalElement.querySelector('.modal-body');
        let existingModalAction = modalBody.querySelector('.modal-action');
        if (existingModalAction) {
            modalBody.removeChild(existingModalAction);
        }
    }

    static #removeEditor() {
        let modalBody = this.modalElement.querySelector('.modal-body');
        // let isExistTextEditor = modalBody.querySelector('textarea');
        // let isExistAnswerTextArea = this.modalElement.querySelector("#faq-answer");;
        try {
            let formElements = modalBody.querySelectorAll(".form-content");

            formElements.forEach(formElement => {
                formElement.remove();
            });
        } catch (error) {
            console.error("Element is already removed. ", error);
        }


        // if (isExistTextEditor) {
        //     modalBody.removeChild(isExistTextEditor);
        // }
        // if (isExistAnswerTextArea) {
        //     modalBody.removeChild(isExistAnswerTextArea);
        // }
    }

    static #handlePrimaryBtn() {
        if (this.data) {
            let primaryButton = this.modalElement.querySelector('.modal-body #modal-action-primary');

            const inputElement = document.getElementById('faq-question');
            const feedbackAlert = inputElement.nextElementSibling;

            if (ConfigPage.vote_rule_validate.validate(inputElement, ConfigPage.inputFeedbackHandler)) {
                inputElement.classList.remove('is-invalid');
                primaryButton.disabled = false;

                if (feedbackAlert.classList.contains('feedback-alert')) {
                    feedbackAlert.innerHTML = "&nbsp;";
                }


            } else {
                inputElement.classList.add('is-invalid');
                primaryButton.disabled = true;
                console.log('invalid q');
            }

            ConfigPage.handleDescValidate(null, null, 'save-btn');
            const answerInput = document.getElementById('faq-answer');

            if (answerInput.classList.contains('is-invalid')) {
                return;
            }

            if (primaryButton.disabled) {
                return;
            }

            primaryButton.disabled = true;
            this.#handleSubmit();
        }
    }

    static #handleSubmit() {
        if (this.data) {
            this.#extractData();

            let data = [];
            let item = {
                faq_id: ConfigPage.itemId,
                sequence: ConfigPage.itemSequence,
                description: ConfigPage.description,
                answer: ConfigPage.faqAnswer
            }

            data.push(item);

            let method = 'PUT';

            if (this.isAdd) {
                method = 'POST';
            }

            console.log(data);

            ConfigPage.postData(data, method).then(function (result) {

                try {
                    let { data, success, error } = result;
                    if (success) {
                        data = [data];

                        let processedData = ConfigPage.processData(data);
                        if (this.isAdd) {

                            ConfigPage.TableHandler.insertData(processedData)
                                .then(() => {
                                    // ConfigPage.handleSucessResponse();

                                    ConfigPage.handleResponseStatus(200, data, 'FAQ added successfuly.');
                                })
                                .catch((error) => {
                                    console.error("Error inserting data:", error);
                                });



                        } else {

                            ConfigPage.TableHandler.updateData(processedData)
                                .then(() => {
                                    // ConfigPage.handleSucessResponse();

                                    ConfigPage.handleResponseStatus(200, data, 'FAQ updated successfuly.');
                                })
                                .catch((error) => {
                                    console.error("Error inserting data:", error);
                                });
                        }




                        ConfigPage.EditorModal.close();
                    }
                }
                catch (e) {
                    console.error('', e);
                }
            }.bind(this));
        }
    }

    static toggleSaveBtn() {
        let textarea = this.modalElement.querySelector('.modal-body textarea');
        let hasInvalidTextarea = textarea?.classList.matches('.is-invalid') && textarea?.classList.matches('.form-control');
        let saveBtn = this.modalElement.getElementById('modal-action-primary');

        if (hasInvalidTextarea) {
            saveBtn.disabled = true;
        } else {
            saveBtn.disabled = false;
        }
    }

    static close() {
        if (this.data) {

            ConfigPage.itemSequence = null;
            ConfigPage.itemId = null;
            ConfigPage.description = null;
            ConfigPage.faqAnswer = null;
            this.modalInstance.hide();
        }
    }

    static #extractData() {
        let modalBody = this.modalElement.querySelector('.modal-body');
        let isExistTextEditor = modalBody.querySelector('textarea');
        let answer = ConfigPage.quill.getContents();
        if (isExistTextEditor) {
            ConfigPage.description = isExistTextEditor.value;
            ConfigPage.faqAnswer = answer;
        }

    }

}
