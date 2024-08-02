export function initializeConfigurationJS(ConfigPage = null) {

    let tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    let tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));
    const toastElList = document.querySelectorAll('.toast');
    const toastList = [...toastElList].map(toastEl => new bootstrap.Toast(toastEl));
    // toastList.forEach(toast => toast.show());

    // try {
    //     SortableTiles[0].destroy()
    // } catch (error) {
    //     console.warn(error);
    // }

    try {
        const secondaryNav = document.querySelector('.secondary-nav-container ul.nav');

        function scrollToActiveLink() {
            const activeLink = secondaryNav.querySelector('.nav-link.active');
            console.log(activeLink);
            if (!activeLink) return;

            const linkRect = activeLink.getBoundingClientRect();
            const containerRect = secondaryNav.getBoundingClientRect();

            if (linkRect.left < containerRect.left || linkRect.right > containerRect.right) {
                secondaryNav.scrollTo({
                    left: linkRect.left - containerRect.left,
                    behavior: 'smooth'
                });
            }
        }

        if (secondaryNav) {
            scrollToActiveLink();
        }
    } catch (error) {

    }
}

export function shortFnv1a(input) {
    let hash = 2166136261; // FNV offset basis
    for (let i = 0; i < input.length; i++) {
        hash ^= input.charCodeAt(i);
        hash *= 16777619; // FNV prime
    }
    const hexString = (hash >>> 0).toString(16); // Convert to unsigned 32-bit integer and then to hexadecimal string
    return ('0000' + hexString).slice(-4); // Ensure output is 4 characters long
}

export class EventListenerUtils {
    /**
     * Removes all event listeners stored in the provided Map.
     * It iterates over the Map and removes each event listener using removeEventListener(),
     * and then clears the Map.
     * @function
     * @name removeEventListeners
     * @param {Map} eventListenersMap - The Map containing event listeners to be removed.
     */

    static clearEventListeners(eventListenersMap) {
        if (eventListenersMap && eventListenersMap instanceof Map && eventListenersMap.size > 0) {
            eventListenersMap.forEach((listener, element) => {
                element.removeEventListener(listener.event, listener.handler);
            });

            eventListenersMap.clear();
        }
    }

    /**
     * Adds an event listener to the specified element and stores it in the provided Map.
     * @function
     * @name addEventListenerAndStore
     * @param {Element} element - The DOM element to which the event listener is added.
     * @param {string} event - The name of the event to listen for.
     * @param {function} handler - The function to be executed when the event is triggered.
     * @param {Map} eventListenersMap - The Map in which the event listener will be stored.
     */
    static addEventListenerAndStore(element, event, handler, eventListenersMap) {
        element.addEventListener(event, handler);
        const key = `${element}-${event}`;
        eventListenersMap.set(key, handler);
    }



    /**
     * Removes the event listener associated with the specified element and deletes its entry from the provided Map.
     * @function
     * @name removeEventListenerAndDelete
     * @param {Element} element - The DOM element from which the event listener is removed.
     * @param {string} event - The name of the event for which the listener is to be removed.
     * @param {Map} eventListenersMap - The Map from which the event listener will be removed.
     */
    static removeEventListenerAndDelete(element, event, eventListenersMap) {
        const key = `${element}-${event}`;
        if (eventListenersMap.has(key)) {
            const listener = eventListenersMap.get(key);
            element.removeEventListener(listener.event, listener.handler);
            eventListenersMap.delete(key);
        }
    }

}

export function isScheduleOngoing(datetimeEnd) {
    const scheduleEnd = new Date(datetimeEnd);
    const now = new Date();

    return scheduleEnd >= now;
}

export class IsScheduleDone {
    constructor(dateTime) {
        this.dateTime = new Date(dateTime);
    }

    check() {
        const now = new Date();
        return this.dateTime >= now;
    }

}

export function reqNotificationPermission() {
    return new Promise((resolve, reject) => {
        if (Notification.permission === 'granted') {
            resolve('granted');
        } else if (Notification.permission !== 'denied') {
            Notification.requestPermission().then(permission => {
                resolve(permission);
            }, error => {
                reject(error);
            });
        } else {
            reject('Permission denied');
        }
    });
}

export function setNotificationPermCookie() {
    const expirationDate = new Date();
    expirationDate.setMonth(expirationDate.getMonth() + 1);
    document.cookie = `notification_granted=true; expires=${expirationDate.toUTCString()}; path=/`;
}

export function setNotificationCookie(cookieName, expirationDate) {
    console.log(cookieName);
    console.log(expirationDate);
    document.cookie = `${cookieName}=true; expires=${expirationDate.toUTCString()}; path=/`;
}

export function checkNotificationCookie(cookieName) {
    console.log(cookieName);
    const cookies = document.cookie.split(';');
    for (const cookie of cookies) {
        const [name, value] = cookie.trim().split('=');
        if (name == cookieName) {
            return true; // Cookie exists
        }
    }
    return false;
}

export class NotificationManager {
    constructor(dateTime, callback) {
        this.dateTime = new Date(dateTime);
        this.callback = callback;
    }

    push() {
        const NOW = new Date();

        if (NOW >= this.dateTime) {
            this.callback(this.dateTime);
        } else {

            const tomorrowAtMidnight = new Date(NOW);
            tomorrowAtMidnight.setDate(tomorrowAtMidnight.getDate() + 1);
            tomorrowAtMidnight.setHours(0, 0, 0, 0);

            const TODAY = new Date();
            TODAY.setHours(0, 0, 0, 0);
            // console.log(tomorrowAtMidnight);
            // console.log(NOW);
            // console.log(TODAY);


            if ((this.dateTime >= TODAY && this.dateTime < tomorrowAtMidnight) || this.dateTime == tomorrowAtMidnight) {
                console.log("Notification callback not executed!");
                setTimeout(() => {
                    this.push();
                }, 600);
            }

        }
    }

