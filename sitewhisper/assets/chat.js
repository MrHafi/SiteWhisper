document.addEventListener( 'DOMContentLoaded', function () {

    // Elements
    const bubble   = document.getElementById( 'sw-bubble' );  // Chat icon button
    const window_  = document.getElementById( 'sw-window' ); // Chat window container
    const closeBtn = document.getElementById( 'sw-close' );// Close button
    const input    = document.getElementById( 'sw-input' );
    const sendBtn  = document.getElementById( 'sw-send' );
    const messages = document.getElementById( 'sw-messages' );

    // Open chat window
    bubble.addEventListener( 'click', function () {
        window_.style.display = 'flex';
        bubble.style.display  = 'none';
    });

    // Close chat window
    closeBtn.addEventListener( 'click', function () {
        window_.style.display = 'none';
        bubble.style.display  = 'flex';
    });

    // Send on button click
    sendBtn.addEventListener( 'click', send_message );

    // Send on Enter key
    input.addEventListener( 'keypress', function ( e ) {
        if ( e.key === 'Enter' ) send_message();
    });

    // Add message bubble to chat
    function add_message( text, type ) {
        const div = document.createElement( 'div' );
        div.classList.add( 'sw-message', 'sw-' + type );
        div.textContent = text;
        messages.appendChild( div );
        messages.scrollTop = messages.scrollHeight;
        return div;
    }

    // Show typing animation
    function show_typing() {
        const div = document.createElement( 'div' );
        div.classList.add( 'sw-typing' );
        div.id = 'sw-typing';
        div.innerHTML = '<span></span><span></span><span></span>';
        messages.appendChild( div );
        messages.scrollTop = messages.scrollHeight;
    }

    // Remove typing animation
    function hide_typing() {
        const typing = document.getElementById( 'sw-typing' );
        if ( typing ) typing.remove();
    }

    // Main send function
    function send_message() {
        const text = input.value.trim();
        if ( ! text ) return;

        // Show user message
        add_message( text, 'user' ); // Show user's message in chat
        input.value = '';// Clear input field


        // Show typing animation
        show_typing();

        // Disable input while waiting
        input.disabled  = true;
        sendBtn.disabled = true;

        // Send to WordPress AJAX
        fetch( SW.ajax_url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({
                action:  'sw_send_message',
                message: text,
                nonce:   SW.nonce,
            })
        })
        .then( res => res.json() )
        .then( data => {
            hide_typing();
            // Show bot response
            add_message( data.success ? data.data : 'Something went wrong.', 'bot' );
        })
        .catch( () => {
            hide_typing();
            add_message( 'Connection error. Please try again.', 'bot' );
        })
        .finally( () => {
            // Re-enable input
            input.disabled   = false;
            sendBtn.disabled = false;
            input.focus();
        });
    }




// Load chat history on page load
fetch( SW.ajax_url, {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: new URLSearchParams({
        action: 'sw_get_history',
        nonce:  SW.nonce,
    })
})
.then( res => res.json() )
.then( data => {
    if ( data.success && data.data.length > 0 ) {
        // Show each saved message in chat
        data.data.forEach( row => {
            add_message( row.message,  'user' ); // user message
            add_message( row.response, 'bot'  ); // AI response
        });
    }
});

});