window._meter = window._meter || [];
window._meterConfig = Object.assign({ baseUrl: '/' }, window._meterConfig);

function createMeter(queue) {

    const SERVER_API_URL = '/api/event';
    const MAX_EVENTS_TO_PUSH = 100
    const PUSH_INTERVAL = 60000
    let events = []

    for (const eventValObj of queue) {
        events.push(createEventDto(eventValObj))
        if (events.length === MAX_EVENTS_TO_PUSH) {
            pushEventsToServer()
        }
    }

    window.addEventListener(
        "unload",
        _ => {
            if (!events.length) {
                return
            }
            navigator.sendBeacon(`${_meterConfig.baseUrl}${SERVER_API_URL}`, JSON.stringify(events))
        },
        {capture: true}
    );

    function formatWithZeroPrefix(value) {
        if (value >= 10) {
            return `${value}`
        }

        return `0${value}`
    }

    function  createEventDto(eventValObj) {
        const eventDateTime = new Date()
        const year = eventDateTime.getUTCFullYear()
        const month = formatWithZeroPrefix(eventDateTime.getUTCMonth()+1)
        const date = formatWithZeroPrefix(eventDateTime.getUTCDate())
        const hours = formatWithZeroPrefix(eventDateTime.getUTCHours())
        const minutes = formatWithZeroPrefix(eventDateTime.getUTCMinutes())
        const seconds = formatWithZeroPrefix(eventDateTime.getUTCSeconds())
        const millis = formatWithZeroPrefix(eventDateTime.getUTCMilliseconds())

        const eventDto = {
            eventDateTimeOnClient: `${year}-${month}-${date} ${hours}:${minutes}:${seconds}.${millis}`,
            clientTimezone: Intl.DateTimeFormat().resolvedOptions().timeZone,
            sourceHostName: window.location.hostname,
            sourcePathName: window.location.pathname
        };

        return {...eventDto, ...eventValObj};
    }

    function pushEventsToServer() {
            if (events.length === 0) {
            return
        }

        const body = JSON.stringify(events)
        events = []
        fetch(
        `${_meterConfig.baseUrl}${SERVER_API_URL}`,
        {
            method: 'POST',
            body
        });
    }

    setInterval(_ => pushEventsToServer(), PUSH_INTERVAL)

    return {
        push(eventValObj) {
            events.push(createEventDto(eventValObj))
            if (events.length === MAX_EVENTS_TO_PUSH) {
                pushEventsToServer()
            }
        }
    };
}

_meter = createMeter(_meter);
