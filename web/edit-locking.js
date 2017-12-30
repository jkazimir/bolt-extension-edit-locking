;(function ($, w) {

    var messageHandlers = {
        editLockGranted: onEditLockGranted,
        editLockDenied: onEditLockDenied
    };

    var $form = $('form[name="content_edit"]'),
        $page,
        $lockingAlert,
        contenttype,
        id,
        state,
        ws;

    // If we're not in an edit page, don't do anything
    if ($form.length === 0) {
        return;
    }

    /**
     * Initialize edit locking
     */
    function init() {
        $page = $('#navpage-content');
        $lockingAlert = $('<div class="alert alert-warning" role="alert" />');
        contenttype = $('#contenttype').val();
        id = $('#id').val();

        // If there's no id, this is new content, no need for locking here
        if (!id) {
            return;
        }

        $lockingAlert.hide();
        $page.find('.row:first > .col-xs-12').prepend($lockingAlert);

        disableEditing();
        initSocket();
    }

    /**
     * Initialize web socket
     */
    function initSocket() {
        var protocol = w.location.protocol === 'https:' ? 'wss:' : 'ws:';
        ws = new WebSocket(protocol + '//' + w.location.hostname + ':8080');

        state = 'init';

        ws.onopen = onOpen;
        ws.onclose = onClose;
        ws.onerror = onError;
        ws.onmessage = onMessage;
    }

    /**
     * Handle websocket connection open event
     *
     * @param {Event} e
     */
    function onOpen(e) {
        state = 'requestingLock';

        ws.send(JSON.stringify({
            type: 'requestEditLock',
            contenttype: contenttype,
            id: id
        }));
    }

    /**
     * Handle websocket connection close event
     *
     * This could be set to disable editing for more restrictive locking.
     *
     * For now, it's probably best to simply continue on with the initial
     * editing state. If the socket server is down for any new edit page loads,
     * we will fallback onto default behavior.
     *
     * @param {Event} e
     */
    function onClose(e) {
        if (state === 'init' || state === 'requestingLock') {
            enableEditing();
        }

        state = 'closed';
    }

    /**
     * Handle websocket error
     *
     * @param {Event} e
     */
    function onError(e) {
        ws.close();

        console.log('Websocket connection error', e);
    }

    /**
     * Handle incoming websocket messages
     *
     * @param {Event} e
     */
    function onMessage(e) {
        try {
            var message = JSON.parse(e.data);
        } catch(err) {
            return;
        }

        if (!message.type || !messageHandlers[message.type]) {
            return;
        }

        messageHandlers[message.type](message);
    }

    /**
     * Handle edit lock granted
     *
     * @param {object} message
     */
    function onEditLockGranted(message) {
        enableEditing();
    }

    /**
     * Handle edit lock denied
     *
     * @param {object} message
     */
    function onEditLockDenied(message) {
        disableEditing('Cannot open for editing. This has been opened by another user since ' + message.time + '.');
    }

    /**
     * Enabled editing
     */
    function enableEditing() {
        $page.removeClass('editing-disabled');
        $lockingAlert.hide();
        state = 'editEnabled';
    }

    /**
     * Disable editing
     *
     * @param {string} message
     */
    function disableEditing(message) {
        $page.addClass('editing-disabled');

        if (typeof message !== 'undefined') {
            $lockingAlert.html(message);
            $lockingAlert.show();
        }

        state = 'editDisabled';
    }

    init();

})(jQuery, window);
