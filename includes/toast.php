<?php
function set_toast($message, $type = 'warning') {
    $_SESSION['message'] = $message;
    $_SESSION['message_type'] = $type;
}

function render_toast() {
    if (isset($_SESSION['message'])) {
        $toast_class = 'text-bg-' . $_SESSION['message_type'];
        echo '<div class="toast-container top-0 start-50 translate-middle-x mt-2">
                <div id="toastMessage" class="toast align-items-center ' . $toast_class . ' border-0 w-auto" role="alert" aria-live="assertive" aria-atomic="true">
                    <div class="d-flex">
                        <div class="toast-body">' . $_SESSION['message'] . '</div>
                        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                    </div>
                </div>
            </div>';
        unset($_SESSION['message']);
        unset($_SESSION['message_type']);
    }
}
?>