    update(dateTime) {
        this.dateTime = new Date(dateTime);
    }
}

export function createToast(message, type, toastContainer) {
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

    toastContainer.appendChild(toast);

    toast.addEventListener('hidden.bs.toast', () => {
        toast.remove();
    });

    new bootstrap.Toast(toast).show();
}

export function registrationNotifHandler(notifId, toastContainer, isStart) {
    let message = isStart ? "Registration period has started." : "Registration period has ended.";

    const expirationDate = new Date(notifId);
    expirationDate.setYear(expirationDate.getFullYear() + 1);

    notifId = notifId.toISOString();
    let pushId = `reg-${notifId}`;

    if (checkNotificationCookie(pushId)) {
        return;
    }

    if (document.visibilityState === "hidden") {
        new Notification("Registration", {
            body: message,
            icon: "src/images/resc/ivote-icon.webp",
            tag: pushId
        });
    } else {
        createToast(message, 'info', toastContainer);
    }

    setNotificationCookie(pushId, expirationDate);

    return pushId;
}

export function electionNotifHandler(notifId, toastContainer, isStart) {
    let message = isStart ? "Election period has started." : "Election period has ended.";
    console.log(toastContainer);

    const expirationDate = new Date(notifId);
    expirationDate.setYear(expirationDate.getFullYear() + 1);

    notifId = notifId.toISOString();
    let pushId = `election-${notifId}`;

    if (checkNotificationCookie(pushId)) {
        return;
    }

    if (document.visibilityState === "hidden") {
        new Notification("Election", {
            body: message,
            icon: "src/images/resc/ivote-icon.webp",
            tag: pushId
        });
    } else {
        createToast(message, 'info', toastContainer);
    }

    setNotificationCookie(pushId, expirationDate);

    return pushId;
}


export class ElectionSchedule {
    constructor(csrfToken, toastContainer) {
        this.csrfToken = csrfToken;
        this.toastContainer = toastContainer;
        console.log(this.toastContainer);
    };

    fetch() {
        let url = `src/includes/classes/config-election-sched-controller.php`;
        const queryParams = new URLSearchParams(this.csrfToken);
        url = `${url}?${queryParams.toString()}`;

        fetch(url)
            .then(function (response) {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(function (data) {
                console.log('GET request successful:', data);
                this.setFetchedElectionSchedule(data);

            }.bind(this))
            .catch(function (error) {
                console.error('GET request error:', error);
            });
    };



    electionNotifHandlerStart(notifId) {
        let pushId = electionNotifHandler(notifId, this.toastContainer, true);
    };

    electionNotifHandlerEnd(notifId) {
        let pushId = electionNotifHandler(notifId, this.toastContainer, false);

    };

    setFetchedElectionSchedule(data) {
        const schedule = new IsScheduleDone(data[0].electionEnd);
        const isDone = schedule.check();

        if (data[0] && isDone) {

            if (!this.electionStartNotif) {
                this.electionStartNotif = new NotificationManager(data[0].electionStart, this.electionNotifHandlerStart.bind(this));
                this.electionStartNotif.push();
            } else {
                this.electionStartNotif.update(data[0].electionStart);
            }

            if (!this.electionEndNotif) {
                this.electionEndNotif = new NotificationManager(data[0].electionEnd, this.electionNotifHandlerEnd.bind(this));
                this.electionEndNotif.push();
            } else {

                this.electionEndNotif.update(data[0].electionEnd);
            }
        }
    }


}

export class RegistrationSchedule {
    constructor(csrfToken, toastContainer) {
        this.csrfToken = csrfToken;
        this.toastContainer = toastContainer;
    };

    fetch() {
        let url = `src/includes/classes/config-registration-sched-controller.php`;
        const queryParams = new URLSearchParams(this.csrfToken);
        url = `${url}?${queryParams.toString()}`;

        fetch(url)
            .then(function (response) {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(function (data) {
                console.log('GET request successful:', data);
                this.setFetchedRegistrationSchedule(data);

            }.bind(this))
            .catch(function (error) {
                console.error('GET request error:', error);
            });
    };



    registrationNotifHandlerStart(notifId) {
        let pushId = registrationNotifHandler(notifId, this.toastContainer, true);
    };

    registrationNotifHandlerEnd(notifId) {
        let pushId = registrationNotifHandler(notifId, this.toastContainer, false);
    };

    setFetchedRegistrationSchedule(data) {
        const schedule = new IsScheduleDone(data[0].registrationEnd);
        const isDone = schedule.check();

        if (data[0] && isDone) {

            if (!this.registrationStartNotif) {
                this.registrationStartNotif = new NotificationManager(data[0].registrationStart, this.registrationNotifHandlerStart.bind(this));
                this.registrationStartNotif.push();
            } else {
                this.registrationStartNotif.update(data[0].registrationStart);
            }

            if (!this.registrationEndNotif) {
                this.registrationEndNotif = new NotificationManager(data[0].registrationEnd, this.registrationNotifHandlerEnd.bind(this));
                this.registrationEndNotif.push();
            } else {

                this.registrationEndNotif.update(data[0].registrationEnd);
            }
        }
    }

}